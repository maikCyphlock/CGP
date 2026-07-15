<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseFile extends Model
{
    use HasFactory;

    protected $table = 'case_file';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'case_number',
        'tracking_code',
        'claim_type_id',
        'status_id',
        'citizen_id',
        'narrative',
        'incident_date',
        'incident_location',
        'other_instance',
        'other_instance_name',
        'is_popular_consultation',
        'sworn_declaration',
        'declaration_date',
        'protocolized_at',
        'qr_payload',
        'receipt_pdf_url',
        'physical_docs_deadline',
        'physical_docs_submitted',
        'physical_docs_date',
        'physical_file_number',
        'analyst_notes',
        'irregularity_type_id',
        'referral_unit_id',
        'referred_at',
        'referred_by',
        'referral_letter_url',
        'sigece_reference',
        'received_by',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'other_instance' => 'boolean',
        'is_popular_consultation' => 'boolean',
        'sworn_declaration' => 'boolean',
        'declaration_date' => 'datetime',
        'protocolized_at' => 'datetime',
        'physical_docs_deadline' => 'date',
        'physical_docs_submitted' => 'boolean',
        'physical_docs_date' => 'date',
        'referred_at' => 'datetime',
    ];

    public function citizen()
    {
        return $this->belongsTo(Citizen::class, 'citizen_id');
    }

    public function claimType()
    {
        return $this->belongsTo(ClaimType::class, 'claim_type_id');
    }

    public function respondents()
    {
        return $this->hasMany(Respondent::class, 'case_file_id');
    }
}
