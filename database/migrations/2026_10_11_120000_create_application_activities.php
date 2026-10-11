<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who did what to an application.
 *
 * Only selection and submission were attributed before, so a client could not
 * tell which of their team shortlisted or rejected a candidate, and a vendor
 * could not tell which colleague submitted one.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('application_activities')) {
            Schema::create('application_activities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('job_application_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('actor_name')->nullable();   // kept so history survives a deleted user
                $table->string('actor_side', 20)->nullable(); // client | vendor | simplyhiree | candidate
                $table->string('action', 60);                 // shortlisted | rejected | interview_scheduled | ...
                $table->string('detail')->nullable();
                $table->timestamps();

                $table->index(['job_application_id', 'id']);
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('application_activities');
    }
};
