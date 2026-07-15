<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('citizen')) {
            $sql = file_get_contents(database_path('sql/db.sql'));
            DB::unprepared($sql);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop views
        DB::statement('DROP VIEW IF EXISTS v_case_detail CASCADE');
        DB::statement('DROP VIEW IF EXISTS v_public_case_tracking CASCADE');

        // Drop tables in reverse order of creation/dependencies
        Schema::dropIfExists('cms_content_version');
        Schema::dropIfExists('cms_content');
        Schema::dropIfExists('cms_content_type');
        Schema::dropIfExists('unauthorized_access_log');
        Schema::dropIfExists('access_audit_log');
        Schema::dropIfExists('case_action');
        Schema::dropIfExists('case_status_log');
        Schema::dropIfExists('scanned_minute');
        Schema::dropIfExists('case_physical_doc');
        Schema::dropIfExists('evidence_file');
        Schema::dropIfExists('respondent');
        Schema::dropIfExists('popular_consultation');
        Schema::dropIfExists('case_file');
        Schema::dropIfExists('annual_sequence');
        Schema::dropIfExists('citizen');
        Schema::dropIfExists('session_log');
        Schema::dropIfExists('staff_privilege');
        Schema::dropIfExists('staff_user');
        Schema::dropIfExists('app_module');
        Schema::dropIfExists('job_position');
        Schema::dropIfExists('physical_doc_type');
        Schema::dropIfExists('id_document_type');
        Schema::dropIfExists('respondent_type');
        Schema::dropIfExists('referral_unit');
        Schema::dropIfExists('case_status');
        Schema::dropIfExists('irregularity_type');
        Schema::dropIfExists('claim_type');

        // Drop functions
        DB::statement('DROP FUNCTION IF EXISTS fn_generate_case_number() CASCADE');
        DB::statement('DROP FUNCTION IF EXISTS fn_log_status_change() CASCADE');
        DB::statement('DROP FUNCTION IF EXISTS fn_initial_status() CASCADE');
        DB::statement('DROP FUNCTION IF EXISTS fn_set_docs_deadline() CASCADE');
        DB::statement('DROP FUNCTION IF EXISTS fn_protect_active_position() CASCADE');
        DB::statement('DROP FUNCTION IF EXISTS fn_updated_at() CASCADE');
    }
};
