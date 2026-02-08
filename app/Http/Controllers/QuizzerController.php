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
        $user = Auth::user();
        $activeQuizzes = $user->quizzes()->where('is_active', true)->get();
        
        return view('quizzer.dashboard', compact('activeQuizzes'));
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
        })->withCount(['questions as available_questions' => function($query) use ($quizId, $user) {
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
        
        return view('quizzer.quiz_grid', compact('quiz', 'subject', 'questions', 'attemptedQuestions', 'attemptedCount'));
    }

    public function questions($quizId, $subjectId)
    {
        $user = Auth::user();
        $quiz = Quiz::findOrFail($quizId);
        $subject = Subject::findOrFail($subjectId);

        // Check attempts for this subject (max 5)
        $attemptCount = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quizId)
            ->whereHas('question', function($query) use ($subjectId) {
                $query->where('subject_id', $subjectId);
            })->count();

        if ($attemptCount >= 5) {
            return redirect()->back()->with('error', 'Maximum 5 questions per subject reached.');
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
            ->take(5 - $attemptCount)
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
        
        return view('quizzer.question', compact('question'));
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
        $isCorrect = $question->correct_answer === $selectedAnswer;

        QuizAttempt::create([
            'user_id' => Auth::id(),
            'quiz_id' => $question->quiz_id,
            'question_id' => $question->id,
            'selected_answer' => $selectedAnswer,
            'is_correct' => $isCorrect,
        ]);

        // Mark question as used so no other student can access it
        $question->update(['is_used' => true]);

        return response()->json([
            'correct' => $isCorrect,
            'correct_answer' => $question->correct_answer,
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
}