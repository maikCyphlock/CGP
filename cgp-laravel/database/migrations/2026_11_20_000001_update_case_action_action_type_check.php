<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE case_action DROP CONSTRAINT IF EXISTS case_action_action_type_check');
        DB::statement("ALTER TABLE case_action ADD CONSTRAINT case_action_action_type_check CHECK (action_type IN ('CLASSIFICATION','REFERRAL','STATUS_CHANGE','NOTE','DOCUMENT_CHECK','OTHER','EDITION','SOFT_DELETED','RESTORED'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE case_action DROP CONSTRAINT IF EXISTS case_action_action_type_check');
        DB::statement("ALTER TABLE case_action ADD CONSTRAINT case_action_action_type_check CHECK (action_type IN ('CLASSIFICATION','REFERRAL','STATUS_CHANGE','NOTE','DOCUMENT_CHECK','OTHER'))");
    }
};
