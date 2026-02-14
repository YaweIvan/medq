<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Analysis - MedQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    @include('components.topnav')
    @include('components.sidebar')

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">{{ $quiz->title }} - {{ $subject->name }}</h2>
                    <p class="text-muted mb-0">Student Rankings</p>
                </div>
                <a href="{{ route('admin.quiz.analysis', $quiz->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Subjects
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Position</th>
                                    <th>Name of Student</th>
                                    <th class="text-center">Questions Passed/Total Attempted</th>
                                    <th class="text-center">Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rankings as $index => $ranking)
                                    <tr style="cursor: pointer;" onclick="window.location.href='{{ route('admin.student.subject.review', [$quiz->id, $subject->id, $ranking->user_id]) }}'">
                                        <td><span class="badge bg-primary">{{ $index + 1 }}</span></td>
                                        <td><strong>{{ $ranking->user->name }}</strong></td>
                                        <td class="text-center">{{ $ranking->correct }}/{{ $ranking->total }}</td>
                                        <td class="text-center"><strong>{{ $ranking->percentage }}%</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
