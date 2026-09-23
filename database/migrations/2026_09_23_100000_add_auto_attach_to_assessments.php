<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('assessments', function (Blueprint $t) {
            // When true, this questionnaire is auto-attached as a stage to every
            // new job posted by its owning client (or the assigned client).
            $t->boolean('auto_attach')->default(false)->after('is_global');
        });
    }
    public function down(): void {
        Schema::table('assessments', fn (Blueprint $t) => $t->dropColumn('auto_attach'));
    }
};
