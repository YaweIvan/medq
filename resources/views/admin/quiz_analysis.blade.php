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

            <!-- Overall Rankings Table -->
            @if(count($rankings) > 0)
            <div class="mt-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="fw-bold text-dark mb-0">Overall Quiz Rankings</h3>
                    <a href="{{ route('admin.quiz.rankings.export', $quiz->id) }}" class="btn btn-success">
                        <i class="fas fa-file-excel me-2"></i>Export to Excel
                    </a>
                </div>
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="px-4 py-3" style="width: 80px;">Position</th>
                                        <th class="px-4 py-3">Student Name</th>
                                        @foreach($subjects as $subject)
                                            <th class="px-4 py-3 text-center">{{ $subject->name }}</th>
                                        @endforeach
                                        <th class="px-4 py-3 text-center">Total</th>
                                        <th class="px-4 py-3 text-center">Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rankings as $ranking)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <span class="badge bg-primary fw-bold">
                                                    {{ $ranking['position'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 fw-semibold">{{ $ranking['user_name'] }}</td>
                                            @foreach($subjects as $subject)
                                                <td class="px-4 py-3 text-center">
                                                    @if(isset($ranking['subjects'][$subject->id]))
                                                        @php $sd = $ranking['subjects'][$subject->id]; @endphp
                                                        <span class="badge bg-info text-dark" title="{{ $sd['correct'] }} correct × {{ $sd['marks'] }} mark(s)">
                                                            {{ $sd['points'] }}/{{ $sd['max_points'] }} pts
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="px-4 py-3 text-center">
                                                <span class="badge bg-primary fw-bold">
                                                    {{ $ranking['total_points'] }}/{{ $ranking['total_max_points'] }} pts
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="badge fw-bold" style="background-color: #ffcccc; color: #000;">
                                                    {{ $ranking['percentage'] }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
