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
        Schema::table('workflows', function (Blueprint $table) {
            if (!Schema::hasColumn('workflows', 'is_confidential')) {
                $table->boolean('is_confidential')->default(false)->after('department_id');
            }
        });

        Schema::table('files', function (Blueprint $table) {
            if (!Schema::hasColumn('files', 'is_confidential')) {
                $table->boolean('is_confidential')->default(false)->after('department_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            if (Schema::hasColumn('workflows', 'is_confidential')) {
                $table->dropColumn('is_confidential');
            }
        });

        Schema::table('files', function (Blueprint $table) {
            if (Schema::hasColumn('files', 'is_confidential')) {
                $table->dropColumn('is_confidential');
            }
        });
    }
};
