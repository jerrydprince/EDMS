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
        Schema::table('document_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('document_templates', 'document_type_id')) {
                $table->foreignId('document_type_id')->nullable()->after('type')->constrained('document_types')->nullOnDelete();
            }
            if (!Schema::hasColumn('document_templates', 'reference_prefix')) {
                $table->string('reference_prefix')->nullable()->after('is_universal');
            }
            if (!Schema::hasColumn('document_templates', 'reference_sequence')) {
                $table->unsignedBigInteger('reference_sequence')->default(0)->after('reference_prefix');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_templates', function (Blueprint $table) {
            if (Schema::hasColumn('document_templates', 'document_type_id')) {
                $table->dropForeign(['document_type_id']);
                $table->dropColumn('document_type_id');
            }
            if (Schema::hasColumn('document_templates', 'reference_prefix')) {
                $table->dropColumn('reference_prefix');
            }
            if (Schema::hasColumn('document_templates', 'reference_sequence')) {
                $table->dropColumn('reference_sequence');
            }
        });
    }
};
