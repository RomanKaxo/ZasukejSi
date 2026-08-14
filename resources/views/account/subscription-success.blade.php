@extends('layouts.account')

@section('title', __('front.subscription.success_title'))

@php
    $activeItem = 'subscription';
@endphp

@section('account-content')
    {{-- Three distinct outcomes. The page used to claim success unconditionally,
         even for a URL typed by hand or a payment that never completed. --}}
    <div class="flex flex-col items-center justify-center py-16 text-center" style="font-family:'Poppins',sans-serif;">
        <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full"
             style="background: {{ $paid ? 'linear-gradient(90deg, #A07613 0%, #B9A143 100%)' : '#F2F2F2' }};">
            @if($paid)
                <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            @else
                <svg class="h-10 w-10" style="color:#DD3888;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                </svg>
            @endif
        </div>

        <h1 class="mb-2 text-2xl font-bold" style="color:#5C2D62;">
            @if($paid)
                {{ __('front.subscription.success_title') }}
            @else
                {{ __('front.subscription.unverified_title') }}
            @endif
        </h1>

        <p class="mb-8 max-w-md text-[15px] text-[#505050]">
            @if($paid && $subscription)
                {{ __('front.subscription.success_description') }}
            @elseif($paid)
                {{-- Paid, but the webhook that creates the subscription has not
                     been processed yet. Saying "active" here would be a lie. --}}
                {{ __('front.subscription.activation_pending') }}
            @else
                {{ __('front.subscription.unverified_description') }}
            @endif
        </p>

        <a href="{{ route('account.subscription.index') }}" class="btn-primary">
            {{ __('front.subscription.back_to_subscription') }}
        </a>
    </div>
@endsection
