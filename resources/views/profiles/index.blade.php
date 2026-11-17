@extends('layouts.app')

@section('title', __('front.title'))

@section('content')
@php
    $isEnglishHomepage = app()->getLocale() === 'en';
@endphp
<style>
    .search-badge {
        width: 167px;
        height: 26px;
        border-radius: 999px;
        background: {{ $isEnglishHomepage ? '#FFFFFF' : '#F2F2F2' }} !important;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        box-sizing: border-box;
        padding: 0 10px;
        transition: transform 220ms ease, box-shadow 220ms ease, background-color 220ms ease;
    }

    @media (max-width: 425px) {
        .search-badge {
            width: 310px !important;
            height: 35px !important;
            background: {{ $isEnglishHomepage ? '#FFFFFF' : '#F2F2F2' }} !important;
            backdrop-filter: blur(4px);
        }
    }

    .homepage-mobile-blur {
        display: none;
    }

    @if ($isEnglishHomepage)
    /* English homepage (Figma home-24.jpg / vybraná země.jpg), desktop: a
       477 px hero without the search card, the title on two lines, the two
       counter pills under the subtitle from x 444; below it the 208 px
       country column, 23 px gutter and a 903 px list (4 × 210 cards, 21 px apart). */
    @media (min-width: 1024px) {
        .homepage-hero { height: 477px !important; }
        .homepage-hero .hero-copy-block { max-width: none; }
        .homepage-hero .hero-text-block { width: 760px; }
        /* Text block 18 px higher than the Czech hero; 26/34 subtitle. */
        /* home-24: title cap at y 171 on 60 px lines, subtitle ink from 300. */
        .homepage-hero .hero-copy-block { top: -15px; }
        .homepage-hero .hero-main-title { line-height: 60px; }
        .homepage-hero .hero-subtitle { font-size: 26px; line-height: 34px; margin-top: -11px; }
        .homepage-hero { background-size: auto 488px !important; background-position: calc(50% + 5px) 45% !important; }
        .homepage-hero .hero-desktop-badges { left: 124px !important; top: 398px; bottom: auto !important; gap: 8px !important; }
        /* home-24: the counter pills are 30 px tall (y 398–427). */
        .homepage-hero .hero-desktop-badges > .search-badge { height: 30px; }
    }
    /* phone 360 px-18: four title lines ("We are a / community of /
       people who / enjoy sex."), cap top at y 135; the two counter pills
       sit under the subtitle, 310 × 35, 25 px gutter, 5 px apart, from y 398. */
    @media (max-width: 767px) {
        .homepage-hero .hero-main-title { max-width: 270px; margin-top: 9px; }
        .homepage-hero .hero-subtitle { margin-top: 7px; }
        .en-mobile-badges { display: flex; flex-direction: column; gap: 5px; width: 310px; max-width: calc(100% - 18px); margin: 26px 0 0 9px; order: 2; position: relative; z-index: 2; }
        .en-mobile-badges .en-mobile-badge { height: 35px; border-radius: 999px; background: #F2F2F2; display: flex; align-items: center; justify-content: center; font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-size: 13px; line-height: 1; white-space: nowrap; }
        .en-mobile-badge__dot { width: 10px; height: 10px; border-radius: 50%; background: #00B80F; margin-right: 6px; flex: 0 0 10px; }
        .en-mobile-badge__strong { font-weight: 500; color: #505050; }
        .en-mobile-badge__soft { font-weight: 400; color: #A6A6A6; margin-left: 4px; }
    }
    @media (min-width: 768px) { .en-mobile-badges { display: none; } }
    @media (min-width: 1280px) {
        .en-home-grid { padding-left: 57px; padding-right: 57px; grid-template-columns: 208px minmax(0, 1fr) !important; column-gap: 23px !important; }
        .en-home-grid .profile-list-cards-grid { column-gap: 21px !important; }
        .en-home-grid .pl-pad { padding-left: 0 !important; padding-right: 0 !important; }
        .en-home-grid .pl-filters { margin-bottom: 38px !important; }
        /* home-24 / vybraná země: the "Girls in Europe" box from y 522, 1 px above the pills. */
        .en-home-grid aside > div > div:first-child { margin-top: -1px; margin-bottom: 25px; }
        /* vybraná země / home-24: the advert stays inside the 903 px list column. */
        .en-home-grid .pl-pad--grid .pl-advert { margin-left: 0 !important; margin-right: 0 !important; }
    }
    @endif
    
    /* Fill `backwards`, not `forwards`: a transform kept after the reveal
       (even translateY(0)) makes this section the containing block of the
       fixed full-screen country / town pickers (phone 360 px-21…23), which
       then opened mid-page instead of covering the screen. */
    .homepage-profiles-surface {
        animation: sectionReveal 880ms cubic-bezier(.2,.9,.3,1) backwards;
        animation-delay: 320ms;
    }

    @keyframes sectionReveal {
        from { opacity: 0; transform: translateY(26px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 640px) {
        @if (app()->getLocale() === 'en')
            body { background: #FFFFFF; }
            .profiles-section-wrap, .homepage-profiles-surface { background: #FFFFFF; }
            .homepage-profiles-surface { margin-top: 40px; }
        @endif
    }
</style>

<div class="relative z-0 w-full min-h-screen">
    <!-- Random Background Bubbles on Edges (Desktop only) -->
    <div class="absolute inset-0 overflow-hidden hidden md:block pointer-events-none" style="z-index: -1;" aria-hidden="true">
        @for ($i = 0; $i < 15; $i++)
            @php
                $size = rand(50, 180);
                $top = rand(0, 100);
                $side = ($i % 2 === 0) ? 'left' : 'right';
                $offset = rand(-5, 5);
                $color = (rand(0, 1) === 0) ? '#DD3888' : '#5C2D62';
            @endphp
            <div style="
                position: absolute;
                top: {{ $top }}%;
                {{ $side }}: {{ $offset }}%;
                width: {{ $size }}px;
                height: {{ $size }}px;
                background-color: {{ $color }};
                filter: blur(50px);
                border-radius: 50%;
                opacity: 1;
            "></div>
        @endfor
    </div>

    @php
        $heroTitle = __('front.landing.wearecommunity') . '<br>' . __('front.landing.fucking_prefix') . ' <span class="hero-main-highlight">' . __('front.landing.fucking_keyword') . '</span><span class="hero-main-period">.</span>';
    @endphp

    <x-hero-section 
        :title="$heroTitle"
        :subtitle="__('front.landing.girlsregisternow')"
        :showSearch="!$isEnglishHomepage"
    >
        @if($isEnglishHomepage)
            <div class="en-mobile-badges" aria-hidden="true">
                <div class="en-mobile-badge"><span class="en-mobile-badge__dot"></span><span class="en-mobile-badge__strong">{{ number_format($girlsCount, 0, ',', ' ') }} {{ __('front.profiles.search.girls') }}</span><span class="en-mobile-badge__soft">{{ __('front.profiles.search.registered') }}</span></div>
                <div class="en-mobile-badge"><span class="en-mobile-badge__dot"></span><span class="en-mobile-badge__strong">{{ number_format($gentsCount, 0, ',', ' ') }} {{ __('front.profiles.search.men') }}</span><span class="en-mobile-badge__soft">{{ __('front.profiles.search.registered') }}</span></div>
            </div>
            <div class="hero-desktop-badges absolute bottom-8 left-16 hidden md:flex gap-4" aria-hidden="true">
                <div class="search-badge" style="flex-wrap: nowrap;">
                    <span style="width:10px; height:10px; border-radius:50%; background:#00B80F; margin-right:6px; flex-shrink:0; display:inline-block;"></span>
                    <span style="font-family:'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight:600; font-size:11px; color:#505050; line-height:1; white-space:nowrap;">{{ number_format($girlsCount, 0, ',', ' ') }} {{ __('front.profiles.search.girls_desktop') }}</span>
                    <span style="font-family:'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight:600; font-size:11px; color:#A6A6A6; line-height:1; white-space:nowrap;">{{ __('front.profiles.search.registered') }}</span>
                </div>
                <div class="search-badge" style="flex-wrap: nowrap;">
                    <span style="width:10px; height:10px; border-radius:50%; background:#00B80F; margin-right:6px; flex-shrink:0; display:inline-block;"></span>
                    <span style="font-family:'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight:600; font-size:11px; color:#505050; line-height:1; white-space:nowrap;">{{ number_format($gentsCount, 0, ',', ' ') }} {{ __('front.profiles.search.men') }}</span>
                    <span style="font-family:'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight:600; font-size:11px; color:#A6A6A6; line-height:1; white-space:nowrap;">{{ __('front.profiles.search.registered') }}</span>
                </div>
            </div>
        @endif
    </x-hero-section>

    <!-- Profiles Section -->
    <div class="container mx-auto px-4 {{ $isEnglishHomepage ? 'pt-8 md:pt-[46px]' : 'pt-10 md:pt-20' }} profiles-section-wrap homepage-profiles-surface">
        @if($isEnglishHomepage)
            <div class="lg:grid lg:grid-cols-[208px_minmax(0,1fr)] lg:gap-8 lg:items-start en-home-grid">
                <x-english-country-sidebar />
                <div class="min-w-0">
                    <livewire:profile-list heading-tag="h2" />
                </div>
            </div>
        @else
            <livewire:profile-list heading-tag="h2" />
        @endif
    </div>
</div>
@endsection
