<div class="sidebar" id="quizzerSidebar">
    <script>
    // Apply collapsed state immediately to prevent flash
    (function() {
        if (window.innerWidth > 768 && localStorage.getItem('quizzerSidebarCollapsed') === 'true') {
            document.getElementById('quizzerSidebar').classList.add('collapsed');
        }
    })();
    </script>
    <div class="sidebar-section">
        <div class="dashboard-link-container">
            <a href="{{ route('quizzer.dashboard') }}" class="dashboard-link {{ request()->routeIs('quizzer.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span class="sidebar-text">Dashboard</span>
            </a>
            <button class="sidebar-collapse-btn" onclick="toggleSidebarCollapse(event)" title="Toggle sidebar">
                <i class="fas fa-chevron-left sidebar-toggle-icon"></i>
            </button>
        </div>
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
        <div class="sidebar-title" style="color: #ffffff;">Active Quizzes</div>
        @php
            $activeQuizzes = auth()->user()->quizzes()->select('quizzes.*')->where('is_active', true)->get();
        @endphp
        @if($activeQuizzes->isNotEmpty())
            @foreach($activeQuizzes as $quiz)
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
        return; // Don't apply collapse on mobile
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
    
    // Remove pre-collapsed class and apply proper classes
    document.documentElement.classList.remove('sidebar-pre-collapsed');
    
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('sidebar-collapsed');
    }
});
</script>

<style>
.dashboard-link-container {
    display: flex;
    align-items: center;
    position: relative;
}

.sidebar.collapsed .dashboard-link-container {
    flex-direction: column-reverse;
    align-items: stretch;
}

.dashboard-link-container .dashboard-link {
    flex: 1;
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: #555;
    text-decoration: none;
    border-left: 3px solid transparent;
}

.dashboard-link-container .dashboard-link:hover {
    background-color: #f8f9fa;
    border-left-color: #007bff;
}

.dashboard-link-container .dashboard-link.active {
    background-color: #e3f2fd;
    border-left-color: #007bff;
    color: #007bff;
}

.dashboard-link-container .dashboard-link i:first-child {
    width: 25px;
    text-align: center;
}

.sidebar-collapse-btn {
    background: none;
    border: none;
    padding: 12px 15px;
    cursor: pointer;
    color: #555;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.sidebar-collapse-btn:hover {
    background-color: #f8f9fa;
    color: #007bff;
}

.sidebar-collapse-btn .sidebar-toggle-icon {
    font-size: 1rem;
}

.sidebar.collapsed .sidebar-collapse-btn {
    width: 100%;
    padding: 1rem;
    border-radius: 8px;
    margin: 0.5rem 0;
}

.sidebar.collapsed .sidebar-collapse-btn:hover {
    background-color: var(--primary);
    color: white;
}

.sidebar.collapsed .sidebar-collapse-btn .sidebar-toggle-icon {
    transform: rotate(180deg);
}

/* Hide collapse button on mobile */
@media (max-width: 768px) {
    .sidebar-collapse-btn {
        display: none;
    }
    .dashboard-link-container .dashboard-link {
        flex: 1;
    }
}
</style>