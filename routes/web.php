<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\QuizzerController;
use App\Http\Controllers\StatisticsApiController;
use App\Http\Controllers\QuizzerApiController;

Route::get('/quizzer/stats', [StatisticsApiController::class, 'index']);


// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/welcome', function () {
    return view('welcome');
});

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin routes
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/approvals', [AdminController::class, 'approvals'])->name('approvals');
    Route::post('/approve/{id}', [AdminController::class, 'approveUser'])->name('approve');
    Route::post('/reject/{id}', [AdminController::class, 'rejectUser'])->name('reject');
    Route::post('/cancel-approval/{id}', [AdminController::class, 'cancelApproval'])->name('cancel-approval');
    Route::delete('/delete-user/{id}', [AdminController::class, 'deleteUser'])->name('delete-user');
    Route::get('/statistics', [AdminController::class, 'statistics'])->name('statistics');
    Route::get('/quiz/{quiz}/analysis', [AdminController::class, 'quizAnalysis'])->name('quiz.analysis');
    Route::get('/quiz/{quiz}/rankings/export', [AdminController::class, 'exportQuizRankings'])->name('quiz.rankings.export');
    Route::get('/quiz/{quiz}/subject/{subject}/analysis', [AdminController::class, 'subjectAnalysis'])->name('subject.analysis');
    Route::get('/quiz/{quiz}/subject/{subject}/student/{user}/review', [AdminController::class, 'studentSubjectReview'])->name('student.subject.review');
    Route::get('/leaderboard', [AdminController::class, 'leaderboard'])->name('leaderboard');
    Route::get('/leaderboard/{quiz}', [AdminController::class, 'quizLeaderboard'])->name('quiz.leaderboard');
    Route::get('/leaderboard/{quiz}/student/{user}', [AdminController::class, 'studentQuizDetails'])->name('student.quiz.details');
    Route::get('/tutorial', function() { return view('admin.tutorial'); })->name('tutorial');
    Route::get('/users/api', [AdminController::class, 'getUsersApi'])->name('users.api');
    Route::get('/download-template', [AdminController::class, 'downloadTemplate'])->name('download.template');
    Route::get('/sounds', [AdminController::class, 'sounds'])->name('sounds');
    Route::post('/upload-sound', [AdminController::class, 'uploadSound'])->name('upload-sound');
    Route::delete('/delete-sound', [AdminController::class, 'deleteSound'])->name('delete-sound');
    
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/quiz-stats', [\App\Http\Controllers\StatisticsApiController::class, 'quizStats']);
        Route::get('/quiz-participation', [\App\Http\Controllers\StatisticsApiController::class, 'quizParticipation']);
        Route::get('/quiz-leaderboard/{quiz}', [\App\Http\Controllers\StatisticsApiController::class, 'quizLeaderboardData']);
        Route::get('/leaderboard', [\App\Http\Controllers\StatisticsApiController::class, 'leaderboard']);
        Route::get('/subject-performance', [\App\Http\Controllers\StatisticsApiController::class, 'subjectPerformance']);
    });
    
    Route::prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/', [AdminController::class, 'quizzes'])->name('index');
        Route::get('/create', [AdminController::class, 'createQuiz'])->name('create');
        Route::post('/', [AdminController::class, 'storeQuiz'])->name('store');
        Route::get('/select-edit', [AdminController::class, 'selectEditQuiz'])->name('select-edit');
        Route::get('/{id}/edit', [AdminController::class, 'editQuiz'])->name('edit');
        Route::put('/{id}', [AdminController::class, 'updateQuiz'])->name('update');
        Route::post('/{id}/update-users', [AdminController::class, 'updateQuizUsers'])->name('update-users');
        Route::post('/{id}/reset', [AdminController::class, 'resetQuiz'])->name('reset');
        Route::post('/{id}/add-subjects', [AdminController::class, 'addSubjects'])->name('add-subjects');
        Route::post('/{quizId}/subjects/{subjectId}/update-time', [AdminController::class, 'updateSubjectTime'])->name('update-subject-time');
        Route::delete('/{quizId}/subjects/{subjectId}', [AdminController::class, 'deleteQuizSubject'])->name('delete-subject');
        Route::delete('/{id}', [AdminController::class, 'deleteQuiz'])->name('delete');
        Route::post('/{id}/toggle', [AdminController::class, 'toggleQuizStatus'])->name('toggle');
    });
});

// Quizzer routes
Route::prefix('quizzer')->middleware(['auth', 'quizzer'])->name('quizzer.')->group(function () {
    Route::get('/dashboard', [QuizzerController::class, 'dashboard'])->name('dashboard');
    Route::get('/quiz/{quiz}/subjects', [QuizzerController::class, 'quizSubjects'])->name('quiz.subjects');
    Route::get('/quiz/{quiz}/subject/{subject}/grid', [QuizzerController::class, 'questionGrid'])->name('question.grid');
    Route::get('/quiz/{quiz}/subject/{subject}/questions', [QuizzerController::class, 'questions'])->name('questions');
    Route::get('/question/{question}', [QuizzerController::class, 'showQuestion'])->name('question.show');
    Route::post('/submit-answer', [QuizzerController::class, 'submitAnswer'])->name('submit.answer');
    Route::get('/statistics', [QuizzerController::class, 'statistics'])->name('statistics');
    Route::get('/quiz/{quiz}/review', [QuizzerController::class, 'quizReview'])->name('quiz.review');
    Route::get('/quiz/{quiz}/subject/{subject}/review', [QuizzerController::class, 'subjectReview'])->name('subject.review');
    Route::get('/tutorial', function() { return view('quizzer.tutorial'); })->name('tutorial');
    
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/check-updates', [QuizzerApiController::class, 'checkQuizUpdates'])->name('check-updates');
        Route::get('/my-stats', function() {
            return app(\App\Http\Controllers\StatisticsApiController::class)->userStats(auth()->id());
        });
        Route::get('/my-subjects', function() {
            return app(\App\Http\Controllers\StatisticsApiController::class)->userSubjects(auth()->id());
        });
        Route::get('/my-rankings', function() {
            return app(\App\Http\Controllers\StatisticsApiController::class)->userRankingsPerQuiz(auth()->id());
        });
    });
});

// Cache clearing route for InfinityFree (remove after first use for security)
Route::get('/clear-all-cache', function() {
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    
    return '<h1>✅ All Caches Cleared Successfully!</h1>
            <p>Cache cleared ✓</p>
            <p>Views cleared ✓</p>
            <p>Config cleared ✓</p>
            <p>Routes cleared ✓</p>
            <p><strong>⚠️ IMPORTANT: Remove this route from web.php after use for security!</strong></p>';
});
