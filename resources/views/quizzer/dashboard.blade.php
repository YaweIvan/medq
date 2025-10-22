<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzer Dashboard - MedQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    @include('components.topnav')
    @include('components.quizzer_sidebar')

    <div class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="text-center mb-5">
                        <h2 class="fw-bold text-dark mb-3">Welcome to Your Dashboard</h2>
                        <p class="text-muted">Select a quiz to begin your medical knowledge assessment</p>
                    </div>

                    @if($activeQuizzes->count() > 0)
                        <div class="row g-3">
                            @foreach($activeQuizzes as $quiz)
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body text-center p-3">
                                            <div class="mb-2">
                                                <i class="fas fa-clipboard-list text-primary" style="font-size: 2rem;"></i>
                                            </div>
                                            <h5 class="card-title text-dark mb-2">{{ $quiz->title }}</h5>
                                            <p class="card-text text-muted small mb-3">{{ Str::limit($quiz->description, 60) }}</p>
                                            <a href="{{ route('quizzer.quiz.subjects', $quiz->id) }}" class="btn btn-primary px-3">
                                                <i class="fas fa-play me-1"></i>Start Quiz
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No Active Quizzes</h5>
                            <p class="text-muted">No quizzes have been assigned to you at this time.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>