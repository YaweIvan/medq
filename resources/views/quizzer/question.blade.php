<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question - MedQ</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .sidebar-pre-collapsed #quizzerSidebar { width: 70px; }
        .sidebar-pre-collapsed .main-content { margin-left: 70px; }
        
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

        .option.correct {
            border-color: #10b981;
            background-color: #dcfce7;
        }

        .option.incorrect {
            border-color: #ef4444;
            background-color: #fee2e2;
        }

        .option.disabled {
            pointer-events: none;
            opacity: 0.7;
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

        .option.correct .option-label {
            background: #10b981;
            color: white;
        }

        .option.incorrect .option-label {
            background: #ef4444;
            color: white;
        }

        .option-text {
            flex: 1;
            color: #374151;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    @include('components.topnav')
    @include('components.quizzer_sidebar')

    <div class="question-timer" id="timerContainer" style="display: none;">
        <div style="font-size: 0.7rem; color: #6b7280; margin-bottom: 0.25rem;">Time Left</div>
        <div class="question-timer-display" id="questionTimer">--</div>
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
                    
                    @if($question->diagram)
                    <div class="question-diagram mt-3 mb-4">
                        <img src="{{ asset('storage/question_diagrams/'.$question->diagram) }}" alt="Question Diagram" class="img-fluid" style="max-width: 600px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    </div>
                    @endif
                    
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
                        <i class="fas fa-check me-2"></i>Submit Answer
                    </button>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const options    = document.querySelectorAll('.option');
    const submitBtn  = document.getElementById('submitAnswer');
    const questionId = {{ $question->id }};
    const gridUrl    = '{{ route("quizzer.question.grid", [$question->quiz_id, $question->subject_id]) }}';
    const submitUrl  = '{{ route("quizzer.submit.answer") }}';
    const csrfToken  = '{{ csrf_token() }}';

    // --- Server-driven timer (4.2) ---
    const expiresAt = new Date('{{ $attempt->expires_at->toISOString() }}').getTime();
    const serverNow = new Date('{{ $serverNow->toISOString() }}').getTime();
    const clockSkew = serverNow - Date.now();

    function remainingMs() {
        return expiresAt - (Date.now() + clockSkew);
    }

    let timerInterval;
    let hasSubmitted   = false;
    let selectedAnswer = null;

    document.getElementById('timerContainer').style.display = 'block';

    timerInterval = setInterval(() => {
        const ms          = remainingMs();
        const displaySecs = Math.max(0, Math.ceil(ms / 1000));
        const timerEl     = document.getElementById('questionTimer');
        const containerEl = document.getElementById('timerContainer');

        timerEl.textContent = displaySecs;

        if (displaySecs <= 10) {
            timerEl.classList.add('danger');
            containerEl.classList.add('danger');
        }

        if (ms <= 0) {
            clearInterval(timerInterval);
            if (!hasSubmitted) {
                hasSubmitted = true;
                lockUI();
                fireSubmit(selectedAnswer); // send whatever was selected (may be null)
            }
        }
    }, 100);

    // --- Option click: visual selection only, no server call (4.4) ---
    options.forEach(option => {
        option.addEventListener('click', function () {
            if (hasSubmitted) return;

            options.forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            selectedAnswer = this.dataset.answer;

            submitBtn.disabled = false;
        });
    });

    // --- Submit button click (4.4) ---
    submitBtn.addEventListener('click', function () {
        if (hasSubmitted || !selectedAnswer) return;
        hasSubmitted = true;
        clearInterval(timerInterval);
        lockUI();
        fireSubmit(selectedAnswer);
    });

    function lockUI() {
        options.forEach(o => o.classList.add('disabled'));
        submitBtn.disabled = true;
    }

    // --- AJAX submission ---
    function fireSubmit(answer) {
        fetch(submitUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                question_id:     questionId,
                selected_answer: answer  // null if nothing selected (auto-expire)
            })
        })
        .then(r => r.json())
        .then(data => {
            const correctAnswer = data.correct_answer;

            if (answer) {
                const selectedEl = document.querySelector(`[data-answer="${answer}"]`);
                if (selectedEl) {
                    selectedEl.classList.remove('selected');
                    selectedEl.classList.add(data.is_correct ? 'correct' : 'incorrect');
                }
            }

            if (!data.is_correct && correctAnswer) {
                const correctEl = document.querySelector(`[data-answer="${correctAnswer}"]`);
                if (correctEl) correctEl.classList.add('correct');
            }

            setTimeout(() => { window.location.href = data.redirect_url || gridUrl; }, 1000);
        })
        .catch(() => {
            setTimeout(() => { window.location.href = gridUrl; }, 1000);
        });
    }
});
</script>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>