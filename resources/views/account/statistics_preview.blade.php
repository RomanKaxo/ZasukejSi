<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiky</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white">

<div id="app">
    <x-navbar />

    <main class="max-[426px]:w-[313px] max-[426px]:mx-auto">
        <div class="container mx-auto pt-32 px-4 max-[426px]:w-full max-[426px]:max-w-full max-[426px]:px-0">
            
            <div class="flex flex-col max-[426px]:flex-col-reverse items-center max-[426px]:w-full">
                
                <!-- Title -->
                <div class="flex flex-col items-end w-[1134px] mx-auto max-[426px]:items-center max-[426px]:w-full max-[426px]:my-8">
                    <div class="w-[843px] max-[426px]:w-full flex items-center justify-center relative max-[426px]:flex-col max-[426px]:gap-3 max-[426px]:items-center">
                        <h2 class="font-bold text-[36px] text-[#5C2D62] max-[426px]:text-center max-[426px]:text-[24px] max-[426px]:leading-[30px]" style="font-family: 'Poppins', sans-serif;">Statistiky</h2>
                        
                        <div class="absolute right-0 flex flex-col items-end gap-0.5 max-[426px]:relative max-[426px]:w-full max-[426px]:items-start">
                            <div class="hidden max-[426px]:block w-full mb-2">
                                <x-dashboard.section-divider />
                            </div>
                            <!-- Desktop: Stacked, Mobile: Side-by-side -->
                            <div class="flex flex-col items-end max-[426px]:flex-row max-[426px]:justify-between max-[426px]:w-full">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-[14px] h-[14px] rounded-full bg-[#00B80F]"></div>
                                    <span class="text-[13px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">Nyní jsi online</span>
                                </div>
                                <span class="text-[13px] text-[#505050] underline" style="font-family: 'Poppins', sans-serif; color: #DD3888;">nastavení</span>
                            </div>
                        </div>
                    </div>
                    <x-dashboard.section-divider />
                </div>
            </div>

            <div class="flex justify-end mb-8 gap-x-12 w-[1134px] mx-auto max-[426px]:w-full max-[426px]:mx-auto">
                <div class="max-[426px]:hidden">
                    <x-dashboard.sidebar />
                </div>
                
                <div class="w-[843px] max-[426px]:w-full max-[426px]:mx-auto mt-12 max-[426px]:mt-8">
                    <h3 class="font-bold text-[24px] text-[#5C2D62] mb-8 max-[426px]:mb-5 max-[426px]:text-center max-[426px]:text-[16px] max-[426px]:leading-[20px]" style="font-family: 'Poppins', sans-serif;">Počet zobrazení profilu na hlavní stránce</h3>
                    
                    <div class="py-6">
                        @livewire('profile-statistics', ['variant' => 'homepage'], key('profile-statistics-homepage'))
                    </div>

                    <h3 class="font-bold text-[24px] text-[#5C2D62] mt-[34px] mb-8 max-[426px]:mb-5 max-[426px]:text-center max-[426px]:text-[16px] max-[426px]:leading-[20px]" style="font-family: 'Poppins', sans-serif;">Počet zobrazení detailu profilu</h3>

                    <div class="py-6">
                        @livewire('profile-statistics', ['variant' => 'detail'], key('profile-statistics-detail'))
                    </div>
                </div>
            </div>

        </div>
    </main>

    <x-footer />
</div>
</body>
</html>
