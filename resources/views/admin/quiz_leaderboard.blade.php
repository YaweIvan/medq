<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $quiz->title }} - Leaderboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    @include('components.topnav')
    @include('components.sidebar')

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">{{ $quiz->title }}</h2>
                    <p class="text-muted mb-0">Quiz Performance Analysis</p>
                </div>
                <a href="{{ route('admin.leaderboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Quizzes
                </a>
            </div>

            <!-- Quiz Overview Cards -->
            <div class="row g-4 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-question-circle fa-2x text-primary mb-2"></i>
                            <h3 class="mb-0">{{ $quiz->questions->count() }}</h3>
                            <small class="text-muted">Total Questions</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-2x text-success mb-2"></i>
                            <h3 class="mb-0">{{ $quiz->users->count() }}</h3>
                            <small class="text-muted">Participants</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-clipboard-check fa-2x text-info mb-2"></i>
                            <h3 class="mb-0">{{ $leaderboard->where('total_attempts', '>', 0)->count() }}</h3>
                            <small class="text-muted">Students Attempted</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line fa-2x text-warning mb-2"></i>
                            <h3 class="mb-0">
                                @php
                                    $totalAttempts = $leaderboard->sum('total_attempts');
                                    $totalCorrect = $leaderboard->sum('correct_answers');
                                    $avgAccuracy = $totalAttempts > 0 ? round(($totalCorrect / $totalAttempts) * 100, 1) : 0;
                                @endphp
                                {{ $avgAccuracy }}%
                            </h3>
                            <small class="text-muted">Average Accuracy</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leaderboard Table -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Student Rankings</h5>
                </div>
                <div class="card-body">
                    @if($leaderboard->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="80">Rank</th>
                                        <th>Student Name</th>
                                        <th class="text-center">Questions Attempted</th>
                                        <th class="text-center">Correct</th>
                                        <th class="text-center">Failed</th>
                                        <th width="200">Accuracy</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leaderboard as $index => $student)
                                        <tr style="cursor: pointer;" onclick="window.location='{{ route('admin.student.quiz.details', [$quiz->id, $student->id]) }}'">
                                            <td>
                                                <strong class="fs-5">{{ $index + 1 }}</strong>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle bg-primary text-white me-2">
                                                        {{ strtoupper(substr($student->name, 0, 1)) }}
                                                    </div>
                                                    <strong>{{ $student->name }}</strong>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary fs-6">{{ $student->total_attempts }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success fs-6">{{ $student->correct_answers }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-danger fs-6">{{ $student->failed_answers }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1" style="height: 25px;">
                                                        <div class="progress-bar bg-{{ $student->accuracy >= 80 ? 'success' : ($student->accuracy >= 60 ? 'warning' : 'danger') }}" 
                                                             style="width: {{ $student->accuracy }}%">
                                                            <strong>{{ number_format($student->accuracy, 1) }}%</strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-users-slash text-muted" style="font-size: 4rem;"></i>
                            <h4 class="text-muted mt-3">No Attempts Yet</h4>
                            <p class="text-muted">Students haven't started this quiz yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    .text-bronze {
        color: #cd7f32;
    }
    tr[style*="cursor: pointer"]:hover {
        background-color: #f8f9fa;
    }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
