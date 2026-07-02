@props(['activeItem' => ''])

@php
    $hasProfile = auth()->user()->profile !== null;
@endphp

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
    class="w-full h-full md:w-80 md:relative fixed top-0 left-0 z-40 bg-white transition-transform duration-300 md:translate-x-0 overflow-y-auto pt-28 md:pt-0 md:mt-10"
    :class="$store.accountSidebar.isOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
>
    <!-- Navigation Menu -->
    <nav class="p-6">
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
                @if($hasProfile)
                <a href="{{ route('account.photos') }}" 
                   class="nav-button  {{ $activeItem === 'photos' ? 'active' : '' }}">
                @else
                <span class="nav-button !text-gray-400 !cursor-not-allowed" title="{{ __('front.account.profile_required_short') }}">
                @endif
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ __('front.account.sidebar.photos') }}
                    @if(!$hasProfile)
                        <svg class="w-4 h-4 ml-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    @endif
                @if($hasProfile)
                </a>
                @else
                </span>
                @endif
            </li>
            
            <li>
                @if($hasProfile)
                <a href="{{ route('account.services') }}" 
                   class="nav-button  {{ $activeItem === 'services' ? 'active' : '' }}">
                @else
                <span class="nav-button !text-gray-400 !cursor-not-allowed" title="{{ __('front.account.profile_required_short') }}">
                @endif
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    {{ __('front.account.sidebar.services') }}
                    @if(!$hasProfile)
                        <svg class="w-4 h-4 ml-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    @endif
                @if($hasProfile)
                </a>
                @else
                </span>
                @endif
            </li>
            
            <li>
                @if($hasProfile)
                <a href="{{ route('account.statistics') }}" 
                   class="nav-button  {{ $activeItem === 'statistics' ? 'active' : '' }}">
                @else
                <span class="nav-button !text-gray-400 !cursor-not-allowed" title="{{ __('front.account.profile_required_short') }}">
                @endif
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    {{ __('front.account.sidebar.statistics') }}
                    @if(!$hasProfile)
                        <svg class="w-4 h-4 ml-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    @endif
                @if($hasProfile)
                </a>
                @else
                </span>
                @endif
            </li>
            
            <li>
                <a href="#" 
                   class="nav-button  {{ $activeItem === 'reviews' ? 'active' : '' }} !text-gray-400">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    {{ __('front.account.sidebar.reviews') }}
                </a>
            </li>
        </ul>

        <!-- Advert for VIP (hidden on mobile) -->
        @unless(request()->routeIs('preview.*'))
        <div class="mt-6 relative hidden md:block">
            <!-- VIP Image -->
            <img src="{{ asset('images/vip-advert.png') }}" alt="VIP" class="w-full rounded-t-xl">
            
            <!-- Golden Background Section -->
            <div class="relative p-5 rounded-b-xl border-b-3 border-gold-light" style="background: linear-gradient(180deg, #F5E4B8 0%, #FFFFFF 100%);">
            <!-- Gold Star - Absolutely Positioned -->
            <img src="{{ asset('images/gold-star.png') }}" alt="Gold Star" class="absolute -top-10 left-1/2 -translate-x-1/2 w-16 h-16">
            
            <h3 class="text-3xl py-3font-bold text-gold mb-2 text-center">{{ __('front.account.sidebar.vip_title') }}</h3>
            <a href="#" class="btn-gold w-full text-center">
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