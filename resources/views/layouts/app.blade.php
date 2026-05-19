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
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1E3A5F">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
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
            window.activeCharts = [];
        })();
    </script>
    <style>
        * { font-family: 'Poppins', sans-serif !important; }

        /* Global Modal Responsive & Scroll Fix */
        .modal-overlay {
            position: fixed !important;
            inset: 0 !important;
            background: rgba(15, 23, 42, 0.75) !important;
            backdrop-filter: blur(8px) !important;
            display: none;
            align-items: flex-start !important; /* Align to top of viewport */
            justify-content: center !important;
            overflow-y: auto !important; /* Enable scrolling if content is taller than screen */
            padding: 0.5rem 1rem !important; /* Vertical spacing around the modal (placed at very top) */
            z-index: 99999 !important;
        }
        .modal-overlay.active {
            display: flex !important;
        }
        .modal-content {
            margin-top: 0 !important; /* Align to top */
            margin-bottom: 3rem !important; /* Spacing at bottom to prevent bottom cut-off */
            max-height: none !important; /* Let modal grow naturally */
            background: var(--color-bg-card, #ffffff) !important;
        }

        /* Stacking Context Fix: Lift wrapper z-index when modal is active so it covers sidebar and topbar */
        body:has(.modal-overlay.active) .main-wrapper,
        body:has(.modal-overlay.active) .page-content {
            z-index: 99999 !important;
            position: relative !important;
        }

        /* Laravel Pagination styling matching simple-datatables footers perfectly */
        nav[role="navigation"] > div:first-child {
            display: none !important; /* Hide redundant mobile pagination buttons */
        }
        nav[role="navigation"] > div:last-child {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            width: 100% !important;
            border-top: 1px solid var(--color-gray-100, #f1f5f9) !important;
            padding: 1rem 1.5rem !important;
            margin-top: 0.5rem !important;
            background: #fff !important;
            border-bottom-left-radius: var(--radius-xl, 16px) !important;
            border-bottom-right-radius: var(--radius-xl, 16px) !important;
        }
        nav[role="navigation"] p {
            margin: 0 !important;
            font-size: 0.875rem !important;
            color: var(--color-gray-500, #64748b) !important;
            font-weight: 500 !important;
        }
        nav[role="navigation"] span.relative.z-0 {
            display: inline-flex !important;
            gap: 0.25rem !important;
            box-shadow: none !important;
            border-radius: 0 !important;
        }
        nav[role="navigation"] span.relative.z-0 a,
        nav[role="navigation"] span.relative.z-0 span[aria-current="page"] > span,
        nav[role="navigation"] span.relative.z-0 span[aria-disabled="true"] > span {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 32px !important;
            height: 32px !important;
            padding: 0 0.5rem !important;
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            color: var(--color-gray-600, #475569) !important;
            background: transparent !important;
            border: 1px solid transparent !important;
            border-radius: 8px !important;
            text-decoration: none !important;
            transition: all 0.2s ease !important;
            cursor: pointer !important;
        }
        nav[role="navigation"] span.relative.z-0 a:hover {
            background: var(--color-gray-100, #f1f5f9) !important;
            color: var(--color-gray-900, #0f172a) !important;
        }
        nav[role="navigation"] span.relative.z-0 span[aria-current="page"] > span {
            background: #d9d9d9 !important; /* Perfect simple-datatables grey highlight matching G1-G10 active state */
            color: #0f172a !important;
            cursor: default !important;
        }
        nav[role="navigation"] span.relative.z-0 span[aria-disabled="true"] > span {
            opacity: 0.4 !important;
            cursor: not-allowed !important;
        }
        nav[role="navigation"] svg {
            width: 14px !important;
            height: 14px !important;
            display: inline-block !important;
            vertical-align: middle !important;
        }

        /* Premium Global Loader Style with Pulsing Wireframe Skeleton */
        .global-loader-overlay {
            position: fixed !important;
            inset: 0 !important;
            background: rgba(15, 23, 42, 0.7) !important;
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            z-index: 1000000 !important; /* Always on top of everything! */
            opacity: 0;
            visibility: hidden !important; /* Completely hidden from clicks when inactive */
            pointer-events: none !important; /* Let clicks pass through when inactive */
            transition: opacity 0.35s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.35s ease;
        }
        .global-loader-overlay.active {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important; /* Block clicks when loading */
        }
        .global-loader-content {
            background: var(--color-bg-card, #ffffff);
            padding: 2rem 2.5rem;
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
            width: 320px;
            text-align: center;
            border: 1px solid var(--color-gray-100);
            transform: scale(0.9);
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .global-loader-overlay.active .global-loader-content {
            transform: scale(1);
        }
        .global-loader-text {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--color-gray-700);
            margin-top: 0.5rem;
        }

        /* Futuristic Wireframe Skeleton Loader Styles */
        .wireframe-skeleton {
            display: flex;
            align-items: center;
            gap: 1rem;
            width: 100%;
            padding: 1.25rem;
            background: #f8fafc;
            border-radius: 16px;
            border: 1.5px dashed var(--color-gray-200, #e2e8f0);
            animation: skeleton-pulse 1.5s ease-in-out infinite;
        }
        [data-theme='dark'] .wireframe-skeleton {
            background: rgba(30, 41, 59, 0.5);
            border-color: var(--color-gray-700);
        }
        .wireframe-avatar {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: var(--color-gray-200, #e2e8f0);
            flex-shrink: 0;
        }
        [data-theme='dark'] .wireframe-avatar {
            background: var(--color-gray-700);
        }
        .wireframe-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .wireframe-line {
            height: 8px;
            border-radius: 4px;
            background: var(--color-gray-200, #e2e8f0);
        }
        [data-theme='dark'] .wireframe-line {
            background: var(--color-gray-700);
        }
        .wireframe-title {
            width: 65%;
            height: 10px;
            background: var(--color-primary, #3b82f6) !important;
            opacity: 0.3;
        }
        .wireframe-text {
            width: 85%;
        }
        .wireframe-sub {
            width: 50%;
        }
        @keyframes skeleton-pulse {
            0% { opacity: 0.5; transform: scale(0.98); }
            50% { opacity: 1; transform: scale(1); }
            100% { opacity: 0.5; transform: scale(0.98); }
        }
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
                    const isKaryawanDashboard = href.includes('/karyawan/dashboard');
                    
                    if (isInternal && !isKaryawanDashboard) {
                        e.preventDefault();
                        const mainWrapper = document.querySelector('.main-wrapper');
                        if (mainWrapper) {
                            mainWrapper.classList.remove('page-fade-in');
                            mainWrapper.classList.add('page-fade-out');
                        }
                        showLoader('Memuat halaman...');
                        setTimeout(() => {
                            window.location.href = href;
                        }, 500); // Elegant 500ms delay to display pulsing wireframe loader
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

        // Global Loader Functions
        function showLoader(text = 'Memproses data...') {
            const loader = document.getElementById('global-page-loader');
            if (loader) {
                const textEl = loader.querySelector('.global-loader-text');
                if (textEl) textEl.innerText = text;
                loader.style.display = 'flex';
                // Force reflow
                loader.offsetHeight;
                loader.classList.add('active');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Keep the gorgeous wireframe loading animation visible for 800ms for premium fluid feel
            setTimeout(() => {
                hideLoader();
            }, 800);
        });

        function hideLoader() {
            const loader = document.getElementById('global-page-loader');
            if (loader) {
                loader.classList.remove('active');
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 350);
            }
        }

        // Global Form Submission Interceptor (SweetAlert2 & Loader Workflow)
        document.addEventListener('submit', function(e) {
            const form = e.target;
            
            // Check if form submission has already been confirmed
            if (form.dataset.confirmed === 'true') {
                return;
            }

            const onsubmitStr = form.getAttribute('onsubmit') || '';
            if (onsubmitStr.includes('confirm(')) {
                e.preventDefault();
                e.stopPropagation();

                // Extract message
                let message = 'Apakah Anda yakin ingin melakukan tindakan ini?';
                const match = onsubmitStr.match(/confirm\(['"](.+?)['"]\)/);
                if (match && match[1]) {
                    message = match[1];
                }

                Swal.fire({
                    title: 'Konfirmasi Tindakan',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3b82f6', // Matching clean theme colors
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Lakukan!',
                    cancelButtonText: 'Batal',
                    background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#ffffff',
                    color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#f8fafc' : '#0f172a'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.dataset.confirmed = 'true';
                        form.removeAttribute('onsubmit');
                        showLoader('Sedang memproses...');
                        setTimeout(() => {
                            form.submit();
                        }, 800); // Premium delay of 800ms to appreciate loader
                    }
                });
            } else {
                // Show loader workflow for standard forms
                e.preventDefault();
                showLoader('Sedang menyimpan data...');
                setTimeout(() => {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }, 800); // Premium delay of 800ms to appreciate loader
            }
        });
    </script>
    @stack('scripts')

    {{-- Premium Global Page Loader Overlay (starts active for instant loading) --}}
    @php
        $isKaryawanDashboard = request()->routeIs('karyawan.dashboard');
    @endphp
    <div id="global-page-loader" class="global-loader-overlay {{ $isKaryawanDashboard ? '' : 'active' }}" style="{{ $isKaryawanDashboard ? 'display: none;' : '' }}">
        <div class="global-loader-content">
            <!-- Modern Pulse Wireframe Skeleton Core -->
            <div class="wireframe-skeleton">
                <div class="wireframe-avatar"></div>
                <div class="wireframe-info">
                    <div class="wireframe-line wireframe-title"></div>
                    <div class="wireframe-line wireframe-text"></div>
                    <div class="wireframe-line wireframe-sub"></div>
                </div>
            </div>
            <div class="global-loader-text">Sedang memuat data... silakan tunggu</div>
        </div>
    </div>

    {{-- PWA Service Worker --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>
</body>
</html>
