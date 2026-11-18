@props(['activeItem' => ''])

<div>
{{-- Mobile account navigation lives in the header menu. --}}

{{-- Sidebar --}}
@php
    // The sidebar has to start level with the divider under the page's
    // heading, and each heading is a different height, so the offset is
    // calibrated by hand.
    //
    // The ordinary heading — a title with a rule under it — is 88px tall, so
    // that is the default. It used to be the dashboard's 258px, which meant a
    // page nobody had listed here (archived notifications) sat 170px too low.
    // The outlier is the exception now, not the rule.
    $sidebarOffsetClass = match($activeItem) {
        'dashboard' => 'md:mt-[238px]', // heading carries the stats row (Figma home-2: 238px)
        'reviews' => 'md:mt-[88px]',
        default => 'md:mt-[85px]',
    };
@endphp
{{-- Figma home-2/11/17: items 210x50, 1px #E6E6E6 border (crisp in home-17;
     home-2 draws it on a half pixel, which reads as 2px #F0F0F0), radius 8, 10px
     apart, icon 18px from the border and 10px before the label; the column
     sits 1px right of the notice bar (x 394) and 79px left of the content. --}}
<style>
    .account-sidebar .nav-button { width: 210px; max-width: 100%; height: 50px; padding: 0 16px 0 18px; border-width: 1px; border-color: #E6E6E6; border-radius: 8px; }
    .account-sidebar .nav-button.active, .account-sidebar .nav-button:hover { border-color: var(--color-primary-500); }
    .account-sidebar .nav-button > svg, .account-sidebar .nav-button > span { margin-right: 10px; }
    .account-sidebar .nav-list > li + li { margin-top: 10px; }
</style>
{{-- Pass 5: no h-full — with the top offset (md:mt-[238px]) a full-height
     aside hung 238 px below its column, a white box over the top of the
     footer (in WebKit/Safari it covered "VIP & Premium" and "FAQ"). --}}
<aside
    class="account-sidebar hidden md:block w-full md:w-[211px] md:ml-px md:relative bg-white overflow-y-auto {{ $sidebarOffsetClass }}"
>
    <!-- Navigation Menu -->
    <nav>
        <ul class="nav-list">
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
                    {{-- Figma home-2 draws this item with the thumbs-up icon. --}}
                    <x-icons name="ThumbsUp" class="w-5 h-5 mr-3" />
                    {{ __('front.account.sidebar.reviews') }}
                </a>
            </li>
        </ul>

        <!-- Advert for VIP (hidden on mobile) -->
        @unless(request()->routeIs('preview.*'))
        @php
            $vipOwnPhotoUrl = auth()->user()?->profile?->getFirstImageUrl('medium');
        @endphp
        {{-- Figma home-2: card 30px under the last item, photo 313 tall, gold
             panel 159 tall incl. the 4px gold rule overlapping the photo by 3px
             (pass 2 measurement: panel top 310px under the card top), title 24/700, button
             174x46 with 18px side padding, star 57px reaching 37px up. --}}
        <div class="mt-[30px] relative hidden md:block w-[210px]">
            <!-- VIP Image (own profile photo) -->
            <div class="relative w-full h-[313px] overflow-hidden rounded-t-[8px]">
                @if ($vipOwnPhotoUrl)
                    <img
                        src="{{ $vipOwnPhotoUrl }}"
                        alt="VIP"
                        class="absolute inset-0 w-full h-full object-cover"
                    >
                @else
                    <img
                        src="{{ asset('images/vip-advert.png') }}"
                        alt="VIP"
                        class="absolute inset-0 w-full h-full object-cover"
                    >
                @endif
            </div>

            <!-- Golden Background Section -->
            <div class="relative -mt-[3px] h-[159px] px-[18px] pt-[33px] rounded-[8px] border-b-4 border-[#F6E5BA]" style="background: linear-gradient(180deg, #F5E4B8 0%, #FFFFFF 100%);">
            <!-- Gold Star - Absolutely Positioned -->
            <img src="{{ asset('images/gold-star.png') }}" alt="Gold Star" class="absolute -top-[35px] left-[calc(50%-4px)] -translate-x-1/2 w-[57px] h-[57px]">

            <h3 class="text-[24px] leading-[36px] font-bold text-[#A07613] text-center whitespace-nowrap" style="font-family: 'Poppins', sans-serif;">{{ __('front.account.sidebar.vip_title') }}</h3>
            <a href="{{ route('account.subscription.index') }}" class="btn-gold w-full text-center !h-[46px] !py-0 !text-[16px] mt-[14px]">
                {{ __('front.account.sidebar.vip_button') }}
            </a>
            </div>
        </div>
        @endunless
    </nav>
</aside>
</div>
