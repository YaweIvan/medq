<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MedQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
                <h6>Total Users</h6>
                <div class="stat-value-compact">{{ $stats['total_users'] }}</div>
            </a>
            <a href="{{ route('admin.quizzes.index') }}" class="stat-card-compact success">
                <h6>Total Quizzes</h6>
                <div class="stat-value-compact">{{ $stats['total_quizzes'] }}</div>
            </a>
            <a href="{{ route('admin.approvals') }}" class="stat-card-compact warning">
                <h6>Pending Approvals</h6>
                <div class="stat-value-compact">{{ $stats['pending_approvals'] }}</div>
            </a>
            <div class="stat-card-compact info">
                <h6>Total Questions</h6>
                <div class="stat-value-compact">{{ $stats['total_questions'] }}</div>
            </div>
            <a href="{{ route('admin.leaderboard') }}" class="stat-card-compact info">
                <h6>Leaderboard Top 3</h6>
                <ul class="leaderboard-list-compact">
                    @forelse($topUsers as $index => $user)
                        <li><span class="rank">{{ $index + 1 }}</span> {{ $user->name }} <strong>{{ number_format($user->accuracy, 0) }}%</strong></li>
                    @empty
                        <li class="text-muted">No quiz attempts yet</li>
                    @endforelse
                </ul>
            </a>
        </div>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>