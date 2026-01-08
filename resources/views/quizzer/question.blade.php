<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question - MedQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .question-timer {
            position: fixed;
            top: 80px;
            right: 20px;
            background: #ffffff;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            z-index: 1000;
            min-width: 100px;
            text-align: center;
        }
        
        .question-timer-label {
            font-size: 0.65rem;
            color: #6b7280;
            margin-bottom: 0.1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .question-timer-display {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }
        
        .question-timer-display.warning {
            color: #f59e0b;
        }
        
        .question-timer-display.danger {
            color: #ef4444;
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        

    </style>
</head>
<body>
    @include('components.topnav')
    @include('components.quizzer_sidebar')

    <!-- Total Quiz Timer -->
    <div class="question-timer">
        <div class="question-timer-label">Time Left</div>
        <div class="question-timer-display" id="questionTimer">2:30</div>
    </div>



    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-dark">Question</h2>
                <a href="{{ route('quizzer.question.grid', [$question->quiz_id, $question->subject_id]) }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Grid
                </a>
            </div>

            <div class="question-card">
                    <div class="question-header">
                        <h4>{{ $question->quiz->title }} - {{ $question->subject->name }}</h4>
                    </div>
                    
                    <div class="question-content">
                        <h5 class="question-text">{{ $question->question }}</h5>
                        
                        <div class="options mt-4">
                            <div class="option" data-answer="A">
                                <span class="option-label">A</span>
                                <span class="option-text">{{ $question->option_a }}</span>
                            </div>
                            <div class="option" data-answer="B">
                                <span class="option-label">B</span>
                                <span class="option-text">{{ $question->option_b }}</span>
                            </div>
                            <div class="option" data-answer="C">
                                <span class="option-label">C</span>
                                <span class="option-text">{{ $question->option_c }}</span>
                            </div>
                            <div class="option" data-answer="D">
                                <span class="option-label">D</span>
                                <span class="option-text">{{ $question->option_d }}</span>
                            </div>
                        </div>
                        
                        <button id="submitAnswer" class="btn btn-primary mt-4" disabled>
                            Submit Answer
                        </button>
                    </div>
                </div>
        </div>
    </div>

<style>
.question-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.question-header {
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 1rem;
    margin-bottom: 2rem;
}

.question-text {
    color: #1f2937;
    line-height: 1.6;
    margin-bottom: 2rem;
}

.option {
    display: flex;
    align-items: center;
    padding: 1rem;
    margin-bottom: 0.5rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.option:hover {
    border-color: #93c5fd;
    background-color: #f8fafc;
}

.option.selected {
    border-color: #3b82f6;
    background-color: #eff6ff;
}

.option-label {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: #f3f4f6;
    border-radius: 50%;
    font-weight: 600;
    margin-right: 1rem;
    color: #374151;
}

.option.selected .option-label {
    background: #3b82f6;
    color: white;
}

.option-text {
    flex: 1;
    color: #374151;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const options = document.querySelectorAll('.option');
    const submitBtn = document.getElementById('submitAnswer');
    let selectedAnswer = null;
    let questionTimerInterval;
    let hasSubmitted = false;
    
    // Total quiz timer (150 seconds = 2.5 minutes)
    const quizKey = 'quiz_{{ $question->quiz_id }}_{{ $question->subject_id }}';
    const timerKey = `${quizKey}_timer`;
    const timerStartKey = `${quizKey}_timer_start`;
    
    // Initialize or retrieve timer
    let totalTimeRemaining;
    const savedTime = localStorage.getItem(timerKey);
    const timerStart = localStorage.getItem(timerStartKey);
    
    if (savedTime && timerStart) {
        // Calculate elapsed time since last save
        const elapsed = Math.floor((Date.now() - parseInt(timerStart)) / 1000);
        totalTimeRemaining = Math.max(0, parseInt(savedTime) - elapsed);
    } else {
        // First question - start with 150 seconds (2.5 minutes)
        totalTimeRemaining = 150;
    }
    
    // Save initial state
    localStorage.setItem(timerKey, totalTimeRemaining.toString());
    localStorage.setItem(timerStartKey, Date.now().toString());

    // Start total quiz timer
    function startQuestionTimer() {
        updateQuestionTimer();
        questionTimerInterval = setInterval(() => {
            totalTimeRemaining--;
            localStorage.setItem(timerKey, totalTimeRemaining.toString());
            localStorage.setItem(timerStartKey, Date.now().toString());
            updateQuestionTimer();
            
            if (totalTimeRemaining <= 0) {
                autoSubmit();
            }
        }, 1000);
    }

    function updateQuestionTimer() {
        const timerElement = document.getElementById('questionTimer');
        const minutes = Math.floor(totalTimeRemaining / 60);
        const seconds = totalTimeRemaining % 60;
        timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        if (totalTimeRemaining <= 10) {
            timerElement.classList.add('danger');
        } else if (totalTimeRemaining <= 30) {
            timerElement.classList.add('warning');
        }
    }

    function autoSubmit() {
        clearInterval(questionTimerInterval);
        
        if (hasSubmitted) return;
        
        // Show time's up alert
        if (!selectedAnswer) {
            alert("Time's Up!");
        }
        
        submitAnswer();
    }

    options.forEach(option => {
        option.addEventListener('click', function() {
            if (hasSubmitted) return;
            
            options.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            selectedAnswer = this.dataset.answer;
            submitBtn.disabled = false;
        });
    });

    submitBtn.addEventListener('click', function() {
        if (hasSubmitted) return;
        submitAnswer();
    });

    function submitAnswer() {
        if (hasSubmitted) return;
        hasSubmitted = true;
        
        // Stop timer immediately
        clearInterval(questionTimerInterval);
        
        submitBtn.disabled = true;
        options.forEach(opt => opt.style.pointerEvents = 'none');

        fetch('{{ route("quizzer.submit.answer") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                question_id: {{ $question->id }},
                selected_answer: selectedAnswer || '',
                time_taken: 30
            })
        })
        .then(response => response.json())
        .then(data => {
            // Update localStorage for question count
            const quizKey = 'quiz_{{ $question->quiz_id }}_{{ $question->subject_id }}';
            const questionsKey = `${quizKey}_questions`;
            const currentCount = parseInt(localStorage.getItem(questionsKey) || '0');
            localStorage.setItem(questionsKey, (currentCount + 1).toString());
            
            // Show visual feedback on options
            if (selectedAnswer) {
                const selectedOption = document.querySelector('.option.selected');
                if (data.correct) {
                    selectedOption.style.borderColor = '#10b981';
                    selectedOption.style.backgroundColor = '#dcfce7';
                } else {
                    selectedOption.style.borderColor = '#ef4444';
                    selectedOption.style.backgroundColor = '#fee2e2';
                    
                    const correctOption = document.querySelector(`[data-answer="${data.correct_answer}"]`);
                    correctOption.style.borderColor = '#10b981';
                    correctOption.style.backgroundColor = '#dcfce7';
                }
            }
            
            // Redirect back to grid
            setTimeout(() => {
                if (totalTimeRemaining <= 0) {
                    localStorage.removeItem(timerKey);
                    localStorage.removeItem(timerStartKey);
                }
                window.location.href = '{{ route("quizzer.question.grid", [$question->quiz_id, $question->subject_id]) }}';
            }, 500);
        });
    }



    // Initialize timer on page load
    startQuestionTimer();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>