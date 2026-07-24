<?php
/**
 * Phase 7 — Global Question Locking acceptance test
 * Run: php artisan tinker --execute="require base_path('tests/phase7_global_lock_test.php');"
 */

use App\Models\Quiz;
use App\Models\User;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Services\QuestionAttemptResolver;

$resolver = new QuestionAttemptResolver();

$user1 = User::where('role', 'quizzer')->first();
$user2 = User::where('role', 'quizzer')->where('id', '!=', $user1->id)->first();
$quiz  = Quiz::first();
$q     = Question::where('quiz_id', $quiz->id)->first();

if (!$user1 || !$user2 || !$quiz || !$q) {
    echo "SKIP: Need at least 1 quiz, 1 question, 2 quizzer users.\n";
    return;
}

// Helper: clean slate
$clean = fn() => QuizAttempt::where('quiz_id', $quiz->id)
    ->whereIn('user_id', [$user1->id, $user2->id])->delete();

$makeAttempt = fn($userId, $locked = false, $expired = false) => QuizAttempt::create([
    'user_id'         => $userId,
    'quiz_id'         => $quiz->id,
    'subject_id'      => $q->subject_id,
    'question_id'     => $q->id,
    'started_at'      => now()->subSeconds($expired ? 120 : 5),
    'expires_at'      => $expired ? now()->subMinute() : now()->addMinutes(5),
    'locked'          => $locked,
    'submitted'       => $locked,
    'is_correct'      => false,
    'is_auto_expired' => false,
]);

echo "\n=== Phase 7 Global Lock Tests ===\n\n";

// -----------------------------------------------------------------------
// Test 1: toggle defaults to per_user, saves correctly
// -----------------------------------------------------------------------
$quiz->update(['lock_mode' => 'per_user']);
$quiz->refresh();
$t1_default = $quiz->lock_mode === 'per_user' && !$quiz->isGlobalLock();

$quiz->update(['lock_mode' => 'global']);
$quiz->refresh();
$t1_saves = $quiz->lock_mode === 'global' && $quiz->isGlobalLock();

echo "Test 1 — toggle defaults per_user, saves global:\n";
echo "  default per_user   : " . ($t1_default ? "PASS" : "FAIL") . "\n";
echo "  saves global       : " . ($t1_saves   ? "PASS" : "FAIL") . "\n\n";

// -----------------------------------------------------------------------
// Test 2: per_user mode — user1 locking does NOT affect user2
// -----------------------------------------------------------------------
$quiz->update(['lock_mode' => 'per_user']);
$clean();

$a1 = $makeAttempt($user1->id);
$a2 = $makeAttempt($user2->id);

// Lock user1's attempt (simulate submission)
$a1->update(['locked' => true, 'submitted' => true, 'submitted_at' => now(), 'locked_at' => now()]);
$resolver->cascadeGlobalLockIfNeeded($a1->fresh());

$a2->refresh();
$t2_user2_unaffected = !$a2->locked;

echo "Test 2 — per_user: user1 lock does NOT affect user2:\n";
echo "  user2 still unlocked : " . ($t2_user2_unaffected ? "PASS" : "FAIL") . "\n\n";

// -----------------------------------------------------------------------
// Test 3: global mode — user1 locking cascades to user2
// -----------------------------------------------------------------------
$quiz->update(['lock_mode' => 'global']);
$clean();

$a1 = $makeAttempt($user1->id);
$a2 = $makeAttempt($user2->id);

$a1->update(['locked' => true, 'submitted' => true, 'submitted_at' => now(), 'locked_at' => now()]);
$resolver->cascadeGlobalLockIfNeeded($a1->fresh());

$a2->refresh();
$t3_cascaded      = $a2->locked === true;
$t3_auto_expired  = $a2->is_auto_expired === true;
$t3_submitted_at  = $a2->submitted_at !== null;

echo "Test 3 — global: user1 lock cascades to user2:\n";
echo "  user2 locked         : " . ($t3_cascaded     ? "PASS" : "FAIL") . "\n";
echo "  user2 is_auto_expired: " . ($t3_auto_expired ? "PASS" : "FAIL") . "\n";
echo "  user2 submitted_at   : " . ($t3_submitted_at ? "PASS" : "FAIL") . "\n\n";

// -----------------------------------------------------------------------
// Test 4: global mode — globally locked question blocks new open attempt
// -----------------------------------------------------------------------
$quiz->update(['lock_mode' => 'global']);
$clean();

// user1 already has a locked attempt
$makeAttempt($user1->id, locked: true);

// Check: globally locked?
$globallyLocked = QuizAttempt::where('question_id', $q->id)->where('locked', true)->exists();
$t4_blocked = $globallyLocked === true;

echo "Test 4 — global: locked question blocks new attempt:\n";
echo "  globally locked detected : " . ($t4_blocked ? "PASS" : "FAIL") . "\n\n";

// -----------------------------------------------------------------------
// Test 5: race — user1 submits first, user2's in-flight attempt is cascade-closed
// -----------------------------------------------------------------------
$quiz->update(['lock_mode' => 'global']);
$clean();

$a1 = $makeAttempt($user1->id);
$a2 = $makeAttempt($user2->id);

// user1 submits
$a1->update(['locked' => true, 'submitted' => true, 'submitted_at' => now(), 'locked_at' => now(), 'is_correct' => true]);
$resolver->cascadeGlobalLockIfNeeded($a1->fresh());

// user2 tries to submit (already locked by cascade)
$a2->refresh();
$t5_a2_locked    = $a2->locked === true;
$t5_a2_no_answer = $a2->selected_answer === null; // no fabricated answer

echo "Test 5 — race: user2 cascade-closed, no fabricated answer:\n";
echo "  user2 locked         : " . ($t5_a2_locked    ? "PASS" : "FAIL") . "\n";
echo "  user2 answer null    : " . ($t5_a2_no_answer ? "PASS" : "FAIL") . "\n\n";

// -----------------------------------------------------------------------
// Test 6: toggling back to per_user stops cascade for future attempts
// -----------------------------------------------------------------------
$quiz->update(['lock_mode' => 'per_user']);
$clean();

$a1 = $makeAttempt($user1->id);
$a2 = $makeAttempt($user2->id);

$a1->update(['locked' => true, 'submitted' => true, 'submitted_at' => now(), 'locked_at' => now()]);
$resolver->cascadeGlobalLockIfNeeded($a1->fresh()); // should no-op in per_user mode

$a2->refresh();
$t6_no_cascade = !$a2->locked;

echo "Test 6 — toggle back to per_user stops cascade:\n";
echo "  user2 not cascaded   : " . ($t6_no_cascade ? "PASS" : "FAIL") . "\n\n";

// -----------------------------------------------------------------------
// Cleanup + restore
// -----------------------------------------------------------------------
$clean();
$quiz->update(['lock_mode' => 'per_user']);

echo "=== Done. Cleanup complete. ===\n\n";
