<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Internal chat between SimplyHiree and one outside party (a client, a vendor
 * or a self-registered candidate). Outside parties never talk to each other,
 * so a conversation always has exactly two sides.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chat_conversations')) {
            Schema::create('chat_conversations', function (Blueprint $table) {
                $table->id();
                $table->string('subject')->nullable();

                // The outside party. party_user_id is the ACCOUNT OWNER, so a
                // client's or vendor's team sees their organisation's thread.
                $table->string('party_type', 20);              // client | vendor | candidate
                $table->unsignedBigInteger('party_user_id');

                // Optional context, so a thread can hang off a job or application.
                $table->unsignedBigInteger('job_id')->nullable();
                $table->unsignedBigInteger('job_application_id')->nullable();

                // SimplyHiree side: a team member only sees threads assigned to them.
                $table->unsignedBigInteger('assigned_to_user_id')->nullable();

                $table->string('status', 20)->default('open'); // open | closed
                $table->timestamp('last_message_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['party_type', 'party_user_id']);
                $table->index('assigned_to_user_id');
                $table->index('last_message_at');
            });
        }

        if (!Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
                $table->unsignedBigInteger('sender_user_id');
                $table->string('sender_side', 20);             // simplyhiree | party
                $table->text('body')->nullable();
                $table->timestamps();

                $table->index(['conversation_id', 'id']);
            });
        }

        if (!Schema::hasTable('chat_attachments')) {
            Schema::create('chat_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('message_id')->constrained('chat_messages')->cascadeOnDelete();
                $table->string('path');
                $table->string('original_name');
                $table->string('mime', 120)->nullable();
                $table->unsignedBigInteger('size')->default(0);
                $table->timestamps();
            });
        }

        // One row per participant per conversation: drives unread counts.
        if (!Schema::hasTable('chat_reads')) {
            Schema::create('chat_reads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id');
                $table->timestamp('last_read_at')->nullable();
                $table->timestamps();

                $table->unique(['conversation_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_reads');
        Schema::dropIfExists('chat_attachments');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
    }
};
