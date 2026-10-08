<div class="w-[310px] md:w-full mx-auto" x-data @services-saved.window="$nextTick(() => $refs.feedback.scrollIntoView({ behavior: 'smooth', block: 'start' }))">
    <div x-ref="feedback" style="scroll-margin-top: 110px;" role="status" aria-live="polite">
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
    @if (session()->has('error'))
        <p class="alert alert-danger mb-6">{{ session('error') }}</p>
    @endif
    </div>

    <!-- Services Section -->
    <div class="py-6">
        <h2 class="mb-4 text-left" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">{{ __('front.account.services.title') }}</h2>

        @if($services && $services->count() > 0)
            @php
                // The design fills the 3-column grid to 28 toggle slots by
                // cycling the same real service list rather than needing 28
                // distinct services. Every slot still points at the real
                // $service->id, so toggling any copy toggles them all — only
                // the DOM id per slot needs to stay unique.
                $gridSlotCount = max($services->count(), 28);
                $gridServices = collect(range(0, $gridSlotCount - 1))
                    ->map(fn ($i) => $services[$i % $services->count()]);
            @endphp
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-x-4 gap-y-1">
                @foreach($gridServices as $slot => $service)
                <div class="flex flex-col md:flex-row md:items-start md:justify-between py-1">
                    <div class="flex-shrink-0">
                        <x-toggle-switch
                            name="service_{{ $service->id }}_{{ $slot }}"
                            id="service_{{ $service->id }}_{{ $slot }}"
                            :checked="in_array($service->id, $selectedServices)"
                            :aria-label="$service->name"
                            wire:click="toggleService({{ $service->id }})"
                        />
                    </div>
                    <div class="flex-1 md:ml-3">
                        <h5 style="font-family:'Poppins',sans-serif; font-weight:400; font-size:14px; color:#505050;">
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

    <hr class="w-[843px] max-w-full relative left-1/2 -translate-x-1/2 mt-[80px] mb-[50px]">

    <!-- Languages Section -->
    <div class="py-6">
        <h2 class="mb-4 text-left" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">{{ __('front.account.services.languages_title') }}</h2>

        <div class="grid grid-cols-2 lg:grid-cols-3 gap-x-4 gap-y-1">
            @foreach($languages as $language)
            <div class="flex flex-col md:flex-row md:items-start md:justify-between py-1">
                <div class="flex-shrink-0">
                    <x-toggle-switch
                        name="language_{{ Str::slug($language) }}"
                        id="language_{{ Str::slug($language) }}"
                        :checked="in_array($language, $selectedLanguages)"
                        :aria-label="__('front.account.services.language_names.' . $language)"
                        wire:click="toggleLanguage('{{ $language }}')"
                    />
                </div>
                <div class="flex-1 md:ml-3">
                    <h5 style="font-family:'Poppins',sans-serif; font-weight:400; font-size:14px; color:#505050;">
                        {{ __('front.account.services.language_names.' . $language) }}
                    </h5>
                </div>
            </div>
            @endforeach
        </div>

    </div>

    <hr class="w-[843px] max-w-full relative left-1/2 -translate-x-1/2 mt-[80px] mb-[50px]">

    <!-- Online Hours Section -->
    <div class="py-6" x-data="{ stillOnline: @entangle('alwaysOnline'), dirty: false, saving: false }" @availability-saved.window="dirty = false">
        <h2 class="mb-4 text-left" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">{{ __('front.account.services.online_hours_title') }}</h2>

        <!-- Always Online Toggle -->
        <div class="mb-6 flex items-center">
            <x-toggle-switch
                name="always_online"
                id="always_online"
                :checked="$alwaysOnline"
                x-model="stillOnline"
                x-on:change="dirty = true"
                x-bind:disabled="saving"
            />
            <label for="always_online" class="ml-3" style="font-family:'Poppins',sans-serif; font-weight:400; font-size:14px; color:#505050;">
                {{ __('front.account.services.always_online') }}
            </label>
        </div>

        <!-- Days of Week Schedule -->
        <div class="space-y-4" :class="stillOnline ? 'opacity-40 pointer-events-none' : ''">
            @php
                $days = [
                    ['key' => 'monday', 'label' => __('front.account.weekdays.monday')],
                    ['key' => 'tuesday', 'label' => __('front.account.weekdays.tuesday')],
                    ['key' => 'wednesday', 'label' => __('front.account.weekdays.wednesday')],
                    ['key' => 'thursday', 'label' => __('front.account.weekdays.thursday')],
                    ['key' => 'friday', 'label' => __('front.account.weekdays.friday')],
                    ['key' => 'saturday', 'label' => __('front.account.weekdays.saturday')],
                    ['key' => 'sunday', 'label' => __('front.account.weekdays.sunday')],
                ];
            @endphp

            @php
                $timeOptions = [];
                for ($h = 0; $h < 24; $h++) {
                    $timeOptions[] = sprintf('%02d:00', $h);
                    $timeOptions[] = sprintf('%02d:30', $h);
                }
            @endphp

            @foreach($days as $day)
            <div class="flex flex-wrap gap-4">
                <!-- From Time -->
                <div>
                    <label for="{{ $day['key'] }}_from" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ $day['label'] }} {{ __('front.account.services.from') }}
                    </label>
                    <div class="relative">
                        <select
                            wire:model="schedule.{{ $day['key'] }}.from"
                            x-on:change="dirty = true"
                            name="{{ $day['key'] }}_from"
                            id="{{ $day['key'] }}_from"
                            class="input-control !w-[144px] !h-[50px] md:!w-[240px] md:!h-[50px] rounded-[8px] appearance-none pr-[54px]" :disabled="stillOnline || saving">
                            <option value="">-</option>
                            @foreach($timeOptions as $time)
                                <option value="{{ $time }}">{{ $time }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-1 top-1/2 -translate-y-1/2 w-[42px] h-[42px] rounded-[4px] bg-[#DD3888] flex items-center justify-center pointer-events-none">
                            <svg class="w-[10px] h-[5px]" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 1L5 4L9 1" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- To Time -->
                <div>
                    <label for="{{ $day['key'] }}_to" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ $day['label'] }} {{ __('front.account.services.to') }}
                    </label>
                    <div class="relative">
                        <select
                            wire:model="schedule.{{ $day['key'] }}.to"
                            x-on:change="dirty = true"
                            name="{{ $day['key'] }}_to"
                            id="{{ $day['key'] }}_to"
                            class="input-control !w-[144px] !h-[50px] md:!w-[240px] md:!h-[50px] rounded-[8px] appearance-none pr-[54px]" :disabled="stillOnline || saving">
                            <option value="">-</option>
                            @foreach($timeOptions as $time)
                                <option value="{{ $time }}">{{ $time }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-1 top-1/2 -translate-y-1/2 w-[42px] h-[42px] rounded-[4px] bg-[#DD3888] flex items-center justify-center pointer-events-none">
                            <svg class="w-[10px] h-[5px]" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 1L5 4L9 1" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Unsaved changes notice -->
        <p x-show="dirty" x-cloak class="mt-6 mb-0" style="font-family:'Poppins',sans-serif; font-weight:500; font-size:13px; color:#DD3888;">
            {{ __('front.account.services.unsaved_changes') }}
        </p>

        @if ($errors->any())
            <div role="alert" class="mt-4 text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        @if ($availabilityMessage)
            <p x-show="!dirty" role="status" class="mt-4 text-sm text-green-700">{{ $availabilityMessage }}</p>
        @endif

        <!-- Only an acknowledged save clears the unsaved state. -->
        <button id="save-availability" type="button" :disabled="!dirty || saving" :aria-busy="saving.toString()" x-on:click="saving = true; $wire.saveAvailability().finally(() => saving = false)" :style="{ backgroundColor: dirty ? '#5C2D62' : '#E8E8E8', color: dirty ? '#FFFFFF' : '#A4A4A4' }" class="mt-6 w-[310px] md:w-[240px] h-[50px] rounded-[8px] flex items-center justify-center gap-2 transition-colors duration-200 disabled:cursor-not-allowed">
            <img :src="dirty ? @js(asset('images/icons/SaveWhite.svg')) : @js(asset('images/icons/Save.svg'))" class="w-[20px] h-[20px]" alt="">
            <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px;">{{ __('front.profiles.form.save_changes') }}</span>
        </button>
    </div>
</div>
