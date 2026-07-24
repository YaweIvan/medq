<?php

namespace App\Services;

use App\Models\User;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\Question;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardStatisticsService
{
    private const CACHE_KEY = 'dashboard_statistics';
    private const CACHE_DURATION = 60; // 60 seconds

    public function getStatistics(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
            return $this->calculateStatistics();
        });
    }

    public function refreshStatistics(): array
    {
        Cache::forget(self::CACHE_KEY);
        return $this->getStatistics();
    }

    private function calculateStatistics(): array
    {
        // Optimize queries using single database calls
        $userStats = User::selectRaw('
            COUNT(*) as total_users,
            SUM(CASE WHEN role = "quizzer" THEN 1 ELSE 0 END) as total_quizzers,
            SUM(CASE WHEN role = "quizzer" AND is_approved = 1 THEN 1 ELSE 0 END) as approved_quizzers,
            SUM(CASE WHEN role = "quizzer" AND is_approved = 0 THEN 1 ELSE 0 END) as pending_quizzers
        ')->first();

        $systemStats = [
            'total_quizzes' => Quiz::count(),
            'total_subjects' => Subject::count(),
            'total_questions' => Question::count(),
            'total_attempts' => QuizAttempt::count(),
        ];

        // 6.3 — push sort+limit into SQL, filter by submitted_at
        $topPerformers = User::where('role', 'quizzer')
            ->withCount(['attempts as total_attempts' => fn($q) => $q->whereNotNull('submitted_at')])
            ->withCount(['attempts as correct_answers' => fn($q) => $q->whereNotNull('submitted_at')->where('is_correct', true)])
            ->having('total_attempts', '>', 0)
            ->orderByRaw('(correct_answers / total_attempts) DESC')
            ->limit(3)
            ->get()
            ->map(function($user) {
                $user->accuracy = round(($user->correct_answers / $user->total_attempts) * 100, 2);
                return $user;
            });

        return array_merge([
            'total_users' => $userStats->total_users,
            'total_quizzers' => $userStats->total_quizzers,
            'approved_quizzers' => $userStats->approved_quizzers,
            'pending_quizzers' => $userStats->pending_quizzers,
            'top_performers' => $topPerformers,
            'cache_timestamp' => now()->toDateTimeString(),
        ], $systemStats);
    }
}