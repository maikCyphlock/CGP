<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Asegurar o actualizar los registros en case_status
        $hasInReview = DB::table('case_status')->where('code', 'IN_REVIEW')->exists();
        $hasProcessing = DB::table('case_status')->where('code', 'PROCESSING')->exists();

        if ($hasInReview && ! $hasProcessing) {
            DB::table('case_status')
                ->where('code', 'IN_REVIEW')
                ->update([
                    'code' => 'PROCESSING',
                    'name' => 'En tramitación / valoración',
                    'sort_order' => 2,
                    'is_terminal' => false,
                ]);
        } elseif (! $hasProcessing) {
            DB::table('case_status')->insert([
                'code' => 'PROCESSING',
                'name' => 'En tramitación / valoración',
                'sort_order' => 2,
                'is_terminal' => false,
            ]);
        } else {
            DB::table('case_status')
                ->where('code', 'PROCESSING')
                ->update([
                    'name' => 'En tramitación / valoración',
                    'sort_order' => 2,
                    'is_terminal' => false,
                ]);
        }

        // Mapeo o actualización de PROCESSED -> SUBSANATION
        $processingId = DB::table('case_status')->where('code', 'PROCESSING')->value('id');
        $processedStatus = DB::table('case_status')->where('code', 'PROCESSED')->first();
        $hasSubsanation = DB::table('case_status')->where('code', 'SUBSANATION')->exists();

        if ($processedStatus && $processingId) {
            DB::table('case_file')
                ->where('status_id', $processedStatus->id)
                ->update(['status_id' => $processingId]);
        }

        if ($processedStatus && ! $hasSubsanation) {
            DB::table('case_status')
                ->where('code', 'PROCESSED')
                ->update([
                    'code' => 'SUBSANATION',
                    'name' => 'En subsanación',
                    'sort_order' => 3,
                    'is_terminal' => false,
                ]);
        } elseif (! $hasSubsanation) {
            DB::table('case_status')->insert([
                'code' => 'SUBSANATION',
                'name' => 'En subsanación',
                'sort_order' => 3,
                'is_terminal' => false,
            ]);
        } else {
            DB::table('case_status')
                ->where('code', 'SUBSANATION')
                ->update([
                    'name' => 'En subsanación',
                    'sort_order' => 3,
                    'is_terminal' => false,
                ]);
        }

        // Actualizar nombres y órdenes de los demás estados
        DB::table('case_status')
            ->where('code', 'RECEIVED')
            ->update([
                'name' => 'Recibido',
                'sort_order' => 1,
                'is_terminal' => false,
            ]);

        DB::table('case_status')
            ->where('code', 'REFERRED')
            ->update([
                'name' => 'Remitido / derivado',
                'sort_order' => 4,
                'is_terminal' => false,
            ]);

        DB::table('case_status')
            ->where('code', 'INVALIDATED')
            ->update([
                'name' => 'Improcedente / desestimado y archivado',
                'sort_order' => 5,
                'is_terminal' => true,
            ]);

        DB::table('case_status')
            ->where('code', 'ARCHIVED')
            ->update([
                'name' => 'Cerrado / archivado',
                'sort_order' => 6,
                'is_terminal' => true,
            ]);

        // Asegurar constraint en case_action
        try {
            DB::statement('ALTER TABLE case_action DROP CONSTRAINT IF EXISTS case_action_action_type_check');
            DB::statement("ALTER TABLE case_action ADD CONSTRAINT case_action_action_type_check CHECK (action_type IN ('CLASSIFICATION','REFERRAL','STATUS_CHANGE','NOTE','DOCUMENT_CHECK','ASSIGNMENT','DERIVATION','CITIZEN_RESPONSE','OTHER','EDITION','SOFT_DELETED','RESTORED'))");
        } catch (\Throwable $th) {
            // No crítico si ya está configurado
        }
    }

    public function down(): void
    {
        DB::table('case_status')
            ->where('code', 'RECEIVED')
            ->update(['name' => 'Recibido', 'sort_order' => 1]);

        DB::table('case_status')
            ->where('code', 'PROCESSING')
            ->update(['code' => 'IN_REVIEW', 'name' => 'En Revisión', 'sort_order' => 2]);

        DB::table('case_status')
            ->where('code', 'SUBSANATION')
            ->update(['code' => 'PROCESSED', 'name' => 'Procesando', 'sort_order' => 3]);

        DB::table('case_status')
            ->where('code', 'REFERRED')
            ->update(['name' => 'Derivado', 'sort_order' => 4]);

        DB::table('case_status')
            ->where('code', 'INVALIDATED')
            ->update(['name' => 'Invalidado', 'sort_order' => 5]);

        DB::table('case_status')
            ->where('code', 'ARCHIVED')
            ->update(['name' => 'Archivado', 'sort_order' => 6]);
    }
};
