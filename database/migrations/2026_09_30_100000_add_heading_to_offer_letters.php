<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('offer_letters', fn (Blueprint $t) => $t->string('heading')->nullable()->after('subject'));
        Schema::table('offer_letter_templates', fn (Blueprint $t) => $t->string('default_heading')->nullable()->after('subject'));
    }
    public function down(): void {
        Schema::table('offer_letters', fn (Blueprint $t) => $t->dropColumn('heading'));
        Schema::table('offer_letter_templates', fn (Blueprint $t) => $t->dropColumn('default_heading'));
    }
};
