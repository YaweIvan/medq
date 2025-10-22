<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Statistics - MedQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    @include('components.topnav')
    @include('components.quizzer_sidebar')

    <div class="main-content">
        <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold text-dark mb-1">My Statistics</h2>
                        <p class="text-muted mb-0">Track your quiz performance</p>
                    </div>
                    <a href="{{ route('quizzer.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>

                @if($stats->count() > 0)
                    <div class="row g-4">
                        @foreach($stats as $stat)
                            <div class="col-lg-4 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary">{{ $stat->quiz->title ?? 'Quiz' }}</h5>
                                        <div class="row text-center mt-3">
                                            <div class="col-4">
                                                <div class="stat-value text-primary">{{ $stat->total_attempts }}</div>
                                                <small class="text-muted">Attempts</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="stat-value text-success">{{ $stat->correct_answers }}</div>
                                                <small class="text-muted">Correct</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="stat-value text-info">{{ number_format($stat->accuracy, 1) }}%</div>
                                                <small class="text-muted">Accuracy</small>
                                            </div>
                                        </div>
                                        <div class="progress mt-3" style="height: 8px;">
                                            <div class="progress-bar bg-success" style="width: {{ $stat->accuracy }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-chart-line text-muted" style="font-size: 4rem;"></i>
                        <h4 class="text-muted mt-3">No Statistics Yet</h4>
                        <p class="text-muted">Start taking quizzes to see your performance statistics</p>
                        <a href="{{ route('quizzer.dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-play me-2"></i>Take a Quiz
                        </a>
                    </div>
                @endif
            </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>