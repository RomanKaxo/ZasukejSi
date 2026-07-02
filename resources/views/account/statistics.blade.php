@extends('layouts.account')

@section('title', __('front.account.statistics.page_title'))

@php
    $activeItem = 'statistics';
@endphp

@section('account-content')
    <!-- Warning Banner -->
    <div class="w-[1134px] max-w-full h-[50px] bg-[#FFE0E5] rounded-[8px] flex items-center justify-between px-4 mb-8">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/icons/OctagonAlert.svg') }}" class="w-[20px] h-[20px]" alt="Alert">
            <span class="font-poppins-medium text-[14px] text-[#505050]">
                Dokončete registraci Oprávněné aniž i odstoupil o <span class="underline">snadno osoby</span> vede grafikou osobami
            </span>
        </div>
        <button class="text-[#DD3888] font-bold">X</button>
    </div>

    <!-- Stats Cards -->
    <div class="flex gap-4 mb-8">
        @php
            $cards = [
                ['icon' => 'eye', 'value' => '10 458', 'label' => 'Celkové zobrazení profilu'],
                ['icon' => 'thumbsup', 'value' => '4.78/5', 'label' => 'Moje hodnocení'],
                ['icon' => 'MessageCircleMore', 'value' => '12', 'label' => 'moje recenze'],
            ];
        @endphp
        @foreach($cards as $card)
            <div class="w-[272px] h-[100px] rounded-[8px] flex flex-col items-center justify-center bg-gradient-to-b from-[#FFFFFF] to-[#E6E6E6]">
                <img src="{{ asset('images/icons/' . $card['icon'] . '.svg') }}" class="w-[28px] h-[28px] mb-1" alt="{{ $card['label'] }}">
                <span class="font-poppins-bold text-[24px] text-[#5C2D62]">{{ $card['value'] }}</span>
                <span class="font-poppins-400 text-[13px] text-[#505050]">{{ $card['label'] }}</span>
            </div>
        @endforeach
    </div>

    <div class="mb-4 md:mb-8">
        <h1 class="text-2xl md:text-4xl font-semibold text-secondary py-3 md:py-6 text-center">
            {{ __('front.account.statistics.page_title') }}
        </h1>
        <hr>
    </div>

    <!-- Statistics Content -->
    <div class="py-6">
        @livewire('profile-statistics')
    </div>
@endsection
