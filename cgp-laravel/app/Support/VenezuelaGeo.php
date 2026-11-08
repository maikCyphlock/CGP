<?php

namespace App\Support;

use App\Models\Estado;
use Illuminate\Support\Facades\Cache;

class VenezuelaGeo
{
    private const CACHE_KEY = 'venezuela_geo.catalogo';

    private static ?array $data = null;

    public static function all(): array
    {
        if (self::$data === null) {
            self::$data = Cache::rememberForever(self::CACHE_KEY, function () {
                $catalogo = [];

                Estado::with('municipios.parroquias')->orderBy('id')->get()
                    ->each(function (Estado $estado) use (&$catalogo) {
                        foreach ($estado->municipios as $municipio) {
                            $catalogo[$estado->nombre][$municipio->nombre] =
                                $municipio->parroquias->pluck('nombre')->all();
                        }
                    });

                return $catalogo;
            });
        }

        return self::$data;
    }

    public static function estados(): array
    {
        return array_keys(self::all());
    }

    public static function municipios(string $estado): array
    {
        return array_keys(self::all()[$estado] ?? []);
    }

    public static function parroquias(string $estado, string $municipio): array
    {
        return self::all()[$estado][$municipio] ?? [];
    }

    /**
     * Valida si la combinación Estado / Municipio / Parroquia es válida geográficamente.
     */
    public static function esValido(?string $estado, ?string $municipio, ?string $parroquia = null): bool
    {
        if (empty($estado)) {
            return false;
        }

        if (strtoupper($estado) === 'EXTRANJERO' || stripos($estado, 'Extranjero') !== false) {
            return true;
        }

        $data = self::all();
        if (! isset($data[$estado])) {
            return false;
        }

        if (empty($municipio)) {
            return false;
        }

        if (! isset($data[$estado][$municipio])) {
            return false;
        }

        if ($parroquia !== null && $parroquia !== '') {
            return in_array($parroquia, $data[$estado][$municipio], true);
        }

        return true;
    }

    /**
     * Invalida la caché (el seeder la llama al terminar).
     */
    public static function limpiarCache(): void
    {
        self::$data = null;
        Cache::forget(self::CACHE_KEY);
    }
}