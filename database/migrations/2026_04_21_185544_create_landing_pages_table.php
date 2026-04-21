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
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('status', ['draft', 'published'])->default('draft');

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Branding
            $table->string('logo_path')->nullable();
            $table->string('primary_color')->default('#4f46e5');
            $table->string('secondary_color')->default('#2563eb');

            // Hero Section
            $table->string('hero_headline');
            $table->text('hero_subheadline')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->string('cta_text')->default('Reserve My FREE Slot!');

            // Event Details
            $table->date('event_date')->nullable();
            $table->string('event_time')->nullable();
            $table->string('event_platform')->nullable();
            $table->string('event_language')->nullable();
            $table->integer('seats_total')->default(0);
            $table->datetime('registration_deadline')->nullable();

            // About Section
            $table->string('about_title')->nullable();
            $table->text('about_description')->nullable();

            // Host / Presenter
            $table->string('host_name')->nullable();
            $table->string('host_title')->nullable();
            $table->text('host_bio')->nullable();
            $table->string('host_photo_path')->nullable();

            // JSON Sections
            $table->json('learnings')->nullable();
            $table->json('qualifications')->nullable();
            $table->json('benefits')->nullable();
            $table->json('faqs')->nullable();

            // Form Configuration
            $table->json('form_fields')->nullable();

            // Footer
            $table->text('footer_disclaimer')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_pages');
    }
};
