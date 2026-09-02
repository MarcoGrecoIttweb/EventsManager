<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_page_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('visits_count')->default(1);
            $table->timestamp('first_visited_at')->nullable();
            $table->timestamp('last_visited_at')->nullable();

            $table->unique(['event_id', 'user_id'], 'event_page_visits_event_user_unique');
            $table->index('event_id', 'event_page_visits_event_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_page_visits');
    }
};
