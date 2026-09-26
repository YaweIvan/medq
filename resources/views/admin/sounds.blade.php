<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Sound Settings - MedQ</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        /* Black and Light Blue Theme */
        .sound-card-primary {
            border: 2px solid #87CEEB !important;
        }
        .sound-card-primary .card-header {
            background-color: rgba(135, 206, 235, 0.1) !important;
            color: #1e90ff !important;
            border-bottom: 1px solid #87CEEB;
        }
        .sound-card-secondary {
            border: 2px solid #333 !important;
        }
        .sound-card-secondary .card-header {
            background-color: rgba(51, 51, 51, 0.05) !important;
            color: #000 !important;
            border-bottom: 1px solid #333;
        }
        .btn-sound-primary {
            background-color: #87CEEB !important;
            border-color: #87CEEB !important;
            color: #000 !important;
        }
        .btn-sound-primary:hover {
            background-color: #1e90ff !important;
            border-color: #1e90ff !important;
            color: #fff !important;
        }
        .btn-sound-secondary {
            background-color: #333 !important;
            border-color: #333 !important;
            color: #fff !important;
        }
        .btn-sound-secondary:hover {
            background-color: #000 !important;
            border-color: #000 !important;
        }
        .alert-sound-info {
            background-color: rgba(135, 206, 235, 0.1) !important;
            border-color: #87CEEB !important;
            color: #000 !important;
        }
        .alert-sound-warning {
            background-color: rgba(51, 51, 51, 0.05) !important;
            border-color: #333 !important;
            color: #000 !important;
        }
        .alert-sound-tips {
            background-color: rgba(135, 206, 235, 0.05) !important;
            border: 1px solid #87CEEB !important;
            color: #000 !important;
        }
        .page-title {
            color: #000 !important;
        }
        .btn-outline-primary {
            border-color: #87CEEB !important;
            color: #1e90ff !important;
        }
        .btn-outline-primary:hover {
            background-color: #87CEEB !important;
            color: #000 !important;
        }
    </style>
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
                        <div class="alert alert-sound-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            Upload custom audio files for quiz answer feedback. Supported formats: MP3, WAV, OGG (Max size: 2MB each)
                        </div>

                        @if(session('audio_success'))
                            <div class="alert alert-sound-info alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('audio_success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('audio_error'))
                            <div class="alert alert-sound-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('audio_error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="row">
                            <!-- Correct Answer Sound -->
                            <div class="col-md-6 mb-4">
                                <div class="card sound-card-primary h-100">
                                    <div class="card-header">
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
                                            <div class="alert alert-sound-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>No correct sound uploaded. Using default generated sound.
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
                                            <button type="submit" class="btn btn-sound-primary w-100">
                                                <i class="fas fa-upload me-2"></i>{{ $correctSound ? 'Replace' : 'Upload' }} Correct Sound
                                            </button>
                                        </form>

                                        @if($correctSound)
                                            <form action="{{ route('admin.delete-sound') }}" method="POST" class="mt-2">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="sound_type" value="correct">
                                                <button type="submit" class="btn btn-sound-secondary btn-sm w-100" 
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
                                <div class="card sound-card-secondary h-100">
                                    <div class="card-header">
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
                                            <div class="alert alert-sound-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>No incorrect sound uploaded. Using default generated sound.
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
                                            <button type="submit" class="btn btn-sound-secondary w-100">
                                                <i class="fas fa-upload me-2"></i>{{ $incorrectSound ? 'Replace' : 'Upload' }} Incorrect Sound
                                            </button>
                                        </form>

                                        @if($incorrectSound)
                                            <form action="{{ route('admin.delete-sound') }}" method="POST" class="mt-2">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="sound_type" value="incorrect">
                                                <button type="submit" class="btn btn-sound-secondary btn-sm w-100" 
                                                        onclick="return confirm('Are you sure you want to delete this sound? The default generated sound will be used.')">
                                                    <i class="fas fa-trash me-2"></i>Delete & Use Default
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Timer/Tick-Tock Sound -->
                            <div class="col-md-6 mb-4">
                                <div class="card sound-card-primary h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0 fw-bold">
                                            <i class="fas fa-clock me-2"></i>Timer Sound (Tick-Tock)
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $timerSound = $quizSounds->get('timer');
                                        @endphp
                                        
                                        @if($timerSound)
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Current Audio:</label>
                                                <div class="mb-2">
                                                    <audio controls class="w-100" id="timerAudioPreview">
                                                        <source src="{{ asset($timerSound->file_path) }}" type="{{ $timerSound->mime_type }}">
                                                    </audio>
                                                </div>
                                                <div class="small text-muted">
                                                    <i class="fas fa-file me-1"></i>{{ $timerSound->file_name }} 
                                                    ({{ $timerSound->file_size_human }})
                                                    <br>
                                                    <i class="fas fa-user me-1"></i>Uploaded by: {{ $timerSound->uploader->name ?? 'Unknown' }}
                                                    <br>
                                                    <i class="fas fa-clock me-1"></i>{{ $timerSound->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert alert-sound-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>Using default generated tick-tock sound
                                            </div>
                                        @endif

                                        <form action="{{ route('admin.upload-sound') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="sound_type" value="timer">
                                            
                                            <div class="mb-3">
                                                <label for="timerSound" class="form-label fw-bold">Upload New Sound:</label>
                                                <input type="file" class="form-control" id="timerSound" name="sound_file" 
                                                       accept="audio/mpeg,audio/wav,audio/ogg" required>
                                                <small class="text-muted">MP3, WAV, or OGG (Max: 2MB). Loops continuously.</small>
                                            </div>
                                            <button type="submit" class="btn btn-sound-primary w-100">
                                                <i class="fas fa-upload me-2"></i>{{ $timerSound ? 'Replace' : 'Upload' }} Timer Sound
                                            </button>
                                        </form>

                                        @if($timerSound)
                                            <form action="{{ route('admin.delete-sound') }}" method="POST" class="mt-2">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="sound_type" value="timer">
                                                <button type="submit" class="btn btn-sound-secondary btn-sm w-100" 
                                                        onclick="return confirm('Are you sure you want to delete this sound? The default generated tick-tock sound will be used.')">
                                                    <i class="fas fa-trash me-2"></i>Delete & Use Default
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- 10-Second Warning Sound -->
                            <div class="col-md-6 mb-4">
                                <div class="card sound-card-secondary h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0 fw-bold">
                                            <i class="fas fa-exclamation-triangle me-2"></i>10-Second Warning Alert
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $warningSound = $quizSounds->get('warning');
                                        @endphp
                                        
                                        @if($warningSound)
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Current Audio:</label>
                                                <div class="mb-2">
                                                    <audio controls class="w-100" id="warningAudioPreview">
                                                        <source src="{{ asset($warningSound->file_path) }}" type="{{ $warningSound->mime_type }}">
                                                    </audio>
                                                </div>
                                                <div class="small text-muted">
                                                    <i class="fas fa-file me-1"></i>{{ $warningSound->file_name }} 
                                                    ({{ $warningSound->file_size_human }})
                                                    <br>
                                                    <i class="fas fa-user me-1"></i>Uploaded by: {{ $warningSound->uploader->name ?? 'Unknown' }}
                                                    <br>
                                                    <i class="fas fa-clock me-1"></i>{{ $warningSound->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert alert-sound-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>Using default generated alert beep
                                            </div>
                                        @endif

                                        <form action="{{ route('admin.upload-sound') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="sound_type" value="warning">
                                            
                                            <div class="mb-3">
                                                <label for="warningSound" class="form-label fw-bold">Upload New Sound:</label>
                                                <input type="file" class="form-control" id="warningSound" name="sound_file" 
                                                       accept="audio/mpeg,audio/wav,audio/ogg" required>
                                                <small class="text-muted">MP3, WAV, or OGG (Max: 2MB). Plays once at 10s.</small>
                                            </div>
                                            <button type="submit" class="btn btn-sound-secondary w-100">
                                                <i class="fas fa-upload me-2"></i>{{ $warningSound ? 'Replace' : 'Upload' }} Warning Sound
                                            </button>
                                        </form>

                                        @if($warningSound)
                                            <form action="{{ route('admin.delete-sound') }}" method="POST" class="mt-2">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="sound_type" value="warning">
                                                <button type="submit" class="btn btn-sound-secondary btn-sm w-100" 
                                                        onclick="return confirm('Are you sure you want to delete this sound? The default generated alert will be used.')">
                                                    <i class="fas fa-trash me-2"></i>Delete & Use Default
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-sound-tips mt-3">
                            <h6 class="fw-bold mb-2"><i class="fas fa-lightbulb me-2"></i>Tips for Best Results:</h6>
                            <ul class="mb-0">
                                <li><strong>Answer Feedback:</strong> Keep sounds short (0.5-2 seconds) for quick feedback</li>
                                <li><strong>Timer Sound:</strong> Use a subtle tick-tock that loops continuously (1-2 seconds)</li>
                                <li><strong>10-Second Warning:</strong> Use a distinct alert sound to grab attention (plays once)</li>
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

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
    // Listen to the native browser volume knob on each audio player and persist
    // the new volume to the DB so question.blade.php picks it up system-wide.
    (function () {
        const map = {
            'correctAudioPreview':   'correct',
            'incorrectAudioPreview': 'incorrect',
            'timerAudioPreview':     'timer',
            'warningAudioPreview':   'warning',
        };

        const csrf    = '{{ csrf_token() }}';
        const saveUrl = '{{ route("admin.update-sound-volume") }}';

        let saveTimer = null; // debounce — don't spam on every tiny drag tick

        Object.entries(map).forEach(function([id, soundType]) {
            const el = document.getElementById(id);
            if (!el) return;

            el.addEventListener('volumechange', function () {
                clearTimeout(saveTimer);
                saveTimer = setTimeout(function () {
                    fetch(saveUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({
                            sound_type: soundType,
                            volume: parseFloat(el.volume.toFixed(2)),
                        }),
                    }).catch(function () {});
                }, 400);
            });
        });
    })();
    </script>
</body>
</html>
