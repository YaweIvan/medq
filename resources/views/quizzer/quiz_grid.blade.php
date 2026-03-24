<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Grid - MedQ</title>
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
        .timer-container {
            position: fixed;
            top: 80px;
            right: 20px;
            background: #ffffff;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            z-index: 1000;
            min-width: 160px;
        }
        
        .timer-label {
            font-size: 0.7rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .timer-display {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
        }
        
        .timer-display.warning {
            color: #f59e0b;
        }
        
        .timer-display.danger {
            color: #ef4444;
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        
        .start-quiz-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }
        
        .start-quiz-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            max-width: 400px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            position: relative;
        }
        
        .start-quiz-card h3 {
            margin-bottom: 1rem;
            color: #1f2937;
        }
        
        .start-quiz-card p {
            color: #6b7280;
            margin-bottom: 1.5rem;
        }
        
        .start-quiz-card .btn {
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
        }
        
        .question-grid {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 0.5rem;
            margin: 2rem 0;
        }
        
        .question-number {
            aspect-ratio: 1;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .question-number:hover {
            border-color: var(--primary);
            background: #f8fafc;
        }
        
        .question-number.attempted {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }
        
        .question-number.locked {
            background: #6b7280;
            color: white;
            border-color: #6b7280;
            cursor: not-allowed;
        }
        
        @media (max-width: 768px) {
            .question-grid {
                grid-template-columns: repeat(5, 1fr);
            }
            
            .timer-container {
                top: 70px;
                right: 10px;
                padding: 0.75rem 1rem;
            }
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
                    <h2 class="fw-bold text-dark mb-1">{{ $quiz->title }}</h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-book me-2"></i>{{ $subject->name }} - Select Questions ({{ $attemptedCount }}/{{ $subject->max_questions ?? 5 }} attempted)
                    </p>
                </div>
                <a href="{{ route('quizzer.quiz.subjects', $quiz->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Subjects
                </a>
            </div>

            @if($attemptedCount == 0)
            <div class="start-quiz-overlay" id="startOverlay">
                <div class="start-quiz-card">
                    <button class="btn btn-sm btn-close position-absolute top-0 end-0 m-3" onclick="closeTimerMessage()"></button>
                    <h3><i class="fas fa-clock text-primary me-2"></i>Timer Starting!</h3>
                    <p>The timer will begin counting down automatically.<br>You will have {{ $questions->first()->time_per_question ?? 60 }} seconds to answer each question.</p>
                    <p class="mb-0"><i class="fas fa-info-circle text-info me-2"></i><strong>{{ $subject->max_questions ?? 5 }} questions</strong> maximum per attempt in this subject.</p>
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="question-grid">
                        @foreach($questions as $index => $question)
                            @php
                                $isAttempted = $attemptedQuestions->contains($question->id);
                                $isLocked = $question->is_used;
                                $maxQuestions = $subject->max_questions ?? 5;
                                $canAttempt = !$isAttempted && !$isLocked && $attemptedCount < $maxQuestions;
                            @endphp
                            
                            <div class="question-number 
                                {{ $isAttempted ? 'attempted' : '' }}
                                {{ $isLocked ? 'locked' : '' }}"
                                @if($canAttempt) 
                                    onclick="attemptQuestion({{ $question->id }})"
                                @endif>
                                {{ $index + 1 }}
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="question-number me-2" style="width: 30px; height: 30px; font-size: 0.8rem;"></div>
                                <span>Available</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="question-number attempted me-2" style="width: 30px; height: 30px; font-size: 0.8rem;"></div>
                                <span>Attempted</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="question-number locked me-2" style="width: 30px; height: 30px; font-size: 0.8rem;"></div>
                                <span>Locked/Used</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const MAX_QUESTIONS = {{ $subject->max_questions ?? 5 }};
        const hasActiveTimer = {{ $hasActiveTimer ? 'true' : 'false' }};

        function closeTimerMessage() {
            document.getElementById('startOverlay').style.display = 'none';
        }

        function attemptQuestion(questionId) {
            // Check if timer is running for a DIFFERENT question
            const timerKeys = Object.keys(localStorage).filter(key => key.startsWith('quiz_timer_start_'));
            for (let key of timerKeys) {
                const currentQuestionId = key.replace('quiz_timer_start_', '');
                if (currentQuestionId != questionId) {
                    const startTime = parseInt(localStorage.getItem(key));
                    const elapsed = (Date.now() - startTime) / 1000;
                    if (elapsed < 60) {
                        alert('Please complete or wait for the current question timer to finish before attempting another question.');
                        return;
                    }
                }
            }
            
            // Check if maximum questions already completed
            if ({{ $attemptedCount }} >= MAX_QUESTIONS) {
                alert(`You have completed all ${MAX_QUESTIONS} questions for this subject!`);
                window.location.href = '{{ route("quizzer.quiz.subjects", $quiz->id) }}';
                return;
            }
            
            if (questionId > 0) {
                window.location.href = `/quizzer/question/${questionId}`;
            }
        }

        // Check if quiz is complete on page load
        document.addEventListener('DOMContentLoaded', function() {
            if ({{ $attemptedCount }} >= MAX_QUESTIONS) {
                setTimeout(() => {
                    alert(`Congratulations! You completed all ${MAX_QUESTIONS} questions for this subject!`);
                    window.location.href = '{{ route("quizzer.quiz.subjects", $quiz->id) }}';
                }, 500);
            }
        });
    </script>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>