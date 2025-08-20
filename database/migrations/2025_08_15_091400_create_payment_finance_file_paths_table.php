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
        Schema::create('payment_finance_file_paths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_file_paths_id')
                ->constrained('finance_file_paths');
            $table->foreignId('payment_id')
                ->constrained('payments');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_finance_file_paths');
    }
};
