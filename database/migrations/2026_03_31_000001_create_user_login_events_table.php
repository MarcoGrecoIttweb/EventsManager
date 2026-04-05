<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_login_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->dateTime('logged_in_at');
            $table->string('ip_address', 45)->nullable();

            $table->index('logged_in_at');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_login_events');
    }
};

