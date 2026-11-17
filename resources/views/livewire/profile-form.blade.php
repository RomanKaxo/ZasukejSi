<div>
    {{-- Figma home-2 field rhythm: label glyph 22px above the 50px field,
         fields 94px apart, label text 8px in, #E9E9E9 rules. Scoped here
         because components.css belongs to the shared front-end. --}}
    <style>
        .profile-form-narrow label { padding-left: 8px; padding-bottom: 0; }
        .profile-form-narrow .input-control.mt-1 { margin-top: 0; }
        .profile-form-narrow input.input-control, .profile-form-narrow select.input-control { padding-left: 17px; }
        .profile-form-narrow hr { border-color: #E9E9E9; }
        .profile-form-narrow label.pf-label-off { color: #E5E5E5; }
        .profile-form-narrow textarea#about { font-weight: 400; font-size: 15px; line-height: 22px; padding: 20px 22px; margin-top: 2px; }
        @media (min-width: 768px) { .profile-form-narrow textarea#about { font-size: 14px; padding: 23px 25px 23px 27px; margin-top: 0; } }
        @media (min-width: 1180px) { .pf-shift-2 { left: calc(50% - 2px) !important; } }
        /* pass 2 (phone 360 px-13): section rules span x 22..335 while the fields sit at 25..335. */
        @media (max-width: 767px) { .profile-form-narrow hr { width: 313px !important; max-width: none !important; margin-left: -3px; } }
    </style>

    @if (session()->has('message'))
    {{-- Figma home-4: on desktop the "saved" toast takes the place of the
         account notice bar — 1134x50 at the top of the page, pinned 10px
         under the header once that spot scrolls away, icon + text centred,
         cross like the notice bar. The pink notice is hidden meanwhile so
         the two never stack. Mobile keeps the 310x110 card in the form. --}}
    <style>
        @media (min-width: 768px) {
            .account-shell:has(.pf-saved-toast) .account-notice { visibility: hidden; }
            .pf-saved-toast { position: fixed; z-index: 45; left: 50%; transform: translateX(-50%); width: 1134px; max-width: calc(100% - 32px); margin: 0; }
        }
    </style>
    <div x-data="{ top: 135, place() { const nav = document.querySelector('nav'); const navB = nav ? Math.max(0, nav.getBoundingClientRect().bottom) : 0; this.top = Math.max(navB + 10, 135 - window.scrollY); } }"
         x-init="place()" @scroll.window.passive="place()" @resize.window="place()"
         :style="window.innerWidth >= 768 ? `top:${top}px` : ''"
         class="pf-saved-toast relative mx-auto w-[310px] max-w-full h-[110px] px-4 py-3 md:h-[50px] md:pl-10 md:pr-[47px] md:py-0 bg-[#01B810] rounded-[8px] flex items-center justify-center gap-3 md:gap-[8px] mb-4" role="status">
        <img src="{{ asset('images/icons/SaveWhite.svg') }}" class="w-[20px] h-[20px] shrink-0" alt="">
        <span class="font-medium text-[14px] text-white text-center" style="font-family: 'Poppins', sans-serif;">{{ session('message') }}</span>
        <button type="button" aria-label="{{ __('front.notifications.archive') }}" class="absolute right-3 top-1/2 -translate-y-1/2 md:right-[16px] flex w-[20px] h-[20px] items-center justify-center" onclick="this.parentElement.remove()">
            <svg class="w-[10px] h-[10px]" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>
    </div>
    @endif

    {{-- A validation failure anywhere in the form (e.g. a price row with an
         amount but no duration) used to fail the whole save silently — the
         only sign was small red text next to one field, often scrolled out
         of view. This banner, plus the scroll-to-top in save(), makes the
         "nothing saved" state visible instead of looking like nothing
         happened. --}}
    @if ($errors->any())
    <div wire:key="validation-errors-banner" class="relative mx-auto w-[310px] max-w-full px-4 py-3 md:w-full md:mx-0 bg-[#DD3888] rounded-[8px] mb-4">
        <div class="flex items-start">
            <x-icons name="TriangleAlert" class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5" style="color: #FFFFFF;" />
            <div class="text-[14px] text-white" style="font-family: 'Poppins', sans-serif;">
                <p class="font-medium mb-1">{{ __('front.profiles.form.validation_summary') }}</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $index => $error)
                        <li wire:key="validation-error-{{ $index }}">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif



    <form wire:submit="save" class="space-y-8">
    <div class="profile-form-narrow w-[310px] max-w-full md:w-[400px] mx-auto space-y-8">
        <!-- Personal Information Section -->
        <div class="space-y-6">
            <h3 style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62; margin-bottom:15px;">{{ __('front.profiles.form.my_data') }}</h3>

            <div class="grid grid-cols-1 gap-[10px] !mt-0">
                <!-- Name -->
                <div>
                    @php($nicknameField = $hasProfile ? 'display_name' : 'name')
                    <label for="{{ $nicknameField }}" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.nickname') }}</label>
                    <input
                        type="text"
                        id="{{ $nicknameField }}"
                        wire:model="{{ $nicknameField }}"
                        class="input-control mt-1 @error($nicknameField) border-red-500 @enderror">
                    @error($nicknameField) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="account_email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.your_email') }}</label>
                    <input type="email" id="account_email" value="{{ auth()->user()->email }}" readonly class="input-control mt-1 bg-gray-50">
                </div>

                <div class="space-y-3">
                    <h4 class="font-semibold text-secondary leading-6">{{ __('front.profiles.form.change_email') }}</h4>
                    <div>
                        <label for="new_email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.new_email') }}</label>
                        <input type="email" id="new_email" wire:model="new_email" autocomplete="email"
                            class="input-control mt-1 @error('new_email') border-red-500 @enderror">
                        @error('new_email') <p role="alert" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email_change_password" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.password_to_confirm') }}</label>
                        <input type="password" id="email_change_password" wire:model="email_change_password" autocomplete="current-password"
                            class="input-control mt-1 @error('email_change_password') border-red-500 @enderror">
                        @error('email_change_password') <p role="alert" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <p class="text-sm text-gray-500">{{ __('front.profiles.form.email_change_notice') }}</p>
                </div>

                @if($hasProfile)
                {{--
                    Country + city are resolved entirely in the browser: picking either one
                    fires no Livewire request (values are sent with the form on save).
                --}}
                <div
                    wire:ignore
                    x-data="{
                        citiesByCountry: {{ \Illuminate\Support\Js::from($citiesByCountry) }},
                        country: @js($country_code),
                        city: @js($city),
                        get cities() {
                            return this.citiesByCountry[this.country] ?? [];
                        },
                        selectCountry() {
                            this.city = '';
                            $wire.set('country_code', this.country, false);
                            $wire.set('city', '', false);
                        },
                        selectCity() {
                            $wire.set('city', this.city, false);
                        }
                    }"
                    class="contents">
                    <!-- Country -->
                    <div>
                        <label for="country_code" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.country') }}</label>
                        <div class="relative group">
                            <select
                                id="country_code"
                                x-model="country"
                                @change="selectCountry()"
                                class="input-control w-full appearance-none pr-[54px] @error('country_code') border-red-500 @enderror {{ $this->shouldShowPublishRequirement('country_code') ? 'border-red-500' : '' }}">
                                <option value="">{{ __('front.profiles.form.selectcountry') }}</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country['code'] }}">{{ $country['name'] }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-1 top-1/2 -translate-y-1/2 w-[42px] h-[42px] rounded-[4px] bg-[#DD3888] group-hover:bg-[#CA2474] transition-colors duration-200 flex items-center justify-center pointer-events-none">
                                <svg class="w-[10px] h-[5px] transition-transform duration-200 group-focus-within:-rotate-180" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 1L5 4L9 1" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                        @error('country_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        @if($this->shouldShowPublishRequirement('country_code'))
                            <p class="mt-1 text-sm text-red-600">{{ __('front.profiles.form.publish_required') }}</p>
                        @endif
                    </div>

                    <!-- City (options follow the selected country) -->
                    <div>
                        <div class="flex items-center justify-between">
                            <label for="city" class="block text-sm font-medium text-gray-700">{{ __('front.profiles.form.city') }}</label>
                            <span class="flex items-center gap-1" x-show="!city" x-cloak>
                                <img src="{{ asset('images/icons/OctagonAlert.svg') }}" class="w-[14px] h-[14px]" alt="">
                                <span style="font-family:'Poppins',sans-serif; font-weight:400; font-size:13px; color:#D80027;">{{ __('front.profiles.form.required_field') }}</span>
                            </span>
                        </div>
                        <div class="relative group">
                            <select
                                id="city"
                                x-model="city"
                                x-init="$nextTick(() => { $el.value = city })"
                                @change="selectCity()"
                                :disabled="!country"
                                :class="(!country ? 'bg-gray-100 cursor-not-allowed ' : '') + (!city ? 'border-2 border-[#D80027]' : '')"
                                class="input-control w-full appearance-none pr-[54px] @error('city') border-red-500 @enderror">
                                <option value="">{{ __('front.profiles.form.city_placeholder') }}</option>
                                <template x-for="cityName in cities" :key="cityName">
                                    <option :value="cityName" x-text="cityName"></option>
                                </template>
                            </select>
                            <div class="absolute right-1 top-1/2 -translate-y-1/2 w-[42px] h-[42px] rounded-[4px] bg-[#DD3888] group-hover:bg-[#CA2474] transition-colors duration-200 flex items-center justify-center pointer-events-none">
                                <svg class="w-[10px] h-[5px] transition-transform duration-200 group-focus-within:-rotate-180" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 1L5 4L9 1" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500" x-show="!country">{{ __('front.profiles.form.select_country_first') }}</p>
                        @error('city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                @endif

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.your_phone') }}</label>
                    <input
                        type="tel"
                        id="phone"
                        wire:model="phone"
                        class="input-control mt-1 @error('phone') border-red-500 @enderror">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            @if($hasProfile)
            <div class="space-y-[11px] !mb-[23px] md:!mb-[26px]">
                <div class="flex items-center gap-2">
                    <x-toggle-switch
                        name="has_whatsapp"
                        id="has_whatsapp"
                        wire-model="has_whatsapp"
                        :checked="$has_whatsapp" />
                    <label for="has_whatsapp" class="text-sm font-medium text-gray-700">{{ __('front.profiles.form.whatsapp_toggle') }}</label>
                </div>
                <div class="flex items-center gap-2">
                    <x-toggle-switch
                        name="has_telegram"
                        id="has_telegram"
                        wire-model="has_telegram"
                        :checked="$has_telegram" />
                    <label for="has_telegram" class="text-sm font-medium text-gray-700">{{ __('front.profiles.form.telegram_toggle') }}</label>
                </div>
            </div>
            @endif

            <button type="submit" class="w-[310px] max-w-full md:w-[400px] h-[50px] rounded-[8px] flex items-center justify-center gap-2 bg-[#E8E8E8] hover:bg-[#5C2D62] transition-colors duration-200 group">
                <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px] group-hover:hidden" alt="">
                <img src="{{ asset('images/icons/SaveWhite.svg') }}" class="w-[20px] h-[20px] hidden group-hover:block" alt="">
                <span class="text-[#A4A4A4] group-hover:text-white" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px;">{{ __('front.profiles.form.save_changes') }}</span>
            </button>

            {{-- phone 360 px-13: on mobile the rules sit 50px under the save
                 buttons and the next heading 42–61px under them, per section. --}}
            <hr class="w-full min-[1180px]:w-[843px] min-[1180px]:max-w-none relative min-[1180px]:left-1/2 min-[1180px]:-translate-x-1/2 mt-[49px] md:mt-[80px] mb-[55px] md:mb-[52px]">

            {{--
                A provider without a profile had no way to create one: the
                "Create profile" block was dropped in the account redesign while
                createProfile() stayed, so a newly registered woman only ever saw
                her name, e-mail and phone, and the photo/services pages sent her
                back here with "create your profile first".
            --}}
            @if(! $hasProfile && (auth()->user()->gender === 'female' || $this->isAdmin()))
            <div class="space-y-6" data-create-profile>
                <h3 style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62; margin-bottom:15px;">{{ __('front.profiles.form.create_profile_btn') }}</h3>
                <p class="text-sm text-gray-600 !mt-0">{{ __('front.profiles.form.createprofile') }}</p>

                <div class="grid grid-cols-1 gap-[10px]">
                    <div>
                        <label for="create_display_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.displayname') }}</label>
                        <input type="text" id="create_display_name" wire:model="display_name"
                            class="input-control mt-1 @error('display_name') border-red-500 @enderror">
                        @error('display_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="create_age" class="block text-sm font-medium text-gray-700 mb-2">{{ \Illuminate\Support\Str::lcfirst(__('front.profiles.form.age')) }}</label>
                        <input type="number" id="create_age" wire:model="age" min="18" max="120"
                            class="input-control mt-1 @error('age') border-red-500 @enderror">
                        @error('age') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="create_country_code" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.country') }}</label>
                        <select id="create_country_code" wire:model="country_code"
                            class="input-control mt-1 w-full @error('country_code') border-red-500 @enderror">
                            <option value="">{{ __('front.profiles.form.selectcountry') }}</option>
                            @foreach($countries as $country)
                                <option value="{{ $country['code'] }}">{{ $country['name'] }}</option>
                            @endforeach
                        </select>
                        @error('country_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="create_about" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.aboutme') }}</label>
                        <textarea id="create_about" wire:model="about" rows="4" maxlength="640"
                            placeholder="{{ __('front.profiles.form.aboutmeplaceholder') }}"
                            class="input-control mt-1 @error('about') border-red-500 @enderror"></textarea>
                        @error('about') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <p class="text-sm text-gray-500">{{ __('front.profiles.form.verification_notice') }}</p>

                <button type="button" wire:click="createProfile" wire:loading.attr="disabled" wire:target="createProfile"
                    class="w-[310px] max-w-full md:w-[400px] h-[50px] rounded-[8px] flex items-center justify-center bg-[#DD3888] hover:bg-[#CA2474] text-white transition-colors duration-200"
                    style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px;">
                    <span wire:loading.remove wire:target="createProfile">{{ __('front.profiles.form.create_profile_btn') }}</span>
                    <span wire:loading wire:target="createProfile">{{ __('front.profiles.form.saving') }}</span>
                </button>
            </div>
            @endif

            @if($hasProfile)
            <div class="space-y-6">
                <h3 style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62; margin-bottom:15px;">{{ __('front.profiles.form.body') }}</h3>

                <div class="grid grid-cols-1 gap-[10px] !mt-0 !mb-[30px]">
                    <!-- Age -->
                    <div>
                        <label for="age" class="block text-sm font-medium text-gray-700 mb-2">{{ \Illuminate\Support\Str::lcfirst(__('front.profiles.form.age')) }}</label>
                        <input
                            type="number"
                            id="age"
                            wire:model="age"
                            min="0"
                            class="input-control mt-1 @error('age') border-red-500 @enderror">
                        @error('age') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Weight -->
                    <div>
                        <div class="flex items-center justify-between">
                            <label for="weight_kg" class="block text-sm font-medium text-gray-700">{{ \Illuminate\Support\Str::lcfirst(__('front.profiles.form.weight_kg')) }}</label>
                            @if(empty($weight_kg))
                                <span class="flex items-center gap-1">
                                    <img src="{{ asset('images/icons/OctagonAlert.svg') }}" class="w-[14px] h-[14px]" alt="">
                                    <span style="font-family:'Poppins',sans-serif; font-weight:400; font-size:13px; color:#D80027;">{{ __('front.profiles.form.required_field') }}</span>
                                </span>
                            @endif
                        </div>
                        <input
                            type="number"
                            id="weight_kg"
                            wire:model="weight_kg"
                            min="0"
                            class="input-control mt-1 @error('weight_kg') border-red-500 @enderror {{ empty($weight_kg) ? 'border-2 border-[#D80027]' : '' }}">
                        @error('weight_kg') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Height -->
                    <div>
                        <label for="height_cm" class="block text-sm font-medium text-gray-700 mb-2">{{ \Illuminate\Support\Str::lcfirst(__('front.profiles.form.height_cm')) }}</label>
                        <input
                            type="number"
                            id="height_cm"
                            wire:model="height_cm"
                            min="0"
                            class="input-control mt-1 @error('height_cm') border-red-500 @enderror">
                        @error('height_cm') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Bust size -->
                    <div class="relative">
                        <label for="bust_size" class="block text-sm font-medium text-gray-700 mb-2">{{ \Illuminate\Support\Str::lcfirst(__('front.profiles.form.bust')) }}</label>
                        <div class="relative group">
                            <select
                                id="bust_size"
                                wire:model.live="bust_size"
                                class="input-control w-full appearance-none pr-[54px] @error('bust_size') border-red-500 @enderror">
                                <option value="">{{ __('front.profiles.form.selectbust') }}</option>
                                @foreach($this->bustSizeOptions as $size)
                                    <option value="{{ $size }}">{{ $size }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-1 top-1/2 -translate-y-1/2 w-[42px] h-[42px] rounded-[4px] bg-[#DD3888] group-hover:bg-[#CA2474] transition-colors duration-200 flex items-center justify-center pointer-events-none">
                                <svg class="w-[10px] h-[5px] transition-transform duration-200 group-focus-within:-rotate-180" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 1L5 4L9 1" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                        @error('bust_size') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <button type="submit" class="w-[310px] max-w-full md:w-[400px] h-[50px] rounded-[8px] flex items-center justify-center gap-2 bg-[#E8E8E8] hover:bg-[#5C2D62] transition-colors duration-200 group">
                    <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px]" alt="">
                    <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #A4A4A4;">{{ __('front.profiles.form.save_changes') }}</span>
                </button>

                <hr class="w-full min-[1180px]:w-[843px] min-[1180px]:max-w-none relative min-[1180px]:left-1/2 min-[1180px]:-translate-x-1/2 mt-[49px] md:mt-[80px] mb-[48px] md:mb-[52px]">
            </div>
            @endif

            @if($hasProfile)
            <div class="space-y-6">
                <h3 style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62; margin-bottom:15px;">{{ __('front.profiles.form.aboutme') }}</h3>

                <div class="!mb-[27px] md:!mb-[30px]">
                    <label for="about" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.about_label') }}</label>
                    <textarea
                        id="about"
                        wire:model="about"
                        maxlength="640"
                        class="input-control w-[310px] max-w-full md:w-[400px] h-[420px] md:h-[362px] rounded-[8px] resize-none @error('about') border-red-500 @enderror"></textarea>
                    @error('about') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-[310px] max-w-full md:w-[400px] h-[50px] rounded-[8px] flex items-center justify-center gap-2 bg-[#E8E8E8] hover:bg-[#5C2D62] transition-colors duration-200 group">
                    <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px]" alt="">
                    <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #A4A4A4;">{{ __('front.profiles.form.save_changes') }}</span>
                </button>

                <hr class="w-full min-[1180px]:w-[843px] min-[1180px]:max-w-none relative min-[1180px]:left-1/2 min-[1180px]:-translate-x-1/2 mt-[49px] md:mt-[80px] mb-[36px] md:mb-[52px] pf-shift-2">
            </div>
            @endif

            @if($hasProfile)
            <div class="space-y-6">
                <h3 style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62; margin-bottom:30px;">{{ __('front.profiles.form.incall_outcall') }}</h3>

                <div class="space-y-[11px] !mb-[36px]">
                    <div class="flex items-center gap-2">
                        <x-toggle-switch
                            name="incall"
                            id="incall"
                            wire-model="incall"
                            :checked="$incall" />
                        <label for="incall" class="text-sm font-medium text-gray-700">{{ __('front.profiles.form.incall') }}</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-toggle-switch
                            name="outcall"
                            id="outcall"
                            wire-model="outcall"
                            :checked="$outcall" />
                        <label for="outcall" class="text-sm font-medium text-gray-700">{{ __('front.profiles.form.outcall') }}</label>
                    </div>
                </div>

                <button type="submit" class="w-[310px] max-w-full md:w-[400px] h-[50px] rounded-[8px] flex items-center justify-center gap-2 bg-[#E8E8E8] hover:bg-[#5C2D62] transition-colors duration-200 group">
                    <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px]" alt="">
                    <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #A4A4A4;">{{ __('front.profiles.form.save_changes') }}</span>
                </button>

                <hr class="w-full min-[1180px]:w-[843px] min-[1180px]:max-w-none relative min-[1180px]:left-1/2 min-[1180px]:-translate-x-1/2 mt-[49px] md:mt-[80px] mb-[51px] pf-shift-2">
            </div>
            @endif

            @if($hasProfile)
            <div class="w-full min-[1180px]:w-[843px] min-[1180px]:max-w-none relative min-[1180px]:left-1/2 min-[1180px]:-translate-x-1/2 pf-shift-2 space-y-6">
                <div class="flex items-center gap-[17px] max-md:!mb-[17px]" style="margin-bottom:26px;">
                    {{-- home-2: round flag in the Figma flag palette with a soft shadow, half a pixel above the title's centre, title 17px to its right --}}
                    <img src="{{ asset('images/icons/flag-cz-round.svg') }}" alt="CZ" class="w-[30px] h-[30px] rounded-full -translate-y-[0.5px] shadow-[0_0_8px_rgba(0,0,0,0.2)]">
                    <h3 style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">{{ __('front.profiles.form.local_prices') }}</h3>
                </div>

                <!-- Currency -->
                <div class="relative w-[310px] max-w-full md:w-[240px] !mb-[10px] max-md:pb-[20px] max-md:border-b max-md:border-[#E6E6E6]">
                    <label for="local_currency" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.local_currency') }}</label>
                    <div class="relative group max-md:mt-[2px]">
                        <select
                            id="local_currency"
                            wire:model.live="local_currency"
                            class="input-control w-full h-[50px] rounded-[8px] appearance-none pr-[54px]">
                            @foreach($currencyOptions as $currency)
                                <option value="{{ $currency }}">{{ mb_strtoupper($currency) }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-1 top-1/2 -translate-y-1/2 w-[42px] h-[42px] rounded-[4px] bg-[#DD3888] group-hover:bg-[#CA2474] transition-colors duration-200 flex items-center justify-center pointer-events-none">
                            <svg class="w-[10px] h-[5px] transition-transform duration-200 group-focus-within:-rotate-180" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 1L5 4L9 1" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- phone 360 px-13 (pass 2): on mobile the currency field, every price
                     row and the "+ Přidat další" box are closed by a 1px #E6E6E6 rule
                     20px below them; price fields 144px wide, 21px apart. --}}
                <!-- Fixed 30 / 60 minute price rows -->
                <div class="space-y-[10px] !mb-[10px]">
                    <div class="flex flex-wrap items-end gap-4 max-md:gap-x-[21px] max-md:gap-y-[10px] min-[1180px]:flex-nowrap md:gap-[31px] max-md:pb-[20px] max-md:border-b max-md:border-[#E6E6E6]">
                        <div class="w-full md:w-[240px]">
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ \Illuminate\Support\Str::lcfirst(__('front.profiles.form.time_hours_full')) }}</label>
                            <div class="w-full h-[50px] rounded-[8px] flex items-center pl-[17px] bg-white border-2 border-[#E6E6E6] text-[15px] font-bold text-[#505050]" style="font-family:'Poppins',sans-serif;">
                                {{ __('front.profiles.form.time_30min') }}
                            </div>
                        </div>
                        <div class="w-[144px] md:w-[240px]">
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.incall_price_full') }}</label>
                            <input
                                type="number"
                                inputmode="numeric"
                                min="0"
                                step="1"
                                onkeydown="if(event.key === '-' || event.key === 'e' || event.key === '+') event.preventDefault()"
                                wire:model="local_price_30_incall"
                                class="input-control w-full h-[50px] rounded-[8px] @error('local_price_30_incall') border-red-500 @enderror">
                            @error('local_price_30_incall') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="w-[144px] md:w-[240px]">
                            <label class="block text-sm font-medium mb-2 {{ $outcall ? '' : 'pf-label-off' }}">{{ __('front.profiles.form.outcall_price') }}</label>
                            <input
                                type="number"
                                inputmode="numeric"
                                min="0"
                                step="1"
                                onkeydown="if(event.key === '-' || event.key === 'e' || event.key === '+') event.preventDefault()"
                                wire:model="local_price_30_outcall"
                                class="input-control w-full h-[50px] rounded-[8px] @error('local_price_30_outcall') border-red-500 @enderror">
                            @error('local_price_30_outcall') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex flex-wrap items-end gap-4 max-md:gap-x-[21px] max-md:gap-y-[10px] min-[1180px]:flex-nowrap md:gap-[31px] max-md:pb-[20px] max-md:border-b max-md:border-[#E6E6E6]">
                        <div class="w-full md:w-[240px]">
                            <label class="block text-sm font-medium text-gray-700 mb-2"><span class="md:hidden">{{ \Illuminate\Support\Str::lcfirst(__('front.profiles.form.time_hours_full')) }}</span><span class="hidden md:inline">{{ \Illuminate\Support\Str::lcfirst(__('front.profiles.form.time_hours')) }}</span></label>
                            <div class="w-full h-[50px] rounded-[8px] flex items-center pl-[17px] bg-white border-2 border-[#E6E6E6] text-[15px] font-bold text-[#505050]" style="font-family:'Poppins',sans-serif;">
                                {{ __('front.profiles.form.time_60min') }}
                            </div>
                        </div>
                        <div class="w-[144px] md:w-[240px]">
                            <label class="block text-sm font-medium text-gray-700 mb-2"><span class="md:hidden">{{ __('front.profiles.form.incall_price_full') }}</span><span class="hidden md:inline">{{ __('front.profiles.form.incall_price') }}</span></label>
                            <input
                                type="number"
                                inputmode="numeric"
                                min="0"
                                step="1"
                                onkeydown="if(event.key === '-' || event.key === 'e' || event.key === '+') event.preventDefault()"
                                wire:model="local_price_60_incall"
                                class="input-control w-full h-[50px] rounded-[8px] @error('local_price_60_incall') border-red-500 @enderror">
                            @error('local_price_60_incall') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="w-[144px] md:w-[240px]">
                            <label class="block text-sm font-medium mb-2 {{ $outcall ? '' : 'pf-label-off' }}">{{ __('front.profiles.form.outcall_price') }}</label>
                            <input
                                type="number"
                                inputmode="numeric"
                                min="0"
                                step="1"
                                onkeydown="if(event.key === '-' || event.key === 'e' || event.key === '+') event.preventDefault()"
                                wire:model="local_price_60_outcall"
                                class="input-control w-full h-[50px] rounded-[8px] @error('local_price_60_outcall') border-red-500 @enderror">
                            @error('local_price_60_outcall') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Custom duration price rows -->
                <div class="space-y-[10px] !mb-[30px] max-md:!mb-[20px]">
                    @foreach($local_prices as $index => $price)
                        <div wire:key="local-price-{{ $index }}" class="flex flex-wrap items-end gap-4 max-md:gap-x-[21px] max-md:gap-y-[10px] min-[1180px]:flex-nowrap md:gap-[31px] max-md:pb-[20px] max-md:border-b max-md:border-[#E6E6E6]">
                            <div class="w-full md:w-[240px]">
                                <label class="block text-sm font-medium text-gray-700 mb-2"><span class="md:hidden">{{ \Illuminate\Support\Str::lcfirst(__('front.profiles.form.time_hours_full')) }}</span><span class="hidden md:inline">{{ \Illuminate\Support\Str::lcfirst(__('front.profiles.form.time_hours')) }}</span></label>
                                <div class="relative">
                                    <select
                                        wire:model="local_prices.{{ $index }}.time_hours"
                                        class="input-control w-full h-[50px] rounded-[8px] appearance-none pr-[54px] @error('local_prices.'.$index.'.time_hours') border-red-500 @enderror">
                                        <option value="">&mdash;</option>
                                        @foreach($this->durationOptions($price['time_hours'] ?? null) as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="hidden absolute right-1 top-1/2 -translate-y-1/2 w-[42px] h-[42px] rounded-[4px] bg-[#DD3888] items-center justify-center pointer-events-none">
                                        <svg class="w-[10px] h-[5px]" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 1L5 4L9 1" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="removeLocalPrice({{ $index }})"
                                        class="md:hidden absolute right-2 top-1/2 -translate-y-1/2 w-[35px] h-[35px] rounded-full bg-[#DD3888] hover:bg-[#CA2474] transition-colors duration-200 flex items-center justify-center">
                                        <svg class="w-[10px] h-[10px]" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                </div>
                                @error('local_prices.'.$index.'.time_hours') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="w-[144px] md:w-[240px]">
                                <label class="block text-sm font-medium text-gray-700 mb-2"><span class="md:hidden">{{ __('front.profiles.form.incall_price_full') }}</span><span class="hidden md:inline">{{ __('front.profiles.form.incall_price') }}</span></label>
                                <input
                                    type="number"
                                    inputmode="numeric"
                                    min="0"
                                    step="1"
                                    onkeydown="if(event.key === '-' || event.key === 'e' || event.key === '+') event.preventDefault()"
                                    wire:model="local_prices.{{ $index }}.incall_price"
                                    class="input-control w-full h-[50px] rounded-[8px] @error('local_prices.'.$index.'.incall_price') border-red-500 @enderror">
                                @error('local_prices.'.$index.'.incall_price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="w-[144px] md:w-[240px]">
                                <label class="block text-sm font-medium mb-2 {{ $outcall ? '' : 'pf-label-off' }}">{{ __('front.profiles.form.outcall_price') }}</label>
                                <input
                                    type="number"
                                    inputmode="numeric"
                                    min="0"
                                    step="1"
                                    onkeydown="if(event.key === '-' || event.key === 'e' || event.key === '+') event.preventDefault()"
                                    wire:model="local_prices.{{ $index }}.outcall_price"
                                    class="input-control w-full h-[50px] rounded-[8px] @error('local_prices.'.$index.'.outcall_price') border-red-500 @enderror">
                                @error('local_prices.'.$index.'.outcall_price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <button
                                type="button"
                                wire:click="removeLocalPrice({{ $index }})"
                                class="hidden md:flex w-[35px] h-[35px] md:-ml-[5px] rounded-full bg-[#DD3888] hover:bg-[#CA2474] transition-colors duration-200 items-center justify-center flex-shrink-0 mb-[7px]">
                                <svg class="w-[10px] h-[10px]" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>

                <button
                    type="button"
                    wire:click="addLocalPrice"
                    class="relative max-md:after:content-[''] max-md:after:absolute max-md:after:inset-x-0 max-md:after:-bottom-[21px] max-md:after:h-px max-md:after:bg-[#E6E6E6] w-full md:w-[782px] max-w-full !mb-[41px] md:!mb-[39px] md:h-[50px] rounded-lg py-3 text-center font-semibold text-[#5C2D62] transition-colors duration-200"
                    style="background-color: #F8F9F9;"
                    onmouseover="this.style.backgroundColor='#EDEDED'"
                    onmouseout="this.style.backgroundColor='#F8F9F9'">
                    {{ __('front.profiles.form.add_local_price') }}
                </button>

                <button type="submit" class="w-[310px] max-w-full md:w-[240px] h-[50px] rounded-[8px] flex items-center justify-center gap-2 bg-[#E8E8E8] hover:bg-[#5C2D62] transition-colors duration-200 group">
                    <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px]" alt="">
                    <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #A4A4A4;">{{ __('front.profiles.form.save_changes') }}</span>
                </button>

                <hr class="w-full mt-[49px] md:mt-[80px] mb-[46px] md:mb-[43px]">
            </div>
            @endif

            @if($hasProfile)
            <div class="w-full min-[1180px]:w-[843px] min-[1180px]:max-w-none relative min-[1180px]:left-1/2 min-[1180px]:-translate-x-1/2 pf-shift-2 space-y-6">
                <div class="flex items-center gap-[17px] max-md:!mb-[14px]" style="margin-bottom:26px;">
                    {{-- home-2: round flag in the Figma flag palette with a soft shadow, half a pixel above the title's centre, title 17px to its right --}}
                    <img src="{{ asset('images/icons/flag-eu-round.svg') }}" alt="EU" class="w-[30px] h-[30px] rounded-full -translate-y-[0.5px] shadow-[0_0_8px_rgba(0,0,0,0.2)]">
                    <h3 style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">{{ __('front.profiles.form.global_prices') }}</h3>
                </div>

                <!-- Currency -->
                <div class="relative w-[310px] max-w-full md:w-[240px] !mb-[10px] max-md:pb-[20px] max-md:border-b max-md:border-[#E6E6E6]">
                    <label for="global_currency" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.global_currency') }}</label>
                    <div class="relative group max-md:mt-[2px]">
                        <select
                            id="global_currency"
                            wire:model.live="global_currency"
                            class="input-control w-full h-[50px] rounded-[8px] appearance-none pr-[54px]">
                            @foreach($globalCurrencyOptions as $currency)
                                <option value="{{ $currency }}">{{ mb_strtoupper($currency) }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-1 top-1/2 -translate-y-1/2 w-[42px] h-[42px] rounded-[4px] bg-[#DD3888] group-hover:bg-[#CA2474] transition-colors duration-200 flex items-center justify-center pointer-events-none">
                            <svg class="w-[10px] h-[5px] transition-transform duration-200 group-focus-within:-rotate-180" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 1L5 4L9 1" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Fixed 30 / 60 minute price rows -->
                <div class="space-y-[10px] !mb-[10px]">
                    <div class="flex flex-wrap items-end gap-4 max-md:gap-x-[21px] max-md:gap-y-[10px] min-[1180px]:flex-nowrap md:gap-[31px] max-md:pb-[20px] max-md:border-b max-md:border-[#E6E6E6]">
                        <div class="w-full md:w-[240px]">
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ \Illuminate\Support\Str::lcfirst(__('front.profiles.form.time_hours_full')) }}</label>
                            <div class="w-full h-[50px] rounded-[8px] flex items-center pl-[17px] bg-white border-2 border-[#E6E6E6] text-[15px] font-bold text-[#505050]" style="font-family:'Poppins',sans-serif;">
                                {{ __('front.profiles.form.time_30min') }}
                            </div>
                        </div>
                        <div class="w-[144px] md:w-[240px]">
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.incall_price_full_intl') }}</label>
                            <input
                                type="number"
                                inputmode="numeric"
                                min="0"
                                step="1"
                                onkeydown="if(event.key === '-' || event.key === 'e' || event.key === '+') event.preventDefault()"
                                wire:model="global_price_30_incall"
                                class="input-control w-full h-[50px] rounded-[8px] @error('global_price_30_incall') border-red-500 @enderror">
                            @error('global_price_30_incall') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="w-[144px] md:w-[240px]">
                            <label class="block text-sm font-medium mb-2 {{ $outcall ? '' : 'pf-label-off' }}"><span class="md:hidden">{{ __('front.profiles.form.outcall_price') }}</span><span class="hidden md:inline">{{ __('front.profiles.form.outcall_price_intl') }}</span></label>
                            <input
                                type="number"
                                inputmode="numeric"
                                min="0"
                                step="1"
                                onkeydown="if(event.key === '-' || event.key === 'e' || event.key === '+') event.preventDefault()"
                                wire:model="global_price_30_outcall"
                                class="input-control w-full h-[50px] rounded-[8px] @error('global_price_30_outcall') border-red-500 @enderror">
                            @error('global_price_30_outcall') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex flex-wrap items-end gap-4 max-md:gap-x-[21px] max-md:gap-y-[10px] min-[1180px]:flex-nowrap md:gap-[31px] max-md:pb-[20px] max-md:border-b max-md:border-[#E6E6E6]">
                        <div class="w-full md:w-[240px]">
                            <label class="block text-sm font-medium text-gray-700 mb-2"><span class="md:hidden">{{ \Illuminate\Support\Str::lcfirst(__('front.profiles.form.time_hours_full')) }}</span><span class="hidden md:inline">{{ \Illuminate\Support\Str::lcfirst(__('front.profiles.form.time_hours')) }}</span></label>
                            <div class="w-full h-[50px] rounded-[8px] flex items-center pl-[17px] bg-white border-2 border-[#E6E6E6] text-[15px] font-bold text-[#505050]" style="font-family:'Poppins',sans-serif;">
                                {{ __('front.profiles.form.time_60min') }}
                            </div>
                        </div>
                        <div class="w-[144px] md:w-[240px]">
                            <label class="block text-sm font-medium text-gray-700 mb-2"><span class="md:hidden">{{ __('front.profiles.form.incall_price_full_intl') }}</span><span class="hidden md:inline">{{ __('front.profiles.form.incall_price') }}</span></label>
                            <input
                                type="number"
                                inputmode="numeric"
                                min="0"
                                step="1"
                                onkeydown="if(event.key === '-' || event.key === 'e' || event.key === '+') event.preventDefault()"
                                wire:model="global_price_60_incall"
                                class="input-control w-full h-[50px] rounded-[8px] @error('global_price_60_incall') border-red-500 @enderror">
                            @error('global_price_60_incall') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="w-[144px] md:w-[240px]">
                            <label class="block text-sm font-medium mb-2 {{ $outcall ? '' : 'pf-label-off' }}"><span class="md:hidden">{{ __('front.profiles.form.outcall_price') }}</span><span class="hidden md:inline">{{ __('front.profiles.form.outcall_price_intl') }}</span></label>
                            <input
                                type="number"
                                inputmode="numeric"
                                min="0"
                                step="1"
                                onkeydown="if(event.key === '-' || event.key === 'e' || event.key === '+') event.preventDefault()"
                                wire:model="global_price_60_outcall"
                                class="input-control w-full h-[50px] rounded-[8px] @error('global_price_60_outcall') border-red-500 @enderror">
                            @error('global_price_60_outcall') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Custom duration price rows -->
                <div class="space-y-[10px] max-md:!mb-[20px] md:!mb-[30px]">
                    @foreach($global_prices as $index => $price)
                        <div wire:key="global-price-{{ $index }}" class="flex flex-wrap items-end gap-4 max-md:gap-x-[21px] max-md:gap-y-[10px] min-[1180px]:flex-nowrap md:gap-[31px] max-md:pb-[20px] max-md:border-b max-md:border-[#E6E6E6]">
                            <div class="w-full md:w-[240px]">
                                <label class="block text-sm font-medium text-gray-700 mb-2"><span class="md:hidden">{{ \Illuminate\Support\Str::lcfirst(__('front.profiles.form.time_hours_full')) }}</span><span class="hidden md:inline">{{ \Illuminate\Support\Str::lcfirst(__('front.profiles.form.time_hours')) }}</span></label>
                                <div class="relative">
                                    <select
                                        wire:model="global_prices.{{ $index }}.time_hours"
                                        class="input-control w-full h-[50px] rounded-[8px] appearance-none pr-[54px] @error('global_prices.'.$index.'.time_hours') border-red-500 @enderror">
                                        <option value="">&mdash;</option>
                                        @foreach($this->durationOptions($price['time_hours'] ?? null) as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="hidden absolute right-1 top-1/2 -translate-y-1/2 w-[42px] h-[42px] rounded-[4px] bg-[#DD3888] items-center justify-center pointer-events-none">
                                        <svg class="w-[10px] h-[5px]" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 1L5 4L9 1" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="removeGlobalPrice({{ $index }})"
                                        class="md:hidden absolute right-2 top-1/2 -translate-y-1/2 w-[35px] h-[35px] rounded-full bg-[#DD3888] hover:bg-[#CA2474] transition-colors duration-200 flex items-center justify-center">
                                        <svg class="w-[10px] h-[10px]" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                </div>
                                @error('global_prices.'.$index.'.time_hours') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="w-[144px] md:w-[240px]">
                                <label class="block text-sm font-medium text-gray-700 mb-2"><span class="md:hidden">{{ __('front.profiles.form.incall_price_full_intl') }}</span><span class="hidden md:inline">{{ __('front.profiles.form.incall_price') }}</span></label>
                                <input
                                    type="number"
                                    inputmode="numeric"
                                    min="0"
                                    step="1"
                                    onkeydown="if(event.key === '-' || event.key === 'e' || event.key === '+') event.preventDefault()"
                                    wire:model="global_prices.{{ $index }}.incall_price"
                                    class="input-control w-full h-[50px] rounded-[8px] @error('global_prices.'.$index.'.incall_price') border-red-500 @enderror">
                                @error('global_prices.'.$index.'.incall_price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="w-[144px] md:w-[240px]">
                                <label class="block text-sm font-medium mb-2 {{ $outcall ? '' : 'pf-label-off' }}"><span class="md:hidden">{{ __('front.profiles.form.outcall_price') }}</span><span class="hidden md:inline">{{ __('front.profiles.form.outcall_price_intl') }}</span></label>
                                <input
                                    type="number"
                                    inputmode="numeric"
                                    min="0"
                                    step="1"
                                    onkeydown="if(event.key === '-' || event.key === 'e' || event.key === '+') event.preventDefault()"
                                    wire:model="global_prices.{{ $index }}.outcall_price"
                                    class="input-control w-full h-[50px] rounded-[8px] @error('global_prices.'.$index.'.outcall_price') border-red-500 @enderror">
                                @error('global_prices.'.$index.'.outcall_price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <button
                                type="button"
                                wire:click="removeGlobalPrice({{ $index }})"
                                class="hidden md:flex w-[35px] h-[35px] md:-ml-[5px] rounded-full bg-[#DD3888] hover:bg-[#CA2474] transition-colors duration-200 items-center justify-center flex-shrink-0 mb-[7px]">
                                <svg class="w-[10px] h-[10px]" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>

                <button
                    type="button"
                    wire:click="addGlobalPrice"
                    class="relative max-md:after:content-[''] max-md:after:absolute max-md:after:inset-x-0 max-md:after:-bottom-[21px] max-md:after:h-px max-md:after:bg-[#E6E6E6] w-full md:w-[782px] max-w-full !mb-[41px] md:!mb-[39px] md:h-[50px] rounded-lg py-3 text-center font-semibold text-[#5C2D62] transition-colors duration-200"
                    style="background-color: #F8F9F9;"
                    onmouseover="this.style.backgroundColor='#EDEDED'"
                    onmouseout="this.style.backgroundColor='#F8F9F9'">
                    {{ __('front.profiles.form.add_local_price') }}
                </button>

                <button type="submit" class="!mb-[56px] md:!mb-[30px] w-[310px] max-w-full md:w-[240px] h-[50px] rounded-[8px] flex items-center justify-center gap-2 bg-[#E8E8E8] hover:bg-[#5C2D62] transition-colors duration-200 group">
                    <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px]" alt="">
                    <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #A4A4A4;">{{ __('front.profiles.form.save_changes') }}</span>
                </button>

                {{-- Figma home-2 ends the EU block with the save button (no
                     rule); its bottom is 157px above the footer logo glyph
                     (pass 1: footer now starts 126px above the glyph). --}}
            </div>
            @endif

        </div>

    </div>
    </form>

</div>
