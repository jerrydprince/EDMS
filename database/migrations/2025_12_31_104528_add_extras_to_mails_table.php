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
        Schema::table('mails', function (Blueprint $table) {
            if (!Schema::hasColumn('mails', 'from_email')) {
                $table->string('from_email')->nullable()->after('subject');
            }
            if (!Schema::hasColumn('mails', 'from_name')) {
                $table->string('from_name')->nullable()->after('from_email');
            }
            if (!Schema::hasColumn('mails', 'body_html')) {
                $table->text('body_html')->nullable()->after('from_name');
            }
            if (!Schema::hasColumn('mails', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('body');
            }
            if (!Schema::hasColumn('mails', 'has_attachments')) {
                $table->boolean('has_attachments')->default(false)->after('received_at');
            }
            if (!Schema::hasColumn('mails', 'body_text')) {
                $table->text('body_text')->nullable()->after('body');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            //
        });
    }
};
