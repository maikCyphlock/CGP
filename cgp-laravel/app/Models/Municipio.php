<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipio extends Model
{
    protected $table = 'municipios';

    protected $fillable = ['estado_id', 'nombre'];

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class);
    }

    public function parroquias(): HasMany
    {
        return $this->hasMany(Parroquia::class)->orderBy('id');
    }
}