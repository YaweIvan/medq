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
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            z-index: 1000;
            min-width: 120px;
            text-align: center;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .question-timer.danger {
            border-color: #ef4444;
            background: #fee2e2;
        }
        
        .question-timer-label {
            font-size: 0.7rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        
        .question-timer-display {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
        }
        
        .question-timer-display.danger {
            color: #ef4444;
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body>
    @include('components.topnav')
    @include('components.quizzer_sidebar')

    <!-- Question Timer -->
    <div class="question-timer" id="timerContainer" style="display: none;">
        <div class="question-timer-label">Time Left</div>
        <div class="question-timer-display" id="questionTimer">60</div>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-dark">Question #{{ $questionNumber }}</h2>
                <a href="{{ route('quizzer.question.grid', [$question->quiz_id, $question->subject_id]) }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Grid
                </a>
            </div>

            <div class="question-card">
                    <div class="question-content">
                        <h5 class="question-text">{!! $question->question !!}</h5>
                        
                        <div class="options mt-4">
                            <div class="option" data-answer="A">
                                <span class="option-label">A</span>
                                <span class="option-text">{!! $question->option_a !!}</span>
                            </div>
                            <div class="option" data-answer="B">
                                <span class="option-label">B</span>
                                <span class="option-text">{!! $question->option_b !!}</span>
                            </div>
                            <div class="option" data-answer="C">
                                <span class="option-label">C</span>
                                <span class="option-text">{!! $question->option_c !!}</span>
                            </div>
                            <div class="option" data-answer="D">
                                <span class="option-label">D</span>
                                <span class="option-text">{!! $question->option_d !!}</span>
                            </div>
                            @if($question->option_e)
                            <div class="option" data-answer="E">
                                <span class="option-label">E</span>
                                <span class="option-text">{!! $question->option_e !!}</span>
                            </div>
                            @endif
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

.question-text {
    color: #1f2937;
    line-height: 1.8;
    margin-bottom: 2rem;
    font-size: 1.1rem;
    word-wrap: break-word;
    overflow-wrap: break-word;
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
    word-wrap: break-word;
    overflow-wrap: break-word;
}
</style>

<script>
// Sound effects - checks for uploaded files first, falls back to generated sounds
function playCorrectSound() {
    // Try to play uploaded sound file
    const audio = new Audio();
    
    // Try different formats
    const formats = [
        { src: '{{ asset("sounds/correct.mp3") }}', type: 'audio/mpeg' },
        { src: '{{ asset("sounds/correct.wav") }}', type: 'audio/wav' },
        { src: '{{ asset("sounds/correct.ogg") }}', type: 'audio/ogg' }
    ];
    
    let played = false;
    
    for (let format of formats) {
        if (audio.canPlayType(format.type)) {
            audio.src = format.src;
            audio.play().then(() => {
                played = true;
            }).catch(() => {
                // File doesn't exist, will use generated sound
                if (!played) playGeneratedCorrectSound();
            });
            break;
        }
    }
    
    // If no format supported or error, use generated sound
    if (!played) {
        setTimeout(() => playGeneratedCorrectSound(), 100);
    }
}

function playGeneratedCorrectSound() {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    // Pleasant ascending notes for correct answer
    oscillator.frequency.setValueAtTime(523.25, audioContext.currentTime); // C5
    oscillator.frequency.setValueAtTime(659.25, audioContext.currentTime + 0.1); // E5
    oscillator.frequency.setValueAtTime(783.99, audioContext.currentTime + 0.2); // G5
    
    oscillator.type = 'sine';
    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.4);
    
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.4);
}

function playIncorrectSound() {
    // Try to play uploaded sound file
    const audio = new Audio();
    
    // Try different formats
    const formats = [
        { src: '{{ asset("sounds/incorrect.mp3") }}', type: 'audio/mpeg' },
        { src: '{{ asset("sounds/incorrect.wav") }}', type: 'audio/wav' },
        { src: '{{ asset("sounds/incorrect.ogg") }}', type: 'audio/ogg' }
    ];
    
    let played = false;
    
    for (let format of formats) {
        if (audio.canPlayType(format.type)) {
            audio.src = format.src;
            audio.play().then(() => {
                played = true;
            }).catch(() => {
                // File doesn't exist, will use generated sound
                if (!played) playGeneratedIncorrectSound();
            });
            break;
        }
    }
    
    // If no format supported or error, use generated sound
    if (!played) {
        setTimeout(() => playGeneratedIncorrectSound(), 100);
    }
}

function playGeneratedIncorrectSound() {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    // Descending note for incorrect answer
    oscillator.frequency.setValueAtTime(392.00, audioContext.currentTime); // G4
    oscillator.frequency.setValueAtTime(329.63, audioContext.currentTime + 0.15); // E4
    
    oscillator.type = 'sine';
    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
    
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.3);
}

document.addEventListener('DOMContentLoaded', function() {
    const options = document.querySelectorAll('.option');
    const submitBtn = document.getElementById('submitAnswer');
    const questionId = {{ $question->id }};
    const timerStartKey = `quiz_timer_start_${questionId}`;
    const TOTAL_TIME = 60; // 60 seconds per question (1 minute)
    
    let selectedAnswer = null;
    let questionTimerInterval;
    let hasSubmitted = false;
    let timeRemaining = TOTAL_TIME;
    let timerStarted = false;
    let startTimestamp = null;

    // Check if timer was already started for this question
    const savedTimerStart = localStorage.getItem(timerStartKey);
    
    if (savedTimerStart) {
        // Timer was started before - calculate exact remaining time
        startTimestamp = parseInt(savedTimerStart);
        const currentTime = Date.now();
        const elapsedMilliseconds = currentTime - startTimestamp;
        const elapsedSeconds = elapsedMilliseconds / 1000;
        
        // Calculate remaining time and ensure it never goes negative
        timeRemaining = Math.max(0, TOTAL_TIME - elapsedSeconds);
        
        if (timeRemaining > 0.5) { // Allow half-second buffer to prevent premature expiry
            // Resume timer automatically
            timerStarted = true;
            document.getElementById('timerContainer').style.display = 'block';
            options.forEach(opt => opt.style.pointerEvents = 'auto');
            submitBtn.style.pointerEvents = 'auto';
            startCountdown();
        } else {
            // Time expired, auto submit immediately with empty answer
            autoSubmit();
        }
    } else {
        // First time on this question - start timer automatically
        timerStarted = true;
        startTimestamp = Date.now();
        localStorage.setItem(timerStartKey, startTimestamp.toString());
        document.getElementById('timerContainer').style.display = 'block';
        options.forEach(opt => opt.style.pointerEvents = 'auto');
        submitBtn.style.pointerEvents = 'auto';
        startCountdown();
    }

    // Function removed - no longer needed

    function startCountdown() {
        updateTimerDisplay();
        
        questionTimerInterval = setInterval(() => {
            // Calculate precise remaining time from absolute start timestamp
            const currentTime = Date.now();
            const elapsedMilliseconds = currentTime - startTimestamp;
            const elapsedSeconds = elapsedMilliseconds / 1000;
            
            // Calculate remaining time, ensuring it never goes negative
            timeRemaining = Math.max(0, TOTAL_TIME - elapsedSeconds);
            
            updateTimerDisplay();
            
            // Auto submit when time reaches 0
            if (timeRemaining <= 0) {
                clearInterval(questionTimerInterval);
                autoSubmit();
            }
        }, 100); // Update every 100ms for smooth display
    }

    function updateTimerDisplay() {
        const timerElement = document.getElementById('questionTimer');
        const timerContainer = document.getElementById('timerContainer');
        
        // Display as integer, never negative
        const displayTime = Math.max(0, Math.ceil(timeRemaining));
        timerElement.textContent = displayTime;
        
        // Turn red when 10 seconds or below
        if (displayTime <= 10) {
            timerElement.classList.add('danger');
            timerContainer.classList.add('danger');
        }
    }

    function clearTimerStorage() {
        localStorage.removeItem(timerStartKey);
    }

    function autoSubmit() {
        clearInterval(questionTimerInterval);
        
        if (hasSubmitted) return;
        
        hasSubmitted = true;
        clearTimerStorage();
        
        // Disable all options
        options.forEach(opt => opt.style.pointerEvents = 'none');
        submitBtn.style.pointerEvents = 'none';
        
        // Submit with empty answer - marks question as FAILED and LOCKED
        fetch('{{ route("quizzer.submit.answer") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                question_id: questionId,
                selected_answer: '', // Empty = time expired, marked as FAILED (is_correct = false)
                time_taken: TOTAL_TIME
            })
        })
        .then(response => response.json())
        .then(() => {
            // Play incorrect sound for time expiry
            playIncorrectSound();
            
            // Wait 1 second before redirecting to grid
            setTimeout(() => {
                window.location.href = '{{ route("quizzer.question.grid", [$question->quiz_id, $question->subject_id]) }}';
            }, 1000);
        })
        .catch(() => {
            // Even on error, redirect to grid after 1 second
            setTimeout(() => {
                window.location.href = '{{ route("quizzer.question.grid", [$question->quiz_id, $question->subject_id]) }}';
            }, 1000);
        });
    }

    options.forEach(option => {
        option.addEventListener('click', function() {
            if (hasSubmitted || !timerStarted) return;
            
            options.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            selectedAnswer = this.dataset.answer;
            submitBtn.disabled = false;
        });
    });

    submitBtn.addEventListener('click', function() {
        if (hasSubmitted || !timerStarted) return;
        submitAnswer();
    });

    function submitAnswer() {
        if (hasSubmitted) return;
        hasSubmitted = true;
        
        // Stop timer immediately
        clearInterval(questionTimerInterval);
        clearTimerStorage();
        
        submitBtn.disabled = true;
        options.forEach(opt => opt.style.pointerEvents = 'none');

        // Calculate actual time taken
        const currentTime = Date.now();
        const elapsedMilliseconds = currentTime - startTimestamp;
        const timeTaken = Math.min(TOTAL_TIME, Math.ceil(elapsedMilliseconds / 1000));

        fetch('{{ route("quizzer.submit.answer") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                question_id: questionId,
                selected_answer: selectedAnswer || '',
                time_taken: timeTaken
            })
        })
        .then(response => response.json())
        .then(data => {
            // Play sound based on answer correctness
            if (data.correct) {
                playCorrectSound();
            } else {
                playIncorrectSound();
            }
            
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
                    if (correctOption) {
                        correctOption.style.borderColor = '#10b981';
                        correctOption.style.backgroundColor = '#dcfce7';
                    }
                }
            }
            
            // Redirect back to grid after showing feedback
            setTimeout(() => {
                window.location.href = '{{ route("quizzer.question.grid", [$question->quiz_id, $question->subject_id]) }}';
            }, 1500);
        });
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>