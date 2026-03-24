<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Review - MedQ</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .correct-row {
            background-color: #d1fae5 !important;
        }
        .incorrect-row {
            background-color: #fee2e2 !important;
        }
        .correct-row td {
            background-color: #d1fae5 !important;
        }
        .incorrect-row td {
            background-color: #fee2e2 !important;
        }
        .question-row {
            cursor: pointer;
            transition: opacity 0.15s;
        }
        .question-row:hover td {
            filter: brightness(0.94);
        }
        .question-row.selected-row td {
            outline: 2px solid #6366f1 !important;
            outline-offset: -2px;
        }
        .option-card {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 10px 16px;
            margin-bottom: 8px;
            background: #fff;
            transition: border-color 0.15s;
        }
        .option-card.option-correct {
            border-color: #22c55e;
            background-color: #f0fdf4;
        }
        .option-card.option-selected-wrong {
            border-color: #ef4444;
            background-color: #fef2f2;
        }
        .option-card.option-selected-correct {
            border-color: #22c55e;
            background-color: #dcfce7;
        }
        .option-label {
            font-weight: 700;
            margin-right: 8px;
            font-size: 1rem;
        }
        #question-detail {
            display: none;
        }
    </style>
</head>
<body>
    @include('components.topnav')
    @include('components.sidebar')

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">{{ $user->name }} - {{ $quiz->title }}</h2>
                    <p class="text-muted mb-0">{{ $subject->name }} - Review Answers</p>
                </div>
                <a href="{{ route('admin.subject.analysis', [$quiz->id, $subject->id]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Rankings
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Question Number</th>
                                    <th class="text-center">Answer Selected</th>
                                    <th class="text-center">Correct Answer</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attempts as $attempt)
                                    <tr class="question-row {{ $attempt->is_correct ? 'correct-row' : 'incorrect-row' }}"
                                        data-qnum="{{ $attempt->question_number }}"
                                        data-question="{{ $attempt->question->question }}"
                                        data-option-a="{{ $attempt->question->option_a }}"
                                        data-option-b="{{ $attempt->question->option_b }}"
                                        data-option-c="{{ $attempt->question->option_c }}"
                                        data-option-d="{{ $attempt->question->option_d }}"
                                        data-option-e="{{ $attempt->question->option_e }}"
                                        data-correct="{{ $attempt->question->correct_answer }}"
                                        data-selected="{{ $attempt->selected_answer ?: '' }}">
                                        <td>Question #{{ $attempt->question_number }} <small class="text-muted ms-1"><i class="fas fa-chevron-down" style="font-size:0.75rem"></i></small></td>
                                        <td class="text-center">
                                            <strong>{{ $attempt->selected_answer ?: 'No Answer' }}</strong>
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

            {{-- Question Detail Panel --}}
            <div id="question-detail" class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold" id="detail-qnum">Question</span>
                    <button type="button" class="btn-close" onclick="document.getElementById('question-detail').style.display='none'; document.querySelectorAll('.question-row').forEach(r=>r.classList.remove('selected-row'));"></button>
                </div>
                <div class="card-body">
                    <p class="fw-semibold fs-5 mb-4" id="detail-question"></p>
                    <div id="detail-options"></div>
                </div>
                <div class="card-footer text-muted small" id="detail-footer"></div>
            </div>

        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        const detail = document.getElementById('question-detail');
        const detailNum = document.getElementById('detail-qnum');
        const detailText = document.getElementById('detail-question');
        const detailOptions = document.getElementById('detail-options');
        const detailFooter = document.getElementById('detail-footer');

        const labels = ['A', 'B', 'C', 'D', 'E'];

        document.querySelectorAll('.question-row').forEach(row => {
            row.addEventListener('click', function () {
                // Toggle off if same row clicked again
                if (this.classList.contains('selected-row')) {
                    this.classList.remove('selected-row');
                    detail.style.display = 'none';
                    return;
                }
                document.querySelectorAll('.question-row').forEach(r => r.classList.remove('selected-row'));
                this.classList.add('selected-row');

                const qnum    = this.dataset.qnum;
                const question = this.dataset.question;
                const correct  = this.dataset.correct.toUpperCase();
                const selected = this.dataset.selected.toUpperCase();
                const opts = {
                    A: this.dataset.optionA,
                    B: this.dataset.optionB,
                    C: this.dataset.optionC,
                    D: this.dataset.optionD,
                    E: this.dataset.optionE,
                };

                detailNum.textContent = 'Question #' + qnum;
                detailText.innerHTML = question;

                let html = '';
                labels.forEach(lbl => {
                    const text = opts[lbl];
                    if (!text) return;
                    let cls = 'option-card';
                    let badge = '';
                    if (lbl === correct && lbl === selected) {
                        cls += ' option-selected-correct';
                        badge = '<span class="badge bg-success ms-2">Your Answer ✓</span>';
                    } else if (lbl === correct) {
                        cls += ' option-correct';
                        badge = '<span class="badge bg-success ms-2">Correct Answer</span>';
                    } else if (lbl === selected) {
                        cls += ' option-selected-wrong';
                        badge = '<span class="badge bg-danger ms-2">Your Answer ✗</span>';
                    }
                    html += `<div class="${cls}"><span class="option-label">${lbl}.</span>${text}${badge}</div>`;
                });

                if (!selected) {
                    html += `<div class="alert alert-warning mt-2 mb-0 py-2"><i class="fas fa-exclamation-triangle me-2"></i>No answer was selected for this question.</div>`;
                }

                detailOptions.innerHTML = html;

                if (correct && opts[correct]) {
                    detailFooter.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i>Correct Answer: <strong>${correct}. ${opts[correct]}</strong>`;
                } else {
                    detailFooter.innerHTML = '';
                }

                detail.style.display = 'block';
                detail.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        });
    </script>
</body>
</html>
