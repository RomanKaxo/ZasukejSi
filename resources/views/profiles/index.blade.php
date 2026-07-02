@extends('layouts.app')

@section('title', __('front.title'))

@section('content')
<style>
    .hero-bg {
        width: min(1331px, calc(100% - 24px));
        height: 602px;
        margin: 0 auto;
        border-bottom-left-radius: 24px;
        border-bottom-right-radius: 24px;
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
        overflow: visible;
        position: relative;
        z-index: 30;
    }

    .hero-bg::before {
        content: none;
    }

    .hero-bg .hero-inner {
        height: 100%;
        position: relative;
        z-index: 31;
    }

    .hero-search-wrap {
        margin-top: auto;
        padding-left: 0;
        padding-right: 0;
        transform: translateY(90px);
        position: relative;
        z-index: 60;
    }

    .profiles-section-wrap {
        position: relative;
        z-index: 1;
    }

    .hero-main-title {
        font-family: 'Poppins', sans-serif;
        font-weight: 800;
        font-size: 50px;
        line-height: 1.08;
        color: #5C2D62;
    }

    .hero-main-title .hero-main-highlight {
        color: #DD3888;
        white-space: nowrap;
    }

    .hero-main-title .hero-main-period {
        color: #5C2D62;
    }

    .hero-subtitle {
        font-family: 'Poppins', sans-serif;
        font-weight: 400;
        font-size: 26px;
        line-height: 1.3;
        color: #5C5C5C;
        max-width: 430px;
        margin-bottom: 0;
    }

    .hero-desktop-badges {
        display: flex;
        gap: 12px;
        margin-top: 12px;
        align-items: center;
    }

    /* Reuse search-badge styles so hero badges match the search component */
    .search-badge {
        width: 167px;
        height: 26px;
        border-radius: 999px;
        background: #F2F2F2;
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
            background: rgba(242, 242, 242, 0.8) !important;
            backdrop-filter: blur(4px);
        }
    }

    .search-badge:hover {
        transform: translateY(-2px);
        background: #FFF4F9;
        box-shadow: 0 10px 20px rgba(92, 45, 98, 0.08);
    }

    .search-badge-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #00B80F;
        flex: 0 0 10px;
    }

    .search-badge-strong {
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 11px;
        color: #505050;
        line-height: 1;
        white-space: nowrap;
    }

    .search-badge-soft {
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 11px;
        color: #A6A6A6;
        line-height: 1;
        white-space: nowrap;
    }

    @if (app()->getLocale() === 'en')
        /* On English homepage, badges should have white background */
        .search-badge {
            background: #FFFFFF;
        }

        @media (max-width: 425px) {
            .search-badge {
                background: #FFFFFF !important;
            }
        }
    @endif

    .hero-copy-block {
        transform: translateX(15px) translateY(45px);
    }

    .hero-text-block {
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: min(750px, 100%);
        min-height: 112px;
    }

    .hero-mobile-badges {
        display: none;
    }

    .homepage-hero {
        isolation: isolate;
        box-shadow: 0 32px 80px rgba(92, 45, 98, 0.14);
    }

    .homepage-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        border-bottom-left-radius: 24px;
        border-bottom-right-radius: 24px;
        background: linear-gradient(120deg, rgba(255,255,255,0.18), rgba(255,255,255,0) 42%);
        opacity: 0.75;
        pointer-events: none;
        animation: heroGlow 10s ease-in-out infinite alternate;
    }

    .hero-animate {
        opacity: 0;
        transform: translateY(32px) scale(0.98);
        animation: heroReveal 760ms cubic-bezier(.2,.9,.3,1) forwards;
    }

    .hero-animate-delay-1 {
        animation-delay: 120ms;
    }

    .hero-animate-delay-2 {
        animation-delay: 260ms;
    }

    .homepage-profiles-surface {
        opacity: 0;
        animation: sectionReveal 880ms cubic-bezier(.2,.9,.3,1) forwards;
        animation-delay: 320ms;
    }

    @keyframes heroReveal {
        from {
            opacity: 0;
            transform: translateY(32px) scale(0.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes sectionReveal {
        from {
            opacity: 0;
            transform: translateY(26px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes heroGlow {
        from {
            opacity: 0.45;
            transform: translateX(-2%) scale(1);
        }
        to {
            opacity: 0.92;
            transform: translateX(2%) scale(1.03);
        }
    }

    .hero-text-block h1, .hero-text-block p {
        margin: 0;
    }

    @media (max-width: 1024px) {
        .hero-bg {
            width: calc(100% - 12px);
            height: auto;
            min-height: 520px;
        }
    }

    @media (max-width: 640px) {
        .hero-bg {
            width: 100%;
            height: 463px;
            min-height: 463px;
            background-image: none !important;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
            overflow: visible !important;
        }

        .hero-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url('/images/icons/mobile/mobile.png');
            background-repeat: no-repeat;
            background-size: 488px 463px;
            background-position: center top;
            filter: blur(5px);
            clip-path: inset(0 round 0 0 24px 24px);
            z-index: 0;
            pointer-events: none;
        }

        .hero-main-title {
            font-size: 36px;
        }

        .hero-subtitle {
            font-size: 18px;
        }

        .hero-inner {
            display: flex !important;
            flex-direction: column !important;
        }

        .hero-copy-block {
            order: 1 !important;
            transform: none !important;
            padding-bottom: 0 !important;
        }

        .hero-text-block {
            width: 100%;
            min-height: auto;
        }

        .hero-search-wrap {
            order: 2 !important;
            transform: translateY(40px) !important;
            margin-top: 0 !important;
        }
    }

    @media (max-width: 425px) {
        .hero-mobile-badges {
            display: flex !important;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            width: 100%;
            max-width: 310px;
            margin: 24px auto 0;
        }
        .hero-desktop-badges {
            display: none !important;
        }
    }

    @media (max-width: 359px) {
        .hero-inner {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }

        .hero-copy-block {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .homepage-hero::after,
        .hero-animate,
        .homepage-profiles-surface {
            animation: none;
            opacity: 1;
            transform: none;
        }
    }

    @if (app()->getLocale() === 'en')
        @media (max-width: 640px) {
            body {
                background: #FFFFFF;
            }

            .profiles-section-wrap,
            .homepage-profiles-surface {
                background: #FFFFFF;
            }

            .homepage-mobile-blur {
                display: none;
            }

            .homepage-profiles-surface {
                margin-top: 40px;
            }
        }
    @endif
</style>
<!-- Hero Section  max-w-[1331px] -->
@php
    $isEnglishHomepage = app()->getLocale() === 'en';
@endphp

<div class="hero-bg homepage-hero" style="background-image: url('/images/header.png');">
    <div class="hero-inner container mx-auto px-4 pt-16 md:pt-24 pb-8 flex flex-col min-h-[420px] md:min-h-[520px]">
        <div class="max-w-2xl px-4 md:pl-16 py-10 md:py-16 hero-copy-block hero-animate hero-animate-delay-1">
            <div class="hero-text-block">
                <h1 class="hero-main-title py-4 md:py-5">
                    {!! __('front.landing.wearecommunity') !!}<br>
                    {!! __('front.landing.fucking_prefix') !!} <span class="hero-main-highlight">{!! __('front.landing.fucking_keyword') !!}</span><span class="hero-main-period">.</span>
                </h1>

                <p class="hero-subtitle">
                    {!! __('front.landing.girlsregisternow') !!}
                </p>

                <div class="hero-desktop-badges" aria-hidden="true">
                    <div class="search-badge">
                        <span class="search-badge-dot"></span>
                        <span class="search-badge-strong">1 420 {{ __('front.profiles.search.girls') }}</span>
                        <span class="search-badge-soft">{{ __('front.profiles.search.registered') }}</span>
                    </div>
                    <div class="search-badge">
                        <span class="search-badge-dot"></span>
                        <span class="search-badge-strong">382 {{ __('front.profiles.search.men') }}</span>
                        <span class="search-badge-soft">{{ __('front.profiles.search.registered') }}</span>
                    </div>
                </div>

                <div class="hero-mobile-badges" aria-hidden="true">
                    <div class="search-badge">
                        <span class="search-badge-dot"></span>
                        <span class="search-badge-strong">1 420 {{ __('front.profiles.search.girls') }}</span>
                        <span class="search-badge-soft">{{ __('front.profiles.search.registered') }}</span>
                    </div>
                    <div class="search-badge">
                        <span class="search-badge-dot"></span>
                        <span class="search-badge-strong">382 {{ __('front.profiles.search.men') }}</span>
                        <span class="search-badge-soft">{{ __('front.profiles.search.registered') }}</span>
                    </div>
                </div>
            </div>
        </div>

        @unless($isEnglishHomepage)
            <div class="hero-search-wrap hero-animate hero-animate-delay-2">
                <!-- Search Card -->
                <livewire:search-profiles />
            </div>
        @endunless
    </div>
</div>

<!-- Profiles Section -->

<div class="container mx-auto px-4 {{ $isEnglishHomepage ? 'pt-8 md:pt-10' : 'pt-10 md:pt-20' }} profiles-section-wrap homepage-profiles-surface">
    @if($isEnglishHomepage)
        <div class="lg:grid lg:grid-cols-[208px_minmax(0,1fr)] lg:gap-8 lg:items-start">
            <x-english-country-sidebar />
            <div class="min-w-0">
                <livewire:profile-list />
            </div>
        </div>
    @else
        <livewire:profile-list />
    @endif
</div>

<!-- Blog pages list gallery -->
<x-blog-listing :posts="$blogPosts" />


<div class="homepage-mobile-blur -z-10 absolute top-[620px] left-0 right-0 -bottom-1 overflow-x-hidden">
    <div class="radial-blur"></div>
    <div class="radial-blur-secondary radial-blur-right"></div>
    <div class="radial-blur-secondary "></div>
</div>

@endsection