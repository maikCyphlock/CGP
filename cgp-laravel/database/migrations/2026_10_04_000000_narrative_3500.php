<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// El formulario público admite hasta 3500 caracteres de narración.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE case_file DROP CONSTRAINT case_file_narrative_check');
        DB::statement('ALTER TABLE case_file ADD CONSTRAINT case_file_narrative_check CHECK (char_length(narrative) BETWEEN 50 AND 3500)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE case_file DROP CONSTRAINT case_file_narrative_check');
        DB::statement('ALTER TABLE case_file ADD CONSTRAINT case_file_narrative_check CHECK (char_length(narrative) BETWEEN 50 AND 3000)');
    }
};
