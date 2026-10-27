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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'ippis_no')) {
                $table->string('ippis_no', 30)->nullable()->after('grade_level');
            }
            if (!Schema::hasColumn('users', 'step')) {
                $table->integer('step')->default(1)->after('grade_level');
            }
            if (!Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('users', 'date_of_first_appointment')) {
                $table->date('date_of_first_appointment')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'date_of_confirmation')) {
                $table->date('date_of_confirmation')->nullable()->after('date_of_first_appointment');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'ippis_no',
                'step',
                'date_of_birth',
                'date_of_first_appointment',
                'date_of_confirmation'
            ]);
        });
    }
};
