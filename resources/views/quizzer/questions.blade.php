<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Questions - MedQ</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="fw-bold text-dark mb-1">{{ $quiz->title }}</h2>
                            <p class="text-muted mb-0">
                                <i class="fas fa-book me-2"></i>{{ $subject->name }} Questions
                            </p>
                        </div>
                        <a href="{{ route('quizzer.quiz.subjects', $quiz->id) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Subjects
                        </a>
                    </div>

                    @foreach($questions as $index => $question)
                        <div class="question-card mb-4" id="question-{{ $question->id }}">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="fw-bold text-primary mb-0">Question {{ $index + 1 }}</h5>
                                <span class="badge bg-light text-dark">{{ $index + 1 }}/{{ $questions->count() }}</span>
                            </div>
                            
                            <div class="question-text mb-4">
                                <p class="fs-5 text-dark mb-0">{!! $question->question !!}</p>
                            </div>
                            
                            <div class="options-grid">
                                <button class="option-btn" onclick="submitAnswer({{ $question->id }}, 'A')">
                                    <div class="d-flex align-items-center">
                                        <span class="option-letter me-3">A</span>
                                        <span>{!! $question->option_a !!}</span>
                                    </div>
                                </button>
                                <button class="option-btn" onclick="submitAnswer({{ $question->id }}, 'B')">
                                    <div class="d-flex align-items-center">
                                        <span class="option-letter me-3">B</span>
                                        <span>{!! $question->option_b !!}</span>
                                    </div>
                                </button>
                                <button class="option-btn" onclick="submitAnswer({{ $question->id }}, 'C')">
                                    <div class="d-flex align-items-center">
                                        <span class="option-letter me-3">C</span>
                                        <span>{!! $question->option_c !!}</span>
                                    </div>
                                </button>
                                <button class="option-btn" onclick="submitAnswer({{ $question->id }}, 'D')">
                                    <div class="d-flex align-items-center">
                                        <span class="option-letter me-3">D</span>
                                        <span>{!! $question->option_d !!}</span>
                                    </div>
                                </button>
                                @if($question->option_e)
                                <button class="option-btn" onclick="submitAnswer({{ $question->id }}, 'E')">
                                    <div class="d-flex align-items-center">
                                        <span class="option-letter me-3">E</span>
                                        <span>{!! $question->option_e !!}</span>
                                    </div>
                                </button>
                                @endif
                            </div>
                            
                            <div class="feedback mt-4" id="feedback-{{ $question->id }}" style="display: none;"></div>
                        </div>
                    @endforeach
                    
                    @if($questions->count() == 0)
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-3">All Questions Completed!</h4>
                            <p class="text-muted mb-4">You have completed the maximum number of questions for this subject.</p>
                            <a href="{{ route('quizzer.quiz.subjects', $quiz->id) }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Subjects
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .options-grid {
            display: grid;
            gap: 1rem;
        }
        
        .option-letter {
            width: 32px;
            height: 32px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .option-btn.correct .option-letter {
            background: var(--success);
        }
        
        .option-btn.incorrect .option-letter {
            background: var(--danger);
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
        
        // Keep session alive during quiz
        setInterval(() => {
            fetch('{{ route("quizzer.api.check-updates") }}').catch(() => {});
        }, 60000); // Every 60 seconds
        
        function submitAnswer(questionId, selectedAnswer) {
            const questionCard = document.getElementById('question-' + questionId);
            const options = questionCard.querySelectorAll('.option-btn');
            const feedback = document.getElementById('feedback-' + questionId);
            
            options.forEach(btn => {
                btn.disabled = true;
                btn.style.opacity = '0.7';
            });
            
            fetch('{{ route("quizzer.submit.answer") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    question_id: questionId,
                    selected_answer: selectedAnswer
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
                
                options.forEach(btn => {
                    const btnText = btn.textContent.trim();
                    if (btnText.startsWith(selectedAnswer)) {
                        btn.classList.add(data.correct ? 'correct' : 'incorrect');
                    }
                    if (btnText.startsWith(data.correct_answer)) {
                        btn.classList.add('correct');
                    }
                });
                
                feedback.innerHTML = data.correct 
                    ? '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Correct! Well done.</div>'
                    : '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Incorrect. The correct answer is ' + data.correct_answer + '.</div>';
                feedback.style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                options.forEach(btn => {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                });
            });
        }
    </script>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>