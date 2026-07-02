<div>


    @if (session()->has('message'))
    <div class="alert alert-success flex items-center justify-between my-3">
        <div class="flex items-center font-semibold">
            <x-icons name="bell" class="w-5 h-5 mr-2.5" />
            <span>{{ session('message') }}</span>
        </div>
        <button type="button" class="flex items-center ml-2 text-gray-400 hover:text-gray-600" onclick="this.parentElement.remove()">
            <x-icons name="cross" class="text-green-800 w-3 h-3" />
        </button>
    </div>
    @endif



    <form wire:submit="save" class="space-y-8">
        <!-- Personal Information Section -->
        <div class="space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.name') }} *</label>
                    <input
                        type="text"
                        id="name"
                        wire:model="name"
                        class="input-control mt-1 @error('name') border-red-500 @enderror"
                        placeholder="{{ __('front.profiles.form.name') }}">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.email') }} *</label>
                    <input
                        type="email"
                        id="email"
                        value="{{ $email }}"
                        disabled
                        class="input-control mt-1 bg-gray-50 text-gray-500 cursor-not-allowed"
                        placeholder="{{ __('front.profiles.form.email_placeholder') }}">
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.phone') }}</label>
                    <input
                        type="tel"
                        id="phone"
                        wire:model="phone"
                        class="input-control mt-1 @error('phone') border-red-500 @enderror"
                        placeholder="+420 123 456 789">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Save Basic Info Button -->
            <div class="flex justify-end">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    class="btn-primary btn-small justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span wire:loading.remove>{{ __('front.profiles.form.savechanges') }}</span>
                    <span wire:loading>{{ __('front.profiles.form.saving') }}</span>
                </button>
            </div>

            <!-- Email Change Section -->
            <div x-data="{ expanded: false }" class="mt-6">
                <button
                    type="button"
                    @click="expanded = !expanded"
                    class="w-full flex items-center justify-between py-3 px-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        {{ __('front.profiles.form.change_email') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div
                    x-show="expanded"
                    x-collapse
                    x-cloak
                    class="mt-4 space-y-4 px-1">
                    <!-- New Email -->
                    <div>
                        <label for="new_email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.new_email') }}</label>
                        <input
                            type="email"
                            id="new_email"
                            wire:model="new_email"
                            class="input-control mt-1 @error('new_email') border-red-500 @enderror"
                            placeholder="{{ __('front.profiles.form.email_placeholder') }}">
                        @error('new_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Password Confirmation for Email Change -->
                    <div>
                        <label for="email_change_password" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.password_to_confirm') }}</label>
                        <input
                            type="password"
                            id="email_change_password"
                            wire:model="email_change_password"
                            class="input-control mt-1 @error('email_change_password') border-red-500 @enderror"
                            placeholder="••••••••">
                        @error('email_change_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-2 text-xs text-gray-500">{{ __('front.profiles.form.email_change_notice') }}</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-4">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="btn-primary btn-small justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span wire:loading.remove>{{ __('front.profiles.form.savechanges') }}</span>
                            <span wire:loading>{{ __('front.profiles.form.saving') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Password Reset Section -->
            <div x-data="{ expanded: false }" class="mt-6">
                <button
                    type="button"
                    @click="expanded = !expanded"
                    class="w-full flex items-center justify-between py-3 px-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        {{ __('front.profiles.form.change_password') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div
                    x-show="expanded"
                    x-collapse
                    x-cloak
                    class="mt-4 space-y-4 px-1">
                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.current_password') }}</label>
                        <input
                            type="password"
                            id="current_password"
                            wire:model="current_password"
                            class="input-control mt-1 @error('current_password') border-red-500 @enderror"
                            placeholder="••••••••">
                        @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- New Password -->
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.new_password') }}</label>
                            <input
                                type="password"
                                id="new_password"
                                wire:model="new_password"
                                class="input-control mt-1 @error('new_password') border-red-500 @enderror"
                                placeholder="••••••••">
                            @error('new_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.confirm_password') }}</label>
                            <input
                                type="password"
                                id="new_password_confirmation"
                                wire:model="new_password_confirmation"
                                class="input-control mt-1 @error('new_password_confirmation') border-red-500 @enderror"
                                placeholder="••••••••">
                            @error('new_password_confirmation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-4">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="btn-primary btn-small justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span wire:loading.remove>{{ __('front.profiles.form.savechanges') }}</span>
                            <span wire:loading>{{ __('front.profiles.form.saving') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Section -->
        <div class="space-y-6 pt-10">
            <h3 class="text-xl font-semibold text-gray-900 border-b border-gray-200 pb-2">{{ __('front.profiles.form.profile') }}</h3>

            @if($hasProfile)
            <div class="space-y-6">
                <!-- Display Name -->
                <div>
                    <label for="display_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.displayname') }} *</label>
                    <input
                        type="text"
                        id="display_name"
                        wire:model="display_name"
                        class="input-control mt-1 @error('display_name') border-red-500 @enderror {{ $this->shouldShowPublishRequirement('display_name') ? 'border-red-500' : '' }}"
                        placeholder="{{ __('front.profiles.form.displayname') }}">
                    @error('display_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @if($this->shouldShowPublishRequirement('display_name'))
                        <p class="mt-1 text-sm text-red-600">{{ __('front.profiles.form.publish_required') }}</p>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Age -->
                    <div>
                        <label for="age" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.age') }} *</label>
                        <input
                            type="number"
                            id="age"
                            wire:model="age"
                            min="18"
                            max="120"
                            class="input-control mt-1 @error('age') border-red-500 @enderror {{ $this->shouldShowPublishRequirement('age') ? 'border-red-500' : '' }}"
                            placeholder="25">
                        @error('age') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        @if($this->shouldShowPublishRequirement('age'))
                            <p class="mt-1 text-sm text-red-600">{{ __('front.profiles.form.publish_required') }}</p>
                        @endif
                    </div>

                    <!-- City -->
                    <div x-data="{ open: @entangle('cityDropdownOpen') }" class="relative">
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.city') }}</label>
                        <div class="relative">
                            <input
                                type="text"
                                id="city"
                                wire:model.live.debounce.300ms="citySearchTerm"
                                @focus="open = true"
                                @click="open = true"
                                :disabled="!$wire.country_code"
                                class="input-control mt-1 @error('city') border-red-500 @enderror"
                                :class="{ 'bg-gray-100 cursor-not-allowed': !$wire.country_code }"
                                placeholder="{{ __('front.profiles.form.city') }}">
                            @if(!$country_code)
                                <p class="mt-1 text-xs text-gray-500">{{ __('front.profiles.form.select_country_first') }}</p>
                            @endif
                        </div>
                        
                        <!-- City Suggestions Dropdown -->
                        <div
                            x-show="open && $wire.country_code"
                            @click.away="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
                            style="display: none;">
                            @if(count($citySuggestions) > 0)
                                @foreach($citySuggestions as $suggestion)
                                    <button
                                        type="button"
                                        wire:click="selectCity('{{ addslashes($suggestion) }}')"
                                        class="w-full px-4 py-2 text-left hover:bg-gray-50 flex items-center gap-2"
                                        :class="{ 'bg-pink-50': '{{ addslashes($suggestion) }}' === '{{ addslashes($city) }}' }">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span>{{ $suggestion }}</span>
                                    </button>
                                @endforeach
                            @elseif(strlen($citySearchTerm) >= 2)
                                <div class="px-4 py-3 text-sm text-gray-500 text-center">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    {{ __('front.profiles.form.city_no_results') }}
                                </div>
                            @endif
                        </div>
                        @error('city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Country -->
                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.country') }} *</label>
                    <div x-data="{ open: @entangle('countryDropdownOpen'), searchTerm: @entangle('countrySearchTerm') }" class="relative">
                        <button
                            type="button"
                            @click="open = !open"
                            class="input-control w-full text-left flex items-center justify-between @error('country_code') border-red-500 @enderror {{ $this->shouldShowPublishRequirement('country_code') ? 'border-red-500' : '' }}">
                            <span class="flex items-center gap-2 flex-nowrap"> 
                                <span class="flex items-center gap-2"> 
                                    <svg class="w-5 h-5 text-gray-400" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </span>
                                <span class="flex items-center gap-2">
                                    @if($country_code)
                                        <img src="https://flagcdn.com/{{ strtolower($country_code) }}.svg" alt="{{ $country_code }}" class="w-5 h-4 object-cover">
                                        <span>{{ collect($countries)->firstWhere('code', $country_code)['name'] ?? $country_code }}</span>
                                    @else
                                        <span class="text-gray-400">{{ __('front.profiles.form.selectcountry') }}</span>
                                    @endif
                                </span>
                            </span>
                        </button>

                        <div
                            x-show="open"
                            @click.away="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
                            style="display: none;">
                            <!-- Search Input -->
                            <div class="sticky top-0 bg-white p-2 border-b border-gray-200">
                                <input
                                    type="text"
                                    wire:model.live="countrySearchTerm"
                                    placeholder="{{ __('front.profiles.form.searchcountry') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                            </div>
                            <!-- Country List -->
                            <div class="py-1">
                                @forelse($this->filteredCountries as $country)
                                    <button
                                        type="button"
                                        wire:click="selectCountry('{{ $country['code'] }}')"
                                        class="w-full px-4 py-2 text-left hover:bg-gray-50 flex items-center gap-2"
                                        :class="{ 'bg-pink-50': '{{ $country['code'] }}' === '{{ $country_code }}' }">
                                        <img src="https://flagcdn.com/{{ strtolower($country['code']) }}.svg" alt="{{ $country['code'] }}" class="w-5 h-4 object-cover">
                                        <span>{{ $country['name'] }}</span>
                                    </button>
                                @empty
                                    <div class="px-4 py-2 text-gray-500 text-sm">{{ __('front.profiles.form.nocountries') }}</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    @error('country_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @if($this->shouldShowPublishRequirement('country_code'))
                        <p class="mt-1 text-sm text-red-600">{{ __('front.profiles.form.publish_required') }}</p>
                    @endif
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.address') }}</label>
                    <input
                        type="text"
                        id="address"
                        wire:model="address"
                        class="input-control mt-1 @error('address') border-red-500 @enderror"
                        placeholder="{{ __('front.profiles.form.street') }}">
                    @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- About -->
                <div>
                    <label for="about" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.aboutme') }}</label>
                    <textarea
                        id="about"
                        wire:model="about"
                        rows="4"
                        class="input-control mt-1 @error('about') border-red-500 @enderror"
                        placeholder="{{ __('front.profiles.form.aboutmeplaceholder') }}"></textarea>
                    @error('about') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- InCall & OutCall Toggles -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <label for="incall" class="text-sm font-medium text-gray-700">{{ __('front.profiles.form.incall') }}</label>
                            <p class="text-xs text-gray-500">{{ __('front.profiles.form.incall_desc') }}</p>
                        </div>
                        <x-toggle-switch
                            name="incall"
                            id="incall"
                            wire-model="incall"
                            :checked="$incall" />
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label for="outcall" class="text-sm font-medium text-gray-700">{{ __('front.profiles.form.outcall') }}</label>
                            <p class="text-xs text-gray-500">{{ __('front.profiles.form.outcall_desc') }}</p>
                        </div>
                        <x-toggle-switch
                            name="outcall"
                            id="outcall"
                            wire-model="outcall"
                            :checked="$outcall" />
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label for="is_porn_actress" class="text-sm font-medium text-gray-700">{{ __('front.profiles.form.porn_actress') }}</label>
                            <p class="text-xs text-gray-500">{{ __('front.profiles.form.porn_actress_desc') }}</p>
                        </div>
                        <x-toggle-switch
                            name="is_porn_actress"
                            id="is_porn_actress"
                            wire-model="is_porn_actress"
                            :checked="$is_porn_actress" />
                    </div>
                </div>

                <!-- Local Prices -->
                <div class="space-y-4 border-t border-gray-200 pt-10 mt-20">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-full overflow-hidden flex-shrink-0 bg-gray-200 flex items-center justify-center">
                            <img src="https://flagcdn.com/cz.svg"
                                alt="Czech Republic"
                                class="w-full h-full object-cover">
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('front.profiles.form.local_prices') }}</h3>
                    </div>

                    <div class="space-y-3">
                        @foreach($local_prices as $index => $price)
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                            <!-- Time Hours -->
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.time_hours') }}</label>
                                <input
                                    type="text"
                                    wire:model="local_prices.{{ $index }}.time_hours"
                                    class="input-control @error('local_prices.' . $index . '.time_hours') border-red-500 @enderror"
                                    placeholder="0.5">
                                @error('local_prices.' . $index . '.time_hours')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Incall Price -->
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.incall_price') }}</label>
                                <input
                                    type="number"
                                    wire:model="local_prices.{{ $index }}.incall_price"
                                    class="input-control @error('local_prices.' . $index . '.incall_price') border-red-500 @enderror"
                                    placeholder="8000">
                                @error('local_prices.' . $index . '.incall_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Outcall Price -->
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.outcall_price') }}</label>
                                <input
                                    type="number"
                                    wire:model="local_prices.{{ $index }}.outcall_price"
                                    class="input-control @error('local_prices.' . $index . '.outcall_price') border-red-500 @enderror"
                                    placeholder="10000">
                                @error('local_prices.' . $index . '.outcall_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Remove Button -->
                            <div class="md:col-span-1 flex justify-end pb-2">
                                <button
                                    type="button"
                                    wire:click="removeLocalPrice({{ $index }})"
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-pink-500 text-white hover:bg-pink-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @endforeach

                        <!-- Add Button -->
                        <button
                            type="button"
                            wire:click="addLocalPrice"
                            class="w-full py-3 px-4 rounded-2xl bg-gray-50 text-gray-700 hover:bg-gray-100 transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span class="font-medium">{{ __('front.profiles.form.add_price') }}</span>
                        </button>
                    </div>
                </div>


                <!-- Global Prices -->
                <div class="space-y-4 border-t border-gray-200 pt-10 mt-20">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-full overflow-hidden flex-shrink-0 bg-gray-200 flex items-center justify-center">
                            <img src="https://flagcdn.com/w80/un.png"
                                alt="Global"
                                class="w-full h-full object-cover">
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('front.profiles.form.global_prices') }}</h3>
                    </div>

                    <div class="space-y-3">
                        @foreach($global_prices as $index => $price)
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                            <!-- Time Hours -->
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.time_hours') }}</label>
                                <input
                                    type="text"
                                    wire:model="global_prices.{{ $index }}.time_hours"
                                    class="input-control @error('global_prices.' . $index . '.time_hours') border-red-500 @enderror"
                                    placeholder="0.5">
                                @error('global_prices.' . $index . '.time_hours')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Incall Price -->
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.incall_price') }}</label>
                                <input
                                    type="number"
                                    wire:model="global_prices.{{ $index }}.incall_price"
                                    class="input-control @error('global_prices.' . $index . '.incall_price') border-red-500 @enderror"
                                    placeholder="8000">
                                @error('global_prices.' . $index . '.incall_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Outcall Price -->
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.outcall_price') }}</label>
                                <input
                                    type="number"
                                    wire:model="global_prices.{{ $index }}.outcall_price"
                                    class="input-control @error('global_prices.' . $index . '.outcall_price') border-red-500 @enderror"
                                    placeholder="10000">
                                @error('global_prices.' . $index . '.outcall_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Remove Button -->
                            <div class="md:col-span-1 flex justify-end pb-2">
                                <button
                                    type="button"
                                    wire:click="removeGlobalPrice({{ $index }})"
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-pink-500 text-white hover:bg-pink-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @endforeach

                        <!-- Add Button -->
                        <button
                            type="button"
                            wire:click="addGlobalPrice"
                            class="w-full py-3 px-4 rounded-2xl bg-gray-50 text-gray-700 hover:bg-gray-100 transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span class="font-medium">{{ __('front.profiles.form.add_price') }}</span>
                        </button>
                    </div>
                </div>


                <!-- Contacts -->
                <div class="space-y-4 border-t border-gray-200 pt-10 mt-20">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-full overflow-hidden flex-shrink-0 bg-primary-100 flex items-center justify-center">
                            <x-icons name="message" class="w-4 h-4 text-primary" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('front.profiles.form.contacts') }}</h3>
                    </div>

                    <div class="space-y-3">
                        @foreach($contacts as $index => $contact)
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                            <!-- Contact Type -->
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.contact_type') }}</label>
                                <select
                                    wire:model="contacts.{{ $index }}.type"
                                    class="input-control @error('contacts.' . $index . '.type') border-red-500 @enderror">
                                    <option value="phone">{{ __('front.profiles.form.contact_phone') }}</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="telegram">Telegram</option>
                                </select>
                                @error('contacts.' . $index . '.type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Contact Value -->
                            <div class="md:col-span-7">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.contact_value') }}</label>
                                <input
                                    type="text"
                                    wire:model="contacts.{{ $index }}.value"
                                    class="input-control @error('contacts.' . $index . '.value') border-red-500 @enderror"
                                    placeholder="{{ $contact['type'] === 'telegram' ? '@username' : '+420 123 456 789' }}">
                                @error('contacts.' . $index . '.value')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Remove Button -->
                            <div class="md:col-span-1 flex justify-end pb-2">
                                <button
                                    type="button"
                                    wire:click="removeContact({{ $index }})"
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-pink-500 text-white hover:bg-pink-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @endforeach

                        <!-- Add Button -->
                        <button
                            type="button"
                            wire:click="addContact"
                            class="w-full py-3 px-4 rounded-2xl bg-gray-50 text-gray-700 hover:bg-gray-100 transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span class="font-medium">{{ __('front.profiles.form.add_contact') }}</span>
                        </button>
                    </div>
                </div>


                <!-- Availability Hours -->
                <div class="border-t border-gray-200 pt-10 mt-20">
                    <label for="availability_hours" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.availability') }}</label>
                    <input
                        type="text"
                        id="availability_hours"
                        wire:model="availability_hours"
                        class="input-control mt-1 @error('availability_hours') border-red-500 @enderror"
                        placeholder="{{ __('front.profiles.form.availabilityplaceholder') }}">
                    @error('availability_hours') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Profile Status -->
                @if($this->isAdmin())
                <!-- Admin: Editable Select -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.status') }}</label>
                    <select
                        id="status"
                        wire:model="status"
                        class="input-control mt-1 @error('status') border-red-500 @enderror">
                        <option value="">{{ __('front.profiles.form.selectstatus') }}</option>
                        <option value="pending">{{ __('front.profiles.form.pending') }}</option>
                        <option value="approved">{{ __('front.profiles.form.approved') }}</option>
                        <option value="rejected">{{ __('front.profiles.form.rejected') }}</option>
                    </select>
                    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                @else
                <!-- Non-Admin: Status Display -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.status') }}</label>
                    <div class="mt-1 flex items-center">
                        <span class="inline-flex items-center px-3 py-2 rounded-lg border text-sm font-medium {{ $this->getStatusColor() }}">
                            @if($status === 'pending')
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            @elseif($status === 'approved')
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            @elseif($status === 'rejected')
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            @endif
                            {{ $this->getStatusLabel() }}
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">{{ __('front.profiles.form.statusdesc') }}</p>
                </div>
                @endif

                <!-- Public Profile Toggle -->
                <div class="flex items-center justify-between">
                    <div>
                        <label for="is_public" class="text-sm font-medium text-gray-700">{{ __('front.profiles.form.public') }}</label>
                        <p class="text-xs text-gray-500">{{ __('front.profiles.form.publicdesc') }}</p>
                    </div>
                    <x-toggle-switch
                        name="is_public"
                        id="is_public"
                        wire-model="is_public"
                        :checked="$is_public"
                        :disabled="!$this->canPublishProfile()" />
                </div>

                <!-- Save Profile Button -->
                <div class="flex justify-end pt-6">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="btn-primary justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span wire:loading.remove>{{ __('front.profiles.form.savechanges') }}</span>
                        <span wire:loading>{{ __('front.profiles.form.saving') }}</span>
                    </button>
                </div>
            </div>
            @else
            <div x-data="{ expanded: false }">
                <!-- No Profile CTA Banner -->
                <div class="relative overflow-hidden rounded-xl border-2 border-primary bg-gradient-to-br from-primary-50 via-white to-secondary/5 shadow-md">
                    <!-- Decorative accent -->
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary via-primary-400 to-secondary"></div>

                    <div class="p-6 md:p-8">
                        <h4 class="text-xl font-bold text-secondary">{{ __('front.profiles.form.noprofile') }}</h4>
                        <p class="text-sm text-gray-600 mt-1">{{ __('front.profiles.form.createprofile') }}</p>

                        <!-- Verification info -->
                        <div class="mt-3 flex items-start gap-2 text-xs text-gray-500 bg-white/60 rounded-lg p-3 border border-gray-100">
                            <svg class="w-4 h-4 text-primary flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span>{{ __('front.profiles.form.verification_notice') }}</span>
                        </div>

                        <!-- Create profile button -->
                        <button
                            type="button"
                            @click="expanded = !expanded"
                            class="mt-5 btn-primary justify-center"
                            x-text="expanded ? '{{ __('front.profiles.form.collapse_form') }}' : '{{ __('front.profiles.form.create_profile_btn') }}'">
                        </button>
                    </div>
                </div>

                <!-- Collapsible Profile Creation Form -->
                <div
                    x-show="expanded"
                    x-collapse
                    x-cloak
                    class="mt-6 space-y-6">

                    <!-- Display Name -->
                    <div>
                        <label for="display_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.displayname') }} *</label>
                        <input
                            type="text"
                            id="display_name"
                            wire:model="display_name"
                            class="input-control mt-1 @error('display_name') border-red-500 @enderror"
                            placeholder="{{ __('front.profiles.form.displayname') }}"
                            required>
                        @error('display_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Age -->
                        <div>
                            <label for="age" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.age') }} *</label>
                            <input
                                type="number"
                                id="age"
                                wire:model="age"
                                min="18"
                                max="120"
                                class="input-control mt-1 @error('age') border-red-500 @enderror"
                                placeholder="25">
                            @error('age') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- City -->
                        <div x-data="{ open: @entangle('cityDropdownOpen') }" class="relative">
                            <label for="city_new" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.city') }}</label>
                            <div class="relative">
                                <input
                                    type="text"
                                    id="city_new"
                                    wire:model.live.debounce.300ms="citySearchTerm"
                                    @focus="open = true"
                                    @click="open = true"
                                    :disabled="!$wire.country_code"
                                    class="input-control mt-1 @error('city') border-red-500 @enderror"
                                    :class="{ 'bg-gray-100 cursor-not-allowed': !$wire.country_code }"
                                    placeholder="{{ __('front.profiles.form.city') }}">
                                @if(!$country_code)
                                    <p class="mt-1 text-xs text-gray-500">{{ __('front.profiles.form.select_country_first') }}</p>
                                @endif
                            </div>
                            
                            <!-- City Suggestions Dropdown -->
                            <div
                                x-show="open && $wire.citySuggestions.length > 0 || (open && $wire.citySearchTerm.length >= 2)"
                                @click.away="open = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
                                style="display: none;">
                                @if(count($citySuggestions) > 0)
                                    @foreach($citySuggestions as $suggestion)
                                        <button
                                            type="button"
                                            wire:click="selectCity('{{ addslashes($suggestion) }}')"
                                            class="w-full px-4 py-2 text-left hover:bg-gray-50 flex items-center gap-2"
                                            :class="{ 'bg-pink-50': '{{ addslashes($suggestion) }}' === '{{ addslashes($city) }}' }">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span>{{ $suggestion }}</span>
                                        </button>
                                    @endforeach
                                @else
                                    <div class="px-4 py-3 text-sm text-gray-500 text-center">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        {{ __('front.profiles.form.city_no_results') }}
                                    </div>
                                @endif
                            </div>
                            @error('city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Country -->
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.country') }} *</label>
                        <div x-data="{ open: @entangle('countryDropdownOpen'), searchTerm: @entangle('countrySearchTerm') }" class="relative">
                            <button
                                type="button"
                                @click="open = !open"
                                class="input-control w-full text-left flex items-center justify-between @error('country_code') border-red-500 @enderror">
                                <span class="flex items-center flex-nowrap gap-2">
                                    <svg class="w-5 h-5 text-gray-400" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    <span class="flex items-center flex-nowrap ">
                                        @if($country_code)
                                            <img src="https://flagcdn.com/{{ strtolower($country_code) }}.svg" alt="{{ $country_code }}" class="w-5 h-4 object-cover">
                                            <span>{{ collect($countries)->firstWhere('code', $country_code)['name'] ?? $country_code }}</span>
                                        @else
                                            <span class="text-gray-400">{{ __('front.profiles.form.selectcountry') }}</span>
                                        @endif
                                    </span>
                               </span>
                            </button>

                            <div
                                x-show="open"
                                @click.away="open = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
                                style="display: none;">
                                <!-- Search Input -->
                                <div class="sticky top-0 bg-white p-2 border-b border-gray-200">
                                    <input
                                        type="text"
                                        wire:model.live="countrySearchTerm"
                                        placeholder="{{ __('front.profiles.form.searchcountry') }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                                </div>
                                <!-- Country List -->
                                <div class="py-1">
                                    @forelse($this->filteredCountries as $country)
                                        <button
                                            type="button"
                                            wire:click="selectCountry('{{ $country['code'] }}')"
                                            class="w-full px-4 py-2 text-left hover:bg-gray-50 flex items-center gap-2"
                                            :class="{ 'bg-pink-50': '{{ $country['code'] }}' === '{{ $country_code }}' }">
                                            <img src="https://flagcdn.com/{{ strtolower($country['code']) }}.svg" alt="{{ $country['code'] }}" class="w-5 h-4 object-cover">
                                            <span>{{ $country['name'] }}</span>
                                        </button>
                                    @empty
                                        <div class="px-4 py-2 text-gray-500 text-sm">{{ __('front.profiles.form.nocountries') }}</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        @error('country_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.address') }}</label>
                        <input
                            type="text"
                            id="address"
                            wire:model="address"
                            class="input-control mt-1 @error('address') border-red-500 @enderror"
                            placeholder="{{ __('front.profiles.form.street') }}">
                        @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- About -->
                    <div>
                        <label for="about" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.aboutme') }}</label>
                        <textarea
                            id="about"
                            wire:model="about"
                            rows="4"
                            class="input-control mt-1 @error('about') border-red-500 @enderror"
                            placeholder="{{ __('front.profiles.form.aboutmeplaceholder') }}"></textarea>
                        @error('about') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Submit Button (inside collapsed section for clarity) -->
                    <div class="flex justify-end">
                        <button
                            type="button"
                            wire:click.prevent="createProfile"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="btn-primary justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span wire:loading.remove wire:target="createProfile">{{ __('front.profiles.form.create_profile_btn') }}</span>
                            <span wire:loading wire:target="createProfile">{{ __('front.profiles.form.saving') }}</span>
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </form>

</div>