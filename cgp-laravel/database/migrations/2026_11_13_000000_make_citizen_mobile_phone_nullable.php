<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Se usa SQL directo (y no ->change()) porque la vista v_case_detail depende
     * de la columna y PostgreSQL no permite alterar su tipo; quitar NOT NULL sí.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE citizen ALTER COLUMN mobile_phone DROP NOT NULL');
    }

    /**
     * Reverse the migrations.
     * Nota: fallará si ya existen ciudadanos sin teléfono celular.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE citizen ALTER COLUMN mobile_phone SET NOT NULL');
    }
};