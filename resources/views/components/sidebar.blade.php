<div class="sidebar">
    <div class="sidebar-section">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
        </a>
        <a href="{{ route('admin.approvals') }}" class="{{ request()->routeIs('admin.approvals') ? 'active' : '' }}">
            <i class="fas fa-user-check"></i>
            User Approvals
        </a>
    </div>
    
    <hr>
    
    <div class="sidebar-section">
        <div class="sidebar-title">Quiz Management</div>
        <a href="{{ route('admin.quizzes.index') }}" class="{{ request()->routeIs('admin.quizzes.index') ? 'active' : '' }}">
            <i class="fas fa-list"></i>
            All Quizzes
        </a>
        <a href="{{ route('admin.quizzes.create') }}" class="{{ request()->routeIs('admin.quizzes.create') ? 'active' : '' }}">
            <i class="fas fa-plus-circle"></i>
            Create Quiz
        </a>
        <a href="{{ route('admin.quizzes.select-edit') }}" class="{{ request()->routeIs('admin.quizzes.select-edit') || request()->routeIs('admin.quizzes.edit') ? 'active' : '' }}">
            <i class="fas fa-edit"></i>
            Edit Quiz
        </a>
    </div>
    
    <hr>
    
    <div class="sidebar-section">
        <a href="{{ route('admin.statistics') }}" class="{{ request()->routeIs('admin.statistics') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i>
            Statistics
        </a>
        <a href="{{ route('admin.leaderboard') }}" class="{{ request()->routeIs('admin.leaderboard') || request()->routeIs('admin.quiz.leaderboard') || request()->routeIs('admin.student.quiz.details') ? 'active' : '' }}">
            <i class="fas fa-trophy"></i>
            Leaderboard
        </a>
        <a href="{{ route('admin.tutorial') }}" class="{{ request()->routeIs('admin.tutorial') ? 'active' : '' }}">
            <i class="fas fa-book"></i>
            Tutorial
        </a>
        <a href="{{ route('admin.sounds') }}" class="{{ request()->routeIs('admin.sounds') ? 'active' : '' }}">
            <i class="fas fa-volume-up"></i>
            Sounds
        </a>
    </div>
</div>

<style>
.sidebar {
    position: fixed !important;
    will-change: auto;
}
.sidebar a {
    display: block !important;
}
</style>