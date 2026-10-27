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
            $table->string('status')->default('active')->comment('active, archived');
        });

        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->json('form_schema')->nullable();
            $table->json('notification_rules')->nullable();
        });

        Schema::table('workflow_transitions', function (Blueprint $table) {
            $table->json('notification_rules')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropColumn(['form_schema', 'notification_rules']);
        });

        Schema::table('workflow_transitions', function (Blueprint $table) {
            $table->dropColumn('notification_rules');
        });
    }
};
