<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedQ - Medical Quiz Platform</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>:root { --primary: {{ $appPrimaryColor ?? '#93c5fd' }}; --secondary: {{ $appSecondaryColor ?? '#bfdbfe' }}; }</style>
    <script>
        // Apply collapsed state immediately before page renders to prevent flash
        if (window.innerWidth > 768 && localStorage.getItem('quizzerSidebarCollapsed') === 'true') {
            document.documentElement.classList.add('sidebar-pre-collapsed');
        }
    </script>
    <style>
        .sidebar-pre-collapsed #quizzerSidebar {
            width: 70px;
        }
        .sidebar-pre-collapsed .main-content {
            margin-left: 70px;
        }
    </style>
</head>
<body>
    @yield('content')
    
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>