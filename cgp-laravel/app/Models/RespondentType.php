<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RespondentType extends Model
{
    use HasFactory;

    protected $table = 'respondent_type';

    protected $fillable = [
        'code',
        'name',
        'field_schema',
        'active',
    ];

    protected $casts = [
        'field_schema' => 'array',
        'active' => 'boolean',
    ];

    public function respondents()
    {
        return $this->hasMany(Respondent::class, 'respondent_type_id');
    }
}
