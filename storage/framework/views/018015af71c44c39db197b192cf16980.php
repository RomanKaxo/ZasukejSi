

<?php $__env->startSection('title', __('front.countries.title')); ?>

<?php $__env->startSection('content'); ?>

<?php
    $heroTitle = __('front.countries.browse_by')
        . ' <span class="hero-main-highlight">'
        . __('front.countries.countries_text')
        . '</span><span class="hero-main-period">.</span>';
?>

<?php if (isset($component)) { $__componentOriginala038281ce129721dd88a49670137597b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala038281ce129721dd88a49670137597b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hero-section','data' => ['title' => $heroTitle,'subtitle' => __('front.countries.subtitle'),'showSearch' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hero-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($heroTitle),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('front.countries.subtitle')),'showSearch' => true]); ?>
    
    <div class="hero-desktop-badges absolute bottom-8 left-16 hidden md:flex gap-4" aria-hidden="true">
        <div class="search-badge">
            <span class="w-2.5 h-2.5 rounded-full bg-green-500 mr-1.5"></span>
            <span class="font-semibold text-xs text-gray-700">
                <?php echo e(number_format($girlsCount)); ?> <?php echo e(__('front.landing.girls_registered')); ?>

            </span>
        </div>
        <div class="search-badge">
            <span class="w-2.5 h-2.5 rounded-full bg-green-500 mr-1.5"></span>
            <span class="font-semibold text-xs text-gray-700">
                <?php echo e(number_format($gentsCount)); ?> <?php echo e(__('front.landing.gents_registered')); ?>

            </span>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala038281ce129721dd88a49670137597b)): ?>
<?php $attributes = $__attributesOriginala038281ce129721dd88a49670137597b; ?>
<?php unset($__attributesOriginala038281ce129721dd88a49670137597b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala038281ce129721dd88a49670137597b)): ?>
<?php $component = $__componentOriginala038281ce129721dd88a49670137597b; ?>
<?php unset($__componentOriginala038281ce129721dd88a49670137597b); ?>
<?php endif; ?>

<!-- Countries and Profiles Section -->
<div class="container mx-auto px-4 pt-20">
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('country-profiles', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-3013948266-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
</div>

<div class="-z-10 absolute top-[620px] left-0 right-0 -bottom-1 overflow-x-hidden">
    <div class="radial-blur"></div>
    <div class="radial-blur-secondary radial-blur-right"></div>
    <div class="radial-blur-secondary "></div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/countries/index.blade.php ENDPATH**/ ?>