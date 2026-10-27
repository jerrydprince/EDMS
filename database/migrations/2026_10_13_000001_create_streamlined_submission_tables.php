<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create task_document_versions table
        if (!Schema::hasTable('task_document_versions')) {
            Schema::create('task_document_versions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id');
                $table->unsignedInteger('version')->default(1);
                $table->string('path');
                $table->unsignedBigInteger('created_by');
                $table->string('action')->default('submit'); // submit, sign, return, approve
                $table->text('note')->nullable();
                $table->timestamps();

                $table->foreign('task_id')->references('id')->on('task')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->index(['task_id', 'version']);
            });
        }

        // 2. Create task_annotations table
        if (!Schema::hasTable('task_annotations')) {
            Schema::create('task_annotations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('version_id');
                $table->unsignedInteger('page')->default(1);
                $table->decimal('x', 8, 2)->default(0);
                $table->decimal('y', 8, 2)->default(0);
                $table->text('text');
                $table->timestamp('resolved_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->foreign('version_id')->references('id')->on('task_document_versions')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        // 3. Add columns to task_minutes
        if (Schema::hasTable('task_minutes')) {
            Schema::table('task_minutes', function (Blueprint $table) {
                if (!Schema::hasColumn('task_minutes', 'role')) {
                    $table->string('role')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('task_minutes', 'remark')) {
                    $table->text('remark')->nullable()->after('action');
                }
                if (!Schema::hasColumn('task_minutes', 'signature_path')) {
                    $table->string('signature_path')->nullable()->after('remark');
                }
                if (!Schema::hasColumn('task_minutes', 'skip_reason')) {
                    $table->text('skip_reason')->nullable()->after('signature_path');
                }
                if (!Schema::hasColumn('task_minutes', 'signed_at')) {
                    $table->timestamp('signed_at')->nullable()->after('is_signed');
                }
            });
        }

        // 4. Add due_at to task_assignments
        if (Schema::hasTable('task_assignments')) {
            Schema::table('task_assignments', function (Blueprint $table) {
                if (!Schema::hasColumn('task_assignments', 'due_at')) {
                    $table->timestamp('due_at')->nullable()->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('task_assignments') && Schema::hasColumn('task_assignments', 'due_at')) {
            Schema::table('task_assignments', function (Blueprint $table) {
                $table->dropColumn('due_at');
            });
        }

        if (Schema::hasTable('task_minutes')) {
            Schema::table('task_minutes', function (Blueprint $table) {
                $cols = [];
                foreach (['role', 'remark', 'signature_path', 'skip_reason', 'signed_at'] as $col) {
                    if (Schema::hasColumn('task_minutes', $col)) {
                        $cols[] = $col;
                    }
                }
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        Schema::dropIfExists('task_annotations');
        Schema::dropIfExists('task_document_versions');
    }
};
