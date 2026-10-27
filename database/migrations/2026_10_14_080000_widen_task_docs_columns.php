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
        if (Schema::hasTable('task_docs')) {
            DB::statement("ALTER TABLE `task_docs` MODIFY `path` TEXT NOT NULL, MODIFY `file_name` VARCHAR(500) NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('task_docs')) {
            DB::statement("ALTER TABLE `task_docs` MODIFY `path` VARCHAR(191) NOT NULL, MODIFY `file_name` VARCHAR(191) NOT NULL");
        }
    }
};
