<style>
:root {
    --primary: {{ $appPrimaryColor ?? '#93c5fd' }};
    --secondary: {{ $appSecondaryColor ?? '#bfdbfe' }};
}
</style>
<nav class="top-nav">
    <div class="d-flex align-items-center">
        <button class="btn btn-link text-white d-md-none me-2" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="brand">
            <i class="fas fa-stethoscope"></i>
            MedQ {{ auth()->user()->isAdmin() ? 'Admin' : 'Quizzer' }}
        </div>
    </div>
    <div class="nav-actions">
        <span class="me-3 d-none d-md-inline text-white-50">
            <i class="fas fa-calendar-day me-1"></i>{{ date('D, M j, Y') }}
        </span>
        @if(auth()->user()->isAdmin())
            @php
                $pendingCount = \App\Models\User::where('is_approved', false)->count();
            @endphp
            @if($pendingCount > 0)
                <a href="{{ route('admin.approvals') }}" class="btn btn-warning btn-sm me-2" title="Pending Approvals">
                    <i class="fas fa-user-clock"></i>
                    <span class="badge bg-danger ms-1">{{ $pendingCount }}</span>
                </a>
            @endif
        @endif
        <span class="me-3 d-none d-sm-inline">Welcome, {{ auth()->user()->name }}</span>
        <button id="navThemeToggle" onclick="toggleTheme()" title="Toggle theme" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.30);border-radius:50%;width:34px;height:34px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:1rem;margin-right:0.5rem;transition:all 0.2s ease;">🌙</button>
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt"></i> <span class="d-none d-sm-inline">Logout</span>
            </button>
        </form>
    </div>
</nav>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('show');
}

// ── Theme persistence ──────────────────────────────────────────
(function () {
    if (localStorage.getItem('medq-theme') === 'light') {
        document.body.classList.add('light-theme');
    }
    _updateNavToggle();
})();

function _updateNavToggle() {
    var btn = document.getElementById('navThemeToggle');
    if (!btn) return;
    var isLight = document.body.classList.contains('light-theme');
    btn.textContent = isLight ? '🌙' : '☀️';
    btn.title = isLight ? 'Switch to Dark theme' : 'Switch to Light theme';
}

function toggleTheme() {
    var isLight = document.body.classList.toggle('light-theme');
    localStorage.setItem('medq-theme', isLight ? 'light' : 'dark');
    _updateNavToggle();
}
</script>