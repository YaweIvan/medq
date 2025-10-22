<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - MedQ</title>
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
                    <h2 class="fw-bold text-dark mb-1">Leaderboard</h2>
                    <p class="text-muted mb-0">Top performing students</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Student Rankings</h5>
                </div>
                <div class="card-body">
                    @if($leaderboard->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Student Name</th>
                                        <th>Total Attempts</th>
                                        <th>Correct Answers</th>
                                        <th>Accuracy</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leaderboard as $index => $student)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($index < 3)
                                                        <i class="fas fa-medal text-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'dark') }} me-2"></i>
                                                    @endif
                                                    <strong>{{ $index + 1 }}</strong>
                                                </div>
                                            </td>
                                            <td>{{ $student->name }}</td>
                                            <td>
                                                <span class="badge bg-primary">{{ $student->total_attempts }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">{{ $student->correct_answers }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $accuracy = $student->total_attempts > 0 ? ($student->correct_answers / $student->total_attempts) * 100 : 0;
                                                @endphp
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-info" style="width: {{ $accuracy }}%">
                                                        {{ number_format($accuracy, 1) }}%
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
                            <i class="fas fa-trophy text-muted" style="font-size: 4rem;"></i>
                            <h4 class="text-muted mt-3">No Rankings Available</h4>
                            <p class="text-muted">Leaderboard will appear once students start taking quizzes</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>