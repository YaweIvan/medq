<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUMSA MedQ - Loading</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #0d1117;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .splash-container {
            text-align: center;
            animation: fadeIn 0.8s ease-in;
        }
        
        .logo-wrapper {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            border: 4px solid rgba(147, 197, 253, 0.40);
            background: rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: pulse 2s infinite;
            box-shadow: 0 4px 20px rgba(147,197,253,0.20);
            overflow: hidden;
            flex-shrink: 0;
        }

        .mumsa-logo {
            width: 85%;
            height: 85%;
            object-fit: contain;
        }
        
        .branding-text {
            color: #f1f5f9;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            text-shadow: none;
            line-height: 1.6;
        }
        
        .tagline {
            color: rgba(203, 213, 225, 0.85);
            font-size: 0.95rem;
            margin-bottom: 40px;
            text-shadow: none;
        }
        
        .loading-dots {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
        }
        
        .dot {
            width: 12px;
            height: 12px;
            background-color: #93c5fd;
            border-radius: 50%;
            animation: bounce 1.4s infinite ease-in-out;
            box-shadow: 0 2px 8px rgba(147, 197, 253, 0.4);
        }
        
        .dot:nth-child(1) { animation-delay: -0.32s; }
        .dot:nth-child(2) { animation-delay: -0.16s; }
        .dot:nth-child(3) { animation-delay: 0s; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); opacity: 0.5; }
            40% { transform: scale(1); opacity: 1; }
        }

        /* ── Light theme overrides for splash page ── */
        body.light-theme                  { background: #f8fafc !important; }
        body.light-theme .logo-wrapper    { background: #ffffff !important; border-color: #93c5fd !important; box-shadow: 0 4px 20px rgba(147,197,253,0.20) !important; }
        body.light-theme .branding-text   { color: #1e293b !important; }
        body.light-theme .tagline         { color: #64748b !important; }
    </style>
</head>
<body>
    <script>(function(){if(localStorage.getItem('medq-theme')==='light'){document.body.classList.add('light-theme');}})();</script>
    <div class="splash-container">
        <div class="logo-wrapper">
            <img src="{{ $splashLogo ? asset('storage/' . $splashLogo) : asset('images/mums.png') }}" alt="MUMSA Logo" class="mumsa-logo">
        </div>
        
        <div class="branding-text">
            {{ $orgName }}
        </div>
        
        <div class="tagline">
            {{ $orgTagline }}
        </div>
        
        <div class="loading-dots">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
    </div>
    
    <script>
        setTimeout(() => {
            @if(session('after_splash') === 'login')
                window.location.href = '{{ route("login") }}';
            @else
                window.location.href = '{{ route("welcome") }}';
            @endif
        }, 2000);
    </script>
</body>
</html>