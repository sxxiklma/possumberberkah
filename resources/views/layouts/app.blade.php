<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name', 'TOKO SUMBER BERKAH') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        [x-cloak] { display: none !important; }

        /* Hardware-accelerated smooth touch scrolling on mobile */
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
            overscroll-behavior-x: contain;
            scrollbar-width: thin;
            scrollbar-color: #94a3b8 #f1f5f9;
        }

        /* Sleek horizontal scrollbar for all tables */
        .overflow-x-auto::-webkit-scrollbar {
            height: 6px;
        }
        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 9999px;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 9999px;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full text-slate-800 antialiased selection:bg-emerald-500 selection:text-white"
      x-data="{ 
          sidebarOpen: window.innerWidth >= 1024,
          sidebarCollapsed: false,
          activeAccordion: '{{ $activeAccordion ?? '' }}'
      }">

    <div class="min-h-full flex flex-col lg:flex-row">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 min-h-screen bg-slate-50 transition-all duration-300"
             :class="{'lg:pl-64': !sidebarCollapsed && sidebarOpen, 'lg:pl-20': sidebarCollapsed && sidebarOpen, 'lg:pl-0': !sidebarOpen}">
            
            <!-- Navbar -->
            @include('layouts.navbar')

            <!-- Page Header / Breadcrumb -->
            @hasSection('header')
                <div class="bg-white border-b border-slate-200 px-4 sm:px-6 lg:px-8 py-4">
                    @yield('header')
                </div>
            @endif

            <!-- Main Content Container -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-slate-200 px-4 sm:px-6 py-4 text-center text-xs text-slate-500">
                &copy; {{ date('Y') }} <strong>TOKO SUMBER BERKAH</strong> — Point of Sale & Inventory Management System. All rights reserved.
            </footer>
        </div>
    </div>

    <!-- Global Toast & SweetAlert handler -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if(session('success'))
                window.showToast('success', "{{ session('success') }}");
            @endif

            @if(session('error'))
                window.Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: "{{ session('error') }}",
                    confirmButtonColor: '#ef4444',
                });
            @endif

            @if(session('warning'))
                window.showToast('warning', "{{ session('warning') }}");
            @endif
        });
    </script>

    <!-- Mobile Interactive Horizontal Table Slider (Capsule Scrollbar) -->
    <script>
        function initTableSliders() {
            const containers = document.querySelectorAll('.overflow-x-auto');
            containers.forEach(container => {
                if (!container.querySelector('table') || container.closest('.print\\:block, [data-no-scroll-slider]')) return;

                // Remove legacy hint banner if present
                if (container.previousElementSibling && container.previousElementSibling.classList.contains('mobile-table-hint')) {
                    container.previousElementSibling.remove();
                }

                let sliderContainer = container.nextElementSibling;
                if (!sliderContainer || !sliderContainer.classList.contains('table-slider-container')) {
                    sliderContainer = document.createElement('div');
                    sliderContainer.className = 'table-slider-container lg:hidden px-3 py-2 bg-slate-50/80 border-t border-slate-100 select-none';
                    sliderContainer.innerHTML = `
                        <div class="table-slider-track relative w-full h-2 bg-slate-200/90 rounded-full cursor-pointer touch-none">
                            <div class="table-slider-thumb absolute top-0 bottom-0 bg-slate-400 active:bg-slate-600 rounded-full shadow-2xs transition-colors" style="width: 25%; left: 0%;"></div>
                        </div>
                    `;
                    container.parentNode.insertBefore(sliderContainer, container.nextSibling);
                }

                const track = sliderContainer.querySelector('.table-slider-track');
                const thumb = sliderContainer.querySelector('.table-slider-thumb');

                function updateThumb() {
                    const clientWidth = container.clientWidth;
                    const scrollWidth = container.scrollWidth;
                    const maxScroll = scrollWidth - clientWidth;

                    if (maxScroll <= 4) {
                        sliderContainer.style.display = 'none';
                        return;
                    }

                    sliderContainer.style.display = 'block';
                    const thumbWidthPercent = Math.max(15, Math.min(80, (clientWidth / scrollWidth) * 100));
                    thumb.style.width = thumbWidthPercent + '%';

                    const scrollRatio = maxScroll > 0 ? (container.scrollLeft / maxScroll) : 0;
                    const thumbLeftPercent = scrollRatio * (100 - thumbWidthPercent);
                    thumb.style.left = Math.max(0, Math.min(100 - thumbWidthPercent, thumbLeftPercent)) + '%';
                }

                container.addEventListener('scroll', updateThumb, { passive: true });

                let isDragging = false;
                function seekTo(clientX) {
                    const rect = track.getBoundingClientRect();
                    const pos = Math.max(0, Math.min(rect.width, clientX - rect.left));
                    const ratio = rect.width > 0 ? (pos / rect.width) : 0;
                    const maxScroll = container.scrollWidth - container.clientWidth;
                    container.scrollLeft = ratio * maxScroll;
                }

                track.addEventListener('pointerdown', (e) => {
                    isDragging = true;
                    try { track.setPointerCapture(e.pointerId); } catch(err) {}
                    seekTo(e.clientX);
                });

                track.addEventListener('pointermove', (e) => {
                    if (!isDragging) return;
                    seekTo(e.clientX);
                });

                const endDrag = (e) => {
                    if (isDragging) {
                        isDragging = false;
                        try { track.releasePointerCapture(e.pointerId); } catch(err) {}
                    }
                };

                track.addEventListener('pointerup', endDrag);
                track.addEventListener('pointercancel', endDrag);

                updateThumb();
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            initTableSliders();
            setTimeout(initTableSliders, 350);
            window.addEventListener('resize', initTableSliders, { passive: true });
        });
    </script>

    @stack('scripts')
</body>
</html>

