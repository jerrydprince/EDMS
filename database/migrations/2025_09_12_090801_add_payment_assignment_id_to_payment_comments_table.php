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
        Schema::table('payment_comments', function (Blueprint $table) {
            $table->unsignedInteger('payment_assignment_id');
            $table->foreign('payment_assignment_id')->references('id')->on('payment_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_comments', function (Blueprint $table) {
            //
        });
    }
};
