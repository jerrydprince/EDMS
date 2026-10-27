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
        if (Schema::hasTable('payment_comments') && Schema::hasColumn('payment_comments', 'payment_assignment_id')) {
            DB::statement("ALTER TABLE `payment_comments` MODIFY `payment_assignment_id` BIGINT UNSIGNED NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
