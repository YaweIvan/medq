<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUMSA MedQ - Loading</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
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
        
        .mumsa-logo {
            max-width: 250px;
            height: auto;
            margin-bottom: 30px;
            animation: pulse 2s infinite;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.1));
            border-radius: 50%;
            border: 4px solid #93c5fd;
            padding: 10px;
            background: white;
        }
        
        .branding-text {
            color: #1e293b;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            text-shadow: none;
            line-height: 1.6;
        }
        
        .tagline {
            color: #64748b;
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
    </style>
</head>
<body>
    <div class="splash-container">
        <img src="{{ asset('images/mums.png') }}" alt="MUMSA Logo" class="mumsa-logo">
        
        <div class="branding-text">
            MAKERERE UNIVERSITY MEDICAL<br>
            STUDENTS ASSOCIATION
        </div>
        
        <div class="tagline">
            All Rights Reserved © 2026
        </div>
        
        <div class="loading-dots">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
    </div>
    
    <script>
        setTimeout(() => {
            window.location.href = '{{ route("welcome") }}';
        }, 3000);
    </script>
</body>
</html>