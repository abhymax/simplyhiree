<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Many-to-many: clients ↔ their preferred vendors
        Schema::create('client_preferred_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('added_at')->useCurrent();
            $table->timestamps();
            $table->unique(['client_id', 'partner_id']);
        });

        // Client-initiated vendor invites (the partner may not yet exist on the platform)
        Schema::create('client_vendor_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('company')->nullable();
            $table->string('status', 20)->default('pending'); // pending|joined|cancelled
            $table->string('invite_token', 64)->unique();
            $table->foreignId('joined_partner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->index(['client_id','status']);
        });

        // Client → "assign me N best vendors" requests, fulfilled by admin
        Schema::create('client_vendor_assignment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('vendor_count')->default(5);
            $table->string('industry_hint')->nullable();
            $table->string('location_hint')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('pending'); // pending|in_progress|fulfilled|cancelled
            $table->text('admin_notes')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->foreignId('fulfilled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['client_id','status']);
        });

        // Per-job vendor pool mode
        Schema::table('jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('jobs', 'vendor_assignment_mode')) {
                $table->string('vendor_assignment_mode', 20)->default('open')->after('partner_visibility');
                // open = all partners | preferred = client's preferred list | selected = the existing allowed_partners pivot
            }
            if (!Schema::hasColumn('jobs', 'max_vendors_per_job')) {
                $table->unsignedSmallInteger('max_vendors_per_job')->nullable()->after('vendor_assignment_mode');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_vendor_assignment_requests');
        Schema::dropIfExists('client_vendor_invitations');
        Schema::dropIfExists('client_preferred_vendors');
        Schema::table('jobs', function (Blueprint $table) {
            foreach (['vendor_assignment_mode','max_vendors_per_job'] as $col) {
                if (Schema::hasColumn('jobs', $col)) $table->dropColumn($col);
            }
        });
    }
};
