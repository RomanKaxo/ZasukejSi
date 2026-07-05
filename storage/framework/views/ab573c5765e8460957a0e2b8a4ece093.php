

<?php $__env->startSection('title', __('front.title')); ?>

<?php $__env->startSection('content'); ?>
<?php
    $isEnglishHomepage = app()->getLocale() === 'en';
?>
<style>
    .search-badge {
        width: 167px;
        height: 26px;
        border-radius: 999px;
        background: <?php echo e($isEnglishHomepage ? '#FFFFFF' : '#F2F2F2'); ?> !important;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        box-sizing: border-box;
        padding: 0 10px;
        transition: transform 220ms ease, box-shadow 220ms ease, background-color 220ms ease;
    }

    @media (max-width: 425px) {
        .search-badge {
            width: 310px !important;
            height: 35px !important;
            background: <?php echo e($isEnglishHomepage ? '#FFFFFF' : '#F2F2F2'); ?> !important;
            backdrop-filter: blur(4px);
        }
    }

    .homepage-mobile-blur {
        display: none;
    }
    
    .homepage-profiles-surface {
        opacity: 0;
        animation: sectionReveal 880ms cubic-bezier(.2,.9,.3,1) forwards;
        animation-delay: 320ms;
    }

    @keyframes sectionReveal {
        from { opacity: 0; transform: translateY(26px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 640px) {
        <?php if(app()->getLocale() === 'en'): ?>
            body { background: #FFFFFF; }
            .profiles-section-wrap, .homepage-profiles-surface { background: #FFFFFF; }
            .homepage-profiles-surface { margin-top: 40px; }
        <?php endif; ?>
    }
</style>

<?php
    $heroTitle = __('front.landing.wearecommunity') . '<br>' . __('front.landing.fucking_prefix') . ' <span class="hero-main-highlight">' . __('front.landing.fucking_keyword') . '</span><span class="hero-main-period">.</span>';
?>

<?php if (isset($component)) { $__componentOriginala038281ce129721dd88a49670137597b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala038281ce129721dd88a49670137597b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hero-section','data' => ['title' => $heroTitle,'subtitle' => __('front.landing.girlsregisternow'),'showSearch' => !$isEnglishHomepage]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hero-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($heroTitle),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('front.landing.girlsregisternow')),'showSearch' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(!$isEnglishHomepage)]); ?>
    <?php if($isEnglishHomepage): ?>
        <div class="hero-desktop-badges absolute bottom-8 left-16 hidden md:flex gap-4" aria-hidden="true">
            <div class="search-badge">
                <span class="w-2.5 h-2.5 rounded-full bg-green-500 mr-1.5"></span>
                <span class="font-semibold text-xs text-gray-700">1 420 <?php echo e(__('front.profiles.search.girls')); ?></span>
            </div>
            <div class="search-badge">
                <span class="w-2.5 h-2.5 rounded-full bg-green-500 mr-1.5"></span>
                <span class="font-semibold text-xs text-gray-700">382 <?php echo e(__('front.profiles.search.men')); ?></span>
            </div>
        </div>
    <?php endif; ?>
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

<!-- Profiles Section -->
<div class="container mx-auto px-4 <?php echo e($isEnglishHomepage ? 'pt-8 md:pt-10' : 'pt-10 md:pt-20'); ?> profiles-section-wrap homepage-profiles-surface">
    <?php if($isEnglishHomepage): ?>
        <div class="lg:grid lg:grid-cols-[208px_minmax(0,1fr)] lg:gap-8 lg:items-start">
            <?php if (isset($component)) { $__componentOriginald2299218ccb8d51de0f7b4e9e02de16d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald2299218ccb8d51de0f7b4e9e02de16d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.english-country-sidebar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('english-country-sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald2299218ccb8d51de0f7b4e9e02de16d)): ?>
<?php $attributes = $__attributesOriginald2299218ccb8d51de0f7b4e9e02de16d; ?>
<?php unset($__attributesOriginald2299218ccb8d51de0f7b4e9e02de16d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald2299218ccb8d51de0f7b4e9e02de16d)): ?>
<?php $component = $__componentOriginald2299218ccb8d51de0f7b4e9e02de16d; ?>
<?php unset($__componentOriginald2299218ccb8d51de0f7b4e9e02de16d); ?>
<?php endif; ?>
            <div class="min-w-0">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('profile-list', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-530824008-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            </div>
        </div>
    <?php else: ?>
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('profile-list', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-530824008-1', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/profiles/index.blade.php ENDPATH**/ ?>