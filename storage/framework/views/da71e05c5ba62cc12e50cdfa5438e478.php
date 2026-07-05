<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['name', 'class' => '', 'strokeWidth' => 1.5, 'fill' => false, 'block' => true, 'preserveColors' => false]));

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

foreach (array_filter((['name', 'class' => '', 'strokeWidth' => 1.5, 'fill' => false, 'block' => true, 'preserveColors' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $iconPath = public_path("images/icons/{$name}.svg");
?>

<!--[if BLOCK]><![endif]--><?php if(file_exists($iconPath)): ?>
    <span <?php echo e($attributes->merge(['class' => trim(($block == "false" ? 'inline' : 'inline-block') . ' ' . $class)])); ?>>
        <?php
            $svgContent = file_get_contents($iconPath);

            if ($preserveColors) {
                $svgContent = preg_replace([
                    '/\bwidth="[^"]*"/',
                    '/\bheight="[^"]*"/',
                    '/<svg/'
                ], [
                    '',
                    '',
                    '<svg class="h-full w-full" '
                ], $svgContent);
            } else {
                $svgContent = preg_replace([
                    '/\bstroke="[^"]*"/',
                    '/\bwidth="[^"]*"/',
                    '/\bheight="[^"]*"/',
                    '/\bfill="[^"]*"/',
                    '/<svg/'
                ], [
                    'stroke="currentColor"',
                    '',
                    '',
                    $fill ? 'fill="currentColor"' : 'fill="none"',
                    '<svg stroke-width="' . ($strokeWidth) . '" '
                ], $svgContent);
            }
        ?>
        <?php echo $svgContent; ?>

    </span>
<?php else: ?>
    <!-- Icon not found: <?php echo e($name); ?> -->
<?php endif; ?><!--[if ENDBLOCK]><![endif]--><?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/components/icons.blade.php ENDPATH**/ ?>