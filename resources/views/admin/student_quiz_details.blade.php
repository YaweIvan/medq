<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->name }} - {{ $quiz->title }}</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    @include('components.topnav')
    @include('components.sidebar')

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">{{ $user->name }}</h2>
                    <p class="text-muted mb-0">{{ $quiz->title }} - Detailed Performance</p>
                </div>
                <a href="{{ route('admin.quiz.leaderboard', $quiz->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Leaderboard
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-clipboard-list fa-2x text-primary mb-2"></i>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                            <small class="text-muted">Questions Attempted</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h3 class="mb-0">{{ $stats['correct'] }}</h3>
                            <small class="text-muted">Correct Answers</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                            <h3 class="mb-0">{{ $stats['incorrect'] }}</h3>
                            <small class="text-muted">Incorrect Answers</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                            <h3 class="mb-0">{{ $stats['unanswered'] }}</h3>
                            <small class="text-muted">Timed Out / Unanswered</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accuracy row -->
            <div class="row g-4 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-percentage fa-2x text-info mb-2"></i>
                            <h3 class="mb-0">{{ number_format($stats['accuracy'], 1) }}%</h3>
                            <small class="text-muted">Accuracy</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questions Display -->
            <div class="row g-4">
                <!-- Passed Questions -->
                <div class="col-lg-4">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Correct ({{ $stats['correct'] }})</h5>
                        </div>
                        <div class="card-body">
                            @php $passedQuestions = $attempts->where('is_correct', true); @endphp
                            @if($passedQuestions->count() > 0)
                                @foreach($passedQuestions as $attempt)
                                    @if($attempt->question)
                                    <div class="card mb-3 border-success">
                                        <div class="card-body bg-success bg-opacity-10">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="badge bg-secondary">{{ $attempt->question->subject->name ?? 'N/A' }}</span>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>{{ $attempt->submitted_at ? \Carbon\Carbon::parse($attempt->submitted_at)->format('M j, Y') : '' }}
                                                </small>
                                            </div>
                                            <h6 class="fw-bold mb-3">{!! $attempt->question->question !!}</h6>
                                            <div class="mt-2">
                                                <small class="text-muted">Selected: <strong class="text-success">{{ $attempt->selected_answer }}</strong></small>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            @else
                                <p class="text-center text-muted py-4">No correct answers</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Incorrect Questions -->
                <div class="col-lg-4">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="fas fa-times-circle me-2"></i>Incorrect ({{ $stats['incorrect'] }})</h5>
                        </div>
                        <div class="card-body">
                            @php $incorrectQuestions = $attempts->where('is_correct', false)->where('is_auto_expired', false); @endphp
                            @if($incorrectQuestions->count() > 0)
                                @foreach($incorrectQuestions as $attempt)
                                    @if($attempt->question)
                                    <div class="card mb-3 border-danger">
                                        <div class="card-body bg-danger bg-opacity-10">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="badge bg-secondary">{{ $attempt->question->subject->name ?? 'N/A' }}</span>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>{{ $attempt->submitted_at ? \Carbon\Carbon::parse($attempt->submitted_at)->format('M j, Y') : '' }}
                                                </small>
                                            </div>
                                            <h6 class="fw-bold mb-3">{!! $attempt->question->question !!}</h6>
                                            <div class="mt-2">
                                                <small class="text-muted">Selected: <strong class="text-danger">{{ $attempt->selected_answer }}</strong> | Correct: <strong class="text-success">{{ $attempt->question->correct_answer }}</strong></small>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            @else
                                <p class="text-center text-muted py-4">No incorrect answers</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Unanswered / Timed Out -->
                <div class="col-lg-4">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Timed Out / Unanswered ({{ $stats['unanswered'] }})</h5>
                        </div>
                        <div class="card-body">
                            @php $unansweredQuestions = $attempts->where('is_correct', false)->where('is_auto_expired', true); @endphp
                            @if($unansweredQuestions->count() > 0)
                                @foreach($unansweredQuestions as $attempt)
                                    @if($attempt->question)
                                    <div class="card mb-3 border-warning">
                                        <div class="card-body bg-warning bg-opacity-10">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="badge bg-secondary">{{ $attempt->question->subject->name ?? 'N/A' }}</span>
                                                <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Timed Out</span>
                                            </div>
                                            <h6 class="fw-bold mb-3">{!! $attempt->question->question !!}</h6>
                                            <div class="mt-2">
                                                <small class="text-muted">Correct answer was: <strong class="text-success">{{ $attempt->question->correct_answer }}</strong></small>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            @else
                                <p class="text-center text-muted py-4">No timed-out questions</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
