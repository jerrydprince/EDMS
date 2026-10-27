<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chat_archives', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_archives', 'group_id')) {
                $table->unsignedBigInteger('group_id')->nullable()->after('archived_user_id');
            }
            if (!Schema::hasColumn('chat_archives', 'group_type')) {
                $table->string('group_type')->nullable()->after('group_id');
            }
        });

        // Make sure archived_user_id is nullable if not already
        DB::statement("ALTER TABLE chat_archives MODIFY archived_user_id BIGINT UNSIGNED NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_archives', function (Blueprint $table) {
            if (Schema::hasColumn('chat_archives', 'group_type')) {
                $table->dropColumn('group_type');
            }
            if (Schema::hasColumn('chat_archives', 'group_id')) {
                $table->dropColumn('group_id');
            }
        });
    }
};
