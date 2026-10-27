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
        if (!Schema::hasTable('system_capabilities')) {
            Schema::create('system_capabilities', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->string('category'); // e.g. 'Financial Accounting', 'Executive Oversight', 'Audit & Inspection', etc.
                $table->text('description')->nullable();
                $table->json('applicable_tiers'); // ['department', 'division', 'unit', 'section']
                $table->string('default_route')->nullable();
                $table->string('icon')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('entity_capabilities')) {
            Schema::create('entity_capabilities', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type'); // 'department', 'division', 'unit', 'section'
                $table->unsignedBigInteger('entity_id');
                $table->string('capability_code');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['entity_type', 'entity_id', 'capability_code'], 'ent_cap_unique');
                $table->index(['entity_type', 'entity_id']);
                $table->index('capability_code');
            });
        }

        if (Schema::hasTable('document_templates')) {
            Schema::table('document_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('document_templates', 'division_id')) {
                    $table->unsignedBigInteger('division_id')->nullable()->after('department_id');
                }
                if (!Schema::hasColumn('document_templates', 'unit_id')) {
                    $table->unsignedBigInteger('unit_id')->nullable()->after('division_id');
                }
                if (!Schema::hasColumn('document_templates', 'section_id')) {
                    $table->unsignedBigInteger('section_id')->nullable()->after('unit_id');
                }
                if (!Schema::hasColumn('document_templates', 'is_universal')) {
                    $table->boolean('is_universal')->default(false)->after('section_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_capabilities');
        Schema::dropIfExists('system_capabilities');

        if (Schema::hasTable('document_templates')) {
            Schema::table('document_templates', function (Blueprint $table) {
                $cols = ['division_id', 'unit_id', 'section_id', 'is_universal'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('document_templates', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
