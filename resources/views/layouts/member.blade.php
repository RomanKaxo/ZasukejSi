@extends('layouts.app')

@section('content')
@php
    // Figma (home-5, muž - záložky*): the member area is one 1134 px block at
    // 1920 — sidebar 210 px on the left, content column flush right. The
    // content column is 843 px for ratings/settings and 902 px for the card
    // grids (four 210 px cards). Pages opt into the wide column with
    // @section('member-width', 'wide').
    $memberWide = trim($__env->yieldContent('member-width')) === 'wide';
    $memberColumn = $memberWide ? 'lg:max-w-[902px]' : 'lg:max-w-[843px]';
    $hasMemberHeading = $__env->hasSection('member-heading');
@endphp
{{-- Mobile: a 310 px column like every 360 px frame; the fixed header is
     ~70 px tall there, so content starts below it. --}}
<div class="member-shell mx-auto w-[310px] max-w-[calc(100%-32px)] pt-24 min-h-screen md:w-auto md:max-w-none md:px-6 md:pt-[140px] xl:w-[1134px] xl:px-0">
    {{-- Row 1: banner + page heading sit in the content column only. Row 2:
         sidebar and content start together at the divider, which is what
         every desktop frame shows (sidebar top = divider line), so no
         per-page hand-calibrated offsets are needed any more. --}}
    <div class="md:grid md:grid-cols-[210px_minmax(0,1fr)] md:gap-x-6 xl:gap-x-0">
        <div class="w-full md:col-start-2 md:row-start-1 md:justify-self-end {{ $memberColumn }}">
            <x-premium-banner />

            @if($hasMemberHeading)
                <div class="member-heading text-center">
                    @yield('member-heading')
                </div>
            @endif
        </div>

        <div class="hidden md:block md:col-start-1 md:row-start-2">
            <x-member-sidebar :activeItem="$activeItem ?? 'dashboard'" />
        </div>

        <main class="w-full min-w-0 md:col-start-2 md:row-start-2 md:justify-self-end {{ $memberColumn }}">
            @if($hasMemberHeading)
                <hr class="member-divider">
            @endif

            @yield('member-content')
        </main>
    </div>
</div>
<x-reported-case-modal />
<style>
    /* Figma divider: 1 px #E6E6E6 across the content column, square ends. */
    .member-shell hr.member-divider { border: 0; border-top: 1px solid #E6E6E6; border-radius: 0; margin: 0; }
    .member-heading h1 { font-family: 'Poppins', sans-serif; font-weight: 700; color: #5C2D62; }

    /* Heading block: values measured on phone 360 px-1 and home-5 /
       muž - záložky* (1920). */
    .member-heading { padding-top: 43px; padding-bottom: 36px; }
    .member-title { font-size: 36px; line-height: 43px; margin: 0; }
    .member-lead { font-family: 'Poppins', sans-serif; font-weight: 400; font-size: 14px; line-height: 25px; color: #505050; max-width: 840px; margin: 35px auto 6px; }
    .member-subtitle { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 24px; line-height: 30px; color: #5C2D62; text-align: center; margin: 36px 0 35px; }
    @media (min-width: 768px) { .member-subtitle { margin-top: 58px; margin-bottom: 42px; } }

    /* Card grids (favorites, archive, girls of the month). Phone: two columns
       of the 145 px listing card 20 px apart, as in phone 360 px-11 (two
       fixed 155 px columns touched at 360 px and ran 22 px off a 320 px
       screen); they shrink on narrower phones. Desktop: four cards flush with both edges of
       the 902 px column, like the frames. */
    .member-card-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 145px)); column-gap: 20px; justify-content: space-between; justify-items: center; row-gap: 24px; margin-top: 32px; }
    .member-empty { margin-top: 32px; }
    .member-empty-icon { display: block; width: 48px; height: 48px; margin: 0 auto 16px; color: #DD3888; }
    .member-empty-icon svg { width: 100%; height: 100%; }

    @media (min-width: 768px) {
        .member-heading { padding-top: 40px; padding-bottom: 36px; }
        .member-card-grid { grid-template-columns: repeat(2, auto); justify-content: space-around; row-gap: 26px; }
    }
    @media (min-width: 1024px) {
        .member-card-grid { grid-template-columns: repeat(3, auto); justify-content: space-between; }
    }
    @media (min-width: 1280px) {
        .member-card-grid { grid-template-columns: repeat(4, auto); margin-top: 65px; }
        .member-card-grid--top { margin-top: 60px; }
    }
</style>
@endsection

