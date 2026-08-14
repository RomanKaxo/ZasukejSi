@extends('layouts.app')

@section('content')
<!-- Add top padding to account for fixed navbar -->
<div class="container mx-auto pt-24 md:pt-38 min-h-screen px-4 md:px-0">
    @php
        // The banner used to carry a fixed line of filler text and showed on
        // every account page regardless of state. It now reports what is
        // actually missing, and disappears once the profile is complete.
        $accountProfile = auth()->user()?->profile;

        $missingSteps = [];
        if ($accountProfile) {
            if (blank($accountProfile->about)) {
                $missingSteps[] = ['label' => __('front.account.completion.about'), 'route' => 'account.dashboard'];
            }
            if ($accountProfile->getAllImages()->isEmpty()) {
                $missingSteps[] = ['label' => __('front.account.completion.photos'), 'route' => 'account.photos'];
            }
            if (blank($accountProfile->local_prices)) {
                $missingSteps[] = ['label' => __('front.account.completion.prices'), 'route' => 'account.dashboard'];
            }
            if ($accountProfile->services()->count() === 0) {
                $missingSteps[] = ['label' => __('front.account.completion.services'), 'route' => 'account.services'];
            }
        }
    @endphp

    @if($accountProfile && $missingSteps !== [])
        <div x-data="{ show: true }" x-show="show" class="sticky top-14 md:top-20 z-40 w-full md:w-[1134px] md:mx-auto mb-8">
            <div class="relative mx-auto w-[310px] min-h-[110px] px-4 py-3 md:w-full md:min-h-[50px] md:mx-0 md:px-4 md:py-0 bg-[#FFE0E5] rounded-[8px] md:flex md:items-center md:justify-center">
                <img src="{{ asset('images/icons/OctagonAlert.svg') }}" class="absolute top-3 left-3 md:static md:mr-3 w-[20px] h-[20px]" alt="">
                <button
                    @click="show = false"
                    aria-label="{{ __('front.notifications.archive') }}"
                    class="absolute top-3 right-3 md:right-4 md:top-1/2 md:-translate-y-1/2 text-[#DD3888] font-bold">X</button>
                <span class="block pl-8 pt-1 md:pl-0 md:pt-0 md:py-3 font-medium text-[14px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">
                    {{ __('front.account.completion.prompt') }}
                    @foreach($missingSteps as $step)
                        <a href="{{ route($step['route']) }}" class="underline">{{ $step['label'] }}</a>{{ ! $loop->last ? ', ' : '' }}
                    @endforeach
                </span>
            </div>
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:justify-center md:gap-x-[80px]">
        <!-- Sidebar -->
        <x-account-sidebar :activeItem="$activeItem ?? 'dashboard'" />

        <!-- Main Content -->
        <main class="w-full md:w-[843px]">

                @yield('account-content')

        </main>
    </div>
</div>
@endsection