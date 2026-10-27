<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workflows') && !Schema::hasColumn('workflows', 'document_template_id')) {
            Schema::table('workflows', function (Blueprint $table) {
                $table->unsignedBigInteger('document_template_id')->nullable()->after('department_id');
                $table->foreign('document_template_id')->references('id')->on('document_templates')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workflows') && Schema::hasColumn('workflows', 'document_template_id')) {
            Schema::table('workflows', function (Blueprint $table) {
                $table->dropForeign(['document_template_id']);
                $table->dropColumn('document_template_id');
            });
        }
    }
};
