<?php
/**
 * Phase 2 — QuestionAttemptResolver acceptance test
 * Run with: php artisan tinker --execute="require base_path('tests/phase2_resolver_test.php');"
 */

use App\Models\QuizAttempt;
use App\Services\QuestionAttemptResolver;

$resolver = new QuestionAttemptResolver();

// Use a fake user/quiz ID that won't collide with real data
$userId = 99999;
$quizId = 99999;

// Clean up any leftover rows from a previous run
QuizAttempt::where('user_id', $userId)->delete();

echo "\n=== Phase 2 Resolver Tests ===\n\n";

// -----------------------------------------------------------------------
// Test 1: expired attempt gets closed out
// -----------------------------------------------------------------------
$expired = QuizAttempt::create([
    'user_id'        => $userId,
    'quiz_id'        => $quizId,
    'question_id'    => 1,
    'started_at'     => now()->subMinutes(2),
    'expires_at'     => now()->subMinute(),
    'locked'         => false,
    'is_correct'     => false,
    'is_auto_expired'=> false,
    'submitted'      => false,
    'selected_answer'=> null,
]);

$resolver->resolveExpired($userId, $quizId);
$expired->refresh();

$t1_locked        = $expired->locked === true;
$t1_auto_expired  = $expired->is_auto_expired === true;
$t1_submitted_at  = $expired->submitted_at !== null;
$t1_is_correct    = $expired->is_correct === false;

echo "Test 1 — expired attempt is closed:\n";
echo "  locked = true          : " . ($t1_locked       ? "PASS" : "FAIL") . "\n";
echo "  is_auto_expired = true : " . ($t1_auto_expired  ? "PASS" : "FAIL") . "\n";
echo "  submitted_at set       : " . ($t1_submitted_at  ? "PASS" : "FAIL") . "\n";
echo "  is_correct = false     : " . ($t1_is_correct    ? "PASS" : "FAIL") . "\n\n";

// -----------------------------------------------------------------------
// Test 2: future attempt is NOT touched
// -----------------------------------------------------------------------
$future = QuizAttempt::create([
    'user_id'        => $userId,
    'quiz_id'        => $quizId,
    'question_id'    => 1,
    'started_at'     => now(),
    'expires_at'     => now()->addMinutes(5),
    'locked'         => false,
    'is_correct'     => false,
    'is_auto_expired'=> false,
    'submitted'      => false,
    'selected_answer'=> null,
]);

$resolver->resolveExpired($userId, $quizId);
$future->refresh();

$t2_still_unlocked = $future->locked === false;
$t2_not_expired    = $future->is_auto_expired === false;

echo "Test 2 — future attempt is untouched:\n";
echo "  still locked = false       : " . ($t2_still_unlocked ? "PASS" : "FAIL") . "\n";
echo "  is_auto_expired = false    : " . ($t2_not_expired    ? "PASS" : "FAIL") . "\n\n";

// Clean up test 2 row
$future->delete();

// -----------------------------------------------------------------------
// Test 3: no attempt at all — no error
// -----------------------------------------------------------------------
$noErrorThrown = true;
try {
    $resolver->resolveExpired($userId + 1, $quizId + 1);
} catch (\Throwable $e) {
    $noErrorThrown = false;
    echo "  Exception: " . $e->getMessage() . "\n";
}

echo "Test 3 — no attempt exists, no error:\n";
echo "  no exception thrown        : " . ($noErrorThrown ? "PASS" : "FAIL") . "\n\n";

// -----------------------------------------------------------------------
// Test 4: resolveAllExpiredForQuiz closes multiple expired rows
// -----------------------------------------------------------------------
QuizAttempt::where('user_id', $userId)->delete();

$rows = [];
for ($i = 0; $i < 3; $i++) {
    $rows[] = QuizAttempt::create([
        'user_id'        => $userId,
        'quiz_id'        => $quizId,
        'question_id'    => $i + 1,
        'started_at'     => now()->subMinutes(3),
        'expires_at'     => now()->subMinutes(1),
        'locked'         => false,
        'is_correct'     => false,
        'is_auto_expired'=> false,
        'submitted'      => false,
        'selected_answer'=> null,
    ]);
}

$resolver->resolveAllExpiredForQuiz($userId, $quizId);

$allClosed = true;
foreach ($rows as $row) {
    $row->refresh();
    if (!$row->locked || !$row->is_auto_expired || $row->submitted_at === null) {
        $allClosed = false;
    }
}

echo "Test 4 — resolveAllExpiredForQuiz closes all 3 expired rows:\n";
echo "  all rows closed            : " . ($allClosed ? "PASS" : "FAIL") . "\n\n";

// -----------------------------------------------------------------------
// Cleanup
// -----------------------------------------------------------------------
QuizAttempt::where('user_id', $userId)->delete();

echo "=== Done. Cleanup complete. ===\n\n";
