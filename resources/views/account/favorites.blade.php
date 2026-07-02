<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moje favoritky</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-white" style="overflow-y: auto; height: auto;">

<div id="app" style="min-height: 100%; overflow-y: auto;">
    <x-navbar />

    <main>
        <div class="container mx-auto pt-32 max-[426px]:pt-12 px-4 max-[426px]:px-0">
            <!-- Outer layout: sidebar + content -->
            <div class="flex justify-end mb-8 gap-x-12 w-[1134px] mx-auto max-[426px]:w-[310px] max-[426px]:justify-center">

                <!-- Member Sidebar -->
                <div class="max-[426px]:hidden mt-[38px]">
                    <div class="flex flex-col w-[211px] gap-[10px]">
                        <div class="flex flex-col w-[211px] gap-[10px]">
                            <!-- Hodnocení dívek -->
                            <a href="{{ route('preview.ratings') }}"
                               class="w-[210px] h-[50px] rounded-[8px] border border-[#E6E6E6] text-[#505050] flex items-center px-4 gap-3 font-medium text-[14px]"
                               style="font-family: 'Poppins', sans-serif;">
                                <img src="{{ asset('images/icons/star.svg') }}" class="w-[20px] h-[20px]" alt="Star"
                                     style="filter: invert(36%) sepia(87%) saturate(2222%) hue-rotate(309deg) brightness(90%) contrast(92%);">
                                Hodnocení dívek
                            </a>
                            <!-- Moje Favoritky – ACTIVE -->
                            <a href="{{ route('account.favorites') }}"
                               class="w-[210px] h-[50px] rounded-[8px] bg-[#DD3888] text-white flex items-center px-4 gap-3 font-medium text-[14px]"
                               style="font-family: 'Poppins', sans-serif;">
                                <img src="{{ asset('images/icons/heart.svg') }}" class="w-[20px] h-[20px]" alt="Heart"
                                     style="filter: brightness(0) invert(1);">
                                Moje Favoritky
                            </a>
                            <!-- Dívky měsíc -->
                            <a href="#"
                               class="w-[210px] h-[50px] rounded-[8px] border border-[#E6E6E6] text-[#505050] flex items-center px-4 gap-3 font-medium text-[14px]"
                               style="font-family: 'Poppins', sans-serif;">
                                <img src="{{ asset('images/icons/calendar.svg') }}" class="w-[20px] h-[20px]" alt="Calendar"
                                     style="filter: invert(36%) sepia(87%) saturate(2222%) hue-rotate(309deg) brightness(90%) contrast(92%);">
                                Dívky měsíc
                            </a>
                            <!-- Archiv dívek -->
                            <a href="#"
                               class="w-[210px] h-[50px] rounded-[8px] border border-[#E6E6E6] text-[#505050] flex items-center px-4 gap-3 font-medium text-[14px]"
                               style="font-family: 'Poppins', sans-serif;">
                                <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px]" alt="Archive"
                                     style="filter: invert(36%) sepia(87%) saturate(2222%) hue-rotate(309deg) brightness(90%) contrast(92%);">
                                Archiv dívek
                            </a>
                            <!-- Nahlášené dívky -->
                            <a href="#"
                               class="w-[210px] h-[50px] rounded-[8px] border border-[#E6E6E6] text-[#505050] flex items-center px-4 gap-3 font-medium text-[14px]"
                               style="font-family: 'Poppins', sans-serif;">
                                <img src="{{ asset('images/icons/OctagonAlert.svg') }}" class="w-[20px] h-[20px]" alt="Report"
                                     style="filter: invert(36%) sepia(87%) saturate(2222%) hue-rotate(309deg) brightness(90%) contrast(92%);">
                                Nahlášené dívky
                            </a>
                            <!-- Základní nastavení -->
                            <a href="{{ route('preview.dashboard') }}"
                               class="w-[210px] h-[50px] rounded-[8px] border border-[#E6E6E6] text-[#505050] flex items-center px-4 gap-3 font-medium text-[14px]"
                               style="font-family: 'Poppins', sans-serif;">
                                <img src="{{ asset('images/icons/User.svg') }}" class="w-[20px] h-[20px]" alt="Settings"
                                     style="filter: invert(36%) sepia(87%) saturate(2222%) hue-rotate(309deg) brightness(90%) contrast(92%);">
                                Základní nastavení
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Main Content (843px) -->
                <div class="w-[843px] max-[426px]:w-[310px]">

                    <!-- Status Bar -->
                    <div x-data="{ show: true }" x-show="show"
                         class="w-[843px] max-[426px]:w-[309px] h-[50px] max-[426px]:h-[91px] rounded-[8px] flex items-center max-[426px]:items-center justify-center max-[426px]:justify-between px-4 max-[426px]:px-3 gap-3 relative mb-6 max-[426px]:mb-4 max-[426px]:mt-20"
                         style="background-color: #E6FEE8;">
                        <!-- Desktop layout -->
                        <div class="flex items-center gap-2 max-[426px]:hidden">
                            <img src="{{ asset('images/icons/diamond.svg') }}" class="w-[20px] h-[20px] flex-shrink-0" alt="Diamond">
                            <p class="text-[13px] text-[#505050] font-medium leading-tight"
                               style="font-family: 'Poppins', sans-serif;">
                                Máte aktivní členství Premium už jen 5 dní – <span class="underline">prodloužení členství zde</span>
                            </p>
                        </div>
                        
                        <!-- Mobile layout: Diamond | Text | X -->
                        <img src="{{ asset('images/icons/diamond.svg') }}" class="hidden max-[426px]:block w-[20px] h-[20px] flex-shrink-0 max-[426px]:self-start max-[426px]:mt-3" alt="Diamond">
                        
                        <p class="hidden max-[426px]:block text-[13px] text-[#505050] font-medium leading-tight text-left max-[426px]:w-[223px] max-[426px]:h-[59px] max-[426px]:self-center"
                           style="font-family: 'Poppins', sans-serif;">
                            Máte aktivní členství Premium už jen 5 dní – <span class="underline">prodloužení členství zde</span>
                        </p>
                        
                        <!-- Close button -->
                        <button @click="show = false"
                                class="flex items-center justify-center flex-shrink-0 absolute right-4 top-1/2 -translate-y-1/2 max-[426px]:static max-[426px]:translate-y-0 max-[426px]:self-start max-[426px]:mt-3 status-bar-close">
                            <img src="{{ asset('images/icons/cross.svg') }}" class="w-[16px] h-[16px]" alt="Close">
                        </button>
                    </div>

                    <!-- Page Title -->
                    <h1 class="text-[24px] font-bold text-[#5C2D62] mb-6" style="font-family: 'Poppins', sans-serif;">
                        Moje favoritky
                    </h1>

                    <!-- Profile Cards Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 profile-list-cards-grid">
                        @php
                            // Mock data for testing - replace with actual favorites from database
                            $mockProfiles = collect([
                                (object)['id' => 1, 'display_name' => 'Tamara', 'city' => 'Mariánka'],
                                (object)['id' => 2, 'display_name' => 'Jana', 'city' => 'Mariánka'],
                                (object)['id' => 3, 'display_name' => 'Angela', 'city' => 'Hodonínová'],
                                (object)['id' => 4, 'display_name' => 'Lucie', 'city' => 'Hodonínová'],
                                (object)['id' => 5, 'display_name' => 'Tamara', 'city' => 'Mariánka'],
                                (object)['id' => 6, 'display_name' => 'Jana', 'city' => 'Mariánka'],
                                (object)['id' => 7, 'display_name' => 'Angela', 'city' => 'Hodonínová'],
                                (object)['id' => 8, 'display_name' => 'Lucie', 'city' => 'Hodonínová'],
                                (object)['id' => 9, 'display_name' => 'Tamara', 'city' => 'Mariánka'],
                                (object)['id' => 10, 'display_name' => 'Jana', 'city' => 'Mariánka'],
                                (object)['id' => 11, 'display_name' => 'Angela', 'city' => 'Hodonínová'],
                                (object)['id' => 12, 'display_name' => 'Lucie', 'city' => 'Hodonínová'],
                                (object)['id' => 13, 'display_name' => 'Tamara', 'city' => 'Mariánka'],
                                (object)['id' => 14, 'display_name' => 'Jana', 'city' => 'Mariánka'],
                                (object)['id' => 15, 'display_name' => 'Angela', 'city' => 'Hodonínová'],
                                (object)['id' => 16, 'display_name' => 'Lucie', 'city' => 'Hodonínová'],
                            ]);
                        @endphp

                        @foreach($mockProfiles as $profile)
                            <x-profile-card :profile="$profile" />
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="flex justify-center items-center gap-2 mt-8 mb-12">
                        <button class="w-[32px] h-[32px] rounded flex items-center justify-center text-[#DD3888] hover:bg-[#FFF4F9]">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        
                        <button class="w-[32px] h-[32px] rounded flex items-center justify-center bg-[#DD3888] text-white font-semibold text-[14px]" style="font-family: 'Poppins', sans-serif;">
                            1
                        </button>
                        
                        <button class="w-[32px] h-[32px] rounded flex items-center justify-center text-[#505050] hover:bg-[#FFF4F9] font-medium text-[14px]" style="font-family: 'Poppins', sans-serif;">
                            2
                        </button>
                        
                        <button class="w-[32px] h-[32px] rounded flex items-center justify-center text-[#505050] hover:bg-[#FFF4F9] font-medium text-[14px]" style="font-family: 'Poppins', sans-serif;">
                            3
                        </button>
                        
                        <button class="w-[32px] h-[32px] rounded flex items-center justify-center text-[#505050] hover:bg-[#FFF4F9] font-medium text-[14px]" style="font-family: 'Poppins', sans-serif;">
                            4
                        </button>
                        
                        <button class="w-[32px] h-[32px] rounded flex items-center justify-center text-[#505050] hover:bg-[#FFF4F9] font-medium text-[14px]" style="font-family: 'Poppins', sans-serif;">
                            5
                        </button>
                        
                        <button class="w-[32px] h-[32px] rounded flex items-center justify-center text-[#505050] hover:bg-[#FFF4F9] font-medium text-[14px]" style="font-family: 'Poppins', sans-serif;">
                            6
                        </button>
                        
                        <button class="w-[32px] h-[32px] rounded flex items-center justify-center bg-[#5C2D62] text-white font-semibold text-[14px]" style="font-family: 'Poppins', sans-serif;">
                            ...
                        </button>
                        
                        <button class="w-[32px] h-[32px] rounded flex items-center justify-center text-[#DD3888] hover:bg-[#FFF4F9]">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </main>
</div>

</body>
</html>
