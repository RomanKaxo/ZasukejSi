<div x-data="{ verifyModalOpen: false }">
    @if (session()->has('message'))
        <div class="alert alert-success flex items-center justify-between mb-6">
            <div class="flex items-center font-semibold">
                <x-icons name="bell" class="w-5 h-5 mr-2.5" />
                <span>{{ session('message') }}</span>
            </div>
            <button type="button" class="flex items-center ml-2 text-gray-400 hover:text-gray-600"
                onclick="this.parentElement.remove()">
                <x-icons name="cross" class="text-green-800 w-3 h-3" />
            </button>
        </div>
    @endif

    @if (session()->has('info'))
        <div
            class="alert alert-info flex items-center justify-between mb-6 bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-lg">
            <div class="flex items-center font-semibold">
                <x-icons name="bell" class="w-5 h-5 mr-2.5" />
                <span>{{ session('info') }}</span>
            </div>
            <button type="button" class="flex items-center ml-2 text-gray-400 hover:text-gray-600"
                onclick="this.parentElement.remove()">
                <x-icons name="cross" class="text-blue-800 w-3 h-3" />
            </button>
        </div>
    @endif

    <!-- Main Photo Section -->
    <div class="mb-10">
        <h2 class="mb-6" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">{{ __('front.profiles.photos.main_photo') }}</h2>

        <div class="flex flex-col lg:flex-row gap-6 items-start">
            <!-- Main Photo Display -->
            <div class="shrink-0">
                @if ($mainPhoto)
                    <div class="relative w-[240px] h-[382px]">
                        <img src="{{ $mainPhoto->hasGeneratedConversion('medium') ? $mainPhoto->getUrl('medium') : $mainPhoto->getUrl() }}"
                            alt="{{ __('front.profiles.photos.main_photo') }}"
                            class="w-[240px] h-[382px] object-cover rounded-[15px]">
                        <button type="button" wire:click="removeExistingImage({{ $mainPhoto->id }})"
                            wire:confirm="{{ __('front.profiles.photos.delete_confirm') }}"
                            class="absolute top-[10px] right-[10px] w-[35px] h-[35px] rounded-full bg-[#DD3888] hover:bg-[#CA2474] transition-colors duration-200 flex items-center justify-center">
                            <svg class="w-[10px] h-[10px]" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                @else
                    <label for="photo-upload-input"
                        class="w-[240px] h-[382px] bg-gray-100 rounded-[15px] !flex items-center justify-center border-2 border-dashed border-gray-300 cursor-pointer hover:border-primary hover:bg-primary-50 hover:text-primary transition-colors group">
                        <div class="text-center text-gray-400 group-hover:text-primary transition-colors">
                            <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-xs">{{ __('front.profiles.photos.no_main') }}</p>
                        </div>
                    </label>
                @endif

                <!-- Save Changes Button (under main photo) -->
                @if ($mainPhoto)
                    <button type="button"
                        class="mt-[44px] w-[240px] h-[50px] rounded-[8px] flex items-center justify-center gap-2 bg-[#E8E8E8] hover:bg-[#5C2D62] transition-colors duration-200 group"
                        wire:click="$refresh">
                        <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px] group-hover:hidden" alt="Save">
                        <img src="{{ asset('images/icons/SaveWhite.svg') }}" class="w-[20px] h-[20px] hidden group-hover:block" alt="Save">
                        <span class="text-[#A4A4A4] group-hover:text-white" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px;">{{ __('front.profiles.photos.save_changes') }}</span>
                    </button>
                @endif
            </div>

            <!-- Verification Panel -->
            <div class="w-[553px] max-w-full h-[475px] rounded-[15px] flex flex-col items-center justify-center px-8 text-center {{ $this->isProfileVerified() ? 'bg-[#E6FEE8]' : 'border border-[#E6E6E6]' }}">
                @if ($this->isProfileVerified())
                    <!-- Verified Status -->
                    <img src="{{ asset('images/icons/BadgeCheckBig.svg') }}" class="w-[123px] h-[123px]" alt="">

                    <div class="mt-4 w-[123px] h-[30px] rounded-[8px] bg-[#CDEFD0] flex items-center justify-center gap-1.5">
                        <img src="{{ asset('images/icons/camera.svg') }}" class="w-[18px] h-[18px]" alt="">
                        <span style="font-family:'Poppins',sans-serif; font-weight:900; font-size:10px; color:#00B80F;">{{ __('front.profiles.photos.verified_badge') }}</span>
                    </div>

                    <h3 class="mt-4 mb-2" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">
                        {{ __('front.profiles.photos.verified_title') }}</h3>
                    <p style="font-family:'Poppins',sans-serif; font-weight:400; font-size:14px; color:#505050;">{{ __('front.profiles.photos.verified_desc') }}</p>
                @elseif($this->isVerificationRejected())
                    <!-- Rejected Status -->
                    <h3 class="mb-2" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">
                        {{ __('front.profiles.photos.rejected_title') }}</h3>
                    <p class="text-sm text-gray-600 mb-6 w-[399px] max-w-full">{{ __('front.profiles.photos.rejected_desc') }}</p>
                    <button type="button" wire:click="requestVerification" wire:loading.attr="disabled"
                        class="w-[401px] max-w-full h-[45px] px-6 rounded-lg bg-[#5C2D62] hover:bg-[#4a2450] transition-colors flex items-center justify-between gap-2">
                        <span style="font-family:'Poppins',sans-serif; font-weight:600; font-size:16px; color:#FFFFFF;">{{ __('front.profiles.photos.try_again') }}</span>
                        <img src="{{ asset('images/icons/BadgeCheck.svg') }}" class="w-[24px] h-[24px]" alt="">
                    </button>
                @elseif($mainPhoto)
                    <!-- Not Verified - Can Request -->
                    <h3 class="mb-6" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">
                        {{ __('front.profiles.photos.verify_title') }}</h3>
                    <p class="text-sm text-gray-600 mb-6 w-[399px] max-w-full h-[54px]">{{ __('front.profiles.photos.verify_desc') }}</p>
                    <button type="button" @click="verifyModalOpen = true"
                        class="mt-8 w-[401px] max-w-full h-[45px] px-6 rounded-lg bg-[#5C2D62] hover:bg-[#4a2450] transition-colors flex items-center justify-between gap-2">
                        <span style="font-family:'Poppins',sans-serif; font-weight:600; font-size:16px; color:#FFFFFF;">{{ __('front.profiles.photos.verify_button') }}</span>
                        <img src="{{ asset('images/icons/BadgeCheck.svg') }}" class="w-[24px] h-[24px]" alt="">
                    </button>

                    <!-- Verify Photo Modal -->
                    <template x-teleport="body">
                        <div x-show="verifyModalOpen" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
                            <div class="fixed inset-0" style="background-color: #5C2D62CC; backdrop-filter: blur(30px);" @click="verifyModalOpen = false"></div>

                            <div class="relative w-[600px] h-[908px] max-w-full max-h-full rounded-[24px] bg-white overflow-y-auto"
                                style="box-shadow: 0 10px 25px 0 #00000033;">
                                <button type="button" @click="verifyModalOpen = false"
                                    class="absolute w-[35px] h-[35px] rounded-full bg-[#DD3888] hover:bg-[#CA2474] transition-colors duration-200 flex items-center justify-center"
                                    style="top: 40px; right: 35px;">
                                    <svg class="w-[10px] h-[10px]" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </button>

                                <div class="flex flex-col items-center px-10" style="margin-top: 80px;">
                                    <img src="{{ asset('images/icons/BadgeCheckBig.svg') }}" class="w-[78px] h-[78px]" alt="">
                                    <h2 class="text-center mt-4" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:36px; color:#5C2D62;">{{ __('front.profiles.photos.verify_modal_title') }}</h2>
                                    <p class="text-center mt-3" style="font-family:'Poppins',sans-serif; font-weight:400; font-size:14px; color:#505050;">
                                        {{ __('front.profiles.photos.verify_modal_desc_1') }}
                                        <span style="color:#00B80F; font-weight:700;">{{ __('front.profiles.photos.verify_modal_trust') }}</span>
                                        {{ __('front.profiles.photos.verify_modal_desc_2') }}
                                    </p>

                                    @if ($this->isVerificationPending())
                                        <div class="w-[510px] h-[535px] max-w-full rounded-[15px] bg-[#EBF8EC] flex flex-col items-center justify-center mt-6">
                                            <svg class="w-[36px] h-[36px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M5 22H19M5 2H19M17 22V17.828C16.9999 17.2976 16.7891 16.789 16.414 16.414L12 12M12 12L7.586 16.414C7.2109 16.789 7.00011 17.2976 7 17.828V22M12 12L7.586 7.586C7.2109 7.21101 7.00011 6.70239 7 6.172V2M12 12L16.414 7.586C16.7891 7.21101 16.9999 6.70239 17 6.172V2" stroke="#00B80F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <p class="text-center w-[233px] h-[48px] mt-3" style="font-family:'Poppins',sans-serif; font-weight:400; font-size:14px; color:#505050;">{{ __('front.profiles.photos.verify_thanks_line1') }}<br>{{ __('front.profiles.photos.verify_thanks_line2') }}</p>
                                        </div>
                                    @else
                                        <div class="w-[510px] h-[535px] max-w-full rounded-[15px] bg-[#F2F2F2] mt-6 flex items-center justify-center gap-4">
                                            <div class="w-[214px] h-[285px] rounded-[15px] flex items-center justify-center overflow-hidden" style="border: 2px dashed #A4A4A4;">
                                                <img src="{{ asset('images/AccountPhotos/verify.png') }}" class="w-full h-full object-cover" alt="">
                                            </div>

                                            <div
                                                class="relative w-[214px] h-[285px] bg-white border-2 border-dashed border-gray-300 rounded-[15px] flex flex-col items-center justify-center">
                                                <div class="w-[70px] h-[70px] bg-white rounded-full flex items-center justify-center" style="box-shadow: 0 4px 10px 0 #00000040;">
                                                    <img src="{{ asset('images/icons/ImagePlus.svg') }}" class="w-[32px] h-[32px]" alt="">
                                                </div>
                                                <span class="text-center" style="margin-top: 12px; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 16px; color: #5C2D62;">{{ __('front.profiles.photos.video_add_line1') }}<br>{{ __('front.profiles.photos.video_add_line2') }}</span>
                                            </div>
                                        </div>

                                        <p class="w-[450px] max-w-full mt-4" style="font-family:'Poppins',sans-serif; font-weight:400; font-size:11px; color:#5C5C5C;">{{ __('front.profiles.photos.verify_modal_terms') }}</p>

                                        <button type="button" wire:click="requestVerification" wire:loading.attr="disabled"
                                            wire:loading.class="opacity-50 cursor-not-allowed"
                                            class="w-[450px] max-w-full h-[60px] rounded-lg bg-[#DD3888] hover:bg-[#CA2474] transition-colors mt-4 flex items-center justify-center">
                                            <span wire:loading.remove style="font-family:'Poppins',sans-serif; font-weight:600; font-size:16px; color:#FFFFFF;">{{ __('front.profiles.photos.verify_modal_submit') }}</span>
                                            <span wire:loading style="font-family:'Poppins',sans-serif; font-weight:600; font-size:16px; color:#FFFFFF;">{{ __('front.profiles.photos.processing') }}</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </template>
                @else
                    <!-- No Main Photo - Upload First -->
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="mb-2" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">
                        {{ __('front.profiles.photos.upload_first_title') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('front.profiles.photos.upload_first_desc') }}</p>
                @endif
            </div>
        </div>
    </div>

    <hr class="w-[843px] max-w-full relative left-1/2 -translate-x-1/2 mt-[80px] mb-[50px]">

    <!-- Other Photos Section -->
    <div class="mb-10">
        <h2 class="mb-6" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">{{ __('front.profiles.photos.other_photos') }}</h2>

        <div class="flex flex-wrap gap-4" x-data="{ modalOpen: false }">
            @forelse ($otherPhotos as $photo)
                <div class="relative w-[150px] h-[240px]" wire:key="other-photo-{{ $photo->id }}">
                    <img src="{{ $photo->hasGeneratedConversion('thumb') ? $photo->getUrl('thumb') : $photo->getUrl() }}"
                        alt="{{ $photo->name }}"
                        class="w-[150px] h-[240px] object-cover rounded-[15px]">

                    <!-- Delete Button -->
                    <button type="button" wire:click="removeExistingImage({{ $photo->id }})"
                        wire:confirm="{{ __('front.profiles.photos.delete_confirm') }}"
                        wire:loading.attr="disabled"
                        class="absolute top-[10px] right-[10px] w-[35px] h-[35px] rounded-full bg-[#DD3888] hover:bg-[#CA2474] transition-colors duration-200 flex items-center justify-center">
                        <svg class="w-[10px] h-[10px]" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            @empty
                <p class="w-full text-sm text-gray-500 mb-2" style="font-family:'Poppins',sans-serif;">
                    {{ __('front.profiles.photos.no_other_photos') }}
                </p>
            @endforelse

            <!-- Add Photo Button (opens upload modal) -->
            <button type="button" @click="modalOpen = true"
                class="relative w-[150px] h-[240px] border-2 border-dashed border-gray-300 rounded-[15px] flex flex-col items-center justify-center cursor-pointer hover:border-primary hover:bg-primary-50 transition-colors">
                <div>
                    <div
                        class="w-[70px] h-[70px] mx-auto mb-2 bg-white rounded-full flex items-center justify-center"
                        style="box-shadow: 0 4px 10px 0 #00000040;">
                        <img src="{{ asset('images/icons/ImagePlus.svg') }}" class="w-[32px] h-[32px]" alt="">
                    </div>
                    <span style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 16px; color: #5C2D62;">{{ __('front.profiles.photos.add_more') }}</span>
                </div>
            </button>

            <!-- Upload Modal -->
            <template x-teleport="body">
            <div x-show="modalOpen" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
                <div class="fixed inset-0" style="background-color: #5C2D62CC; backdrop-filter: blur(30px);" @click="modalOpen = false"></div>

                <div class="relative w-[1000px] h-[570px] max-w-full max-h-full rounded-[24px] bg-[#FFFFFF] p-10 overflow-y-auto"
                    style="box-shadow: 0 10px 25px 0 #00000033;">
                    <button type="button" @click="modalOpen = false"
                        class="absolute top-6 right-6 w-[35px] h-[35px] rounded-full bg-[#DD3888] hover:bg-[#CA2474] transition-colors duration-200 flex items-center justify-center">
                        <svg class="w-[10px] h-[10px]" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </button>

                    <h2 class="text-center mb-8" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:36px; color:#5C2D62;">{{ __('front.profiles.photos.upload_modal_title') }}</h2>

                    <div class="absolute left-0 right-0 bottom-[180px] px-10">
                        <div class="flex flex-wrap justify-center gap-4">
                            @foreach ($images as $index => $image)
                                @if ($image)
                                    <div class="relative w-[150px] h-[240px]">
                                        <img src="{{ $image->temporaryUrl() }}" alt="Preview"
                                            class="w-[150px] h-[240px] object-cover rounded-[15px]">
                                        <button type="button" wire:click="removeUploadedImage({{ $index }})"
                                            class="absolute top-[10px] right-[10px] w-[35px] h-[35px] rounded-full bg-[#DD3888] hover:bg-[#CA2474] transition-colors duration-200 flex items-center justify-center">
                                            <svg class="w-[10px] h-[10px]" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                            @endforeach

                            <label
                                class="relative w-[200px] h-[200px] bg-white border-2 border-dashed border-gray-300 rounded-[15px] cursor-pointer hover:border-primary transition-colors !flex !flex-col !items-center !justify-center !p-0">
                                <input id="photo-upload-input" type="file" wire:model="images" multiple accept="image/*"
                                    class="!hidden">
                                <div
                                    class="w-[70px] h-[70px] bg-white rounded-full flex items-center justify-center"
                                    style="box-shadow: 0 4px 10px 0 #00000040;">
                                    <img src="{{ asset('images/icons/ImagePlus.svg') }}" class="w-[32px] h-[32px]" alt="">
                                </div>
                                <span class="text-center" style="margin-top: 12px; margin-bottom: 37px; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 16px; color: #5C2D62;">{{ __('front.profiles.photos.add_photo_line1') }}<br>{{ __('front.profiles.photos.add_photo_line2') }}</span>
                                <div wire:loading wire:target="images" class="absolute inset-0 bg-white/80 rounded-[15px] z-10 flex items-center justify-center">
                                    <svg class="animate-spin h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="absolute bottom-[80px] left-0 right-0 flex justify-center">
                        <button type="button" wire:click="uploadImages" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="w-[200px] h-[50px] rounded-lg bg-[#5C2D62] flex items-center justify-center gap-2">
                            <img src="{{ asset('images/icons/SaveWhite.svg') }}" class="w-[20px] h-[20px]" alt="">
                            <span wire:loading.remove style="font-family:'Poppins',sans-serif; font-weight:600; font-size:16px; color:#FFFFFF;">{{ __('front.profiles.photos.save_changes') }}</span>
                            <span wire:loading style="font-family:'Poppins',sans-serif; font-weight:600; font-size:16px; color:#FFFFFF;">{{ __('front.profiles.photos.uploading') }}</span>
                        </button>
                    </div>
                </div>
            </div>
            </template>
        </div>

        @error('images.*')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <hr class="w-[843px] max-w-full relative left-1/2 -translate-x-1/2 mt-[80px] mb-[50px]">

    <!-- Video Upload Section -->
    <div class="mb-10">
        <h2 class="mb-6 text-center lg:text-left" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">{{ __('front.profiles.photos.video_title') }}</h2>

        <div class="flex flex-col lg:flex-row gap-6 items-center lg:items-start">
          <div class="shrink-0">
            <!-- Video Display/Upload Area -->
            <div class="shrink-0">
                @if ($existingVideo)
                    <div class="relative w-[240px] h-[381px]">
                        <div class="w-[240px] h-[381px] bg-black rounded-[15px] overflow-hidden">
                            <video id="profile-video-preview" src="{{ $existingVideo->getUrl() }}"
                                class="w-full h-full object-cover transition-all duration-300"
                                style="filter: brightness(0.7);" preload="metadata">
                            </video>
                            <!-- Custom Play Button Overlay -->
                            <button type="button" onclick="toggleVideoPlayback('profile-video-preview')"
                                id="video-play-button"
                                class="absolute inset-0 flex items-center justify-center transition-colors hover:filter hover:brightness-75">
                                <div
                                    class="w-16 h-16 bg-primary rounded-full flex items-center justify-center shadow-lg hover:bg-primary-600 transition-colors">
                                    <svg class="w-8 h-8 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </div>
                            </button>
                        </div>
                        <!-- Delete Button -->
                        <button type="button" wire:click="removeVideo"
                            wire:confirm="{{ __('front.profiles.photos.video_delete_confirm') }}"
                            class="absolute top-[10px] right-[10px] w-[35px] h-[35px] rounded-full bg-[#DD3888] hover:bg-[#CA2474] transition-colors duration-200 flex items-center justify-center z-10">
                            <svg class="w-[10px] h-[10px]" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                @else
                    <label for="video-upload-input"
                        class="relative w-[240px] h-[381px] bg-white border-2 border-dashed border-gray-300 rounded-[15px] cursor-pointer hover:border-primary transition-colors !flex !flex-col !items-center !justify-center !p-0">
                        <input id="video-upload-input" type="file" wire:model="video"
                            accept="video/mp4,video/webm,video/quicktime" class="!hidden"
                            x-on:livewire-upload-error="$dispatch('video-upload-error')">
                        <div
                            class="w-[70px] h-[70px] bg-white rounded-full flex items-center justify-center"
                            style="box-shadow: 0 4px 10px 0 #00000040;">
                            <img src="{{ asset('images/icons/ImagePlus.svg') }}" class="w-[32px] h-[32px]" alt="">
                        </div>
                        <span class="text-center" style="margin-top: 12px; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 16px; color: #5C2D62;">{{ __('front.profiles.photos.video_add_line1') }}<br>{{ __('front.profiles.photos.video_add_line2') }}</span>
                        <div wire:loading wire:target="video" class="absolute inset-0 bg-white/80 rounded-[15px] z-10 flex items-center justify-center">
                            <svg class="animate-spin h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </div>
                    </label>
                @endif
            </div>

            <!-- Save Button (under video placeholder) -->
            <button type="button"
                class="mt-4 w-[240px] h-[50px] rounded-[8px] flex items-center justify-center gap-2 bg-[#E8E8E8] hover:bg-[#5C2D62] transition-colors duration-200 group"
                wire:click="$refresh">
                <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px] group-hover:hidden" alt="Save">
                <img src="{{ asset('images/icons/SaveWhite.svg') }}" class="w-[20px] h-[20px] hidden group-hover:block" alt="Save">
                <span class="text-[#A4A4A4] group-hover:text-white" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px;">{{ __('front.profiles.photos.save_changes') }}</span>
            </button>
          </div>

            <!-- Description beside placeholder -->
            <p class="w-[250px] h-[54px]" style="font-family:'Poppins',sans-serif; font-weight:400; font-size:14px; color:#505050;">{{ __('front.profiles.photos.video_verify_desc') }}</p>
        </div>

        <!-- Video Upload Error Message -->
        <div x-data="{ showError: false }"
            x-on:video-upload-error.window="showError = true; setTimeout(() => showError = false, 10000)"
            x-show="showError" x-transition
            class="mt-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-start gap-3"
            style="display: none;">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd" />
            </svg>
            <div>
                <p class="font-semibold">{{ __('front.profiles.photos.video_upload_failed') }}</p>
                <p class="text-sm mt-1 text-red-600">{{ __('front.profiles.photos.video_too_large') }}</p>
            </div>
            <button type="button" @click="showError = false" class="ml-auto text-red-500 hover:text-red-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        @error('video')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Preview Uploaded Video -->
    @if ($video)
        <div class="mb-10 bg-primary-50 border border-primary-200 rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('front.profiles.photos.video_preview') }}</h2>

            <div class="flex flex-col sm:flex-row gap-6 items-start">
                <div class="w-48 aspect-[9/16] bg-black rounded-2xl border-2 border-primary overflow-hidden relative">
                    <video id="video-preview-upload" src="{{ $video->temporaryUrl() }}"
                        class="w-full h-full object-cover transition-all duration-300"
                        style="filter: brightness(0.7);" preload="metadata">
                    </video>
                    <!-- Custom Play Button Overlay -->
                    <button type="button" onclick="toggleVideoPlayback('video-preview-upload')"
                        class="absolute inset-0 flex items-center justify-center hover:bg-black/10 transition-colors video-play-overlay">
                        <div
                            class="w-12 h-12 bg-primary rounded-full flex items-center justify-center shadow-lg hover:bg-primary-600 transition-colors">
                            <svg class="w-6 h-6 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z" />
                            </svg>
                        </div>
                    </button>
                </div>


                <div class="flex-1">
                    <p class="text-xs text-gray-500 mb-4">{{ __('front.profiles.photos.video_formats') }}</p>
                    <button type="button" wire:click="uploadVideo" wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed" class="btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <span wire:loading.remove
                            wire:target="uploadVideo">{{ __('front.profiles.photos.video_upload_button') }}</span>
                        <span wire:loading
                            wire:target="uploadVideo">{{ __('front.profiles.photos.uploading') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Image Modal -->
    <div id="imageModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-75 p-4"
        style="display: none; align-items: center; justify-content: center;">
        <div class="relative max-w-4xl max-h-full">
            <img id="modalImage" src="" alt="Full size image"
                class="max-w-full max-h-full object-contain rounded-lg">
            <button onclick="closeImageModal()"
                class="absolute top-4 right-4 bg-white text-gray-800 rounded-full p-2 hover:bg-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <script>
        function openImageModal(imageUrl) {
            const modal = document.getElementById('imageModal');
            document.getElementById('modalImage').src = imageUrl;
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }

        // Close modal when clicking outside the image
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });

        // Video playback control
        function toggleVideoPlayback(videoId) {
            const video = document.getElementById(videoId);
            const playButton = video.parentElement.querySelector('.video-play-overlay, #video-play-button');

            if (video.paused) {
                video.play();
                video.style.filter = 'brightness(1)';
                if (playButton) {
                    playButton.style.opacity = '0';
                }
            } else {
                video.pause();
                video.style.filter = 'brightness(0.7)';
                if (playButton) {
                    playButton.style.opacity = '1';
                }
            }
        }

        // Show play button when video ends or pauses
        document.querySelectorAll('video').forEach(function(video) {
            video.addEventListener('ended', function() {
                const playButton = this.parentElement.querySelector(
                    '.video-play-overlay, #video-play-button');
                this.style.filter = 'brightness(0.7)';
                if (playButton) {
                    playButton.style.opacity = '1';
                }
            });

            video.addEventListener('pause', function() {
                const playButton = this.parentElement.querySelector(
                    '.video-play-overlay, #video-play-button');
                this.style.filter = 'brightness(0.7)';
                if (playButton) {
                    playButton.style.opacity = '1';
                }
            });

            // Click on video to toggle playback
            video.addEventListener('click', function(e) {
                e.preventDefault();
                toggleVideoPlayback(this.id);
            });
        });
    </script>


</div>
