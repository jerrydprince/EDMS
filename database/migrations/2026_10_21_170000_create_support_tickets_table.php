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
        if (!Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_no', 32)->unique();
                $table->unsignedBigInteger('user_id');
                $table->string('category', 64);
                $table->string('subject', 255);
                $table->text('description');
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->string('attachment_path', 255)->nullable();
                $table->string('attachment_name', 255)->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['user_id', 'status'], 'idx_tickets_user_status');
                $table->index(['status', 'priority'], 'idx_tickets_status_priority');
            });
        }

        // Add category column to faqs if not present
        if (Schema::hasTable('faqs') && !Schema::hasColumn('faqs', 'category')) {
            Schema::table('faqs', function (Blueprint $table) {
                $table->string('category', 64)->default('General')->after('id');
            });
        }

        // Add category column to documentations if not present
        if (Schema::hasTable('documentations') && !Schema::hasColumn('documentations', 'category')) {
            Schema::table('documentations', function (Blueprint $table) {
                $table->string('category', 64)->default('Guides & SOPs')->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');

        if (Schema::hasTable('faqs') && Schema::hasColumn('faqs', 'category')) {
            Schema::table('faqs', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }

        if (Schema::hasTable('documentations') && Schema::hasColumn('documentations', 'category')) {
            Schema::table('documentations', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }
};
