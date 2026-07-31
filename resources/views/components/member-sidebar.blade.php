<div x-data>
{{-- Mobile Menu Button --}}
<button 
    @click="$store.memberSidebar.toggle()"
    class="fixed top-20 left-4 z-50 md:hidden bg-primary text-white p-3 rounded-lg shadow-lg hover:bg-primary-600 transition-colors"
    aria-label="Toggle menu"
>
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
</button>

{{-- Overlay for mobile --}}
<div 
    x-show="$store.memberSidebar.isOpen" 
    @click="$store.memberSidebar.close()"
    x-transition.opacity
    x-cloak
    class="fixed inset-0 bg-black/50 z-40 md:hidden"
></div>

{{-- Sidebar --}}
<aside
    class="w-full h-full md:w-[210px] md:relative fixed top-0 left-0 z-40 bg-white transition-transform duration-300 md:translate-x-0 overflow-y-auto pt-28 md:pt-0 md:mt-[75px]"
    :class="$store.memberSidebar.isOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
>
    <!-- Navigation Menu -->
    <nav class="p-6 md:p-0">
        <ul class="space-y-3">
            {{-- Ratings Section --}}
            <li>
                <a href="{{ route('account.member.ratings') }}"
                   class="nav-button !w-[210px] !h-[50px] !p-0 !px-4 !rounded-[8px] !border !border-[#E6E6E6] {{ request()->routeIs('account.member.ratings') ? 'active' : '' }}">
                    <svg class="w-[20px] h-[20px] mr-3" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5.83464 17.5H14.168M10.0013 2.5V17.5M2.5013 5.83333H4.16797C5.83464 5.83333 8.33464 5 10.0013 4.16667C11.668 5 14.168 5.83333 15.8346 5.83333H17.5013M13.3346 13.3333L15.8346 6.66667L18.3346 13.3333C17.6096 13.875 16.7346 14.1667 15.8346 14.1667C14.9346 14.1667 14.0596 13.875 13.3346 13.3333ZM1.66797 13.3333L4.16797 6.66667L6.66797 13.3333C5.94297 13.875 5.06797 14.1667 4.16797 14.1667C3.26797 14.1667 2.39297 13.875 1.66797 13.3333Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ __('front.account.member.ratings') }}
                </a>
            </li>

            {{-- My Favorites Section --}}
            <li>
                <a href="{{ route('account.member.favorites') }}"
                   class="nav-button !w-[210px] !h-[50px] !p-0 !px-4 !rounded-[8px] !border !border-[#E6E6E6] {{ request()->routeIs('account.member.favorites') ? 'active' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    {{ __('front.account.member.favorites') }}
                </a>
            </li>

            {{-- Girls of the Month Section --}}
            <li>
                <a href="{{ route('account.member.girls-of-month') }}"
                   class="nav-button !w-[210px] !h-[50px] !p-0 !px-4 !rounded-[8px] !border !border-[#E6E6E6] {{ request()->routeIs('account.member.girls-of-month') ? 'active' : '' }}">
                    <svg class="w-[20px] h-[20px] mr-3" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6.66667 1.66675V5.00008M13.3333 1.66675V5.00008M2.5 8.33342H17.5M6.66667 11.6667H6.675M10 11.6667H10.0083M13.3333 11.6667H13.3417M6.66667 15.0001H6.675M10 15.0001H10.0083M13.3333 15.0001H13.3417M4.16667 3.33341H15.8333C16.7538 3.33341 17.5 4.07961 17.5 5.00008V16.6667C17.5 17.5872 16.7538 18.3334 15.8333 18.3334H4.16667C3.24619 18.3334 2.5 17.5872 2.5 16.6667V5.00008C2.5 4.07961 3.24619 3.33341 4.16667 3.33341Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ __('front.account.member.girls_of_month') }}
                </a>
            </li>

            {{-- Girls Archive Section --}}
            <li>
                <a href="{{ route('account.member.archive') }}"
                   class="nav-button !w-[210px] !h-[50px] !p-0 !px-4 !rounded-[8px] !border !border-[#E6E6E6] {{ request()->routeIs('account.member.archive') ? 'active' : '' }}">
                    <svg class="w-[20px] h-[20px] mr-3" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.5 10C2.5 11.4834 2.93987 12.9334 3.76398 14.1668C4.58809 15.4001 5.75943 16.3614 7.12987 16.9291C8.50032 17.4968 10.0083 17.6453 11.4632 17.3559C12.918 17.0665 14.2544 16.3522 15.3033 15.3033C16.3522 14.2544 17.0665 12.918 17.3559 11.4632C17.6453 10.0083 17.4968 8.50032 16.9291 7.12987C16.3614 5.75943 15.4001 4.58809 14.1668 3.76398C12.9334 2.93987 11.4834 2.5 10 2.5C7.90329 2.50789 5.89081 3.32602 4.38333 4.78333L2.5 6.66667M6.66667 6.66667H2.5V2.5M10 5.83333V10L13.3333 11.6667" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ __('front.account.member.archive') }}
                </a>
            </li>

            {{-- Reported Girls Section --}}
            <li>
                <a href="{{ route('account.member.reported') }}"
                   class="nav-button !w-[210px] !h-[50px] !p-0 !px-4 !rounded-[8px] !border !border-[#E6E6E6] {{ request()->routeIs('account.member.reported') ? 'active' : '' }}">
                    <svg class="w-[20px] h-[20px] mr-3" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10.0007 7.50012V10.8335M10.0007 14.1668H10.0091M18.109 15.0001L11.4423 3.33344C11.297 3.07694 11.0862 2.8636 10.8314 2.71516C10.5767 2.56673 10.2872 2.48853 9.99234 2.48853C9.69752 2.48853 9.40797 2.56673 9.15324 2.71516C8.8985 2.8636 8.6877 3.07694 8.54234 3.33344L1.87567 15.0001C1.72874 15.2546 1.6517 15.5434 1.65235 15.8372C1.653 16.131 1.73132 16.4195 1.87938 16.6733C2.02744 16.9271 2.23996 17.1373 2.49542 17.2825C2.75088 17.4277 3.04018 17.5028 3.33401 17.5001H16.6673C16.9598 17.4998 17.2469 17.4226 17.5001 17.2762C17.7532 17.1298 17.9634 16.9193 18.1094 16.666C18.2555 16.4127 18.3324 16.1254 18.3323 15.833C18.3322 15.5406 18.2552 15.2533 18.109 15.0001Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ __('front.account.member.reported') }}
                </a>
            </li>

            {{-- User Settings Section --}}
            <li>
                <a href="{{ route('account.member.dashboard') }}"
                   class="nav-button !w-[210px] !h-[50px] !p-0 !px-4 !rounded-[8px] !border !border-[#E6E6E6] {{ request()->routeIs('account.member.dashboard') ? 'active' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ __('front.account.member.settings') }}
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
        Alpine.store('memberSidebar', {
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
