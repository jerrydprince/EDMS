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
        Schema::table('leaves', function (Blueprint $table) {
            $table->string('leave_type')->default('annual')->after('end_date');
            $table->string('status')->default('approved')->after('leave_type');
            $table->unsignedBigInteger('relief_user_id')->nullable()->after('user_id');
            $table->string('destination')->nullable()->after('relief_user_id');
            $table->string('duty_memo_ref')->nullable()->after('destination');
            $table->unsignedBigInteger('approved_by')->nullable()->after('duty_memo_ref');
            $table->text('supervisor_comment')->nullable()->after('approved_by');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_on_official_assignment')->default(false)->after('is_on_leave');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn([
                'leave_type',
                'status',
                'relief_user_id',
                'destination',
                'duty_memo_ref',
                'approved_by',
                'supervisor_comment'
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_on_official_assignment');
        });
    }
};
