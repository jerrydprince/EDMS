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
        Schema::table('folder', function (Blueprint $table) {
            if (!Schema::hasColumn('folder', 'trace_id')) {
                $table->string('trace_id')->nullable()->index()->after('number');
            }
        });

        Schema::table('task', function (Blueprint $table) {
            if (!Schema::hasColumn('task', 'folder_id')) {
                $table->unsignedBigInteger('folder_id')->nullable()->index()->after('file_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('folder', function (Blueprint $table) {
            if (Schema::hasColumn('folder', 'trace_id')) {
                $table->dropColumn('trace_id');
            }
        });

        Schema::table('task', function (Blueprint $table) {
            if (Schema::hasColumn('task', 'folder_id')) {
                $table->dropColumn('folder_id');
            }
        });
    }
};
