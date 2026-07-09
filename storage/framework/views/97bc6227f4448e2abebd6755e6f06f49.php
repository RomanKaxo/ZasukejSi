<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - Nahlášené dívky</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-white" style="overflow-y: auto; height: auto;">

<div id="app" style="min-height: 100%; overflow-y: auto;">
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

    <main>
        <div class="container mx-auto pt-32 max-[426px]:pt-12 px-4 max-[426px]:px-0">
            <!-- Outer layout: sidebar + content -->
            <div class="flex justify-end mb-8 gap-x-12 w-[1134px] mx-auto max-[426px]:w-[310px] max-[426px]:justify-center">

                <!-- Member Sidebar -->
                <div class="max-[426px]:hidden mt-[150px]">
                    <div class="flex flex-col w-[211px] gap-[10px]">
                        <div class="flex flex-col w-[211px] gap-[10px]">
                            <!-- Hodnocení dívek -->
                            <a href="<?php echo e(route('preview.ratings')); ?>"
                               class="w-[210px] h-[50px] rounded-[8px] border border-[#E6E6E6] text-[#505050] flex items-center px-4 gap-3 font-medium text-[14px]"
                               style="font-family: 'Poppins', sans-serif;">
                                <img src="<?php echo e(asset('images/icons/star.svg')); ?>" class="w-[20px] h-[20px]" alt="Star"
                                     style="filter: invert(36%) sepia(87%) saturate(2222%) hue-rotate(309deg) brightness(90%) contrast(92%);">
                                Hodnocení dívek
                            </a>
                            <!-- Moje Favoritky -->
                            <a href="<?php echo e(route('preview.favorites')); ?>"
                               class="w-[210px] h-[50px] rounded-[8px] border border-[#E6E6E6] text-[#505050] flex items-center px-4 gap-3 font-medium text-[14px]"
                               style="font-family: 'Poppins', sans-serif;">
                                <img src="<?php echo e(asset('images/icons/heart.svg')); ?>" class="w-[20px] h-[20px]" alt="Heart"
                                     style="filter: invert(36%) sepia(87%) saturate(2222%) hue-rotate(309deg) brightness(90%) contrast(92%);">
                                Moje Favoritky
                            </a>
                            <!-- Dívky měsíc -->
                            <a href="#"
                               class="w-[210px] h-[50px] rounded-[8px] border border-[#E6E6E6] text-[#505050] flex items-center px-4 gap-3 font-medium text-[14px]"
                               style="font-family: 'Poppins', sans-serif;">
                                <img src="<?php echo e(asset('images/icons/calendar.svg')); ?>" class="w-[20px] h-[20px]" alt="Calendar"
                                     style="filter: invert(36%) sepia(87%) saturate(2222%) hue-rotate(309deg) brightness(90%) contrast(92%);">
                                Dívky měsíc
                            </a>
                            <!-- Archiv dívek -->
                            <a href="#"
                               class="w-[210px] h-[50px] rounded-[8px] border border-[#E6E6E6] text-[#505050] flex items-center px-4 gap-3 font-medium text-[14px]"
                               style="font-family: 'Poppins', sans-serif;">
                                <img src="<?php echo e(asset('images/icons/Save.svg')); ?>" class="w-[20px] h-[20px]" alt="Archive"
                                     style="filter: invert(36%) sepia(87%) saturate(2222%) hue-rotate(309deg) brightness(90%) contrast(92%);">
                                Archiv dívek
                            </a>
                            <!-- Nahlášené dívky – ACTIVE -->
                            <a href="<?php echo e(route('preview.reported')); ?>"
                               class="w-[210px] h-[50px] rounded-[8px] bg-[#DD3888] text-white flex items-center px-4 gap-3 font-medium text-[14px]"
                               style="font-family: 'Poppins', sans-serif;">
                                <img src="<?php echo e(asset('images/icons/OctagonAlert.svg')); ?>" class="w-[20px] h-[20px]" alt="Report"
                                     style="filter: brightness(0) invert(1);">
                                Nahlášené dívky
                            </a>
                            <!-- Základní nastavení -->
                            <a href="<?php echo e(route('preview.dashboard')); ?>"
                               class="w-[210px] h-[50px] rounded-[8px] border border-[#E6E6E6] text-[#505050] flex items-center px-4 gap-3 font-medium text-[14px]"
                               style="font-family: 'Poppins', sans-serif;">
                                <img src="<?php echo e(asset('images/icons/User.svg')); ?>" class="w-[20px] h-[20px]" alt="Settings"
                                     style="filter: invert(36%) sepia(87%) saturate(2222%) hue-rotate(309deg) brightness(90%) contrast(92%);">
                                Základní nastavení
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Main Content (843px) -->
                <div class="w-[843px] max-[426px]:w-[310px]">

                    <!-- Status Bar -->
                    <div x-data="{ show: true }" x-show="show"
                         class="w-[843px] max-[426px]:w-[309px] h-[50px] max-[426px]:h-[91px] rounded-[8px] flex items-center max-[426px]:items-center justify-center max-[426px]:justify-between px-4 max-[426px]:px-3 gap-3 relative mb-6 max-[426px]:mb-4 max-[426px]:mt-20"
                         style="background-color: #E6FEE8;">
                        <!-- Desktop layout -->
                        <div class="flex items-center gap-2 max-[426px]:hidden">
                            <img src="<?php echo e(asset('images/icons/diamond.svg')); ?>" class="w-[20px] h-[20px] flex-shrink-0" alt="Diamond">
                            <p class="text-[13px] text-[#505050] font-medium leading-tight"
                               style="font-family: 'Poppins', sans-serif;">
                                Máte aktivní členství Premium už jen 5 dní – <span class="underline">prodloužení členství zde</span>
                            </p>
                        </div>
                        
                        <!-- Mobile layout: Diamond | Text | X -->
                        <img src="<?php echo e(asset('images/icons/diamond.svg')); ?>" class="hidden max-[426px]:block w-[20px] h-[20px] flex-shrink-0 max-[426px]:self-start max-[426px]:mt-3" alt="Diamond">
                        
                        <p class="hidden max-[426px]:block text-[13px] text-[#505050] font-medium leading-tight text-left max-[426px]:w-[223px] max-[426px]:h-[59px] max-[426px]:self-center"
                           style="font-family: 'Poppins', sans-serif;">
                            Máte aktivní členství Premium už jen 5 dní – <span class="underline">prodloužení členství zde</span>
                        </p>
                        
                        <!-- Close button -->
                        <button @click="show = false"
                                class="flex items-center justify-center flex-shrink-0 absolute right-4 top-1/2 -translate-y-1/2 max-[426px]:static max-[426px]:translate-y-0 max-[426px]:self-start max-[426px]:mt-3 status-bar-close">
                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 1L9 9M9 1L1 9" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Welcome Heading -->
                    <div class="mt-[32px] mb-[32px] text-center">
                        <h1 class="font-bold text-[36px] max-[426px]:text-[24px] leading-none" style="font-family: 'Poppins', sans-serif;">
                            <span style="color: #5C2D62;">Nahlášené dívky</span>
                        </h1>
                    </div>

                    <!-- Subtitle -->
                    <div class="w-[843px] max-[426px]:w-[310px] flex justify-center mb-8 mt-[14px]">
                        <p class="text-[14px] text-center" style="font-family: 'Poppins', sans-serif; color: #505050;">
                            Oprávněné aniž i odstoupil o snadno osoby vede grafikou osobami úmyslu 60 % před platbě státu zvláštních tuzemsku. Dohodnou zvláštní provádí o nebezpečí kódech § 6 příjmu vhodným třetím
                        </p>
                    </div>

                    <!-- Divider -->
                    <div class="w-[843px] max-[426px]:w-[310px] h-[1px] mb-6" style="background-color: #E6E6E6;"></div>

                    <!-- Grid: 2 columns -->
                    <div class="grid grid-cols-2 gap-6 max-[426px]:grid-cols-1">
                        <?php
                            $mockProfiles = collect([
                                (object)['id' => 1, 'display_name' => 'Tamara', 'age' => 24, 'city' => 'Mariánka', 'content' => ['card_location' => 'Mariánka', 'card_height_cm' => 168], 'image_url' => asset('images/models/model1.png')],
                                (object)['id' => 2, 'display_name' => 'Jana', 'age' => 26, 'city' => 'Mariánka', 'content' => ['card_location' => 'Mariánka', 'card_height_cm' => 168], 'image_url' => asset('images/models/model2.png')],
                                (object)['id' => 3, 'display_name' => 'Angela', 'age' => 22, 'city' => 'Hodonínová', 'content' => ['card_location' => 'Hodonínová', 'card_height_cm' => 170], 'image_url' => asset('images/models/model3.png')],
                                (object)['id' => 4, 'display_name' => 'Lucie', 'age' => 25, 'city' => 'Hodonínová', 'content' => ['card_location' => 'Hodonínová', 'card_height_cm' => 168], 'image_url' => asset('images/models/model4.png')],
                                (object)['id' => 5, 'display_name' => 'Tereza', 'age' => 23, 'city' => 'Praha', 'content' => ['card_location' => 'Praha', 'card_height_cm' => 165], 'image_url' => asset('images/models/model5.png')],
                                (object)['id' => 6, 'display_name' => 'Eva', 'age' => 27, 'city' => 'Brno', 'content' => ['card_location' => 'Brno', 'card_height_cm' => 169], 'image_url' => asset('images/models/model6.png')],
                            ]);
                        ?>
                        <?php $__currentLoopData = $mockProfiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $profile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if (isset($component)) { $__componentOriginal2299f79f212ad7b2f1b6f23328beba2f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2299f79f212ad7b2f1b6f23328beba2f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.profile-card','data' => ['profile' => $profile,'simpleMode' => true,'isReported' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('profile-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['profile' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($profile),'simpleMode' => true,'isReported' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2299f79f212ad7b2f1b6f23328beba2f)): ?>
<?php $attributes = $__attributesOriginal2299f79f212ad7b2f1b6f23328beba2f; ?>
<?php unset($__attributesOriginal2299f79f212ad7b2f1b6f23328beba2f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2299f79f212ad7b2f1b6f23328beba2f)): ?>
<?php $component = $__componentOriginal2299f79f212ad7b2f1b6f23328beba2f; ?>
<?php unset($__componentOriginal2299f79f212ad7b2f1b6f23328beba2f); ?>
<?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                </div>
                <!-- /Main Content -->

            </div>
        </div>
    </main>

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
</div>

<style>
    .status-bar-close svg path {
        stroke: #505050;
    }
    
    @media (max-width: 426px) {
        .status-bar-close svg path {
            stroke: #DD3888;
        }
    }
</style>

</body>
</html>
<?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/account/reported_preview.blade.php ENDPATH**/ ?>