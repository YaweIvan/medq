<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            // started_at: set the instant the student opens the question
            $table->timestamp('started_at')->nullable()->after('expires_at');

            // is_auto_expired: true if locked by timeout rather than explicit submit
            $table->boolean('is_auto_expired')->default(false)->after('started_at');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'is_auto_expired']);
        });
    }
};
