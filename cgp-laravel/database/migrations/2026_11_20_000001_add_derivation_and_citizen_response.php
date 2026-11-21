<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            -- Columnas de derivación en case_file
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS deriv_scope VARCHAR(255);
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS deriv_dest_type VARCHAR(255);
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS deriv_unit VARCHAR(255);
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS deriv_external_entity VARCHAR(500);
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS deriv_office_number VARCHAR(100);
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS deriv_office_date DATE;
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS deriv_letter_url TEXT;
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS deriv_fundamento TEXT;
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS deriv_recaudos JSONB DEFAULT '[]'::jsonb;
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS deriv_citizen_notified BOOLEAN DEFAULT FALSE;
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS deriv_citizen_notified_at DATE;
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS deriv_citizen_notified_ref VARCHAR(255);

            -- Tabla de respuestas ciudadanas
            CREATE TABLE IF NOT EXISTS case_citizen_response (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                case_file_id UUID NOT NULL REFERENCES case_file(id) ON DELETE CASCADE,
                user_id UUID REFERENCES staff_user(id),
                response_type VARCHAR(100) NOT NULL,
                response_date DATE NOT NULL,
                referred_entity VARCHAR(500),
                legal_basis TEXT,
                office_circular VARCHAR(200),
                subsanation_days SMALLINT,
                viabilidad_result TEXT,
                informed_max_authority BOOLEAN DEFAULT FALSE,
                response_text TEXT NOT NULL,
                issued_by_name VARCHAR(200) NOT NULL,
                issued_by_cargo VARCHAR(200) NOT NULL,
                created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
                updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
            );

            CREATE INDEX IF NOT EXISTS idx_case_citizen_resp_case_id ON case_citizen_response(case_file_id);

            -- Actualizar constraint en case_action si existe para permitir DERIVATION y CITIZEN_RESPONSE
            DO $$
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM pg_constraint WHERE conname = 'case_action_action_type_check'
                ) THEN
                    ALTER TABLE case_action DROP CONSTRAINT case_action_action_type_check;
                    ALTER TABLE case_action ADD CONSTRAINT case_action_action_type_check
                        CHECK (action_type IN ('CLASSIFICATION','REFERRAL','STATUS_CHANGE','NOTE','DOCUMENT_CHECK','ASSIGNMENT','DERIVATION','CITIZEN_RESPONSE','OTHER'));
                END IF;
            END $$;
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            DROP TABLE IF EXISTS case_citizen_response;
            ALTER TABLE case_file DROP COLUMN IF EXISTS deriv_scope;
            ALTER TABLE case_file DROP COLUMN IF EXISTS deriv_dest_type;
            ALTER TABLE case_file DROP COLUMN IF EXISTS deriv_unit;
            ALTER TABLE case_file DROP COLUMN IF EXISTS deriv_external_entity;
            ALTER TABLE case_file DROP COLUMN IF EXISTS deriv_office_number;
            ALTER TABLE case_file DROP COLUMN IF EXISTS deriv_office_date;
            ALTER TABLE case_file DROP COLUMN IF EXISTS deriv_letter_url;
            ALTER TABLE case_file DROP COLUMN IF EXISTS deriv_fundamento;
            ALTER TABLE case_file DROP COLUMN IF EXISTS deriv_recaudos;
            ALTER TABLE case_file DROP COLUMN IF EXISTS deriv_citizen_notified;
            ALTER TABLE case_file DROP COLUMN IF EXISTS deriv_citizen_notified_at;
            ALTER TABLE case_file DROP COLUMN IF EXISTS deriv_citizen_notified_ref;
        SQL);
    }
};
