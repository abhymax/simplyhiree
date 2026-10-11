<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Track reads by message id, not timestamp. Timestamps are second-precision,
     * so a reply landing in the same second a user last read the thread was
     * counted as already read.
     */
    public function up(): void
    {
        Schema::table('chat_reads', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_reads', 'last_read_message_id')) {
                $table->unsignedBigInteger('last_read_message_id')->default(0)->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chat_reads', function (Blueprint $table) {
            if (Schema::hasColumn('chat_reads', 'last_read_message_id')) {
                $table->dropColumn('last_read_message_id');
            }
        });
    }
};
