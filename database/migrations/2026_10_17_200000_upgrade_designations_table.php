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
        Schema::table('designations', function (Blueprint $table) {
            // Make department_id nullable to allow global designations
            $table->unsignedBigInteger('department_id')->nullable()->change();
            
            // Hierarchy placement fields
            $table->unsignedBigInteger('division_id')->nullable()->after('department_id');
            $table->unsignedBigInteger('unit_id')->nullable()->after('division_id');
            $table->unsignedBigInteger('section_id')->nullable()->after('unit_id');
            $table->string('hierarchy_level')->default('global')->after('section_id'); // global, department, division, unit, section
            
            // Cadre & functional descriptors
            $table->string('cadre')->nullable()->after('title');
            $table->text('description')->nullable()->after('cadre');
            $table->boolean('is_registry')->default(false)->after('description');
            $table->boolean('is_head_of_office')->default(false)->after('is_registry');
            $table->boolean('is_active')->default(true)->after('is_head_of_office');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('designations', function (Blueprint $table) {
            $table->dropColumn([
                'division_id',
                'unit_id',
                'section_id',
                'hierarchy_level',
                'cadre',
                'description',
                'is_registry',
                'is_head_of_office',
                'is_active'
            ]);
        });
    }
};
