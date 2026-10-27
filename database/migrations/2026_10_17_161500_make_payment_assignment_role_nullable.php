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
        if (Schema::hasTable('payment_assignment')) {
            DB::statement("ALTER TABLE `payment_assignment` MODIFY `role_id` BIGINT UNSIGNED NULL");
            DB::statement("ALTER TABLE `payment_assignment` MODIFY `role` VARCHAR(255) NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
