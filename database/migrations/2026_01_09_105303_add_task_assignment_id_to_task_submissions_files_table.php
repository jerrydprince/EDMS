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
        Schema::table('task_submission_files', function (Blueprint $table) {
            $table->unsignedBigInteger('task_assignment_id')->nullable()->after('task_submissions_id');

            $table->foreign('task_assignment_id')->references('id')->on('task_assignments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_submission_files', function (Blueprint $table) {
            $table->dropForeign(['task_assignment_id']);
            $table->dropColumn('task_assignment_id');
        });
    }
};
