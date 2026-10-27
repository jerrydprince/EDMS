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
        Schema::create('support_infos', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('location')->nullable();
            $table->string('start_day')->nullable();
            $table->string('end_day')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_infos');
    }
};
