<?php

namespace App\Http\Controllers\Oac;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Reportes gerenciales de la OAC: totales, cierre, distribución por tipo/estatus/irregularidad y soportes vencidos. */
class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $f = $request->validate(['desde' => 'nullable|date', 'hasta' => 'nullable|date|after_or_equal:desde']);
        $desde = $f['desde'] ?? now()->startOfYear()->toDateString();
        $hasta = $f['hasta'] ?? now()->toDateString();

        // Expedientes creados en el rango (el día final se incluye completo).
        $base = fn () => DB::table('case_file as c')->where('c.created_at', '>=', $desde)->where('c.created_at', '<', date('Y-m-d', strtotime("$hasta +1 day")));
        $cuenta = fn ($tabla, $fk, $campo) => $base()->join("$tabla as t", 't.id', '=', "c.$fk")->groupBy("t.$campo")->orderByRaw('count(*) desc')
            ->select("t.$campo", DB::raw('count(*) as n'))->pluck('n', $campo);

        $porEstatus = $base()->join('case_status as t', 't.id', '=', 'c.status_id')->groupBy('t.name', 't.code', 't.sort_order')->orderBy('t.sort_order')
            ->get(['t.name', 't.code', DB::raw('count(*) as n')]);
        $total = $porEstatus->sum('n');
        $cerrados = $porEstatus->whereIn('code', ['ARCHIVED', 'INVALIDATED'])->sum('n');

        $datos = [
            'desde' => $desde, 'hasta' => $hasta, 'total' => $total, 'cerrados' => $cerrados,
            'eficiencia' => $total ? round(100 * $cerrados / $total) : 0,
            'porEstatus' => $porEstatus,
            'porTipo' => $cuenta('claim_type', 'claim_type_id', 'name'),
            'porIrregularidad' => $base()->join('irregularity_type as t', 't.id', '=', 'c.irregularity_type_id')->groupBy('t.name')->orderByRaw('count(*) desc')->select('t.name', DB::raw('count(*) as n'))->pluck('n', 'name'),
            'sinClasificar' => $base()->whereNull('c.irregularity_type_id')->count(),
            'porMes' => $base()->selectRaw("to_char(c.created_at, 'YYYY-MM') as mes, count(*) as n")->groupBy('mes')->orderBy('mes')->pluck('n', 'mes'),
            // Soportes físicos fuera de plazo en expedientes todavía abiertos (no depende del rango).
            'vencidos' => DB::table('case_file as c')->join('case_status as s', 's.id', '=', 'c.status_id')
                ->where('s.is_terminal', false)->where('c.physical_docs_submitted', false)->where('c.physical_docs_deadline', '<', now()->toDateString())
                ->orderBy('c.physical_docs_deadline')->limit(20)->get(['c.id', 'c.case_number', 'c.physical_docs_deadline']),
        ];

        if ($request->query('formato') === 'csv') {
            return $this->csv($datos);
        }

        return view('oac.reportes', $datos);
    }

    private function csv(array $d)
    {
        return response()->streamDownload(function () use ($d) {
            $o = fopen('php://output', 'w');
            fwrite($o, "\xEF\xBB\xBF"); // BOM para que Excel respete los acentos
            fputcsv($o, ['Reporte OAC', $d['desde'], $d['hasta']], ';');
            fputcsv($o, ['Total recibidos', $d['total']], ';');
            fputcsv($o, ['Cerrados', $d['cerrados']], ';');
            fputcsv($o, ['Eficiencia de cierre %', $d['eficiencia']], ';');
            foreach (['Estatus' => $d['porEstatus']->pluck('n', 'name'), 'Tipo de trámite' => $d['porTipo'], 'Irregularidad' => $d['porIrregularidad'], 'Mes' => $d['porMes']] as $titulo => $filas) {
                fputcsv($o, [], ';');
                fputcsv($o, [$titulo, 'Cantidad'], ';');
                foreach ($filas as $k => $n) {
                    fputcsv($o, [$k, $n], ';');
                }
            }
            fclose($o);
        }, "reporte-oac-{$d['desde']}-{$d['hasta']}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
