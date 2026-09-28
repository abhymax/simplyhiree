<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_letter_templates', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('subject')->nullable();
            $t->longText('body_html');
            $t->boolean('is_active')->default(true);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('offer_letters', function (Blueprint $t) {
            $t->id();
            $t->foreignId('job_application_id')->nullable()->constrained('job_applications')->nullOnDelete();
            $t->unsignedBigInteger('candidate_id')->nullable();
            $t->unsignedBigInteger('candidate_user_id')->nullable();
            $t->foreignId('template_id')->nullable()->constrained('offer_letter_templates')->nullOnDelete();
            $t->string('candidate_name')->nullable();
            $t->string('candidate_email')->nullable();
            $t->string('job_title')->nullable();
            $t->string('company_name')->nullable();
            $t->string('subject')->nullable();
            $t->longText('body_html');
            $t->string('pdf_path')->nullable();
            $t->string('status', 20)->default('draft'); // draft | sent
            $t->timestamp('sent_at')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index('status');
        });

        Schema::create('offer_letter_settings', function (Blueprint $t) {
            $t->id();
            $t->string('director_name')->nullable();
            $t->string('director_designation')->nullable();
            $t->string('signature_path')->nullable();
            $t->string('logo_path')->nullable();
            $t->text('company_address')->nullable();
            $t->text('company_footer')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_letters');
        Schema::dropIfExists('offer_letter_templates');
        Schema::dropIfExists('offer_letter_settings');
    }
};
