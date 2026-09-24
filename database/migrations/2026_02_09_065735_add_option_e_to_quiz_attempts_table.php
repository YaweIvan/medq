<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (\DB::getDriverName() === 'mysql') {
            // Modify the enum to include option E
            \DB::statement("ALTER TABLE quiz_attempts MODIFY COLUMN selected_answer ENUM('A', 'B', 'C', 'D', 'E') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\DB::getDriverName() === 'mysql') {
            // Revert back to original enum without E
            \DB::statement("ALTER TABLE quiz_attempts MODIFY COLUMN selected_answer ENUM('A', 'B', 'C', 'D') NOT NULL");
        }
    }
};
