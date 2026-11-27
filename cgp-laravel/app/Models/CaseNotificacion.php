<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CaseNotificacion extends Model
{
    use HasUuids;

    protected $table = 'case_citizen_response';

    protected $fillable = [
        'case_file_id',
        'user_id',
        'remision_id',
        'response_type',
        'response_date',
        'is_competencia',
        'referred_entity',
        'legal_basis',
        'office_circular',
        'subsanation_days',
        'viabilidad_result',
        'informed_max_authority',
        'response_text',
        'issued_by_name',
        'issued_by_cargo',
    ];

    protected $casts = [
        'response_date' => 'date',
        'subsanation_days' => 'integer',
        'informed_max_authority' => 'boolean',
    ];

    public function caseFile()
    {
        return $this->belongsTo(CaseFile::class, 'case_file_id');
    }

    public function remision()
    {
        return $this->belongsTo(CaseRemision::class, 'remision_id');
    }

    public function usuario()
    {
        return $this->belongsTo(StaffUser::class, 'user_id');
    }
}
