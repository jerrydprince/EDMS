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
        Schema::create('password_policies', function (Blueprint $table) {
            $table->id();
            $table->boolean('number_of_characters')->default(true);
            $table->boolean('uppercase')->default(true);
            $table->boolean('lowercase')->default(true);
            $table->boolean('special_character')->default(true);
            $table->boolean('number')->default(true);
            $table->boolean('password_request_notification')->default(false);
            $table->boolean('password_change_notification')->default(false);
            $table->integer('prevent_password_reusing')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_policies');
    }
};
