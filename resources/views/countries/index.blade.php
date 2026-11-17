@extends('layouts.app')

@section('title', __('front.countries.title'))

@section('content')
{{-- The search lands here. The design has no separate "browse by countries"
     page any more:
     - Czech site (home-1.jpg, home-21.jpg): the compact results state – the
       search card in a grey panel under the header, the chosen region as the
       list heading, then the same filters and grid as the homepage.
     - English site (vybraná země.jpg, vybrané město v zemi*.jpg): the route
       renders the English homepage itself, country / town picked in its
       sidebar (routes/web.php).
     The old hero ("Procházet podle zemí") with its counter pills over the
     subtitle is gone with it. --}}
@php
    $searchRegion = (string) request('region', '');
    $regionLabels = [
        'Praha' => 'Hlavní město Praha',
        'Vysočina' => 'Kraj Vysočina',
    ];
    $resultsTitle = $searchRegion !== ''
        ? ($regionLabels[$searchRegion] ?? str_replace(' Kraj', ' kraj', $searchRegion))
        : null;
@endphp

<div class="relative z-0 w-full min-h-screen countries-page">
    <x-hero-section :compact="true" />

    <div class="container mx-auto px-4 pt-10 md:pt-[78px] profiles-section-wrap countries-results">
        <livewire:profile-list :list-title="$resultsTitle" />
    </div>

    {{-- home-1.jpg: title ink at y 407, pills from 489 (1 px further down
         than on the homepage), cards from 610. --}}
    <style>
        /* phone 360 px-15: under the search card the phone list starts with
           its own "Nejlepší výsledky" heading (cap at y 568); the desktop
           region heading and the homepage's hero clearance are not shown. */
        @media (max-width: 767px) {
            .countries-results { padding-top: 0 !important; }
            .countries-results .pl-heading { display: none !important; }
            .countries-results .mobile-top-results { padding-top: 98px !important; }
        }
        @media (min-width: 768px) {
            .countries-results .pl-heading { padding-bottom: 48px !important; }
            /* home-21.jpg: footer wordmark 73 px under the empty panel. */
            body:has(.countries-results .filter-empty-state) .site-footer { padding-top: 74.5px !important; }
        }
    </style>
    {{-- Desktop only: phone 360 px-15 has a plain white page under the card. --}}
    <div class="-z-10 absolute top-[620px] left-0 right-0 -bottom-1 overflow-x-hidden hidden md:block" aria-hidden="true">
        <div class="radial-blur"></div>
        <div class="radial-blur-secondary radial-blur-right"></div>
        <div class="radial-blur-secondary "></div>
    </div>
</div>
@endsection
