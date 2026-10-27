<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table("task_active_steps", function (Blueprint $table) {
            $table->boolean("is_paused")->default(false)->after("due_date");
            $table->timestamp("paused_at")->nullable()->after("is_paused");
            $table->integer("total_paused_minutes")->default(0)->after("paused_at");
            $table->boolean("is_breached")->default(false)->after("total_paused_minutes");
            $table->timestamp("breached_at")->nullable()->after("is_breached");
            $table->timestamp("warning_sent_at")->nullable()->after("breached_at");
            $table->boolean("pause_requested")->default(false)->after("warning_sent_at");
            $table->unsignedBigInteger("pause_approved_by")->nullable()->after("pause_requested");
        });
    }
    public function down(): void {
        Schema::table("task_active_steps", function (Blueprint $table) {
            $table->dropColumn(["is_paused", "paused_at", "total_paused_minutes", "is_breached", "breached_at", "warning_sent_at", "pause_requested", "pause_approved_by"]);
        });
    }
};