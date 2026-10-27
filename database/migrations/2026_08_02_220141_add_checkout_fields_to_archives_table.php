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
        Schema::table('archives', function (Blueprint $table) {
            $table->boolean('is_checked_out')->default(false);
            $table->unsignedBigInteger('checked_out_by')->nullable();
            
            $table->foreign('checked_out_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archives', function (Blueprint $table) {
            $table->dropForeign(['checked_out_by']);
            $table->dropColumn(['is_checked_out', 'checked_out_by']);
        });
    }
};
