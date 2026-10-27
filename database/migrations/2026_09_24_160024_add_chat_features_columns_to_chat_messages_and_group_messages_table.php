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
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->json('reactions')->nullable();
        });

        Schema::table('group_messages', function (Blueprint $table) {
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->json('reactions')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['is_pinned', 'is_starred', 'is_deleted', 'reactions']);
        });

        Schema::table('group_messages', function (Blueprint $table) {
            $table->dropColumn(['is_pinned', 'is_starred', 'is_deleted', 'reactions']);
        });
    }
};
