<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add composite indexes that were missing and are hit on every request.
     *
     * Existing indexes (from prior migrations — NOT duplicated here):
     *   users:         (role, is_approved)
     *   subjects:      (sort_order, created_at)
     *   quiz_attempts: (user_id, created_at)
     *                  (user_id, quiz_id)
     *                  (user_id, subject_id)
     *                  (quiz_id, subject_id)
     *
     * New indexes added here:
     *   quiz_attempts: (question_id, locked)       — global lock check on every question page
     *   quiz_attempts: (quiz_id, locked, started_at) — expiry resolver on every question load
     *   quiz_attempts: (quiz_id, submitted_at)     — every ranking / statistics query
     *   questions:     (quiz_id, subject_id)        — every grid and subject page load
     *   quiz_user:     (quiz_id, user_id) UNIQUE    — rankings, analysis, subject loops
     */
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->index(['question_id', 'locked'],        'qa_question_locked_idx');
            $table->index(['quiz_id', 'locked', 'started_at'], 'qa_quiz_locked_started_idx');
            $table->index(['quiz_id', 'submitted_at'],      'qa_quiz_submitted_at_idx');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->index(['quiz_id', 'subject_id'], 'q_quiz_subject_idx');
        });

        Schema::table('quiz_user', function (Blueprint $table) {
            // quiz_user is a pivot — add a unique composite if it doesn't already have one
            $table->unique(['quiz_id', 'user_id'], 'qu_quiz_user_unique');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropIndex('qa_question_locked_idx');
            $table->dropIndex('qa_quiz_locked_started_idx');
            $table->dropIndex('qa_quiz_submitted_at_idx');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex('q_quiz_subject_idx');
        });

        Schema::table('quiz_user', function (Blueprint $table) {
            $table->dropUnique('qu_quiz_user_unique');
        });
    }
};
