<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            ALTER TABLE referral_unit ADD COLUMN IF NOT EXISTS active BOOLEAN NOT NULL DEFAULT TRUE;

            -- Hasta ahora todo usuario del panel tenía acceso total: se conserva con permisos explícitos.
            INSERT INTO staff_privilege (user_id, module_id, can_read, can_write, can_delete)
            SELECT u.id, m.id, TRUE, TRUE, TRUE FROM staff_user u CROSS JOIN app_module m
            ON CONFLICT (user_id, module_id) DO NOTHING;
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('ALTER TABLE referral_unit DROP COLUMN IF EXISTS active');
    }
};
