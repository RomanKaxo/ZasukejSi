{{--
    Shared heading of the provider account pages (Figma home-2, home-11,
    home-17, home-18): 36/700 title, its glyph 60px above the #E9E9E9 rule,
    online state on the right with the "nastavení" link left-aligned under
    the text. Every page used to carry its own copy, and three of them
    printed "offline" with a red dot no matter what.

    @param string $title
    @param string $ruleGap  Tailwind margin class under the rule (the
                            distance to the first section differs per frame)
--}}
@php
    $isOnline = auth()->user()?->isOnline();
    $statusDot = $isOnline ? 'bg-[#00B80F]' : 'bg-[#D80027]';
    $statusText = $isOnline ? __('front.account.dashboard.online') : __('front.account.dashboard.offline');
@endphp

@if($mobile ?? true)
<!-- Mobile Header -->
{{-- pass 2: the rules and the online row span x 22..335 at 360px (313 wide,
     1.5px left of centre), as in every phone frame.
     pass 1 overlay (phone 360 px, -4, -5, -13): the title is centred in a
     fixed block — 83px under a notice bar (rule 119px under the bar), 132px
     from the header when no bar is shown (rule at y 230) — whether it wraps
     to one line or two; the online row sits 13.5px under the rule. --}}
<style>
    @media (max-width: 767px) {
        .account-mobile-title { min-height: 83px; padding-top: 0 !important; padding-bottom: 7.5px !important; }
        .account-mobile-title h1 { line-height: 38px; }
        .account-shell:not(:has(.account-notice-sticky, [data-profile-visibility])) .account-mobile-title { min-height: 131px; padding-top: 4px !important; padding-bottom: 0 !important; }
        .account-mobile-rule { margin-bottom: 13.5px !important; }
        /* pass 2 (phone 360 px-13): under a notice bar the rule sits at y 324.5. */
        .account-shell:has(.account-notice-sticky, [data-profile-visibility]) .account-mobile-rule { margin-top: -2.5px; }
        /* pass 2 (phone 360 px, -4, -5): without a bar the first rule sits at y 229, the second stays at 278. */
        .account-shell:not(:has(.account-notice-sticky, [data-profile-visibility])) .account-mobile-rule { margin-bottom: 14.5px !important; }
    }
</style>
<div class="{{ ($mobileRule ?? true) ? 'mb-[49.5px]' : 'mb-4' }} md:hidden">
    <div class="account-mobile-title relative flex items-center justify-center py-3">
        <h1 class="w-[400px] max-w-full text-[38px] text-center" style="font-family:'Poppins',sans-serif; font-weight:700; color:#5C2D62;">
            {{ $title }}
        </h1>
    </div>
    <hr class="account-mobile-rule w-[313px] max-w-full mx-auto mb-4 relative -left-[1.5px]">
    <div class="flex items-center justify-between w-[313px] max-w-full mx-auto relative -left-[1.5px]">
        <div class="flex items-center gap-1.5">
            <div class="w-[14px] h-[14px] rounded-full {{ $statusDot }}"></div>
            <span class="text-[13px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">{{ $statusText }}</span>
        </div>
        <a href="{{ route('account.dashboard') }}" class="text-[13px] underline" style="font-family: 'Poppins', sans-serif; color: #DD3888;">{{ __('front.account.dashboard.settings') }}</a>
    </div>
    {{-- phone 360 px / -4 / -5: a second rule closes the online row (the
         dashboard, -13, goes straight into its stat cards instead); the
         first section heading follows 53px under it. --}}
    @if($mobileRule ?? true)
        <hr class="account-mobile-rule2 w-[313px] max-w-full mx-auto relative -left-[1.5px]" style="margin-top:14px;">
    @endif
</div>

@endif

@if($desktop ?? true)
<!-- Desktop Header -->
{{-- Pass 5: between 768 and 1023 px (iPad, unfolded Fold) the content
     column is only ~450–700 px wide, so the absolutely placed online state
     sat on top of the 400 px centred title ("ceny" under "Nyní jsi online").
     There it drops under the title, right-aligned. --}}
<style>
    @media (min-width: 768px) and (max-width: 1023px) {
        .account-desktop-head { flex-direction: column; align-items: center; }
        .account-desktop-head .account-desktop-status { position: static; transform: none; translate: none; margin-top: 10px; align-self: flex-end; }
    }
</style>
<div class="hidden md:block">
    <div class="account-desktop-head relative flex items-center justify-center pt-6 pb-[21px]">
        <h1 class="w-[400px] max-w-full text-4xl text-center" style="font-family:'Poppins',sans-serif; font-weight:700; color:#5C2D62;">
            {{ $title }}
        </h1>
        <div class="account-desktop-status flex flex-col items-start absolute right-0 top-1/2 -translate-y-1/2 -mt-[4px]">
            <div class="flex items-center gap-1.5">
                <div class="w-[14px] h-[14px] rounded-full {{ $statusDot }}"></div>
                <span class="text-[13px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">{{ $statusText }}</span>
            </div>
            <a href="{{ route('account.dashboard') }}" class="ml-[21px] -mt-[3px] text-[13px] underline" style="font-family: 'Poppins', sans-serif; color: #DD3888;">{{ __('front.account.dashboard.settings') }}</a>
        </div>
    </div>
    <hr class="{{ $ruleGap ?? 'mb-[80px]' }}">
</div>
@endif
