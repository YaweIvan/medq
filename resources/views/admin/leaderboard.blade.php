<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Leaderboards - MedQ</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    @include('components.topnav')
    @include('components.sidebar')

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Quiz Leaderboards</h2>
                    <p class="text-muted mb-0">Select a quiz to view detailed performance</p>
                </div>
            </div>

            @if($quizzes->count() > 0)
                <div class="row g-4">
                    @foreach($quizzes as $quiz)
                        <div class="col-lg-4 col-md-6">
                            <a href="{{ route('admin.quiz.leaderboard', $quiz->id) }}" class="text-decoration-none">
                                <div class="card h-100 hover-shadow">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="flex-shrink-0">
                                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                                    <i class="fas fa-clipboard-list fa-2x text-primary"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="card-title text-dark mb-1">{{ $quiz->title }}</h5>
                                                <span class="badge bg-{{ $quiz->is_active ? 'success' : 'secondary' }}">{{ $quiz->is_active ? 'Active' : 'Inactive' }}</span>
                                            </div>
                                        </div>
                                        <div class="row text-center mt-4">
                                            <div class="col-4">
                                                <div class="stat-value text-primary">{{ $quiz->questions_count }}</div>
                                                <small class="text-muted">Questions</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="stat-value text-success">{{ $quiz->participants_count }}</div>
                                                <small class="text-muted">Students</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="stat-value text-info">{{ $quiz->total_attempts }}</div>
                                                <small class="text-muted">Attempts</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-top">
                                        <div class="text-center text-primary">
                                            <i class="fas fa-arrow-right me-2"></i>View Leaderboard
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-trophy text-muted" style="font-size: 4rem;"></i>
                    <h4 class="text-muted mt-3">No Quizzes Available</h4>
                    <p class="text-muted">Create quizzes to view leaderboards</p>
                    <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Quiz
                    </a>
                </div>
            @endif
        </div>
    </div>

    <style>
    .hover-shadow {
        transition: all 0.3s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
    }
    </style>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>