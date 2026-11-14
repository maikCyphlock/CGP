<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('case_status')
            ->where('code', 'PROCESSED')
            ->update(['name' => 'Procesando']);

        DB::table('case_status')
            ->where('code', 'INVALIDATED')
            ->update(['sort_order' => 5]);

        DB::table('case_status')
            ->where('code', 'ARCHIVED')
            ->update(['sort_order' => 6]);
    }

    public function down(): void
    {
        DB::table('case_status')
            ->where('code', 'PROCESSED')
            ->update(['name' => 'Procesado']);

        DB::table('case_status')
            ->where('code', 'ARCHIVED')
            ->update(['sort_order' => 5]);

        DB::table('case_status')
            ->where('code', 'INVALIDATED')
            ->update(['sort_order' => 6]);
    }
};
