<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // --- 1. Bank Details ---
            $table->string('beneficiary_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_type')->nullable(); // Savings/Current
            $table->string('ifsc_code')->nullable();
            $table->string('cancelled_cheque_path')->nullable(); // File Path

            // --- 2. PAN Details ---
            $table->string('pan_name')->nullable(); // Name as on PAN
            $table->string('pan_number')->nullable();
            $table->string('pan_card_path')->nullable(); // File Path

            // --- 3. GST Details ---
            $table->string('gst_number')->nullable();
            $table->string('gst_certificate_path')->nullable(); // File Path
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_profiles');
    }
};