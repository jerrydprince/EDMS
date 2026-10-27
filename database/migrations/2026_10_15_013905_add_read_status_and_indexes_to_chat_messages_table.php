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
            if (!Schema::hasColumn('chat_messages', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('is_deleted');
            }
            if (!Schema::hasColumn('chat_messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('is_read');
            }
            $table->index(['sender_id', 'recipient_id', 'created_at']);
        });

        Schema::table('group_messages', function (Blueprint $table) {
            $table->index(['group_id', 'group_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex(['sender_id', 'recipient_id', 'created_at']);
            $table->dropColumn(['is_read', 'read_at']);
        });

        Schema::table('group_messages', function (Blueprint $table) {
            $table->dropIndex(['group_id', 'group_type', 'created_at']);
        });
    }
};
