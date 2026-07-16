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
        if (window.innerWidth > 768 && localStorage.getItem('quizzerSidebarCollapsed') === 'true') {
            document.documentElement.classList.add('sidebar-pre-collapsed');
        }
    </script>
    <style>
        .sidebar-pre-collapsed #quizzerSidebar { width: 70px; }
        .sidebar-pre-collapsed .main-content  { margin-left: 70px; }

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
            font-size: 1rem;
        }

        .question-number:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }

        .question-number.open {
            border-color: #3b82f6;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .question-number.locked {
            background: #6b7280;
            color: white;
            border-color: #6b7280;
            cursor: not-allowed;
        }
        .question-number.locked:hover {
            background: #6b7280;
            border-color: #6b7280;
        }

        .question-number.blocked {
            opacity: 0.35;
            cursor: not-allowed;
            pointer-events: none;
        }
        .question-number.blocked:hover {
            border-color: #e2e8f0;
            background: white;
        }

        @media (max-width: 768px) {
            .question-grid { grid-template-columns: repeat(5, 1fr); }
        }
    </style>
</head>
<body>
    @include('components.topnav')
    @include('components.quizzer_sidebar')

    {{-- ============================================================
         8.1 — Entry popup (first visit: no attempt rows exist yet)
         Condition checked server-side via DB, not a cookie/flag.
    ============================================================ --}}
    @php
        $isFirstVisit = $userAttempts->isEmpty();

        // Time-per-question display logic
        $times       = $questions->pluck('time_per_question')->filter()->unique();
        $timeDisplay = match(true) {
            $times->count() === 0 => '60 seconds per question',
            $times->count() === 1 => $times->first() . ' seconds per question',
            default               => $times->min() . '–' . $times->max() . ' seconds, varies per question',
        };
    @endphp

    @if($isFirstVisit)
    <div class="modal fade" id="entryModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="entryModalLabel" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="entryModalLabel">
                        <i class="fas fa-info-circle text-primary me-2"></i>Before You Start
                    </h5>
                </div>
                <div class="modal-body pt-2">
                    <p class="mb-2">You will answer <strong>{{ $subject->max_questions ?? 5 }} question(s)</strong> from <strong>{{ $subject->name }}</strong>.</p>
                    <p class="mb-0 text-muted"><i class="fas fa-clock me-1"></i>{{ $timeDisplay }}</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">Got it</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ============================================================
         8.2 — Completion popup
         Condition: attemptedCount >= max_questions on this page load.
         Only shown once per session via sessionStorage flag.
    ============================================================ --}}
    @if($attemptedCount >= ($subject->max_questions ?? 5))
    <div class="modal fade" id="completionModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="completionModalLabel" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="completionModalLabel">
                        <i class="fas fa-check-circle text-success me-2"></i>Subject Complete!
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="mb-0">You've completed all your questions for <strong>{{ $subject->name }}</strong>. Well done!</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="main-content">
        <div class="container-fluid">

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">{{ $quiz->title }}</h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-book me-2"></i>{{ $subject->name }} —
                        <span id="attemptedCount">{{ $attemptedCount }}</span>/{{ $subject->max_questions ?? 5 }} attempted
                    </p>
                </div>
                <a href="{{ route('quizzer.quiz.subjects', $quiz->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Subjects
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="question-grid" id="questionGrid">
                        @foreach($questions as $index => $question)
                            @php
                                $attempt   = $userAttempts->get($question->id);
                                $isLocked  = isset($globalLockedIds) && $globalLockedIds->contains($question->id)
                                    ? true
                                    : ($attempt && $attempt->locked);
                                $isOpen    = $openAttempt && $openAttempt->question_id === $question->id;
                                $isBlocked = $openAttempt && !$isOpen && !$isLocked;
                                $canClick  = !$isLocked && !$isBlocked && $attemptedCount < ($subject->max_questions ?? 5);
                            @endphp

                            <div class="question-number
                                    {{ $isLocked  ? 'locked'  : '' }}
                                    {{ $isOpen    ? 'open'    : '' }}
                                    {{ $isBlocked ? 'blocked' : '' }}"
                                 data-question-id="{{ $question->id }}"
                                 @if($canClick || $isOpen)
                                     onclick="attemptQuestion({{ $question->id }})"
                                 @endif>
                                {{ $index + 1 }}
                            </div>
                        @endforeach
                    </div>

                    <div class="row mt-4 g-2">
                        <div class="col-auto d-flex align-items-center gap-2">
                            <div class="question-number" style="width:28px;height:28px;font-size:.75rem;"></div>
                            <span class="small">Available</span>
                        </div>
                        <div class="col-auto d-flex align-items-center gap-2">
                            <div class="question-number open" style="width:28px;height:28px;font-size:.75rem;"></div>
                            <span class="small">In Progress</span>
                        </div>
                        <div class="col-auto d-flex align-items-center gap-2">
                            <div class="question-number locked" style="width:28px;height:28px;font-size:.75rem;"></div>
                            <span class="small">Locked</span>
                        </div>
                        <div class="col-auto d-flex align-items-center gap-2">
                            <div class="question-number blocked" style="width:28px;height:28px;font-size:.75rem;opacity:.35;"></div>
                            <span class="small">Finish current question first</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    const MAX_QUESTIONS  = {{ $subject->max_questions ?? 5 }};
    const STATUS_URL     = '{{ route('quizzer.grid.status', [$quiz->id, $subject->id]) }}';
    const SUBJECT_KEY    = 'grid_completed_{{ $quiz->id }}_{{ $subject->id }}';

    function attemptQuestion(questionId) {
        window.location.href = '/quizzer/question/' + questionId;
    }

    // --- 8.1 Entry popup: show on first visit (no attempts exist) ---
    @if($isFirstVisit)
    document.addEventListener('DOMContentLoaded', function () {
        var entryModal = new bootstrap.Modal(document.getElementById('entryModal'));
        entryModal.show();
    });
    @endif

    // --- 8.2 Completion popup: show once per session after completing ---
    @if($attemptedCount >= ($subject->max_questions ?? 5))
    document.addEventListener('DOMContentLoaded', function () {
        var flagKey = SUBJECT_KEY;
        if (!sessionStorage.getItem(flagKey)) {
            sessionStorage.setItem(flagKey, '1');
            var completionModal = new bootstrap.Modal(document.getElementById('completionModal'));
            completionModal.show();
        }
    });
    @endif

    // --- Lightweight status polling ---
    function applyStatus(data) {
        let openQuestionId = data.open_question_id;

        data.status.forEach(item => {
            const tile = document.querySelector(`[data-question-id="${item.question_id}"]`);
            if (!tile) return;

            tile.classList.remove('locked', 'open', 'blocked');
            tile.removeAttribute('onclick');

            if (item.locked) {
                tile.classList.add('locked');
            } else if (item.open) {
                tile.classList.add('open');
                tile.setAttribute('onclick', `attemptQuestion(${item.question_id})`);
            } else if (openQuestionId) {
                tile.classList.add('blocked');
            } else {
                const lockedCount = data.status.filter(i => i.locked).length;
                if (lockedCount < MAX_QUESTIONS) {
                    tile.setAttribute('onclick', `attemptQuestion(${item.question_id})`);
                }
            }
        });

        const totalLocked = data.status.filter(i => i.locked).length;
        const el = document.getElementById('attemptedCount');
        if (el) el.textContent = totalLocked;
    }

    setInterval(() => {
        fetch(STATUS_URL)
            .then(r => r.json())
            .then(applyStatus)
            .catch(() => {});
    }, 7000);
</script>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
