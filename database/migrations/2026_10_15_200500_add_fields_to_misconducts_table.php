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
        Schema::table('misconducts', function (Blueprint $table) {
            if (!Schema::hasColumn('misconducts', 'reported_by')) {
                $table->unsignedBigInteger('reported_by')->nullable()->after('user_id');
                $table->foreign('reported_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('misconducts', 'status')) {
                $table->string('status')->default('submitted')->after('file'); // submitted, under_investigation, resolved, dismissed
            }
            if (!Schema::hasColumn('misconducts', 'incident_date')) {
                $table->date('incident_date')->nullable()->after('status');
            }
            if (!Schema::hasColumn('misconducts', 'action_taken')) {
                $table->text('action_taken')->nullable()->after('incident_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('misconducts', function (Blueprint $table) {
            if (Schema::hasColumn('misconducts', 'reported_by')) {
                $table->dropForeign(['reported_by']);
                $table->dropColumn('reported_by');
            }
            if (Schema::hasColumn('misconducts', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('misconducts', 'incident_date')) {
                $table->dropColumn('incident_date');
            }
            if (Schema::hasColumn('misconducts', 'action_taken')) {
                $table->dropColumn('action_taken');
            }
        });
    }
};
