<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            -- 1. Añadir columnas de asignación explícita a case_file si no existen
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS assigned_unit_id SMALLINT REFERENCES referral_unit(id);
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS assigned_position_id INT REFERENCES job_position(id);
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS assigned_user_id UUID REFERENCES staff_user(id);
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS assigned_at TIMESTAMPTZ;
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS assigned_by UUID REFERENCES staff_user(id);
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS assignment_notes TEXT;

            -- 2. Asegurar que 'OAC' (Oficina de Atención al Ciudadano) exista como unidad en referral_unit
            INSERT INTO referral_unit (code, name, active) VALUES
                ('OAC', 'Oficina de Atención al Ciudadano', TRUE)
            ON CONFLICT (code) DO NOTHING;

            -- 3. Actualizar constraint en case_action para permitir 'ASSIGNMENT' si existe la constraint
            DO $$
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM pg_constraint WHERE conname = 'case_action_action_type_check'
                ) THEN
                    ALTER TABLE case_action DROP CONSTRAINT case_action_action_type_check;
                    ALTER TABLE case_action ADD CONSTRAINT case_action_action_type_check
                        CHECK (action_type IN ('CLASSIFICATION','REFERRAL','STATUS_CHANGE','NOTE','DOCUMENT_CHECK','ASSIGNMENT','OTHER'));
                END IF;
            END $$;
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            ALTER TABLE case_file DROP COLUMN IF EXISTS assigned_unit_id;
            ALTER TABLE case_file DROP COLUMN IF EXISTS assigned_position_id;
            ALTER TABLE case_file DROP COLUMN IF EXISTS assigned_user_id;
            ALTER TABLE case_file DROP COLUMN IF EXISTS assigned_at;
            ALTER TABLE case_file DROP COLUMN IF EXISTS assigned_by;
            ALTER TABLE case_file DROP COLUMN IF EXISTS assignment_notes;
        SQL);
    }
};
