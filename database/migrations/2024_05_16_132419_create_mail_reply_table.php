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
        Schema::create('mail_reply', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mail_id');
            $table->integer('reply_id');
            $table->foreign('mail_id')->references('id')->on('mails');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_reply');
    }
};
