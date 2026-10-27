<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table("workflow_slas", function (Blueprint $table) {
            $table->integer("warning_hours")->nullable()->after("duration_hours");
            $table->boolean("is_working_hours_only")->default(false)->after("warning_hours");
            $table->string("escalation_action")->nullable()->after("escalation_role");
            $table->boolean("remove_original_assignee")->default(false)->after("escalation_action");
            $table->boolean("requires_pause_approval")->default(false)->after("remove_original_assignee");
        });
    }
    public function down(): void {
        Schema::table("workflow_slas", function (Blueprint $table) {
            $table->dropColumn(["warning_hours", "is_working_hours_only", "escalation_action", "remove_original_assignee", "requires_pause_approval"]);
        });
    }
};