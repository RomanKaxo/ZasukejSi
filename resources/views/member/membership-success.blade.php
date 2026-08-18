@extends('layouts.member')

@section('title', __('front.membership.success_title'))

@php
    $activeItem = 'membership';
@endphp

@section('member-content')
    {{-- Čtyři různé konce, ne jeden.
         „Zaplaceno a běží" a „zaplaceno, aktivujeme" nejsou totéž a tvrdit to
         první, když platí to druhé, je lež. „Aktivováno bez platby" se říká
         nahlas, protože takové členství vzniklo bez peněz a nemá to být
         překvapení. --}}
    <div class="flex flex-col items-center justify-center py-16 text-center" style="font-family:'Poppins',sans-serif;">
        <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full"
             style="background: {{ $state === 'unverified' ? '#F2F2F2' : 'linear-gradient(90deg, #00B80F 0%, #4BD35A 100%)' }};">
            @if ($state === 'unverified')
                <svg class="h-10 w-10" style="color:#DD3888;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                </svg>
            @else
                <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            @endif
        </div>

        <h1 class="mb-2 text-2xl font-bold" style="color:#5C2D62;">
            @switch($state)
                @case('active')
                    {{ __('front.membership.success_title') }}
                    @break
                @case('granted')
                    {{ __('front.membership.granted_title') }}
                    @break
                @case('pending')
                    {{ __('front.membership.pending_title') }}
                    @break
                @default
                    {{ __('front.membership.unverified_title') }}
            @endswitch
        </h1>

        <p class="mb-2 max-w-md text-[15px] text-[#505050]">
            @switch($state)
                @case('active')
                    {{ __('front.membership.activated') }}
                    @break
                @case('granted')
                    {{ __('front.membership.activated_without_payment') }}
                    @break
                @case('pending')
                    {{ __('front.membership.activation_pending') }}
                    @break
                @default
                    {{ __('front.membership.not_verified') }}
            @endswitch
        </p>

        @if (!empty($membership?->ends_at))
            <p class="mb-8 text-[15px] font-semibold" style="color:#5C2D62;">
                {{ __('front.membership.valid_until', ['date' => $membership->ends_at->format('d.m.Y')]) }}
            </p>
        @else
            <div class="mb-8"></div>
        @endif

        <a href="{{ route('account.member.membership.index') }}" class="btn-gold">
            {{ __('front.membership.back_to_membership') }}
        </a>
    </div>
@endsection
