<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add direct, indexed columns to activity_log for lightning-fast queries
        Schema::table('activity_log', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_log', 'severity')) {
                $table->string('severity', 20)->default('routine')->nullable()->after('event');
            }
            if (!Schema::hasColumn('activity_log', 'forensic_hash')) {
                $table->string('forensic_hash', 64)->nullable()->after('batch_uuid');
            }
            if (!Schema::hasColumn('activity_log', 'parent_hash')) {
                $table->string('parent_hash', 64)->nullable()->after('forensic_hash');
            }

            // High-Performance Composite Indexes
            $table->index(['severity', 'created_at'], 'idx_activity_severity_created');
            $table->index(['event', 'created_at'], 'idx_activity_event_created');
            $table->index('forensic_hash', 'idx_activity_forensic_hash');
            $table->index(['causer_id', 'created_at'], 'idx_activity_causer_created');
        });

        // 2. Backfill existing rows from properties JSON if present
        try {
            DB::statement("
                UPDATE activity_log 
                SET 
                    severity = COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(properties, '$.severity')), ''), 'routine'),
                    forensic_hash = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(properties, '$.forensic_hash')), ''),
                    parent_hash = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(properties, '$.parent_hash')), '')
                WHERE properties IS NOT NULL
            ");
        } catch (\Throwable $e) {
            // Ignore if empty or error during backfill
        }

        // 3. Create Audit Checkpoints table for Merkle epoch verification
        if (!Schema::hasTable('audit_checkpoints')) {
            Schema::create('audit_checkpoints', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('start_activity_id');
                $table->unsignedBigInteger('end_activity_id');
                $table->integer('block_size');
                $table->string('merkle_root_hash', 64);
                $table->string('parent_checkpoint_hash', 64)->default('GENESIS_ROOT');
                $table->string('status', 20)->default('verified');
                $table->timestamp('sealed_at');
                $table->timestamps();

                $table->index(['start_activity_id', 'end_activity_id'], 'idx_checkpoint_range');
                $table->index('merkle_root_hash', 'idx_checkpoint_merkle');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_checkpoints');

        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex('idx_activity_severity_created');
            $table->dropIndex('idx_activity_event_created');
            $table->dropIndex('idx_activity_forensic_hash');
            $table->dropIndex('idx_activity_causer_created');

            if (Schema::hasColumn('activity_log', 'severity')) {
                $table->dropColumn('severity');
            }
            if (Schema::hasColumn('activity_log', 'forensic_hash')) {
                $table->dropColumn('forensic_hash');
            }
            if (Schema::hasColumn('activity_log', 'parent_hash')) {
                $table->dropColumn('parent_hash');
            }
        });
    }
};
