<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['profile', 'imageOverride' => null, 'imagesOverride' => null, 'variant' => null, 'showRemoveButton' => false, 'cardHeight' => '510px', 'imageHeight' => '265px']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['profile', 'imageOverride' => null, 'imagesOverride' => null, 'variant' => null, 'showRemoveButton' => false, 'cardHeight' => '510px', 'imageHeight' => '265px']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $shouldBlur = $variant === 'vip-detail';
    $cardContent = (isset($profile->content) && is_array($profile->content)) ? $profile->content : [];
    $cardLocation = $cardContent['card_location'] ?? ($profile->city ?? null);
    $cardHeightCm = $cardContent['card_height_cm'] ?? 168;
    
    // Check if profile is a model instance (has methods) or a plain object
    $isModel = method_exists($profile, 'isVerified');
    
    // Compute profile URL - handle both model and plain object cases
    $profileUrl = $shouldBlur ? '#' : ($isModel ? route('profiles.show', $profile) : route('profiles.show', $profile->id ?? 0));
    
    // Safe property accessors for plain objects
    $profileName = $profile->display_name ?? 'Profile';
    $profileAge = $profile->age ?? null;
    
    // Check if profile is verified or VIP (works for both models and plain objects)
    $isVerified = $isModel ? $profile->isVerified() : ($profile->is_verified ?? false);
    $isVip = $isModel ? $profile->isVip() : ($profile->is_vip ?? false);
?>

<?php
    // Prepare image URLs for simple Alpine-based slideshow
    $imageUrls = [];
    if($imagesOverride && is_array($imagesOverride)) {
        $imageUrls = $imagesOverride;
    } elseif($imageOverride) {
        $imageUrls = [$imageOverride];
    } elseif($isModel && $profile->getAllImages()->count() > 0) {
        $imageUrls = $profile->getAllImages()->map(function($i){ return $i->getUrl('thumb'); })->all();
    } elseif($isModel && method_exists($profile, 'getFirstImageThumbUrl') && $profile->getFirstImageThumbUrl()) {
        $imageUrls = [$profile->getFirstImageThumbUrl()];
    } elseif(isset($profile->image_url)) {
        $imageUrls = [$profile->image_url];
    }
?>

<div class="bg-white rounded-lg overflow-hidden transition-all duration-300 cursor-pointer group relative z-10 home-profile-card" style="width: 210px; height: <?php echo e($cardHeight); ?>; border-radius: 15px; box-shadow: 0 15px 15px 0 rgba(92, 45, 98, 0.1);" x-cloak x-data="{ removed: false, showBtn: false, currentIndex: 0, imageUrls: [] }" data-image-urls='<?php echo json_encode($imageUrls, 15, 512) ?>' x-init="imageUrls = JSON.parse($el.getAttribute('data-image-urls') || '[]')" x-show="!removed" @mouseenter="showBtn = true" @mouseleave="showBtn = false">
    <!--[if BLOCK]><![endif]--><?php if($showRemoveButton): ?>
    <!-- Remove Button - Hidden by default, shown on hover -->
    <button @click.stop="removed = true" x-show="showBtn" class="absolute top-2 right-2 z-30 w-7 h-7 flex items-center justify-center rounded-full transition-opacity duration-200" style="background-color: #DD3888;">
        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1 1L9 9M9 1L1 9" stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
    </button>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    
    <!-- Profile Image -->
    <div class="relative overflow-hidden home-profile-card-media" style="width: 210px; height: <?php echo e($imageHeight); ?>; border-radius: 15px;">

        <!--[if BLOCK]><![endif]--><?php if((!$shouldBlur) && ($isVerified || $isVip)): ?>
        <div class="absolute top-3 left-3 z-20 home-profile-card-badge-stack">
            <!-- Verified Badge -->
            <!--[if BLOCK]><![endif]--><?php if($isVerified): ?>
            <div class="home-profile-card-badge">
                <div class="bg-green-100 text-green-500 p-1 px-0.5 rounded-xl flex flex-wrap justify-center home-profile-card-verified-badge">
                    <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'camera','class' => 'w-5 h-5 home-profile-card-verified-camera']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'camera','class' => 'w-5 h-5 home-profile-card-verified-camera']); ?>
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
                    <p class="text-xs font-bold w-full text-center home-profile-card-verified-copy">
                        <?php echo e(__('front.profiles.list.verified')); ?>

                    </p>
                    <span class="home-profile-card-verified-check" aria-hidden="true">
                        <img src="<?php echo e(asset('images/icons/check.svg')); ?>" alt="" />
                    </span>
                </div>
            </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <!--[if BLOCK]><![endif]--><?php if($isVip): ?>
            <div class="home-profile-card-vip home-profile-card-vip-mobile" style="width:50px;height:26px;border-radius:999px;background:#FFB700;">
                <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'star','class' => 'inline-block','style' => 'width:14px;height:14px;color:#FFFFFF;']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'star','class' => 'inline-block','style' => 'width:14px;height:14px;color:#FFFFFF;']); ?>
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
                <span style="font-family:'Poppins', sans-serif; font-weight:900; font-size:10px; color:#FFFFFF; line-height:1;">VIP</span>
            </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php if($shouldBlur): ?>
        <div class="absolute inset-0 z-30 flex items-center justify-center pointer-events-none">
            <span class="inline-flex items-center justify-center bg-white rounded-full p-5 shadow-lg">
                <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'lock','strokeWidth' => '1','class' => 'w-8 h-8 -translate-y-0.5','style' => 'color: #DD3888;']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'lock','strokeWidth' => '1','class' => 'w-8 h-8 -translate-y-0.5','style' => 'color: #DD3888;']); ?>
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
        </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!-- Profile Photo (Alpine-driven simple slideshow) -->
        <div class="w-full h-full bg-gradient-to-br from-primary-100 to-secondary-100 relative overflow-hidden <?php echo e($shouldBlur ? 'blur-md' : ''); ?>">
            <?php $firstImageUrl = $imageUrls[0] ?? null; ?>
            <!--[if BLOCK]><![endif]--><?php if($firstImageUrl): ?>
                <img src="<?php echo e($firstImageUrl); ?>" x-bind:src="imageUrls[currentIndex]" alt="<?php echo e($profileName); ?>" class="w-full h-full object-cover home-profile-card-image absolute inset-0" />
            <?php else: ?>
                <div class="flex items-center justify-center w-full h-full">
                    <svg class="w-16 h-16 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        </div>
        
        <?php
            $imagesCount = null;
            if($imagesOverride && is_array($imagesOverride)) {
                $imagesCount = count($imagesOverride);
            } elseif($isModel) {
                $imagesCount = $profile->getAllImages()->count();
            } else {
                $imagesCount = $profile->images_count ?? 1;
            }
            $visibleDots = min(5, max(1, (int) $imagesCount));
        ?>

        <!-- Photo count dots (5 total) -->
        <div class="absolute left-0 right-0 bottom-3 flex justify-center z-30" style="gap:3px;">
            <!--[if BLOCK]><![endif]--><?php for($i = 0; $i < 5; $i++): ?>
                <button type="button" @click.prevent="currentIndex = <?php echo e(min($i, $visibleDots - 1)); ?>" class="w-2.5 h-2.5 rounded-full bg-white flex items-center justify-center" style="box-shadow: 0 0 0 1px rgba(0,0,0,0.04);">
                    <span class="w-1.5 h-1.5 rounded-full" :class="{ 'bg-transparent': currentIndex !== <?php echo e($i); ?>, 'bg-[#DD3888]': currentIndex === <?php echo e($i); ?> }" style="display:block;"></span>
                </button>
            <?php endfor; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>

    <!-- Profile Info -->
    <div class="p-4 space-y-3 home-profile-card-content">
        <!-- Name and VIP Badge -->
        <div class="flex items-center justify-between py-1 home-profile-card-header">
            <h4 class="text-gray-700 flex-grow-0 truncate max-w-[80%] home-profile-card-name <?php echo e($shouldBlur ? 'blur-md' : ''); ?>" style="font-family: 'Poppins', sans-serif; font-weight:700; font-size:18px; color:#333;">
                <?php echo e($profileName); ?>

            </h4>
            <!--[if BLOCK]><![endif]--><?php if($isVip): ?>
            <div class="home-profile-card-vip home-profile-card-vip-desktop" style="width:50px;height:26px;border-radius:999px;background:#FFB700;display:flex;align-items:center;justify-content:center;gap:6px;">
                <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'star','class' => 'inline-block','style' => 'width:14px;height:14px;color:#FFFFFF;']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'star','class' => 'inline-block','style' => 'width:14px;height:14px;color:#FFFFFF;']); ?>
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
                <span style="font-family:'Poppins', sans-serif; font-weight:900; font-size:10px; color:#FFFFFF; line-height:1;">VIP</span>
            </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <!-- Details Button -->
            <a href="<?php echo e($profileUrl); ?>"
            class="flex items-center justify-between home-profile-card-cta"
            style="width:170px;height:45px;border-radius:8px;background:#5C2D62;color:#FFFFFF; display:inline-flex; align-items:center; justify-content:space-between; padding:0 16px; font-family:'Poppins', sans-serif; font-weight:600; font-size:16px; text-decoration:none; <?php echo e($shouldBlur ? 'pointer-events:none;' : ''); ?>">
            <span><?php echo e(__('front.profiles.list.detail')); ?></span>
            <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'search','class' => 'inline-block','style' => 'width:24px;height:24px;color:#FFFFFF;']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','class' => 'inline-block','style' => 'width:24px;height:24px;color:#FFFFFF;']); ?>
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

            <!-- Age and Height Stats -->
        <div class="home-profile-card-rating-wrap">
            <div class="flex justify-between gap-x-3 home-profile-card-stats">
                <div class="home-profile-card-stat" style="width:82px;height:30px;border-radius:8px;background:#F2F2F2;display:flex;align-items:center;justify-content:center;">
                    <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:600;font-size:11px;color:#505050;"><?php echo e($cardHeightCm); ?> cm</div>
                </div>
                <div class="home-profile-card-stat" style="width:82px;height:30px;border-radius:8px;background:#F2F2F2;display:flex;align-items:center;justify-content:center;">
                    <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:600;font-size:11px;color:#505050;"><?php echo e($profileAge); ?> <?php echo e(__('front.profiles.list.years')); ?></div>
                </div>
            </div>

            <!-- Location -->
            <div class="flex py-2 justify-center items-center gap-x-2 home-profile-card-location">
                <!--[if BLOCK]><![endif]--><?php if($cardLocation): ?>
                    <img src="<?php echo e(asset('images/icons/location.svg')); ?>" alt="" aria-hidden="true" class="inline-block" style="width:20px;height:20px;" />
                    <h5 style="margin:0;font-family:'Plus Jakarta Sans', sans-serif;font-weight:600;font-size:11px;color:#505050;"><?php echo e($cardLocation); ?></h5>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            <!-- Rating Badge -->
            <div class="home-profile-card-rating-badge" style="display:flex;align-items:center;justify-content:center;gap:8px;height:40px;border-radius:8px;background:#E6FEE8;padding:0 12px;">
                <div style="font-family:'Plus Jakarta Sans', sans-serif;font-weight:600;font-size:11px;color:#505050;line-height:1;">Hodnocení:</div>
                <div style="font-family:'Poppins', sans-serif;font-weight:600;font-size:14px;color:#5C2D62;line-height:1;"><?php echo e($isModel && $profile->getTotalRatings() > 0 ? number_format($profile->getAverageRating(), 1) : (4.5 + ((isset($profile->id) ? $profile->id : 0) % 5) * 0.1)); ?>/5</div>
                <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'HeartFilled','class' => 'inline-block flex-shrink-0','style' => 'width:20px;height:20px;','preserveColors' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'HeartFilled','class' => 'inline-block flex-shrink-0','style' => 'width:20px;height:20px;','preserveColors' => 'true']); ?>
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
            </div>

        </div>
    </div>
</div>
<?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/components/profile-card.blade.php ENDPATH**/ ?>