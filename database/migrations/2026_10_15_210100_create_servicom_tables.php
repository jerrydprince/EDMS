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
        Schema::create('servicom_reports', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code')->unique();
            $table->unsignedBigInteger('complainant_id')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->unsignedBigInteger('accused_user_id');
            $table->string('category'); // harassment, intimidation, victimization, extortion, misconduct, other
            $table->longText('complaint_details');
            $table->string('evidence_file_path')->nullable();
            $table->enum('status', ['submitted', 'under_review', 'panel_convened', 'investigation_complete', 'closed', 'dismissed'])->default('submitted');
            $table->unsignedBigInteger('assigned_officer_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('complainant_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('accused_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('assigned_officer_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('servicom_panels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('report_id');
            $table->string('panel_title');
            $table->unsignedBigInteger('chairperson_id');
            $table->unsignedBigInteger('secretary_id');
            $table->date('hearing_date')->nullable();
            $table->longText('findings')->nullable();
            $table->longText('recommendations')->nullable();
            $table->longText('action_taken')->nullable();
            $table->enum('status', ['constituted', 'in_progress', 'report_submitted', 'resolved'])->default('constituted');
            $table->timestamps();

            $table->foreign('report_id')->references('id')->on('servicom_reports')->cascadeOnDelete();
            $table->foreign('chairperson_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('secretary_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('servicom_panel_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('panel_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('panel_id')->references('id')->on('servicom_panels')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicom_panel_members');
        Schema::dropIfExists('servicom_panels');
        Schema::dropIfExists('servicom_reports');
    }
};
