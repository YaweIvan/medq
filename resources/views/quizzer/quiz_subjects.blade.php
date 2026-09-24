<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Subjects - MedQ</title>
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
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="text-center flex-grow-1">
                    <h2 class="fw-bold text-white mb-2">{{ $quiz->title }}</h2>
                    <p class="text-white">Select a subject to begin</p>
                </div>
                <a href="{{ route('quizzer.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Quizzes
                </a>
            </div>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            
            <div class="row g-3">
                @foreach($subjects as $subject)
                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center p-3">
                                <div class="mb-2">
                                    <i class="fas fa-book text-info" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="card-title text-white mb-2">{{ $subject->name }}</h5>
                                <p class="card-text mb-3">
                                    <span class="badge bg-primary">{{ $subject->available_questions }} questions available</span>
                                    <br><span class="badge bg-info mt-2">{{ $subject->max_questions ?? 5 }} questions per attempt ({{ $subject->marks_per_question ?? 1 }} marks each)</span>
                                </p>
                                @if($subject->available_questions > 0)
                                    <a href="{{ route('quizzer.question.grid', [$quiz->id, $subject->id]) }}" class="btn btn-info px-3">
                                        <i class="fas fa-th me-1"></i>View Questions Grid
                                    </a>
                                @else
                                    <button class="btn btn-secondary px-3" disabled>
                                        <i class="fas fa-lock me-1"></i>No Questions Available
                                    </button>
                                @endif
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