<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\QuizSound;
use App\Models\Subject;
use App\Services\QuestionAttemptResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizzerController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user()->fresh();
        $activeQuizzes = $user->quizzes()
            ->select('quizzes.id', 'quizzes.title', 'quizzes.is_active')
            ->where('is_active', true)
            ->get();

        return response()
            ->view('quizzer.dashboard', compact('activeQuizzes'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function quizSubjects($quizId)
    {
        $quiz = Quiz::select('id', 'title', 'is_active')->findOrFail($quizId);
        $user = Auth::user();

        if (!$user->quizzes->contains($quiz)) {
            return redirect()->route('quizzer.dashboard');
        }

        $subjects = Subject::ordered()
            ->select('subjects.id', 'subjects.name', 'subjects.max_questions', 'subjects.marks_per_question')
            ->withCount(['questions as available_questions' => function ($query) use ($quizId) {
                $query->where('quiz_id', $quizId);
            }])
            ->whereHas('questions', function ($query) use ($quizId) {
                $query->where('quiz_id', $quizId);
            })
            ->get();

        return view('quizzer.quiz_subjects', compact('quiz', 'subjects'));
    }

    public function questionGrid($quizId, $subjectId, QuestionAttemptResolver $resolver)
    {
        $quiz    = Quiz::select('id', 'title', 'lock_mode')->findOrFail($quizId);
        $subject = Subject::select('id', 'name', 'max_questions')->findOrFail($subjectId);
        $user    = Auth::user();

        if (!$user->quizzes->contains($quiz)) {
            return redirect()->route('quizzer.dashboard')
                ->with('warning', 'This quiz is no longer available.');
        }

        $resolver->resolveAllExpiredForQuiz($user->id, $quizId);

        $questions = Question::select('id', 'question', 'time_per_question')
            ->where('quiz_id', $quizId)
            ->where('subject_id', $subjectId)
            ->orderBy('id')
            ->get();

        $userAttempts = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quizId)
            ->where('subject_id', $subjectId)
            ->get()
            ->keyBy('question_id');

        $attemptedCount = $userAttempts->where('locked', true)->count();

        $openAttempt = $userAttempts
            ->filter(fn($a) => !$a->locked && $a->started_at !== null)
            ->first();

        // 7.4 — in global mode, a question is locked if ANY user has locked it
        $globalLockedIds = collect();
        if ($quiz->isGlobalLock()) {
            $questionIds = $questions->pluck('id');
            $globalLockedIds = QuizAttempt::whereIn('question_id', $questionIds)
                ->where('locked', true)
                ->pluck('question_id')
                ->unique();
        }

        return view('quizzer.quiz_grid',
            compact('quiz', 'subject', 'questions', 'userAttempts', 'attemptedCount', 'openAttempt', 'globalLockedIds'));
    }

    public function gridStatus($quizId, $subjectId, QuestionAttemptResolver $resolver)
    {
        $user = Auth::user();
        $quiz = Quiz::select('id', 'lock_mode')->findOrFail($quizId);

        $resolver->resolveAllExpiredForQuiz($user->id, $quizId);

        $attempts = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quizId)
            ->where('subject_id', $subjectId)
            ->get()
            ->keyBy('question_id');

        $questionIds = Question::where('quiz_id', $quizId)
            ->where('subject_id', $subjectId)
            ->orderBy('id')
            ->pluck('id');

        $openAttempt = $attempts
            ->filter(fn($a) => !$a->locked && $a->started_at !== null)
            ->first();

        // 7.4 — global mode: locked if any user locked it
        $globalLockedIds = collect();
        if ($quiz->isGlobalLock()) {
            $globalLockedIds = QuizAttempt::whereIn('question_id', $questionIds)
                ->where('locked', true)
                ->pluck('question_id')
                ->unique();
        }

        $status = $questionIds->map(function ($qid) use ($attempts, $openAttempt, $quiz, $globalLockedIds) {
            $attempt  = $attempts->get($qid);
            $isLocked = $quiz->isGlobalLock()
                ? $globalLockedIds->contains($qid)
                : ($attempt ? (bool) $attempt->locked : false);

            return [
                'question_id' => $qid,
                'locked'      => $isLocked,
                'open'        => $openAttempt && $openAttempt->question_id === $qid,
            ];
        })->values();

        return response()->json([
            'status'           => $status,
            'open_question_id' => $openAttempt ? $openAttempt->question_id : null,
        ]);
    }

    public function showQuestion(int $questionId, QuestionAttemptResolver $resolver)
    {
        // Eager-load quiz so $question->quiz doesn't fire a second query below
        $question = Question::with('quiz')->findOrFail($questionId);
        $user     = Auth::user();

        if (!$user->quizzes->contains($question->quiz)) {
            return redirect()->route('quizzer.dashboard')
                ->with('warning', 'This quiz is no longer available.');
        }

        $quizId = $question->quiz_id;

        $attempt = DB::transaction(function () use ($user, $question, $quizId, $questionId, $resolver) {

            // Step 1 — close any stale expired attempts for this user/quiz
            $resolver->resolveAllExpiredForQuiz($user->id, $quizId);

            // Step 2 — 7.3 global lock: block if ANY user has already locked this question
            // Reuse the already-loaded relationship instead of a fresh Quiz::find()
            $quiz = $question->quiz;
            if ($quiz && $quiz->isGlobalLock()) {
                $globallyLocked = QuizAttempt::where('question_id', $questionId)
                    ->where('locked', true)
                    ->lockForUpdate()
                    ->exists();
                if ($globallyLocked) {
                    return 'GLOBALLY_LOCKED';
                }
            }

            // Step 3 — check for a conflicting open attempt on a DIFFERENT question
            $conflicting = QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $quizId)
                ->where('locked', false)
                ->whereNotNull('started_at')
                ->where('question_id', '!=', $questionId)
                ->lockForUpdate()
                ->first();

            if ($conflicting) {
                return 'CONFLICT:' . $conflicting->question_id;
            }

            // Step 4 — fetch or create the attempt for THIS question
            $attempt = QuizAttempt::where('user_id', $user->id)
                ->where('question_id', $questionId)
                ->lockForUpdate()
                ->first();

            if ($attempt && ($attempt->locked || $attempt->submitted)) {
                return 'LOCKED';
            }

            if ($attempt && $attempt->started_at) {
                return $attempt;
            }

            return QuizAttempt::create([
                'user_id'         => $user->id,
                'quiz_id'         => $quizId,
                'subject_id'      => $question->subject_id,
                'question_id'     => $questionId,
                'started_at'      => now(),
                'expires_at'      => now()->addSeconds($question->time_per_question ?? 60),
                'locked'          => false,
                'submitted'       => false,
                'is_correct'      => false,
                'is_auto_expired' => false,
            ]);
        });

        if ($attempt === 'LOCKED') {
            return redirect()->route('quizzer.question.grid', [$question->quiz_id, $question->subject_id]);
        }

        if ($attempt === 'GLOBALLY_LOCKED') {
            return redirect()
                ->route('quizzer.question.grid', [$question->quiz_id, $question->subject_id])
                ->with('warning', 'This question has already been completed and is locked.');
        }

        if (is_string($attempt) && str_starts_with($attempt, 'CONFLICT:')) {
            return redirect()
                ->route('quizzer.question.grid', [$question->quiz_id, $question->subject_id])
                ->with('warning', 'Finish your current question first.');
        }

        $questionNumber = Question::where('quiz_id', $question->quiz_id)
            ->where('subject_id', $question->subject_id)
            ->where('id', '<=', $questionId)
            ->count();

        $serverNow = now();
        $soundUrls = QuizSound::whereIn('sound_type', ['correct', 'incorrect', 'timer', 'warning'])
            ->get()
            ->keyBy('sound_type')
            ->map(fn ($sound) => ['url' => asset($sound->file_path), 'volume' => (float) $sound->volume])
            ->all();

        return view('quizzer.question', compact('question', 'questionNumber', 'attempt', 'serverNow', 'soundUrls'));
    }

    public function submitAnswer(Request $request, QuestionAttemptResolver $resolver)
    {
        $request->validate([
            'question_id'     => 'required|exists:questions,id',
            'selected_answer' => 'nullable|in:A,B,C,D,E',
        ]);

        $question       = Question::findOrFail($request->question_id);
        $user           = Auth::user();
        $selectedAnswer = $request->selected_answer ?? null;

        if (!$user->quizzes->contains($question->quiz)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Step 1 — run lazy expiry resolver before touching anything
        $resolver->resolveExpired($user->id, $question->quiz_id);

        try {
            $result = DB::transaction(function () use ($user, $question, $selectedAnswer, $resolver) {

                $attempt = QuizAttempt::where('user_id', $user->id)
                    ->where('question_id', $question->id)
                    ->lockForUpdate()
                    ->first();

                if (!$attempt) {
                    return ['error' => 'No open attempt found', 'status' => 422];
                }

                // Already locked — idempotent response (covers race + global cascade)
                if ($attempt->locked) {
                    return [
                        'is_correct'     => (bool) $attempt->is_correct,
                        'correct_answer' => $question->correct_answer,
                        'expired'        => (bool) $attempt->is_auto_expired,
                    ];
                }

                $isCorrect    = !empty($selectedAnswer) && $question->correct_answer === $selectedAnswer;
                $subject      = Subject::find($question->subject_id);
                $scoreAwarded = $isCorrect ? ($subject->marks_per_question ?? 1) : 0;

                $attempt->update([
                    'selected_answer' => $selectedAnswer,
                    'is_correct'      => $isCorrect,
                    'score_awarded'   => $scoreAwarded,
                    'submitted'       => true,
                    'locked'          => true,
                    'submitted_at'    => now(),
                    'locked_at'       => now(),
                    'is_auto_expired' => false,
                ]);

                // 7.4 — cascade global lock after successful submission
                $resolver->cascadeGlobalLockIfNeeded($attempt->fresh());

                return [
                    'is_correct'     => $isCorrect,
                    'correct_answer' => $question->correct_answer,
                    'expired'        => false,
                ];
            });

            if (isset($result['status'])) {
                return response()->json(['error' => $result['error']], $result['status']);
            }

            return response()->json(array_merge($result, [
                'redirect_url' => route('quizzer.question.grid', [$question->quiz_id, $question->subject_id]),
            ]));

        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function statistics()
    {
        $user  = Auth::user();
        $stats = QuizAttempt::where('user_id', $user->id)
            ->selectRaw('
                quiz_id,
                COUNT(*) as total_attempts,
                SUM(is_correct) as correct_answers,
                SUM(score_awarded) as total_score,
                AVG(is_correct) * 100 as accuracy
            ')
            ->groupBy('quiz_id')
            ->with(['quiz' => function ($q) {
                $q->select('id', 'title', 'is_active');
            }])
            ->get();

        return view('quizzer.statistics', compact('stats'));
    }

    public function quizReview($quizId)
    {
        $quiz = Quiz::select('id', 'title')->findOrFail($quizId);
        $user = Auth::user();

        $subjects = Subject::select('subjects.id', 'subjects.name')
            ->whereHas('questions', function ($query) use ($quizId, $user) {
                $query->where('quiz_id', $quizId)
                      ->whereIn('id', function ($subQuery) use ($user) {
                          $subQuery->select('question_id')
                                   ->from('quiz_attempts')
                                   ->where('user_id', $user->id)
                                   ->where('submitted', true);
                      });
            })->get();

        return view('quizzer.quiz_review', compact('quiz', 'subjects'));
    }

    public function subjectReview($quizId, $subjectId)
    {
        $quiz    = Quiz::select('id', 'title')->findOrFail($quizId);
        $subject = Subject::select('id', 'name')->findOrFail($subjectId);
        $user    = Auth::user();

        // 6.7 — resolve expiry before reading own attempts
        app(QuestionAttemptResolver::class)->resolveAllExpiredForQuiz($user->id, $quizId);

        $allQuestions = Question::where('quiz_id', $quizId)
            ->where('subject_id', $subjectId)
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        $attempts = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quizId)
            ->where('subject_id', $subjectId)
            ->whereNotNull('submitted_at')
            ->with(['question' => function ($q) {
                $q->select('id', 'question', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'correct_answer');
            }])
            ->get();

        foreach ($attempts as $attempt) {
            $attempt->question_number = array_search($attempt->question_id, $allQuestions) + 1;
        }

        return view('quizzer.subject_review', compact('quiz', 'subject', 'attempts'));
    }
}
