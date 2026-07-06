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

<div class="vip-lightbox fixed inset-0 z-[90] bg-[rgba(255,255,255,0.74)] backdrop-blur-[16px] hidden items-center justify-center p-[24px] max-[767px]:p-[12px]" id="vip-lightbox">
    <div class="relative w-full max-w-[1120px] flex flex-col items-center gap-[16px]">
        <div class="relative w-full flex items-center justify-center px-[75px]">
            <div class="swiper vip-lightbox-swiper w-full">
                <div class="swiper-wrapper">
                    
                </div>
            </div>
        </div>

        <button type="button" class="absolute -top-[8px] -right-[8px] z-[3] border-0 w-[45px] h-[45px] rounded-[8px] bg-primary-500 text-white shadow-[0_16px_28px_rgba(221,56,136,0.24)] cursor-pointer" id="vip-lightbox-close" aria-label="Close">X</button>
        <button type="button" class="absolute top-1/2 -translate-y-1/2 z-[3] border-0 w-[45px] h-[45px] rounded-[8px] bg-primary-500 text-white shadow-[0_16px_28px_rgba(221,56,136,0.24)] cursor-pointer left-[30px] max-[767px]:hidden" id="vip-lightbox-prev" aria-label="Previous">&#10094;</button>
        <button type="button" class="absolute top-1/2 -translate-y-1/2 z-[3] border-0 w-[45px] h-[45px] rounded-[8px] bg-primary-500 text-white shadow-[0_16px_28px_rgba(221,56,136,0.24)] cursor-pointer right-[30px] max-[767px]:hidden" id="vip-lightbox-next" aria-label="Next">&#10095;</button>

        <div class="flex gap-[8px] justify-center w-full">
            
        </div>
    </div>
</div>
<?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/components/profile-gallery/lightbox.blade.php ENDPATH**/ ?>