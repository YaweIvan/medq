<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MedQ</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

</head>
<body>
    <!-- Top Navigation -->
    
    @include('components.topnav')

    <!-- Sidebar -->
    @include('components.sidebar')


    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome back! Here's what's happening with your medical quiz platform.</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid-compact">
            <a href="{{ route('admin.approvals') }}" class="stat-card-compact primary">
                <h6>Total Quizzers</h6>
                <div class="stat-value-compact" id="stat-total-quizzers">{{ $stats['total_quizzers'] }}</div>
            </a>
            <a href="{{ route('admin.approvals') }}" class="stat-card-compact success">
                <h6>Approved Quizzers</h6>
                <div class="stat-value-compact" id="stat-approved-quizzers">{{ $stats['approved_quizzers'] }}</div>
            </a>
            <a href="{{ route('admin.approvals') }}" class="stat-card-compact warning">
                <h6>Pending Quizzers</h6>
                <div class="stat-value-compact" id="stat-pending-quizzers">{{ $stats['pending_quizzers'] }}</div>
            </a>
            <a href="{{ route('admin.quizzes.index') }}" class="stat-card-compact info">
                <h6>Total Quizzes</h6>
                <div class="stat-value-compact" id="stat-total-quizzes">{{ $stats['total_quizzes'] }}</div>
            </a>
            <div class="stat-card-compact secondary">
                <h6>Total Subjects</h6>
                <div class="stat-value-compact" id="stat-total-subjects">{{ $stats['total_subjects'] }}</div>
            </div>
            <div class="stat-card-compact info">
                <h6>Total Questions</h6>
                <div class="stat-value-compact" id="stat-total-questions">{{ $stats['total_questions'] }}</div>
            </div>
            <div class="stat-card-compact success">
                <h6>Total Attempts</h6>
                <div class="stat-value-compact" id="stat-total-attempts">{{ $stats['total_attempts'] }}</div>
            </div>
            <a href="{{ route('admin.leaderboard') }}" class="stat-card-compact info">
                <h6>Top Performers</h6>
                <ul class="leaderboard-list-compact" id="stat-leaderboard">
                    @forelse($stats['top_performers'] as $index => $user)
                        <li><span class="rank">{{ $index + 1 }}</span> {{ $user->name }} <strong>{{ number_format($user->accuracy, 0) }}%</strong></li>
                    @empty
                        <li class="text-muted">No quiz attempts yet</li>
                    @endforelse
                </ul>
            </a>
        </div>
        
        <!-- Cache Info -->
        @if(isset($stats['cache_timestamp']))
        <div class="alert alert-info mt-3">
            <small><i class="fas fa-clock"></i> Statistics cached at: {{ $stats['cache_timestamp'] }} (refreshes every 60 seconds)</small>
        </div>
        @endif

        <!-- Quick Actions -->
        <h2 class="section-title">
            <i class="fas fa-bolt"></i>
            Quick Actions
        </h2>
        <div class="quick-actions-horizontal">
            <a href="{{ route('admin.quizzes.create') }}" class="action-card-small">
                <i class="fas fa-plus-circle"></i>
                <span>Create Quiz</span>
            </a>
            <a href="{{ route('admin.quizzes.select-edit') }}" class="action-card-small">
                <i class="fas fa-edit"></i>
                <span>Edit Quiz</span>
            </a>
            <a href="{{ route('admin.approvals') }}" class="action-card-small">
                <i class="fas fa-user-check"></i>
                <span>User Approvals</span>
            </a>
            <a href="{{ route('admin.sounds') }}" class="action-card-small">
                <i class="fas fa-volume-up"></i>
                <span>Quiz Sounds</span>
            </a>
            <a href="{{ route('admin.leaderboard') }}" class="action-card-small">
                <i class="fas fa-trophy"></i>
                <span>Leaderboard</span>
            </a>
            <a href="{{ route('admin.statistics') }}" class="action-card-small">
                <i class="fas fa-chart-bar"></i>
                <span>Statistics</span>
            </a>
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>