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
        Schema::create('payment_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('recipient_id');
            $table->unsignedBigInteger('sender_id');
            $table->text('comment');
            $table->foreign('payment_id')->references('id')->on('payments');
            $table->foreign('recipient_id')->references('id')->on('users');
            $table->foreign('sender_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_comments');
    }
};
