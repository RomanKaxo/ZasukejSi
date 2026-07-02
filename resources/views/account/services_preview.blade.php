<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Služby a ceny</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-white">

<div id="app">
    <x-navbar />

    <main>
        <div class="container mx-auto pt-32 px-4">
            
            <div class="flex flex-col max-[426px]:flex-col-reverse items-center max-[426px]:w-full">
                
                <!-- Title -->
                <div class="flex flex-col items-end w-[1134px] mx-auto max-[426px]:items-center max-[426px]:w-full max-[426px]:my-8">
                            <div class="w-[843px] max-[426px]:w-[310px] flex items-center justify-center relative max-[426px]:flex-col max-[426px]:gap-2 max-[426px]:items-start">
                        <h2 class="font-bold text-[36px] text-[#5C2D62] max-[426px]:text-left" style="font-family: 'Poppins', sans-serif;">Moje služby a ceny</h2>
                        
                        <div class="absolute right-0 flex flex-col items-end gap-0.5 max-[426px]:relative max-[426px]:w-full">
                            <div class="hidden max-[426px]:block w-full mb-2">
                                <x-dashboard.section-divider />
                            </div>
                            <!-- Desktop: Stacked, Mobile: Side-by-side -->
                            <div class="flex flex-col items-end max-[426px]:flex-row max-[426px]:justify-between max-[426px]:w-full">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-[14px] h-[14px] rounded-full bg-[#00B80F]"></div>
                                    <span class="text-[13px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">Nyní jste online</span>
                                </div>
                                <span class="text-[13px] text-[#505050] underline" style="font-family: 'Poppins', sans-serif; color: #DD3888;">nastavení</span>
                            </div>
                        </div>
                    </div>
                    <x-dashboard.section-divider />
                </div>
            </div>

            <div class="flex justify-end mb-8 gap-x-12 w-[1134px] mx-auto max-[426px]:!w-[310px] max-[426px]:mx-auto">
                <div class="max-[426px]:hidden">
                    <x-dashboard.sidebar />
                </div>
                
                <div class="w-[843px] max-[426px]:w-[310px] max-[426px]:mx-auto mt-12">
                    <!-- Služby Toggles -->
                    <div x-data="{ services: [
                        { name: 'Pozice 69', enabled: false }, { name: 'Lízaání', enabled: false }, { name: 'Výstřik do pusy', enabled: false },
                        { name: 'Vaginální sex', enabled: false }, { name: 'Nadávání', enabled: false }, { name: 'Výstřik na tělo', enabled: false },
                        { name: 'Výstřik na obličej', enabled: false }, { name: 'Erotická masáž', enabled: false }, { name: 'Lízaání', enabled: false },
                        { name: 'Výstřik do pusy', enabled: false }, { name: 'Facesitting', enabled: false }, { name: 'Nadávání', enabled: false },
                        { name: 'Výstřik na tělo', enabled: false }, { name: 'Prstění', enabled: false }, { name: 'Erotická masáž', enabled: false },
                        { name: 'Lízaání', enabled: false }, { name: 'Pozice 69', enabled: false }, { name: 'Facesitting', enabled: false },
                        { name: 'Nadávání', enabled: false }, { name: 'Vaginální sex', enabled: false }, { name: 'Prstění', enabled: false },
                        { name: 'Erotická masáž', enabled: false }, { name: 'Výstřik na obličej', enabled: false }, { name: 'Pozice 69', enabled: false },
                        { name: 'Služba 25', enabled: false }, { name: 'Služba 26', enabled: false }, { name: 'Služba 27', enabled: false },
                        { name: 'Služba 28', enabled: false }
                    ]}" class="flex flex-col gap-4 max-[426px]:items-start">
                        <h3 class="font-bold text-[24px] text-[#5C2D62] mb-4 text-left" style="font-family: 'Poppins', sans-serif;">Moje služby</h3>
                        <div class="grid grid-cols-3 max-[426px]:grid-cols-2 gap-x-[30px] gap-y-6 max-[426px]:justify-items-center">
                            <template x-for="(service, index) in services" :key="index">
                                <div class="flex flex-row max-[426px]:flex-col items-center gap-2 max-[426px]:items-start">
                                    <button @click="service.enabled = !service.enabled" 
                                            class="!w-[44px] !h-[24px] !min-w-[44px] !min-h-[24px] rounded-full flex items-center p-[2px] transition-colors duration-300"
                                            :class="service.enabled ? 'bg-[#00B80F]' : 'bg-[#E4E4E7]'">
                                        <div class="!w-[20px] !h-[20px] !min-w-[20px] !min-h-[20px] bg-white rounded-full shadow-sm transform transition-transform duration-300"
                                             :class="service.enabled ? 'translate-x-[20px]' : 'translate-x-0'"></div>
                                    </button>
                                    <div class="w-[156px] h-[24px] flex items-center justify-start max-[426px]:justify-start overflow-hidden">
                                        <span class="text-[15px] text-[#505050] truncate" style="font-family: 'Poppins', sans-serif;" x-text="service.name"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                        
                        <!-- Save Button -->
                        <button class="group w-[240px] h-[50px] bg-[#E8E8E8] hover:bg-[#5C2D62] rounded-[8px] flex items-center justify-center gap-2 mt-8 transition-colors duration-300">
                            <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px] group-hover:hidden" alt="Save">
                            <img src="{{ asset('images/icons/SaveWhite.svg') }}" class="w-[20px] h-[20px] hidden group-hover:block" alt="Save">
                            <span class="text-[#A4A4A4] group-hover:text-white" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px;">Uložit změny</span>
                        </button>
                        
                        <div class="mt-12">
                            <x-dashboard.section-divider />
                            <div class="max-[456px]:w-[272px] max-[456px]:h-[30px] flex items-center max-[456px]:mt-20">
                                <h3 class="font-bold text-[24px] text-[#5C2D62] mb-4 mt-8 max-[456px]:mt-0" style="font-family: 'Poppins', sans-serif;">Kdy bude systém ukazovat, že nejsi online?</h3>
                            </div>
                            
                            <div x-data="{ staleOnline: false }" class="flex items-center gap-3 max-[456px]:mt-20">
                                <button @click="staleOnline = !staleOnline" 
                                        class="!w-[44px] !h-[24px] !min-w-[44px] !min-h-[24px] rounded-full flex items-center p-[2px] transition-colors duration-300"
                                        :class="staleOnline ? 'bg-[#00B80F]' : 'bg-[#E4E4E7]'">
                                    <div class="!w-[20px] !h-[20px] !min-w-[20px] !min-h-[20px] bg-white rounded-full shadow-sm transform transition-transform duration-300"
                                         :class="staleOnline ? 'translate-x-[20px]' : 'translate-x-0'"></div>
                                </button>
                                <span class="text-[15px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">Budu stále online</span>
                            </div>
                            <div class="mt-8 bg-white rounded-[15px] w-[584px]">
                            <div class="grid grid-cols-2 max-[426px]:grid-cols-2 gap-x-[20px] gap-y-4 max-[456px]:w-[310px]" x-data="{ 
                                    openDay: null, 
                                    times: ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00'],
                                    selectedTimes: {}
                                }">
                                    @foreach(['Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota', 'Neděle'] as $day)
                                        @foreach(['od', 'do'] as $type)
                                            <div class="flex flex-col gap-2 max-[456px]:w-[144px]">
                                                <span class="text-[13px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">{{ $day }} {{ strtoupper($type) }}</span>
                                                <div class="relative w-[240px] max-[426px]:w-[144px]">
                                                    <input type="text" readonly 
                                                           class="w-[240px] max-[426px]:w-[144px] h-[50px] bg-white border border-[#E6E6E6] rounded-[8px] px-4 font-bold text-[15px] max-[456px]:text-[15px] text-[#505050] cursor-pointer" 
                                                           style="font-family: 'Poppins', sans-serif;" 
                                                           placeholder="-" 
                                                           x-model="selectedTimes['{{ $day }}_{{ $type }}']"
                                                           @click="openDay = (openDay === '{{ $day }}_{{ $type }}' ? null : '{{ $day }}_{{ $type }}')">
                                                    <button @click="openDay = (openDay === '{{ $day }}_{{ $type }}' ? null : '{{ $day }}_{{ $type }}')" class="absolute right-1 top-1 w-[42px] h-[42px] rounded-[4px] bg-[#DD3888] flex items-center justify-center">
                                                        <svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M1 1L5 4L9 1" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>
                                                    <div x-show="openDay === '{{ $day }}_{{ $type }}'" class="absolute w-[240px] max-[426px]:w-[144px] border-[2px] border-t-0 border-[#E6E6E6] rounded-b-[8px] bg-white z-20 mt-0">
                                                        <template x-for="time in times" :key="time">
                                                            <div class="p-2 cursor-pointer hover:bg-[#FFE0E5]" @click="selectedTimes['{{ $day }}_{{ $type }}'] = time; openDay = null">
                                                                <span x-text="time"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        </div>
                        </div>
                        </main>

                        <x-footer />
                        </div>
                        </body>
                        </html>
