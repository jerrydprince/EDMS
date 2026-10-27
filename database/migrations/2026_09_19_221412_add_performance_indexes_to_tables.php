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
        Schema::table('tasks', function (Blueprint $table) {
            //
        });
        
        Schema::table('notifications', function (Blueprint $table) {
            $table->index('read_at');
        });
        
        Schema::table('files', function (Blueprint $table) {
            $table->index('department_id');
            $table->index('division_id');
            $table->index('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            //
        });
        
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['read_at']);
        });
        
        Schema::table('files', function (Blueprint $table) {
            $table->dropIndex(['department_id']);
            $table->dropIndex(['division_id']);
            $table->dropIndex(['unit_id']);
        });
    }
};
