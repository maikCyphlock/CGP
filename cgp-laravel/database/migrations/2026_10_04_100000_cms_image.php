<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// La imagen vive en disco (storage/app/private/cms); la base solo guarda el nombre del archivo.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE cms_content ADD COLUMN IF NOT EXISTS image_path VARCHAR(100)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE cms_content DROP COLUMN IF EXISTS image_path');
    }
};
