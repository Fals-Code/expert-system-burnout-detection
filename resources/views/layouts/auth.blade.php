<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sanctuary Hub')</title>
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/sanctuary-hub-mark.svg') }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/minimal-ui.css') }}">
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
