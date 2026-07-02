<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - Fotografie a video</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-white">

<div id="app" x-data="{ showModal: false }">
    <div x-show="!showModal">
        <x-navbar />
    </div>

    <main>
        <div class="container mx-auto pt-32 px-4">
            
            <!-- Modal -->
            <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-[#5C2D62CC] backdrop-blur-[25px]" style="display: none;">
                <div class="w-[1000px] h-[570px] bg-white rounded-[24px] shadow-[0_10px_25px_0_rgba(0,0,0,0.15)] flex flex-col items-center justify-center gap-8 relative">
                    <button @click="showModal = false" class="absolute top-4 right-4 w-[35px] h-[35px] rounded-full bg-[#DD3888] flex items-center justify-center">
                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    
                    <h3 class="font-bold text-[36px] text-[#5C2D62]" style="font-family: 'Poppins', sans-serif;">Nahrát fotografie</h3>
                    
                    <div class="flex flex-wrap gap-4 items-center justify-center" x-data="{ previews: [] }">
                        <!-- Preview Container -->
                        <template x-for="(preview, index) in previews" :key="index">
                            <div class="w-[200px] h-[200px] relative">
                                <img :src="preview" class="w-[200px] h-[200px] object-cover rounded-[15px]" alt="Preview">
                                <button @click="previews.splice(index, 1)" class="absolute top-[10px] right-[10px] w-[35px] h-[35px] rounded-full bg-[#DD3888] flex items-center justify-center">
                                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <!-- Add Photo Button -->
                        <label class="cursor-pointer relative">
                            <input type="file" class="absolute opacity-0 w-0 h-0" accept="image/*" @change="previews.push(URL.createObjectURL($event.target.files[0]))">
                            <div class="w-[200px] h-[200px] rounded-[15px] border-[2px] border-dashed border-[#A4A4A4] flex flex-col items-center justify-center gap-4">
                                <div class="w-[70px] h-[70px] rounded-full bg-white flex items-center justify-center shadow-[0_4px_10px_0_rgba(0,0,0,0.25)]">
                                    <img src="{{ asset('images/icons/ImagePlus.svg') }}" class="w-[32px] h-[32px]" alt="Add">
                                </div>
                            </div>
                        </label>
                    </div>
                    
                    <button class="group w-[200px] h-[50px] bg-[#E8E8E8] hover:bg-[#5C2D62] rounded-[8px] flex items-center justify-center gap-2 transition-colors duration-300">
                        <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px] group-hover:hidden" alt="Save">
                        <img src="{{ asset('images/icons/SaveWhite.svg') }}" class="w-[20px] h-[20px] hidden group-hover:block" alt="Save">
                        <span class="text-[#A4A4A4] group-hover:text-white" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px;">Uložit změny</span>
                    </button>
                </div>
            </div>

            <!-- Container to reorder on mobile -->
            <div class="flex flex-col max-[426px]:flex-col-reverse items-center max-[426px]:w-full">
                
                <!-- Title -->
                <div class="flex flex-col items-end w-[1134px] mx-auto max-[426px]:items-center max-[426px]:w-full max-[426px]:my-8">
                    <div class="w-[843px] max-[426px]:w-[310px] flex items-center justify-center relative max-[426px]:flex-col max-[426px]:gap-2">
                        <h2 class="font-bold text-[36px] text-[#5C2D62]" style="font-family: 'Poppins', sans-serif;">Fotografie a video</h2>
                        
                        <div class="absolute right-0 flex flex-col items-end gap-0.5 max-[426px]:relative max-[426px]:flex-row max-[426px]:justify-between max-[426px]:w-full max-[426px]:items-center">
                            <div class="flex items-center gap-1.5">
                                <div class="w-[14px] h-[14px] rounded-full bg-[#D80027]"></div>
                                <span class="text-[13px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">Nyní jste offline</span>
                            </div>
                            <span class="text-[13px] text-[#505050] underline" style="font-family: 'Poppins', sans-serif; color: #DD3888;">nastavení</span>
                        </div>
                    </div>
                    <x-dashboard.section-divider />
                </div>
            </div>


            <div class="flex justify-end mb-8 gap-x-12 w-[1134px] mx-auto max-[426px]:!w-[310px] max-[426px]:mx-auto">
                <div class="max-[426px]:hidden">
                    <x-dashboard.sidebar />
                </div>
                
                <div class="w-[843px] max-[426px]:w-[310px] max-[426px]:mx-auto flex flex-col items-center mt-12 max-[426px]:items-center">
                <div class="w-[843px] max-[426px]:w-[310px] max-[426px]:mx-auto flex gap-6 mt-12 max-[426px]:flex-col">
                    <!-- Left Column: Photo & Save -->
                    <div class="flex flex-col gap-[44px]" x-data="{ profilePhoto: '{{ asset('images/accountPhotos/profilePicture.png') }}' }">
                        <!-- Profile Photo Container -->
                        <div class="w-[240px] max-[426px]:!w-[310px] h-[381px] max-[426px]:!h-[493px] rounded-[15px] bg-[#F8F9F9] border-[1px] border-[#E6E6E6] flex items-center justify-center overflow-hidden relative">
                             <!-- Image -->
                             <img x-show="profilePhoto" :src="profilePhoto" class="w-full h-full object-cover" alt="Profile Photo">
                             
                             <!-- Placeholder -->
                             <label x-show="!profilePhoto" class="cursor-pointer absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                                 <input type="file" class="absolute opacity-0 w-0 h-0" accept="image/*" @change="profilePhoto = URL.createObjectURL($event.target.files[0])">
                                 <div class="w-[70px] h-[70px] rounded-full bg-white flex items-center justify-center shadow-[0_4px_10px_0_rgba(0,0,0,0.25)]">
                                    <img src="{{ asset('images/icons/ImagePlus.svg') }}" class="w-[32px] h-[32px]" alt="Add">
                                </div>
                             </label>

                             <!-- Remove Button -->
                             <button x-show="profilePhoto" @click="profilePhoto = null" class="absolute top-[10px] right-[10px] z-10 w-[35px] h-[35px] rounded-full bg-[#DD3888] flex items-center justify-center">
                                <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                        <button class="group w-[240px] max-[426px]:!w-[310px] h-[50px] bg-[#E8E8E8] hover:bg-[#5C2D62] rounded-[8px] flex items-center justify-center gap-2 transition-colors duration-300">
                            <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px] group-hover:hidden" alt="Save">
                            <img src="{{ asset('images/icons/SaveWhite.svg') }}" class="w-[20px] h-[20px] hidden group-hover:block" alt="Save">
                            <span class="text-[#A4A4A4] group-hover:text-white" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px;">Uložit změny</span>
                        </button>
                    </div>

                    <!-- Right Column: Verification -->
                    <div x-data="{ verified: false }" 
                         :class="verified ? 'bg-[#E6FEE8]' : 'border-[#E6E6E6] border'"
                         class="w-[553px] max-[426px]:!w-[310px] h-[475px] max-[426px]:!h-[381px] rounded-[15px] p-8 flex flex-col items-center justify-center max-[426px]:p-4 gap-3 transition-colors duration-300">
                        
                        <!-- Unverified State -->
                        <template x-if="!verified">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-[402px] max-[426px]:!w-[254px] font-bold text-[24px] text-[#5C2D62] text-center" style="font-family: 'Poppins', sans-serif;">
                                    Zvažte ověření fotografie...
                                </div>
                                <div class="w-[398px] max-[426px]:!w-[255px] font-normal text-[14px] text-[#505050] text-center" style="font-family: 'Poppins', sans-serif;">
                                    Oprávněné aniž i odstoupil o snadno osoby vede grafikou osobami úmyslu 60 % před platbě státu zvláštních tuzemsku. Dohodnou zvláštní provádí o nebezpečí kódech § 6 příjmu vhodným třetím
                                </div>
                                <button @click="verified = true" class="w-[401px] max-[426px]:!w-[253px] h-[45px] rounded-[8px] bg-[#5C2D62] flex flex-row items-center justify-between px-4">
                                    <span class="text-left" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #FFFFFF;">Ověřit moje fotografie</span>
                                    <img src="{{ asset('images/icons/BadgeCheck.svg') }}" class="w-[24px] h-[24px]" alt="Check">
                                </button>
                            </div>
                        </template>

                        <!-- Verified State -->
                        <template x-if="verified">
                            <div class="flex flex-col items-center gap-4 text-center">
                                <img src="{{ asset('images/icons/BadgeCheckBig.svg') }}" class="w-[123px] h-[123px]" alt="Verified">
                                
                                <div class="w-[123px] h-[30px] rounded-[8px] bg-[#CDEFD0] flex items-center justify-center gap-1">
                                    <img src="{{ asset('images/icons/camera.svg') }}" class="w-[18px] h-[18px]" alt="Camera">
                                    <span style="font-family: 'Poppins', sans-serif; font-weight: 900; font-size: 10px; color: #00B80F;">FOTO OVĚŘENO</span>
                                </div>

                                <div class="font-bold text-[24px] text-[#5C2D62]" style="font-family: 'Poppins', sans-serif;">Máte ověřený profil</div>
                                <div class="w-[400px] max-[426px]:w-[237px] h-[54px] font-normal text-[14px] text-[#505050] text-center" style="font-family: 'Poppins', sans-serif;">Oprávněné aniž i odstoupil o snadno osoby vede grafikou osobami úmyslu 60 %</div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Ostatní fotografie Section -->
                <x-dashboard.section-divider />

                <div class="w-[843px] max-[426px]:w-[310px] mx-auto mt-12">
                    <h3 class="font-bold text-[24px] text-[#5C2D62] mb-8" style="font-family: 'Poppins', sans-serif;">Ostatní fotografie</h3>
                    
                    <div class="grid grid-cols-5 max-[426px]:grid-cols-2 gap-[23px]">
                        @php
                            $photos = ['pic1', 'pic2', 'pic3', 'pic4', 'pic5', 'pic4', 'pic6', 'pic7'];
                        @endphp
                        @for ($i = 0; $i < 8; $i++)
                            <div class="relative w-[150px] h-[240px] rounded-[15px] bg-[#F8F9F9] border-[1px] border-[#E6E6E6] flex items-center justify-center overflow-hidden {{ $i >= 5 ? 'max-[426px]:hidden' : '' }}">
                                <img src="{{ asset('images/accountPhotos/' . $photos[$i] . '.png') }}" class="w-full h-full object-cover" alt="Photo">
                                <button class="absolute top-[10px] right-[10px] w-[35px] h-[35px] rounded-full bg-[#DD3888] flex items-center justify-center">
                                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        @endfor
                        
                        <!-- Add Photo Container -->
                        <button @click="showModal = true" class="w-[150px] h-[240px] rounded-[15px] border-[2px] border-dashed border-[#A4A4A4] flex flex-col items-center justify-center gap-4 cursor-pointer">
                            <div class="w-[70px] h-[70px] rounded-full bg-white flex items-center justify-center shadow-[0_4px_10px_0_rgba(0,0,0,0.25)]">
                                <img src="{{ asset('images/icons/ImagePlus.svg') }}" class="w-[32px] h-[32px]" alt="Add">
                            </div>
                            <span class="font-bold text-[16px] text-[#5C2D62]" style="font-family: 'Plus Jakarta Sans', sans-serif;">Přidat další</span>
                        </button>
                    </div>
                </div>

                <!-- Vaše reálné video Section -->
                <x-dashboard.section-divider />

                <div class="w-[843px] max-[426px]:w-[310px] mx-auto mt-12 mb-24">
                    <h3 class="font-bold text-[24px] text-[#5C2D62] mb-8" style="font-family: 'Poppins', sans-serif;">Vaše reálné video</h3>
                    
                    <div class="flex flex-col gap-[44px]">
                        <!-- Video & Text Row -->
                        <div class="flex flex-row gap-6 items-start max-[426px]:flex-col">
                            <!-- Video Container -->
                            <div class="w-[240px] max-[426px]:!w-[310px] h-[381px] max-[426px]:!h-[493px] rounded-[15px] border-[2px] border-dashed border-[#A4A4A4] flex flex-col items-center justify-center gap-4 flex-shrink-0">
                                <div class="w-[70px] h-[70px] rounded-full bg-white flex items-center justify-center shadow-[0_4px_10px_0_rgba(0,0,0,0.25)]">
                                    <img src="{{ asset('images/icons/ImagePlus.svg') }}" class="w-[32px] h-[32px]" alt="Add">
                                </div>
                                <span class="font-bold text-[16px] text-[#5C2D62]" style="font-family: 'Plus Jakarta Sans', sans-serif;">Přidat video</span>
                            </div>
                            
                            <!-- Text Container -->
                            <div class="w-[250px] max-[426px]:!w-[310px] max-[426px]:!h-[54px] font-normal text-[14px] text-[#505050] mt-4" style="font-family: 'Poppins', sans-serif;">
                                Oprávněné aniž i odstoupil o snadno osoby vede grafikou osobami úmyslu 60 % před platbě státu zvláštních tuzemsku. Dohodnou zvláštní provádí o nebezpečí kódech § 6 příjmu vhodným třetím
                            </div>
                        </div>
                        
                        <!-- Save Button -->
                        <button class="group w-[240px] max-[426px]:!w-[310px] h-[50px] bg-[#E8E8E8] hover:bg-[#5C2D62] rounded-[8px] flex items-center justify-center gap-2 max-[426px]:mt-6 transition-colors duration-300">
                            <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px] group-hover:hidden" alt="Save">
                            <img src="{{ asset('images/icons/SaveWhite.svg') }}" class="w-[20px] h-[20px] hidden group-hover:block" alt="Save">
                            <span class="text-[#A4A4A4] group-hover:text-white" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px;">Uložit změny</span>
                        </button>
                    </div>
                </div>
                </div>
            </div>

            <x-footer />
        </div>
    </div>
</div>
</body>
</html>
