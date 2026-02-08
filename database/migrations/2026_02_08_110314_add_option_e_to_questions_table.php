<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('option_e')->nullable()->after('option_d');
        });
        
        // Update the correct_answer enum to include 'E'
        DB::statement("ALTER TABLE questions MODIFY correct_answer ENUM('A', 'B', 'C', 'D', 'E')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('option_e');
        });
        
        // Revert the correct_answer enum back to A-D only
        DB::statement("ALTER TABLE questions MODIFY correct_answer ENUM('A', 'B', 'C', 'D')");
    }
};
