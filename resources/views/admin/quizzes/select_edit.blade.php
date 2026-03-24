<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Quiz to Edit - MedQ</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .quiz-card {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
            background: white;
        }
        
        .quiz-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
        }
        
        .quiz-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        
        .quiz-card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }
        
        .quiz-card-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-active {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .quiz-card-info {
            display: flex;
            gap: 2rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .quiz-info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .quiz-info-item i {
            color: #3b82f6;
        }
        
        .quiz-card-description {
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        
        .quiz-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .user-avatars {
            display: flex;
            gap: 0.25rem;
        }
        
        .user-avatars .badge {
            font-size: 0.75rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }
        
        .empty-state h3 {
            color: #6b7280;
            margin-bottom: 0.5rem;
        }
        
        .empty-state p {
            color: #9ca3af;
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
                    <h2 class="fw-bold text-dark mb-1">Select Quiz to Edit</h2>
                    <p class="text-muted mb-0">Click on any quiz card to edit its details and manage users</p>
                </div>
                <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>Create New Quiz
                </a>
            </div>

            @if($quizzes->count() > 0)
                <div class="row">
                    @foreach($quizzes as $quiz)
                        <div class="col-md-6 col-lg-4">
                            <div class="quiz-card" onclick="window.location.href='{{ route('admin.quizzes.edit', $quiz->id) }}'">
                                <div class="quiz-card-header">
                                    <h3 class="quiz-card-title">{{ $quiz->title }}</h3>
                                    <span class="quiz-card-status {{ $quiz->is_active ? 'status-active' : 'status-inactive' }}">
                                        {{ $quiz->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                
                                <div class="quiz-card-info">
                                    <div class="quiz-info-item">
                                        <i class="fas fa-question-circle"></i>
                                        <span>{{ $quiz->questions_count }} Questions</span>
                                    </div>
                                    <div class="quiz-info-item">
                                        <i class="fas fa-users"></i>
                                        <span>{{ $quiz->users_count }} Users</span>
                                    </div>
                                    <div class="quiz-info-item">
                                        <i class="fas fa-book"></i>
                                        <span>{{ $quiz->subjects_count }} Subjects</span>
                                    </div>
                                </div>
                                
                                @if($quiz->description)
                                    <p class="quiz-card-description">{{ Str::limit($quiz->description, 100) }}</p>
                                @endif
                                
                                <div class="quiz-card-footer">
                                    <div>
                                        @if($quiz->users_count > 0)
                                            <small class="text-muted">Assigned to {{ $quiz->users_count }} {{ Str::plural('user', $quiz->users_count) }}</small>
                                        @else
                                            <small class="text-warning">No users assigned</small>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); window.location.href='{{ route('admin.quizzes.edit', $quiz->id) }}'">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); resetQuiz({{ $quiz->id }}, '{{ $quiz->title }}')">
                                            <i class="fas fa-redo me-1"></i>Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No Quizzes Yet</h3>
                    <p>Create your first quiz to get started</p>
                    <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus-circle me-2"></i>Create Quiz
                    </a>
                </div>
            @endif
        </div>
    </div>

    <script>
    async function resetQuiz(quizId, quizTitle) {
        if (!confirm(`Are you sure you want to reset "${quizTitle}"?\n\nThis will:\n• Mark all questions as unused (is_used = false)\n• Delete all quiz attempts\n• Allow users to retake all questions\n\nThis action cannot be undone!`)) {
            return;
        }
        
        try {
            const response = await fetch(`/admin/quizzes/${quizId}/reset`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                alert(`Quiz reset successfully!\n\n• ${data.questions_reset} questions marked as unused\n• ${data.attempts_deleted} attempts deleted`);
                window.location.reload();
            } else {
                const error = await response.json();
                alert('Error resetting quiz: ' + (error.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error resetting quiz. Please try again.');
        }
    }
    </script>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
