<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            // PAN (Mandatory logic handled in Controller)
            $table->string('pan_number')->nullable()->after('pincode');
            $table->string('pan_file_path')->nullable()->after('pan_number');

            // TAN (Optional)
            $table->string('tan_number')->nullable()->after('pan_file_path');
            $table->string('tan_file_path')->nullable()->after('tan_number');

            // COI (Optional)
            $table->string('coi_number')->nullable()->after('tan_file_path'); // CIN Number
            $table->string('coi_file_path')->nullable()->after('coi_number');
        });
    }

    public function down(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'pan_number', 'pan_file_path',
                'tan_number', 'tan_file_path',
                'coi_number', 'coi_file_path'
            ]);
        });
    }
};