<div class="sidebar" id="quizzerSidebar">
    <div class="sidebar-section">
        <a href="{{ route('quizzer.dashboard') }}" class="{{ request()->routeIs('quizzer.dashboard') ? 'active' : '' }}" onclick="toggleSidebarCollapse(event)">
            <i class="fas fa-tachometer-alt"></i>
            <span class="sidebar-text">Dashboard</span>
            <i class="fas fa-chevron-left sidebar-toggle-icon"></i>
        </a>
        <a href="{{ route('quizzer.statistics') }}" class="{{ request()->routeIs('quizzer.statistics') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i>
            <span class="sidebar-text">My Statistics</span>
        </a>
        <a href="{{ route('quizzer.tutorial') }}" class="{{ request()->routeIs('quizzer.tutorial') ? 'active' : '' }}">
            <i class="fas fa-question-circle"></i>
            <span class="sidebar-text">Tutorial</span>
        </a>
    </div>
    
    <hr>
    
    <div class="sidebar-section">
        <div class="sidebar-title">Active Quizzes</div>
        @if(auth()->user()->quizzes()->where('is_active', true)->exists())
            @foreach(auth()->user()->quizzes()->where('is_active', true)->get() as $quiz)
                <a href="{{ route('quizzer.quiz.subjects', $quiz->id) }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="sidebar-text">{{ Str::limit($quiz->title, 20) }}</span>
                </a>
            @endforeach
        @else
            <div class="text-muted px-3 py-2 small">No active quizzes</div>
        @endif
    </div>
</div>

<script>
function toggleSidebarCollapse(event) {
    // Only apply collapse functionality on desktop (screen width > 768px)
    if (window.innerWidth <= 768) {
        return; // Let the normal link navigation work on mobile
    }
    
    event.preventDefault();
    event.stopPropagation();
    
    const sidebar = document.getElementById('quizzerSidebar');
    const mainContent = document.querySelector('.main-content');
    
    // Toggle the sidebar collapse
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('sidebar-collapsed');
    
    // Save state to localStorage
    if (sidebar.classList.contains('collapsed')) {
        localStorage.setItem('quizzerSidebarCollapsed', 'true');
    } else {
        localStorage.setItem('quizzerSidebarCollapsed', 'false');
    }
}

// Restore sidebar state on page load (desktop only)
document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth <= 768) {
        return; // Don't restore collapsed state on mobile
    }
    
    const sidebar = document.getElementById('quizzerSidebar');
    const mainContent = document.querySelector('.main-content');
    const isCollapsed = localStorage.getItem('quizzerSidebarCollapsed') === 'true';
    
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('sidebar-collapsed');
    }
});
</script>