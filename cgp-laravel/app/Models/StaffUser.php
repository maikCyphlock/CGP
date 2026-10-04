<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StaffUser extends Authenticatable
{
    use HasUuids;

    protected $table = 'staff_user';

    protected $fillable = [
        'job_position_id', 'id_doc_type_id', 'id_doc_number', 'first_name', 'last_name',
        'email', 'password_hash', 'active', 'failed_attempts', 'locked_until', 'last_login',
    ];

    protected $hidden = ['password_hash'];

    private ?Collection $privilegios = null;

    protected $casts = [
        'password_hash' => 'hashed',
        'active' => 'boolean',
        'locked_until' => 'datetime',
        'last_login' => 'datetime',
    ];

    // Un usuario desactivado no puede entrar y pierde la sesión que tuviera abierta.
    protected static function booted(): void
    {
        static::addGlobalScope('activo', fn ($q) => $q->where('active', true));
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    // staff_user no tiene columna remember_token.
    public function getRememberTokenName(): string
    {
        return '';
    }

    /** ¿Tiene permiso `read|write|delete` sobre el módulo (código de app_module)? */
    public function puede(string $modulo, string $accion = 'read'): bool
    {
        $this->privilegios ??= DB::table('staff_privilege as p')
            ->join('app_module as m', 'm.id', '=', 'p.module_id')
            ->where('p.user_id', $this->id)
            ->get(['m.code', 'p.can_read', 'p.can_write', 'p.can_delete'])
            ->keyBy('code');

        return (bool) ($this->privilegios[$modulo]->{"can_$accion"} ?? false);
    }

    public function getNombreAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function cargo(): ?string
    {
        return DB::table('job_position')->where('id', $this->job_position_id)->value('title');
    }
}
