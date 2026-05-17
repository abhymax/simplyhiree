<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_credit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('source_application_id')->constrained('job_applications')->cascadeOnDelete()
                ->comment('The failed-replacement application that triggered this credit note');
            $table->decimal('amount', 12, 2)->comment('Partner payout amount being credited back');
            $table->string('status', 20)->default('pending')
                ->comment('pending | applied | cancelled');
            $table->text('reason')->nullable();
            $table->timestamp('applied_at')->nullable()
                ->comment('Set when the credit is offset against a partner payout');
            $table->timestamps();

            $table->index(['partner_id', 'status']);
            $table->unique('source_application_id'); // one credit per failed source
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_credit_notes');
    }
};
