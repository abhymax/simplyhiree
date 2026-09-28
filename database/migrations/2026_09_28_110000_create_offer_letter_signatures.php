<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_letter_signatures', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('designation')->nullable();
            $t->string('signature_path')->nullable();
            $t->boolean('is_active')->default(true);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::table('offer_letters', function (Blueprint $t) {
            $t->string('signatory_name')->nullable()->after('company_name');
            $t->string('signatory_designation')->nullable()->after('signatory_name');
            $t->string('signatory_signature_path')->nullable()->after('signatory_designation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_letter_signatures');
        Schema::table('offer_letters', fn (Blueprint $t) => $t->dropColumn(['signatory_name','signatory_designation','signatory_signature_path']));
    }
};
