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
        Schema::table('mails', function (Blueprint $table) {
            if (!Schema::hasColumn('mails', 'is_sent')) {
                $table->boolean('is_sent')->default(false)->after('is_starred');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            //
        });
    }
};
