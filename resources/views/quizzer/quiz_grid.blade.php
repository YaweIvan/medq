<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Grid - MedQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
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

    <!-- Start Quiz Overlay -->
    <div class="start-quiz-overlay" id="startOverlay">
        <div class="start-quiz-card">
            <h3><i class="fas fa-clock me-2"></i>Ready to Start?</h3>
            <p>You have <strong>2.5 minutes</strong> to answer <strong>5 questions</strong><br>
            Timer will start when you click below</p>
            <button class="btn btn-primary" onclick="startQuiz()">
                <i class="fas fa-play me-2"></i>Start Quiz
            </button>
        </div>
    </div>

    <!-- Total Quiz Timer -->
    <div class="timer-container" style="display: none;" id="timerContainer">
        <div class="timer-label">Time Left</div>
        <div class="timer-display" id="totalTimer">2:30</div>
        <div class="timer-label mt-1" style="font-size: 0.65rem;">Q: <span id="questionsCount">{{ $attemptedCount }}/5</span></div>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">{{ $quiz->title }}</h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-book me-2"></i>{{ $subject->name }} - Select Questions ({{ $attemptedCount }}/5 attempted)
                    </p>
                </div>
                <a href="{{ route('quizzer.quiz.subjects', $quiz->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Subjects
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="question-grid">
                        @foreach($questions as $index => $question)
                            @php
                                $isAttempted = $attemptedQuestions->contains($question->id);
                                $isLocked = $question->is_used;
                                $canAttempt = !$isAttempted && !$isLocked && $attemptedCount < 5;
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
        const TOTAL_TIME = 150; // 2.5 minutes in seconds
        const MAX_QUESTIONS = 5;
        let totalTimeRemaining = TOTAL_TIME;
        let timerInterval;
        let quizStarted = false;
        const quizKey = 'quiz_{{ $quiz->id }}_{{ $subject->id }}';
        const startTimeKey = `${quizKey}_start`;
        const questionsKey = `${quizKey}_questions`;

        function startQuiz() {
            // Hide overlay, show timer
            document.getElementById('startOverlay').style.display = 'none';
            document.getElementById('timerContainer').style.display = 'block';
            
            // Mark quiz as started
            quizStarted = true;
            localStorage.setItem(startTimeKey, Date.now().toString());
            localStorage.setItem(questionsKey, '{{ $attemptedCount }}');
            
            // Start the timer
            totalTimeRemaining = TOTAL_TIME;
            startTimer();
        }

        function initializeTimer() {
            const startTime = localStorage.getItem(startTimeKey);
            
            if (startTime) {
                // Quiz already started, restore timer
                const elapsed = Math.floor((Date.now() - parseInt(startTime)) / 1000);
                totalTimeRemaining = Math.max(0, TOTAL_TIME - elapsed);
                
                if (totalTimeRemaining > 0) {
                    quizStarted = true;
                    document.getElementById('startOverlay').style.display = 'none';
                    document.getElementById('timerContainer').style.display = 'block';
                    updateTimerDisplay();
                    startTimer();
                } else {
                    // Time expired
                    endQuiz('timeout');
                }
            } else {
                // Show start overlay
                document.getElementById('startOverlay').style.display = 'flex';
            }
        }

        function startTimer() {
            if (timerInterval) clearInterval(timerInterval);
            
            timerInterval = setInterval(() => {
                totalTimeRemaining--;
                updateTimerDisplay();
                
                if (totalTimeRemaining <= 0) {
                    endQuiz('timeout');
                }
            }, 1000);
        }

        function updateTimerDisplay() {
            const minutes = Math.floor(totalTimeRemaining / 60);
            const seconds = totalTimeRemaining % 60;
            const display = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            const timerElement = document.getElementById('totalTimer');
            timerElement.textContent = display;
            
            if (totalTimeRemaining <= 30) {
                timerElement.classList.add('danger');
            } else if (totalTimeRemaining <= 60) {
                timerElement.classList.add('warning');
            }
        }

        function attemptQuestion(questionId) {
            if (!quizStarted) {
                alert('Please start the quiz first by clicking the Start Quiz button!');
                return;
            }
            
            const currentQuestions = parseInt(localStorage.getItem(questionsKey) || '{{ $attemptedCount }}');
            
            if (currentQuestions >= MAX_QUESTIONS) {
                alert('You have completed all 5 questions!');
                endQuiz('complete');
                return;
            }
            
            if (totalTimeRemaining <= 0) {
                alert('Time is up!');
                endQuiz('timeout');
                return;
            }
            
            if (questionId > 0) {
                window.location.href = `/quizzer/question/${questionId}`;
            }
        }

        function endQuiz(reason) {
            clearInterval(timerInterval);
            localStorage.removeItem(startTimeKey);
            localStorage.removeItem(questionsKey);
            
            const message = reason === 'timeout' 
                ? 'Time is up! Quiz has ended.' 
                : 'Congratulations! You completed all 5 questions!';
            
            alert(message);
            window.location.href = '{{ route("quizzer.quiz.subjects", $quiz->id) }}';
        }

        // Check if quiz should auto-end
        function checkQuizStatus() {
            const currentQuestions = parseInt(localStorage.getItem(questionsKey) || '{{ $attemptedCount }}');
            document.getElementById('questionsCount').textContent = `${currentQuestions}/5`;
            
            if (currentQuestions >= MAX_QUESTIONS) {
                setTimeout(() => endQuiz('complete'), 1000);
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeTimer();
            checkQuizStatus();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>