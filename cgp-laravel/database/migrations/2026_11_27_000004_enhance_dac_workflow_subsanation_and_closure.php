<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            -- 1. Asegurar Unidad de Auditoría Interna (UAI) en referral_unit
            INSERT INTO referral_unit (code, name, active) VALUES
                ('UAI', 'Unidad de Auditoría Interna (UAI)', TRUE)
            ON CONFLICT (code) DO NOTHING;

            -- 2. Columnas de control del lapso de subsanación (5 días hábiles)
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS subsanation_started_at TIMESTAMPTZ;
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS subsanation_deadline DATE;
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS subsanation_completed_at TIMESTAMPTZ;
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS subsanation_missing_docs TEXT;

            -- 3. Columnas de Auto de Cierre / Improcedencia, V°B° DAC y Aprobación del Contralor(a)
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS closure_auto_number VARCHAR(100);
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS closure_auto_date DATE;
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS closure_auto_url TEXT;
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS closure_reason VARCHAR(200);
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS dac_approved BOOLEAN NOT NULL DEFAULT FALSE;
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS dac_approved_by VARCHAR(160);
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS dac_approved_at TIMESTAMPTZ;
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS comptroller_approved BOOLEAN NOT NULL DEFAULT FALSE;
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS comptroller_approved_by VARCHAR(160);
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS comptroller_approved_at TIMESTAMPTZ;
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            ALTER TABLE case_file DROP COLUMN IF EXISTS comptroller_approved_at;
            ALTER TABLE case_file DROP COLUMN IF EXISTS comptroller_approved_by;
            ALTER TABLE case_file DROP COLUMN IF EXISTS comptroller_approved;
            ALTER TABLE case_file DROP COLUMN IF EXISTS dac_approved_at;
            ALTER TABLE case_file DROP COLUMN IF EXISTS dac_approved_by;
            ALTER TABLE case_file DROP COLUMN IF EXISTS dac_approved;
            ALTER TABLE case_file DROP COLUMN IF EXISTS closure_reason;
            ALTER TABLE case_file DROP COLUMN IF EXISTS closure_auto_url;
            ALTER TABLE case_file DROP COLUMN IF EXISTS closure_auto_date;
            ALTER TABLE case_file DROP COLUMN IF EXISTS closure_auto_number;
            ALTER TABLE case_file DROP COLUMN IF EXISTS subsanation_missing_docs;
            ALTER TABLE case_file DROP COLUMN IF EXISTS subsanation_completed_at;
            ALTER TABLE case_file DROP COLUMN IF EXISTS subsanation_deadline;
            ALTER TABLE case_file DROP COLUMN IF EXISTS subsanation_started_at;
        SQL);
    }
};
