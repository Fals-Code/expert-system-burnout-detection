<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'BurnoutXpert')</title>
    
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23F4845F%22 stroke-width=%222.5%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><path d=%22M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3 1.07.56 2 1.25 2 3a2.5 2.5 0 0 1-2.5 2.5z%22></path><path d=%22M15 16.5c0-1-1-2-1-3 2 1.5 3 3 3 5a5 5 0 0 1-10 0c0-2 1-4 3-6a8 8 0 0 1 5 4z%22></path></svg>">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    @stack('styles')

    <script>
        // Immediate Theme Application
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    <style>
        * { font-family: 'Poppins', sans-serif !important; }
    </style>
</head>
<body class="login-body">
    @yield('content')
    
    <script src="{{ asset('assets/js/login.js') }}"></script>
    @stack('scripts')
</body>
</html>
