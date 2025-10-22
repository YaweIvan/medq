<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MedQ</title>
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
            <div class="col-lg-5 col-md-7 col-sm-9">
                <div class="card auth-card">
                    <div class="card-header text-center border-0 bg-transparent">
                        <div class="mb-3">
                            <i class="fas fa-stethoscope text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h3 class="fw-bold text-dark">Welcome Back</h3>
                        <p class="text-muted">Sign in to your MedQ account</p>
                    </div>
                    <div class="card-body p-4">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                @foreach($errors->all() as $error)
                                    <div><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif
                        
                        @if(session('success'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            </div>
                        @endif
                        
                        <!-- Demo Credentials -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body p-3 text-center">
                                        <h6 class="text-primary mb-2"><i class="fas fa-user-md me-1"></i> Admin</h6>
                                        <small class="text-muted d-block">admin@medq.com</small>
                                        <small class="text-muted">password</small>
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-2 w-100" onclick="fillCredentials('admin@medq.com', 'password')">Use Demo</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body p-3 text-center">
                                        <h6 class="text-success mb-2"><i class="fas fa-user-graduate me-1"></i> Student</h6>
                                        <small class="text-muted d-block">john@example.com</small>
                                        <small class="text-muted">password</small>
                                        <button type="button" class="btn btn-sm btn-outline-success mt-2 w-100" onclick="fillCredentials('john@example.com', 'password')">Use Demo</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-envelope text-muted"></i>
                                    </span>
                                    <input type="email" name="email" id="email" class="form-control border-start-0" placeholder="Enter your email" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-lock text-muted"></i>
                                    </span>
                                    <input type="password" name="password" id="password" class="form-control border-start-0" placeholder="Enter your password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-3 fw-semibold">
                                <i class="fas fa-sign-in-alt me-2"></i> Sign In
                            </button>
                        </form>
                        
                        <script>
                        function fillCredentials(email, password) {
                            document.getElementById('email').value = email;
                            document.getElementById('password').value = password;
                        }
                        </script>
                        
                        <div class="text-center mt-4 pt-3 border-top">
                            <p class="text-muted mb-0">Don't have an account? 
                                <a href="{{ route('register') }}" class="text-primary fw-semibold text-decoration-none">Create Account</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>