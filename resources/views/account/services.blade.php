@extends('layouts.account')

@section('title', __('front.account.services.page_title'))

@php
    $activeItem = 'services';
@endphp

@section('account-content')
    <!-- Mobile Header -->
    <div class="mb-4 md:hidden">
        <div class="relative flex items-center justify-center py-3">
            <h1 class="w-[400px] max-w-full text-[38px] text-center" style="font-family:'Poppins',sans-serif; font-weight:700; color:#5C2D62;">
                {{ __('front.account.services.page_title') }}
            </h1>
        </div>
        <hr class="w-[312px] mx-auto mb-4">
        <div class="flex items-center justify-between w-[312px] mx-auto">
            <div class="flex items-center gap-1.5">
                <div class="w-[14px] h-[14px] rounded-full {{ auth()->user()->isOnline() ? 'bg-[#00B80F]' : 'bg-gray-300' }}"></div>
                <span class="text-[13px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">
                    {{ auth()->user()->isOnline() ? __('front.account.dashboard.online') : __('front.account.dashboard.offline') }}
                </span>
            </div>
            <a href="{{ route('account.dashboard') }}" class="text-[13px] text-[#505050] underline" style="font-family: 'Poppins', sans-serif; color: #DD3888;">{{ __('front.account.dashboard.settings') }}</a>
        </div>
    </div>

    <!-- Desktop Header -->
    <div class="hidden md:block mb-8">
        <div class="relative flex items-center justify-center py-6">
            <h1 class="w-[400px] max-w-full text-4xl text-center" style="font-family:'Poppins',sans-serif; font-weight:700; color:#5C2D62;">
                {{ __('front.account.services.page_title') }}
            </h1>
            <div class="flex flex-col items-end gap-0.5 absolute right-0 top-1/2 -translate-y-1/2">
                <div class="flex items-center gap-1.5">
                    <div class="w-[14px] h-[14px] rounded-full {{ auth()->user()->isOnline() ? 'bg-[#00B80F]' : 'bg-gray-300' }}"></div>
                    <span class="text-[13px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">
                        {{ auth()->user()->isOnline() ? __('front.account.dashboard.online') : __('front.account.dashboard.offline') }}
                    </span>
                </div>
                <a href="{{ route('account.dashboard') }}" class="text-[13px] text-[#505050] underline" style="font-family: 'Poppins', sans-serif; color: #DD3888;">{{ __('front.account.dashboard.settings') }}</a>
            </div>
        </div>
        <hr class="mb-[77px]">
    </div>

    @livewire('services-manager')
@endsection
