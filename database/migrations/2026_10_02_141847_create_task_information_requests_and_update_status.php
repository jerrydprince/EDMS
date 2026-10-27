<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('task_information_requests')) {
            Schema::create('task_information_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('task')->onDelete('cascade');
                $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
                $table->foreignId('requested_from')->constrained('users')->onDelete('cascade');
                $table->text('request_message');
                $table->text('response_message')->nullable();
                $table->enum('status', ['pending', 'fulfilled'])->default('pending');
                $table->timestamps();
            });
        }

        // Add 'on_hold' to task_assignments status enum
        DB::statement("ALTER TABLE task_assignments MODIFY COLUMN status ENUM('pending', 'uncompleted', 'completed', 'review', 'revision', 'returned', 'on_hold') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_information_requests');
        
        // Note: Reverting enum is tricky if there are rows with 'on_hold'. 
        // We will just leave the enum, but if we have to revert, we would update rows first.
        DB::statement("UPDATE task_assignments SET status = 'pending' WHERE status = 'on_hold'");
        DB::statement("ALTER TABLE task_assignments MODIFY COLUMN status ENUM('pending', 'uncompleted', 'completed', 'review', 'revision', 'returned') NOT NULL DEFAULT 'pending'");
    }
};
