<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MensajeEstado extends Model
{
    protected $table = 'mensajes_estado';

    protected $fillable = [
        'estado_codigo',
        'titulo',
        'mensaje',
        'activo',
        'actualizado_por',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function actualizadoPor()
    {
        return $this->belongsTo(StaffUser::class, 'actualizado_por');
    }
}
