<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Archiving is separate from resolving: a chat can be resolved and still
 * visible, or archived out of the way whatever its status.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_conversations', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('status');
                $table->index('archived_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            if (Schema::hasColumn('chat_conversations', 'archived_at')) {
                $table->dropIndex(['archived_at']);
                $table->dropColumn('archived_at');
            }
        });
    }
};
