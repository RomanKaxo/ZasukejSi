<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name')); ?> - <?php echo $__env->yieldContent('title', __('front.title')); ?></title>

    <!-- Scripts -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>


    <style>
        @media (max-width: 767px) {
            html,
            body,
            #app,
            main {
                background-color: #FFFFFF !important;
            }
        }
    </style>
</head>
<body class="font-poppins antialiased text-text-default relative bg-white md:bg-transparent">
    <div id="app" class="overflow-x-hidden bg-white md:bg-transparent">
        <!-- Navigation -->
        <?php if (isset($component)) { $__componentOriginala591787d01fe92c5706972626cdf7231 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala591787d01fe92c5706972626cdf7231 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.navbar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('navbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala591787d01fe92c5706972626cdf7231)): ?>
<?php $attributes = $__attributesOriginala591787d01fe92c5706972626cdf7231; ?>
<?php unset($__attributesOriginala591787d01fe92c5706972626cdf7231); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala591787d01fe92c5706972626cdf7231)): ?>
<?php $component = $__componentOriginala591787d01fe92c5706972626cdf7231; ?>
<?php unset($__componentOriginala591787d01fe92c5706972626cdf7231); ?>
<?php endif; ?> 

        <!-- Main Content -->
        <main class="flex-1 bg-white md:bg-transparent">
            <?php echo $__env->yieldContent('content'); ?>
        </main>

        <!-- Footer -->
        <?php if (isset($component)) { $__componentOriginal8a8716efb3c62a45938aca52e78e0322 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a8716efb3c62a45938aca52e78e0322 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.footer','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a8716efb3c62a45938aca52e78e0322)): ?>
<?php $attributes = $__attributesOriginal8a8716efb3c62a45938aca52e78e0322; ?>
<?php unset($__attributesOriginal8a8716efb3c62a45938aca52e78e0322); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a8716efb3c62a45938aca52e78e0322)): ?>
<?php $component = $__componentOriginal8a8716efb3c62a45938aca52e78e0322; ?>
<?php unset($__componentOriginal8a8716efb3c62a45938aca52e78e0322); ?>
<?php endif; ?>

        <!-- Auth Modals -->
        <?php if(auth()->guard()->guest()): ?>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('login-modal', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-1113753095-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('register-modal', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-1113753095-1', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('reset-modal', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-1113753095-2', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        <?php endif; ?>
    </div>

    <!-- Additional Scripts -->
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>


    <?php echo $__env->yieldPushContent('scripts'); ?>

    <!-- Auth Modal Scripts -->
    <?php if(auth()->guard()->guest()): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle escape key to close modals
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    Livewire.dispatch('hide-login-modal');
                    Livewire.dispatch('hide-register-modal');
                    Livewire.dispatch('hide-reset-modal');
                }
            });
        });
    </script>
    <?php endif; ?>
    <script>
        // Small visual debug badge to show Livewire client presence
        (function(){
            try {
                var d = document.createElement('div');
                d.id = 'lw-debug-badge';
                d.style.position = 'fixed';
                d.style.right = '12px';
                d.style.bottom = '12px';
                d.style.zIndex = 99999;
                d.style.background = 'rgba(255,255,255,0.95)';
                d.style.border = '1px solid #eee';
                d.style.padding = '6px 10px';
                d.style.fontSize = '12px';
                d.style.color = '#222';
                d.style.borderRadius = '6px';
                d.style.boxShadow = '0 6px 18px rgba(0,0,0,0.06)';
                d.innerText = 'Livewire: checking...';
                document.body.appendChild(d);

                function update() {
                    var present = typeof Livewire !== 'undefined' ? 'loaded' : 'missing';
                    d.innerText = 'Livewire: ' + present;
                }

                // Update on load and when Livewire fires its load event
                update();
                document.addEventListener('livewire:load', update);
            } catch (err) {}
        })();
    </script>
    <script>
        // Global fallback for opening the English fullscreen country picker on mobile
        (function () {
            function openPicker() {
                var picker = document.querySelector('.search-country-mobile-picker');
                if (!picker) return;
                picker.style.display = 'block';
                document.documentElement.style.overflow = 'hidden';
                document.body.style.overflow = 'hidden';
            }

            function closePicker() {
                var picker = document.querySelector('.search-country-mobile-picker');
                if (!picker) return;
                picker.style.display = 'none';
                document.documentElement.style.overflow = '';
                document.body.style.overflow = '';
            }

            // Use capture phase to intercept clicks before Alpine/Livewire handlers
            document.addEventListener('click', function (e) {
                try {
                    if (window.innerWidth > 767) return;
                    if (e.target.closest && e.target.closest('#country-select')) {
                        e.preventDefault();
                        e.stopPropagation();
                        openPicker();
                        return;
                    }

                    if (e.target.closest && e.target.closest('.search-country-mobile-picker__close')) {
                        e.preventDefault();
                        e.stopPropagation();
                        closePicker();
                        return;
                    }

                    var picker = document.querySelector('.search-country-mobile-picker');
                    if (picker && getComputedStyle(picker).display !== 'none') {
                        if (!e.target.closest('.search-country-mobile-picker__inner') && !e.target.closest('#country-select')) {
                            closePicker();
                        }
                    }
                } catch (err) {
                    // ignore
                }
            }, true);

            // Also listen for pointerdown (mobile taps) at capture phase to catch touch events earlier
            document.addEventListener('pointerdown', function (e) {
                try {
                    if (window.innerWidth > 767) return;
                    if (e.target.closest && e.target.closest('#country-select')) {
                        e.preventDefault();
                        e.stopPropagation();
                        var picker = document.querySelector('.search-country-mobile-picker');
                        if (picker) {
                            picker.style.display = 'block';
                            document.documentElement.style.overflow = 'hidden';
                            document.body.style.overflow = 'hidden';
                        }
                        return;
                    }
                } catch (err) {}
            }, true);
        })();
    </script>
</body>
</html>
<?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/layouts/app.blade.php ENDPATH**/ ?>