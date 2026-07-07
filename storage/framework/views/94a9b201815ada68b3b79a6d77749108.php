<?php
    $isEnglishHomepage = app()->getLocale() === 'en' && request()->routeIs('profiles.index');
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
?>

<div>
    <!--[if BLOCK]><![endif]--><?php if (! ($isEnglishHomepage)): ?>
    <div class="mt-1 md:mt-2 md:px-8 lg:px-12 py-4 md:py-9 flex items-center gap-2 md:gap-4">
        <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'search','class' => 'w-5 h-5 md:w-7 md:h-7','style' => 'color: #DD3888;']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','class' => 'w-5 h-5 md:w-7 md:h-7','style' => 'color: #DD3888;']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd)): ?>
<?php $attributes = $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd; ?>
<?php unset($__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd)): ?>
<?php $component = $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd; ?>
<?php unset($__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd); ?>
<?php endif; ?>
        <h1 class="text-2xl md:text-4xl font-bold" style="color: #5C2D62;"><?php echo e(__('front.profiles.list.topresults')); ?></h1>
    </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

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
                padding: 8px 16px;
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
                padding: <?php echo e($isEnglishHomepage ? '40px 16px 0 16px' : '300px 16px 0 8px'); ?>;
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

            @media (max-width: 426px) {
                .profile-list-cards-grid {
                    display: grid !important;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: 20px !important;
                    justify-items: center;
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

        <!--[if BLOCK]><![endif]--><?php if($isEnglishHomepage && $hasSelectedLocation): ?>
            <div class="mb-5 md:mb-6">
                <div class="selected-location-banner mx-auto flex w-full max-w-[904px] items-center gap-4 rounded-xl border-2 border-[#F2F2F2] bg-white px-5 md:h-[100px] md:px-6">
                    <!--[if BLOCK]><![endif]--><?php if($showSelectedCountryFlag): ?>
                        <img src="<?php echo e($selectedCountryFlagUrl); ?>" alt="<?php echo e($selectedLocationTitle); ?>" class="h-8 w-8 shrink-0 rounded-full object-cover">
                    <?php else: ?>
                        <img src="<?php echo e(asset('images/icons/MapPinned.svg')); ?>" alt="Selected location" class="h-12 w-12 shrink-0">
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <div class="min-w-0 flex-1">
                        <div class="selected-location-banner__title"><?php echo e($selectedLocationTitle); ?></div>
                        <!--[if BLOCK]><![endif]--><?php if(filled($selectedLocationSubtitle)): ?>
                            <div class="selected-location-banner__subtitle"><?php echo e($selectedLocationSubtitle); ?></div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <button wire:click="clearLocation"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#DD3888] text-white transition-transform duration-200 hover:scale-105"
                        title="<?php echo e(__('front.profiles.list.clear_all_filters')); ?>">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <div class="mobile-top-results md:hidden">
            <div class="mobile-top-results-heading mb-5">
                <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'search','class' => 'mobile-top-results-icon w-[27px] h-[27px] text-[#DD3888]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','class' => 'mobile-top-results-icon w-[27px] h-[27px] text-[#DD3888]']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd)): ?>
<?php $attributes = $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd; ?>
<?php unset($__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd)): ?>
<?php $component = $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd; ?>
<?php unset($__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd); ?>
<?php endif; ?>
                <h1 class="mobile-top-results-title"><?php echo e(__('front.profiles.list.topresults')); ?></h1>
            </div>

            <!--[if BLOCK]><![endif]--><?php if($isEnglishHomepage): ?>
                <div class="mobile-top-results-search">
                    <!-- Mobile-only opener for fullscreen country search (fallback) -->
                    <button id="mobile-country-open" type="button" class="mobile-country-open-button md:hidden" aria-label="<?php echo e(__('front.profiles.search.open_mobile_search')); ?>" onclick="(function(){var p=document.querySelector('.search-country-mobile-picker'); if(p){p.style.display='block'; document.documentElement.style.overflow='hidden'; document.body.style.overflow='hidden';}})()"></button>
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
                    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('search-profiles', []);

$__html = app('livewire')->mount($__name, $__params, 'en-mobile-search', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <div class="mobile-filter-group mb-5">
                <button wire:click.debounce.300ms="toggleAgeGroup('')"
                    class="mobile-filter-pill icon-pill <?php echo e($ageGroup === '' ? 'is-active' : ''); ?>">
                    <svg class="mobile-filter-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M4 21C4.8 17.9 7.7 16 12 16C16.3 16 19.2 17.9 20 21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    <?php echo e(__('front.profiles.list.all_girls')); ?>

                </button>

                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = [
                    '18-25' => __('front.profiles.list.age_18_25'),
                    '26-30' => __('front.profiles.list.age_26_30'),
                    '31-35' => __('front.profiles.list.age_31_35'),
                    '36-40' => __('front.profiles.list.age_36_40'),
                    '40-50' => __('front.profiles.list.age_40_50'),
                    '50+' => __('front.profiles.list.age_50_plus'),
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button wire:click.debounce.300ms="toggleAgeGroup('<?php echo e($value); ?>')"
                        class="mobile-filter-pill <?php echo e($ageGroup === $value ? ($value === '40-50' ? 'is-highlighted' : 'is-active') : ''); ?>">
                        <?php echo e($label); ?>

                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

                <!--[if BLOCK]><![endif]--><?php if($this->activeFiltersCount() > 0): ?>
                    <button wire:click.debounce.300ms="resetFilters" wire:loading.attr="disabled" wire:target="resetFilters"
                        class="mobile-filter-pill mobile-filter-clear" title="<?php echo e(__('front.profiles.list.clear_all_filters')); ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            <div class="mobile-filter-divider"></div>

            <div class="mobile-filter-group">
                <button wire:click.debounce.300ms="toggleRecommendation"
                    class="mobile-filter-pill icon-pill <?php echo e($sortRecommendation !== '' ? 'is-active' : ''); ?>">
                    <svg class="mobile-filter-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 5V19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M7 10L12 5L17 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?php echo e(__('front.profiles.list.recommendation')); ?>

                </button>

                <button wire:click.debounce.300ms="toggleVerifiedPhoto"
                    class="mobile-filter-pill switch-pill <?php echo e($hasVerifiedPhoto ? 'is-active' : ''); ?>">
                    <?php echo e(__('front.profiles.list.verified_photo')); ?>

                    <span class="mobile-filter-switch"></span>
                </button>

                <button wire:click.debounce.300ms="toggleVideo"
                    class="mobile-filter-pill switch-pill <?php echo e($hasVideo ? 'is-active' : ''); ?>">
                    <?php echo e(__('front.profiles.list.video')); ?>

                    <span class="mobile-filter-switch"></span>
                </button>

                <button wire:click.debounce.300ms="togglePornActress"
                    class="mobile-filter-pill switch-pill <?php echo e($isPornActress ? 'is-active' : ''); ?>">
                    <?php echo e(__('front.profiles.list.porn_actress')); ?>

                    <span class="mobile-filter-switch"></span>
                </button>

                <button wire:click.debounce.300ms="toggleNew"
                    class="mobile-filter-pill icon-pill <?php echo e($sortNew !== '' ? 'is-active' : ''); ?>">
                    <svg class="mobile-filter-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 5V19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M7 10L12 5L17 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" opacity="0.45"/>
                    </svg>
                    <?php echo e(__('front.profiles.list.new')); ?>

                </button>

                <button wire:click.debounce.300ms="toggleRating"
                    class="mobile-filter-pill icon-pill <?php echo e($hasRating ? 'is-active' : ''); ?>">
                    <svg class="mobile-filter-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M5 19V11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M12 19V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M19 19V4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    <?php echo e(__('front.profiles.list.rating')); ?>

                </button>

                <!--[if BLOCK]><![endif]--><?php if($this->activeFiltersCount() > 0): ?>
                    <button wire:click.debounce.300ms="resetFilters" wire:loading.attr="disabled" wire:target="resetFilters"
                        class="mobile-filter-pill mobile-filter-clear" title="<?php echo e(__('front.profiles.list.clear_all_filters')); ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>

        <!-- Age Group Filters - Desktop Buttons -->
        <div class="hidden md:flex flex-wrap gap-2 md:gap-3 mb-3 md:mb-4">
            <!-- All Girls Filter -->
            <button wire:click.debounce.300ms="toggleAgeGroup('')"
                :class="{
                    'filter-pill active': '<?php echo e($ageGroup); ?>' === '',
                    'filter-pill inactive': '<?php echo e($ageGroup); ?>' !== ''
                }">
                <img src="<?php echo e(asset('images/icons/profile.svg')); ?>" alt="Profile Icon" class="icon" style="filter: invert(34%) sepia(98%) saturate(1551%) hue-rotate(310deg) brightness(90%) contrast(95%);">
                <?php echo e(__('front.profiles.list.all_girls')); ?>

            </button>

            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = [
                '18-25' => __('front.profiles.list.age_18_25'),
                '26-30' => __('front.profiles.list.age_26_30'),
                '31-35' => __('front.profiles.list.age_31_35'),
                '36-40' => __('front.profiles.list.age_36_40'),
                '40-50' => __('front.profiles.list.age_40_50'),
                '50+' => __('front.profiles.list.age_50_plus'),
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button wire:click.debounce.300ms="toggleAgeGroup('<?php echo e($value); ?>')"
                    :class="{
                        'filter-pill active': '<?php echo e($ageGroup); ?>' === '<?php echo e($value); ?>',
                        'filter-pill inactive': '<?php echo e($ageGroup); ?>' !== '<?php echo e($value); ?>'
                    }">
                    <?php echo e($label); ?>

                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <!-- Feature Filters -->
        <div class="hidden md:flex flex-wrap gap-2 md:gap-3">
            <!-- Recommendation Filter -->
            <button wire:click.debounce.300ms="toggleRecommendation"
                :class="{
                    'filter-pill active': '<?php echo e($sortRecommendation); ?>' !== '',
                    'filter-pill inactive': '<?php echo e($sortRecommendation); ?>' === ''
                }">
                <img src="<?php echo e(asset('images/icons/ArrowUp.svg')); ?>" alt="Arrow Up Icon" class="icon" style="filter: invert(34%) sepia(98%) saturate(1551%) hue-rotate(310deg) brightness(90%) contrast(95%);">
                <?php echo e(__('front.profiles.list.recommendation')); ?>

            </button>

            <!-- Verified Photo Filter -->
            <button wire:click.debounce.300ms="toggleVerifiedPhoto"
                :class="{
                    'filter-pill active': <?php echo e($hasVerifiedPhoto ? 'true' : 'false'); ?>,
                    'filter-pill inactive': <?php echo e(!$hasVerifiedPhoto ? 'true' : 'false'); ?>

                }">
                <?php echo e(__('front.profiles.list.verified_photo')); ?>

                <div class="filter-switch">
                    <div class="filter-switch-thumb"></div>
                </div>
            </button>

            <!-- Video Filter -->
            <button wire:click.debounce.300ms="toggleVideo"
                :class="{
                    'filter-pill active': <?php echo e($hasVideo ? 'true' : 'false'); ?>,
                    'filter-pill inactive': <?php echo e(!$hasVideo ? 'true' : 'false'); ?>

                }">
                <?php echo e(__('front.profiles.list.video')); ?>

                <div class="filter-switch">
                    <div class="filter-switch-thumb"></div>
                </div>
            </button>

            <!-- Porn Actress Filter -->
            <button wire:click.debounce.300ms="togglePornActress"
                :class="{
                    'filter-pill active': <?php echo e($isPornActress ? 'true' : 'false'); ?>,
                    'filter-pill inactive': <?php echo e(!$isPornActress ? 'true' : 'false'); ?>

                }">
                <?php echo e(__('front.profiles.list.porn_actress')); ?>

                <div class="filter-switch">
                    <div class="filter-switch-thumb"></div>
                </div>
            </button>

            <!-- New Filter -->
            <button wire:click.debounce.300ms="toggleNew"
                :class="{
                    'filter-pill active': '<?php echo e($sortNew); ?>' !== '',
                    'filter-pill inactive': '<?php echo e($sortNew); ?>' === ''
                }">
                <?php echo e(__('front.profiles.list.new')); ?>

            </button>

            <!-- Rating Filter -->
            <button wire:click.debounce.300ms="toggleRating"
                :class="{
                    'filter-pill active': <?php echo e($hasRating ? 'true' : 'false'); ?>,
                    'filter-pill inactive': <?php echo e(!$hasRating ? 'true' : 'false'); ?>

                }">
                <img src="<?php echo e(asset('images/icons/lock.svg')); ?>" alt="Lock Icon" class="icon">
                <?php echo e(__('front.profiles.list.rating')); ?>

            </button>

            <!-- Clear All Filters Button -->
            <!--[if BLOCK]><![endif]--><?php if($this->activeFiltersCount() > 0): ?>
                <button wire:click.debounce.300ms="resetFilters" wire:loading.attr="disabled" wire:target="resetFilters"
                    class="filter-pill inactive"
                    title="<?php echo e(__('front.profiles.list.clear_all_filters')); ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <!-- Active Filters Count -->
        <!--[if BLOCK]><![endif]--><?php if($this->activeFiltersCount() > 0): ?>
            <div class="mt-4">
                <span class="text-sm text-gray-600">
                    <?php echo e(__('front.profiles.list.filters_active', ['count' => $this->activeFiltersCount()])); ?>

                </span>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    <!--[if BLOCK]><![endif]--><?php if($this->profiles && $this->profiles->count() > 0): ?>
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

            <div class="<?php echo e($profileGridClasses); ?>">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->profiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $profile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <!--[if BLOCK]><![endif]--><?php if($loop->iteration === $ecoBadgeInsertAt): ?>
                        <div class="col-span-full my-4 flex justify-center px-2">
                            <?php if (isset($component)) { $__componentOriginal0bc54ccb1d761eeae6a771aa1e5eb3d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0bc54ccb1d761eeae6a771aa1e5eb3d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ecobadge','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ecobadge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0bc54ccb1d761eeae6a771aa1e5eb3d0)): ?>
<?php $attributes = $__attributesOriginal0bc54ccb1d761eeae6a771aa1e5eb3d0; ?>
<?php unset($__attributesOriginal0bc54ccb1d761eeae6a771aa1e5eb3d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0bc54ccb1d761eeae6a771aa1e5eb3d0)): ?>
<?php $component = $__componentOriginal0bc54ccb1d761eeae6a771aa1e5eb3d0; ?>
<?php unset($__componentOriginal0bc54ccb1d761eeae6a771aa1e5eb3d0); ?>
<?php endif; ?>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <!--[if BLOCK]><![endif]--><?php if($loop->iteration === $advertInsertAt): ?>
                        <div class="col-span-full my-6 lg:-my-20 -mx-2 md:-mx-8 lg:-mx-12 relative z-0">
                            <?php if (isset($component)) { $__componentOriginal15188872f7093b195f064e354f330b96 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal15188872f7093b195f064e354f330b96 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.advert-hero','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('advert-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal15188872f7093b195f064e354f330b96)): ?>
<?php $attributes = $__attributesOriginal15188872f7093b195f064e354f330b96; ?>
<?php unset($__attributesOriginal15188872f7093b195f064e354f330b96); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal15188872f7093b195f064e354f330b96)): ?>
<?php $component = $__componentOriginal15188872f7093b195f064e354f330b96; ?>
<?php unset($__componentOriginal15188872f7093b195f064e354f330b96); ?>
<?php endif; ?>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <?php
                        $imageOverride = null;
                        $idx = $loop->iteration;
                        // 6-10 => model6..10
                        if ($idx >= 6 && $idx <= 10) {
                            $imageOverride = asset('images/models/model' . $idx . '.png');
                        }
                        // 11-15 => model11..15
                        elseif ($idx >= 11 && $idx <= 15) {
                            $imageOverride = asset('images/models/model' . $idx . '.png');
                        }
                        // 16-20 => model16..20
                        elseif ($idx >= 16 && $idx <= 20) {
                            $imageOverride = asset('images/models/model' . $idx . '.png');
                        }
                        // 21-25 => map back to model11..15
                        elseif ($idx >= 21 && $idx <= 25) {
                            $mapped = 11 + ($idx - 21); // 21->11, 25->15
                            $imageOverride = asset('images/models/model' . $mapped . '.png');
                        }
                        
                        $cardVariant = auth()->check() ? null : 'vip-detail';
                    ?>
                    <?php if (isset($component)) { $__componentOriginal2299f79f212ad7b2f1b6f23328beba2f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2299f79f212ad7b2f1b6f23328beba2f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.profile-card','data' => ['profile' => $profile,'imageOverride' => $imageOverride,'variant' => $cardVariant]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('profile-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['profile' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($profile),'image-override' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($imageOverride),'variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($cardVariant)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2299f79f212ad7b2f1b6f23328beba2f)): ?>
<?php $attributes = $__attributesOriginal2299f79f212ad7b2f1b6f23328beba2f; ?>
<?php unset($__attributesOriginal2299f79f212ad7b2f1b6f23328beba2f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2299f79f212ad7b2f1b6f23328beba2f)): ?>
<?php $component = $__componentOriginal2299f79f212ad7b2f1b6f23328beba2f; ?>
<?php unset($__componentOriginal2299f79f212ad7b2f1b6f23328beba2f); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            <!-- Pagination -->
            <?php echo e($this->profiles()->links('livewire.pagination-links')); ?>

        </div>
    <?php else: ?>
        <!-- Empty State -->
        <?php if (isset($component)) { $__componentOriginalaaf3d42d021a3f789053706e030f96c2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaaf3d42d021a3f789053706e030f96c2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-empty-state','data' => ['wire:loading.remove' => true,'class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:loading.remove' => true,'class' => 'mt-6']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaaf3d42d021a3f789053706e030f96c2)): ?>
<?php $attributes = $__attributesOriginalaaf3d42d021a3f789053706e030f96c2; ?>
<?php unset($__attributesOriginalaaf3d42d021a3f789053706e030f96c2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaaf3d42d021a3f789053706e030f96c2)): ?>
<?php $component = $__componentOriginalaaf3d42d021a3f789053706e030f96c2; ?>
<?php unset($__componentOriginalaaf3d42d021a3f789053706e030f96c2); ?>
<?php endif; ?>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->


<!-- Latest News Section -->
<section class="news-section py-12 md:py-16 lg:py-20">
    <div class="px-4 md:px-8 lg:px-12 mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <img src="<?php echo e(asset('images/icons/Newspaper.svg')); ?>" alt="Novinky" width="36" height="36" class="news-icon" />
            <h2 class="news-title m-0">Poslední novinky</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <div class="news-card">
                <div class="relative">
                    <img src="<?php echo e(asset('images/news1.png')); ?>" alt="News 1" class="news-image" />
                    <div class="news-badges">
                        <div class="news-badge badge-date">
                            <img src="<?php echo e(asset('images/icons/calendar.svg')); ?>" alt="calendar" width="16" height="16" />
                            <span class="badge-text">25. 4. 2025</span>
                        </div>
                        <div class="news-badge badge-time">
                            <img src="<?php echo e(asset('images/icons/clock.svg')); ?>" alt="clock" width="16" height="16" />
                            <span class="badge-text">5 minut čtení</span>
                        </div>
                    </div>
                </div>

                <h3 class="news-card-title">Považována užitého za nesou užitých</h3>
                <p class="news-card-desc">Oprávněné aniž i odstoupil o snadno osoby vede grafikou osobami úmyslu 60 % poskytovat, dělí způsobem, § 36 veletrhu pověřit spravují zřejmém, k před platbě státu zvláštních tuzemsku. Dohodnou zvláštní provádí o nebezpečí kódech § 6 příjmu vhodným třetím</p>

                <button class="news-button">číst článek</button>
            </div>

            
            <div class="news-card">
                <div class="relative">
                    <img src="<?php echo e(asset('images/news2.png')); ?>" alt="News 2" class="news-image" />
                    <div class="news-badges">
                        <div class="news-badge badge-date">
                            <img src="<?php echo e(asset('images/icons/calendar.svg')); ?>" alt="calendar" width="16" height="16" />
                            <span class="badge-text">25. 4. 2025</span>
                        </div>
                        <div class="news-badge badge-time">
                            <img src="<?php echo e(asset('images/icons/clock.svg')); ?>" alt="clock" width="16" height="16" />
                            <span class="badge-text">5 minut čtení</span>
                        </div>
                    </div>
                </div>

                <h3 class="news-card-title">Souhlasem o tato i vždy každý k že nabytí uděleného, vůbec se skončením</h3>
                <p class="news-card-desc">Oprávněné aniž i odstoupil o snadno osoby vede osobami úmyslu 60 % poskytovat, dělí způsobem, § 36 veletrhu pověřit spravují zřejmém, k před platbě státu zvláštních tuzemsku. Dohodnou zvláštní provádí o nebezpečí kódech § 6 příjmu vhodným třetím</p>

                <button class="news-button">číst článek</button>
            </div>
        </div>
    </div>
</section>

</div>

<?php $__env->startPush('scripts'); ?>
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
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/livewire/profile-list.blade.php ENDPATH**/ ?>