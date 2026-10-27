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
        Schema::table('task_assignments', function (Blueprint $table) {
            $table->index(['assigned_to', 'status']);
            $table->index(['task_id', 'status']);
        });

        Schema::table('task', function (Blueprint $table) {
            $table->index('status');
            $table->index('due_date');
            $table->index('created_at');
        });

        Schema::table('archives', function (Blueprint $table) {
            $table->index('folder_id');
            // Adding a composite index for department and division which is frequently used
            $table->index(['department_id', 'division_id']);
        });
        
        Schema::table('activity_log', function (Blueprint $table) {
            $table->index(['log_name', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_assignments', function (Blueprint $table) {
            $table->dropIndex(['assigned_to', 'status']);
            $table->dropIndex(['task_id', 'status']);
        });

        Schema::table('task', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('archives', function (Blueprint $table) {
            $table->dropIndex(['folder_id']);
            $table->dropIndex(['department_id', 'division_id']);
        });
        
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex(['log_name', 'created_at']);
        });
    }
};
