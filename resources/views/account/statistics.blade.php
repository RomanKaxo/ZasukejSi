@extends('layouts.account')

@section('title', __('front.account.statistics.page_title'))

@php
    $activeItem = 'statistics';
@endphp

@section('account-content')
    @include('account.partials.page-header', ['title' => __('front.account.statistics.page_title'), 'ruleGap' => 'mb-[43px]'])

    <!-- Statistics Content (Figma home-18: second heading glyph 155px under the first month arrows) -->
    {{-- The gap under each chart depends on whether the month has a VIP
         row (home-18) or not (home-19), so the component sets it. --}}
    <div class="pb-6 md:pb-0">
        @livewire('profile-statistics', ['variant' => 'homepage'])
    </div>

    <div class="py-6 md:pt-0 md:pb-0">
        @livewire('profile-statistics', ['variant' => 'detail'])
    </div>
@endsection
