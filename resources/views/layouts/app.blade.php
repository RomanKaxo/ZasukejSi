<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} - @yield('title', __('front.title'))</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        @media (max-width: 767px) {
            html,
            body,
            #app,
            main,
            footer {
                background-color: #FFFFFF !important;
            }
        }
    </style>
</head>
<body class="font-poppins antialiased text-text-default relative bg-white md:bg-transparent">
    <div id="app" class="overflow-x-hidden bg-white md:bg-transparent">
        <!-- Navigation -->
        <x-navbar /> 

        <!-- Main Content -->
        <main class="flex-1 bg-white md:bg-transparent">
            @yield('content')
        </main>

        <!-- Footer -->
        <x-footer />

        <!-- Auth Modals -->
        @guest
            <livewire:login-modal />
            <livewire:register-modal />
            <livewire:reset-modal />
        @endguest
    </div>

    <!-- Additional Scripts -->
    @livewireScripts

    @stack('scripts')

    <!-- Auth Modal Scripts -->
    @guest
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle escape key to close modals
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    Livewire.dispatch('hide-login-modal');
                    Livewire.dispatch('hide-register-modal');
                    Livewire.dispatch('hide-reset-modal');
                }
            });
        });
    </script>
    @endguest
    <script>
        // Small visual debug badge to show Livewire client presence
        (function(){
            try {
                var d = document.createElement('div');
                d.id = 'lw-debug-badge';
                d.style.position = 'fixed';
                d.style.right = '12px';
                d.style.bottom = '12px';
                d.style.zIndex = 99999;
                d.style.background = 'rgba(255,255,255,0.95)';
                d.style.border = '1px solid #eee';
                d.style.padding = '6px 10px';
                d.style.fontSize = '12px';
                d.style.color = '#222';
                d.style.borderRadius = '6px';
                d.style.boxShadow = '0 6px 18px rgba(0,0,0,0.06)';
                d.innerText = 'Livewire: checking...';
                document.body.appendChild(d);

                function update() {
                    var present = typeof Livewire !== 'undefined' ? 'loaded' : 'missing';
                    d.innerText = 'Livewire: ' + present;
                }

                // Update on load and when Livewire fires its load event
                update();
                document.addEventListener('livewire:load', update);
            } catch (err) {}
        })();
    </script>
    <script>
        // Global fallback for opening the English fullscreen country picker on mobile
        (function () {
            function openPicker() {
                var picker = document.querySelector('.search-country-mobile-picker');
                if (!picker) return;
                picker.style.display = 'block';
                document.documentElement.style.overflow = 'hidden';
                document.body.style.overflow = 'hidden';
            }

            function closePicker() {
                var picker = document.querySelector('.search-country-mobile-picker');
                if (!picker) return;
                picker.style.display = 'none';
                document.documentElement.style.overflow = '';
                document.body.style.overflow = '';
            }

            // Use capture phase to intercept clicks before Alpine/Livewire handlers
            document.addEventListener('click', function (e) {
                try {
                    if (window.innerWidth > 767) return;
                    if (e.target.closest && e.target.closest('#country-select')) {
                        e.preventDefault();
                        e.stopPropagation();
                        openPicker();
                        return;
                    }

                    if (e.target.closest && e.target.closest('.search-country-mobile-picker__close')) {
                        e.preventDefault();
                        e.stopPropagation();
                        closePicker();
                        return;
                    }

                    var picker = document.querySelector('.search-country-mobile-picker');
                    if (picker && getComputedStyle(picker).display !== 'none') {
                        if (!e.target.closest('.search-country-mobile-picker__inner') && !e.target.closest('#country-select')) {
                            closePicker();
                        }
                    }
                } catch (err) {
                    // ignore
                }
            }, true);

            // Also listen for pointerdown (mobile taps) at capture phase to catch touch events earlier
            document.addEventListener('pointerdown', function (e) {
                try {
                    if (window.innerWidth > 767) return;
                    if (e.target.closest && e.target.closest('#country-select')) {
                        e.preventDefault();
                        e.stopPropagation();
                        var picker = document.querySelector('.search-country-mobile-picker');
                        if (picker) {
                            picker.style.display = 'block';
                            document.documentElement.style.overflow = 'hidden';
                            document.body.style.overflow = 'hidden';
                        }
                        return;
                    }
                } catch (err) {}
            }, true);
        })();
    </script>
</body>
</html>
