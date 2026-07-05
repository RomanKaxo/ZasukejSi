
<?php
    $isEnglishAdvert = app()->getLocale() === 'en';

    $advertFeatures = [
        [
            'icon' => 'hand-heart',
            'iconClass' => 'w-8 h-8 lg:w-10 lg:h-10',
            'text' => __('front.landing.advert.features.hand_heart'),
        ],
        [
            'icon' => 'icecream',
            'iconClass' => 'w-8 h-8 lg:w-10 lg:h-10',
            'text' => __('front.landing.advert.features.icecream'),
        ],
    ];

    if (! $isEnglishAdvert) {
        $advertFeatures[] = [
            'icon' => 'laptop',
            'iconClass' => 'w-9 h-9 lg:w-10 lg:h-10',
            'text' => __('front.landing.advert.features.laptop'),
        ];
    }
?>

<section class="advert-hero relative overflow-visible rounded-3xl lg:overflow-hidden">
    <div class="lg:hidden">
        <div class="relative mx-auto aspect-[346/344] w-full max-w-[346px] overflow-hidden rounded-[28px]">
            <div class="absolute inset-0">
                <img
                    src="<?php echo e(asset('images/icons/mobile/mobile2.png')); ?>"
                    alt="Advert background"
                    class="h-full w-full object-cover"
                >
                <div class="absolute inset-0 bg-gradient-to-r from-white/96 via-white/82 to-white/8"></div>
            </div>

            <div class="relative z-10 flex h-full flex-col justify-start px-6 py-8">
                <div class="relative max-w-[210px]">
                    <svg width="42" height="28" viewBox="0 0 38 30" fill="none" xmlns="http://www.w3.org/2000/svg" class="mb-5 block text-[#DD3888]">
                        <path d="M35.2366 -3.67935e-05C36.7782 4.40453 37.549 8.77239 37.549 13.1036C37.549 18.0954 36.2276 22.0962 33.5848 25.106C30.9421 28.1892 27.0881 29.7308 22.0229 29.7308V22.7936C25.9136 22.7936 27.8589 20.2977 27.8589 15.3058V13.6541H20.8116V-3.67935e-05H35.2366ZM14.425 -3.67935e-05C15.8932 4.47794 16.6273 8.8458 16.6273 13.1036C16.6273 18.0954 15.3426 22.0962 12.7733 25.106C10.1305 28.1892 6.27652 29.7308 1.21127 29.7308V22.7936C5.02856 22.7936 6.93721 20.2977 6.93721 15.3058V13.6541H1.44755e-05V-3.67935e-05H14.425Z" fill="currentColor" />
                    </svg>
                    <h2 class="mb-4 text-[28px] font-bold leading-[1.1] text-[#5C2D62]">
                        <?php echo e(__('front.landing.advert.title')); ?>

                    </h2>
                    <p class="text-[20px] leading-[1.5] text-[#5C5C5C]">
                        <?php echo e(__('front.landing.advert.subtitle')); ?>

                    </p>
                </div>
            </div>
        </div>

        <!--[if BLOCK]><![endif]--><?php if(auth()->guard()->guest()): ?>
            <div class="mx-auto mt-4 w-full max-w-[346px]">
                <button @click="$dispatch('show-register-modal')" class="btn-primary inline-flex w-full items-center justify-center gap-3 !py-3.5 text-sm font-semibold">
                    <?php echo e(__('front.nav.register')); ?>

                    <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'heart','class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'heart','class' => 'h-5 w-5']); ?>
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
                </button>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <div class="mx-auto mt-5 grid w-full max-w-[346px] grid-cols-1 gap-4">
            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $advertFeatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div
                    class="<?php echo e($isEnglishAdvert ? 'flex min-h-[87px] items-center gap-5 rounded-[12px] px-6 py-5 backdrop-blur-[15px]' : 'flex items-center gap-5 rounded-[12px] bg-white px-6 py-5 shadow-[0_10px_24px_rgba(92,45,98,0.10)]'); ?>"
                    <?php if($isEnglishAdvert): ?> style="background: rgba(255, 255, 255, 0.40); box-shadow: 0 15px 15px rgba(220, 214, 221, 0.70);" <?php endif; ?>
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center text-primary">
                        <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => $feature['icon'],'strokeWidth' => 2.5,'preserveColors' => true,'class' => $feature['iconClass'] . ' text-[#DD3888]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($feature['icon']),'strokeWidth' => 2.5,'preserveColors' => true,'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($feature['iconClass'] . ' text-[#DD3888]')]); ?>
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
                    <p class="text-[13px] leading-[1.4] text-gray-700">
                        <?php echo e($feature['text']); ?>

                    </p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>

    <div class="hidden lg:block">
        <div class="absolute inset-0">
            <img 
                src="<?php echo e(asset('images/dvert.png')); ?>"
                alt="Advert background" 
                class="w-full h-full object-cover"
            >
        </div>

        <div class="relative z-10 px-8 py-12 md:px-14 md:py-16 lg:px-36 lg:py-40">
            <div class="max-w-2xl mb-8 md:mb-7 relative">
                <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'quote','preserveColors' => true,'class' => 'absolute -left-14 -top-6 w-10 h-10 text-[#DD3888]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'quote','preserveColors' => true,'class' => 'absolute -left-14 -top-6 w-10 h-10 text-[#DD3888]']); ?>
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
                <h2 class="advert-hero-title text-secondary mb-3">
                    <?php echo e(__('front.landing.advert.title')); ?>

                </h2>
                <p class="advert-hero-subtitle text-gray-600 text-base md:text-lg leading-relaxed max-w-lg">
                    <?php echo e(__('front.landing.advert.subtitle')); ?>

                </p>
            </div>

            <!--[if BLOCK]><![endif]--><?php if(auth()->guard()->guest()): ?>
            <div class="mb-10 md:mb-10">
                <button @click="$dispatch('show-register-modal')" class="btn-primary !py-3 inline-flex items-center gap-3 min-w-[304px]">
                    <?php echo e(__('front.nav.register')); ?>

                    <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'heart','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'heart','class' => 'w-5 h-5']); ?>
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
                </button>
            </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <div class="grid grid-cols-1 <?php echo e($isEnglishAdvert ? 'md:grid-cols-2 justify-items-center gap-4 md:gap-5' : 'md:grid-cols-3 gap-4 md:gap-6'); ?>">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $advertFeatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="feature-block <?php echo e($isEnglishAdvert ? 'w-full max-w-[363px] min-h-[87px] backdrop-blur-[15px] lg:w-[363px] lg:h-[87px] lg:px-6 lg:py-5' : ''); ?>" <?php if($isEnglishAdvert): ?> style="background: rgba(255, 255, 255, 0.40); box-shadow: 0 15px 15px rgba(220, 214, 221, 0.70);" <?php endif; ?>>
                        <div class="feature-block-icon">
                            <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => $feature['icon'],'strokeWidth' => 2.5,'preserveColors' => true,'class' => $feature['iconClass'] . ' text-[#DD3888]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($feature['icon']),'strokeWidth' => 2.5,'preserveColors' => true,'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($feature['iconClass'] . ' text-[#DD3888]')]); ?>
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
                        <p class="feature-block-text <?php echo e($isEnglishAdvert ? 'text-[14px] font-normal leading-[1.35] text-[#5C5C5C]' : ''); ?>">
                            <?php echo e($feature['text']); ?>

                        </p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    </div>
</section><?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/components/advert-hero.blade.php ENDPATH**/ ?>