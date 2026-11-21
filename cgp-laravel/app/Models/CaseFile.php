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
        'assigned_unit_id',
        'assigned_position_id',
        'assigned_user_id',
        'assigned_at',
        'assigned_by',
        'assignment_notes',
        'deriv_scope',
        'deriv_dest_type',
        'deriv_unit',
        'deriv_external_entity',
        'deriv_office_number',
        'deriv_office_date',
        'deriv_letter_url',
        'deriv_fundamento',
        'deriv_recaudos',
        'deriv_citizen_notified',
        'deriv_citizen_notified_at',
        'deriv_citizen_notified_ref',
        'incident_lat',
        'incident_lng',
        'data_confirmation_days',
        'data_confirmation_deadline',
        'data_confirmed_at',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'incident_lat' => 'decimal:7',
        'incident_lng' => 'decimal:7',
        'data_confirmation_days' => 'integer',
        'data_confirmation_deadline' => 'date',
        'data_confirmed_at' => 'datetime',
        'other_instance' => 'boolean',
        'is_popular_consultation' => 'boolean',
        'sworn_declaration' => 'boolean',
        'declaration_date' => 'datetime',
        'protocolized_at' => 'datetime',
        'physical_docs_deadline' => 'date',
        'physical_docs_submitted' => 'boolean',
        'physical_docs_date' => 'date',
        'referred_at' => 'datetime',
        'assigned_at' => 'datetime',
        'deriv_office_date' => 'date',
        'deriv_citizen_notified' => 'boolean',
        'deriv_citizen_notified_at' => 'date',
        'deriv_recaudos' => 'array',
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

    public function assignedUser()
    {
        return $this->belongsTo(StaffUser::class, 'assigned_user_id');
    }
}
