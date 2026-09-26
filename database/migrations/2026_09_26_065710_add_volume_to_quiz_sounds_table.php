<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_sounds', function (Blueprint $table) {
            // Volume level 0.0 – 1.0, default 0.7 for timer, 0.8 for others
            $table->decimal('volume', 3, 2)->default(0.80)->after('file_size');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_sounds', function (Blueprint $table) {
            $table->dropColumn('volume');
        });
    }
};
