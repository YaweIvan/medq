<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Management - MedQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    @include('components.topnav')
    @include('components.sidebar')

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Quiz Management</h2>
                    <p class="text-muted mb-0">Create and manage medical quizzes</p>
                </div>
                <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create New Quiz
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            <h5 class="mb-3"><i class="fas fa-list me-2"></i>All Quizzes</h5>
            
            @if($quizzes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Quiz Title</th>
                                <th>Description</th>
                                <th>Assigned Users</th>
                                <th>Questions</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quizzes as $quiz)
                                <tr>
                                    <td>
                                        <strong>{{ $quiz->title }}</strong>
                                    </td>
                                    <td>{{ Str::limit($quiz->description, 50) }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $quiz->users->count() }} users</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $quiz->questions->count() }} questions</span>
                                    </td>
                                    <td>
                                        @if($quiz->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.quizzes.edit', $quiz->id) }}" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-outline-danger" title="Delete" onclick="deleteQuiz({{ $quiz->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-outline-{{ $quiz->is_active ? 'warning' : 'success' }}" title="{{ $quiz->is_active ? 'Deactivate' : 'Activate' }}" onclick="toggleStatus({{ $quiz->id }})">
                                                <i class="fas fa-{{ $quiz->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list text-muted" style="font-size: 4rem;"></i>
                    <h4 class="text-muted mt-3">No Quizzes Found</h4>
                    <p class="text-muted">Create your first quiz to get started</p>
                    <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Quiz
                    </a>
                </div>
            @endif
        </div>
    </div>

    <script>
        function deleteQuiz(id) {
            if(confirm('Are you sure you want to delete this quiz?')) {
                fetch(`/admin/quizzes/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }).then(() => location.reload());
            }
        }
        
        function toggleStatus(id) {
            fetch(`/admin/quizzes/${id}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(() => location.reload());
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>