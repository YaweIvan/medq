<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - MedQ</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        body {
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .setup-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            color: #e2e8f0;
        }
        .setup-card .form-label {
            color: #94a3b8;
            font-size: 0.85rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .setup-card .form-control {
            background: #0f172a;
            border: 1px solid #334155;
            color: #e2e8f0;
            border-radius: 8px;
        }
        .setup-card .form-control:focus {
            background: #0f172a;
            border-color: #6366f1;
            color: #e2e8f0;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
        }
        .setup-card .form-control::placeholder {
            color: #475569;
        }
        .setup-card .form-control.is-invalid {
            border-color: #ef4444;
        }
        .invalid-feedback { color: #f87171; }
        .btn-submit {
            background: #6366f1;
            border: none;
            color: #fff;
            border-radius: 8px;
            padding: 10px;
            font-weight: 600;
            width: 100%;
            transition: background 0.15s;
        }
        .btn-submit:hover { background: #4f46e5; color: #fff; }
        .divider { border-color: #334155; }
        .back-link { color: #6366f1; font-size: 0.9rem; }
        .back-link:hover { color: #818cf8; }
        .toggle-pw { cursor: pointer; color: #475569; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); }
        .pw-wrap { position: relative; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7 col-sm-10">
                <div class="card setup-card p-2">
                    <div class="card-header text-center border-0 bg-transparent pt-4 pb-2">
                        <div class="mb-2">
                            <i class="fas fa-shield-alt" style="font-size:2.5rem; color:#6366f1;"></i>
                        </div>
                        <h4 class="fw-bold mb-1" style="color:#e2e8f0;">Admin Recovery</h4>
                        <p style="color:#64748b; font-size:0.9rem;">Reset admin credentials using your server secret key.</p>
                    </div>

                    <div class="card-body px-4 pb-4">
                        @if($errors->any())
                            <div class="alert alert-danger py-2" style="background:#450a0a; border-color:#b91c1c; color:#fca5a5; border-radius:8px;">
                                @foreach($errors->all() as $error)
                                    <div><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('setup.process') }}" autocomplete="off">
                            @csrf

                            <div class="mb-3">
                                <label for="secret_key" class="form-label">Secret Key</label>
                                <div class="pw-wrap">
                                    <input type="password"
                                           id="secret_key"
                                           name="secret_key"
                                           class="form-control pe-5 @error('secret_key') is-invalid @enderror"
                                           placeholder="Enter SETUP_SECRET_KEY from .env"
                                           required
                                           autocomplete="off">
                                    <span class="toggle-pw" onclick="togglePw('secret_key', this)">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                                @error('secret_key')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="divider my-3">

                            <div class="mb-3">
                                <label for="new_email" class="form-label">New Admin Email</label>
                                <input type="email"
                                       id="new_email"
                                       name="new_email"
                                       class="form-control @error('new_email') is-invalid @enderror"
                                       value="{{ old('new_email') }}"
                                       placeholder="admin@example.com"
                                       required>
                                @error('new_email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <div class="pw-wrap">
                                    <input type="password"
                                           id="new_password"
                                           name="new_password"
                                           class="form-control pe-5 @error('new_password') is-invalid @enderror"
                                           placeholder="At least 8 characters"
                                           required>
                                    <span class="toggle-pw" onclick="togglePw('new_password', this)">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                                @error('new_password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="new_password_confirmation" class="form-label">Confirm Password</label>
                                <div class="pw-wrap">
                                    <input type="password"
                                           id="new_password_confirmation"
                                           name="new_password_confirmation"
                                           class="form-control pe-5"
                                           placeholder="Repeat new password"
                                           required>
                                    <span class="toggle-pw" onclick="togglePw('new_password_confirmation', this)">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-submit">
                                <i class="fas fa-key me-2"></i>Reset Admin Credentials
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            <a href="{{ route('login') }}" class="back-link">
                                <i class="fas fa-arrow-left me-1"></i>Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePw(fieldId, icon) {
            const input = document.getElementById(fieldId);
            const i = icon.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                i.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                i.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
