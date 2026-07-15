<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Citizen extends Model
{
    use HasFactory;

    protected $table = 'citizen';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'id_doc_type_id',
        'id_doc_number',
        'first_name',
        'last_name',
        'sex',
        'birth_date',
        'email',
        'mobile_phone',
        'address',
        'parish',
        'municipality',
        'city',
        'contact_data',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'contact_data' => 'array',
    ];

    public function caseFiles()
    {
        return $this->hasMany(CaseFile::class, 'citizen_id');
    }
}
