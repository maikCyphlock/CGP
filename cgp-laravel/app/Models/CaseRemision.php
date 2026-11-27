<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CaseRemision extends Model
{
    use HasUuids;

    protected $table = 'case_remisiones';

    protected $fillable = [
        'case_file_id',
        'user_id',
        'ambito_competencia',
        'ente_destino',
        'unidad_interna',
        'ente_externo',
        'referral_unit_id',
        'numero_oficio',
        'fecha_remision',
        'oficio_url',
        'fundamento_juridico',
        'recaudos',
        'ciudadano_notificado',
        'fecha_notificacion_previa',
        'referencia_notificacion',
        'observacion',
    ];

    protected $casts = [
        'recaudos' => 'array',
        'fecha_remision' => 'date',
        'ciudadano_notificado' => 'boolean',
        'fecha_notificacion_previa' => 'date',
    ];

    public function caseFile()
    {
        return $this->belongsTo(CaseFile::class, 'case_file_id');
    }

    public function usuario()
    {
        return $this->belongsTo(StaffUser::class, 'user_id');
    }

    public function notificaciones()
    {
        return $this->hasMany(CaseNotificacion::class, 'remision_id');
    }
}
