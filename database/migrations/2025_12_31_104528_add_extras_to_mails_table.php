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
            $table->string('from_email')->nullable()->after('subject');
            $table->string('from_name')->nullable()->after('from_email');
            $table->text('body_html')->nullable()->after('from_name');
            $table->timestamp('received_at')->nullable()->after('body');
            $table->boolean('has_attachments')->default(false)->after('received_at');
            $table->text('body_text')->nullable()->after('body');
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
