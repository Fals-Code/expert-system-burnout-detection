<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'BurnoutXpert')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/table.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/wizard.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/empty-state.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/report.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/profile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/notifikasi.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/detail-karyawan.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/hasil.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bantuan.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-knowledge.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/kelola-pengguna.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pengaturan.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/log-aktivitas.css') }}">

    <!-- ApexCharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <!-- Lottie Web Player -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <!-- Intro.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/introjs.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Simple-DataTables -->
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.0/dist/style.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.0/dist/umd/simple-datatables.js"></script>

    @stack('styles')

    <script>
        // Immediate Theme Application (Prevent FOUC)
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                document.body && document.body.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    <style>
        * { font-family: 'Poppins', sans-serif !important; }
    </style>
</head>
<body>

    @include('layouts.sidebar')

    <div class="main-wrapper">
        @include('layouts.topbar')

        <main class="page-content">
            @yield('content')
        </main>
    </div>

    <script>
        // Global Sidebar Toggle for Mobile
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('active');
            }
        }

        // Page Transition Interceptor
        document.addEventListener('DOMContentLoaded', () => {
            const links = document.querySelectorAll('a[href]');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    const target = this.getAttribute('target');
                    
                    if (!href || href.startsWith('#') || href.startsWith('javascript:') || target === '_blank') {
                        return;
                    }

                    if (this.hasAttribute('download') || e.defaultPrevented) return;
                    
                    const isInternal = href.startsWith('/') || href.startsWith('./') || href.startsWith('../') || !href.includes('://');
                    
                    if (isInternal) {
                        e.preventDefault();
                        const mainWrapper = document.querySelector('.main-wrapper');
                        if (mainWrapper) {
                            mainWrapper.classList.remove('page-fade-in');
                            mainWrapper.classList.add('page-fade-out');
                        }
                        setTimeout(() => {
                            window.location.href = href;
                        }, 250);
                    }
                });
            });

            window.addEventListener('pageshow', function (event) {
                if (event.persisted) {
                    const mainWrapper = document.querySelector('.main-wrapper');
                    if (mainWrapper) {
                        mainWrapper.classList.remove('page-fade-out');
                        mainWrapper.classList.add('page-fade-in');
                    }
                }
            });

            const mainWrapper = document.querySelector('.main-wrapper');
            if (mainWrapper) {
                mainWrapper.classList.add('page-fade-in');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
