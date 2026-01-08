<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - MedQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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

            <div class="row g-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-book me-2"></i>Subject Performance</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="subjectChart" height="80"></canvas>
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

    // Subject Performance Chart
    fetch('/admin/api/subject-performance')
        .then(res => res.json())
        .then(data => {
            new Chart(document.getElementById('subjectChart'), {
                type: 'line',
                data: {
                    labels: data.map(d => d.subject),
                    datasets: [{
                        label: 'Accuracy %',
                        data: data.map(d => d.accuracy),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { 
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>