<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_key', 120)->index();
            $table->string('title');
            $table->text('message');
            $table->string('icon', 50)->default('check-circle');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name')->nullable();
            $table->nullableMorphs('subject');
            $table->json('metadata')->nullable();
            $table->string('whatsapp_status', 20)->default('skipped');
            $table->text('whatsapp_last_error')->nullable();
            $table->timestamp('occurred_at')->useCurrent()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};
