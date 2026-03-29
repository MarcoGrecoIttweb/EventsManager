<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('partecipa')) {
            return;
        }

        if (Schema::hasColumn('partecipa', 'ospiti_inseriti_il')) {
            return;
        }

        Schema::table('partecipa', function (Blueprint $table) {
            $table->longText('ospiti_inseriti_il')->nullable()->after('amici');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('partecipa') || !Schema::hasColumn('partecipa', 'ospiti_inseriti_il')) {
            return;
        }

        Schema::table('partecipa', function (Blueprint $table) {
            $table->dropColumn('ospiti_inseriti_il');
        });
    }
};
