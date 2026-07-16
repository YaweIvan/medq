<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\QuizAttempt;
use App\Models\Question;

class QuestionAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $questionId = $request->route('question');
        $user = auth()->user();
        
        if (!$questionId || !$user) {
            return redirect()->route('quizzer.dashboard');
        }
        
        $question = Question::find($questionId);
        if (!$question) {
            return redirect()->route('quizzer.dashboard');
        }
        
        // Check if user has access to this quiz
        if (!$user->quizzes->contains($question->quiz)) {
            return redirect()->route('quizzer.dashboard');
        }
        
        // Check existing attempt
        $attempt = QuizAttempt::where('user_id', $user->id)
            ->where('question_id', $questionId)
            ->first();
            
        if ($attempt) {
            // If submitted or locked, redirect silently
            if ($attempt->submitted || $attempt->locked) {
                return redirect()->route('quizzer.question.grid', [$question->quiz_id, $question->subject_id]);
            }
            
            // If expired, redirect silently
            if ($attempt->isExpired()) {
                return redirect()->route('quizzer.question.grid', [$question->quiz_id, $question->subject_id]);
            }
        }
        
        return $next($request);
    }
}
