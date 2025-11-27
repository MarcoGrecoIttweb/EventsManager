<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('allow_guests')->default(false)->after('max_participants');
            $table->integer('max_guests_per_user')->default(3)->after('allow_guests');
        });

        Schema::table('event_user', function (Blueprint $table) {
            $table->integer('guests_count')->default(0)->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['allow_guests', 'max_guests_per_user']);
        });

        Schema::table('event_user', function (Blueprint $table) {
            $table->dropColumn('guests_count');
        });
    }
};
