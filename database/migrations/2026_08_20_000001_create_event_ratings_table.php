<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comment')->nullable(); // privato: visibile solo a organizzatore/admin
            $table->timestamps();

            $table->unique(['event_id', 'user_id'], 'event_ratings_event_user_unique');
            $table->index('event_id', 'event_ratings_event_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_ratings');
    }
};
