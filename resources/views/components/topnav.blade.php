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