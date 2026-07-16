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
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable()->after('quiz_id')->constrained()->onDelete('cascade');
            $table->integer('score_awarded')->default(0)->after('is_correct');
            $table->boolean('submitted')->default(false)->after('score_awarded');
            $table->boolean('locked')->default(false)->after('submitted');
            $table->timestamp('submitted_at')->nullable()->after('locked');
            $table->timestamp('locked_at')->nullable()->after('submitted_at');
            $table->timestamp('expires_at')->nullable()->after('locked_at');
            
            // Add indexes for performance
            $table->index(['user_id', 'quiz_id']);
            $table->index(['user_id', 'subject_id']);
            $table->index(['quiz_id', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropIndex(['user_id', 'quiz_id']);
            $table->dropIndex(['user_id', 'subject_id']);
            $table->dropIndex(['quiz_id', 'subject_id']);
            $table->dropColumn(['subject_id', 'score_awarded', 'submitted', 'locked', 'submitted_at', 'locked_at', 'expires_at']);
        });
    }
};
