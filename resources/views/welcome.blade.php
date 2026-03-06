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
            padding: 1rem 0;
        }
        
        .logo {
            font-size: 3rem;
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
            padding: 1.5rem;
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
            width: 60px;
            height: 60px;
            background: #93c5fd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
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
            padding: 0.25rem 0;
            color: #4b5563;
            display: flex;
            align-items: center;
            font-size: 0.85rem;
        }
        
        .feature-list li i {
            margin-right: 0.5rem;
            color: #10b981;
        }
        
        .mumsa-branding {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            padding: 10px 0;
            margin-bottom: 10px;
        }
        
        .mumsa-logo-small {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid #93c5fd;
            padding: 5px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .mumsa-text {
            text-align: left;
        }
        
        .mumsa-text .association {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
            line-height: 1.4;
        }
        
        .mumsa-text .copyright {
            font-size: 0.75rem;
            color: #64748b;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="hero-section">
        
        
        <div class="content-wrapper">
            <div class="container">
                <!-- MUMSA Branding -->
                <div class="mumsa-branding">
                    <img src="{{ $welcomeLogo ? asset('storage/' . $welcomeLogo) : asset('images/mums.png') }}" alt="MUMSA Logo" class="mumsa-logo-small">
                    <div class="mumsa-text">
                        <p class="association">{{ $orgName }}</p>
                        <p class="copyright">{{ $orgTagline }}</p>
                    </div>
                </div>
                
                <!-- Header Section -->
                <div class="text-center mb-3">
                    <div class="logo mb-2">
                        <img src="https://cdn-icons-png.flaticon.com/512/684/684262.png" style="width: 48px; height: 48px; vertical-align: middle; margin-right: 10px;"> MedQ
                    </div>
                    <h1 class="fw-bold mb-2" style="color: #1e293b; font-size: 1.75rem;">Medical Quiz Platform</h1>
                    <p class="mb-2" style="color: #64748b; font-size: 0.95rem;">Advanced medical knowledge assessment system for healthcare professionals and students</p>
                    
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <ul class="feature-list d-flex flex-wrap justify-content-center gap-2 mb-0">
                                <li><img src="https://cdn-icons-png.flaticon.com/512/5610/5610944.png" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 5px;"> Interactive Quizzes</li>
                                <li><img src="https://cdn-icons-png.flaticon.com/512/5610/5610944.png" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 5px;"> Real-time Feedback</li>
                                <li><img src="https://cdn-icons-png.flaticon.com/512/5610/5610944.png" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 5px;"> Progress Tracking</li>
                                <li><img src="https://cdn-icons-png.flaticon.com/512/5610/5610944.png" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 5px;"> Subject-based Learning</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Login Cards -->
                <div class="row justify-content-center g-4">
                    <div class="col-lg-5 col-md-6">
                        <div class="login-card text-center">
                            <div class="card-icon">
                                <img src="https://cdn-icons-png.flaticon.com/512/3774/3774299.png" style="width: 36px; height: 36px;">
                            </div>
                            <h3 class="fw-bold mb-2" style="color: #1e293b; font-size: 1.25rem;">Administrator Portal</h3>
                            <p class="mb-3" style="color: #64748b; font-size: 0.85rem;">Manage quizzes, users, and monitor platform performance with comprehensive admin tools.</p>
                            

                            
                            <a href="{{ route('login') }}" class="btn w-100 fw-semibold" style="background: #93c5fd; color: white;">
                                <img src="https://cdn-icons-png.flaticon.com/128/3206/3206130.png" style="width: 18px; height: 18px; margin-right: 6px; filter: brightness(0) invert(1);">Admin Login
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-5 col-md-6">
                        <div class="login-card text-center">
                            <div class="card-icon">
                                <img src="https://cdn-icons-png.flaticon.com/128/3135/3135810.png" style="width: 36px; height: 36px;">
                            </div>
                            <h3 class="fw-bold mb-2" style="color: #1e293b; font-size: 1.25rem;">Student Portal</h3>
                            <p class="mb-3" style="color: #64748b; font-size: 0.85rem;">Access assigned quizzes, track your progress, and enhance your medical knowledge through interactive learning.</p>
                            

                            <!-- uncomment to work 😂 -->
                            <a href="{{ route('login') }}" class="btn w-100 fw-semibold" style="background: white; color: #93c5fd; border: 2px solid #93c5fd;">
                                <img src="https://cdn-icons-png.flaticon.com/512/9068/9068642.png" style="width: 18px; height: 18px; margin-right: 6px;">Student Login
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Register Section -->
                <div class="text-center mt-3 pt-3">
                    <div class="border-top pt-3" style="border-color: #e5e7eb !important;">
                        <p class="mb-2" style="color: #64748b; font-size: 0.9rem;">New to MedQ? Join thousands of medical professionals</p>
                        <a href="{{ route('register') }}" class="btn btn-success px-4">
                            <img src="https://cdn-icons-png.flaticon.com/128/14616/14616849.png" style="width: 18px; height: 18px; margin-right: 6px; filter: brightness(0) invert(1);">Create Free Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>