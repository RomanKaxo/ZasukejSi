<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['profile', 'gallerySlides']));

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

foreach (array_filter((['profile', 'gallerySlides']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="block min-[840px]:hidden">
    <?php if($gallerySlides->count() > 1): ?>
        <button type="button" class="absolute top-1/2 -translate-y-1/2 z-[3] w-[34px] h-[34px] border-0 rounded-[10px] bg-primary-500 text-white inline-flex items-center justify-center shadow-[0_14px_26px_rgba(221,56,136,0.25)] -left-[15px]" aria-label="Previous image">&#10094;</button>
        <button type="button" class="absolute top-1/2 -translate-y-1/2 z-[3] w-[34px] h-[34px] border-0 rounded-[10px] bg-primary-500 text-white inline-flex items-center justify-center shadow-[0_14px_26px_rgba(221,56,136,0.25)] -right-[15px]" aria-label="Next image">&#10095;</button>

        <div class="swiper vip-profile-gallery-swiper overflow-visible">
            <div class="swiper-wrapper">
                <?php $__currentLoopData = $gallerySlides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $imageUrl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="swiper-slide opacity-70 scale-94 transition-[transform,opacity] duration-200">
                        <button type="button" class="relative w-full h-[410px] border-0 rounded-[26px] overflow-hidden p-0 bg-[#eee7f0] cursor-pointer block lightbox-trigger" data-index="<?php echo e($index); ?>">
                            <img src="<?php echo e($imageUrl); ?>" alt="<?php echo e($profile->display_name); ?>" class="w-full h-full object-cover transition-[transform,opacity] duration-200">
                        </button>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php elseif($gallerySlides->count() === 1): ?>
        <button type="button" class="relative w-full h-[410px] border-0 rounded-[26px] overflow-hidden p-0 bg-[#eee7f0] cursor-pointer block lightbox-trigger" data-index="0">
            <img src="<?php echo e($gallerySlides->first()); ?>" alt="<?php echo e($profile->display_name); ?>" class="w-full h-full object-cover">
        </button>
    <?php else: ?>
        <div class="relative w-full h-[410px] border-0 rounded-[26px] overflow-hidden p-0 bg-[#eee7f0] cursor-pointer block flex items-center justify-center">
            <svg class="h-20 w-20 text-[#d6c7dc]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/components/profile-gallery/mobile.blade.php ENDPATH**/ ?>