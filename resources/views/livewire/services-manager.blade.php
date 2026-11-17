{{-- pass 1 overlay: the last save button ends 161px above the footer
     logo glyph (md:pb-[35px] on the form).
     Figma home-17 / home-20: three toggle columns at 0 / 291 / 582px (the
     second and third 2px lower than the first), rows
     44px apart, label 21px after the switch; schedule fields 240x50, 31px
     apart, label glyph 22px above, rows 92px apart; labels 13/400 and values
     15/700 #505050 like the profile form. --}}
<div class="services-form w-[310px] md:w-full mx-auto md:pb-[35px]" x-data @services-saved.window="$nextTick(() => $refs.feedback.scrollIntoView({ behavior: 'smooth', block: 'start' }))">
    {{-- Inside the root element: as the first top-level node the <style> became
         Livewire's root, so every toggle and save in this component did nothing. --}}
<style>
    .services-form .svc-grid { row-gap: 20px; }
    @media (min-width: 1180px) { .services-form .svc-grid { grid-template-columns: 291px 291px 261px; column-gap: 0; } .services-form .svc-grid > .svc-col-later { transform: translateY(2px); } }
    /* home-17 fills the three columns top to bottom (Pozice 69, Vaginální sex,
       Výstřik na obličej… down the first column), not row by row. */
    @media (min-width: 1024px) { .services-form .svc-grid { grid-auto-flow: column; grid-template-rows: repeat(var(--svc-rows), auto); } }
    /* phone 360 px-4: the same top-to-bottom order in two columns. */
    @media (max-width: 1023px) { .services-form .svc-grid { grid-auto-flow: column; grid-template-rows: repeat(var(--svc-rows-m), auto); } }
    /* phone 360 px-4: second column 165.5px in, title 26px under the heading,
       label 7px under its switch. */
    @media (max-width: 767px) {
        /* pass 2: switches 2px lower (track top 385 in phone 360 px-4), labels and the 64px row pitch unchanged. */
        .services-form .svc-grid { grid-template-columns: 165.5px minmax(0, 1fr); column-gap: 0; row-gap: 14px; }
        .services-form .svc-grid h5 { margin-top: 5px; }
        .services-form .svc-section-title { margin-bottom: 28px !important; }
        /* pass 2: the first heading's glyph at y 331 (phone 360 px-4). */
        .services-form .svc-section-title.svc-first { margin-top: -2px; }
        /* phone 360 px-4 hours block: 29px heading lines, the always-online row
           boxed by two rules 62px apart, days 94px apart, save 40px below. */
        .services-form .svc-hours-title { line-height: 29px; margin-bottom: 22.5px !important; }
        .services-form .svc-online-row { min-height: 63px; border-top: 1px solid #E9E9E9; border-bottom: 1px solid #E9E9E9; margin-bottom: 27.5px !important; }
        .services-form .svc-schedule > * { margin-top: 0 !important; margin-bottom: 10px !important; }
        .services-form .svc-schedule > :last-child { margin-bottom: 0 !important; }
        .services-form #save-availability { margin-top: 40px !important; }
        .services-form .svc-hours { padding-bottom: 43.5px !important; }
    }
    .services-form .svc-schedule label { font-family: 'Poppins', sans-serif; font-weight: 400; font-size: 13px; line-height: 34px; color: #505050; padding: 0 0 0 8px; margin-bottom: 0; }
    .services-form .svc-schedule select { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 15px; color: #505050; border: 2px solid #E6E6E6; padding-left: 17px; }
</style>
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
    <div class="pb-6 md:pb-0">
        <h2 class="svc-section-title svc-first mb-4 md:mb-[37px] text-left" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">{{ __('front.account.services.title') }}</h2>

        @if($services && $services->count() > 0)
            <div class="svc-grid grid grid-cols-2 lg:grid-cols-3 gap-x-4" style="--svc-rows: {{ (int) ceil($services->count() / 3) }}; --svc-rows-m: {{ (int) ceil($services->count() / 2) }};">
                @php $svcRows = (int) ceil($services->count() / 3); @endphp
                @foreach($services as $service)
                <div wire:key="service-{{ $service->id }}" class="{{ $loop->index >= $svcRows ? 'svc-col-later ' : '' }}flex flex-col md:flex-row md:items-center md:justify-between md:min-h-[24px]">
                    <div class="flex-shrink-0">
                        <x-toggle-switch
                            name="service_{{ $service->id }}"
                            id="service_{{ $service->id }}"
                            :checked="in_array($service->id, $selectedServices)"
                            :aria-label="$service->name"
                            wire:click="toggleService({{ $service->id }})"
                        />
                    </div>
                    <div class="flex-1 md:ml-[21px]">
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

    <hr class="w-[843px] max-w-full relative left-1/2 -translate-x-1/2 mt-[80px] mb-[51px]">

    <!-- Languages Section (no Figma frame; same layout as the services) -->
    <div class="pb-6 md:pb-0">
        <h2 class="svc-section-title mb-4 md:mb-[39px] text-left" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">{{ __('front.account.services.languages_title') }}</h2>

        <div class="svc-grid grid grid-cols-2 lg:grid-cols-3 gap-x-4" style="--svc-rows: {{ (int) ceil(count($languages) / 3) }}; --svc-rows-m: {{ (int) ceil(count($languages) / 2) }};">
            @foreach($languages as $language)
            <div class="flex flex-col md:flex-row md:items-center md:justify-between md:min-h-[24px]">
                <div class="flex-shrink-0">
                    <x-toggle-switch
                        name="language_{{ Str::slug($language) }}"
                        id="language_{{ Str::slug($language) }}"
                        :checked="in_array($language, $selectedLanguages)"
                        :aria-label="__('front.account.services.language_names.' . $language)"
                        wire:click="toggleLanguage('{{ $language }}')"
                    />
                </div>
                <div class="flex-1 md:ml-[21px]">
                    <h5 style="font-family:'Poppins',sans-serif; font-weight:400; font-size:14px; color:#505050;">
                        {{ __('front.account.services.language_names.' . $language) }}
                    </h5>
                </div>
            </div>
            @endforeach
        </div>

    </div>

    <hr class="w-[843px] max-w-full relative left-1/2 -translate-x-1/2 mt-[80px] mb-[51px]">

    <!-- Online Hours Section -->
    <div class="svc-hours pb-6 md:pb-0" x-data="{ stillOnline: @entangle('alwaysOnline'), dirty: false, saving: false }" @availability-saved.window="dirty = false">
        <h2 class="svc-hours-title mb-4 md:mb-[27px] text-left" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">{{ __('front.account.services.online_hours_title') }}</h2>

        <!-- Always Online Toggle -->
        <div class="svc-online-row mb-6 md:mb-[27px] flex items-center">
            <x-toggle-switch
                name="always_online"
                id="always_online"
                :checked="$alwaysOnline"
                x-model="stillOnline"
                x-on:change="dirty = true"
                x-bind:disabled="saving"
            />
            <label for="always_online" class="ml-3 md:ml-[21px] md:-translate-y-px !p-0" style="font-family:'Poppins',sans-serif; font-weight:400; font-size:14px; color:#505050;">
                {{ __('front.account.services.always_online') }}
            </label>
        </div>

        <!-- Days of Week Schedule -->
        <div class="svc-schedule space-y-4 md:space-y-[10px]" :class="stillOnline ? 'opacity-30 pointer-events-none' : ''">
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
            <div class="flex flex-wrap gap-4 md:gap-x-[31px] md:gap-y-4">
                <!-- From Time -->
                <div>
                    <label for="{{ $day['key'] }}_from" class="block">
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
                                <option value="{{ $time }}">{{-- Figma prints 9:00, not 09:00; the stored value is unchanged. --}}{{ (int) substr($time, 0, 2) . substr($time, 2) }}</option>
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
                    <label for="{{ $day['key'] }}_to" class="block">
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
                                <option value="{{ $time }}">{{-- Figma prints 9:00, not 09:00; the stored value is unchanged. --}}{{ (int) substr($time, 0, 2) . substr($time, 2) }}</option>
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
        <button id="save-availability" type="button" :disabled="!dirty || saving" :aria-busy="saving.toString()" x-on:click="saving = true; $wire.saveAvailability().finally(() => saving = false)" :style="{ backgroundColor: dirty ? '#5C2D62' : '#E8E8E8', color: dirty ? '#FFFFFF' : '#A4A4A4' }" class="mt-6 md:mt-[40px] w-[310px] md:w-[240px] h-[50px] rounded-[8px] flex items-center justify-center gap-2 transition-colors duration-200 disabled:cursor-not-allowed">
            <img :src="dirty ? @js(asset('images/icons/SaveWhite.svg')) : @js(asset('images/icons/Save.svg'))" class="w-[20px] h-[20px]" alt="">
            <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px;">{{ __('front.profiles.form.save_changes') }}</span>
        </button>
    </div>
</div>
