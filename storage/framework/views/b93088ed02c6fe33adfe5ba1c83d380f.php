<?php
    $isEnglishHomepage = app()->getLocale() === 'en' && request()->routeIs('profiles.index');
?>

<div>
    <div class="grid grid-cols-1 lg:grid-cols-4">
        <!-- Left Panel - Countries List -->
        <div class="lg:col-span-1">
            <div class="p-6 sticky top-24">
                <div class="<?php echo e($isEnglishHomepage ? 'mx-auto w-[208px] space-y-[6px]' : 'space-y-2'); ?>">
                    <!--[if BLOCK]><![endif]--><?php if($isEnglishHomepage): ?>
                    <div class="mx-auto mb-6 hidden md:flex h-20 w-[208px] items-center justify-center rounded-[8px] border-2 border-[#F2F2F2] bg-transparent">
                        <span style="font-family:'Poppins', sans-serif;font-weight:700;font-size:18px;color:#5C2D62;line-height:1;"><?php echo e(__('front.profiles.list.topresults')); ?></span>
                    </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <!-- Individual Countries -->
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="<?php echo e($isEnglishHomepage ? 'space-y-1.5' : 'space-y-1'); ?>">
                        <!-- Country Button -->
                        <button wire:click="toggleCountryExpansion('<?php echo e($country->country_code); ?>')"
                            class="w-full flex items-center text-left transition-all duration-200 <?php echo e($isEnglishHomepage ? 'gap-[10px] px-0 py-[2px]' : 'gap-3 p-1'); ?> <?php echo e($selectedCountryCode === $country->country_code && !$selectedRegion ? ($isEnglishHomepage ? 'opacity-100' : 'bg-primary-50 text-primary-700') : ''); ?>">
                            <div class="overflow-hidden flex-shrink-0 flex items-center justify-center <?php echo e($isEnglishHomepage ? 'w-5 h-5 rounded-full' : 'w-7 h-7 rounded-full bg-gray-200'); ?>">
                                  <img src="https://flagcdn.com/<?php echo e(strtolower($country->country_code)); ?>.svg"
                                      alt="<?php echo e($country->country_name); ?>"
                                      class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1">
                                <div class="<?php echo e($isEnglishHomepage ? 'text-[14px] font-normal text-[#505050]' : 'font-medium text-sm'); ?>"><?php echo e($country->country_name ?? __('codes.' . strtolower($country->country_code))); ?></div>
                            </div>
                            <div class="ml-auto <?php echo e($isEnglishHomepage ? 'text-[12px] font-normal text-[#B8B8B8]' : 'text-sm text-gray-500'); ?>">
                                <?php echo e($country->profiles_count); ?>

                            </div>
                        </button>

                        <!-- Regions Dropdown -->
                        <!--[if BLOCK]><![endif]--><?php if($country->regions && count($country->regions) > 0 && in_array($country->country_code, $expandedCountries)): ?>
                        <div class="flex justify-end" >
                            <div class="<?php echo e($isEnglishHomepage ? 'w-[178px] rounded-xl space-y-1' : 'w-10/12 rounded-2xl space-y-0.5'); ?>">
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $country->regions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $region): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button wire:click="selectRegion('<?php echo e($country->country_code); ?>', '<?php echo e($region['region']); ?>')"
                                    class="w-full bg-gray-100 hover:bg-primary hover:text-white flex items-center gap-3 text-left text-sm <?php echo e($isEnglishHomepage ? 'px-3 py-2 rounded-lg' : 'p-1 px-3'); ?> <?php echo e($selectedCountryCode === $country->country_code && $selectedRegion == $region['region'] ? 'bg-primary text-white' : ''); ?> <?php echo e($isEnglishHomepage ? '' : ($loop->first ? 'rounded-t-lg' : '')); ?> <?php echo e($isEnglishHomepage ? '' : ($loop->last ? 'rounded-b-lg' : '')); ?>">
                                    <div class="flex items-center gap-3 flex-1">
                                        <div class="flex-shrink-0">
                                            <img src="<?php echo e(asset('images/icons/location.svg')); ?>" alt="" class="w-4 h-4 inline-block mr-2" />
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-sm"><?php echo e($region['region']); ?></div>
                                        </div>
                                    </div>
                                    <div class="text-xs hover:bg-primary hover:text-white ml-auto">
                                        <?php echo e($region['profiles_count']); ?>

                                    </div>
                                </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>

        <!-- Right Panel - Profiles -->
        <div class="lg:col-span-3">
            <!-- Header -->
            <div class="mb-6">
                <!--[if BLOCK]><![endif]--><?php if($selectedCountry || $selectedRegion): ?>
                <div class="mx-auto w-full max-w-[904px] h-[100px] border-2 border-[#F2F2F2] rounded-xl flex items-center gap-4 px-6">
                    <img src="/images/icons/MapPinned.svg" alt="map pinned" class="w-[48px] h-[48px] flex-shrink-0" />
                    <div class="flex flex-col justify-center flex-1">
                        <!--[if BLOCK]><![endif]--><?php if($selectedRegion): ?>
                            <div style="font-family:'Poppins', sans-serif; font-weight:700; font-size:30px; color:#5C2D62; line-height:1;"><?php echo e($selectedRegion); ?></div>
                            <div style="font-family:'Poppins', sans-serif; font-weight:400; font-size:14px; color:#DD3888;"><?php echo e($selectedCountry ? __('codes.' . strtolower($selectedCountry->country_code)) : ''); ?></div>
                        <?php else: ?>
                            <div style="font-family:'Poppins', sans-serif; font-weight:700; font-size:30px; color:#5C2D62; line-height:1;"><?php echo e($selectedCountry ? __('codes.' . strtolower($selectedCountry->country_code)) : __('front.countries.all_profiles')); ?></div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <!-- Clear Location Button -->
                    <button wire:click="clearLocation"
                        class="flex-shrink-0 p-3 rounded-full bg-primary text-white hover:bg-primary-600 transition-all duration-200"
                        title="<?php echo e(__('front.profiles.list.clear_all_filters')); ?>">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            <!-- Quick Filters -->
            <div class="mb-8">
                <!-- Age Group Filters -->
                <div class="flex flex-wrap gap-3 mb-4">
                    <!-- All Girls Filter -->
                    <button wire:click="toggleAgeGroup('')"
                        class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border-2 <?php echo e($ageGroup === '' ? 'border-primary text-gray-700 bg-white' : 'border-gray-100 text-gray-700 bg-white'); ?> hover:border-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        wire:target="toggleAgeGroup">
                        <span wire:target="toggleAgeGroup">
                            <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'users','class' => 'w-4 h-4 mr-2 '.e($ageGroup === '' ? 'text-primary' : 'text-gray-500').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'users','class' => 'w-4 h-4 mr-2 '.e($ageGroup === '' ? 'text-primary' : 'text-gray-500').'']); ?>
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
                        </span>
                        <?php echo e(__('front.profiles.list.all_girls')); ?>

                    </button>

                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = ['18-25' => 'age_18_25', '26-30' => 'age_26_30', '31-35' => 'age_31_35', '36-40' => 'age_36_40', '40-50' => 'age_40_50', '50+' => 'age_50_plus']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $labelKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button wire:click="toggleAgeGroup('<?php echo e($value); ?>')"
                        class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border-2 <?php echo e($ageGroup === $value ? 'border-primary text-gray-700 bg-white' : 'border-gray-100 text-gray-700 bg-white'); ?> hover:border-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        wire:target="toggleAgeGroup">
                        <span wire:target="toggleAgeGroup"><?php echo e(__('front.profiles.list.' . $labelKey)); ?></span>
                    </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>

                <!-- Feature Filters -->
                <div class="flex flex-wrap gap-3">
                    <!-- Recommendation Filter -->
                    <button wire:click="toggleRecommendation"
                        class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border-2 <?php echo e($sortRecommendation !== '' ? 'border-primary text-gray-700 bg-white' : 'border-gray-100 text-gray-700 bg-white'); ?> hover:border-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        wire:target="toggleRecommendation">
                        <!--[if BLOCK]><![endif]--><?php if($sortRecommendation === 'desc'): ?>
                            <svg class="w-4 h-4 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        <?php elseif($sortRecommendation === 'asc'): ?>
                            <svg class="w-4 h-4 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        <?php else: ?>
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                            </svg>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <?php echo e(__('front.profiles.list.recommendation')); ?>

                    </button>

                    <!-- Verified Photo Filter -->
                    <button wire:click="toggleVerifiedPhoto"
                        class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border-2 <?php echo e($hasVerifiedPhoto ? 'border-primary text-gray-700 bg-white' : 'border-gray-100 text-gray-700 bg-white'); ?> hover:border-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        wire:target="toggleVerifiedPhoto">
                        <svg wire:target="toggleVerifiedPhoto" class="w-4 h-4 mr-2 <?php echo e($hasVerifiedPhoto ? 'text-primary' : 'text-gray-500'); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <?php echo e(__('front.profiles.list.verified_photo')); ?>

                    </button>

                    <!-- Video Filter -->
                    <button wire:click="toggleVideo"
                        class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border-2 <?php echo e($hasVideo ? 'border-primary text-gray-700 bg-white' : 'border-gray-100 text-gray-700 bg-white'); ?> hover:border-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        wire:target="toggleVideo">
                        <svg wire:target="toggleVideo" class="w-4 h-4 mr-2 <?php echo e($hasVideo ? 'text-primary' : 'text-gray-500'); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <?php echo e(__('front.profiles.list.video')); ?>

                    </button>

                    <!-- Porn Actress Filter -->
                    <button wire:click="togglePornActress"
                        class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border-2 <?php echo e($isPornActress ? 'border-primary text-gray-700 bg-white' : 'border-gray-100 text-gray-700 bg-white'); ?> hover:border-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        wire:target="togglePornActress">
                        <svg wire:target="togglePornActress" class="w-4 h-4 mr-2 <?php echo e($isPornActress ? 'text-primary' : 'text-gray-500'); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                        <?php echo e(__('front.profiles.list.porn_actress')); ?>

                    </button>

                    <!-- New Filter -->
                    <button wire:click="toggleNew"
                        class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border-2 <?php echo e($sortNew !== '' ? 'border-primary text-gray-700 bg-white' : 'border-gray-100 text-gray-700 bg-white'); ?> hover:border-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        wire:target="toggleNew">
                        <!--[if BLOCK]><![endif]--><?php if($sortNew === 'desc'): ?>
                            <svg class="w-4 h-4 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        <?php elseif($sortNew === 'asc'): ?>
                            <svg class="w-4 h-4 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        <?php else: ?>
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <?php echo e(__('front.profiles.list.new')); ?>

                    </button>

                    <!-- Rating Filter -->
                    <button wire:click="toggleRating"
                        class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border-2 <?php echo e($hasRating ? 'border-primary text-gray-700 bg-white' : 'border-gray-100 text-gray-700 bg-white'); ?> hover:border-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        wire:target="toggleRating">
                        <svg wire:target="toggleRating" class="w-4 h-4 mr-2 <?php echo e($hasRating ? 'text-primary' : 'text-gray-500'); ?>" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                        </svg>
                        <?php echo e(__('front.profiles.list.rating')); ?>

                    </button>

                    <!-- Clear All Filters Button -->
                    <!--[if BLOCK]><![endif]--><?php if($this->activeFiltersCount() > 0): ?>
                    <button wire:click="resetFilters"
                        wire:loading.attr="disabled"
                        wire:target="resetFilters"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-full text-sm font-medium transition-all duration-200 border-2 border-red-200 text-red-600 bg-white hover:border-red-300 hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        title="<?php echo e(__('front.profiles.list.clear_all_filters')); ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
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

            <!-- Profiles Grid -->
            <!--[if BLOCK]><![endif]--><?php if($profiles && $profiles->count() > 0): ?>
                <div class="space-y-6 relative">
                    <!-- Loading Overlay -->
                    <div wire:loading wire:target="selectCountry,selectCity,toggleAgeGroup,toggleRecommendation,toggleVerifiedPhoto,toggleVideo,togglePornActress,toggleNew,toggleRating,resetFilters" 
                        class="absolute inset-0 bg-white/80 z-10 flex items-center justify-center">
                        <div class="flex flex-col items-center">
                            <svg class="animate-spin h-12 w-12 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="flex flex-col md:grid md:grid-cols-3 xl:grid-cols-4 gap-4">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $profiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $profile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 cursor-pointer group">
                            <!-- Profile Image -->
                            <div class="relative">
                                <!-- Verified Badge -->
                                <!--[if BLOCK]><![endif]--><?php if($profile->isVerified()): ?>
                                <div class="absolute top-3 left-3 flex flex-col items-start gap-1">
                                    <div class="bg-green-100 text-green-500 p-1 px-0.5 rounded-xl flex flex-wrap justify-center">
                                        <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'camera','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'camera','class' => 'w-5 h-5']); ?>
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
                                        <p class="text-xs font-bold w-full text-center">
                                            <?php echo e(__('front.profiles.list.verified')); ?>

                                        </p>
                                    </div>
                                </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                                <!-- Profile Photo -->
                                <div class="aspect-[4/5] bg-gradient-to-br from-primary-100 to-secondary-100 relative overflow-hidden">
                                    <!--[if BLOCK]><![endif]--><?php if($profile->getAllImages()->count() > 0): ?>
                                    <!--[if BLOCK]><![endif]--><?php if($profile->hasMultipleImages()): ?>
                                    <!-- Swiper for multiple images -->
                                    <div class="swiper profile-swiper-<?php echo e($profile->id); ?> w-full h-full">
                                        <div class="swiper-wrapper">
                                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $profile->getAllImages(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="swiper-slide">
                                                <img src="<?php echo e($image->getUrl()); ?>" alt="<?php echo e($profile->display_name); ?>"
                                                    class="w-full h-full object-cover">
                                            </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <!-- Pagination -->
                                        <div class="swiper-pagination swiper-pagination-<?php echo e($profile->id); ?>"></div>
                                    </div>
                                    <?php else: ?>
                                    <!-- Single image -->
                                    <img src="<?php echo e($profile->getFirstImageUrl()); ?>" alt="<?php echo e($profile->display_name); ?>"
                                        class="w-full h-full object-cover">
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <?php else: ?>
                                    <!-- No image placeholder -->
                                    <div class="flex items-center justify-center w-full h-full">
                                        <svg class="w-16 h-16 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>

                            <!-- Profile Info -->
                            <div class="p-4 space-y-3">
                                <!-- Name and VIP Badge -->
                                <div class="flex items-center justify-between py-3">
                                    <h4 class="text-gray-700 flex-grow-0 truncate max-w-[80%]"><?php echo e($profile->display_name); ?></h4>
                                    <!--[if BLOCK]><![endif]--><?php if($profile->isVip()): ?>
                                    <div class="bg-gold-500 text-white px-2 py-1 rounded-full text-xs font-bold flex items-center gap-1">
                                        <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'star','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'star','class' => 'w-3 h-3']); ?>
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
                                        VIP
                                    </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>

                                <!-- Details Button -->
                                <a href="<?php echo e(route('profiles.show', $profile)); ?>"
                                    class="w-full py-3 px-5 rounded-lg text-white font-semibold transition-colors duration-200 flex items-center justify-between"
                                    style="background:#5C2D62;">
                                    <span class="text-lg"><?php echo e(__('front.profiles.list.detail')); ?></span>
                                    <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'search','class' => 'w-5 h-5 text-white','strokeWidth' => '3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','class' => 'w-5 h-5 text-white','strokeWidth' => '3']); ?>
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
                                </a>

                                <!-- Rating/Evaluation -->
                                <div>
                                    <div class="flex bg-gray-200 rounded-lg" style="height:30px;">
                                        <div class="bg-gray-100 rounded-lg flex items-center justify-center" style="width:82px;height:100%;padding:0 12px;">
                                            <div class="text-sm font-medium text-gray-700 leading-none"><?php echo e(__('front.profiles.list.rating')); ?></div>
                                        </div>
                                        <div class="rounded-r-lg flex items-center justify-center gap-1 px-2" style="width:88px;height:100%;padding:0 10px;">
                                            <!--[if BLOCK]><![endif]--><?php if($profile->getTotalRatings() > 0): ?>
                                                <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                                <span class="text-sm font-bold text-gray-900"><?php echo e(number_format($profile->getAverageRating(), 1)); ?></span>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-500">—</span>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>

                                    <!-- Location -->
                                    <div class="flex py-2 justify-center items-center gap-x-2 text-sm text-primary-600">
                                        <!--[if BLOCK]><![endif]--><?php if($profile->city): ?>
                                        <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'location','class' => 'w-4 h-4 -translate-y-0.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'location','class' => 'w-4 h-4 -translate-y-0.5']); ?>
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
                                        <h5 class="py-1 text-center"><?php echo e($profile->city); ?></h5>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        <!--[if BLOCK]><![endif]--><?php if($profile->country): ?>
                                        <span class="text-gray-400">•</span>
                                        <span class="text-xs"><?php echo e(__('codes.' . strtolower($profile->country->country_code))); ?></span>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>

                                    <div class="flex justify-between gap-x-3">
                                        <div class="flex-1 bg-gray-100 rounded-lg p-3 text-center">
                                            <div class="text-xs ">168 cm</div>
                                        </div>
                                        <div class="flex-1 bg-gray-100 rounded-lg p-3 text-center">
                                            <div class="text-xs "><?php echo e($profile->age); ?> <?php echo e(__('front.profiles.list.years')); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <!-- Load More Button -->
                    <!--[if BLOCK]><![endif]--><?php if($profiles->hasMorePages()): ?>
                    <div class="text-center mt-8">
                        <button wire:click="loadMore"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed shadow hover:shadow-md px-8 py-3 text-base bg-secondary-600 text-white hover:bg-secondary-700 focus:ring-secondary-500">
                            <span wire:loading.remove wire:target="loadMore"><?php echo e(__('front.profiles.list.loadmore')); ?></span>
                            <span wire:loading wire:target="loadMore" class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <?php echo e(__('front.profiles.list.loadingmore')); ?>

                            </span>
                        </button>
                    </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <!-- Results Count -->
                    <div class="text-center text-sm text-gray-600 mt-4">
                        <span><?php echo e(__('front.profiles.list.showing')); ?> <?php echo e($profiles->count()); ?> <?php echo e(__('front.profiles.list.of')); ?> <?php echo e($profiles->total()); ?> <?php echo e(__('front.profiles.list.profiles')); ?></span>
                    </div>
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
        </div>
    </div>
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

    // For Livewire v3 - when content is updated via AJAX
    if (typeof Livewire !== 'undefined') {
        Livewire.hook('morph.updated', () => {
            setTimeout(initializeSwipers, 100);
        });

        // Also listen for specific Livewire events
        Livewire.on('profiles-updated', () => {
            setTimeout(initializeSwipers, 100);
        });
    }
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/livewire/country-profiles.blade.php ENDPATH**/ ?>