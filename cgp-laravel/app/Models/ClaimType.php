<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimType extends Model
{
    use HasFactory;

    protected $table = 'claim_type';

    const UPDATED_AT = null;

    protected $fillable = [
        'code',
        'name',
        'validation_level',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function caseFiles()
    {
        return $this->hasMany(CaseFile::class, 'claim_type_id');
    }
}
