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
            if (!Schema::hasColumn('workflow_versions', 'is_approved')) {
                $table->boolean('is_approved')->default(false)->after('is_published');
                $table->unsignedBigInteger('approved_by_id')->nullable()->after('is_approved');
                $table->timestamp('approved_at')->nullable()->after('approved_by_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_versions', function (Blueprint $table) {
            $table->dropColumn(['is_approved', 'approved_by_id', 'approved_at']);
        });
    }
};
