<div class="sidebar">
    <div class="sidebar-section">
        <a href="{{ route('quizzer.dashboard') }}" class="{{ request()->routeIs('quizzer.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
        </a>
        <a href="{{ route('quizzer.statistics') }}" class="{{ request()->routeIs('quizzer.statistics') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i>
            My Statistics
        </a>
        <a href="{{ route('quizzer.tutorial') }}" class="{{ request()->routeIs('quizzer.tutorial') ? 'active' : '' }}">
            <i class="fas fa-question-circle"></i>
            Tutorial
        </a>
    </div>
    
    <hr>
    
    <div class="sidebar-section">
        <div class="sidebar-title">Active Quizzes</div>
        @if(auth()->user()->quizzes()->where('is_active', true)->exists())
            @foreach(auth()->user()->quizzes()->where('is_active', true)->get() as $quiz)
                <a href="{{ route('quizzer.quiz.subjects', $quiz->id) }}">
                    <i class="fas fa-clipboard-list"></i>
                    {{ Str::limit($quiz->title, 20) }}
                </a>
            @endforeach
        @else
            <div class="text-muted px-3 py-2 small">No active quizzes</div>
        @endif
    </div>
</div>