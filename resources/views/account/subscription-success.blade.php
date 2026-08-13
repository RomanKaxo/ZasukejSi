@extends('layouts.account')

@section('title', __('front.subscription.success_title'))

@php
    $activeItem = 'subscription';
@endphp

@section('account-content')
    <div class="flex flex-col items-center justify-center py-16 text-center" style="font-family:'Poppins',sans-serif;">
        <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full" style="background: linear-gradient(90deg, #A07613 0%, #B9A143 100%);">
            <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="mb-2 text-2xl font-bold" style="color:#5C2D62;">
            {{ __('front.subscription.success_title') }}
        </h1>

        <p class="mb-8 max-w-md text-[15px] text-[#505050]">
            {{ __('front.subscription.success_description') }}
        </p>

        <a href="{{ route('account.subscription.index') }}" class="btn-primary">
            {{ __('front.subscription.back_to_subscription') }}
        </a>
    </div>
@endsection
