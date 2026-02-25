<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update existing NULL values to 5
        DB::table('subjects')->whereNull('max_questions')->update(['max_questions' => 5]);
        
        // Change column to have default value of 5
        Schema::table('subjects', function (Blueprint $table) {
            $table->integer('max_questions')->default(5)->change();
        });
    }

    public function down()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->integer('max_questions')->nullable()->default(null)->change();
        });
    }
};
