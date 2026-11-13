<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Profesion extends Model
{
    public const NINGUNA = 'Ninguna';

    protected $table = 'profesiones';

    protected $fillable = ['nombre', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /**
     * Nombres para el menú desplegable: "Ninguna" primero y el resto
     * en orden alfabético sin distinguir tildes ni mayúsculas.
     *
     * @return string[]
     */
    public static function nombresParaSelect(): array
    {
        $nombres = static::activas()->pluck('nombre')->all();

        usort($nombres, fn ($a, $b) => strcmp(
            Str::ascii(mb_strtolower($a)),
            Str::ascii(mb_strtolower($b))
        ));

        if (in_array(self::NINGUNA, $nombres, true)) {
            $nombres = array_merge(
                [self::NINGUNA],
                array_values(array_diff($nombres, [self::NINGUNA]))
            );
        }

        return $nombres;
    }
}
