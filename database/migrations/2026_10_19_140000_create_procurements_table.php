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
        if (!Schema::hasTable('procurements')) {
            Schema::create('procurements', function (Blueprint $table) {
                $table->id();
                $table->string('bpp_ref')->unique();
                $table->string('title');
                $table->string('vendor_name');
                $table->string('vendor_rc_number')->nullable();
                $table->string('vendor_bank_name')->nullable();
                $table->string('vendor_account_no')->nullable();
                $table->decimal('estimated_amount', 15, 2);
                $table->string('threshold_level')->default('Accounting Officer');
                $table->string('bpp_cert_no')->nullable();
                $table->string('lpo_number')->nullable()->unique();
                $table->integer('delivery_period_days')->default(30);
                $table->string('status')->default('draft');
                $table->text('evaluation_notes')->nullable();
                $table->text('terms_conditions')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('evaluated_by')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->unsignedBigInteger('lpo_issued_by')->nullable();
                $table->unsignedBigInteger('dispatched_by')->nullable();
                $table->timestamp('dispatched_at')->nullable();
                $table->unsignedBigInteger('dispatched_payment_id')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurements');
    }
};
