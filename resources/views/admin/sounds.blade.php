<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Sound Settings - MedQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <!-- Top Navigation -->
    @include('components.topnav')

    <!-- Sidebar -->
    @include('components.sidebar')

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="page-title mb-1">
                        <i class="fas fa-volume-up me-2"></i>Quiz Sound Settings
                    </h1>
                    <p class="page-subtitle mb-0">Manage audio feedback for quiz answers</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            Upload custom audio files for quiz answer feedback. Supported formats: MP3, WAV, OGG (Max size: 2MB each)
                        </div>

                        @if(session('audio_success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('audio_success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('audio_error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('audio_error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="row">
                            <!-- Correct Answer Sound -->
                            <div class="col-md-6 mb-4">
                                <div class="card border-success h-100">
                                    <div class="card-header bg-success bg-opacity-10 text-success">
                                        <h5 class="mb-0 fw-bold">
                                            <i class="fas fa-check-circle me-2"></i>Correct Answer Sound
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $correctSound = $quizSounds->get('correct');
                                        @endphp
                                        
                                        @if($correctSound)
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Current Audio:</label>
                                                <div class="mb-2">
                                                    <audio controls class="w-100" id="correctAudioPreview">
                                                        <source src="{{ asset($correctSound->file_path) }}" type="{{ $correctSound->mime_type }}">
                                                    </audio>
                                                </div>
                                                <div class="small text-muted">
                                                    <i class="fas fa-file me-1"></i>{{ $correctSound->file_name }} 
                                                    ({{ $correctSound->file_size_human }})
                                                    <br>
                                                    <i class="fas fa-user me-1"></i>Uploaded by: {{ $correctSound->uploader->name ?? 'Unknown' }}
                                                    <br>
                                                    <i class="fas fa-clock me-1"></i>{{ $correctSound->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>Using default generated sound
                                            </div>
                                        @endif

                                        <form action="{{ route('admin.upload-sound') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="sound_type" value="correct">
                                            
                                            <div class="mb-3">
                                                <label for="correctSound" class="form-label fw-bold">Upload New Sound:</label>
                                                <input type="file" class="form-control" id="correctSound" name="sound_file" 
                                                       accept="audio/mpeg,audio/wav,audio/ogg" required>
                                                <small class="text-muted">MP3, WAV, or OGG format (Max: 2MB)</small>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-success w-100">
                                                <i class="fas fa-upload me-2"></i>{{ $correctSound ? 'Replace' : 'Upload' }} Correct Sound
                                            </button>
                                        </form>

                                        @if($correctSound)
                                            <form action="{{ route('admin.delete-sound') }}" method="POST" class="mt-2">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="sound_type" value="correct">
                                                <button type="submit" class="btn btn-outline-danger btn-sm w-100" 
                                                        onclick="return confirm('Are you sure you want to delete this sound? The default generated sound will be used.')">
                                                    <i class="fas fa-trash me-2"></i>Delete & Use Default
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Incorrect Answer Sound -->
                            <div class="col-md-6 mb-4">
                                <div class="card border-danger h-100">
                                    <div class="card-header bg-danger bg-opacity-10 text-danger">
                                        <h5 class="mb-0 fw-bold">
                                            <i class="fas fa-times-circle me-2"></i>Incorrect Answer Sound
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $incorrectSound = $quizSounds->get('incorrect');
                                        @endphp
                                        
                                        @if($incorrectSound)
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Current Audio:</label>
                                                <div class="mb-2">
                                                    <audio controls class="w-100" id="incorrectAudioPreview">
                                                        <source src="{{ asset($incorrectSound->file_path) }}" type="{{ $incorrectSound->mime_type }}">
                                                    </audio>
                                                </div>
                                                <div class="small text-muted">
                                                    <i class="fas fa-file me-1"></i>{{ $incorrectSound->file_name }} 
                                                    ({{ $incorrectSound->file_size_human }})
                                                    <br>
                                                    <i class="fas fa-user me-1"></i>Uploaded by: {{ $incorrectSound->uploader->name ?? 'Unknown' }}
                                                    <br>
                                                    <i class="fas fa-clock me-1"></i>{{ $incorrectSound->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>Using default generated sound
                                            </div>
                                        @endif

                                        <form action="{{ route('admin.upload-sound') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="sound_type" value="incorrect">
                                            
                                            <div class="mb-3">
                                                <label for="incorrectSound" class="form-label fw-bold">Upload New Sound:</label>
                                                <input type="file" class="form-control" id="incorrectSound" name="sound_file" 
                                                       accept="audio/mpeg,audio/wav,audio/ogg" required>
                                                <small class="text-muted">MP3, WAV, or OGG format (Max: 2MB)</small>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-danger w-100">
                                                <i class="fas fa-upload me-2"></i>{{ $incorrectSound ? 'Replace' : 'Upload' }} Incorrect Sound
                                            </button>
                                        </form>

                                        @if($incorrectSound)
                                            <form action="{{ route('admin.delete-sound') }}" method="POST" class="mt-2">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="sound_type" value="incorrect">
                                                <button type="submit" class="btn btn-outline-danger btn-sm w-100" 
                                                        onclick="return confirm('Are you sure you want to delete this sound? The default generated sound will be used.')">
                                                    <i class="fas fa-trash me-2"></i>Delete & Use Default
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-secondary mt-3">
                            <h6 class="fw-bold mb-2"><i class="fas fa-lightbulb me-2"></i>Tips for Best Results:</h6>
                            <ul class="mb-0">
                                <li>Keep sounds short (0.5-2 seconds) for quick feedback</li>
                                <li>Use clear, distinct sounds so students can easily differentiate</li>
                                <li>Test your sounds after uploading to ensure they work properly</li>
                                <li>MP3 format is recommended for best browser compatibility</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
