<div>
    @if (session()->has('message'))
    <div class="alert alert-success flex items-center justify-between mb-6">
        <div class="flex items-center font-semibold">
            <x-icons name="bell" class="w-5 h-5 mr-2.5" />
            <span>{{ session('message') }}</span>
        </div>
        <button type="button" class="flex items-center ml-2 text-gray-400 hover:text-gray-600" onclick="this.parentElement.remove()">
            <x-icons name="cross" class="text-green-800 w-3 h-3" />
        </button>
    </div>
    @endif

    <!-- Services Section -->
    <div class="py-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('front.account.services.title') }}</h2>
        <p class="text-sm text-gray-600 mb-6">{{ __('front.account.services.description') }}</p>

        @if($services && $services->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($services as $service)
                <div class="flex items-start justify-between py-1">
                        <div class="flex-shrink-0">
                        <x-toggle-switch 
                            name="service_{{ $service->id }}"
                            id="service_{{ $service->id }}"
                            :checked="in_array($service->id, $selectedServices)"
                            wire:click="toggleService({{ $service->id }})"
                        />
                    </div>
                    <div class="flex-1 ml-3">
                        <h5 class="font-semibold text-gray-900">
                            {{ $service->name }}
                        </h5>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <!-- No Services Message -->
            <div class="card p-6">
                <div class="text-center py-8">
                    <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('front.account.services.noservices') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('front.account.services.noservices_desc') }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Online Hours Section -->
    <div class="py-6 mt-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('front.account.services.online_hours_title') }}</h2>
        
        <!-- Always Online Toggle -->
        <div class="mb-6 flex items-center">
            <x-toggle-switch 
                name="always_online"
                id="always_online"
                :checked="false"
            />
            <label for="always_online" class="ml-3 text-sm font-medium text-gray-700">
                {{ __('front.account.services.always_online') }}
            </label>
        </div>

        <!-- Days of Week Schedule -->
        <div class="space-y-4">
            @php
                $days = [
                    ['key' => 'monday', 'label' => 'Pondělí'],
                    ['key' => 'tuesday', 'label' => 'Úterý'],
                    ['key' => 'wednesday', 'label' => 'Středa'],
                    ['key' => 'thursday', 'label' => 'Čtvrtek'],
                    ['key' => 'friday', 'label' => 'Pátek'],
                    ['key' => 'saturday', 'label' => 'Sobota'],
                    ['key' => 'sunday', 'label' => 'Neděle'],
                ];
            @endphp

            @foreach($days as $day)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- From Time -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ $day['label'] }} OD
                    </label>
                    <div class="relative">
                        <select 
                            name="{{ $day['key'] }}_from" 
                            id="{{ $day['key'] }}_from"
                            class="block w-full px-4 py-3 text-base leading-5 rounded-lg bg-white border border-gray-200 outline-none transition-all duration-200 ease-in-out focus:border-primary-500 focus:ring-2 focus:ring-primary-500 appearance-none cursor-pointer pr-10">
                            <option value="">-</option>
                            <option value="00:00">00:00</option>
                            <option value="01:00">01:00</option>
                            <option value="02:00">02:00</option>
                            <option value="03:00">03:00</option>
                            <option value="04:00">04:00</option>
                            <option value="05:00">05:00</option>
                            <option value="06:00">06:00</option>
                            <option value="07:00">07:00</option>
                            <option value="08:00">08:00</option>
                            <option value="09:00" selected>09:00</option>
                            <option value="10:00">10:00</option>
                            <option value="11:00">11:00</option>
                            <option value="12:00">12:00</option>
                            <option value="13:00">13:00</option>
                            <option value="14:00">14:00</option>
                            <option value="15:00">15:00</option>
                            <option value="16:00">16:00</option>
                            <option value="17:00">17:00</option>
                            <option value="18:00">18:00</option>
                            <option value="19:00">19:00</option>
                            <option value="20:00">20:00</option>
                            <option value="21:00">21:00</option>
                            <option value="22:00">22:00</option>
                            <option value="23:00">23:00</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-5 h-5 text-white bg-primary rounded-md p-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- To Time -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ $day['label'] }} DO
                    </label>
                    <div class="relative">
                        <select 
                            name="{{ $day['key'] }}_to" 
                            id="{{ $day['key'] }}_to"
                            class="block w-full px-4 py-3 text-base leading-5 rounded-lg bg-white border border-gray-200 outline-none transition-all duration-200 ease-in-out focus:border-primary-500 focus:ring-2 focus:ring-primary-500 appearance-none cursor-pointer pr-10">
                            <option value="">-</option>
                            <option value="00:00">00:00</option>
                            <option value="01:00">01:00</option>
                            <option value="02:00">02:00</option>
                            <option value="03:00">03:00</option>
                            <option value="04:00">04:00</option>
                            <option value="05:00">05:00</option>
                            <option value="06:00">06:00</option>
                            <option value="07:00">07:00</option>
                            <option value="08:00">08:00</option>
                            <option value="09:00">09:00</option>
                            <option value="10:00">10:00</option>
                            <option value="11:00">11:00</option>
                            <option value="12:00">12:00</option>
                            <option value="13:00">13:00</option>
                            <option value="14:00">14:00</option>
                            <option value="15:00">15:00</option>
                            <option value="16:00">16:00</option>
                            <option value="16:30" selected>16:30</option>
                            <option value="17:00">17:00</option>
                            <option value="18:00">18:00</option>
                            <option value="19:00">19:00</option>
                            <option value="20:00">20:00</option>
                            <option value="21:00">21:00</option>
                            <option value="22:00">22:00</option>
                            <option value="23:00">23:00</option>
                            <option value="23:59">23:59</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-5 h-5 text-white bg-primary rounded-md p-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Save Button -->
        <div class="mt-6 flex justify-end">
            <button type="button" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                {{ __('front.account.services.save_changes') }}
            </button>
        </div>
    </div>
</div>
