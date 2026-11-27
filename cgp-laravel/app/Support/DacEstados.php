<?php

namespace App\Support;

/**
 * Definición centralizada de los estados del flujo OAC/DAC según el Manual de la
 * Dirección de Atención al Ciudadano de la Contraloría del Municipio Páez.
 */
final class DacEstados
{
    public const TRANSICIONES = [
        'RECEIVED'    => ['PROCESSING', 'INVALIDATED'],
        'PROCESSING'  => ['SUBSANATION', 'REFERRED', 'INVALIDATED', 'ARCHIVED'],
        'SUBSANATION' => ['PROCESSING', 'REFERRED', 'INVALIDATED', 'ARCHIVED'],
        'REFERRED'    => ['PROCESSING', 'ARCHIVED'],
        // Compatibilidad con expedientes creados antes de la migración DAC
        'IN_REVIEW'   => ['PROCESSING', 'SUBSANATION', 'REFERRED', 'INVALIDATED', 'ARCHIVED'],
        'PROCESSED'   => ['PROCESSING', 'SUBSANATION', 'REFERRED', 'INVALIDATED', 'ARCHIVED'],
    ];

    public const ACCIONES = [
        'PROCESSING'  => 'En tramitación / valoración',
        'SUBSANATION' => 'En subsanación',
        'REFERRED'    => 'Remitido / derivado',
        'INVALIDATED' => 'Improcedente / desestimado y archivado',
        'ARCHIVED'    => 'Cerrado / archivado',
    ];

    public const ETIQUETAS = [
        'RECEIVED'    => 'Recibido',
        'PROCESSING'  => 'En tramitación / valoración',
        'SUBSANATION' => 'En subsanación',
        'REFERRED'    => 'Remitido / derivado',
        'INVALIDATED' => 'Improcedente / desestimado y archivado',
        'ARCHIVED'    => 'Cerrado / archivado',
        'IN_REVIEW'   => 'En Revisión',
        'PROCESSED'   => 'Procesando',
    ];

    public const DESCRIPCIONES = [
        'RECEIVED'    => 'Ingreso inicial por el portal público; en espera de validación de datos y protocolización.',
        'PROCESSING'  => 'Se recibió y se revisa admisibilidad, requisitos y competencia.',
        'SUBSANATION' => 'Faltan recaudos (Oficio Circular N° 01-00-499); el solicitante tiene 5 días hábiles para completarlos.',
        'REFERRED'    => 'Se pasa a una unidad interna o a otro órgano o ente competente.',
        'INVALIDATED' => 'No procede, o no se subsanó a tiempo: inadmisión y archivo.',
        'ARCHIVED'    => 'Auto de cierre motivado, con visto bueno de la DAC y aprobación del Contralor/a Municipal.',
    ];

    public const MENSAJES_POR_DEFECTO = [
        'RECEIVED' => [
            'titulo' => 'Solicitud recibida',
            'mensaje' => 'Su solicitud ha sido recibida en el sistema de la Oficina de Atención al Ciudadano (OAC). Dispone del lapso de anticipación indicado para confirmar o rectificar sus datos antes de su formalización.',
        ],
        'PROCESSING' => [
            'titulo' => 'En tramitación y valoración',
            'mensaje' => 'Su trámite se encuentra en revisión de admisibilidad, requisitos y competencia ante la Dirección de Atención al Ciudadano (DAC). En caso de haber presentado denuncia, queja, reclamo o petición, recuerde consignar los soportes físicos en la sede de la Contraloría.',
        ],
        'SUBSANATION' => [
            'titulo' => 'En lapso de subsanación',
            'mensaje' => 'Faltan recaudos o requisitos mínimos según el Oficio Circular N° 01-00-499. Dispone de cinco (5) días hábiles para completarlos y consignarlos ante la Dirección de Atención al Ciudadano.',
        ],
        'REFERRED' => [
            'titulo' => 'Expediente remitido / derivado',
            'mensaje' => 'Su expediente fue remitido a la unidad interna correspondiente o al órgano/ente competente para su atención y seguimiento conforme a la ley.',
        ],
        'INVALIDATED' => [
            'titulo' => 'Trámite improcedente / desestimado y archivado',
            'mensaje' => 'Luego del análisis de admisibilidad y valoración, el trámite fue declarado improcedente o desestimado y archivado conforme a la normativa vigente.',
        ],
        'ARCHIVED' => [
            'titulo' => 'Trámite cerrado / archivado',
            'mensaje' => 'Su trámite ha concluido formalmente mediante auto de cierre motivado, con visto bueno de la Dirección de Atención al Ciudadano y aprobación del Contralor(a) Municipal.',
        ],
    ];

    public const ENTES_REMISION = [
        'Contraloría del Municipio Páez',
        'Unidad de Auditoría Interna',
        'Contraloría General de la República Bolivariana de Venezuela (CGR)',
        'Órgano o ente con competencia',
    ];

    public const CAUSALES_IMPROCEDENCIA = [
        'No subsanó requisitos en el lapso de 5 días hábiles',
        'Falta de competencia fiscal de la Contraloría del Municipio Páez',
        'Omisión de requisitos mínimos (Oficio Circular N° 01-00-499)',
        'Hechos manifiestamente infundados o sin elementos de convicción',
        'Duplicidad o cosa juzgada administrativa',
        'Otra causal motivada',
    ];

    public static function nombre(string $codigo, ?string $fallback = null): string
    {
        return self::ETIQUETAS[$codigo] ?? ($fallback ?? $codigo);
    }

    /** Suma días hábiles (lunes a viernes) a partir de la fecha indicada. */
    public static function sumarDiasHabiles(\Carbon\CarbonInterface $fecha, int $dias = 5): \Illuminate\Support\Carbon
    {
        $cursor = \Illuminate\Support\Carbon::instance($fecha)->copy();
        $restantes = max(0, $dias);

        while ($restantes > 0) {
            $cursor->addDay();
            if (! $cursor->isWeekend()) {
                $restantes--;
            }
        }

        return $cursor;
    }

    /** Cuenta días hábiles (lunes a viernes) restantes entre hoy y la fecha límite. */
    public static function diasHabilesRestantes(\Carbon\CarbonInterface $limite): int
    {
        $hoy = now()->startOfDay();
        $fin = \Illuminate\Support\Carbon::instance($limite)->startOfDay();
        if ($fin->lt($hoy)) {
            return 0;
        }

        $dias = 0;
        $cursor = $hoy->copy();
        while ($cursor->lt($fin)) {
            $cursor->addDay();
            if (! $cursor->isWeekend()) {
                $dias++;
            }
        }

        return $dias;
    }
}
