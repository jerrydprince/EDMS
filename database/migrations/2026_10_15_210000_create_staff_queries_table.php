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
        Schema::create('staff_queries', function (Blueprint $table) {
            $table->id();
            $table->string('query_ref')->unique();
            $table->unsignedBigInteger('issued_by');
            $table->unsignedBigInteger('issued_to');
            $table->string('subject');
            $table->string('incident_category');
            $table->longText('facts_description');
            $table->string('evidence_path')->nullable();
            $table->integer('deadline_hours')->default(48);
            $table->dateTime('deadline_at');
            $table->longText('response_text')->nullable();
            $table->string('response_evidence_path')->nullable();
            $table->dateTime('responded_at')->nullable();
            $table->enum('verdict', ['pending_response', 'under_review', 'accepted', 'cautioned', 'escalated_to_dc'])->default('pending_response');
            $table->text('verdict_comment')->nullable();
            $table->unsignedBigInteger('verdict_by')->nullable();
            $table->dateTime('verdict_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('issued_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('issued_to')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('verdict_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_queries');
    }
};
