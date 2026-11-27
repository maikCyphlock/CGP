<?php

namespace App\Http\Controllers\Oac;

use App\Http\Controllers\Controller;
use App\Models\CaseFile;
use App\Models\CaseNotificacion;
use App\Models\CaseRemision;
use App\Support\DacEstados;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Controlador dedicado para registrar remisiones/derivaciones de expedientes
 * y notificaciones formales al ciudadano en la base de datos.
 */
class RemisionNotificacionController extends Controller
{
    public function storeRemision(Request $request, CaseFile $expediente)
    {
        $d = $request->validate([
            'deriv_scope' => 'required|string|max:255',
            'deriv_dest_type' => ['required', 'string', 'max:255', Rule::in(DacEstados::ENTES_REMISION)],
            'deriv_unit' => 'nullable|string|max:255',
            'deriv_external_entity' => 'nullable|required_if:deriv_dest_type,Órgano o ente con competencia|string|max:500',
            'deriv_office_number' => 'nullable|string|max:100',
            'deriv_office_date' => 'nullable|date',
            'deriv_letter_url' => 'nullable|url:http,https|max:500',
            'deriv_fundamento' => 'nullable|string|max:3000',
            'deriv_recaudos' => 'nullable|array',
            'deriv_recaudos.*' => 'string|max:80',
            'deriv_citizen_notified' => 'nullable|boolean',
            'deriv_citizen_notified_at' => 'nullable|date',
            'deriv_citizen_notified_ref' => 'nullable|string|max:255',
            'nota' => 'required|string|min:5|max:2000',
        ], [
            'deriv_scope.required' => 'Seleccione el ámbito de competencia.',
            'deriv_dest_type.required' => 'Seleccione el ente o destino de remisión.',
            'deriv_dest_type.in' => 'El ente o destino de remisión seleccionado no es válido.',
            'deriv_external_entity.required_if' => 'Indique el órgano o ente competente al que se remite.',
            'deriv_letter_url.url' => 'El enlace del oficio debe ser una URL válida (http:// o https://).',
            'nota.required' => 'La observación o motivo de la remisión es obligatorio.',
            'nota.min' => 'La observación debe tener al menos 5 caracteres.',
        ]);

        if (in_array($d['deriv_dest_type'], ['Contraloría del Municipio Páez', 'Unidad de Auditoría Interna'], true)
            && empty($d['deriv_unit'])) {
            return back()
                ->withInput()
                ->withErrors(['deriv_unit' => 'Seleccione la dirección o unidad interna correspondiente.']);
        }

        $user = $request->user();

        DB::transaction(function () use ($d, $expediente, $user) {
            $e = CaseFile::whereKey($expediente->id)->lockForUpdate()->firstOrFail();

            $destType = $d['deriv_dest_type'];
            $unidadInterna = in_array($destType, ['Contraloría del Municipio Páez', 'Unidad de Auditoría Interna'], true)
                ? ($d['deriv_unit'] ?? null)
                : null;
            $enteExterno = $destType === 'Órgano o ente con competencia'
                ? ($d['deriv_external_entity'] ?? null)
                : null;

            $refUnitId = $unidadInterna
                ? DB::table('referral_unit')->where('name', $unidadInterna)->value('id')
                : null;

            $ciudadanoNotificado = ! empty($d['deriv_citizen_notified']);
            $fechaNotifPrevia = $ciudadanoNotificado
                ? ($d['deriv_citizen_notified_at'] ?? now()->toDateString())
                : null;

            CaseRemision::create([
                'case_file_id' => $e->id,
                'user_id' => $user->id,
                'ambito_competencia' => $d['deriv_scope'],
                'ente_destino' => $destType,
                'unidad_interna' => $unidadInterna,
                'ente_externo' => $enteExterno,
                'referral_unit_id' => $refUnitId,
                'numero_oficio' => $d['deriv_office_number'] ?? null,
                'fecha_remision' => $d['deriv_office_date'] ?? null,
                'oficio_url' => $d['deriv_letter_url'] ?? null,
                'fundamento_juridico' => $d['deriv_fundamento'] ?? null,
                'recaudos' => $d['deriv_recaudos'] ?? [],
                'ciudadano_notificado' => $ciudadanoNotificado,
                'fecha_notificacion_previa' => $fechaNotifPrevia,
                'referencia_notificacion' => $ciudadanoNotificado ? ($d['deriv_citizen_notified_ref'] ?? null) : null,
                'observacion' => $d['nota'],
            ]);

            $cambios = [
                'deriv_scope' => $d['deriv_scope'],
                'deriv_dest_type' => $destType,
                'deriv_unit' => $unidadInterna,
                'deriv_external_entity' => $enteExterno,
                'deriv_office_number' => $d['deriv_office_number'] ?? null,
                'deriv_office_date' => $d['deriv_office_date'] ?? null,
                'deriv_letter_url' => $d['deriv_letter_url'] ?? null,
                'deriv_fundamento' => $d['deriv_fundamento'] ?? null,
                'deriv_recaudos' => $d['deriv_recaudos'] ?? [],
                'deriv_citizen_notified' => $ciudadanoNotificado,
                'deriv_citizen_notified_at' => $fechaNotifPrevia,
                'deriv_citizen_notified_ref' => $ciudadanoNotificado ? ($d['deriv_citizen_notified_ref'] ?? null) : null,
                'referred_at' => now(),
                'referred_by' => $user->id,
            ];

            if ($refUnitId) {
                $cambios['referral_unit_id'] = $refUnitId;
                $cambios['assigned_unit_id'] = $refUnitId;
            }
            if (! empty($d['deriv_letter_url'])) {
                $cambios['referral_letter_url'] = $d['deriv_letter_url'];
            }

            $statusReferred = DB::table('case_status')->where('code', 'REFERRED')->first();
            $currentStatus = DB::table('case_status')->where('id', $e->status_id)->first();
            $cambioEstado = $statusReferred && $currentStatus && ! $currentStatus->is_terminal && $currentStatus->id !== $statusReferred->id;
            if ($cambioEstado) {
                $cambios['status_id'] = $statusReferred->id;
            }

            $e->update($cambios);

            if ($cambioEstado) {
                $logId = DB::table('case_status_log')
                    ->where('case_file_id', $e->id)
                    ->where('new_status', $statusReferred->id)
                    ->max('id');
                if ($logId) {
                    DB::table('case_status_log')->where('id', $logId)->update(['changed_by' => $user->id]);
                }
            }

            $destinoTexto = $unidadInterna ?: ($enteExterno ?: $destType);

            DB::table('case_action')->insert([
                'case_file_id' => $e->id,
                'user_id' => $user->id,
                'action_type' => 'DERIVATION',
                'payload' => json_encode(array_filter([
                    'accion' => 'DERIVATION',
                    'tipo_accion' => 'Remisión / derivación formal registrada',
                    'caso' => $e->case_number,
                    'ambito' => $d['deriv_scope'],
                    'tipo_destino' => $destType,
                    'unidad' => $destinoTexto,
                    'nro_oficio' => $d['deriv_office_number'] ?? null,
                    'oficio' => $d['deriv_letter_url'] ?? null,
                    'nota' => $d['nota'],
                ], fn ($v) => $v !== null)),
            ]);
        });

        return redirect()
            ->route('oac.expedientes.show', $expediente)
            ->with('seccion_activa', 'seccion-remision-notificacion')
            ->with('ok', 'Remisión formal guardada en base de datos y registrada en el historial.');
    }

    public function storeNotificacion(Request $request, CaseFile $expediente)
    {
        $d = $request->validate([
            'remision_id' => [
                'nullable',
                'uuid',
                Rule::exists('case_remisiones', 'id')->where('case_file_id', $expediente->id),
            ],
            'response_type' => 'required|string|max:100',
            'response_date' => 'required|date',
            'is_competencia' => 'nullable|in:si,no,na',
            'referred_entity' => 'nullable|string|max:500',
            'legal_basis' => 'nullable|string|max:1000',
            'office_circular' => 'nullable|string|max:200',
            'subsanation_days' => 'nullable|integer|min:1|max:30',
            'viabilidad_result' => 'nullable|string|max:1000',
            'informed_max_authority' => 'nullable|boolean',
            'response_text' => 'required|string|min:10|max:5000',
            'issued_by_name' => 'required|string|max:200',
            'issued_by_cargo' => 'required|string|max:200',
        ], [
            'response_type.required' => 'Seleccione el tipo de respuesta o estatus del trámite.',
            'response_date.required' => 'Indique la fecha de la comunicación.',
            'response_text.required' => 'El mensaje de notificación al ciudadano es obligatorio.',
            'response_text.min' => 'El mensaje debe tener al menos 10 caracteres.',
            'issued_by_name.required' => 'Indique el nombre del firmante.',
            'issued_by_cargo.required' => 'Indique el cargo del firmante.',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($d, $expediente, $user) {
            $e = CaseFile::whereKey($expediente->id)->lockForUpdate()->firstOrFail();

            CaseNotificacion::create([
                'case_file_id' => $e->id,
                'user_id' => $user->id,
                'remision_id' => $d['remision_id'] ?? null,
                'response_type' => $d['response_type'],
                'response_date' => $d['response_date'],
                'is_competencia' => $d['is_competencia'] ?? null,
                'referred_entity' => $d['referred_entity'] ?? null,
                'legal_basis' => $d['legal_basis'] ?? null,
                'office_circular' => $d['office_circular'] ?? null,
                'subsanation_days' => $d['subsanation_days'] ?? null,
                'viabilidad_result' => $d['viabilidad_result'] ?? null,
                'informed_max_authority' => ! empty($d['informed_max_authority']),
                'response_text' => $d['response_text'],
                'issued_by_name' => $d['issued_by_name'],
                'issued_by_cargo' => $d['issued_by_cargo'],
            ]);

            DB::table('case_action')->insert([
                'case_file_id' => $e->id,
                'user_id' => $user->id,
                'action_type' => 'CITIZEN_RESPONSE',
                'payload' => json_encode(array_filter([
                    'accion' => 'CITIZEN_RESPONSE',
                    'tipo_accion' => 'Notificación formal al ciudadano emitida',
                    'caso' => $e->case_number,
                    'tipo_respuesta' => $d['response_type'],
                    'ente_remitido' => $d['referred_entity'] ?? null,
                    'firmante' => $d['issued_by_name'].' — '.$d['issued_by_cargo'],
                    'nota' => $d['response_text'],
                ], fn ($v) => $v !== null)),
            ]);
        });

        return redirect()
            ->route('oac.expedientes.show', $expediente)
            ->with('seccion_activa', 'seccion-remision-notificacion')
            ->with('ok', 'Notificación formal al ciudadano guardada y registrada exitosamente.');
    }
}
