<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - MedQ</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
</head>
<body>
    @include('components.topnav')
    @include('components.sidebar')

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Statistics Dashboard</h2>
                    <p class="text-muted mb-0">Quiz performance analytics</p>
                </div>
            </div>

            <div class="row g-3 mb-4">
                @foreach(\App\Models\Quiz::all() as $quiz)
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm" style="cursor: pointer;" onclick="window.location.href='{{ route('admin.quiz.analysis', $quiz->id) }}'">
                            <div class="card-body text-center p-3">
                                <i class="fas fa-clipboard-list text-primary mb-2" style="font-size: 2rem;"></i>
                                <h6 class="card-title mb-1">{{ $quiz->title }}</h6>
                                <span class="badge {{ $quiz->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $quiz->is_active ? 'Active' : 'Completed' }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Quiz Performance (Best to Worst)</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="quizChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Quiz Participation (Most to Least)</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="participationChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Quiz Performance Chart (Best to Worst)
    fetch('/admin/api/quiz-stats')
        .then(res => res.json())
        .then(data => {
            new Chart(document.getElementById('quizChart'), {
                type: 'bar',
                data: {
                    labels: data.map(d => d.quiz),
                    datasets: [{
                        label: 'Accuracy %',
                        data: data.map(d => d.accuracy),
                        backgroundColor: data.map((d, i) => {
                            if (i < 2) return '#10b981';
                            if (i >= data.length - 2) return '#ef4444';
                            return '#3b82f6';
                        })
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, max: 100 }
                    }
                }
            });
        });

    // Quiz Participation Chart (Most to Least)
    fetch('/admin/api/quiz-participation')
        .then(res => res.json())
        .then(data => {
            new Chart(document.getElementById('participationChart'), {
                type: 'doughnut',
                data: {
                    labels: data.map(d => d.quiz),
                    datasets: [{
                        data: data.map(d => d.attempts),
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        });
    </script>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>