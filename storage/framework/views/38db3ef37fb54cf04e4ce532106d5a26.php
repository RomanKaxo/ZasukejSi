<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['noDesktopMargin' => false]));

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

foreach (array_filter((['noDesktopMargin' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'w-[843px] max-[426px]:w-[313px] h-[1px] bg-[#E6E6E6] max-[426px]:mt-6 md:mt-2 max-[426px]:mb-6' . ($noDesktopMargin ? '' : ' md:mt-12')])); ?>></div><?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/components/dashboard/section-divider.blade.php ENDPATH**/ ?>