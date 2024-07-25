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
        Schema::create('chat_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // $table->morphs('groupable');
            $table->unsignedBigInteger('groupable_id');
            $table->string('groupable_type');

            // $table->unsignedBigInteger('department_id')->nullable();
            // $table->unsignedBigInteger('division_id')->nullable();
            // $table->unsignedBigInteger('unit_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_groups');
    }
};
