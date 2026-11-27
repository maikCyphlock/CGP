<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS case_remisiones (
                id                       UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                case_file_id             UUID NOT NULL REFERENCES case_file(id) ON DELETE CASCADE,
                user_id                  UUID REFERENCES staff_user(id),
                ambito_competencia       VARCHAR(255) NOT NULL,
                ente_destino             VARCHAR(255) NOT NULL,
                unidad_interna           VARCHAR(255),
                ente_externo             VARCHAR(500),
                referral_unit_id         SMALLINT REFERENCES referral_unit(id),
                numero_oficio            VARCHAR(100),
                fecha_remision           DATE,
                oficio_url               TEXT,
                fundamento_juridico      TEXT,
                recaudos                 JSONB NOT NULL DEFAULT '[]'::jsonb,
                ciudadano_notificado     BOOLEAN NOT NULL DEFAULT FALSE,
                fecha_notificacion_previa DATE,
                referencia_notificacion  VARCHAR(255),
                observacion              TEXT NOT NULL,
                created_at               TIMESTAMPTZ NOT NULL DEFAULT now(),
                updated_at               TIMESTAMPTZ NOT NULL DEFAULT now()
            );

            CREATE INDEX IF NOT EXISTS idx_case_remisiones_case_id ON case_remisiones(case_file_id);

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

            ALTER TABLE case_citizen_response ADD COLUMN IF NOT EXISTS is_competencia VARCHAR(10);
            ALTER TABLE case_citizen_response ADD COLUMN IF NOT EXISTS remision_id UUID REFERENCES case_remisiones(id) ON DELETE SET NULL;

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
            ALTER TABLE case_citizen_response DROP COLUMN IF EXISTS remision_id;
            ALTER TABLE case_citizen_response DROP COLUMN IF EXISTS is_competencia;
            DROP TABLE IF EXISTS case_remisiones;
        SQL);
    }
};
