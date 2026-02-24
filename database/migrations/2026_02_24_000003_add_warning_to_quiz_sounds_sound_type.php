<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // For MySQL, we need to alter the enum to add 'warning'
        DB::statement("ALTER TABLE quiz_sounds MODIFY COLUMN sound_type ENUM('correct', 'incorrect', 'timer', 'warning')");
    }

    public function down()
    {
        DB::statement("ALTER TABLE quiz_sounds MODIFY COLUMN sound_type ENUM('correct', 'incorrect', 'timer')");
    }
};
