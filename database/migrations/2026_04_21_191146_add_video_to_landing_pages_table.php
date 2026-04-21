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
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->string('video_url')->nullable()->after('hero_image_path');
            $table->string('video_file_path')->nullable()->after('video_url');
            $table->string('video_section_title')->nullable()->after('video_file_path');
            $table->text('video_section_description')->nullable()->after('video_section_title');
        });
    }

    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropColumn(['video_url', 'video_file_path', 'video_section_title', 'video_section_description']);
        });
    }
};
