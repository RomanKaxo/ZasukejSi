@extends('layouts.app')

@section('content')
<!-- Add top padding to account for fixed navbar -->
<div class="container mx-auto pt-24 md:pt-38 min-h-screen px-4 md:px-0">
    <div x-data="{ show: true }" x-show="show" class="sticky top-14 md:top-20 z-40 w-full md:w-[1134px] md:mx-auto mb-8">
        <div class="relative mx-auto w-[310px] h-[110px] px-4 py-3 md:w-full md:h-[50px] md:mx-0 md:px-4 md:py-0 bg-[#FFE0E5] rounded-[8px] md:flex md:items-center md:justify-center">
            <img src="{{ asset('images/icons/OctagonAlert.svg') }}" class="absolute top-3 left-3 md:static md:mr-3 w-[20px] h-[20px]" alt="Alert">
            <button
                @click="show = false"
                class="absolute top-3 right-3 md:right-4 md:top-1/2 md:-translate-y-1/2 text-[#DD3888] font-bold">X</button>
            <span class="block pl-8 pt-1 md:pl-0 md:pt-0 font-medium text-[14px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">
                Dokončete registraci Oprávněné aniž i odstoupil o <span class="underline">snadno osoby</span> vede grafikou osobami
            </span>
        </div>
    </div>

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