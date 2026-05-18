<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('rated_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('job_id')->nullable()->constrained('jobs')->nullOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('job_applications')->nullOnDelete();
            $table->unsignedTinyInteger('score');                // 1..5 overall
            $table->unsignedTinyInteger('speed_score')->nullable();
            $table->unsignedTinyInteger('quality_score')->nullable();
            $table->unsignedTinyInteger('communication_score')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->index(['partner_id', 'created_at']);
            $table->unique('application_id'); // one rating per hire
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'avg_rating')) {
                $table->decimal('avg_rating', 3, 2)->nullable()->after('partner_plan');
            }
            if (!Schema::hasColumn('users', 'total_ratings')) {
                $table->unsignedInteger('total_ratings')->default(0)->after('avg_rating');
            }
            if (!Schema::hasColumn('users', 'selection_ratio')) {
                $table->decimal('selection_ratio', 5, 4)->nullable()->after('total_ratings');
            }
            if (!Schema::hasColumn('users', 'closure_rate')) {
                $table->decimal('closure_rate', 5, 4)->nullable()->after('selection_ratio');
            }
            if (!Schema::hasColumn('users', 'repeat_hire_count')) {
                $table->unsignedInteger('repeat_hire_count')->default(0)->after('closure_rate');
            }
            if (!Schema::hasColumn('users', 'vendor_badge')) {
                $table->string('vendor_badge', 30)->nullable()->after('repeat_hire_count');
                // 'Rising Talent' | 'Top Recruiter' | 'Elite Partner' | 'Trusted Vendor'
            }
            if (!Schema::hasColumn('users', 'vendor_level')) {
                $table->string('vendor_level', 20)->default('Basic')->after('vendor_badge');
                // 'Elite' | 'Pro' | 'Basic' | 'Restricted'
            }
            if (!Schema::hasColumn('users', 'penalty_active')) {
                $table->boolean('penalty_active')->default(false)->after('vendor_level');
            }
            if (!Schema::hasColumn('users', 'penalty_reason')) {
                $table->string('penalty_reason')->nullable()->after('penalty_active');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_ratings');
        Schema::table('users', function (Blueprint $table) {
            foreach (['avg_rating','total_ratings','selection_ratio','closure_rate','repeat_hire_count','vendor_badge','vendor_level','penalty_active','penalty_reason'] as $col) {
                if (Schema::hasColumn('users', $col)) $table->dropColumn($col);
            }
        });
    }
};
