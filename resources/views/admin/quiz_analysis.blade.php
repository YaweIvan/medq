<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Analysis - MedQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    @include('components.topnav')
    @include('components.sidebar')

    <div class="main-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">{{ $quiz->title }} - Analysis</h2>
                    <p class="text-muted mb-0">Select a subject to view rankings</p>
                </div>
                <a href="{{ route('admin.statistics') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Statistics
                </a>
            </div>

            <div class="row g-3">
                @foreach($subjects as $subject)
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm" style="cursor: pointer;" onclick="window.location.href='{{ route('admin.subject.analysis', [$quiz->id, $subject->id]) }}'">
                            <div class="card-body text-center p-4">
                                <i class="fas fa-book text-primary mb-3" style="font-size: 2rem;"></i>
                                <h5 class="card-title">{{ $subject->name }}</h5>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
