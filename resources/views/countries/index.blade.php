@extends('layouts.app')

@section('title', __('front.countries.title'))

@section('content')
<!-- Hero Section -->
<div class="min-h-[620px] mx-auto rounded-b-3xl" style="background-image: url('/images/header.png'); background-size: cover; background-position: center;">
    <div class="container mx-auto px-4 pt-24 pb-8 flex flex-col min-h-[620px]">
        <div class="max-w-2xl pl-16 py-16">
            <h1 class="text-secondary leading-tight text-4xl md:text-6xl py-5">
                {{ __('front.countries.browse_by') }}
                <span class="text-primary-500">{{ __('front.countries.countries_text') }}.</span>
            </h1>

            <p class="text-xl text-gray-600 mb-8 max-w-sm">
                {{ __('front.countries.subtitle') }}
            </p>
        </div>
        
        <div class="mt-auto">
        {{-- User count pills --}}
        <div class="flex gap-3 mb-6 pl-16">
            <div class="inline-flex items-center px-4 py-2 bg-white backdrop-blur-sm text-gray-700 rounded-full text-sm font-medium shadow-lg">
                <span class="w-3 h-3 mr-2 bg-green-500 rounded-full"></span>
                {{ number_format($girlsCount) }}
                {!! preg_replace('/\s(\S+)$/', ' <span class="text-gray-400">$1</span>', e(__('front.landing.girls_registered'))) !!}
            </div>
            <div class="inline-flex items-center px-4 py-2 bg-white backdrop-blur-sm text-gray-700 rounded-full text-sm font-medium shadow-lg">
                <span class="w-3 h-3 mr-2 bg-green-500 rounded-full"></span>
                {{ number_format($gentsCount) }}
                {!! preg_replace('/\s(\S+)$/', ' <span class="text-gray-400">$1</span>', e(__('front.landing.gents_registered'))) !!}
            </div>
        </div>

    </div>
    </div>
</div>

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