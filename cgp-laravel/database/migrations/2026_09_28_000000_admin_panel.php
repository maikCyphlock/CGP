<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            -- Los usuarios del panel viven en staff_user (UUID), no en users (bigint).
            DELETE FROM sessions;
            ALTER TABLE sessions ALTER COLUMN user_id TYPE uuid USING NULL;

            INSERT INTO job_position (title, description)
            SELECT 'Jefe de OAC', 'Oficina de Atención al Ciudadano'
            WHERE NOT EXISTS (SELECT 1 FROM job_position WHERE title = 'Jefe de OAC');
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            DELETE FROM sessions;
            ALTER TABLE sessions ALTER COLUMN user_id TYPE bigint USING NULL;
        SQL);
    }
};
