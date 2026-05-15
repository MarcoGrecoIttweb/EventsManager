<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('user_login_events')) {
            return;
        }

        if (! Schema::hasColumn('user_login_events', 'source')) {
            Schema::table('user_login_events', function (Blueprint $table) {
                $table->string('source', 20)->nullable()->after('ip_address');
                $table->index(['source', 'logged_in_at']);
            });
        }

        DB::table('user_login_events')
            ->whereNull('source')
            ->update(['source' => 'laravel']);
    }

    public function down(): void
    {
        if (Schema::hasTable('user_login_events') && Schema::hasColumn('user_login_events', 'source')) {
            Schema::table('user_login_events', function (Blueprint $table) {
                $table->dropIndex(['source', 'logged_in_at']);
                $table->dropColumn('source');
            });
        }
    }
};
