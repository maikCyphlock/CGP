@extends('layouts.oac')

@section('titulo', 'Expediente '.$e->case_number)

@section('contenido')
@php
  $tz  = config('app.timezone');
  $cd  = $c->contact_data ?? [];
  $fecha = fn ($f) => $f ? \Illuminate\Support\Carbon::parse($f)->timezone($tz)->format('d/m/Y h:i a') : '—';
  $fechaCorta = fn ($f) => $f ? \Illuminate\Support\Carbon::parse($f)->timezone($tz)->format('d/m/Y') : '—';
  $dato = fn ($label, $valor) => '<div class="col-12 col-sm-6 col-lg-4 min-w-0"><div class="text-muted small">'.e($label).'</div><div class="fw-semibold texto-ajustable">'.(filled($valor) ? e($valor) : '—').'</div></div>';

  // Fases / estados del trámite alineados con el procedimiento de la DAC
  $etiquetasFaseEstado = [
    'PROCESSING'  => 'En tramitación / valoración',
    'SUBSANATION' => 'En subsanación',
    'REFERRED'    => 'Remitido / derivado',
    'INVALIDATED' => 'Improcedente / desestimado y archivado',
    'ARCHIVED'    => 'Cerrado / archivado',
    'IN_REVIEW'   => 'En Revisión',
    'PROCESSED'   => 'Procesando',
  ];

  // Áreas internas de la DAC
  $areasManualDAC = [
    'Área de Atención de Denuncia, Quejas, Reclamos, Asesorías, Sugerencias y Peticiones (AAD)',
    'Área de Información y Promoción a la Participación Ciudadana (AIP)',
  ];

  // Figuras / cargos de la DAC / Contraloría
  $cargosManualDAC = [
    'Director(a) de la Dirección de Atención al Ciudadano (DAC)',
    'Funcionario(a) / Responsable de Mecanismo',
    'Promotor(a) Adscrito a la DAC',
    'Contralor(a) Municipal',
  ];

  // Opciones para Unidad interna de la Contraloría (UAI)
  $unidadesInternasContraloria = [
    'Unidad de Auditoría Interna (UAI)',
  ];

  $seccionesPermitidas = array_filter([
    'seccion-historial',
    'seccion-expediente',
    'seccion-valoracion-asignacion',
    $puedeEscribir ? 'seccion-remision-notificacion' : null,
  ]);

  $seccionActiva = old('_seccion_activa')
    ?? session('seccion_activa')
    ?? session('tab')
    ?? request('seccion');

  // Compatibilidad con nombres anteriores si vienen en old() o query/hash
  $mapaCompatibilidad = [
    'seccion-ciudadano'  => 'seccion-expediente',
    'seccion-tramite'    => 'seccion-expediente',
    'seccion-soportes'   => 'seccion-expediente',
    'seccion-asignacion' => 'seccion-valoracion-asignacion',
    'seccion-derivacion' => 'seccion-remision-notificacion',
    'seccion-respuesta'  => 'seccion-remision-notificacion',
  ];
  if (isset($mapaCompatibilidad[$seccionActiva])) {
    $seccionActiva = $mapaCompatibilidad[$seccionActiva];
  }

  $tieneErrorActuacion = $errors->hasAny(['estatus', 'referral_unit_id', 'referral_letter_url', 'desde'])
    || ($errors->has('nota') && old('_seccion_activa') === 'seccion-historial');

  $tieneErrorClasificacion = $errors->hasAny(['irregularity_type_id', 'analyst_notes']);
  $tieneErrorAsignacion    = $errors->hasAny(['assigned_unit_id', 'assigned_position_id', 'assigned_user_id', 'assignment_notes']);
  $tieneErrorDerivacion    = $errors->hasAny(['deriv_scope', 'deriv_dest_type', 'deriv_unit', 'deriv_external_entity', 'deriv_office_number', 'deriv_office_date', 'deriv_letter_url', 'deriv_fundamento', 'deriv_recaudos', 'deriv_citizen_notified']);
  $tieneErrorRespuesta     = $errors->hasAny(['response_type', 'response_date', 'is_competencia', 'referred_entity', 'legal_basis', 'office_circular', 'subsanation_days', 'viabilidad_result', 'response_text', 'issued_by_name', 'issued_by_cargo']);

  if (!$seccionActiva && $errors->any()) {
    if ($tieneErrorActuacion) {
      $seccionActiva = 'seccion-historial';
    } elseif ($tieneErrorClasificacion || $tieneErrorAsignacion) {
      $seccionActiva = 'seccion-valoracion-asignacion';
    } elseif ($tieneErrorDerivacion || $tieneErrorRespuesta) {
      $seccionActiva = 'seccion-remision-notificacion';
    }
  }

  if (!in_array($seccionActiva, $seccionesPermitidas, true)) {
    $seccionActiva = 'seccion-historial';
  }
@endphp

<style>
  .seccion-expediente {
    animation: fadeInSeccion 0.18s ease-in-out;
    max-width: 100%;
    min-width: 0;
  }
  @keyframes fadeInSeccion {
    from { opacity: 0; transform: translateY(4px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .texto-ajustable {
    overflow-wrap: anywhere;
    word-break: break-word;
    max-width: 100%;
  }
  .badge-fase-estado {
    display: inline-block;
    max-width: 100%;
    white-space: normal;
    text-align: left;
    line-height: 1.35;
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 600;
  }
  .seccion-titulo {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0d1b3e;
    margin-bottom: 2px;
    overflow-wrap: anywhere;
  }
  .seccion-subtitulo {
    font-size: 0.82rem;
    color: #64748b;
    margin-bottom: 0;
    overflow-wrap: anywhere;
  }
  .barra-acciones {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 24px;
    max-width: 100%;
  }
  .barra-acciones .btn-accion {
    font-size: 0.82rem;
    font-weight: 600;
    padding: 7px 14px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #1e293b;
    text-decoration: none;
    transition: all 0.15s;
    max-width: 100%;
    white-space: normal;
    text-align: left;
    cursor: pointer;
  }
  .barra-acciones .btn-accion:hover {
    background: #1565c0;
    color: #fff;
    border-color: #1565c0;
  }
  .barra-acciones .btn-accion.activo {
    background: #0d1b3e;
    color: #fff;
    border-color: #0d1b3e;
    box-shadow: 0 2px 6px rgba(13, 27, 62, 0.18);
  }
  .hist-tabla td {
    vertical-align: top;
    padding: 10px 12px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.88rem;
  }
  .hist-tabla tr:last-child td { border-bottom: none; }
  .hist-tabla .hist-accion { font-weight: 600; color: #0d1b3e; overflow-wrap: anywhere; }
  .hist-tabla .hist-fecha { white-space: nowrap; color: #64748b; font-size: 0.8rem; }
  .hist-tabla .hist-usuario { color: #475569; font-size: 0.8rem; overflow-wrap: anywhere; }
  .hist-nota { font-size: 0.85rem; color: #374151; white-space: pre-wrap; overflow-wrap: anywhere; word-break: break-word; margin-top: 4px; }
  .card-seccion {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    margin-bottom: 24px;
    max-width: 100%;
    overflow: hidden;
  }
  .card-seccion-header {
    padding: 16px 20px 12px;
    border-bottom: 1px solid #f1f5f9;
  }
  .card-seccion-body {
    padding: 20px;
    max-width: 100%;
  }
  @media (max-width: 575.98px) {
    .card-seccion-header { padding: 14px 14px 10px; }
    .card-seccion-body { padding: 14px; }
  }
  .form-label-sm { font-size: 0.83rem; font-weight: 600; margin-bottom: 4px; color: #374151; overflow-wrap: anywhere; }
  .req { color: #dc2626; }
  .ficha-asignacion {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 14px 16px;
    max-width: 100%;
  }
  .dato-label { font-size: 0.78rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
  .dato-valor { font-size: 0.95rem; font-weight: 700; color: #0d1b3e; overflow-wrap: anywhere; word-break: break-word; }
  .checklist-deriv label { font-size: 0.87rem; overflow-wrap: anywhere; }
  .guia-manual-pasos {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    font-size: 0.82rem;
  }
  .guia-manual-pasos ol {
    margin: 6px 0 0;
    padding-left: 18px;
    color: #1e293b;
  }
  .guia-manual-pasos li { margin-bottom: 3px; overflow-wrap: anywhere; }
</style>

{{-- ── Cabecera institucional del Expediente ── --}}
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('oac.expedientes.index') }}" class="btn btn-sm btn-outline-secondary">Volver al listado</a>
</div>

@if (session('ok'))
  <div class="alert alert-success alert-dismissible fade show py-2 mb-3" role="alert">
    {{ session('ok') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif
@if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert">
    <strong>Revise los siguientes errores:</strong>
    <ul class="mb-0 mt-1">
      @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

{{-- ── Banner Regla de Negocio: Lapso de Anticipación para Confirmar Datos ── --}}
@if ($e->data_confirmed_at)
  <div class="alert alert-success py-2 px-3 small mb-3 border d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
      <strong>Conformidad de datos validada:</strong> El denunciante revisó y confirmó sus datos el {{ $fecha($e->data_confirmed_at) }}.
    </div>
    <span class="badge bg-success text-white">Validado por el ciudadano</span>
  </div>
@elseif ($confirmacionVigente)
  <div class="alert alert-warning py-2 px-3 small mb-3 border d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
      <strong>Lapso de anticipación activo:</strong> El denunciante dispone de {{ $diasRestantesConfirmacion }} día(s) (hasta el {{ $fechaCorta($e->data_confirmation_deadline) }}) para revisar, validar o rectificar sus datos antes de la formalización del expediente.
    </div>
    <span class="badge bg-warning text-dark">Lapso activo ({{ $diasRestantesConfirmacion }} d)</span>
  </div>
@elseif ($confirmacionVencida)
  <div class="alert alert-light py-2 px-3 small mb-3 border text-muted d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
      <strong>Lapso de anticipación:</strong> Concluyó el {{ $fechaCorta($e->data_confirmation_deadline) }}. Expediente procedente para formalización administrativa.
    </div>
    <span class="badge bg-secondary">Lapso concluido</span>
  </div>
@endif

{{-- ══════════════════════════════════════════════════
     BARRA DE NAVEGACIÓN (HISTORIAL PRIMERO)
══════════════════════════════════════════════════ --}}
<div class="barra-acciones" role="tablist" aria-label="Secciones del expediente">
  <span class="text-muted small fw-semibold align-self-center me-1">Sección:</span>
  <button type="button" data-target="seccion-historial" class="btn-accion {{ $seccionActiva === 'seccion-historial' ? 'activo' : '' }}" role="tab" aria-selected="{{ $seccionActiva === 'seccion-historial' ? 'true' : 'false' }}">Historial y Actuaciones</button>
  <button type="button" data-target="seccion-expediente" class="btn-accion {{ $seccionActiva === 'seccion-expediente' ? 'activo' : '' }}" role="tab" aria-selected="{{ $seccionActiva === 'seccion-expediente' ? 'true' : 'false' }}">Expediente</button>
  <button type="button" data-target="seccion-valoracion-asignacion" class="btn-accion {{ $seccionActiva === 'seccion-valoracion-asignacion' ? 'activo' : '' }}" role="tab" aria-selected="{{ $seccionActiva === 'seccion-valoracion-asignacion' ? 'true' : 'false' }}">Valoración y Asignación</button>
  @if ($puedeEscribir)
  <button type="button" data-target="seccion-remision-notificacion" class="btn-accion {{ $seccionActiva === 'seccion-remision-notificacion' ? 'activo' : '' }}" role="tab" aria-selected="{{ $seccionActiva === 'seccion-remision-notificacion' ? 'true' : 'false' }}">Remisión y Notificación</button>
  @endif
</div>

<div class="row g-4">
  {{-- ══════════════════════════════════════════════════
       CONTENIDO PRINCIPAL (UNA SECCIÓN VISIBLE A LA VEZ)
  ══════════════════════════════════════════════════ --}}
  <div class="col-12 col-xl-9 min-w-0">

    {{-- ══════════════════════════════════════════════════
         HISTORIAL Y ACTUACIONES (INCLUYE CIERRE)
    ══════════════════════════════════════════════════ --}}
    <div id="seccion-historial" class="card-seccion seccion-expediente {{ $seccionActiva !== 'seccion-historial' ? 'd-none' : '' }}" role="tabpanel">
      <div class="card-seccion-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="min-w-0">
          <div class="seccion-titulo">Historial y Actuaciones del Expediente</div>
          <p class="seccion-subtitulo">Registro cronológico de diligencias, fases procedimentales y autos de cierre/archivo.</p>
        </div>
        @if ($puedeEscribir)
          <button class="btn btn-sm btn-cgp fw-semibold" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapseRegistrarActuacion"
            aria-expanded="{{ $tieneErrorActuacion ? 'true' : 'false' }}"
            aria-controls="collapseRegistrarActuacion">
            + Registrar Actuación
          </button>
        @endif
      </div>

      @if ($puedeEscribir)
        <div class="collapse {{ $tieneErrorActuacion ? 'show' : '' }}" id="collapseRegistrarActuacion">
          <div class="p-3 p-sm-4 border-bottom" style="background: #f8fafc;">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
              <div class="min-w-0">
                <div class="fw-bold text-dark" style="font-size: 0.95rem;">Registrar Actuación o Fase del Trámite</div>
                <div class="text-muted small">Toda actuación queda asentada en orden cronológico con fecha, hora y funcionario responsable.</div>
              </div>
              <button type="button" class="btn-close flex-shrink-0" data-bs-toggle="collapse" data-bs-target="#collapseRegistrarActuacion" aria-label="Cerrar"></button>
            </div>

            <form method="POST" action="{{ route('oac.expedientes.actuar', $e) }}" class="bg-white p-3 rounded border">
              @csrf
              <input type="hidden" name="_seccion_activa" value="seccion-historial">
              <input type="hidden" name="desde" value="{{ $actual->code }}">

              <div class="row g-3 mb-3">
                <div class="col-12 col-md-6 min-w-0">
                  <label class="form-label-sm" for="accion">Actuación / Fase del trámite</label>
                  <select name="estatus" id="accion" class="form-select form-select-sm @error('estatus') is-invalid @enderror">
                    <option value="">Constancia / diligencia en el trámite (sin cambiar estado)</option>
                    @foreach ($opciones as $code => $etiqueta)
                      <option value="{{ $code }}" @selected(old('estatus') === $code)>{{ $etiqueta }}</option>
                    @endforeach
                  </select>
                  <div id="ayuda-accion-manual" class="form-text texto-ajustable">Asienta una actuación o nota cronológica conservando el estado actual del expediente.</div>
                  @error('estatus') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div id="bloque-unidad" class="col-12 col-md-6 min-w-0" hidden>
                  <label class="form-label-sm">Dirección / Unidad a la que se remite <span class="req">*</span></label>
                  <select name="referral_unit_id" class="form-select form-select-sm @error('referral_unit_id') is-invalid @enderror">
                    <option value="">Seleccione…</option>
                    @foreach ($unidades as $un)
                      <option value="{{ $un->id }}" @selected(old('referral_unit_id') == $un->id)>{{ $un->name }}</option>
                    @endforeach
                  </select>
                  @error('referral_unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div id="bloque-oficio" class="col-12 min-w-0" hidden>
                  <label class="form-label-sm">Enlace al oficio de remisión <span class="text-muted">(opcional)</span></label>
                  <input type="url" name="referral_letter_url" class="form-control form-control-sm @error('referral_letter_url') is-invalid @enderror" maxlength="300" placeholder="https://…" value="{{ old('referral_letter_url') }}">
                  @error('referral_letter_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div id="bloque-aviso-cierre" class="col-12 min-w-0" hidden>
                  <div class="alert alert-warning py-2 px-3 mb-0 small texto-ajustable">
                    <strong>Cierre / Archivo:</strong> debe quedar motivado por escrito, contar con la revisión/visto bueno de la DAC y aprobación del Contralor(a) Municipal.
                  </div>
                </div>

                @if ($confirmacionVigente)
                  <div id="bloque-aviso-lapso" class="col-12 min-w-0" style="display:none;">
                    <div class="alert alert-warning py-2 px-3 mb-0 small texto-ajustable">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="ignorar_lapso_confirmacion" id="ignorar_lapso_confirmacion" value="1">
                        <label class="form-check-label fw-semibold" for="ignorar_lapso_confirmacion">
                          Excepción de formalización: Confirmo que se formaliza el expediente antes del vencimiento del lapso de anticipación (quedan {{ $diasRestantesConfirmacion }} días, vence el {{ $fechaCorta($e->data_confirmation_deadline) }}).
                        </label>
                      </div>
                    </div>
                  </div>
                @endif

                <div class="col-12 min-w-0">
                  <label class="form-label-sm" for="nota_actuacion">Observación o motivación <span class="req">*</span></label>
                  <textarea name="nota" id="nota_actuacion" class="form-control form-control-sm @error('nota') is-invalid @enderror" rows="3" minlength="5" maxlength="2000" required placeholder="Indique lo actuado, el lapso aplicable o la motivación del cierre/archivo…">{{ old('nota') }}</textarea>
                  @error('nota') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                @if ($opciones->isEmpty())
                  <span class="form-text m-0 texto-ajustable">Este expediente está cerrado/archivado; solo se pueden agregar constancias u observaciones.</span>
                @else
                  <span class="form-text m-0 texto-ajustable">Quedará asentado con fecha, hora y usuario.</span>
                @endif
                <button type="submit" class="btn btn-cgp btn-sm px-4 fw-bold">Guardar Actuación</button>
              </div>
            </form>
          </div>
        </div>
      @endif

      <div class="card-seccion-body p-0">
        <div class="table-responsive">
          <table class="table mb-0 hist-tabla">
            <thead class="table-light">
              <tr>
                <th style="font-size:0.8rem; width:160px;">Fecha</th>
                <th style="font-size:0.8rem;">Actuación / Detalle</th>
                <th style="font-size:0.8rem; width:160px;">Realizado por</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($historial as $h)
                @php
                  $isAssignment     = $h->action_type === 'ASSIGNMENT' || ($h->payload['accion'] ?? '') === 'ASSIGNMENT';
                  $isStatusChange   = $h->action_type === 'STATUS_CHANGE';
                  $isClassification = $h->action_type === 'CLASSIFICATION';
                  $isReferral       = $h->action_type === 'REFERRAL';
                  $isDerivation     = $h->action_type === 'DERIVATION'  || ($h->payload['accion'] ?? '') === 'DERIVATION';
                  $isCitizenResp    = $h->action_type === 'CITIZEN_RESPONSE' || ($h->payload['accion'] ?? '') === 'CITIZEN_RESPONSE';

                  $codigoDestino    = $h->payload['a'] ?? '';
                  $nombreEstadoHist = $etiquetasFaseEstado[$codigoDestino]
                    ?? ($estatus->firstWhere('code', $codigoDestino)?->name ?? ($codigoDestino ?: 'Nueva fase'));

                  $accionLabel = match(true) {
                    $isAssignment     => 'Asignación del trámite',
                    $isStatusChange   => 'Fase del trámite → ' . $nombreEstadoHist,
                    $isClassification => 'Valoración / Clasificación: ' . ($h->payload['irregularidad'] ?? 'Sin tipo'),
                    $isReferral       => 'Derivación a unidad competente',
                    $isDerivation     => 'Derivación formal registrada',
                    $isCitizenResp    => 'Respuesta ciudadana emitida',
                    default           => 'Constancia / diligencia registrada',
                  };
                @endphp
                <tr>
                  <td class="hist-fecha">{{ $fecha($h->performed_at) }}</td>
                  <td>
                    <div class="hist-accion">{{ $accionLabel }}</div>

                    @if ($isAssignment)
                      <div class="hist-nota mt-1">
                        @isset($h->payload['oficina']) <span class="text-muted">Oficina:</span> {{ $h->payload['oficina'] }}<br>@endisset
                        @isset($h->payload['cargo'])   <span class="text-muted">Cargo:</span> {{ $h->payload['cargo'] }}<br>@endisset
                        @isset($h->payload['funcionario']) <span class="text-muted">Funcionario:</span> {{ $h->payload['funcionario'] }}@endisset
                      </div>
                    @endif

                    @if ($isDerivation)
                      <div class="hist-nota mt-1">
                        @isset($h->payload['ambito'])       <span class="text-muted">Ámbito:</span> {{ $h->payload['ambito'] }}<br>@endisset
                        @isset($h->payload['tipo_destino']) <span class="text-muted">Destino:</span> {{ $h->payload['tipo_destino'] }}<br>@endisset
                        @isset($h->payload['unidad'])       <span class="text-muted">Unidad:</span> {{ $h->payload['unidad'] }}<br>@endisset
                        @isset($h->payload['nro_oficio'])   <span class="text-muted">Oficio N°:</span> {{ $h->payload['nro_oficio'] }}@endisset
                      </div>
                    @endif

                    @if ($isReferral && isset($h->payload['unidad']))
                      <div class="hist-nota mt-1"><span class="text-muted">Unidad:</span> {{ $h->payload['unidad'] }}</div>
                    @endif

                    @if ($isCitizenResp && isset($h->payload['firmante']))
                      <div class="hist-nota mt-1"><span class="text-muted">Firmante:</span> {{ $h->payload['firmante'] }}</div>
                    @endif

                    @if (!empty($h->payload['nota']))
                      <div class="hist-nota">{{ $h->payload['nota'] }}</div>
                    @endif

                    @isset($h->payload['oficio'])
                      <div class="mt-1 small"><a href="{{ $h->payload['oficio'] }}" target="_blank" rel="noopener noreferrer" class="fw-semibold text-primary">Ver oficio de derivación →</a></div>
                    @endisset
                  </td>
                  <td class="hist-usuario">{{ $h->usuario }}</td>
                </tr>
              @endforeach

              <tr>
                <td class="hist-fecha">{{ $fecha($e->created_at) }}</td>
                <td>
                  <div class="hist-accion" style="color:#16a34a;">Recepción y registro en el sistema</div>
                  <div class="hist-nota">Ingreso público mediante el formulario del Portal Web Institucional.</div>
                  <div class="text-muted small mt-1 texto-ajustable">Origen: Portal Web CMP · Trámite: {{ $e->claimType->name }}</div>
                </td>
                <td class="hist-usuario">Portal público</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         EXPEDIENTE (DATOS, HECHOS Y SOPORTES)
    ══════════════════════════════════════════════════ --}}
    <div id="seccion-expediente" class="card-seccion seccion-expediente {{ $seccionActiva !== 'seccion-expediente' ? 'd-none' : '' }}" role="tabpanel">
      <div class="card-seccion-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="min-w-0">
          <div class="seccion-titulo">Expediente: Solicitante, Hechos y Soportes</div>
          <p class="seccion-subtitulo">Información recibida en la DAC, narración de los hechos, recaudos y archivos consignados.</p>
        </div>
      </div>
      <div class="card-seccion-body">

        {{-- Datos del trámite y narración --}}
        <div class="fw-bold small text-uppercase text-muted mb-2">Mecanismo y hechos</div>
        <div class="row g-3 mb-3">
          {!! $dato('Mecanismo / Trámite', $e->claimType->name) !!}
          {!! $dato('Fecha de recepción', $fecha($e->created_at)) !!}
          {!! $dato('Protocolizado', $e->protocolized_at ? $fecha($e->protocolized_at) : 'Pendiente') !!}
          {!! $dato('Clasificación / Tipificación', $irregularidad ?? 'Pendiente de valoración') !!}
          @if ($unidad) {!! $dato('Remitido a', $unidad.' ('.$fechaCorta($e->referred_at).')') !!} @endif
          @if ($e->referral_letter_url)
            <div class="col-12 col-sm-6 col-lg-4 min-w-0">
              <div class="text-muted small">Oficio de remisión</div>
              <a class="fw-semibold texto-ajustable" href="{{ $e->referral_letter_url }}" target="_blank" rel="noopener noreferrer">Ver oficio →</a>
            </div>
          @endif
          @php
            $otraInstTexto = 'No';
            if ($e->other_instance && $e->other_instance_name !== 'No sé') {
                $otraInstTexto = $e->other_instance_name ? 'Sí ('.$e->other_instance_name.')' : 'Sí';
            } elseif ($e->other_instance_name === 'No sé') {
                $otraInstTexto = 'No sé';
            }
          @endphp
          {!! $dato('Presentado en otra instancia', $otraInstTexto) !!}
        </div>

        <div class="mb-3 min-w-0">
          <div class="text-muted small fw-semibold mb-1">Narración circunstanciada de los hechos</div>
          <div class="border rounded p-3 bg-light texto-ajustable" style="white-space: pre-wrap; font-size:0.92rem;">{{ $e->narrative }}</div>
        </div>

        @if ($e->analyst_notes)
          <div class="mb-3 min-w-0">
            <div class="text-muted small fw-semibold mb-1">Informe de Valoración / Notas asentadas</div>
            <div class="border rounded p-3 bg-light texto-ajustable" style="white-space: pre-wrap; font-size:0.92rem;">{{ $e->analyst_notes }}</div>
          </div>
        @endif

        @if ($consulta)
          <div class="p-3 bg-light rounded border mb-3">
            <div class="fw-bold small text-uppercase text-muted mb-2">Proyecto de Consulta Popular Nacional</div>
            <div class="row g-3">
              {!! $dato('Nombre del proyecto', $consulta->project_name) !!}
              {!! $dato('Fecha de aprobación', \Illuminate\Support\Carbon::parse($consulta->approval_date)->format('d/m/Y')) !!}
              {!! $dato('Monto', number_format($consulta->project_amount, 2, ',', '.')) !!}
              {!! $dato('Ente financiador', $consulta->funding_entity) !!}
              {!! $dato('Código SITUR', $consulta->situr_code) !!}
            </div>
          </div>
        @endif

        <hr class="my-4">

        {{-- Datos del ciudadano y señalados --}}
        <div class="fw-bold small text-uppercase text-muted mb-2">Datos del solicitante</div>
        <div class="row g-3 mb-3">
          {!! $dato('Nombre completo', $c->first_name.' '.$c->last_name) !!}
          {!! $dato('Cédula de identidad', ($docTipo === 'PASSPORT' ? 'P' : $docTipo).'-'.$c->id_doc_number) !!}
          {!! $dato('Sexo', ['M' => 'Masculino', 'F' => 'Femenino'][$c->sex] ?? $c->sex) !!}
          {!! $dato('Fecha / Lugar de nacimiento', trim(($c->birth_date?->format('d/m/Y') ?? '').' '.($cd['birth_place'] ?? ''))) !!}
          {!! $dato('Correo electrónico', $c->email) !!}
          {!! $dato('Teléfono celular', $c->mobile_phone) !!}
          {!! $dato('Teléfono habitación', $cd['home_phone'] ?? null) !!}
          {!! $dato('Teléfono trabajo', $cd['work_phone'] ?? null) !!}
          {!! $dato('Profesión / Ocupación', collect([$cd['profession'] ?? null, $cd['occupation'] ?? null])->filter()->join(' / ')) !!}
          <div class="col-12 min-w-0">
            <div class="text-muted small">Dirección</div>
            <div class="fw-semibold texto-ajustable">{{ collect([$c->address, $c->parish, $c->municipality, $c->city])->filter()->join(', ') }}</div>
          </div>
        </div>

        @if ($e->respondents->isNotEmpty())
          <div class="fw-bold small text-uppercase text-muted mb-2 texto-ajustable">Señalados / Involucrados</div>

          <div class="p-2 mb-3 rounded bg-light border small">
            <div class="text-muted fw-semibold">Ubicación Geográfica del Señalado:</div>
            <div class="fw-bold text-dark">{{ $e->incident_location ?: 'Sin dirección textual especificada' }}</div>
            @if ($e->incident_lat && $e->incident_lng)
              <div class="mt-2 d-flex align-items-center flex-wrap gap-2">
                <span class="badge bg-white text-dark border">
                  Coordenadas: <code>{{ $e->incident_lat }}, {{ $e->incident_lng }}</code>
                </span>
                <a href="https://www.google.com/maps?q={{ $e->incident_lat }},{{ $e->incident_lng }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary py-0 px-2 fw-semibold" style="font-size:0.8rem;">
                  Ver ubicación en Google Maps ↗
                </a>
              </div>
            @endif
          </div>

          @foreach ($e->respondents as $r)
            <div class="mb-2 p-2 bg-light rounded border small texto-ajustable">
              <div class="fw-bold">{{ $r->respondentType->name }}</div>
              @foreach ($r->attributes ?? [] as $k => $v)
                @if (filled($v))
                  <span class="me-3"><span class="text-muted">{{ ucfirst(str_replace('-', ' ', preg_replace('/^[a-z]{2}-/', '', $k))) }}:</span> {{ $v }}</span>
                @endif
              @endforeach
            </div>
          @endforeach
        @endif

        <hr class="my-4">

        {{-- Soportes consignados --}}
        <div class="fw-bold small text-uppercase text-muted mb-2">Soportes consignados</div>
        @if ($documentos->isNotEmpty())
          <div class="mb-3">
            <div class="text-muted small fw-semibold mb-2">Documentos físicos declarados:</div>
            <div class="d-flex flex-wrap gap-2">
              @foreach ($documentos as $d_doc)
                <span class="badge bg-light text-dark border px-3 py-2 texto-ajustable">{{ $d_doc }}</span>
              @endforeach
            </div>
          </div>
        @endif

        <div class="text-muted small fw-semibold mb-2">Archivos digitales adjuntos:</div>
        @forelse ($archivos as $a)
          <div class="d-flex flex-wrap align-items-center gap-2 py-2 border-bottom">
            <div class="flex-grow-1 min-w-0">
              <a href="{{ route('oac.expedientes.archivo', [$e, $a->id]) }}" class="fw-semibold text-decoration-none texto-ajustable">{{ $a->original_name }}</a>
              <span class="text-muted small ms-2">({{ number_format($a->size_bytes / 1024, 0, ',', '.') }} KB)</span>
            </div>
          </div>
        @empty
          <p class="text-muted mb-0">El ciudadano no adjuntó archivos digitales.</p>
        @endforelse

      </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         VALORACIÓN Y ASIGNACIÓN
    ══════════════════════════════════════════════════ --}}
    <div id="seccion-valoracion-asignacion" class="card-seccion seccion-expediente {{ $seccionActiva !== 'seccion-valoracion-asignacion' ? 'd-none' : '' }}" role="tabpanel">
      <div class="card-seccion-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="min-w-0">
          <div class="seccion-titulo">Valoración Técnica y Asignación del Trámite</div>
          <p class="seccion-subtitulo">Análisis de admisibilidad/competencia y designación del área, cargo y funcionario responsable.</p>
        </div>
      </div>

      <div class="card-seccion-body">

        <div class="guia-manual-pasos mb-4">
          <div class="fw-bold text-dark">Criterios del procedimiento:</div>
          <ol>
            <li><strong>Admisibilidad y requisitos:</strong> revise hechos, soportes y requisitos mínimos (<em>Oficio Circular N° 01-00-499</em>; lapso de <strong>5 días hábiles</strong> para subsanar si faltan recaudos en denuncias/peticiones).</li>
            <li><strong>Competencia y designación:</strong> determine si compete a la Contraloría del Municipio Páez (fondos/bienes públicos) o si corresponde remitir a otra instancia, y asigne el área, cargo y funcionario responsable.</li>
          </ol>
        </div>

        {{-- Ficha actual de asignación --}}
        <div class="ficha-asignacion mb-4">
          <div class="row g-3">
            <div class="col-12 col-sm-6 min-w-0">
              <div class="dato-label">Expediente / Mecanismo</div>
              <div class="dato-valor">{{ $e->case_number }}</div>
              <div class="text-muted small texto-ajustable">{{ $e->claimType->name }}</div>
            </div>
            <div class="col-12 col-sm-6 min-w-0">
              <div class="dato-label">Área / Oficina / Departamento</div>
              <div class="dato-valor">{{ $asignacionOficina }}</div>
            </div>
            <div class="col-12 col-sm-6 min-w-0">
              <div class="dato-label">Cargo Asignado</div>
              <div class="dato-valor">{{ $asignacionCargo }}</div>
            </div>
            <div class="col-12 col-sm-6 min-w-0">
              <div class="dato-label">Funcionario Responsable</div>
              <div class="text-secondary fw-semibold texto-ajustable">{{ $asignacionUsuario }}</div>
            </div>
            <div class="col-12 col-sm-6 min-w-0">
              <div class="dato-label">Fecha de Asignación</div>
              <div class="text-muted small">{{ $fechaCorta($asignacionFecha) }}</div>
            </div>
          </div>

          @if ($e->assignment_notes)
            <div class="p-2 bg-white rounded border mt-3 small">
              <div class="text-muted fw-semibold">Instrucciones:</div>
              <div class="texto-ajustable" style="white-space: pre-wrap;">{{ $e->assignment_notes }}</div>
            </div>
          @endif
        </div>

        {{-- Formulario de Asignación / Reasignación --}}
        @if ($puedeEscribir)
          <div class="mb-4 pb-4 border-bottom">
            <button class="btn btn-sm btn-outline-primary fw-semibold mb-3" type="button"
              data-bs-toggle="collapse" data-bs-target="#collapseAsignar"
              aria-expanded="{{ $tieneErrorAsignacion ? 'true' : 'false' }}"
              aria-controls="collapseAsignar">
              Asignar / Reasignar Responsable
            </button>
            <div class="collapse {{ $tieneErrorAsignacion ? 'show' : '' }}" id="collapseAsignar">
              <form method="POST" action="{{ route('oac.expedientes.asignar', $e) }}" class="p-3 bg-light rounded border">
                @csrf
                <input type="hidden" name="_seccion_activa" value="seccion-valoracion-asignacion">
                <div class="row g-3">
                  <div class="col-12 col-md-6 min-w-0">
                    <label class="form-label-sm">Expediente / Mecanismo</label>
                    <input type="text" class="form-control form-control-sm bg-white" value="{{ $e->case_number }} — {{ $e->claimType->name }}" readonly>
                  </div>
                  <div class="col-12 col-md-6 min-w-0">
                    <label class="form-label-sm" for="assigned_unit_id">Área de la DAC / Dependencia <span class="req">*</span></label>
                    <select name="assigned_unit_id" id="assigned_unit_id" class="form-select form-select-sm @error('assigned_unit_id') is-invalid @enderror" required>
                      <option value="">Seleccione área o dependencia…</option>
                      @foreach ($oficinas as $of)
                        <option value="{{ $of->id }}" @selected(old('assigned_unit_id', $e->assigned_unit_id ?? $e->referral_unit_id) == $of->id)>{{ $of->name }}</option>
                      @endforeach
                      @foreach ($areasManualDAC as $areaManual)
                        <option value="{{ $areaManual }}" @selected(old('assigned_unit_id', $e->assigned_unit_id ?? $e->referral_unit_id) === $areaManual)>{{ $areaManual }}</option>
                      @endforeach
                    </select>
                    @error('assigned_unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-12 col-md-6 min-w-0">
                    <label class="form-label-sm" for="assigned_position_id">Cargo / Figura <span class="req">*</span></label>
                    <select name="assigned_position_id" id="assigned_position_id" class="form-select form-select-sm @error('assigned_position_id') is-invalid @enderror" required>
                      <option value="">Seleccione cargo…</option>
                      @foreach ($cargos as $cg)
                        <option value="{{ $cg->id }}" @selected(old('assigned_position_id', $e->assigned_position_id) == $cg->id)>{{ $cg->title }}</option>
                      @endforeach
                      @foreach ($cargosManualDAC as $cargoManual)
                        <option value="{{ $cargoManual }}" @selected(old('assigned_position_id', $e->assigned_position_id) === $cargoManual)>{{ $cargoManual }}</option>
                      @endforeach
                    </select>
                    @error('assigned_position_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-12 col-md-6 min-w-0">
                    <label class="form-label-sm" for="assigned_user_id">Funcionario Designado <span class="text-muted">(opcional)</span></label>
                    <select name="assigned_user_id" id="assigned_user_id" class="form-select form-select-sm">
                      <option value="">Sin funcionario específico</option>
                      @foreach ($usuariosPersonal as $up)
                        <option value="{{ $up->id }}" @selected(old('assigned_user_id', $e->assigned_user_id) == $up->id)>
                          {{ $up->first_name }} {{ $up->last_name }} ({{ $up->cargo ?? 'Personal' }})
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-12 min-w-0">
                    <label class="form-label-sm" for="assignment_notes_inp">Instrucciones / Observación <span class="text-muted">(opcional)</span></label>
                    <textarea name="assignment_notes" id="assignment_notes_inp" class="form-control form-control-sm" rows="2" maxlength="2000"
                      placeholder="Instrucciones para la revisión o tramitación…">{{ old('assignment_notes', $e->assignment_notes) }}</textarea>
                  </div>
                </div>
                <button type="submit" class="btn btn-cgp btn-sm w-100 fw-bold mt-3">Guardar Asignación</button>
              </form>
            </div>
          </div>
        @endif

        {{-- Formulario de Clasificación y Valoración Técnica --}}
        @if ($puedeClasificar)
          <div class="p-3 rounded border" style="background: #f8fafc;">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
              <div class="min-w-0">
                <div class="fw-bold text-dark" style="font-size: 0.95rem;">Clasificación y Valoración Técnica</div>
                <div class="text-muted small">Tipificación e Informe de Valoración (admisibilidad, competencia y norma aplicable).</div>
              </div>
              <span class="badge bg-secondary-subtle text-secondary border">Valoración DAC</span>
            </div>

            <form method="POST" action="{{ route('oac.expedientes.clasificar', $e) }}">
              @csrf
              <input type="hidden" name="_seccion_activa" value="seccion-valoracion-asignacion">
              <div class="row g-3">
                <div class="col-12 col-md-6 min-w-0">
                  <label class="form-label-sm" for="irregularity_type_id">Tipificación / Clasificación</label>
                  <select name="irregularity_type_id" id="irregularity_type_id" class="form-select form-select-sm @error('irregularity_type_id') is-invalid @enderror">
                    <option value="">Sin clasificar</option>
                    @foreach ($irregularidades as $i)
                      <option value="{{ $i->id }}" @selected((old('irregularity_type_id', $e->irregularity_type_id)) == $i->id)>{{ $i->name }}</option>
                    @endforeach
                  </select>
                  @error('irregularity_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 min-w-0">
                  <label class="form-label-sm" for="analyst_notes_inp">Informe de Valoración / Notas de análisis y competencia</label>
                  <textarea name="analyst_notes" id="analyst_notes_inp" class="form-control form-control-sm @error('analyst_notes') is-invalid @enderror" rows="3" maxlength="3000"
                    placeholder="Registre análisis de requisitos (Circular 01-00-499), competencia sobre fondos/bienes públicos y criterio jurídico…">{{ old('analyst_notes', $e->analyst_notes) }}</textarea>
                  @error('analyst_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>
              <div class="mt-3 text-end">
                <button type="submit" class="btn btn-outline-dark btn-sm px-4 fw-bold">Guardar valoración</button>
              </div>
            </form>
          </div>
        @endif

      </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         REMISIÓN Y NOTIFICACIÓN
    ══════════════════════════════════════════════════ --}}
    @if ($puedeEscribir)
    <div id="seccion-remision-notificacion" class="card-seccion seccion-expediente {{ $seccionActiva !== 'seccion-remision-notificacion' ? 'd-none' : '' }}" role="tabpanel">
      <div class="card-seccion-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="min-w-0">
          <div class="seccion-titulo">Remisión y Notificación al Ciudadano</div>
          <p class="seccion-subtitulo">Registro de remisión a la Contraloría del Municipio Páez, Unidad de Auditoría Interna, CGR u órgano/ente con competencia, y notificación formal al ciudadano.</p>
        </div>
      </div>

      <div class="card-seccion-body">

        {{-- Sub-bloque A: Remisión / Derivación --}}
        <div class="fw-bold small text-uppercase text-muted mb-2">Remisión / Derivación del trámite</div>

        @if ($e->deriv_scope)
          <div class="ficha-asignacion mb-3">
            <div class="fw-bold small text-uppercase text-muted mb-2">Última derivación registrada</div>
            <div class="row g-2">
              <div class="col-12 col-sm-6 min-w-0">
                <div class="dato-label">Ámbito de competencia</div>
                <div class="dato-valor">{{ $e->deriv_scope }}</div>
              </div>
              <div class="col-12 col-sm-6 min-w-0">
                <div class="dato-label">Tipo de destino</div>
                <div class="dato-valor">{{ $e->deriv_dest_type }}</div>
              </div>
              @if ($e->deriv_unit)
              <div class="col-12 col-sm-6 min-w-0">
                <div class="dato-label">Dirección / Departamento / Unidad</div>
                <div class="dato-valor">{{ $e->deriv_unit }}</div>
              </div>
              @endif
              @if ($e->deriv_external_entity)
              <div class="col-12 col-sm-6 min-w-0">
                <div class="dato-label">Órgano o ente con competencia</div>
                <div class="dato-valor">{{ $e->deriv_external_entity }}</div>
              </div>
              @endif
              @if ($e->deriv_office_number)
              <div class="col-12 col-sm-6 min-w-0">
                <div class="dato-label">N° de oficio</div>
                <div class="dato-valor">{{ $e->deriv_office_number }}</div>
              </div>
              @endif
              @if ($e->deriv_office_date)
              <div class="col-12 col-sm-6 min-w-0">
                <div class="dato-label">Fecha de remisión</div>
                <div class="dato-valor">{{ $fechaCorta($e->deriv_office_date) }}</div>
              </div>
              @endif
              @if ($e->deriv_letter_url)
              <div class="col-12 min-w-0">
                <div class="dato-label">Oficio de derivación</div>
                <a href="{{ $e->deriv_letter_url }}" target="_blank" rel="noopener noreferrer" class="fw-semibold texto-ajustable">Ver oficio →</a>
              </div>
              @endif
              @if ($e->deriv_fundamento)
              <div class="col-12 min-w-0">
                <div class="dato-label">Fundamento jurídico</div>
                <div class="texto-ajustable" style="font-size:0.9rem; color:#374151; white-space:pre-wrap;">{{ $e->deriv_fundamento }}</div>
              </div>
              @endif
            </div>
          </div>
        @endif

        <form method="POST" action="{{ route('oac.expedientes.derivar', $e) }}" id="form-derivacion" class="p-3 bg-light rounded border mb-5">
          @csrf
          <input type="hidden" name="_seccion_activa" value="seccion-remision-notificacion">

          {{-- 1. Ámbito de competencia --}}
          <div class="mb-3">
            <label class="form-label-sm" for="deriv_scope">1. Ámbito de competencia <span class="req">*</span></label>
            <select name="deriv_scope" id="deriv_scope" class="form-select form-select-sm @error('deriv_scope') is-invalid @enderror" required>
              <option value="">Seleccione el ámbito…</option>
              <option value="Competencia de la Contraloría del Municipio Páez" @selected(old('deriv_scope', $e->deriv_scope) === 'Competencia de la Contraloría del Municipio Páez')>Competencia de la Contraloría del Municipio Páez</option>
              <option value="Contraloría General de la República Bolivariana de Venezuela (CGR)" @selected(old('deriv_scope', $e->deriv_scope) === 'Contraloría General de la República Bolivariana de Venezuela (CGR)')>Contraloría General de la República Bolivariana de Venezuela (CGR)</option>
              <option value="Unidad de Auditoría Interna (UAI)" @selected(old('deriv_scope', $e->deriv_scope) === 'Unidad de Auditoría Interna (UAI)')>Unidad de Auditoría Interna (UAI)</option>
              <option value="Órgano o ente con competencia" @selected(old('deriv_scope', $e->deriv_scope) === 'Órgano o ente con competencia')>Órgano o ente con competencia</option>
            </select>
            @error('deriv_scope') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          {{-- 2. Tipo de destino / Instancia --}}
          <div class="mb-3">
            <label class="form-label-sm" for="deriv_dest_type">2. Tipo de destino / Instancia <span class="req">*</span></label>
            <select name="deriv_dest_type" id="deriv_dest_type" class="form-select form-select-sm @error('deriv_dest_type') is-invalid @enderror" required>
              <option value="">Seleccione el tipo de destino…</option>
              <option value="Contraloría del Municipio Páez" @selected(old('deriv_dest_type', $e->deriv_dest_type) === 'Contraloría del Municipio Páez')>Contraloría del Municipio Páez</option>
              <option value="Contraloría General de la República Bolivariana de Venezuela (CGR)" @selected(old('deriv_dest_type', $e->deriv_dest_type) === 'Contraloría General de la República Bolivariana de Venezuela (CGR)')>Contraloría General de la República Bolivariana de Venezuela (CGR)</option>
              <option value="Unidad de Auditoría Interna" @selected(old('deriv_dest_type', $e->deriv_dest_type) === 'Unidad de Auditoría Interna')>Unidad de Auditoría Interna</option>
              <option value="Órgano o ente con competencia" @selected(old('deriv_dest_type', $e->deriv_dest_type) === 'Órgano o ente con competencia')>Órgano o ente con competencia</option>
            </select>
            @error('deriv_dest_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          {{-- 3a. Direcciones o departamentos de la Contraloría del Municipio Páez --}}
          <div class="mb-3" id="bloque-deriv-cmp" style="display:none;">
            <label class="form-label-sm" for="deriv_unit_cmp">3. Dirección o departamento de la Contraloría del Municipio Páez</label>
            <select name="deriv_unit" id="deriv_unit_cmp" class="form-select form-select-sm" disabled>
              <option value="">Seleccione la dirección o departamento…</option>
              @foreach ($unidades as $un)
                <option value="{{ $un->name }}" @selected(old('deriv_unit', $e->deriv_unit) === $un->name)>{{ $un->name }}</option>
              @endforeach
            </select>
          </div>

          {{-- 3b. Unidad interna de la Contraloría (al elegir Unidad de Auditoría Interna) --}}
          <div class="mb-3" id="bloque-deriv-uai" style="display:none;">
            <label class="form-label-sm" for="deriv_unit_uai">3. Unidad interna de la Contraloría</label>
            <select name="deriv_unit" id="deriv_unit_uai" class="form-select form-select-sm" disabled>
              <option value="">Seleccione la unidad interna…</option>
              @foreach ($unidadesInternasContraloria as $uInterna)
                <option value="{{ $uInterna }}" @selected(old('deriv_unit', $e->deriv_unit) === $uInterna)>{{ $uInterna }}</option>
              @endforeach
            </select>
          </div>

          {{-- 3c. Órgano o ente con competencia (solo para Órgano o ente con competencia) --}}
          <div class="mb-3" id="bloque-deriv-ente-externo" style="display:none;">
            <label class="form-label-sm" for="deriv_external_entity">3. Órgano o ente con competencia (o detalle de la instancia)</label>
            <input type="text" name="deriv_external_entity" id="deriv_external_entity" class="form-control form-control-sm" maxlength="500"
              placeholder="Ej. Órgano o ente con competencia (hidrológica, alcaldía, ente prestador del servicio…)"
              value="{{ old('deriv_external_entity', $e->deriv_external_entity) }}">
            <div class="form-text texto-ajustable">Indique el órgano, ente o prestador del servicio con competencia según la materia del caso.</div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 min-w-0">
              <label class="form-label-sm" for="deriv_office_number">4. Número de oficio de remisión</label>
              <input type="text" name="deriv_office_number" id="deriv_office_number" class="form-control form-control-sm" maxlength="80"
                placeholder="Ej. CMP-DAC-OF-001-2026"
                value="{{ old('deriv_office_number', $e->deriv_office_number) }}">
            </div>
            <div class="col-12 col-sm-6 min-w-0">
              <label class="form-label-sm" for="deriv_office_date">Fecha de remisión</label>
              <input type="date" name="deriv_office_date" id="deriv_office_date" class="form-control form-control-sm"
                max="{{ now()->format('Y-m-d') }}"
                value="{{ old('deriv_office_date', $e->deriv_office_date ? \Illuminate\Support\Carbon::parse($e->deriv_office_date)->format('Y-m-d') : '') }}">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label-sm" for="deriv_letter_url">5. Enlace o soporte del oficio de derivación</label>
            <input type="url" name="deriv_letter_url" id="deriv_letter_url" class="form-control form-control-sm @error('deriv_letter_url') is-invalid @enderror"
              maxlength="500" placeholder="https://…"
              value="{{ old('deriv_letter_url', $e->deriv_letter_url) }}">
            @error('deriv_letter_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="form-label-sm" for="deriv_fundamento">6. Fundamento de valoración / Norma o criterio</label>
            <textarea name="deriv_fundamento" id="deriv_fundamento" class="form-control form-control-sm" rows="2" maxlength="3000"
              placeholder="Encuadre jurídico o razón de competencia/incompetencia…">{{ old('deriv_fundamento', $e->deriv_fundamento) }}</textarea>
          </div>

          <div class="mb-3">
            <label class="form-label-sm">7. Recaudos anexos y resguardo en archivo DAC</label>
            <div class="border rounded p-3 bg-white checklist-deriv">
              @php
                $recaudosGuardados = is_array($e->deriv_recaudos) ? $e->deriv_recaudos : (json_decode($e->deriv_recaudos ?? '[]', true) ?: []);
                $recaudosOpts = [
                  'acta_recepcion'      => 'Constancia / Acta de recepción',
                  'copia_certificada'   => 'Soporte documental / Copia certificada',
                  'informe_valoracion'  => 'Informe de valoración',
                  'nro_folios'          => 'Número de folios consignado',
                  'copia_archivo_dac'   => 'Copia certificada queda en archivo de la DAC',
                ];
              @endphp
              @foreach ($recaudosOpts as $key => $label)
                <div class="form-check mb-1">
                  <input class="form-check-input" type="checkbox" name="deriv_recaudos[]" id="rec_{{ $key }}" value="{{ $key }}"
                    @checked(in_array($key, old('deriv_recaudos', $recaudosGuardados) ?? []))>
                  <label class="form-check-label" for="rec_{{ $key }}">{{ $label }}</label>
                </div>
              @endforeach
            </div>
          </div>

          {{-- 8. Aviso / notificación previa al solicitante --}}
          <div class="mb-3">
            <label class="form-label-sm">8. Aviso / notificación al solicitante sobre la remisión</label>
            <div class="border rounded p-3 bg-white">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="deriv_citizen_notified" id="deriv_citizen_notified" value="1"
                  @checked(old('deriv_citizen_notified', $e->deriv_citizen_notified)) onchange="toggleDerivNotif(this)">
                <label class="form-label-sm mb-0 fw-semibold" for="deriv_citizen_notified">
                  Ya se realizó un aviso previo al ciudadano/a sobre esta remisión (si aún no se ha hecho, envíelo abajo en <em>Respuesta / Notificación formal al ciudadano</em>).
                </label>
              </div>
              <div id="bloque-notif-fecha" style="{{ (old('deriv_citizen_notified', $e->deriv_citizen_notified) ? '' : 'display:none;') }}">
                <div class="row g-2">
                  <div class="col-12 col-sm-6 min-w-0">
                    <label class="form-label-sm" for="deriv_citizen_notified_at">Fecha del aviso previo</label>
                    <input type="date" name="deriv_citizen_notified_at" id="deriv_citizen_notified_at" class="form-control form-control-sm"
                      max="{{ now()->format('Y-m-d') }}"
                      value="{{ old('deriv_citizen_notified_at', $e->deriv_citizen_notified_at ? \Illuminate\Support\Carbon::parse($e->deriv_citizen_notified_at)->format('Y-m-d') : '') }}">
                  </div>
                  <div class="col-12 col-sm-6 min-w-0">
                    <label class="form-label-sm" for="deriv_citizen_notified_ref">Referencia o N° de oficio / constancia</label>
                    <input type="text" name="deriv_citizen_notified_ref" id="deriv_citizen_notified_ref" class="form-control form-control-sm"
                      maxlength="200" placeholder="Ej. CMP-DAC-NOT-001-2026"
                      value="{{ old('deriv_citizen_notified_ref', $e->deriv_citizen_notified_ref) }}">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label-sm" for="nota_derivacion">9. Observación / Motivo de la remisión <span class="req">*</span></label>
            <textarea name="nota" id="nota_derivacion" class="form-control form-control-sm @error('nota') is-invalid @enderror"
              rows="3" minlength="5" maxlength="2000" required
              placeholder="Detalle de la instrucción o motivo de la remisión…">{{ old('nota') }}</textarea>
            @error('nota') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <button type="submit" class="btn btn-cgp fw-bold w-100">Guardar Derivación</button>
        </form>

        <hr class="my-4">

        {{-- Sub-bloque B: Respuesta / Notificación formal al ciudadano --}}
        <div class="fw-bold small text-uppercase text-muted mb-2">Respuesta / Notificación formal al ciudadano</div>
        <p class="text-muted small mb-3 texto-ajustable">Esta comunicación quedará registrada para consulta del ciudadano con su código de expediente.</p>

        @if ($respuestasCiudadano->isNotEmpty())
          <div class="mb-4">
            <div class="fw-bold small text-uppercase text-muted mb-2">Respuestas emitidas anteriormente</div>
            @foreach ($respuestasCiudadano as $rc)
              <div class="border rounded p-3 mb-2 bg-light">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 mb-1">
                  <span class="fw-semibold texto-ajustable">{{ $rc->response_type }}</span>
                  <span class="text-muted small">{{ $fechaCorta($rc->response_date) }}</span>
                </div>
                <div class="texto-ajustable" style="white-space:pre-wrap; font-size:0.9rem;">{{ $rc->response_text }}</div>
                <div class="text-muted small mt-2 border-top pt-2 texto-ajustable">{{ $rc->issued_by_name }} — {{ $rc->issued_by_cargo }}</div>
              </div>
            @endforeach
          </div>
        @endif

        <form method="POST" action="{{ route('oac.expedientes.respuestaCiudadana', $e) }}" id="form-respuesta">
          @csrf
          <input type="hidden" name="_seccion_activa" value="seccion-remision-notificacion">

          <div class="ficha-asignacion mb-3">
            <div class="fw-semibold small mb-1">Datos del expediente</div>
            <div class="row g-2 small">
              <div class="col-12 col-sm-4 min-w-0 texto-ajustable"><span class="text-muted">Expediente:</span> <strong>{{ $e->case_number }}</strong></div>
              <div class="col-12 col-sm-4 min-w-0 texto-ajustable"><span class="text-muted">Trámite:</span> <strong>{{ $e->claimType->name }}</strong></div>
              <div class="col-12 col-sm-4 min-w-0 texto-ajustable"><span class="text-muted">Ciudadano:</span> <strong>{{ $c->first_name }} {{ $c->last_name }}</strong> — C.I. {{ ($docTipo === 'PASSPORT' ? 'P' : $docTipo).'-'.$c->id_doc_number }}</div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label-sm" for="response_type">Tipo de respuesta / Estatus del trámite <span class="req">*</span></label>
            <select name="response_type" id="response_type" class="form-select form-select-sm @error('response_type') is-invalid @enderror" required onchange="actualizarCamposRespuesta(this.value)">
              <option value="">Seleccione el tipo de respuesta…</option>
              <option value="Fase de análisis y valoración"            @selected(old('response_type') === 'Fase de análisis y valoración')>En tramitación / valoración</option>
              <option value="Fase de subsanación (requisitos pendientes)" @selected(old('response_type') === 'Fase de subsanación (requisitos pendientes)')>En subsanación (5 días hábiles)</option>
              <option value="Remisión a otro órgano o ente competente" @selected(old('response_type') === 'Remisión a otro órgano o ente competente')>Remitido / derivado</option>
              <option value="Notificación de improcedencia y archivo"  @selected(old('response_type') === 'Notificación de improcedencia y archivo')>Improcedente / desestimado y archivado</option>
              <option value="Decisión / Resultado de la gestión"       @selected(old('response_type') === 'Decisión / Resultado de la gestión')>Cerrado / archivado</option>
              <option value="Trámite en gestión (seguimiento)"         @selected(old('response_type') === 'Trámite en gestión (seguimiento)')>Trámite en gestión (seguimiento)</option>
            </select>
            @error('response_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 min-w-0">
              <label class="form-label-sm" for="response_date">Fecha de la comunicación <span class="req">*</span></label>
              <input type="date" name="response_date" id="response_date" class="form-control form-control-sm @error('response_date') is-invalid @enderror"
                required max="{{ now()->format('Y-m-d') }}"
                value="{{ old('response_date', now()->format('Y-m-d')) }}">
              @error('response_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>

          <div class="mb-3" id="bloque-resp-competencia" style="display:none;">
            <label class="form-label-sm">¿El trámite es competencia de la Contraloría del Municipio Páez?</label>
            <div class="d-flex flex-wrap gap-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="is_competencia" id="comp_si" value="si" @checked(old('is_competencia') === 'si')>
                <label class="form-check-label" for="comp_si">Sí, es competencia</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="is_competencia" id="comp_no" value="no" @checked(old('is_competencia') === 'no')>
                <label class="form-check-label" for="comp_no">No es competencia</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="is_competencia" id="comp_na" value="na" @checked(old('is_competencia', 'na') === 'na')>
                <label class="form-check-label" for="comp_na">No aplica</label>
              </div>
            </div>
          </div>

          <div class="mb-3" id="bloque-resp-remision" style="display:none;">
            <label class="form-label-sm" for="referred_entity">Órgano o ente con competencia al que se remite el caso</label>
            <input type="text" name="referred_entity" id="referred_entity" class="form-control form-control-sm"
              maxlength="500" placeholder="Ej. Órgano o ente con competencia, CGR, UAI…"
              value="{{ old('referred_entity') }}">
          </div>

          <div class="mb-3">
            <label class="form-label-sm" for="legal_basis">Motivo o fundamento de la decisión</label>
            <textarea name="legal_basis" id="legal_basis" class="form-control form-control-sm" rows="2" maxlength="1000"
              placeholder="Ej. omisión de requisitos mínimos del Oficio Circular N° 01-00-499, falta de competencia fiscal…">{{ old('legal_basis') }}</textarea>
          </div>

          @if ($e->claimType->code === 'COMPLAINT')
          <div class="mb-3" id="bloque-resp-circular">
            <label class="form-label-sm" for="office_circular">Referencia de la Circular aplicable <span class="text-muted">(opcional)</span></label>
            <input type="text" name="office_circular" id="office_circular" class="form-control form-control-sm"
              maxlength="200" placeholder="Ej. Oficio Circular N° 01-00-499 del 31/07/2025"
              value="{{ old('office_circular') }}">
          </div>
          <div class="mb-3" id="bloque-resp-subsanacion">
            <label class="form-label-sm" for="subsanation_days">Días hábiles para subsanación <span class="text-muted">(si aplica)</span></label>
            <input type="number" name="subsanation_days" id="subsanation_days" class="form-control form-control-sm"
              min="1" max="30" placeholder="5"
              value="{{ old('subsanation_days') }}">
            <div class="form-text texto-ajustable">El manual prevé 5 días hábiles para subsanar en denuncias/peticiones.</div>
          </div>
          @endif

          @if ($e->claimType->code === 'SUGGESTION')
          <div class="mb-3">
            <label class="form-label-sm" for="viabilidad_result">Resultado de la evaluación de viabilidad</label>
            <textarea name="viabilidad_result" id="viabilidad_result" class="form-control form-control-sm" rows="2" maxlength="1000"
              placeholder="Resultado de la evaluación por la unidad técnica correspondiente…">{{ old('viabilidad_result') }}</textarea>
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="informed_max_authority" id="informed_max_authority" value="1"
                @checked(old('informed_max_authority'))>
              <label class="form-check-label form-label-sm" for="informed_max_authority">
                Se informó a la Contralora Municipal para posible inclusión en planes de mejora.
              </label>
            </div>
          </div>
          @endif

          <div class="mb-3">
            <label class="form-label-sm" for="response_text">Mensaje de respuesta al ciudadano <span class="req">*</span></label>
            <textarea name="response_text" id="response_text" class="form-control form-control-sm @error('response_text') is-invalid @enderror"
              rows="5" minlength="10" maxlength="5000" required
              placeholder="Redacte el mensaje que recibirá el ciudadano al consultar el estado de su trámite…">{{ old('response_text') }}</textarea>
            @error('response_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 min-w-0">
              <label class="form-label-sm" for="issued_by_name">Nombre del firmante <span class="req">*</span></label>
              <input type="text" name="issued_by_name" id="issued_by_name" class="form-control form-control-sm @error('issued_by_name') is-invalid @enderror"
                maxlength="200" required
                value="{{ old('issued_by_name', auth()->user()->first_name.' '.auth()->user()->last_name) }}">
              @error('issued_by_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-sm-6 min-w-0">
              <label class="form-label-sm" for="issued_by_cargo">Cargo del firmante <span class="req">*</span></label>
              <input type="text" name="issued_by_cargo" id="issued_by_cargo" class="form-control form-control-sm @error('issued_by_cargo') is-invalid @enderror"
                maxlength="200" required
                placeholder="Ej. Director(a) de la DAC, Contralora Municipal"
                value="{{ old('issued_by_cargo') }}">
              @error('issued_by_cargo') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>

          <button type="submit" class="btn btn-cgp fw-bold w-100">Emitir Respuesta Ciudadana</button>
        </form>

      </div>
    </div>
    @endif

  </div>{{-- /col-xl-9 --}}

  {{-- ══════════════════════════════════════════════════
       FICHA RESUMEN (COLUMNA LATERAL)
  ══════════════════════════════════════════════════ --}}
  <div class="col-12 col-xl-3 min-w-0">
    <div class="card-seccion mb-4">
      <div class="card-seccion-header">
        <div class="seccion-titulo">{{ $e->case_number }}</div>
        <p class="seccion-subtitulo">Fecha de ingreso: {{ $fechaCorta($e->created_at) }}</p>
      </div>
      <div class="card-seccion-body">
        <div class="mb-3">
          <div class="dato-label mb-1">Fase / Estado actual</div>
          <span class="badge-fase-estado bg-light text-dark border st-{{ $actual->code }}">{{ $etiquetasFaseEstado[$actual->code] ?? $actual->name }}</span>
        </div>
        <div class="mb-3">
          <div class="dato-label">Mecanismo</div>
          <div class="fw-semibold small text-dark texto-ajustable">{{ $e->claimType->name }}</div>
        </div>
        @if ($irregularidad)
          <div class="mb-3">
            <div class="dato-label">Clasificación</div>
            <div class="fw-semibold small text-dark texto-ajustable">{{ $irregularidad }}</div>
          </div>
        @endif
        <div class="pt-2 border-top">
          <a href="{{ route('denuncias.planilla', $e->tracking_code) }}" class="btn btn-sm btn-outline-dark w-100 ver-planilla" data-titulo="{{ $e->case_number }}">Ver Planilla PDF</a>
        </div>
      </div>
    </div>
  </div>{{-- /col-xl-3 --}}
</div>{{-- /row --}}

@include('oac._modal_planilla')
@endsection

@push('scripts')
<script>
  const accion = document.getElementById('accion');
  if (accion) {
    const ayudaAccion = document.getElementById('ayuda-accion-manual');
    const bloqueAvisoCierre = document.getElementById('bloque-aviso-cierre');
    const selectUnidad = document.querySelector('[name=referral_unit_id]');

    const ayudasPorValor = {
      '': 'Asienta una actuación o nota cronológica conservando el estado actual del expediente.',
      'PROCESSING': 'Cuando el caso fue recibido y está siendo revisado en admisibilidad, requisitos y competencia.',
      'IN_REVIEW': 'Cuando el caso fue recibido y está siendo revisado en admisibilidad, requisitos y competencia.',
      'SUBSANATION': 'Cuando faltan recaudos o requisitos mínimos (como los criterios del Oficio Circular N° 01-00-499) y corre el lapso de 5 días hábiles para que el solicitante subsane.',
      'REFERRED': 'Cuando se traslada a una dirección/unidad interna o a un órgano o ente con competencia (por ejemplo, cuando no es competencia fiscal de la Contraloría del Municipio Páez).',
      'INVALIDATED': 'Cuando no procede o no se subsana en tiempo, resolviéndose su inadmisión/improcedencia y archivo.',
      'ARCHIVED': 'Cuando concluye el trámite mediante un auto de cierre y archivo, el cual debe estar motivado, contar con la revisión/visto bueno de la DAC y la aprobación de la máxima autoridad (Contralor/a Municipal).'
    };

    const mostrarUnidad = () => {
      const derivar = accion.value === 'REFERRED';
      const esCierre = ['ARCHIVED', 'INVALIDATED'].includes(accion.value);
      const bloqueLapso = document.getElementById('bloque-aviso-lapso');

      if (bloqueLapso) bloqueLapso.style.display = (['PROCESSING', 'IN_REVIEW'].includes(accion.value)) ? 'block' : 'none';
      document.getElementById('bloque-unidad').hidden = !derivar;
      document.getElementById('bloque-oficio').hidden = !derivar;
      if (bloqueAvisoCierre) bloqueAvisoCierre.hidden = !esCierre;
      if (selectUnidad) selectUnidad.required = derivar;
      if (ayudaAccion) {
        ayudaAccion.textContent = ayudasPorValor[accion.value] ?? 'Asienta la actuación en el expediente.';
      }
    };

    accion.addEventListener('change', mostrarUnidad);
    mostrarUnidad();

    accion.form.addEventListener('submit', ev => {
      if (['ARCHIVED','INVALIDATED'].includes(accion.value) && !confirm('Esta acción cierra o archiva el expediente y no se puede deshacer. ¿Continuar?')) {
        ev.preventDefault();
        ev.stopImmediatePropagation();
      }
    }, true);
  }

  const derivDestType = document.getElementById('deriv_dest_type');
  if (derivDestType) {
    const bloqueCmp     = document.getElementById('bloque-deriv-cmp');
    const bloqueUai     = document.getElementById('bloque-deriv-uai');
    const bloqueExterno = document.getElementById('bloque-deriv-ente-externo');

    const selectCmp     = document.getElementById('deriv_unit_cmp');
    const selectUai     = document.getElementById('deriv_unit_uai');
    const inputExterno  = document.getElementById('deriv_external_entity');

    const actualizarDestino = () => {
      const v = derivDestType.value;
      const esCmp     = v === 'Contraloría del Municipio Páez';
      const esUai     = v === 'Unidad de Auditoría Interna';
      const esExterno = v === 'Órgano o ente con competencia';

      bloqueCmp.style.display     = esCmp ? '' : 'none';
      bloqueUai.style.display     = esUai ? '' : 'none';
      bloqueExterno.style.display = esExterno ? '' : 'none';

      if (selectCmp) {
        selectCmp.disabled = !esCmp;
        selectCmp.required = esCmp;
      }
      if (selectUai) {
        selectUai.disabled = !esUai;
        selectUai.required = esUai;
      }
      if (inputExterno) {
        inputExterno.required = esExterno;
      }
    };

    derivDestType.addEventListener('change', actualizarDestino);
    actualizarDestino();
  }

  function toggleDerivNotif(cb) {
    const bloque = document.getElementById('bloque-notif-fecha');
    if (bloque) bloque.style.display = cb.checked ? '' : 'none';
  }

  function actualizarCamposRespuesta(tipo) {
    const bloqueComp     = document.getElementById('bloque-resp-competencia');
    const bloqueRemision = document.getElementById('bloque-resp-remision');

    const tiposConComp = [
      'Notificación de improcedencia y archivo',
      'Remisión a otro órgano o ente competente',
      'Decisión / Resultado de la gestión',
      'Trámite en gestión (seguimiento)',
    ];
    const tiposConRemision = ['Remisión a otro órgano o ente competente'];

    if (bloqueComp)     bloqueComp.style.display     = tiposConComp.includes(tipo)     ? '' : 'none';
    if (bloqueRemision) bloqueRemision.style.display = tiposConRemision.includes(tipo) ? '' : 'none';
  }

  const selectResponseType = document.getElementById('response_type');
  if (selectResponseType) {
    actualizarCamposRespuesta(selectResponseType.value);
  }

  const botonesSeccion = document.querySelectorAll('.barra-acciones .btn-accion[data-target]');
  const panelesSeccion = document.querySelectorAll('.seccion-expediente');

  function activarSeccion(seccionId, actualizarHash = true) {
    const panelObjetivo = document.getElementById(seccionId);
    if (!panelObjetivo) return;

    panelesSeccion.forEach(panel => {
      panel.classList.toggle('d-none', panel.id !== seccionId);
    });

    botonesSeccion.forEach(btn => {
      const esActivo = btn.dataset.target === seccionId;
      btn.classList.toggle('activo', esActivo);
      btn.setAttribute('aria-selected', esActivo ? 'true' : 'false');
    });

    if (actualizarHash && history.replaceState) {
      history.replaceState(null, '', '#' + seccionId);
    }
  }

  botonesSeccion.forEach(btn => {
    btn.addEventListener('click', () => {
      activarSeccion(btn.dataset.target, true);
    });
  });

  const tieneErroresServidor = {{ $errors->any() ? 'true' : 'false' }};
  const seccionDesdeServidor = @json($seccionActiva);
  const hashInicial = window.location.hash ? window.location.hash.substring(1) : '';

  if (tieneErroresServidor) {
    activarSeccion(seccionDesdeServidor, true);
  } else if (hashInicial && document.getElementById(hashInicial)) {
    activarSeccion(hashInicial, false);
  }
</script>
@endpush