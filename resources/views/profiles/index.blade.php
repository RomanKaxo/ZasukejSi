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
    
    .homepage-profiles-surface {
        opacity: 0;
        animation: sectionReveal 880ms cubic-bezier(.2,.9,.3,1) forwards;
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

@php
    $heroTitle = __('front.landing.wearecommunity') . '<br>' . __('front.landing.fucking_prefix') . ' <span class="hero-main-highlight">' . __('front.landing.fucking_keyword') . '</span><span class="hero-main-period">.</span>';
@endphp

<x-hero-section 
    :title="$heroTitle"
    :subtitle="__('front.landing.girlsregisternow')"
    :showSearch="!$isEnglishHomepage"
>
    @if($isEnglishHomepage)
        <div class="hero-desktop-badges absolute bottom-8 left-16 hidden md:flex gap-4" aria-hidden="true">
            <div class="search-badge">
                <span class="w-2.5 h-2.5 rounded-full bg-green-500 mr-1.5"></span>
                <span class="font-semibold text-xs text-gray-700">1 420 {{ __('front.profiles.search.girls') }}</span>
            </div>
            <div class="search-badge">
                <span class="w-2.5 h-2.5 rounded-full bg-green-500 mr-1.5"></span>
                <span class="font-semibold text-xs text-gray-700">382 {{ __('front.profiles.search.men') }}</span>
            </div>
        </div>
    @endif
</x-hero-section>

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
@endsection
