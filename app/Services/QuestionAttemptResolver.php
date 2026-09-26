<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\DB;

class QuestionAttemptResolver
{
    /**
     * Resolve a single expired attempt for a user/quiz.
     * Safe to call on every request — no-ops if nothing is expired.
     */
    public function resolveExpired(int $userId, int $quizId): void
    {
        DB::transaction(function () use ($userId, $quizId) {
            $attempt = QuizAttempt::where('user_id', $userId)
                ->where('quiz_id', $quizId)
                ->where('locked', false)
                ->whereNotNull('started_at')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
                ->lockForUpdate()
                ->first();

            if ($attempt) {
                $this->closeExpiredAttempt($attempt);
                $this->cascadeGlobalLockIfNeeded($attempt);
            }
        });
    }

    /**
     * Resolve ALL expired attempts for a user/quiz.
     * Safe to call on every request — no-ops if nothing is expired.
     */
    public function resolveAllExpiredForQuiz(int $userId, int $quizId): void
    {
        DB::transaction(function () use ($userId, $quizId) {
            $attempts = QuizAttempt::where('user_id', $userId)
                ->where('quiz_id', $quizId)
                ->where('locked', false)
                ->whereNotNull('started_at')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
                ->lockForUpdate()
                ->get();

            foreach ($attempts as $attempt) {
                $this->closeExpiredAttempt($attempt);
                $this->cascadeGlobalLockIfNeeded($attempt);
            }
        });
    }

    /**
     * After any attempt is locked, if the quiz is in global mode,
     * close all other users' still-open attempts on the same question.
     * Must be called inside an existing transaction.
     */
    public function cascadeGlobalLockIfNeeded(QuizAttempt $lockedAttempt): void
    {
        // Cache the quiz per quiz_id within this request so repeated calls
        // (e.g. resolving multiple expired attempts in one transaction) only
        // hit the DB once instead of once per attempt.
        static $quizCache = [];

        $quizId = $lockedAttempt->quiz_id;
        if (!array_key_exists($quizId, $quizCache)) {
            $quizCache[$quizId] = Quiz::find($quizId);
        }
        $quiz = $quizCache[$quizId];

        if (!$quiz || !$quiz->isGlobalLock()) {
            return;
        }

        // Close every other open attempt on this same question (any user)
        $others = QuizAttempt::where('question_id', $lockedAttempt->question_id)
            ->where('id', '!=', $lockedAttempt->id)
            ->where('locked', false)
            ->lockForUpdate()
            ->get();

        foreach ($others as $other) {
            $other->update([
                'locked'          => true,
                'submitted'       => true,
                'is_auto_expired' => true,
                'submitted_at'    => now(),
                'locked_at'       => now(),
                // Leave selected_answer and is_correct as-is — don't fabricate a wrong answer
            ]);
        }
    }

    private function closeExpiredAttempt(QuizAttempt $attempt): void
    {
        $attempt->update([
            'selected_answer' => $attempt->selected_answer ?: null,
            'is_correct'      => false,
            'submitted_at'    => now(),
            'locked_at'       => now(),
            'locked'          => true,
            'submitted'       => true,
            'is_auto_expired' => true,
        ]);
    }
}
