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
        if (Schema::hasTable('v_a_t_vouchers')) {
            // Drop foreign key first if needed or modify column
            DB::statement("ALTER TABLE `v_a_t_vouchers` MODIFY `vote_book_id` BIGINT UNSIGNED NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
