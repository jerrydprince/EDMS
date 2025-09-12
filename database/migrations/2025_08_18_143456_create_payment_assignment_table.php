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
        Schema::create('payment_assignment', function (Blueprint $table) {
            $table->id();
            $table->dateTime('due_date');
            $table->integer('recipient_id')->unsigned();
            $table->enum('status', ['pending', 'done', 'rejected', 'due', 'overdue'])->default('pending');
            $table->text('comments')->nullable();
            $table->foreign('recipient_id')->references('id')->on('users');
            $table->string('role');
            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')->references('id')->on('roles');
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
        Schema::dropIfExists('payment_assignment');
    }
};
