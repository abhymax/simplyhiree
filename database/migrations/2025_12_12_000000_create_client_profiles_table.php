<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Brand Details
            $table->string('company_name')->nullable();
            $table->string('website')->nullable();
            $table->string('industry')->nullable(); // e.g., IT, Healthcare, Retail
            $table->string('company_size')->nullable(); // e.g., 10-50, 50-200
            $table->text('description')->nullable(); // "About Us"
            $table->string('logo_path')->nullable();
            
            // Contact & Billing
            $table->string('contact_person_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('gst_number')->nullable(); // Tax ID
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_profiles');
    }
};