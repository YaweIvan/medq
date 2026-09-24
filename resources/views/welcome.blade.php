<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUMSA MedQ - Welcome</title>

    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }

        .hero-section {
            background: #0d1117;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        /* Soft pastel blobs — light enough to keep white feel */
        .hero-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: none;
            pointer-events: none;
            z-index: 0;
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
            background: rgba(147, 197, 253, 0.08);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
        
        .shape:nth-child(1) { width: 100px; height: 100px; top: 10%; left: 10%; animation-delay: 0s; }
        .shape:nth-child(2) { width: 150px; height: 150px; top: 70%; right: 15%; animation-delay: 3s; }
        .shape:nth-child(3) { width: 80px; height: 80px; bottom: 20%; left: 20%; animation-delay: 6s; }
        .shape:nth-child(4) { width: 120px; height: 120px; top: 30%; right: 30%; animation-delay: 2s; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.4; }
            50% { transform: translateY(-30px) rotate(180deg); opacity: 0.75; }
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

        /* ── Glass cards ── */
        .login-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 20px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            height: 100%;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.40),
                        inset 0 1px 0 rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }
        
        .login-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 48px rgba(0, 0, 0, 0.55),
                        inset 0 1px 0 rgba(255, 255, 255, 0.22);
            border-color: rgba(147, 197, 253, 0.35);
            background: rgba(255, 255, 255, 0.13);
        }
        
        .card-icon {
            width: 60px;
            height: 60px;
            background: rgba(147, 197, 253, 0.35);
            border: 1px solid rgba(147, 197, 253, 0.50);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
            box-shadow: 0 4px 16px rgba(147, 197, 253, 0.25),
                        inset 0 1px 0 rgba(255,255,255,0.60);
        }
        
        .credentials {
            background: rgba(255, 255, 255, 0.35);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        .feature-list li {
            padding: 0.25rem 0;
            color: rgba(203, 213, 225, 0.90);
            display: flex;
            align-items: center;
            font-size: 0.85rem;
        }
        
        .feature-list li i {
            margin-right: 0.5rem;
            color: #10b981;
        }

        /* ── MUMSA branding strip — glass pill ── */
        .mumsa-branding {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            padding: 10px 20px;
            margin-bottom: 10px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 60px;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.30),
                        inset 0 1px 0 rgba(255,255,255,0.12);
        }
        
        .mumsa-logo-small {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid rgba(147, 197, 253, 0.40);
            padding: 5px;
            background: rgba(255, 255, 255, 0.10);
            box-shadow: 0 2px 8px rgba(0,0,0,0.30);
        }
        
        .mumsa-text {
            text-align: left;
        }
        
        .mumsa-text .association {
            font-size: 0.85rem;
            font-weight: 600;
            color: #f1f5f9;
            margin: 0;
            line-height: 1.4;
        }
        
        .mumsa-text .copyright {
            font-size: 0.75rem;
            color: rgba(203, 213, 225, 0.80);
            margin: 0;
        }

        /* ── Welcome page text classes ── */
        .welcome-heading { color: #f1f5f9; }
        .welcome-subtext { color: rgba(203, 213, 225, 0.90); }

        /* ── Light theme overrides for welcome page ── */
        body.light-theme .hero-section { background: #f8fafc !important; }
        body.light-theme .shape { background: rgba(147, 197, 253, 0.12) !important; border-color: rgba(147, 197, 253, 0.30) !important; }
        body.light-theme .logo { color: #93c5fd !important; }
        body.light-theme .login-card {
            background: #ffffff !important;
            border: 2px solid #e5e7eb !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08) !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }
        body.light-theme .login-card:hover {
            box-shadow: 0 20px 40px rgba(147, 197, 253, 0.15) !important;
            border-color: #93c5fd !important;
            background: #ffffff !important;
        }
        body.light-theme .card-icon {
            background: #93c5fd !important;
            border: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            box-shadow: none !important;
        }
        body.light-theme .mumsa-branding {
            background: rgba(255,255,255,0.80) !important;
            border-color: #e2e8f0 !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06) !important;
        }
        body.light-theme .mumsa-logo-small { background: #ffffff !important; border-color: #93c5fd !important; }
        body.light-theme .mumsa-text .association { color: #1e293b !important; }
        body.light-theme .mumsa-text .copyright   { color: #64748b !important; }
        body.light-theme .welcome-heading  { color: #1e293b !important; }
        body.light-theme .welcome-subtext  { color: #4b5563 !important; }
        body.light-theme .feature-list li  { color: #4b5563 !important; }
        body.light-theme #themeToggleBtn {
            background: rgba(255,255,255,0.80) !important;
            border-color: #e2e8f0 !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.10) !important;
        }
    </style>
</head>
<body>
    <script>(function(){if(localStorage.getItem('medq-theme')==='light'){document.body.classList.add('light-theme');}})();</script>
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
                    <h1 class="fw-bold mb-2 welcome-heading" style="font-size: 1.75rem;">Medical Quiz Platform</h1>
                    <p class="mb-2 welcome-subtext" style="font-size: 0.95rem;">Advanced medical knowledge assessment system for healthcare professionals and students</p>
                    
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
                            <h3 class="fw-bold mb-2 welcome-heading" style="font-size: 1.25rem;">Administrator Portal</h3>
                            <p class="mb-3 welcome-subtext" style="font-size: 0.85rem;">Manage quizzes, users, and monitor platform performance with comprehensive admin tools.</p>
                            

                            
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
                            <h3 class="fw-bold mb-2 welcome-heading" style="font-size: 1.25rem;">Student Portal</h3>
                            <p class="mb-3 welcome-subtext" style="font-size: 0.85rem;">Access assigned quizzes, track your progress, and enhance your medical knowledge through interactive learning.</p>
                            

                            <!-- uncomment to work 😂 -->
                            <a href="{{ route('login') }}" class="btn w-100 fw-semibold" style="background: rgba(147,197,253,0.15); color: #93c5fd; border: 2px solid rgba(147,197,253,0.45);">
                                <img src="https://cdn-icons-png.flaticon.com/512/9068/9068642.png" style="width: 18px; height: 18px; margin-right: 6px;">Student Login
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Register Section -->
                <div class="text-center mt-3 pt-3">
                    <div class="border-top pt-3" style="border-color: rgba(255,255,255,0.12) !important;">
                        <p class="mb-2 welcome-subtext" style="font-size: 0.9rem;">New to MedQ? Join thousands of medical professionals</p>
                        <a href="{{ route('register') }}" class="btn btn-success px-4">
                            <img src="https://cdn-icons-png.flaticon.com/128/14616/14616849.png" style="width: 18px; height: 18px; margin-right: 6px; filter: brightness(0) invert(1);">Create Free Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Theme Toggle Button ── -->
    <button id="themeToggleBtn" onclick="toggleTheme()" title="Toggle Light / Dark theme" style="
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 9999;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        border: 1px solid rgba(255,255,255,0.25);
        background: rgba(255,255,255,0.12);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.35);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        transition: all 0.3s ease;
    ">🌙</button>

    <script>
        // ── Theme persistence ──────────────────────────────────
        (function () {
            if (localStorage.getItem('medq-theme') === 'light') {
                document.body.classList.add('light-theme');
            }
            updateToggleIcon();
        })();

        function updateToggleIcon() {
            var btn = document.getElementById('themeToggleBtn');
            if (!btn) return;
            var isLight = document.body.classList.contains('light-theme');
            btn.textContent = isLight ? '🌙' : '☀️';
            btn.title = isLight ? 'Switch to Dark theme' : 'Switch to Light theme';
            btn.style.background = isLight
                ? 'rgba(30, 41, 59, 0.15)'
                : 'rgba(255, 255, 255, 0.12)';
            btn.style.borderColor = isLight
                ? 'rgba(30, 41, 59, 0.30)'
                : 'rgba(255, 255, 255, 0.25)';
        }

        function toggleTheme() {
            var isLight = document.body.classList.toggle('light-theme');
            localStorage.setItem('medq-theme', isLight ? 'light' : 'dark');
            updateToggleIcon();
        }
    </script>
</body>
</html>