@extends('layouts.app')

@section('title', __('front.countries.title'))

@section('content')

@php
    $heroTitle = __('front.countries.browse_by')
        . ' <span class="hero-main-highlight">'
        . __('front.countries.countries_text')
        . '</span><span class="hero-main-period">.</span>';
@endphp

<x-hero-section 
    :title="$heroTitle"
    :subtitle="__('front.countries.subtitle')"
    :showSearch="true"
>
    {{-- User count pills --}}
    <div class="hero-desktop-badges absolute bottom-8 left-16 hidden md:flex gap-4" aria-hidden="true">
        <div class="search-badge">
            <span class="w-2.5 h-2.5 rounded-full bg-green-500 mr-1.5"></span>
            <span class="font-semibold text-xs text-gray-700">
                {{ number_format($girlsCount) }} {{ __('front.landing.girls_registered') }}
            </span>
        </div>
        <div class="search-badge">
            <span class="w-2.5 h-2.5 rounded-full bg-green-500 mr-1.5"></span>
            <span class="font-semibold text-xs text-gray-700">
                {{ number_format($gentsCount) }} {{ __('front.landing.gents_registered') }}
            </span>
        </div>
    </div>
</x-hero-section>

<!-- Countries and Profiles Section -->
<div class="container mx-auto px-4 pt-20">
    <livewire:country-profiles />
</div>

<div class="-z-10 absolute top-[620px] left-0 right-0 -bottom-1 overflow-x-hidden">
    <div class="radial-blur"></div>
    <div class="radial-blur-secondary radial-blur-right"></div>
    <div class="radial-blur-secondary "></div>
</div>

@endsection
