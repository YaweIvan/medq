<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Review - MedQ</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
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
</head>
<body>
    @include('components.topnav')
    @include('components.quizzer_sidebar')

    <div class="main-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">{{ $quiz->title }} - Review</h2>
                    <p class="text-muted mb-0">Select a subject to review your answers</p>
                </div>
                <a href="{{ route('quizzer.statistics') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Statistics
                </a>
            </div>

            <div class="row g-3">
                @foreach($subjects as $subject)
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm" style="cursor: pointer;" onclick="window.location.href='{{ route('quizzer.subject.review', [$quiz->id, $subject->id]) }}'">
                            <div class="card-body text-center p-4">
                                <i class="fas fa-book text-primary mb-3" style="font-size: 2rem;"></i>
                                <h5 class="card-title">{{ $subject->name }}</h5>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
