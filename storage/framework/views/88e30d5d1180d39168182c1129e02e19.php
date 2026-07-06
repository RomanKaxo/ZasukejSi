<<?php echo e($level); ?> class="<?php echo \Illuminate\Support\Arr::toCssClasses([
    'font-bold leading-tight',
    'font-3xl' => $level == 'h1',
    'font-2xl' => $level == 'h2',
    'font-xl' => $level == 'h3',
    'font-lg' => $level == 'h4',
    'font-base' => $level == 'h5',
    'font-sm' => $level == 'h6',
]); ?>"><?php echo e($content); ?></<?php echo e($level); ?>><?php /**PATH C:\Users\roman\Desktop\ZasukejSi\vendor\skyraptor\filament-blocks-builder\src/../resources/views/typography/heading.blade.php ENDPATH**/ ?>