<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $table->string('current_plan', 20);
            $table->string('requested_plan', 20);
            $table->string('status', 20)->default('pending'); // pending|contacted|approved|rejected|cancelled
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('actioned_at')->nullable();
            $table->foreignId('actioned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['partner_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_change_requests');
    }
};
