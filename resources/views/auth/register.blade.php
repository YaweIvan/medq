<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MedQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        body {
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .auth-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 col-sm-10">
                <div class="card auth-card">
                    <div class="card-header text-center border-0 bg-transparent">
                        <div class="mb-3">
                            <i class="fas fa-user-plus text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h3 class="fw-bold text-dark">Join MedQ</h3>
                        <p class="text-muted">Create your account to start learning</p>
                    </div>
                    <div class="card-body p-4">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                @foreach($errors->all() as $error)
                                    <div><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-user text-muted"></i>
                                    </span>
                                    <input type="text" name="name" class="form-control border-start-0" placeholder="Enter your full name" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-envelope text-muted"></i>
                                    </span>
                                    <input type="email" name="email" class="form-control border-start-0" placeholder="Enter your email" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-semibold">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                        <input type="password" name="password" id="password" class="form-control border-start-0 border-end-0" placeholder="Password" required>
                                        <span class="input-group-text bg-light border-start-0" style="cursor: pointer;" onclick="togglePassword('password', this)">
                                            <i class="fas fa-eye text-muted"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-semibold">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control border-start-0 border-end-0" placeholder="Confirm" required>
                                        <span class="input-group-text bg-light border-start-0" style="cursor: pointer;" onclick="togglePassword('password_confirmation', this)">
                                            <i class="fas fa-eye text-muted"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success w-100 py-3 fw-semibold">
                                <i class="fas fa-user-plus me-2"></i> Create Account
                            </button>
                        </form>
                        
                        <script>
                        function togglePassword(inputId, icon) {
                            const input = document.getElementById(inputId);
                            const iconElement = icon.querySelector('i');
                            if (input.type === 'password') {
                                input.type = 'text';
                                iconElement.classList.remove('fa-eye');
                                iconElement.classList.add('fa-eye-slash');
                            } else {
                                input.type = 'password';
                                iconElement.classList.remove('fa-eye-slash');
                                iconElement.classList.add('fa-eye');
                            }
                        }
                        </script>
                        
                        <div class="text-center mt-4 pt-3 border-top">
                            <p class="text-muted mb-0">Already have an account? 
                                <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none">Sign In</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>