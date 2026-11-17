@extends('layouts.app')

@section('content')
<!-- Add top padding to account for fixed navbar -->
{{-- Figma account frames draw every rule in #E9E9E9 (components.css
     makes hr gray-300 globally, and that file is shared). --}}
<style>.account-shell hr { border-color: #E9E9E9; }
/* Figma home-11..20 (no notice bar): the page heading's rule sits at y 258,
   38px lower than where it lands when the content simply starts at the
   135px used under a notice bar (home-2). */
@media (min-width: 768px) { .account-shell:not(:has(.account-notice-sticky, [data-profile-visibility])) { padding-top: 173px; } }</style>
<div class="account-shell container mx-auto pt-[98px] md:pt-[135px] min-h-screen px-4 min-[1180px]:px-0">
    @php
        // The banner used to carry a fixed line of filler text and showed on
        // every account page regardless of state. It now reports what is
        // actually missing, and disappears once the profile is complete.
        $accountProfile = auth()->user()?->profile;

        $missingSteps = [];
        if ($accountProfile) {
            if (blank($accountProfile->about)) {
                $missingSteps[] = ['label' => __('front.account.completion.about'), 'route' => 'account.dashboard'];
            }
            if ($accountProfile->getAllImages()->isEmpty()) {
                $missingSteps[] = ['label' => __('front.account.completion.photos'), 'route' => 'account.photos'];
            }
            if (blank($accountProfile->local_prices)) {
                $missingSteps[] = ['label' => __('front.account.completion.prices'), 'route' => 'account.dashboard'];
            }
            if ($accountProfile->services()->count() === 0) {
                $missingSteps[] = ['label' => __('front.account.completion.services'), 'route' => 'account.services'];
            }
        }
    @endphp

    @if($accountProfile && $missingSteps !== [])
        {{-- Figma home-2 / home-3: pink bar 1134x50 at y 135, icon + text
             centred together, thin pink cross 20px from the right edge. When
             the page scrolls, home-3 pins it 10px under the top edge; the
             app shell has overflow set, so CSS sticky never engaged — the
             pin is done here with a placeholder that keeps the height. --}}
        <div x-data="{ show: true }" x-show="show" class="account-notice-sticky w-full md:w-[1134px] max-w-full md:mx-auto mb-[36px]">
        <div x-data="{ pinned: false, top: 0, h: 0,
                       measure() { if (!this.pinned) { const b = this.$el.getBoundingClientRect(); this.top = b.top + window.scrollY; this.h = b.height; } },
                       update() { const nav = document.querySelector('nav'); const navH = nav ? Math.max(0, nav.getBoundingClientRect().bottom) : 0; this.pinned = window.innerWidth >= 768 && window.scrollY + navH + 10 > this.top; } }"
             x-init="measure(); update()"
             @scroll.window.passive="update()" @resize.window="pinned = false; $nextTick(() => { measure(); update() })"
             class="account-notice-slot"
             :style="pinned ? `height:${h}px` : ''">
            <div class="account-notice relative mx-auto flex w-[310px] max-w-full min-h-[110px] items-start justify-start gap-[7px] pl-4 pr-10 pt-[14px] pb-3 md:items-center md:justify-center md:gap-[10px] md:w-full md:min-h-[50px] md:pl-10 md:py-3 bg-[#FFE0E5] rounded-[8px]"
                 :style="pinned ? `position:fixed;z-index:40;left:50%;transform:translateX(-50%);width:1134px;max-width:100%;top:${Math.max(0, document.querySelector('nav')?.getBoundingClientRect().bottom ?? 0) + 10}px` : ''">
                <img src="{{ asset('images/icons/OctagonAlert.svg') }}" class="w-[20px] h-[20px] shrink-0" alt="">
                {{-- phone 360 px-13: icon top-left, text left-aligned on 21px lines --}}
                <span class="min-w-0 text-left md:text-center font-medium text-[14px] leading-[21px] md:leading-5 text-[#505050]" style="font-family: 'Poppins', sans-serif;">
                    {{ __('front.account.completion.prompt') }}
                    @foreach($missingSteps as $step)
                        <a href="{{ route($step['route']) }}" class="underline">{{ $step['label'] }}</a>{{ ! $loop->last ? ', ' : '' }}
                    @endforeach
                </span>
                <button type="button"
                    @click="show = false"
                    aria-label="{{ __('front.notifications.archive') }}"
                    class="absolute right-[15px] top-[14px] md:top-1/2 md:-translate-y-1/2 flex w-[20px] h-[20px] items-center justify-center">
                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" aria-hidden="true"><path d="M1 1L9 9M9 1L1 9" stroke="#DD3888" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>
        </div>
        </div>
    @endif

    @if($accountProfile)
        @php
            // The public listing only shows profiles that are approved AND
            // public. A provider's own profile starts out pending and hidden,
            // so her edits were saved but she never saw them anywhere and
            // nothing said why. This states where the profile stands and links
            // to the owner preview (ProfileController::show allows it).
            $visibilityState = match (true) {
                $accountProfile->status === 'rejected' => 'rejected',
                $accountProfile->status !== 'approved' => 'pending',
                ! $accountProfile->is_public => 'hidden',
                default => 'live',
            };
            // No Figma frame for this bar, so it reuses the account notice
            // bars that are drawn: 1134 wide, radius 8, Poppins 14/500
            // #505050, icon + text centred, underlined link. Live = the light
            // green of the membership bar, anything that needs action = the
            // pink of the completion notice.
            $visibilityBg = $visibilityState === 'live' ? 'bg-[#E6FEE8]' : 'bg-[#FFE0E5]';
            $visibilityIcon = $visibilityState === 'live' ? 'CircleCheck' : 'OctagonAlert';
        @endphp
        {{-- A published profile is the normal state and the Figma account frames
             have no bar for it, so the bar only appears when something keeps the
             profile off the listing. --}}
        @if($visibilityState !== 'live')
        <div class="w-full md:w-[1134px] max-w-full md:mx-auto mb-[36px]" data-profile-visibility="{{ $visibilityState }}">
            <div class="mx-auto flex w-[310px] max-w-full min-h-[50px] items-start justify-start gap-[7px] rounded-[8px] pl-4 pr-4 pt-[14px] pb-3 md:items-center md:justify-center md:gap-[10px] md:w-full md:px-10 md:py-3 {{ $visibilityBg }}" role="status" style="font-family: 'Poppins', sans-serif;">
                <img src="{{ asset('images/icons/' . $visibilityIcon . '.svg') }}" class="w-[20px] h-[20px] shrink-0" alt="">
                <p class="min-w-0 text-left md:text-center font-medium text-[14px] leading-[21px] md:leading-5 text-[#505050]">
                    <span class="font-semibold">{{ __('front.account_visibility.title') }}: {{ __('front.profiles.form.' . ($visibilityState === 'live' || $visibilityState === 'hidden' ? 'approved' : $visibilityState)) }}</span>
                    &ndash; {{ __('front.account_visibility.' . $visibilityState) }}
                    @if($visibilityState !== 'live')
                        {{ __('front.account_visibility.preview_hint') }}
                    @endif
                    <a href="{{ route('profiles.show', $accountProfile->id) }}"
                       target="_blank" rel="noopener"
                       class="underline text-[#505050] whitespace-nowrap">{{ __('front.account_visibility.preview') }}</a>
                </p>
            </div>
        </div>
        @endif
    @endif

    <div class="flex flex-col md:flex-row md:justify-center md:gap-x-[79px]">
        <!-- Sidebar -->
        <x-account-sidebar :activeItem="$activeItem ?? 'dashboard'" />

        <!-- Main Content -->
        {{-- Below 1180px the 211 + 79 + 843 row does not fit; the column
             shrinks and the 843px-wide parts inside fall back to the column
             width instead of being cut off on the right. --}}
        <main class="w-full min-w-0 md:w-[843px] md:max-w-[calc(100%-290px)] min-[1180px]:max-w-none">

                @yield('account-content')

        </main>
    </div>
</div>
@endsection
