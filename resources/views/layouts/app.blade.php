<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BurnoutXpert - Sistem Pakar Deteksi Burnout Karyawan menggunakan metode Backward Chaining dan Certainty Factor.">
    <meta name="keywords" content="burnout, deteksi burnout, kesehatan mental, sistem pakar, backward chaining, certainty factor">
    <meta name="author" content="BurnoutXpert Team">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'BurnoutXpert – Deteksi Kesehatan Mental Karyawan')</title>
    
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23F4845F%22 stroke-width=%222.5%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><path d=%22M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3 1.07.56 2 1.25 2 3a2.5 2.5 0 0 1-2.5 2.5z%22></path><path d=%22M15 16.5c0-1-1-2-1-3 2 1.5 3 3 3 5a5 5 0 0 1-10 0c0-2 1-4 3-6a8 8 0 0 1 5 4z%22></path></svg>">
    
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
    <!-- html2pdf.js for PDF Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <!-- SheetJS for Excel Export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

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

            if (mainWrapper) {
                mainWrapper.classList.add('page-fade-in');
            }
        });

        // Global Excel Export Function
        function exportToExcel(tableId, filename = 'Data-BurnoutXpert.xlsx') {
            const table = document.getElementById(tableId);
            if (!table) {
                Swal.fire('Error', 'Tabel tidak ditemukan!', 'error');
                return;
            }
            
            const wb = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
            XLSX.writeFile(wb, filename);
        }
    </script>
    @stack('scripts')
</body>
</html>
