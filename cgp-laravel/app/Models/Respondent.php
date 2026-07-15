<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Respondent extends Model
{
    use HasFactory;

    protected $table = 'respondent';

    // No timestamps in database schema for respondent, only created_at
    const UPDATED_AT = null;

    protected $fillable = [
        'case_file_id',
        'respondent_type_id',
        'location',
        'attributes',
    ];

    protected $casts = [
        'attributes' => 'array',
    ];

    public function caseFile()
    {
        return $this->belongsTo(CaseFile::class, 'case_file_id');
    }

    public function respondentType()
    {
        return $this->belongsTo(RespondentType::class, 'respondent_type_id');
    }
}
