<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0                <!-- Header Section -->
                <div class="text-center mb-5">
                   
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        
        .hero-section {
            background: #ffffff;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        }
        
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }
        
        .shape {
            position: absolute;
            background: rgba(147, 197, 253, 0.1);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }
        
        .shape:nth-child(1) { width: 100px; height: 100px; top: 10%; left: 10%; animation-delay: 0s; }
        .shape:nth-child(2) { width: 150px; height: 150px; top: 70%; right: 15%; animation-delay: 3s; }
        .shape:nth-child(3) { width: 80px; height: 80px; bottom: 20%; left: 20%; animation-delay: 6s; }
        .shape:nth-child(4) { width: 120px; height: 120px; top: 30%; right: 30%; animation-delay: 2s; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.3; }
            50% { transform: translateY(-30px) rotate(180deg); opacity: 0.6; }
        }
        
        .content-wrapper {
            position: relative;
            z-index: 2;
            padding: 4rem 0;
        }
        
        .logo {
            font-size: 4rem;
            font-weight: 800;
            color: #93c5fd;
            text-shadow: 0 4px 20px rgba(147, 197, 253, 0.3);
            animation: pulse 3s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .login-card {
            background: #ffffff;
            border: 2px solid #e5e7eb;
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.3s ease;
            height: 100%;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .login-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(147, 197, 253, 0.15);
            border-color: #93c5fd;
        }
        
        .card-icon {
            width: 80px;
            height: 80px;
            background: #93c5fd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }
        
        .credentials {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
            border: 1px solid #e5e7eb;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        .feature-list li {
            padding: 0.5rem 0;
            color: #4b5563;
            display: flex;
            align-items: center;
        }
        
        .feature-list li i {
            margin-right: 0.5rem;
            color: #10b981;
        }
    </style>
</head>
<body>
    <div class="hero-section">
        
        
        <div class="content-wrapper">
            <div class="container">
                <!-- Header Section -->
                <div class="text-center mb-5">
                    <div class="logo mb-4">
                        <img src="https://cdn-icons-png.flaticon.com/512/684/684262.png" style="width: 64px; height: 64px; vertical-align: middle; margin-right: 10px;"> MedQ
                    </div>
                    <h1 class="fw-bold mb-3" style="color: #1e293b;">Medical Quiz Platform</h1>
                    <p class="fs-5 mb-4" style="color: #64748b;">Advanced medical knowledge assessment system for healthcare professionals and students</p>
                    
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <ul class="feature-list d-flex flex-wrap justify-content-center gap-4 mb-0">
                                <li><img src="https://cdn-icons-png.flaticon.com/512/5610/5610944.png" style="width: 20px; height: 20px; vertical-align: middle; margin-right: 5px;"> Interactive Quizzes</li>
                                <li><img src="https://cdn-icons-png.flaticon.com/512/5610/5610944.png" style="width: 20px; height: 20px; vertical-align: middle; margin-right: 5px;"> Real-time Feedback</li>
                                <li><img src="https://cdn-icons-png.flaticon.com/512/5610/5610944.png" style="width: 20px; height: 20px; vertical-align: middle; margin-right: 5px;"> Progress Tracking</li>
                                <li><img src="https://cdn-icons-png.flaticon.com/512/5610/5610944.png" style="width: 20px; height: 20px; vertical-align: middle; margin-right: 5px;"> Subject-based Learning</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Login Cards -->
                <div class="row justify-content-center g-4">
                    <div class="col-lg-5 col-md-6">
                        <div class="login-card text-center">
                            <div class="card-icon">
                                <img src="https://cdn-icons-png.flaticon.com/512/3774/3774299.png" style="width: 48px; height: 48px;">
                            </div>
                            <h3 class="fw-bold mb-3" style="color: #1e293b;">Administrator Portal</h3>
                            <p class="mb-4" style="color: #64748b;">Manage quizzes, users, and monitor platform performance with comprehensive admin tools.</p>
                            

                            
                            <a href="{{ route('login') }}" class="btn btn-lg w-100 fw-semibold" style="background: #93c5fd; color: white;">
                                <img src="https://cdn-icons-png.flaticon.com/512/9068/9068642.png" style="width: 20px; height: 20px; margin-right: 8px; filter: brightness(0) invert(1);">Admin Login
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-5 col-md-6">
                        <div class="login-card text-center">
                            <div class="card-icon">
                                <img src="https://cdn-icons-png.flaticon.com/512/3976/3976625.png" style="width: 48px; height: 48px;">
                            </div>
                            <h3 class="fw-bold mb-3" style="color: #1e293b;">Student Portal</h3>
                            <p class="mb-4" style="color: #64748b;">Access assigned quizzes, track your progress, and enhance your medical knowledge through interactive learning.</p>
                            

                            <!-- uncomment to work 😂 -->
                            <a href="{{ route('login') }}" class="btn btn-lg w-100 fw-semibold" style="background: white; color: #93c5fd; border: 2px solid #93c5fd;">
                                <img src="https://cdn-icons-png.flaticon.com/512/9068/9068642.png" style="width: 20px; height: 20px; margin-right: 8px;">Student Login
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Register Section -->
                <div class="text-center mt-5 pt-4">
                    <div class="border-top pt-4" style="border-color: #e5e7eb !important;">
                        <p class="mb-3" style="color: #64748b;">New to MedQ? Join thousands of medical professionals</p>
                        <a href="{{ route('register') }}" class="btn btn-success btn-lg px-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/1828/1828817.png" style="width: 20px; height: 20px; margin-right: 8px; filter: brightness(0) invert(1);">Create Free Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>