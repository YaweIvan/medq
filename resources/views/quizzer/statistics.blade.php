<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Statistics - MedQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script>
        // Apply collapsed state immediately before page renders to prevent flash
        if (window.innerWidth > 768 && localStorage.getItem('quizzerSidebarCollapsed') === 'true') {
            document.documentElement.classList.add('sidebar-pre-collapsed');
        }
    </script>
    <style>
        .sidebar-pre-collapsed #quizzerSidebar {
            width: 70px;
        }
        .sidebar-pre-collapsed .main-content {
            margin-left: 70px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    @include('components.topnav')
    @include('components.quizzer_sidebar')

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">My Statistics</h2>
                    <p class="text-muted mb-0">Track your quiz performance</p>
                </div>
                <a href="{{ route('quizzer.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>

            <div class="row g-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>My Quiz Performance</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="quizChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-percentage me-2"></i>Overall Stats</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="overallChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>My Rankings</h5>
                        </div>
                        <div class="card-body" id="rankingsContainer">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <script>
    // Fetch Rankings Per Quiz
    fetch('/quizzer/api/my-rankings')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('rankingsContainer');
            if (data.length > 0) {
                container.innerHTML = `
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Quiz</th>
                                    <th class="text-center">Last Attempt</th>
                                    <th class="text-center">Rank</th>
                                    <th class="text-center">Correct</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Accuracy</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.map(quiz => `
                                    <tr style="cursor: pointer;" onclick="window.location.href='/quizzer/quiz/${quiz.quiz_id}/review'">
                                        <td><strong>${quiz.quiz}</strong></td>
                                        <td class="text-center">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>${quiz.last_attempt_date}
                                                ${quiz.last_attempt_time ? '<br><i class="fas fa-clock me-1"></i>' + quiz.last_attempt_time : ''}
                                            </small>
                                        </td>
                                        <td class="text-center"><span class="badge bg-primary">${quiz.rank}</span></td>
                                        <td class="text-center text-success">${quiz.correct}</td>
                                        <td class="text-center">${quiz.total}</td>
                                        <td class="text-center"><strong>${quiz.accuracy}%</strong></td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                container.innerHTML = '<p class="text-center text-muted py-5">No ranking data yet.<br>Start taking quizzes!</p>';
            }
        })
        .catch(() => {
            document.getElementById('rankingsContainer').innerHTML = '<p class="text-center text-muted py-5">Unable to load rankings</p>';
        });

    // Quiz Performance Chart
    fetch('/quizzer/api/my-stats')
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                document.getElementById('quizChart').parentElement.innerHTML = '<p class="text-center text-muted py-5">No quiz data available yet. Start taking quizzes!</p>';
                return;
            }

            new Chart(document.getElementById('quizChart'), {
                type: 'bar',
                data: {
                    labels: data.map(d => d.quiz),
                    datasets: [{
                        label: 'Correct',
                        data: data.map(d => d.correct),
                        backgroundColor: '#10b981'
                    }, {
                        label: 'Incorrect',
                        data: data.map(d => d.incorrect),
                        backgroundColor: '#ef4444'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            // Overall Stats Doughnut
            const totalCorrect = data.reduce((sum, d) => sum + d.correct, 0);
            const totalIncorrect = data.reduce((sum, d) => sum + d.incorrect, 0);
            
            new Chart(document.getElementById('overallChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Correct', 'Incorrect'],
                    datasets: [{
                        data: [totalCorrect, totalIncorrect],
                        backgroundColor: ['#10b981', '#ef4444']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>