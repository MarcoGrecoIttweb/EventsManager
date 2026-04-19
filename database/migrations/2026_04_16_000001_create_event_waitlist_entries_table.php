<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_waitlist_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id');
            $table->string('email', 190)->nullable();
            $table->string('display_name', 190)->nullable();
            $table->string('status', 30)->default('pending'); // pending|notified
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'status', 'created_at'], 'ewl_event_status_created_idx');
            $table->unique(['event_id', 'user_id'], 'ewl_event_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_waitlist_entries');
    }
};

