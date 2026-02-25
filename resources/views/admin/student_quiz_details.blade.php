<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->name }} - {{ $quiz->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
                            <h3 class="mb-0">{{ $stats['failed'] }}</h3>
                            <small class="text-muted">Failed Answers</small>
                        </div>
                    </div>
                </div>
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
                <div class="col-lg-6">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Passed Questions ({{ $stats['correct'] }})</h5>
                        </div>
                        <div class="card-body">
                            @php $passedQuestions = $attempts->where('is_correct', true); @endphp
                            @if($passedQuestions->count() > 0)
                                @foreach($passedQuestions as $index => $attempt)
                                    @if($attempt->question)
                                    <div class="card mb-3 border-success">
                                        <div class="card-body bg-success bg-opacity-10">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="badge bg-secondary">{{ $attempt->question->subject->name ?? 'N/A' }}</span>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>{{ $attempt->created_at->format('M j, Y') }}
                                                    <i class="fas fa-clock ms-2 me-1"></i>{{ $attempt->created_at->format('g:i A') }}
                                                </small>
                                            </div>
                                            <h6 class="fw-bold mb-3">{!! $attempt->question->question !!}</h6>
                                            <div class="options">
                                                <div class="option mb-2 p-2 rounded {{ $attempt->question->correct_answer == 'A' ? 'bg-success text-white' : 'bg-light' }}">
                                                    <strong>A:</strong> {!! $attempt->question->option_a !!}
                                                    @if($attempt->selected_answer == 'A')
                                                        <i class="fas fa-check-circle float-end"></i>
                                                    @endif
                                                </div>
                                                <div class="option mb-2 p-2 rounded {{ $attempt->question->correct_answer == 'B' ? 'bg-success text-white' : 'bg-light' }}">
                                                    <strong>B:</strong> {!! $attempt->question->option_b !!}
                                                    @if($attempt->selected_answer == 'B')
                                                        <i class="fas fa-check-circle float-end"></i>
                                                    @endif
                                                </div>
                                                <div class="option mb-2 p-2 rounded {{ $attempt->question->correct_answer == 'C' ? 'bg-success text-white' : 'bg-light' }}">
                                                    <strong>C:</strong> {!! $attempt->question->option_c !!}
                                                    @if($attempt->selected_answer == 'C')
                                                        <i class="fas fa-check-circle float-end"></i>
                                                    @endif
                                                </div>
                                                <div class="option mb-2 p-2 rounded {{ $attempt->question->correct_answer == 'D' ? 'bg-success text-white' : 'bg-light' }}">
                                                    <strong>D:</strong> {!! $attempt->question->option_d !!}
                                                    @if($attempt->selected_answer == 'D')
                                                        <i class="fas fa-check-circle float-end"></i>
                                                    @endif
                                                </div>
                                                @if($attempt->question->option_e)
                                                <div class="option mb-2 p-2 rounded {{ $attempt->question->correct_answer == 'E' ? 'bg-success text-white' : 'bg-light' }}">
                                                    <strong>E:</strong> {!! $attempt->question->option_e !!}
                                                    @if($attempt->selected_answer == 'E')
                                                        <i class="fas fa-check-circle float-end"></i>
                                                    @endif
                                                </div>
                                                @endif
                                            </div>
                                            <div class="mt-2">
                                                <small class="text-muted">Selected Answer: <strong class="text-success">{{ $attempt->selected_answer }}</strong></small>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            @else
                                <p class="text-center text-muted py-4">No passed questions</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Failed Questions -->
                <div class="col-lg-6">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="fas fa-times-circle me-2"></i>Failed Questions ({{ $stats['failed'] }})</h5>
                        </div>
                        <div class="card-body">
                            @php $failedQuestions = $attempts->where('is_correct', false); @endphp
                            @if($failedQuestions->count() > 0)
                                @foreach($failedQuestions as $index => $attempt)
                                    @if($attempt->question)
                                    <div class="card mb-3 border-danger">
                                        <div class="card-body bg-danger bg-opacity-10">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="badge bg-secondary">{{ $attempt->question->subject->name ?? 'N/A' }}</span>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>{{ $attempt->created_at->format('M j, Y') }}
                                                    <i class="fas fa-clock ms-2 me-1"></i>{{ $attempt->created_at->format('g:i A') }}
                                                </small>
                                            </div>
                                            <h6 class="fw-bold mb-3">{!! $attempt->question->question !!}</h6>
                                            <div class="options">
                                                <div class="option mb-2 p-2 rounded {{ $attempt->question->correct_answer == 'A' ? 'bg-success text-white' : ($attempt->selected_answer == 'A' ? 'bg-danger text-white' : 'bg-light') }}">
                                                    <strong>A:</strong> {!! $attempt->question->option_a !!}
                                                    @if($attempt->selected_answer == 'A')
                                                        <i class="fas fa-times-circle float-end"></i>
                                                    @endif
                                                    @if($attempt->question->correct_answer == 'A')
                                                        <i class="fas fa-check-circle float-end"></i>
                                                    @endif
                                                </div>
                                                <div class="option mb-2 p-2 rounded {{ $attempt->question->correct_answer == 'B' ? 'bg-success text-white' : ($attempt->selected_answer == 'B' ? 'bg-danger text-white' : 'bg-light') }}">
                                                    <strong>B:</strong> {!! $attempt->question->option_b !!}
                                                    @if($attempt->selected_answer == 'B')
                                                        <i class="fas fa-times-circle float-end"></i>
                                                    @endif
                                                    @if($attempt->question->correct_answer == 'B')
                                                        <i class="fas fa-check-circle float-end"></i>
                                                    @endif
                                                </div>
                                                <div class="option mb-2 p-2 rounded {{ $attempt->question->correct_answer == 'C' ? 'bg-success text-white' : ($attempt->selected_answer == 'C' ? 'bg-danger text-white' : 'bg-light') }}">
                                                    <strong>C:</strong> {!! $attempt->question->option_c !!}
                                                    @if($attempt->selected_answer == 'C')
                                                        <i class="fas fa-times-circle float-end"></i>
                                                    @endif
                                                    @if($attempt->question->correct_answer == 'C')
                                                        <i class="fas fa-check-circle float-end"></i>
                                                    @endif
                                                </div>
                                                <div class="option mb-2 p-2 rounded {{ $attempt->question->correct_answer == 'D' ? 'bg-success text-white' : ($attempt->selected_answer == 'D' ? 'bg-danger text-white' : 'bg-light') }}">
                                                    <strong>D:</strong> {!! $attempt->question->option_d !!}
                                                    @if($attempt->selected_answer == 'D')
                                                        <i class="fas fa-times-circle float-end"></i>
                                                    @endif
                                                    @if($attempt->question->correct_answer == 'D')
                                                        <i class="fas fa-check-circle float-end"></i>
                                                    @endif
                                                </div>
                                                @if($attempt->question->option_e)
                                                <div class="option mb-2 p-2 rounded {{ $attempt->question->correct_answer == 'E' ? 'bg-success text-white' : ($attempt->selected_answer == 'E' ? 'bg-danger text-white' : 'bg-light') }}">
                                                    <strong>E:</strong> {!! $attempt->question->option_e !!}
                                                    @if($attempt->selected_answer == 'E')
                                                        <i class="fas fa-times-circle float-end"></i>
                                                    @endif
                                                    @if($attempt->question->correct_answer == 'E')
                                                        <i class="fas fa-check-circle float-end"></i>
                                                    @endif
                                                </div>
                                                @endif
                                            </div>
                                            <div class="mt-2">
                                                <small class="text-muted">Selected: <strong class="text-danger">{{ $attempt->selected_answer }}</strong> | Correct: <strong class="text-success">{{ $attempt->question->correct_answer }}</strong></small>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            @else
                                <p class="text-center text-muted py-4">No failed questions</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
