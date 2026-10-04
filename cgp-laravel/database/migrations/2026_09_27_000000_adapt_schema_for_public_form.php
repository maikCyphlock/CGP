<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            INSERT INTO claim_type (code, name, validation_level) VALUES
                ('COMPLAINT',  'Denuncia',   'STRICT'),
                ('GRIEVANCE',  'Queja',      'BASIC'),
                ('CLAIM',      'Reclamo',    'BASIC'),
                ('PETITION',   'Petición',   'BASIC'),
                ('SUGGESTION', 'Sugerencia', 'AUTOMATED')
            ON CONFLICT (code) DO NOTHING;

            -- La planilla oficial no pide fecha de los hechos; el formulario tampoco.
            ALTER TABLE case_file ALTER COLUMN incident_date DROP NOT NULL;

            -- Campo "Código SITUR" del bloque Proyecto de Consulta Popular.
            ALTER TABLE popular_consultation ADD COLUMN IF NOT EXISTS situr_code VARCHAR(40);
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            ALTER TABLE popular_consultation DROP COLUMN IF EXISTS situr_code;
            ALTER TABLE case_file ALTER COLUMN incident_date SET NOT NULL;
        SQL);
    }
};
