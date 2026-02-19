<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ─── New Laravel-only tables ──────────────────────────────

        if (!Schema::hasTable('event_images')) {
            Schema::create('event_images', function (Blueprint $table) {
                $table->id();
                $table->integer('event_id')->unsigned();
                $table->string('filename');
                $table->string('path');
                $table->string('original_name');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('size')->nullable();
                $table->integer('order')->default(0);
                $table->boolean('is_cover')->default(false);
                $table->timestamps();

                $table->index('event_id');
            });
        }

        if (!Schema::hasTable('password_resets')) {
            Schema::create('password_resets', function (Blueprint $table) {
                $table->string('email')->index();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->morphs('tokenable');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }

        // ─── Add Laravel-specific columns to legacy tables ────────

        // Temporarily disable strict mode for legacy tables with 0000-00-00 defaults
        \DB::statement("SET SESSION sql_mode = ''");

        // Add allow_guests and max_guests_per_user to evento
        if (!Schema::hasColumn('evento', 'allow_guests')) {
            \DB::statement("ALTER TABLE `evento` ADD `allow_guests` TINYINT(1) NOT NULL DEFAULT 1 AFTER `pubblicato`, ADD `max_guests_per_user` INT NOT NULL DEFAULT 10 AFTER `allow_guests`");
        }

        // Add edited_at to post (for comment editing feature)
        if (!Schema::hasColumn('post', 'edited_at')) {
            \DB::statement("ALTER TABLE `post` ADD `edited_at` TIMESTAMP NULL DEFAULT NULL AFTER `data`");
        }

        // Re-enable strict mode
        \DB::statement("SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    }

    public function down()
    {
        Schema::dropIfExists('event_images');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('failed_jobs');

        if (Schema::hasColumn('evento', 'allow_guests')) {
            Schema::table('evento', function (Blueprint $table) {
                $table->dropColumn(['allow_guests', 'max_guests_per_user']);
            });
        }

        if (Schema::hasColumn('post', 'edited_at')) {
            Schema::table('post', function (Blueprint $table) {
                $table->dropColumn('edited_at');
            });
        }
    }
};
