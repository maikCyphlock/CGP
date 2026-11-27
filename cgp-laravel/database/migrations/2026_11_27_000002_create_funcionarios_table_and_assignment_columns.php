<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS funcionarios (
                id              SERIAL PRIMARY KEY,
                area_direccion  VARCHAR(180) NOT NULL,
                cargo           VARCHAR(150) NOT NULL,
                figura          VARCHAR(150) NOT NULL,
                nombre_completo VARCHAR(160) NOT NULL,
                cedula          VARCHAR(20),
                referral_unit_id SMALLINT REFERENCES referral_unit(id),
                job_position_id INT REFERENCES job_position(id),
                staff_user_id   UUID REFERENCES staff_user(id),
                activo          BOOLEAN NOT NULL DEFAULT TRUE,
                created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
                updated_at      TIMESTAMPTZ NOT NULL DEFAULT now()
            );

            CREATE INDEX IF NOT EXISTS idx_funcionarios_activo ON funcionarios(activo);

            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS assigned_funcionario_id INT REFERENCES funcionarios(id);
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS assigned_area VARCHAR(180);
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS assigned_cargo VARCHAR(150);
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS assigned_figura VARCHAR(150);
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS assigned_funcionario_nombre VARCHAR(160);
        SQL);

        if (DB::table('funcionarios')->count() === 0) {
            $oacId = DB::table('referral_unit')->where('code', 'OAC')->value('id');
            $cpId = DB::table('referral_unit')->where('code', 'SUBSEQUENT_CONTROL')->value('id');
            $piId = DB::table('referral_unit')->where('code', 'INVESTIGATIVE_POWER')->value('id');

            $registros = [
                [
                    'area_direccion' => 'Dirección de Atención al Ciudadano (DAC)',
                    'cargo' => 'Director(a) de Atención al Ciudadano',
                    'figura' => 'Director(a) de la Dirección de Atención al Ciudadano (DAC)',
                    'nombre_completo' => 'Director(a) de la DAC (Por designar)',
                    'referral_unit_id' => $oacId,
                ],
                [
                    'area_direccion' => 'Área de Atención de Denuncia, Quejas, Reclamos, Asesorías, Sugerencias y Peticiones (AAD)',
                    'cargo' => 'Analista de Atención al Ciudadano',
                    'figura' => 'Funcionario(a) / Responsable de Mecanismo',
                    'nombre_completo' => 'Responsable de Mecanismo AAD (Por designar)',
                    'referral_unit_id' => $oacId,
                ],
                [
                    'area_direccion' => 'Área de Información y Promoción a la Participación Ciudadana (AIP)',
                    'cargo' => 'Promotor(a) de Participación Ciudadana',
                    'figura' => 'Promotor(a) Adscrito a la DAC',
                    'nombre_completo' => 'Promotor(a) AIP (Por designar)',
                    'referral_unit_id' => $oacId,
                ],
                [
                    'area_direccion' => 'Despacho del Contralor(a) Municipal',
                    'cargo' => 'Contralor(a) Municipal',
                    'figura' => 'Contralor(a) Municipal',
                    'nombre_completo' => 'Contralor(a) Municipal del Municipio Páez',
                    'referral_unit_id' => null,
                ],
                [
                    'area_direccion' => 'Dirección de Control Posterior',
                    'cargo' => 'Director(a) / Auditor(a) de Control Posterior',
                    'figura' => 'Funcionario(a) / Responsable de Área Técnica',
                    'nombre_completo' => 'Responsable de Control Posterior',
                    'referral_unit_id' => $cpId,
                ],
                [
                    'area_direccion' => 'Dirección de Determinación de Responsabilidades / Potestad Investigativa',
                    'cargo' => 'Director(a) / Abogado(a) Investigador(a)',
                    'figura' => 'Funcionario(a) / Responsable de Área Investigativa',
                    'nombre_completo' => 'Responsable de Potestad Investigativa',
                    'referral_unit_id' => $piId,
                ],
            ];

            foreach ($registros as $reg) {
                DB::table('funcionarios')->insert($reg + [
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            ALTER TABLE case_file DROP COLUMN IF EXISTS assigned_funcionario_nombre;
            ALTER TABLE case_file DROP COLUMN IF EXISTS assigned_figura;
            ALTER TABLE case_file DROP COLUMN IF EXISTS assigned_cargo;
            ALTER TABLE case_file DROP COLUMN IF EXISTS assigned_area;
            ALTER TABLE case_file DROP COLUMN IF EXISTS assigned_funcionario_id;
            DROP TABLE IF EXISTS funcionarios;
        SQL);
    }
};
