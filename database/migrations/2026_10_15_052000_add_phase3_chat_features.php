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
        if (Schema::hasTable('chat_messages')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                if (!Schema::hasColumn('chat_messages', 'is_edited')) {
                    $table->boolean('is_edited')->default(false)->after('is_deleted');
                }
                if (!Schema::hasColumn('chat_messages', 'edited_at')) {
                    $table->timestamp('edited_at')->nullable()->after('is_edited');
                }
            });
        }

        if (Schema::hasTable('group_messages')) {
            Schema::table('group_messages', function (Blueprint $table) {
                if (!Schema::hasColumn('group_messages', 'is_edited')) {
                    $table->boolean('is_edited')->default(false)->after('is_deleted');
                }
                if (!Schema::hasColumn('group_messages', 'edited_at')) {
                    $table->timestamp('edited_at')->nullable()->after('is_edited');
                }
            });
        }

        if (Schema::hasTable('chat_attachments')) {
            Schema::table('chat_attachments', function (Blueprint $table) {
                if (!Schema::hasColumn('chat_attachments', 'edms_file_id')) {
                    $table->unsignedBigInteger('edms_file_id')->nullable()->after('file_type');
                }
                if (!Schema::hasColumn('chat_attachments', 'metadata')) {
                    $table->json('metadata')->nullable()->after('edms_file_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('chat_messages')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->dropColumn(['is_edited', 'edited_at']);
            });
        }

        if (Schema::hasTable('group_messages')) {
            Schema::table('group_messages', function (Blueprint $table) {
                $table->dropColumn(['is_edited', 'edited_at']);
            });
        }

        if (Schema::hasTable('chat_attachments')) {
            Schema::table('chat_attachments', function (Blueprint $table) {
                $table->dropColumn(['edms_file_id', 'metadata']);
            });
        }
    }
};
