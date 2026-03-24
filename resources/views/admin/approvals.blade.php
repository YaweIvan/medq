<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MedQ</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

</head>
<body>
    <!-- Top Navigation -->
    
    @include('components.topnav')

    <!-- Sidebar -->
    @include('components.sidebar')


    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-user-check"></i> User Approvals</h4>
                        </div>
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            
                            @if($users->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="font-size: 0.875rem;">Name</th>
                                                <th style="font-size: 0.875rem;">Email</th>
                                                <th style="font-size: 0.875rem;">Date</th>
                                                <th style="font-size: 0.875rem;">Status</th>
                                                <th style="font-size: 0.875rem;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($users as $user)
                                                <tr>
                                                    <td style="font-size: 0.875rem;">
                                                        <strong>{{ $user->name }}</strong>
                                                    </td>
                                                    <td style="font-size: 0.875rem;">{{ $user->email }}</td>
                                                    <td style="font-size: 0.875rem;">{{ $user->created_at->format('M d, Y') }}</td>
                                                    <td>
                                                        @if($user->is_approved)
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-check-circle"></i> Approved
                                                            </span>
                                                        @else
                                                            <span class="badge bg-warning text-dark">
                                                                <i class="fas fa-clock"></i> Pending/Rejected
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            @if($user->is_approved)
                                                                <!-- Cancel Approval Button -->
                                                                <form method="POST" action="{{ route('admin.cancel-approval', $user->id) }}" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-warning btn-sm" 
                                                                            onclick="return confirm('Cancel approval for {{ $user->name }}?')"
                                                                            title="Cancel Approval">
                                                                        <i class="fas fa-undo"></i> Cancel
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <!-- Approve Button -->
                                                                <form method="POST" action="{{ route('admin.approve', $user->id) }}" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-success btn-sm" title="Approve User">
                                                                        <i class="fas fa-check"></i> Approve
                                                                    </button>
                                                                </form>
                                                                <!-- Reject Button -->
                                                                <form method="POST" action="{{ route('admin.reject', $user->id) }}" class="d-inline ms-1">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-secondary btn-sm" 
                                                                            onclick="return confirm('Reject {{ $user->name }}?')"
                                                                            title="Reject User">
                                                                        <i class="fas fa-times"></i> Reject
                                                                    </button>
                                                                </form>
                                                            @endif
                                                            <!-- Delete Button (always available) -->
                                                            <form method="POST" action="{{ route('admin.delete-user', $user->id) }}" class="d-inline ms-1">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                                        onclick="return confirm('Permanently delete {{ $user->name }}? This action cannot be undone!')"
                                                                        title="Delete User">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No users found.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>