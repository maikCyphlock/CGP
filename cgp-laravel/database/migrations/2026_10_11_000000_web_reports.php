<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Informes publicados en Contraloría Escolar y Gestión Comunitaria. Los archivos viven en disco (storage/app/private/informes).
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE TABLE IF NOT EXISTS web_report (
            id BIGSERIAL PRIMARY KEY,
            section VARCHAR(10) NOT NULL CHECK (section IN ('escolar', 'gestion')),
            period VARCHAR(10) NOT NULL CHECK (period IN ('mensual', 'trimestral', 'semestral', 'anual')),
            year SMALLINT NOT NULL,
            label VARCHAR(60) NOT NULL,
            pdf_path VARCHAR(100) NOT NULL,
            image_path VARCHAR(100),
            author_id UUID NOT NULL REFERENCES staff_user(id),
            created_at TIMESTAMPTZ NOT NULL DEFAULT now()
        )");
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS web_report');
    }
};
