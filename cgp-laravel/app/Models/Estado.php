<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estado extends Model
{
    protected $table = 'estados';

    protected $fillable = ['nombre'];

    public function municipios(): HasMany
    {
        // orderBy('id') conserva el orden original del catálogo
        return $this->hasMany(Municipio::class)->orderBy('id');
    }
}