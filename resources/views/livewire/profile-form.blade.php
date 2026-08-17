<div>


    @if (session()->has('message'))
    <div class="relative mx-auto w-[310px] h-[110px] px-4 py-3 md:w-full md:h-[50px] md:mx-0 md:px-4 md:py-0 bg-[#01B810] rounded-[8px] flex items-center justify-center mb-4">
        <img src="{{ asset('images/icons/SaveWhite.svg') }}" class="w-[20px] h-[20px] mr-3" alt="Save">
        <span class="font-medium text-[14px] text-white text-center" style="font-family: 'Poppins', sans-serif;">{{ session('message') }}</span>
        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 md:right-4" onclick="this.parentElement.remove()">
            <svg class="w-[10px] h-[10px]" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </button>
    </div>
    @endif



    <form wire:submit="save" class="space-y-8">
    <div class="profile-form-narrow w-[312px] md:w-[400px] max-w-full mx-auto space-y-8">
        <!-- Personal Information Section -->
        <div class="space-y-6">
            <h3 style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62; margin-bottom:19px;">{{ __('front.profiles.form.my_data') }}</h3>

            <div class="grid grid-cols-1 gap-[10px] !mt-0">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.nickname') }}</label>
                    <input
                        type="text"
                        id="name"
                        wire:model="name"
                        class="input-control mt-1 @error('name') border-red-500 @enderror">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
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
                        <div class="flex items-center justify-between mb-2">
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
                                <option value="">{{ __('front.profiles.form.city') }}</option>
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
            <div class="space-y-[13px]">
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

            <button type="submit" class="w-[312px] md:w-[400px] h-[50px] rounded-[8px] flex items-center justify-center gap-2 bg-[#E8E8E8] hover:bg-[#5C2D62] transition-colors duration-200 group">
                <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px] group-hover:hidden" alt="Save">
                <img src="{{ asset('images/icons/SaveWhite.svg') }}" class="w-[20px] h-[20px] hidden group-hover:block" alt="Save">
                <span class="text-[#A4A4A4] group-hover:text-white" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px;">{{ __('front.profiles.form.save_changes') }}</span>
            </button>

            <hr class="w-[843px] max-w-none relative left-1/2 -translate-x-1/2 mt-[80px] mb-[50px]">

            @if($hasProfile)
            <div class="space-y-6">
                <h3 style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62; margin-bottom:19px;">{{ __('front.profiles.form.body') }}</h3>

                <div class="grid grid-cols-1 gap-[10px] !mt-0">
                    <!-- Age -->
                    <div>
                        <label for="age" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.age') }}</label>
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
                        <div class="flex items-center justify-between mb-2">
                            <label for="weight_kg" class="block text-sm font-medium text-gray-700">{{ __('front.profiles.form.weight_kg') }}</label>
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
                        <label for="height_cm" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.height_cm') }}</label>
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
                        <label for="bust_size" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.bust') }}</label>
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

                <button type="submit" class="w-[312px] md:w-[400px] h-[50px] rounded-[8px] flex items-center justify-center gap-2 bg-[#E8E8E8] hover:bg-[#5C2D62] transition-colors duration-200 group">
                    <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px]" alt="Save">
                    <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #A4A4A4;">{{ __('front.profiles.form.save_changes') }}</span>
                </button>

                <hr class="w-[843px] max-w-none relative left-1/2 -translate-x-1/2 mt-[80px] mb-[50px]">
            </div>
            @endif

            @if($hasProfile)
            <div class="space-y-6">
                <h3 style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62; margin-bottom:19px;">{{ __('front.profiles.form.aboutme') }}</h3>

                <div>
                    <label for="about" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.about_label') }}</label>
                    <textarea
                        id="about"
                        wire:model="about"
                        maxlength="640"
                        class="input-control w-[312px] md:w-[400px] h-[396px] rounded-[8px] resize-none @error('about') border-red-500 @enderror"></textarea>
                    @error('about') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-[312px] md:w-[400px] h-[50px] rounded-[8px] flex items-center justify-center gap-2 bg-[#E8E8E8] hover:bg-[#5C2D62] transition-colors duration-200 group">
                    <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px]" alt="Save">
                    <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #A4A4A4;">{{ __('front.profiles.form.save_changes') }}</span>
                </button>

                <hr class="w-[843px] max-w-none relative left-1/2 -translate-x-1/2 mt-[80px] mb-[50px]">
            </div>
            @endif

            @if($hasProfile)
            <div class="space-y-6">
                <h3 style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62; margin-bottom:19px;">{{ __('front.profiles.form.incall_outcall') }}</h3>

                <div class="space-y-[13px]">
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

                <button type="submit" class="w-[312px] md:w-[400px] h-[50px] rounded-[8px] flex items-center justify-center gap-2 bg-[#E8E8E8] hover:bg-[#5C2D62] transition-colors duration-200 group">
                    <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px]" alt="Save">
                    <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #A4A4A4;">{{ __('front.profiles.form.save_changes') }}</span>
                </button>

                <hr class="w-[843px] max-w-none relative left-1/2 -translate-x-1/2 mt-[80px] mb-[50px]">
            </div>
            @endif

            @if($hasProfile)
            <div class="w-full md:w-[843px] md:max-w-none relative md:left-1/2 md:-translate-x-1/2 space-y-6">
                <div class="flex items-center gap-2" style="margin-bottom:19px;">
                    <img src="https://flagcdn.com/cz.svg" alt="CZ" class="w-[30px] h-[30px] rounded-full object-cover">
                    <h3 style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">{{ __('front.profiles.form.local_prices') }}</h3>
                </div>

                <!-- Currency -->
                <div class="relative w-[312px] md:w-[240px]">
                    <label for="local_currency" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.local_currency') }}</label>
                    <div class="relative group">
                        <select
                            id="local_currency"
                            wire:model.live="local_currency"
                            class="input-control w-full h-[50px] rounded-[8px] appearance-none pr-[54px]">
                            @foreach($currencyOptions as $currency)
                                <option value="{{ $currency }}">{{ $currency }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-1 top-1/2 -translate-y-1/2 w-[42px] h-[42px] rounded-[4px] bg-[#DD3888] group-hover:bg-[#CA2474] transition-colors duration-200 flex items-center justify-center pointer-events-none">
                            <svg class="w-[10px] h-[5px] transition-transform duration-200 group-focus-within:-rotate-180" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 1L5 4L9 1" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Price rows -->
                <div class="space-y-4">
                    @foreach($local_prices as $index => $price)
                        <div wire:key="local-price-{{ $index }}" class="flex flex-wrap items-end gap-4 md:flex-nowrap md:gap-[31px]">
                            <div class="w-full md:w-[240px]">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ $loop->first ? __('front.profiles.form.time_hours_full') : __('front.profiles.form.time_hours') }}</label>
                                <div class="relative">
                                    <input
                                        type="number"
                                        inputmode="decimal"
                                        min="0"
                                        max="24"
                                        step="0.5"
                                        onkeydown="if(event.key === '-' || event.key === 'e' || event.key === '+') event.preventDefault()"
                                        wire:model="local_prices.{{ $index }}.time_hours"
                                        class="input-control w-full h-[50px] rounded-[8px] pr-[54px] md:pr-5 @error('local_prices.'.$index.'.time_hours') border-red-500 @enderror">
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
                            <div class="w-[143px] md:w-[240px]">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ $loop->first ? __('front.profiles.form.incall_price_full') : __('front.profiles.form.incall_price') }}</label>
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
                            <div class="w-[143px] md:w-[240px]">
                                <label class="block text-sm font-medium text-gray-400 mb-2">{{ __('front.profiles.form.outcall_price') }}</label>
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
                                class="hidden md:flex w-[35px] h-[35px] rounded-full bg-[#DD3888] hover:bg-[#CA2474] transition-colors duration-200 items-center justify-center flex-shrink-0 mb-[7px]">
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
                    class="w-full md:w-[782px] rounded-lg py-3 text-center font-semibold text-[#5C2D62] transition-colors duration-200"
                    style="background-color: #F8F9F9;"
                    onmouseover="this.style.backgroundColor='#EDEDED'"
                    onmouseout="this.style.backgroundColor='#F8F9F9'">
                    {{ __('front.profiles.form.add_local_price') }}
                </button>

                <button type="submit" class="w-[312px] md:w-[240px] h-[50px] rounded-[8px] flex items-center justify-center gap-2 bg-[#E8E8E8] hover:bg-[#5C2D62] transition-colors duration-200 group">
                    <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px]" alt="Save">
                    <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #A4A4A4;">{{ __('front.profiles.form.save_changes') }}</span>
                </button>

                <hr class="w-full mt-[80px] mb-[50px]">
            </div>
            @endif

            @if($hasProfile)
            <div class="w-full md:w-[843px] md:max-w-none relative md:left-1/2 md:-translate-x-1/2 space-y-6">
                <div class="flex items-center gap-2" style="margin-bottom:19px;">
                    <img src="https://flagcdn.com/eu.svg" alt="EU" class="w-[30px] h-[30px] rounded-full object-cover">
                    <h3 style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">{{ __('front.profiles.form.global_prices') }}</h3>
                </div>

                <!-- Currency -->
                <div class="relative w-[312px] md:w-[240px]">
                    <label for="global_currency" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.global_currency') }}</label>
                    <div class="relative group">
                        <select
                            id="global_currency"
                            wire:model.live="global_currency"
                            class="input-control w-full h-[50px] rounded-[8px] appearance-none pr-[54px]">
                            @foreach($globalCurrencyOptions as $currency)
                                <option value="{{ $currency }}">{{ $currency }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-1 top-1/2 -translate-y-1/2 w-[42px] h-[42px] rounded-[4px] bg-[#DD3888] group-hover:bg-[#CA2474] transition-colors duration-200 flex items-center justify-center pointer-events-none">
                            <svg class="w-[10px] h-[5px] transition-transform duration-200 group-focus-within:-rotate-180" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 1L5 4L9 1" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Price rows -->
                <div class="space-y-4">
                    @foreach($global_prices as $index => $price)
                        <div wire:key="global-price-{{ $index }}" class="flex flex-wrap items-end gap-4 md:flex-nowrap md:gap-[31px]">
                            <div class="w-full md:w-[240px]">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ $loop->first ? __('front.profiles.form.time_hours_full') : __('front.profiles.form.time_hours') }}</label>
                                <div class="relative">
                                    <input
                                        type="number"
                                        inputmode="decimal"
                                        min="0"
                                        max="24"
                                        step="0.5"
                                        onkeydown="if(event.key === '-' || event.key === 'e' || event.key === '+') event.preventDefault()"
                                        wire:model="global_prices.{{ $index }}.time_hours"
                                        class="input-control w-full h-[50px] rounded-[8px] pr-[54px] md:pr-5 @error('global_prices.'.$index.'.time_hours') border-red-500 @enderror">
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
                            <div class="w-[143px] md:w-[240px]">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ $loop->first ? __('front.profiles.form.incall_price_full_intl') : __('front.profiles.form.incall_price') }}</label>
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
                            <div class="w-[143px] md:w-[240px]">
                                <label class="block text-sm font-medium text-gray-400 mb-2">{{ __('front.profiles.form.outcall_price_intl') }}</label>
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
                                class="hidden md:flex w-[35px] h-[35px] rounded-full bg-[#DD3888] hover:bg-[#CA2474] transition-colors duration-200 items-center justify-center flex-shrink-0 mb-[7px]">
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
                    class="w-full md:w-[782px] rounded-lg py-3 text-center font-semibold text-[#5C2D62] transition-colors duration-200"
                    style="background-color: #F8F9F9;"
                    onmouseover="this.style.backgroundColor='#EDEDED'"
                    onmouseout="this.style.backgroundColor='#F8F9F9'">
                    {{ __('front.profiles.form.add_local_price') }}
                </button>

                <button type="submit" class="w-[312px] md:w-[240px] h-[50px] rounded-[8px] flex items-center justify-center gap-2 bg-[#E8E8E8] hover:bg-[#5C2D62] transition-colors duration-200 group">
                    <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px]" alt="Save">
                    <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #A4A4A4;">{{ __('front.profiles.form.save_changes') }}</span>
                </button>

                <hr class="w-full mt-[80px] mb-[50px]">
            </div>
            @endif

        </div>

    </div>
    </form>

</div>
