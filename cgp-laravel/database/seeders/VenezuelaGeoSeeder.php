<?php

namespace Database\Seeders;

use App\Models\Estado;
use App\Models\Municipio;
use App\Models\Parroquia;
use App\Support\VenezuelaGeo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VenezuelaGeoSeeder extends Seeder
{
    /**
     * Carga el catálogo desde resources/data/venezuela_geo.json
     * (estructura: { "Estado": { "Municipio": ["Parroquia", ...] } }).
     * Es idempotente: puede ejecutarse varias veces sin duplicar filas.
     */
    public function run(): void
    {
        $ruta = resource_path('data/venezuela_geo.json');

        if (!is_file($ruta)) {
            throw new RuntimeException("No se encontró el catálogo en: {$ruta}");
        }

        $geo = json_decode(file_get_contents($ruta), true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($geo) {
            foreach ($geo as $nombreEstado => $municipios) {
                $estado = Estado::updateOrCreate(['nombre' => $nombreEstado]);

                foreach ($municipios as $nombreMunicipio => $parroquias) {
                    $municipio = Municipio::updateOrCreate([
                        'estado_id' => $estado->id,
                        'nombre'    => $nombreMunicipio,
                    ]);

                    foreach (array_unique($parroquias) as $nombreParroquia) {
                        Parroquia::updateOrCreate([
                            'municipio_id' => $municipio->id,
                            'nombre'       => $nombreParroquia,
                        ]);
                    }
                }
            }
        });

        VenezuelaGeo::limpiarCache();
    }
}