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
                            <h3 class="mb-0">{{ count($rankings) }}</h3>
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
                                    $totalAttempts = array_sum(array_column($rankings, 'total_attempted'));
                                    $totalCorrect = array_sum(array_column($rankings, 'total_correct'));
                                    $avgAccuracy = $totalAttempts > 0 ? round(($totalCorrect / $totalAttempts) * 100, 1) : 0;
                                @endphp
                                {{ $avgAccuracy }}%
                            </h3>
                            <small class="text-muted">Average Accuracy</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overall Rankings Table -->
            @if(count($rankings) > 0)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Overall Quiz Rankings</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3" style="width: 80px;">Position</th>
                                    <th class="px-4 py-3">Student Name</th>
                                    @foreach($subjects as $subject)
                                        <th class="px-4 py-3 text-center">{{ $subject->name }}</th>
                                    @endforeach
                                    <th class="px-4 py-3 text-center">Total</th>
                                    <th class="px-4 py-3 text-center">Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rankings as $ranking)
                                    <tr style="cursor: pointer;" onclick="window.location='{{ route('admin.student.quiz.details', [$quiz->id, $ranking['user_id']]) }}'">
                                        <td class="px-4 py-3">
                                            <span class="badge bg-primary fw-bold">
                                                {{ $ranking['position'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 fw-semibold">{{ $ranking['user_name'] }}</td>
                                        @foreach($subjects as $subject)
                                            <td class="px-4 py-3 text-center">
                                                @if(isset($ranking['subjects'][$subject->id]))
                                                    <span class="badge bg-info text-dark">
                                                        {{ $ranking['subjects'][$subject->id]['correct'] }}/{{ $ranking['subjects'][$subject->id]['total'] }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="px-4 py-3 text-center">
                                            <span class="badge bg-primary fw-bold">
                                                {{ $ranking['total_correct'] }}/{{ $ranking['total_attempted'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="badge fw-bold" style="background-color: #ffcccc; color: #000;">
                                                {{ $ranking['percentage'] }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-users-slash text-muted" style="font-size: 4rem;"></i>
                        <h4 class="text-muted mt-3">No Attempts Yet</h4>
                        <p class="text-muted">Students haven't started this quiz yet</p>
                    </div>
                </div>
            @endif
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
