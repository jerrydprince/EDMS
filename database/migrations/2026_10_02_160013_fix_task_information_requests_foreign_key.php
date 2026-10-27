<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('task_information_requests', function (Blueprint $table) {
                $table->dropForeign(['task_id']);
                $table->foreign('task_id')->references('id')->on('task')->cascadeOnDelete();
            });
        } catch (\Throwable $e) {
            // Already points to task
        }
    }

    public function down(): void
    {
        Schema::table('task_information_requests', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->foreign('task_id')->references('id')->on('tasks')->cascadeOnDelete();
        });
    }
};
