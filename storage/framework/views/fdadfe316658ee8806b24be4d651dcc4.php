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


<div class="hidden min-[840px]:grid grid-cols-[minmax(180px,0.88fr),minmax(280px,1.18fr),minmax(150px,0.78fr)] gap-[14px] items-stretch relative min-[1280px]:grid-cols-[337px,537px,337px]">
    
    <?php $__currentLoopData = [0, 1, 2]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <button type="button" 
                class="relative w-full h-full border-0 rounded-[26px] overflow-hidden p-0 cursor-pointer bg-[#eee7f0] lightbox-trigger transition-opacity duration-300" 
                data-index="<?php echo e($index); ?>">
            <img src="<?php echo e($gallerySlides[$index] ?? asset('images/models/vip'.($index+1).'.png')); ?>" 
                 alt="<?php echo e($profile->display_name); ?>" 
                 class="w-full h-full object-cover block transition-opacity duration-300">
        </button>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php if($gallerySlides->count() > 1): ?>
        <button type="button" class="absolute top-1/2 -translate-y-1/2 z-[5] w-[45px] h-[45px] border-0 rounded-[8px] bg-primary-500 text-white inline-flex items-center justify-center -left-[14px]" id="vip-gallery-desktop-prev" aria-label="Previous slide">
            <svg class="w-4 h-4 rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button type="button" class="absolute top-1/2 -translate-y-1/2 z-[5] w-[45px] h-[45px] border-0 rounded-[8px] bg-primary-500 text-white inline-flex items-center justify-center -right-[14px]" id="vip-gallery-desktop-next" aria-label="Next slide">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </button>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/components/profile-gallery/desktop.blade.php ENDPATH**/ ?>