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
        Schema::create('client_commercials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('The Client');
            $table->string('billing_type')->default('percentage_based')->comment('percentage_based, profile_wise, flat');
            $table->json('contract_data')->nullable()->comment('Stores the slabs or profiles as JSON');
            $table->integer('invoice_raise_days')->default(30);
            $table->integer('payment_terms_days')->default(30);
            $table->boolean('is_gst_applicable')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_commercials');
    }
};
