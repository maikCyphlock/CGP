<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS incident_lat NUMERIC(10, 7);
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS incident_lng NUMERIC(10, 7);
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS data_confirmation_days SMALLINT DEFAULT 5;
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS data_confirmation_deadline DATE;
            ALTER TABLE case_file ADD COLUMN IF NOT EXISTS data_confirmed_at TIMESTAMPTZ;
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            ALTER TABLE case_file DROP COLUMN IF EXISTS incident_lat;
            ALTER TABLE case_file DROP COLUMN IF EXISTS incident_lng;
            ALTER TABLE case_file DROP COLUMN IF EXISTS data_confirmation_days;
            ALTER TABLE case_file DROP COLUMN IF EXISTS data_confirmation_deadline;
            ALTER TABLE case_file DROP COLUMN IF EXISTS data_confirmed_at;
        SQL);
    }
};
