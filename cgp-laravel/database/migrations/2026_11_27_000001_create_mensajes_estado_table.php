<?php

use App\Support\DacEstados;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS mensajes_estado (
                id              SMALLSERIAL PRIMARY KEY,
                estado_codigo   VARCHAR(30) NOT NULL UNIQUE REFERENCES case_status(code) ON UPDATE CASCADE ON DELETE CASCADE,
                titulo          VARCHAR(120),
                mensaje         TEXT NOT NULL,
                activo          BOOLEAN NOT NULL DEFAULT TRUE,
                actualizado_por UUID REFERENCES staff_user(id),
                created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
                updated_at      TIMESTAMPTZ NOT NULL DEFAULT now()
            );
        SQL);

        foreach (DacEstados::MENSAJES_POR_DEFECTO as $codigo => $info) {
            if (! DB::table('case_status')->where('code', $codigo)->exists()) {
                continue;
            }
            DB::table('mensajes_estado')->updateOrInsert(
                ['estado_codigo' => $codigo],
                [
                    'titulo' => $info['titulo'],
                    'mensaje' => $info['mensaje'],
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::unprepared('DROP TABLE IF EXISTS mensajes_estado;');
    }
};
