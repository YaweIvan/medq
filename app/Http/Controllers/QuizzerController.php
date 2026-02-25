<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizzerController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user()->fresh(['quizzes']); // Fresh data from database with relationships
        $activeQuizzes = $user->quizzes()->where('is_active', true)->get();
        
        return response()
            ->view('quizzer.dashboard', compact('activeQuizzes'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function quizSubjects($quizId)
    {
        $quiz = Quiz::findOrFail($quizId);
        $user = Auth::user();

        if (!$user->quizzes->contains($quiz)) {
            abort(403, 'Not authorized for this quiz.');
        }

        $subjects = Subject::whereHas('questions', function($query) use ($quizId) {
            $query->where('quiz_id', $quizId);
        })->select('subjects.*')
        ->withCount(['questions as available_questions' => function($query) use ($quizId, $user) {
            $query->where('quiz_id', $quizId)
                  ->where('is_used', false)
                  ->whereNotIn('id', function($subQuery) use ($user) {
                      $subQuery->select('question_id')
                               ->from('quiz_attempts')
                               ->where('user_id', $user->id);
                  });
        }])->get();

        return view('quizzer.quiz_subjects', compact('quiz', 'subjects'));
    }
    
    public function questionGrid($quizId, $subjectId)
    {
        $quiz = Quiz::findOrFail($quizId);
        $subject = Subject::findOrFail($subjectId);
        $user = Auth::user();
        
        // Check if user has an active timer running
        $activeTimerKey = "quiz_timer_start_";
        $hasActiveTimer = false;
        foreach (array_keys($_COOKIE) as $key) {
            if (strpos($key, $activeTimerKey) === 0) {
                $questionId = str_replace($activeTimerKey, '', $key);
                $timerStart = $_COOKIE[$key] ?? null;
                if ($timerStart) {
                    $elapsed = time() - ($timerStart / 1000);
                    if ($elapsed < 60) {
                        $hasActiveTimer = true;
                        break;
                    }
                }
            }
        }
        
        $questions = Question::where('quiz_id', $quizId)
            ->where('subject_id', $subjectId)
            ->get();
            
        $attemptedQuestions = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quizId)
            ->whereHas('question', function($query) use ($subjectId) {
                $query->where('subject_id', $subjectId);
            })
            ->pluck('question_id');
            
        $attemptedCount = $attemptedQuestions->count();
        
        return view('quizzer.quiz_grid', compact('quiz', 'subject', 'questions', 'attemptedQuestions', 'attemptedCount', 'hasActiveTimer'));
    }

    public function questions($quizId, $subjectId)
    {
        $user = Auth::user();
        $quiz = Quiz::findOrFail($quizId);
        $subject = Subject::findOrFail($subjectId);

        // Get max questions for this subject (default to 5 if not set)
        $maxQuestions = $subject->max_questions ?? 5;

        // Check attempts for this subject
        $attemptCount = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quizId)
            ->whereHas('question', function($query) use ($subjectId) {
                $query->where('subject_id', $subjectId);
            })->count();

        if ($attemptCount >= $maxQuestions) {
            return redirect()->back()->with('error', "Maximum {$maxQuestions} questions per subject reached.");
        }

        // Get available questions
        $questions = Question::where('quiz_id', $quizId)
            ->where('subject_id', $subjectId)
            ->where('is_used', false)
            ->whereNotIn('id', function($query) use ($user) {
                $query->select('question_id')
                      ->from('quiz_attempts')
                      ->where('user_id', $user->id);
            })
            ->inRandomOrder()
            ->take($maxQuestions - $attemptCount)
            ->get();

        return view('quizzer.questions', compact('quiz', 'subject', 'questions'));
    }

    public function showQuestion($questionId)
    {
        $question = Question::findOrFail($questionId);
        $user = Auth::user();
        
        // Check if user has access to this quiz
        if (!$user->quizzes->contains($question->quiz)) {
            abort(403, 'Not authorized for this quiz.');
        }
        
        // Check if question already attempted
        $attempted = QuizAttempt::where('user_id', $user->id)
            ->where('question_id', $questionId)
            ->exists();
            
        if ($attempted) {
            return redirect()->back()->with('error', 'Question already attempted.');
        }
        
        // Check if user has another active timer running for a DIFFERENT question
        $timerStartKey = "quiz_timer_start_";
        if (isset($_COOKIE)) {
            foreach (array_keys($_COOKIE) as $key) {
                if (strpos($key, $timerStartKey) === 0) {
                    $otherQuestionId = str_replace($timerStartKey, '', $key);
                    if ($otherQuestionId != $questionId) {
                        $timerStart = $_COOKIE[$key] ?? null;
                        if ($timerStart) {
                            $elapsed = time() - ($timerStart / 1000);
                            if ($elapsed < 60) {
                                return redirect()->back()->with('error', 'Please complete the current question first.');
                            }
                        }
                    }
                }
            }
        }
        
        // Get question number within subject
        $questionNumber = Question::where('quiz_id', $question->quiz_id)
            ->where('subject_id', $question->subject_id)
            ->where('id', '<=', $questionId)
            ->count();
        
        return view('quizzer.question', compact('question', 'questionNumber'));
    }

    public function submitAnswer(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'selected_answer' => 'nullable|in:A,B,C,D,E',
            'time_taken' => 'nullable|integer',
        ]);

        $question = Question::findOrFail($request->question_id);
        $selectedAnswer = $request->selected_answer ?? '';
        
        // If no answer provided (empty/time expired), mark as incorrect (failed)
        $isCorrect = !empty($selectedAnswer) && ($question->correct_answer === $selectedAnswer);

        QuizAttempt::create([
            'user_id' => Auth::id(),
            'quiz_id' => $question->quiz_id,
            'question_id' => $question->id,
            'selected_answer' => $selectedAnswer, // Empty string for time-expired attempts
            'is_correct' => $isCorrect, // False when empty or wrong, true when correct
        ]);

        // Mark question as used/locked so no other student can access it
        $question->update(['is_used' => true]);

        return response()->json([
            'correct' => $isCorrect,
            'correct_answer' => $question->correct_answer,
            'time_expired' => empty($selectedAnswer), // Flag to indicate time expiry
        ]);
    }

    public function statistics()
    {
        $user = Auth::user();
        $stats = QuizAttempt::where('user_id', $user->id)
            ->selectRaw('
                quiz_id,
                COUNT(*) as total_attempts,
                SUM(is_correct) as correct_answers,
                AVG(is_correct) * 100 as accuracy
            ')
            ->groupBy('quiz_id')
            ->with('quiz')
            ->get();

        return view('quizzer.statistics', compact('stats'));
    }

    public function quizReview($quizId)
    {
        $quiz = Quiz::findOrFail($quizId);
        $user = Auth::user();
        
        $subjects = Subject::whereHas('questions', function($query) use ($quizId, $user) {
            $query->where('quiz_id', $quizId)
                  ->whereIn('id', function($subQuery) use ($user) {
                      $subQuery->select('question_id')
                               ->from('quiz_attempts')
                               ->where('user_id', $user->id);
                  });
        })->get();
        
        return view('quizzer.quiz_review', compact('quiz', 'subjects'));
    }

    public function subjectReview($quizId, $subjectId)
    {
        $quiz = Quiz::findOrFail($quizId);
        $subject = Subject::findOrFail($subjectId);
        $user = Auth::user();
        
        $attempts = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quizId)
            ->whereHas('question', function($query) use ($subjectId) {
                $query->where('subject_id', $subjectId);
            })
            ->with('question')
            ->get();
        
        // Add question numbers
        $allQuestions = Question::where('quiz_id', $quizId)
            ->where('subject_id', $subjectId)
            ->orderBy('id')
            ->pluck('id')
            ->toArray();
        
        foreach ($attempts as $attempt) {
            $attempt->question_number = array_search($attempt->question_id, $allQuestions) + 1;
        }
        
        return view('quizzer.subject_review', compact('quiz', 'subject', 'attempts'));
    }
}