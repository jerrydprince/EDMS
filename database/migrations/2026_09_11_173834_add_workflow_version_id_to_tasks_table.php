<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('task', 'workflow_version_id')) {
            Schema::table('task', function (Blueprint $table) {
                $table->unsignedBigInteger('workflow_version_id')->nullable()->after('id');
            });
        }
    }

    public function down(): void
    {
                Schema::table('task', function (Blueprint $table) { $table->dropColumn('workflow_version_id'); });
    }
};