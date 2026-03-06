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
        $stats = Quiz::with('attempts')
            ->get()
            ->map(function($quiz) {
                $attempts = $quiz->attempts;
                return [
                    'quiz' => $quiz->title,
                    'quiz_id' => $quiz->id,
                    'total_attempts' => $attempts->count(),
                    'correct' => $attempts->where('is_correct', true)->count(),
                    'incorrect' => $attempts->where('is_correct', false)->count(),
                    'accuracy' => $attempts->count() > 0 ? round(($attempts->where('is_correct', true)->count() / $attempts->count()) * 100, 2) : 0
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
        $userQuizzes = DB::table('quiz_user')
            ->where('user_id', $userId)
            ->pluck('quiz_id');

        $rankings = [];

        foreach ($userQuizzes as $quizId) {
            $quiz = Quiz::find($quizId);
            if (!$quiz) continue;

            // Get subjects for this quiz with marks and max_questions
            $subjects = Subject::whereHas('questions', function($query) use ($quizId) {
                $query->where('quiz_id', $quizId);
            })->get();

            if ($subjects->isEmpty()) continue;

            // Total max points = sum(max_questions × marks_per_question)
            $totalMaxPoints = 0;
            foreach ($subjects as $subject) {
                $totalMaxPoints += ($subject->max_questions ?? 5) * ($subject->marks_per_question ?? 1);
            }

            // Fetch per-user, per-subject correct counts in one query
            $subjectCorrects = DB::table('quiz_attempts')
                ->join('questions', 'quiz_attempts.question_id', '=', 'questions.id')
                ->where('quiz_attempts.quiz_id', $quizId)
                ->whereIn('questions.subject_id', $subjects->pluck('id'))
                ->select('quiz_attempts.user_id', 'questions.subject_id',
                    DB::raw('SUM(quiz_attempts.is_correct) as correct_count'))
                ->groupBy('quiz_attempts.user_id', 'questions.subject_id')
                ->get()
                ->groupBy('user_id');

            if ($subjectCorrects->isEmpty()) continue;

            $subjectMarks = $subjects->keyBy('id')->map(fn($s) => $s->marks_per_question ?? 1);

            // Compute weighted points for every user
            $allUsersData = [];
            foreach ($subjectCorrects as $uid => $rows) {
                $points = 0;
                foreach ($rows as $row) {
                    $marks   = $subjectMarks[$row->subject_id] ?? 1;
                    $points += (int)$row->correct_count * $marks;
                }
                $accuracy = $totalMaxPoints > 0 ? round(($points / $totalMaxPoints) * 100, 2) : 0;
                $allUsersData[] = [
                    'id'       => $uid,
                    'points'   => $points,
                    'correct'  => $points,           // alias kept for frontend compat
                    'total'    => $totalMaxPoints,
                    'accuracy' => $accuracy,
                ];
            }

            // Sort by points desc, then accuracy desc
            usort($allUsersData, function($a, $b) {
                if ($a['points'] == $b['points']) {
                    return $b['accuracy'] <=> $a['accuracy'];
                }
                return $b['points'] <=> $a['points'];
            });
            
            $allUsers = array_values($allUsersData);

            $rank = null;
            $userStats = null;

            foreach ($allUsers as $index => $user) {
                if ($user['id'] == $userId) {
                    $rank = $index + 1;
                    $userStats = $user;
                    break;
                }
            }

            if ($rank) {
                // Get the last attempt date for this user and quiz
                $lastAttempt = QuizAttempt::where('quiz_id', $quizId)
                    ->where('user_id', $userId)
                    ->latest('created_at')
                    ->first();
                
                $rankings[] = [
                    'quiz'             => $quiz->title,
                    'quiz_id'          => $quizId,
                    'rank'             => $rank,
                    'points'           => $userStats['points'],
                    'total_max_points' => $totalMaxPoints,
                    'correct'          => $userStats['points'],       // alias for chart compat
                    'total'            => $totalMaxPoints,             // alias for chart compat
                    'accuracy'         => $userStats['accuracy'],
                    'last_attempt_date' => $lastAttempt ? $lastAttempt->created_at->format('M j, Y') : 'N/A',
                    'last_attempt_time' => $lastAttempt ? $lastAttempt->created_at->format('g:i A') : '',
                ];
            }
        }

        return response()->json($rankings);
    }
}