<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\User;
use App\Models\QuizAttempt;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsApiController extends Controller
{
    public function quizStats()
    {
        // 6.3 — aggregation query instead of loading all attempts into memory
        $stats = DB::table('quizzes')
            ->leftJoin('quiz_attempts', function($join) {
                $join->on('quiz_attempts.quiz_id', '=', 'quizzes.id')
                     ->whereNotNull('quiz_attempts.submitted_at');
            })
            ->select(
                'quizzes.id as quiz_id',
                'quizzes.title as quiz',
                DB::raw('COUNT(quiz_attempts.id) as total_attempts'),
                DB::raw('SUM(CASE WHEN quiz_attempts.is_correct = 1 THEN 1 ELSE 0 END) as correct'),
                DB::raw('SUM(CASE WHEN quiz_attempts.is_correct = 0 AND quiz_attempts.id IS NOT NULL THEN 1 ELSE 0 END) as incorrect')
            )
            ->groupBy('quizzes.id', 'quizzes.title')
            ->get()
            ->map(function($row) {
                return [
                    'quiz'           => $row->quiz,
                    'quiz_id'        => $row->quiz_id,
                    'total_attempts' => (int) $row->total_attempts,
                    'correct'        => (int) $row->correct,
                    'incorrect'      => (int) $row->incorrect,
                    'accuracy'       => $row->total_attempts > 0
                        ? round(($row->correct / $row->total_attempts) * 100, 2) : 0,
                ];
            })
            ->sortByDesc('accuracy')
            ->values();

        return response()->json($stats);
    }

    public function quizParticipation()
    {
        $stats = Quiz::withCount(['attempts as total_attempts'])
            ->get()
            ->map(function($quiz) {
                return [
                    'quiz' => $quiz->title,
                    'quiz_id' => $quiz->id,
                    'attempts' => $quiz->total_attempts
                ];
            })
            ->sortByDesc('attempts')
            ->values();

        return response()->json($stats);
    }

    public function quizLeaderboardData($quizId)
    {
        $users = User::where('role', 'quizzer')
            ->whereHas('quizzes', function($q) use ($quizId) {
                $q->where('quizzes.id', $quizId);
            })
            ->withCount(['attempts as total' => function($q) use ($quizId) {
                $q->where('quiz_id', $quizId);
            }])
            ->withCount(['attempts as correct' => function($q) use ($quizId) {
                $q->where('quiz_id', $quizId)->where('is_correct', true);
            }])
            ->get()
            ->map(function($user) {
                return [
                    'name' => $user->name,
                    'total' => $user->total,
                    'correct' => $user->correct,
                    'accuracy' => $user->total > 0 ? round(($user->correct / $user->total) * 100, 2) : 0
                ];
            })
            ->sortByDesc('accuracy')
            ->values();

        return response()->json($users);
    }

    public function leaderboard()
    {
        $users = User::where('role', 'quizzer')
            ->withCount(['attempts as correct_count' => function($q) {
                $q->where('is_correct', true);
            }])
            ->withCount('attempts as total_count')
            ->orderBy('correct_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function($user) {
                return [
                    'name' => $user->name,
                    'correct' => $user->correct_count,
                    'total' => $user->total_count,
                    'accuracy' => $user->total_count > 0 ? round(($user->correct_count / $user->total_count) * 100, 2) : 0
                ];
            });

        return response()->json($users);
    }

    public function subjectPerformance()
    {
        $stats = DB::table('quiz_attempts')
            ->join('questions', 'quiz_attempts.question_id', '=', 'questions.id')
            ->join('subjects', 'questions.subject_id', '=', 'subjects.id')
            ->select('subjects.name', 
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN quiz_attempts.is_correct = 1 THEN 1 ELSE 0 END) as correct'))
            ->groupBy('subjects.name')
            ->get()
            ->map(function($item) {
                return [
                    'subject' => $item->name,
                    'total' => $item->total,
                    'correct' => $item->correct,
                    'accuracy' => $item->total > 0 ? round(($item->correct / $item->total) * 100, 2) : 0
                ];
            });

        return response()->json($stats);
    }

    public function userStats($userId)
    {
        $stats = Quiz::whereHas('users', function($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->with(['attempts' => function($q) use ($userId) {
                $q->where('user_id', $userId);
            }])
            ->get()
            ->map(function($quiz) {
                $attempts = $quiz->attempts;
                return [
                    'quiz' => $quiz->title,
                    'total' => $attempts->count(),
                    'correct' => $attempts->where('is_correct', true)->count(),
                    'incorrect' => $attempts->where('is_correct', false)->count(),
                    'accuracy' => $attempts->count() > 0 ? round(($attempts->where('is_correct', true)->count() / $attempts->count()) * 100, 2) : 0
                ];
            });

        return response()->json($stats);
    }

    public function userSubjects($userId)
    {
        $stats = DB::table('quiz_attempts')
            ->join('questions', 'quiz_attempts.question_id', '=', 'questions.id')
            ->join('subjects', 'questions.subject_id', '=', 'subjects.id')
            ->where('quiz_attempts.user_id', $userId)
            ->select('subjects.name',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN quiz_attempts.is_correct = 1 THEN 1 ELSE 0 END) as correct'))
            ->groupBy('subjects.name')
            ->get()
            ->map(function($item) {
                return [
                    'subject' => $item->name,
                    'total' => $item->total,
                    'correct' => $item->correct,
                    'accuracy' => $item->total > 0 ? round(($item->correct / $item->total) * 100, 2) : 0
                ];
            });

        return response()->json($stats);
    }

    public function userRankingsPerQuiz($userId)
    {
        $userQuizIds = DB::table('quiz_user')
            ->where('user_id', $userId)
            ->pluck('quiz_id');

        if ($userQuizIds->isEmpty()) {
            return response()->json([]);
        }

        // 6.3 — bulk-load all quizzes in one query
        $quizzesById = Quiz::whereIn('id', $userQuizIds)->get()->keyBy('id');

        // 6.3 — bulk-load subjects for all relevant quizzes
        $subjectsByQuiz = Subject::whereHas('questions', function($q) use ($userQuizIds) {
            $q->whereIn('quiz_id', $userQuizIds);
        })->get()->groupBy(function($s) use ($userQuizIds) {
            // We need quiz_id context — fetch via questions
            return null; // will re-query per quiz below (subjects are shared across quizzes)
        });

        // 6.3 — single aggregation query for all quiz attempts across all quizzes
        $allCorrects = DB::table('quiz_attempts')
            ->join('questions', 'quiz_attempts.question_id', '=', 'questions.id')
            ->whereIn('quiz_attempts.quiz_id', $userQuizIds)
            ->whereNotNull('quiz_attempts.submitted_at')
            ->select(
                'quiz_attempts.quiz_id',
                'quiz_attempts.user_id',
                'questions.subject_id',
                DB::raw('SUM(quiz_attempts.is_correct) as correct_count')
            )
            ->groupBy('quiz_attempts.quiz_id', 'quiz_attempts.user_id', 'questions.subject_id')
            ->get()
            ->groupBy('quiz_id');

        // 6.3 — bulk last-attempt timestamps
        $lastAttempts = DB::table('quiz_attempts')
            ->whereIn('quiz_id', $userQuizIds)
            ->whereNotNull('submitted_at')
            ->select('quiz_id', 'user_id', DB::raw('MAX(submitted_at) as last_at'))
            ->groupBy('quiz_id', 'user_id')
            ->get()
            ->groupBy('quiz_id')
            ->map(fn($rows) => $rows->pluck('last_at', 'user_id'));

        $rankings = [];

        foreach ($userQuizIds as $quizId) {
            $quiz = $quizzesById->get($quizId);
            if (!$quiz) continue;

            $subjects = Subject::whereHas('questions', fn($q) => $q->where('quiz_id', $quizId))->get();
            if ($subjects->isEmpty()) continue;

            $totalMaxPoints = $subjects->sum(fn($s) => ($s->max_questions ?? 5) * ($s->marks_per_question ?? 1));
            $subjectMarks   = $subjects->keyBy('id')->map(fn($s) => $s->marks_per_question ?? 1);

            $quizCorrects = $allCorrects->get($quizId, collect())->groupBy('user_id');
            if ($quizCorrects->isEmpty()) continue;

            $allUsersData = [];
            foreach ($quizCorrects as $uid => $rows) {
                $points = $rows->sum(fn($r) => (int)$r->correct_count * ($subjectMarks[$r->subject_id] ?? 1));
                $allUsersData[] = [
                    'id'       => $uid,
                    'points'   => $points,
                    'accuracy' => $totalMaxPoints > 0 ? round(($points / $totalMaxPoints) * 100, 2) : 0,
                ];
            }

            usort($allUsersData, fn($a, $b) =>
                $b['points'] !== $a['points'] ? $b['points'] <=> $a['points'] : $b['accuracy'] <=> $a['accuracy']
            );

            $rank = null; $userStats = null;
            foreach ($allUsersData as $i => $u) {
                if ($u['id'] == $userId) { $rank = $i + 1; $userStats = $u; break; }
            }
            if (!$rank) continue;

            $lastAt = $lastAttempts->get($quizId, collect())->get($userId);
            $lastCarbon = $lastAt ? \Carbon\Carbon::parse($lastAt) : null;

            $rankings[] = [
                'quiz'              => $quiz->title,
                'quiz_id'           => $quizId,
                'rank'              => $rank,
                'points'            => $userStats['points'],
                'total_max_points'  => $totalMaxPoints,
                'correct'           => $userStats['points'],
                'total'             => $totalMaxPoints,
                'accuracy'          => $userStats['accuracy'],
                'last_attempt_date' => $lastCarbon ? $lastCarbon->format('M j, Y') : 'N/A',
                'last_attempt_time' => $lastCarbon ? $lastCarbon->format('g:i A') : '',
            ];
        }

        return response()->json($rankings);
    }
}