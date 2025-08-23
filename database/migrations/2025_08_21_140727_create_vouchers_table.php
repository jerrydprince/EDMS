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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('deptal_no')->unique();
            $table->string('source');
            $table->dateTime('date');
            $table->string('head');
            $table->string('checked_and_passed');
            $table->decimal('amount', 15, 2);
            $table->string('classification_code');
            $table->string('voucher_number')->unique();
            $table->string('s_head');
            $table->string('address');
            $table->string('data_type');
            $table->unsignedBigInteger('payment_id');
            $table->foreign('payment_id')->references('id')->on('payments');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
