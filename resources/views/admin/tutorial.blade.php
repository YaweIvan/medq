<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutorial - MedQ</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    @include('components.topnav')
    @include('components.sidebar')

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Tutorial Mode</h2>
                    <p class="text-muted mb-0">Demo quiz for training quizzers</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-book me-2"></i>Sample Quiz Demo</h5>
                </div>
                <div class="card-body">
                    <div class="question-card mb-4">
                        <h5 class="fw-bold text-primary mb-3">Sample Question 1</h5>
                        <p class="fs-5 text-dark mb-4">Which bone is the longest in the human body?</p>
                        
                        <div class="options-grid">
                            <button class="option-btn" onclick="showDemo('A')">
                                <div class="d-flex align-items-center">
                                    <span class="option-letter me-3">A</span>
                                    <span>Tibia</span>
                                </div>
                            </button>
                            <button class="option-btn" onclick="showDemo('B')">
                                <div class="d-flex align-items-center">
                                    <span class="option-letter me-3">B</span>
                                    <span>Femur</span>
                                </div>
                            </button>
                            <button class="option-btn" onclick="showDemo('C')">
                                <div class="d-flex align-items-center">
                                    <span class="option-letter me-3">C</span>
                                    <span>Humerus</span>
                                </div>
                            </button>
                            <button class="option-btn" onclick="showDemo('D')">
                                <div class="d-flex align-items-center">
                                    <span class="option-letter me-3">D</span>
                                    <span>Radius</span>
                                </div>
                            </button>
                        </div>
                        
                        <div class="feedback mt-4" id="demo-feedback" style="display: none;"></div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Tutorial Instructions:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Click any option to see how feedback works</li>
                            <li>Green indicates correct answer</li>
                            <li>Red indicates incorrect answer</li>
                            <li>Questions are locked after being attempted</li>
                            <li>Maximum 5 questions per subject</li>
                        </ul>
                    </div>
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
        function showDemo(selectedAnswer) {
            const options = document.querySelectorAll('.option-btn');
            const feedback = document.getElementById('demo-feedback');
            
            options.forEach(btn => {
                btn.disabled = true;
                btn.style.opacity = '0.7';
                
                const btnText = btn.textContent.trim();
                if (btnText.startsWith(selectedAnswer)) {
                    btn.classList.add(selectedAnswer === 'B' ? 'correct' : 'incorrect');
                }
                if (btnText.startsWith('B')) {
                    btn.classList.add('correct');
                }
            });
            
            feedback.innerHTML = selectedAnswer === 'B' 
                ? '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Correct! The femur is indeed the longest bone in the human body.</div>'
                : '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Incorrect. The correct answer is B - Femur.</div>';
            feedback.style.display = 'block';
            
            setTimeout(() => {
                options.forEach(btn => {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.classList.remove('correct', 'incorrect');
                });
                feedback.style.display = 'none';
            }, 3000);
        }
    </script>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>