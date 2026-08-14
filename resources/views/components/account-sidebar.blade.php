@props(['activeItem' => ''])

<div x-data>
{{-- Mobile Menu Button --}}
<button 
    @click="$store.accountSidebar.toggle()"
    class="fixed top-20 left-4 z-50 md:hidden bg-primary text-white p-3 rounded-lg shadow-lg hover:bg-primary-600 transition-colors"
    aria-label="Toggle menu"
>
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
</button>

{{-- Overlay for mobile --}}
<div 
    x-show="$store.accountSidebar.isOpen" 
    @click="$store.accountSidebar.close()"
    x-transition.opacity
    x-cloak
    class="fixed inset-0 bg-black/50 z-40 md:hidden"
></div>

{{-- Sidebar --}}
<aside 
    class="w-full h-full md:w-[211px] md:relative fixed top-0 left-0 z-40 bg-white transition-transform duration-300 md:translate-x-0 overflow-y-auto pt-28 md:pt-0 {{ in_array($activeItem, ['photos', 'services', 'statistics', 'subscription']) ? 'md:mt-[90px]' : 'md:mt-[258px]' }}"
    :class="$store.accountSidebar.isOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
>
    <!-- Navigation Menu -->
    <nav>
        <ul class="space-y-3">
            <li>
                <a href="{{ route('account.dashboard') }}" 
                   class="nav-button {{ $activeItem === 'dashboard' ? 'active' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ __('front.account.sidebar.basic') }}
                </a>
            </li>
            
            <li>
                <a href="{{ route('account.photos') }}"
                   class="nav-button {{ $activeItem === 'photos' ? 'active' : '' }}">
                    <x-icons name="Images" class="w-5 h-5 mr-3" />
                    {{ __('front.account.sidebar.photos') }}
                </a>
            </li>

            <li>
                <a href="{{ route('account.services') }}"
                   class="nav-button {{ $activeItem === 'services' ? 'active' : '' }}">
                    <x-icons name="List" class="w-5 h-5 mr-3" />
                    {{ __('front.account.sidebar.services') }}
                </a>
            </li>

            <li>
                <a href="{{ route('account.statistics') }}"
                   class="nav-button {{ $activeItem === 'statistics' ? 'active' : '' }}">
                    <x-icons name="BarChart4" class="w-5 h-5 mr-3" />
                    {{ __('front.account.sidebar.statistics') }}
                </a>
            </li>
            
            <li>
                <a href="{{ route('account.subscription.index') }}"
                   class="nav-button {{ $activeItem === 'subscription' ? 'active' : '' }}">
                    <x-icons name="star" class="w-5 h-5 mr-3" />
                    {{ __('front.account.sidebar.subscription') }}
                </a>
            </li>

            <li>
                {{-- Was href="#" and greyed out, even though the route, the
                     controller action and the ratings data all exist. --}}
                <a href="{{ route('account.reviews') }}"
                   class="nav-button {{ $activeItem === 'reviews' ? 'active' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    {{ __('front.account.sidebar.reviews') }}
                </a>
            </li>
        </ul>

        <!-- Advert for VIP (hidden on mobile) -->
        @unless(request()->routeIs('preview.*'))
        @php
            $vipAdvertImages = [
                'images/vip-advert.png',
                'images/vip-advert2.png',
                'images/vip-advert3.png',
                'images/vip-advert4.png',
                'images/vip-advert5.png',
                'images/vip-advert6.png',
                'images/vip-advert7.png',
            ];
        @endphp
        <div class="mt-6 relative hidden md:block">
            <!-- VIP Image (auto-rotating) -->
            <div
                class="relative w-full aspect-[210/334] overflow-hidden rounded-t-xl"
                x-data="{ current: 0, images: @js($vipAdvertImages) }"
                x-init="current = Math.floor(Math.random() * images.length); setInterval(() => { current = (current + 1) % images.length }, 10000)"
            >
                @foreach ($vipAdvertImages as $i => $vipImage)
                    <img
                        src="{{ asset($vipImage) }}"
                        alt="VIP"
                        class="absolute inset-0 w-full h-full object-cover transition-opacity duration-700 ease-in-out"
                        x-bind:class="current === {{ $i }} ? 'opacity-100' : 'opacity-0'"
                    >
                @endforeach
            </div>

            <!-- Golden Background Section -->
            <div class="relative p-5 rounded-b-xl border-b-3 border-gold-light" style="background: linear-gradient(180deg, #F5E4B8 0%, #FFFFFF 100%);">
            <!-- Gold Star - Absolutely Positioned -->
            <img src="{{ asset('images/gold-star.png') }}" alt="Gold Star" class="absolute -top-10 left-1/2 -translate-x-1/2 w-16 h-16">
            
            <h3 class="text-3xl py-3font-bold text-gold mb-2 text-center">{{ __('front.account.sidebar.vip_title') }}</h3>
            <a href="{{ route('account.subscription.index') }}" class="btn-gold w-full text-center">
                {{ __('front.account.sidebar.vip_button') }}
            </a>
            </div>
        </div>
        @endunless
    </nav>
</aside>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('accountSidebar', {
            isOpen: false,
            toggle() {
                this.isOpen = !this.isOpen;
            },
            close() {
                this.isOpen = false;
            }
        });
    });
</script>