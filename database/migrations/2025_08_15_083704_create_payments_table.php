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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('comments')->nullable();
            $table->dateTime('due_date');
            $table->foreign('recipient_id')->references('id')->on('users');
            $table->unsignedBigInteger('recipient_id');
            $table->enum('status', ['due', 'paid', 'overdue', 'reverted'])->default('due');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
