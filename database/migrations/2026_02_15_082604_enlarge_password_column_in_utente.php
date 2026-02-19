<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class EnlargePasswordColumnInUtente extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE utente MODIFY COLUMN password VARCHAR(255) NOT NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE utente MODIFY COLUMN password VARCHAR(32) NOT NULL');
    }
}
