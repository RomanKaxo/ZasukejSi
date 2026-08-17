@php
    $isEnglishHomepage = app()->getLocale() === 'en';
    $profileGridClasses = $isEnglishHomepage
        ? 'flex flex-col md:grid md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 gap-4 profile-list-cards-grid'
        : 'flex flex-col md:grid md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 profile-list-cards-grid';
    $ecoBadgeInsertAt = $isEnglishHomepage ? 5 : 6;
    $advertInsertAt = $isEnglishHomepage ? 9 : 11;
    $hasSelectedLocation = filled($region) || filled($country);
    $selectedLocationTitle = filled($region) ? $region : $country;
    $selectedLocationSubtitle = filled($region) && filled($country) ? $country : null;
    $showSelectedCountryFlag = blank($region) && filled($countryCode);
    $selectedCountryFlagUrl = $showSelectedCountryFlag ? 'https://flagcdn.com/' . mb_strtolower($countryCode) . '.svg' : null;
@endphp

<div>
    @unless($isEnglishHomepage)
    <div class="mt-1 md:mt-2 md:px-8 lg:px-12 py-4 md:py-9 flex items-center gap-2 md:gap-4">
        <x-icons name="search" class="w-5 h-5 md:w-7 md:h-7" style="color: #DD3888;" />
        <h1 class="text-2xl md:text-4xl font-bold" style="color: #5C2D62;">{{ __('front.profiles.list.topresults') }}</h1>
    </div>
    @endunless

    <!-- Quick Filters -->
    <div class="mb-4 md:mb-8 md:px-8 lg:px-12" x-data="{
        init() {
            Livewire.on('filters-updated', () => {
                this.$nextTick(() => {
                    // Re-evaluate Alpine bindings if needed
                });
            });
        }
    }">
        <style>
            .filter-pill {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                /* 4px + 21px line-height + 4px + 4px border = 33px, matching the
                   design's pill. Was 8px, which made them 41px tall. */
                padding: 4px 16px;
                border-radius: 9999px;
                font-size: 14px;
                font-weight: 500;
                transition: all 0.2s ease-in-out;
                cursor: pointer;
                border: 2px solid transparent;
                box-shadow: 0 0 0 rgba(92, 45, 98, 0);
            }

            .filter-pill:hover {
                transform: translateY(-3px);
                border-color: #ECDDE6;
                box-shadow: 0 14px 24px rgba(92, 45, 98, 0.08);
            }

            .filter-pill .icon {
                transition: transform 0.2s ease-in-out;
            }

            .filter-pill:hover .icon {
                transform: scale(1.08) rotate(-6deg);
            }

            .filter-pill.inactive {
                background-color: #FFFFFF;
                border-color: #F2F2F2;
                color: #374151;
            }

            .filter-pill.active {
                background-color: #FFFFFF;
                border-color: #DD3888;
                color: #374151;
            }

            .filter-pill.active:hover {
                box-shadow: 0 16px 28px rgba(221, 56, 136, 0.14);
            }

            .filter-pill .icon {
                width: 16px;
                height: 16px;
                margin-right: 8px;
            }

            .filter-switch {
                width: 33px;
                height: 18px;
                border-radius: 7500px;
                background-color: #E4E4E7;
                position: relative;
                transition: background-color 0.2s ease-in-out;
                margin-left: 8px;
            }

            .filter-switch-thumb {
                position: absolute;
                top: 1.5px;
                left: 1.5px;
                width: 15px;
                height: 15px;
                background-color: #FFFFFF;
                border-radius: 9999px;
                transition: transform 0.2s ease-in-out;
            }

            .filter-pill.active .filter-switch {
                background-color: #DD3888;
            }

            .filter-pill.active .filter-switch-thumb {
                transform: translateX(15px);
            }

            .mobile-top-results {
                padding: {{ $isEnglishHomepage ? '40px 16px 0 16px' : '300px 16px 0 8px' }};
            }

            .mobile-top-results-heading {
                display: flex;
                align-items: center;
                gap: 12px;
                flex-wrap: nowrap;
            }

            .mobile-top-results-title {
                margin: 0;
                line-height: 1;
                font-family: 'Poppins', sans-serif;
                font-size: 28px;
                font-weight: 700;
                color: #5C2D62;
                white-space: nowrap;
            }

            .mobile-top-results-search {
                display: flex;
                justify-content: center;
                margin: 0 0 24px;
            }

            /* Mobile opener overlay button to reliably open fullscreen country search */
            #mobile-country-open {
                display: none;
            }

            @media (max-width: 425px) {
                .mobile-top-results-search {
                    width: 100%;
                }

                #mobile-country-open {
                    display: block;
                    position: absolute;
                    left: 50%;
                    transform: translateX(-50%);
                    width: calc(100vw - 32px);
                    height: auto;
                    min-height: 60px;
                    border: 0;
                    background: transparent;
                    z-index: 190;
                    top: unset;
                    bottom: unset;
                    margin: 0 auto 12px;
                }

                .mobile-top-results-search .search-hero-card {
                    width: min(360px, calc(100vw - 32px)) !important;
                    height: auto !important;
                    left: auto !important;
                    min-height: 0 !important;
                    max-height: none !important;
                    min-width: 0 !important;
                    transform: none !important;
                }

                .mobile-top-results-search .search-select-wrap.is-open {
                    height: auto !important;
                }

                .mobile-top-results-search .search-dropdown-panel,
                .mobile-top-results-search .search-dropdown-panel--region {
                    position: static !important;
                    left: auto !important;
                    top: auto !important;
                    width: 100% !important;
                    max-height: 240px !important;
                    margin-top: -2px !important;
                    padding: 12px 16px 14px !important;
                    border-top: 0 !important;
                    border-radius: 0 0 16px 16px !important;
                    box-shadow: none !important;
                }

                .mobile-top-results-search .search-dropdown-item,
                .mobile-top-results-search .search-dropdown-item--region,
                .mobile-top-results-search .search-dropdown-item--region::after {
                    width: 100% !important;
                }

                .mobile-top-results-search .search-dropdown-item--region::after {
                    left: 0 !important;
                    transform: none !important;
                }
            }

            .mobile-filter-group {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            .mobile-filter-pill {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 36px;
                padding: 0 14px;
                border-radius: 999px;
                background: #FFFFFF;
                border: 2px solid #F2F2F2;
                box-sizing: border-box;
                font-family: 'Poppins', sans-serif;
                font-weight: 500;
                font-size: 13px;
                color: #505050;
                transition: all 0.2s ease-in-out;
            }

            .mobile-filter-pill.is-active {
                border-color: #DD3888;
            }

            .mobile-filter-pill.is-highlighted {
                border-color: #2490FF;
            }

            .mobile-filter-pill.icon-pill {
                gap: 8px;
            }

            .mobile-filter-pill.switch-pill {
                gap: 10px;
                background: #F6F6F8;
            }

            .mobile-filter-icon {
                width: 16px;
                height: 16px;
                color: #C8C8CF;
                flex: 0 0 16px;
            }

            .mobile-filter-divider {
                width: 100%;
                height: 1px;
                background: #EDE7EE;
                margin: 18px 0 20px;
            }

            .mobile-filter-clear {
                width: 36px;
                min-width: 36px;
                padding: 0;
                color: #DD3888;
                border-color: #DD3888;
            }

            .mobile-filter-switch {
                width: 33px;
                height: 18px;
                border-radius: 999px;
                background: #E4E4E7;
                position: relative;
                transition: background-color 0.2s ease-in-out;
            }

            .mobile-filter-switch::after {
                content: '';
                position: absolute;
                top: 1.5px;
                left: 1.5px;
                width: 15px;
                height: 15px;
                border-radius: 999px;
                background: #FFFFFF;
                transition: transform 0.2s ease-in-out;
            }

            .selected-location-banner {
                animation: selectedLocationBannerIn 280ms cubic-bezier(0.22, 1, 0.36, 1);
                transform-origin: top center;
                box-shadow: 0 18px 42px rgba(92, 45, 98, 0.08);
            }

            .selected-location-banner__title {
                font-family: 'Poppins', sans-serif;
                font-size: 30px;
                font-weight: 700;
                line-height: 1.05;
                color: #5C2D62;
            }

            .selected-location-banner__subtitle {
                font-family: 'Poppins', sans-serif;
                font-size: 14px;
                font-weight: 400;
                line-height: 1.3;
                color: #DD3888;
            }

            @keyframes selectedLocationBannerIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px) scale(0.98);
                }

                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            .mobile-filter-pill.is-active .mobile-filter-switch {
                background: #DD3888;
            }

            .mobile-filter-pill.is-active .mobile-filter-switch::after {
                transform: translateX(15px);
            }

            /* Phone frame: two 146px cards, 20px apart, in a 23px gutter.
               The 1fr tracks were 154px wide, so the 160px cards overflowed
               them and landed 10px further out than the design. */
            @media (max-width: 426px) {
                .profile-list-cards-grid {
                    display: grid !important;
                    grid-template-columns: repeat(2, 146px);
                    gap: 20px !important;
                    justify-content: center;
                    justify-items: center;
                }
            }

            /* Desktop grid pinned to the design's measurements: five 214px cards
               with a 17.5px gutter = 1140px, the same content width as the eco
               badge. The fluid `xl:grid-cols-5 gap-4` gave 217.6px columns, so
               cards sat 23.6px apart instead of 17.5px.

               The 231.5px column pitch always matched the design; what did not
               was how it split. The design gives the card 214 and the gutter
               17.5, we gave it 210 and 21.5 — same rhythm, four pixels narrower
               card, and a content block 1140 → 1136 that pulled the header and
               the footer off the grid with it. */
            /* auto-fill rather than a fixed five: the English homepage puts a
               208px country sidebar beside this grid, leaving ~897px, and five
               pinned columns came to the full content width — so the row
               overflowed and the first card sat underneath the sidebar. The
               card keeps its design width; the row simply holds as many as
               fit. */
            @media (min-width: 1280px) {
                .profile-list-cards-grid {
                    grid-template-columns: repeat(auto-fill, 214px);
                    gap: 17.5px;
                    justify-content: center;
                }
            }

            @media (max-width: 767px) {

            @media (max-width: 320px) {
                .mobile-top-results {
                    padding-left: 4px;
                    padding-right: 12px;
                }

                .selected-location-banner__title {
                    font-size: 24px;
                }

                .mobile-top-results-heading {
                    gap: 8px;
                }

                .mobile-top-results-icon {
                    width: 24px !important;
                    height: 24px !important;
                }

                .mobile-top-results-title {
                    font-size: 24px;
                }
            }

            @media (min-width: 768px) {
                .mobile-top-results {
                    display: none;
                }
            }

            @media (max-width: 767px) {
                .selected-location-banner {
                    height: auto;
                    min-height: 100px;
                    padding-top: 18px;
                    padding-bottom: 18px;
                }

                .selected-location-banner__title {
                    font-size: 28px;
                }
            }
        </style>

        @if ($isEnglishHomepage && $hasSelectedLocation)
            <div class="mb-5 md:mb-6">
                <div class="selected-location-banner mx-auto flex w-full max-w-[904px] items-center gap-4 rounded-xl border-2 border-[#F2F2F2] bg-white px-5 md:h-[100px] md:px-6">
                    @if ($showSelectedCountryFlag)
                        <img src="{{ $selectedCountryFlagUrl }}" alt="{{ $selectedLocationTitle }}" class="h-8 w-8 shrink-0 rounded-full object-cover">
                    @else
                        <img src="{{ asset('images/icons/MapPinned.svg') }}" alt="Selected location" class="h-12 w-12 shrink-0">
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="selected-location-banner__title">{{ $selectedLocationTitle }}</div>
                        @if (filled($selectedLocationSubtitle))
                            <div class="selected-location-banner__subtitle">{{ $selectedLocationSubtitle }}</div>
                        @endif
                    </div>
                    <button wire:click="clearLocation"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#DD3888] text-white transition-transform duration-200 hover:scale-105"
                        title="{{ __('front.profiles.list.clear_all_filters') }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        <div class="mobile-top-results md:hidden">
            <div class="mobile-top-results-heading mb-5">
                <x-icons name="search" class="mobile-top-results-icon w-[27px] h-[27px] text-[#DD3888]" />
                <h1 class="mobile-top-results-title">{{ __('front.profiles.list.topresults') }}</h1>
            </div>

            @if ($isEnglishHomepage)
                <div class="mobile-top-results-search">
                    <!-- Mobile-only opener for fullscreen country search (fallback) -->
                    <button id="mobile-country-open" type="button" class="mobile-country-open-button md:hidden" aria-label="{{ __('front.profiles.search.open_mobile_search') }}" onclick="(function(){var p=document.querySelector('.search-country-mobile-picker'); if(p){p.style.display='block'; document.documentElement.style.overflow='hidden'; document.body.style.overflow='hidden';}})()"></button>
                    <script>
                        (function(){
                            try {
                                var b = document.getElementById('mobile-country-open');
                                if (!b) return;
                                b.addEventListener('click', function (e) {
                                    try {
                                        var p = document.querySelector('.search-country-mobile-picker');
                                        if (p) {
                                            p.style.display = 'block';
                                            document.documentElement.style.overflow = 'hidden';
                                            document.body.style.overflow = 'hidden';
                                        }
                                    } catch (err2) {}
                                }, true);
                            } catch (err) {}
                        })();
                    </script>
                    <livewire:search-profiles :key="'en-mobile-search'" />
                </div>
            @endif

            <div class="mobile-filter-group mb-5">
                <button wire:click.debounce.300ms="toggleAgeGroup('')"
                    class="mobile-filter-pill icon-pill {{ $ageGroup === '' ? 'is-active' : '' }}">
                    <svg class="mobile-filter-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M4 21C4.8 17.9 7.7 16 12 16C16.3 16 19.2 17.9 20 21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    {{ __('front.profiles.list.all_girls') }}
                </button>

                @foreach ([
                    '18-25' => __('front.profiles.list.age_18_25'),
                    '26-30' => __('front.profiles.list.age_26_30'),
                    '31-35' => __('front.profiles.list.age_31_35'),
                    '36-40' => __('front.profiles.list.age_36_40'),
                    '40-50' => __('front.profiles.list.age_40_50'),
                    '50+' => __('front.profiles.list.age_50_plus'),
                ] as $value => $label)
                    <button wire:click.debounce.300ms="toggleAgeGroup('{{ $value }}')"
                        class="mobile-filter-pill {{ $ageGroup === $value ? ($value === '40-50' ? 'is-highlighted' : 'is-active') : '' }}">
                        {{ $label }}
                    </button>
                @endforeach

                @if ($this->activeFiltersCount() > 0)
                    <button wire:click.debounce.300ms="resetFilters" wire:loading.attr="disabled" wire:target="resetFilters"
                        class="mobile-filter-pill mobile-filter-clear" title="{{ __('front.profiles.list.clear_all_filters') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                @endif
            </div>

            <div class="mobile-filter-divider"></div>

            <div class="mobile-filter-group">
                <button wire:click.debounce.300ms="toggleRecommendation"
                    class="mobile-filter-pill icon-pill {{ $sortRecommendation !== '' ? 'is-active' : '' }}">
                    <svg class="mobile-filter-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 5V19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M7 10L12 5L17 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ __('front.profiles.list.recommendation') }}
                </button>

                <button wire:click.debounce.300ms="toggleVerifiedPhoto"
                    aria-pressed="{{ $hasVerifiedPhoto ? 'true' : 'false' }}"
                    class="mobile-filter-pill switch-pill {{ $hasVerifiedPhoto ? 'is-active' : '' }}">
                    {{ __('front.profiles.list.verified_photo') }}
                    <span class="mobile-filter-switch"></span>
                </button>

                <button wire:click.debounce.300ms="toggleVideo"
                    aria-pressed="{{ $hasVideo ? 'true' : 'false' }}"
                    class="mobile-filter-pill switch-pill {{ $hasVideo ? 'is-active' : '' }}">
                    {{ __('front.profiles.list.video') }}
                    <span class="mobile-filter-switch"></span>
                </button>

                <button wire:click.debounce.300ms="togglePornActress"
                    aria-pressed="{{ $isPornActress ? 'true' : 'false' }}"
                    class="mobile-filter-pill switch-pill {{ $isPornActress ? 'is-active' : '' }}">
                    {{ __('front.profiles.list.porn_actress') }}
                    <span class="mobile-filter-switch"></span>
                </button>

                <button wire:click.debounce.300ms="toggleNew"
                    class="mobile-filter-pill icon-pill {{ $sortNew !== '' ? 'is-active' : '' }}">
                    <svg class="mobile-filter-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 5V19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M7 10L12 5L17 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" opacity="0.45"/>
                    </svg>
                    {{ __('front.profiles.list.new') }}
                </button>

                <button wire:click.debounce.300ms="toggleRating"
                    class="mobile-filter-pill icon-pill {{ $hasRating ? 'is-active' : '' }}">
                    <svg class="mobile-filter-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M5 19V11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M12 19V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M19 19V4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    {{ __('front.profiles.list.rating') }}
                </button>

                {{-- The segment dropdown is not in the design's filter row. It
                     stayed a filter you can still reach: ?segment=<id> keeps
                     working, and the admin's segment links rely on it. --}}

                @if ($this->activeFiltersCount() > 0)
                    <button wire:click.debounce.300ms="resetFilters" wire:loading.attr="disabled" wire:target="resetFilters"
                        class="mobile-filter-pill mobile-filter-clear" title="{{ __('front.profiles.list.clear_all_filters') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                @endif
            </div>
        </div>

        <!-- Age Group Filters - Desktop Buttons -->
        <div class="hidden md:flex flex-wrap gap-2 md:gap-3 mb-3 md:mb-4">
            <!-- All Girls Filter -->
            <button wire:click.debounce.300ms="toggleAgeGroup('')"
                :class="{
                    'filter-pill active': '{{ $ageGroup }}' === '',
                    'filter-pill inactive': '{{ $ageGroup }}' !== ''
                }">
                <img src="{{ asset('images/icons/profile.svg') }}" alt="Profile Icon" class="icon" style="filter: invert(34%) sepia(98%) saturate(1551%) hue-rotate(310deg) brightness(90%) contrast(95%);">
                {{ __('front.profiles.list.all_girls') }}
            </button>

            @foreach ([
                '18-25' => __('front.profiles.list.age_18_25'),
                '26-30' => __('front.profiles.list.age_26_30'),
                '31-35' => __('front.profiles.list.age_31_35'),
                '36-40' => __('front.profiles.list.age_36_40'),
                '40-50' => __('front.profiles.list.age_40_50'),
                '50+' => __('front.profiles.list.age_50_plus'),
            ] as $value => $label)
                <button wire:click.debounce.300ms="toggleAgeGroup('{{ $value }}')"
                    :class="{
                        'filter-pill active': '{{ $ageGroup }}' === '{{ $value }}',
                        'filter-pill inactive': '{{ $ageGroup }}' !== '{{ $value }}'
                    }">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <!-- Feature Filters -->
        <div class="hidden md:flex flex-wrap gap-2 md:gap-3">
            <!-- Recommendation Filter -->
            <button wire:click.debounce.300ms="toggleRecommendation"
                :class="{
                    'filter-pill active': '{{ $sortRecommendation }}' !== '',
                    'filter-pill inactive': '{{ $sortRecommendation }}' === ''
                }">
                <img src="{{ asset('images/icons/ArrowUp.svg') }}" alt="Arrow Up Icon" class="icon" style="filter: invert(34%) sepia(98%) saturate(1551%) hue-rotate(310deg) brightness(90%) contrast(95%);">
                {{ __('front.profiles.list.recommendation') }}
            </button>

            <!-- Verified Photo Filter -->
            {{-- The switch is a <div>, so the pressed state is invisible to
                 assistive tech without aria-pressed. --}}
            <button wire:click.debounce.300ms="toggleVerifiedPhoto"
                aria-pressed="{{ $hasVerifiedPhoto ? 'true' : 'false' }}"
                :class="{
                    'filter-pill active': {{ $hasVerifiedPhoto ? 'true' : 'false' }},
                    'filter-pill inactive': {{ !$hasVerifiedPhoto ? 'true' : 'false' }}
                }">
                {{ __('front.profiles.list.verified_photo') }}
                <div class="filter-switch">
                    <div class="filter-switch-thumb"></div>
                </div>
            </button>

            <!-- Video Filter -->
            <button wire:click.debounce.300ms="toggleVideo"
                aria-pressed="{{ $hasVideo ? 'true' : 'false' }}"
                :class="{
                    'filter-pill active': {{ $hasVideo ? 'true' : 'false' }},
                    'filter-pill inactive': {{ !$hasVideo ? 'true' : 'false' }}
                }">
                {{ __('front.profiles.list.video') }}
                <div class="filter-switch">
                    <div class="filter-switch-thumb"></div>
                </div>
            </button>

            <!-- Porn Actress Filter -->
            <button wire:click.debounce.300ms="togglePornActress"
                aria-pressed="{{ $isPornActress ? 'true' : 'false' }}"
                :class="{
                    'filter-pill active': {{ $isPornActress ? 'true' : 'false' }},
                    'filter-pill inactive': {{ !$isPornActress ? 'true' : 'false' }}
                }">
                {{ __('front.profiles.list.porn_actress') }}
                <div class="filter-switch">
                    <div class="filter-switch-thumb"></div>
                </div>
            </button>

            <!-- New Filter -->
            <button wire:click.debounce.300ms="toggleNew"
                :class="{
                    'filter-pill active': '{{ $sortNew }}' !== '',
                    'filter-pill inactive': '{{ $sortNew }}' === ''
                }">
                {{ __('front.profiles.list.new') }}
            </button>

            <!-- Rating Filter -->
            <button wire:click.debounce.300ms="toggleRating"
                :class="{
                    'filter-pill active': {{ $hasRating ? 'true' : 'false' }},
                    'filter-pill inactive': {{ !$hasRating ? 'true' : 'false' }}
                }">
                <img src="{{ asset('images/icons/lock.svg') }}" alt="Lock Icon" class="icon">
                {{ __('front.profiles.list.rating') }}
            </button>

            {{-- Same on desktop: the design's filter row has pills, not a
                 segment dropdown. The filter itself is untouched. --}}

            <!-- Clear All Filters Button -->
            @if ($this->activeFiltersCount() > 0)
                <button wire:click.debounce.300ms="resetFilters" wire:loading.attr="disabled" wire:target="resetFilters"
                    class="filter-pill inactive"
                    title="{{ __('front.profiles.list.clear_all_filters') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            @endif
        </div>

        <!-- Active Filters Count -->
        @if ($this->activeFiltersCount() > 0)
            <div class="mt-4">
                <span class="text-sm text-gray-600">
                    {{ __('front.profiles.list.filters_active', ['count' => $this->activeFiltersCount()]) }}
                </span>
            </div>
        @endif
    </div>

    @if ($this->profiles && $this->profiles->count() > 0)
        <!-- Profiles Grid -->
        <div class="space-y-4 md:space-y-6 relative px-0 md:px-8 lg:px-12">
            <!-- Loading Overlay -->
            <div wire:loading
                wire:target="toggleAgeGroup,toggleRecommendation,toggleVerifiedPhoto,toggleVideo,togglePornActress,toggleNew,toggleRating,resetFilters,updateFilters"
                class="absolute inset-0 bg-white/80 z-10 flex items-center justify-center pt-12">
                <div class="flex flex-col items-center">
                    <svg class="animate-spin h-12 w-12 text-primary-600" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>
            </div>

            <div class="{{ $profileGridClasses }}">
                @foreach ($this->profiles as $profile)
                    @if ($loop->iteration === $ecoBadgeInsertAt)
                        <div class="col-span-full my-4 flex justify-center px-2">
                            <x-ecobadge />
                        </div>
                    @endif

                    @if ($loop->iteration === $advertInsertAt)
                        <div class="col-span-full my-6 lg:-my-20 -mx-2 md:-mx-8 lg:-mx-12 relative z-0">
                            <x-advert-hero />
                        </div>
                    @endif

                    {{-- Photos are never blurred here.

                         This used to pick roughly every second card by
                         `crc32($id) % 2` and render it in the blurred
                         `vip-detail` variant. The design does the opposite: the
                         photo is always sharp and it is the *rating* that is
                         locked, which x-profile-card now handles through the
                         `view-ratings` gate. --}}
                    <x-profile-card :profile="$profile" />
                @endforeach
            </div>

            <!-- Pagination -->
            {{ $this->profiles->links('livewire.pagination-links') }}
        </div>
    @else
        <!-- Empty State -->
        <x-filter-empty-state wire:loading.remove class="mt-6" />
    @endif


<!-- Latest News Section -->
<section class="news-section py-12 md:py-16 lg:py-20">
    <div class="px-4 md:px-8 lg:px-12 mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <img src="{{ asset('images/icons/Newspaper.svg') }}" alt="Novinky" width="36" height="36" class="news-icon" />
            <h2 class="news-title m-0">{{ __('blogs.latest_news') }}</h2>
        </div>

        {{-- Real blog posts, managed in the admin under "Blog příspěvky".

             These two cards were hardcoded: fixed images, a fixed "25. 4. 2025"
             date, a fixed "5 min" reading time, filler body text and a <button>
             that led nowhere — while the Page model, the blog admin resource and
             aproximateReadingTime() all already existed. --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @forelse($this->latestPosts as $post)
                <article class="news-card">
                    <a href="{{ route('pages.show', $post->slug) }}" class="block relative">
                        @if($post->hasMedia('header-image'))
                            <img src="{{ $post->getFirstMediaUrl('header-image') }}" alt="{{ $post->title }}" class="news-image" />
                        @else
                            <div class="news-image" style="display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#f9edf4,#efe4f2);">
                                <img src="{{ asset('images/icons/Newspaper.svg') }}" alt="" aria-hidden="true" width="48" height="48" style="opacity:.5;" />
                            </div>
                        @endif

                        <div class="news-badges">
                            <div class="news-badge badge-date">
                                <img src="{{ asset('images/icons/calendar.svg') }}" alt="calendar" width="16" height="16" />
                                <span class="badge-text">{{ $post->created_at->translatedFormat('j. n. Y') }}</span>
                            </div>
                            @if($post->aproximateReadingTime() > 0)
                                <div class="news-badge badge-time">
                                    <img src="{{ asset('images/icons/clock.svg') }}" alt="clock" width="16" height="16" />
                                    <span class="badge-text">{{ $post->aproximateReadingTime() }} {{ __('blogs.min_read') }}</span>
                                </div>
                            @endif
                        </div>
                    </a>

                    <h3 class="news-card-title">
                        <a href="{{ route('pages.show', $post->slug) }}">{{ $post->title }}</a>
                    </h3>

                    @if($post->description)
                        <p class="news-card-desc">{{ $post->description }}</p>
                    @endif

                    <a href="{{ route('pages.show', $post->slug) }}" class="news-button">{{ __('blogs.read_article') }}</a>
                </article>
            @empty
                <p class="news-card-desc md:col-span-2"><x-empty-value variant="text" /></p>
            @endforelse
        </div>
    </div>
</section>

</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if Swiper is available
            if (typeof Swiper === 'undefined') {
                console.error('Swiper is not loaded. Make sure to build assets with npm run build');
                return;
            }

            initializeSwipers();
        });

        // Initialize Swiper instances
        function initializeSwipers() {
            // Find all profile swipers and initialize them
            document.querySelectorAll('[class*="profile-swiper-"]').forEach(function(swiperEl) {
                const profileId = swiperEl.className.match(/profile-swiper-(\d+)/)[1];

                // Destroy existing swiper instance if any
                if (swiperEl.swiper) {
                    swiperEl.swiper.destroy(true, true);
                }

                // Initialize new Swiper instance
                new Swiper(swiperEl, {
                    loop: true,
                    pagination: {
                        el: `.swiper-pagination-${profileId}`,
                        clickable: true,
                        dynamicBullets: true,
                    },
                    preloadImages: true,
                });
            });
        }

        // Re-initialize Swiper when Livewire updates content
        document.addEventListener('livewire:navigated', function() {
            setTimeout(initializeSwipers, 100);
        });

        // Register Livewire hooks after Livewire is loaded
        document.addEventListener('livewire:load', function() {
            Livewire.hook('morph.updated', () => {
                setTimeout(initializeSwipers, 100);
            });

            // Also listen for specific Livewire events
            Livewire.on('profiles-updated', () => {
                setTimeout(initializeSwipers, 100);
            });
        });
    </script>
@endpush
