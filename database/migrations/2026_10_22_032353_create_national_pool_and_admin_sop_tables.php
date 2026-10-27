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
        // 1. National Central Register of Federal Treasury Officers
        Schema::create('pool_officers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('other_names')->nullable();
            $table->string('ippis_no')->unique();
            $table->string('staff_id')->nullable()->index();
            $table->string('gender')->default('male');
            $table->string('cadre')->default('Accountant');
            $table->string('grade_level')->default('GL 08');
            $table->integer('step')->default(1);
            $table->string('state_of_origin')->index();
            $table->string('lga')->nullable();
            $table->string('geopolitical_zone')->index();
            $table->string('current_mda_name')->index();
            $table->string('current_mda_code')->nullable();
            $table->string('directorate_in_mda')->nullable();
            $table->date('date_posted_to_mda')->nullable();
            $table->decimal('tenancy_years', 4, 1)->default(0.0);
            $table->json('special_competencies')->nullable();
            $table->string('confirmation_status')->default('confirmed');
            $table->date('date_of_first_appointment')->nullable();
            $table->date('date_of_present_appointment')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('status')->default('active')->index(); // active, due_rotation, recalled, on_leave, interdicted
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. MDA Pool Requisitions (Appendix C)
        Schema::create('pool_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('requisition_no')->unique();
            $table->string('mda_name')->index();
            $table->string('mda_code')->nullable();
            $table->string('letter_ref');
            $table->date('letter_date');
            $table->text('justification');
            $table->string('grade_level_required')->default('GL 10');
            $table->integer('number_required')->default(1);
            $table->json('special_skills_required')->nullable();
            $table->boolean('has_letterhead')->default(true);
            $table->boolean('has_accommodation')->default(false);
            $table->string('status')->default('pending')->index(); // pending, shortlisted, approved, fulfilled, rejected
            $table->json('shortlisted_officer_ids')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('treated_by')->nullable();
            $table->timestamps();
        });

        // 3. Pool Posting Orders & Assumption Slips (Form OAGF/POOL/01 & 02)
        Schema::create('pool_postings', function (Blueprint $table) {
            $table->id();
            $table->string('posting_ref')->unique();
            $table->unsignedBigInteger('pool_officer_id')->index();
            $table->unsignedBigInteger('pool_requisition_id')->nullable()->index();
            $table->string('from_mda_name');
            $table->string('to_mda_name');
            $table->string('posting_type')->default('routine'); // routine, emergency, rotation, promotion
            $table->date('effective_date');
            $table->string('approval_authority')->default('da'); // da (GL 06-13), agf (GL 14+)
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->string('posting_letter_path')->nullable();
            $table->date('assumption_due_date')->nullable();
            $table->string('assumption_status')->default('pending')->index(); // pending, assumed, overdue
            $table->date('assumption_slip_returned_at')->nullable();
            $table->string('assumption_slip_path')->nullable();
            $table->timestamps();

            $table->foreign('pool_officer_id')->references('id')->on('pool_officers')->onDelete('cascade');
        });

        // 4. File Movement Cards (Appendix A - 24-Hour Rule Watchdog)
        Schema::create('file_movement_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_id')->nullable()->index();
            $table->string('file_no')->index();
            $table->string('subject');
            $table->string('from_office');
            $table->string('to_office');
            $table->unsignedBigInteger('releasing_officer_id')->nullable();
            $table->unsignedBigInteger('receiving_officer_id')->nullable();
            $table->dateTime('date_time_out');
            $table->dateTime('date_time_in')->nullable();
            $table->boolean('is_treated')->default(false);
            $table->dateTime('treated_at')->nullable();
            $table->integer('holding_hours')->default(0);
            $table->boolean('sla_breached')->default(false);
            $table->string('remarks')->nullable();
            $table->timestamps();
        });

        // 5. Digital Stamps Registry
        Schema::create('digital_stamps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->string('designation_title');
            $table->string('stamp_type')->default('svg_generated'); // svg_generated, uploaded_png
            $table->string('custom_stamp_path')->nullable();
            $table->string('ink_color')->default('#1e3a8a');
            $table->boolean('is_default')->default(true);
            $table->timestamps();
        });

        // 6. 24-Month Confirmation Lifecycle (PSR 020301)
        Schema::create('staff_confirmations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->date('appointment_date');
            $table->date('confirmation_due_date');
            $table->date('alert_date'); // 21 months
            $table->string('status')->default('probation')->index(); // probation, due, recommended, approved, gazetted, deferred
            $table->text('recommendation_notes')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->string('gazette_reference')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 7. APER Submissions & Campaign Ledger (Appendix D)
        Schema::create('aper_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->integer('year')->index();
            $table->string('grade_level');
            $table->unsignedBigInteger('reporting_officer_id')->nullable();
            $table->unsignedBigInteger('countersigning_officer_id')->nullable();
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->string('rating_category')->nullable(); // Outstanding, Very Good, Good, Fair, Poor
            $table->string('recommendation')->nullable(); // Accelerated Promotion, Promotable, Normal, Not Promotable
            $table->boolean('is_checklist_verified')->default(false);
            $table->string('form_path')->nullable();
            $table->string('status')->default('issued')->index(); // issued, submitted, vetted, archived
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 8. Staff Welfare Claims (48-Hour SLA)
        Schema::create('welfare_claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('claim_type')->index(); // burial, medical, compassionate, nhis, housing_loan
            $table->decimal('amount_requested', 14, 2)->default(0.00);
            $table->decimal('amount_approved', 14, 2)->default(0.00);
            $table->text('justification');
            $table->string('evidence_path')->nullable();
            $table->dateTime('sla_deadline');
            $table->string('status')->default('pending')->index(); // pending, recommended, approved, rejected, disbursed
            $table->unsignedBigInteger('recommended_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welfare_claims');
        Schema::dropIfExists('aper_submissions');
        Schema::dropIfExists('staff_confirmations');
        Schema::dropIfExists('digital_stamps');
        Schema::dropIfExists('file_movement_cards');
        Schema::dropIfExists('pool_postings');
        Schema::dropIfExists('pool_requisitions');
        Schema::dropIfExists('pool_officers');
    }
};
