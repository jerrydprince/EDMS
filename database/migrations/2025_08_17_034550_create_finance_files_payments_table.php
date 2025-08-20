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
        Schema::create('finance_files_payments', function (Blueprint $table) {
              $table->id();
            $table->foreignId('finance_file_id')
                ->constrained('finance_files');
            $table->foreignId('payment_id')
                ->constrained('payments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_files_payments');
    }
};
