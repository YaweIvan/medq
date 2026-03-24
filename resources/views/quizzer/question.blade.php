<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question - MedQ</title>
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
        <div class="question-timer-display" id="questionTimer">{{ $question->time_per_question ?? 60 }}</div>
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

// Timer Sound Functions
let timerAudio = null;
let isTimerSoundPlaying = false;
let warningPlayed = false; // Track if 10-second warning has been played

function playWarningSound() {
    if (warningPlayed) return; // Only play once
    warningPlayed = true;
    
    // Try to play uploaded warning sound file
    const warningAudio = new Audio();
    
    // Try different formats
    const formats = [
        { src: '{{ asset("sounds/warning.mp3") }}', type: 'audio/mpeg' },
        { src: '{{ asset("sounds/warning.wav") }}', type: 'audio/wav' },
        { src: '{{ asset("sounds/warning.ogg") }}', type: 'audio/ogg' }
    ];
    
    let soundLoaded = false;
    
    for (let format of formats) {
        if (warningAudio.canPlayType(format.type)) {
            warningAudio.src = format.src;
            warningAudio.volume = 0.5; // Set to 50% volume for alert
            
            warningAudio.play().then(() => {
                soundLoaded = true;
            }).catch(() => {
                // File doesn't exist, will use generated sound
                if (!soundLoaded) playGeneratedWarningSound();
            });
            break;
        }
    }
    
    // If no format supported or error, use generated sound
    if (!soundLoaded) {
        setTimeout(() => {
            if (warningPlayed && !soundLoaded) playGeneratedWarningSound();
        }, 100);
    }
}

function playGeneratedWarningSound() {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    
    // Play three quick beeps as warning
    for (let i = 0; i < 3; i++) {
        setTimeout(() => {
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.setValueAtTime(1200, audioContext.currentTime); // High pitch for urgency
            oscillator.type = 'sine';
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.15);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.15);
        }, i * 200); // 200ms between beeps
    }
}

function playTimerSound() {
    if (isTimerSoundPlaying) return; // Already playing
    
    // Try to play uploaded timer sound file
    timerAudio = new Audio();
    
    // Try different formats
    const formats = [
        { src: '{{ asset("sounds/timer.mp3") }}', type: 'audio/mpeg' },
        { src: '{{ asset("sounds/timer.wav") }}', type: 'audio/wav' },
        { src: '{{ asset("sounds/timer.ogg") }}', type: 'audio/ogg' }
    ];
    
    let soundLoaded = false;
    
    for (let format of formats) {
        if (timerAudio.canPlayType(format.type)) {
            timerAudio.src = format.src;
            timerAudio.loop = true; // Loop the timer sound
            timerAudio.volume = 0.3; // Set to 30% volume for subtlety
            
            timerAudio.play().then(() => {
                soundLoaded = true;
                isTimerSoundPlaying = true;
            }).catch(() => {
                // File doesn't exist, will use generated sound
                if (!soundLoaded) playGeneratedTimerSound();
            });
            break;
        }
    }
    
    // If no format supported or error, use generated sound
    if (!soundLoaded) {
        setTimeout(() => {
            if (!isTimerSoundPlaying) playGeneratedTimerSound();
        }, 100);
    }
}

function playGeneratedTimerSound() {
    if (isTimerSoundPlaying) return;
    
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    let tickInterval;
    
    function playTick() {
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        // Alternating tick-tock frequencies
        const now = Date.now();
        const isTick = Math.floor(now / 1000) % 2 === 0;
        oscillator.frequency.setValueAtTime(isTick ? 800 : 600, audioContext.currentTime);
        
        oscillator.type = 'sine';
        gainNode.gain.setValueAtTime(0.1, audioContext.currentTime); // Very subtle
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.1);
    }
    
    // Play tick every second
    isTimerSoundPlaying = true;
    playTick(); // Play immediately
    tickInterval = setInterval(playTick, 1000);
    
    // Store interval so we can stop it later
    window.timerTickInterval = tickInterval;
}

function stopTimerSound() {
    isTimerSoundPlaying = false;
    
    // Stop uploaded audio if playing
    if (timerAudio) {
        timerAudio.pause();
        timerAudio.currentTime = 0;
        timerAudio = null;
    }
    
    // Stop generated tick interval
    if (window.timerTickInterval) {
        clearInterval(window.timerTickInterval);
        window.timerTickInterval = null;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const options = document.querySelectorAll('.option');
    const submitBtn = document.getElementById('submitAnswer');
    const questionId = {{ $question->id }};
    const timerStartKey = `quiz_timer_start_${questionId}`;
    const TOTAL_TIME = {{ $question->time_per_question ?? 60 }}; // Dynamic time from question
    
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
        
        // If resuming with less than 10 seconds, mark warning as already played
        if (timeRemaining < 10) {
            warningPlayed = true;
        }
        
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
        // Start playing timer sound
        playTimerSound();
        
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
        
        // Play warning sound when hitting exactly 10 seconds
        if (displayTime === 10 && !warningPlayed) {
            playWarningSound();
        }
        
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
        
        // Stop timer sound
        stopTimerSound();
        
        if (hasSubmitted) return;
        
        hasSubmitted = true;
        clearTimerStorage();
        
        // Disable all options
        options.forEach(opt => opt.style.pointerEvents = 'none');
        submitBtn.style.pointerEvents = 'none';
        submitBtn.disabled = true;
        
        // Submit with selected answer (if any) - time expired
        fetch('{{ route("quizzer.submit.answer") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                question_id: questionId,
                selected_answer: selectedAnswer || '', // Submit selected answer or empty if none selected
                time_taken: TOTAL_TIME
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
                    
                    // Show correct answer
                    const correctOption = document.querySelector(`[data-answer="${data.correct_answer}"]`);
                    if (correctOption) {
                        correctOption.style.borderColor = '#10b981';
                        correctOption.style.backgroundColor = '#dcfce7';
                    }
                }
            } else {
                // No answer selected - show correct answer only
                const correctOption = document.querySelector(`[data-answer="${data.correct_answer}"]`);
                if (correctOption) {
                    correctOption.style.borderColor = '#10b981';
                    correctOption.style.backgroundColor = '#dcfce7';
                }
            }
            
            // Redirect back to grid after showing feedback
            setTimeout(() => {
                window.location.href = '{{ route("quizzer.question.grid", [$question->quiz_id, $question->subject_id]) }}';
            }, 1500);

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
        
        // Stop timer sound
        stopTimerSound();
        
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

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>