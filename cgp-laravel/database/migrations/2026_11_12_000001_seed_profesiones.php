<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    public function up(): void
    {
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\ProfesionSeeder',
            '--force' => true,
        ]);
    }

    public function down(): void
    {
        // Nada que revertir: la tabla se elimina en la migración anterior.
    }
};
