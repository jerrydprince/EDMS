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
        Schema::create('v_a_t_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number')->unique();
            $table->unsignedBigInteger('payment_id');
            $table->string('percentage');
            $table->unsignedBigInteger('vote_book_id');
            $table->decimal('amount', 15, 2);
            $table->decimal('deduction_amount', 15, 2);
            $table->foreign('payment_id')->references('id')->on('payments');
            $table->foreign('vote_book_id')->references('id')->on('vote_books');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('v_a_t_vouchers');
    }
};
