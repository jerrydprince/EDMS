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
        DB::statement("ALTER TABLE task_assignments MODIFY COLUMN status ENUM('pending', 'in progress', 'under review', 'completed', 'revision', 'returned', 'on_hold', 'hold_responded') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE task_assignments MODIFY COLUMN status ENUM('pending', 'in progress', 'under review', 'completed', 'revision', 'returned', 'on_hold') NOT NULL DEFAULT 'pending'");
    }
};
