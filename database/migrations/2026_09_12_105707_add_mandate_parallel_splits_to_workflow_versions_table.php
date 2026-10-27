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
        Schema::table('workflow_versions', function (Blueprint $table) {
            $table->boolean('mandate_parallel_splits')->default(false)->after('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_versions', function (Blueprint $table) {
            $table->dropColumn('mandate_parallel_splits');
        });
    }
};
