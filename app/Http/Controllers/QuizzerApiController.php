<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizzerApiController extends Controller
{
    public function checkQuizUpdates()
    {
        $user = Auth::user();
        $activeQuizzes = $user->quizzes()
            ->where('is_active', true)
            ->select('id', 'title', 'description', 'updated_at')
            ->get();

        return response()->json([
            'quizzes' => $activeQuizzes,
            'count' => $activeQuizzes->count()
        ]);
    }
}
