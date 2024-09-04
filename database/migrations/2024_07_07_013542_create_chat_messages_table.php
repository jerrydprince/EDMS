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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('recipient_id');
            $table->text('message');

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }
    // {
    //     Schema::create('chat_messages', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('chat_user_id')->nullable(); // user ID
    //         $table->unsignedBigInteger('chat_group_id')->nullable(); // Group ID
    //         // $table->unsignedBigInteger('parent_id')->nullable();
    //         $table->string('doc')->nullable();
    //          $table->boolean('is_sent_by_admin')->default(false);
    //         $table->text('message');

    //         // $table->foreign('parent_id')->references('id')->on('chat_messages')->onDelete('cascade');
    //         $table->foreign('chat_user_id')->references('id')->on('chat_users')->onDelete('cascade');
    //         $table->foreign('chat_group_id')->references('id')->on('chat_groups')->onDelete('cascade');
    //         $table->timestamps();
    //     });
    // }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
