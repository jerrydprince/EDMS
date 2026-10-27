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
        Schema::create('share_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sharing_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('shared_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('folder_id')->constrained('folder')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_folders');
    }
};
