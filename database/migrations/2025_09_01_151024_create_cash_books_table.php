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
        Schema::create('cash_books', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vote_book_id');
            $table->date('date');
            $table->string('voucher_no');
            $table->string('ncoa_code');
            $table->string('dvbn');
            $table->string('from');
            $table->string('to');
            $table->text('receipt_description')->nullable();
            $table->text('payment_description')->nullable();
            $table->string('tsa_bank');
            $table->string('treasury_receipt');
            $table->foreign('vote_book_id')->references('id')->on('vote_books');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_books');
    }
};
