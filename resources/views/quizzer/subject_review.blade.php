<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Review - MedQ</title>
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
        .correct-row {
            background-color: rgba(34, 197, 94, 0.18) !important;
        }
        .incorrect-row {
            background-color: rgba(239, 68, 68, 0.18) !important;
        }
        .unanswered-row {
            background-color: rgba(234, 179, 8, 0.18) !important;
        }
        .correct-row td {
            background-color: rgba(34, 197, 94, 0.18) !important;
            color: #e2e8f0 !important;
        }
        .incorrect-row td {
            background-color: rgba(239, 68, 68, 0.18) !important;
            color: #e2e8f0 !important;
        }
        .unanswered-row td {
            background-color: rgba(234, 179, 8, 0.18) !important;
            color: #e2e8f0 !important;
        }
    </style>
</head>
<body>
    @include('components.topnav')
    @include('components.quizzer_sidebar')

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">{{ $quiz->title }} - {{ $subject->name }}</h2>
                    <p class="text-muted mb-0">Review your answers</p>
                </div>
                <a href="{{ route('quizzer.quiz.review', $quiz->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Subjects
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Question Number</th>
                                    <th>Question</th>
                                    <th class="text-center">Answer Selected</th>
                                    <th class="text-center">Correct Answer</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attempts as $attempt)
                                    @php
                                        $rowClass = $attempt->is_correct
                                            ? 'correct-row'
                                            : ($attempt->is_auto_expired ? 'unanswered-row' : 'incorrect-row');
                                    @endphp
                                    <tr class="{{ $rowClass }}">
                                        <td>Question #{{ $attempt->question_number }}</td>
                                        <td>
                                            {!! $attempt->question->question !!}
                                            @if($attempt->question->diagram)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/question_diagrams/'.$attempt->question->diagram) }}" alt="Diagram" class="img-thumbnail" style="max-width: 200px;">
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($attempt->is_auto_expired)
                                                <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Timed Out</span>
                                            @else
                                                <strong>{{ $attempt->selected_answer ?: 'No Answer' }}</strong>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $attempt->question->correct_answer }}</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
