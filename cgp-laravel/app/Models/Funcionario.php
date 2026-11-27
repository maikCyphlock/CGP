<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    protected $table = 'funcionarios';

    protected $fillable = [
        'area_direccion',
        'cargo',
        'figura',
        'nombre_completo',
        'cedula',
        'referral_unit_id',
        'job_position_id',
        'staff_user_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function staffUser()
    {
        return $this->belongsTo(StaffUser::class, 'staff_user_id');
    }
}
