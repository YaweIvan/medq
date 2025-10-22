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
        <span class="me-3 d-none d-sm-inline">Welcome, {{ auth()->user()->name }}</span>
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
</script>