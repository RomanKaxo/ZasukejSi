<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - Dashboard</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</head>
<body class="bg-white">

<div id="app">
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
        <div class="container mx-auto pt-32 px-4">
            <div class="warning-banner w-[1134px] max-[426px]:w-[309px] h-[50px] max-[426px]:h-[110px] bg-[#FFE0E5] rounded-[8px] flex items-center justify-center relative px-4 mb-8 mx-auto">
                <img src="<?php echo e(asset('images/icons/OctagonAlert.svg')); ?>" class="warning-banner-icon w-[20px] h-[20px] max-[426px]:absolute max-[426px]:top-[10px] max-[426px]:left-[10px]" alt="Alert">
                
                <div class="warning-text-container w-auto max-[426px]:w-[223px] max-[426px]:h-[83px] flex items-center justify-center gap-3">
                    <span class="font-medium text-[14px] text-[#505050] whitespace-nowrap max-[426px]:whitespace-normal" style="font-family: 'Poppins', sans-serif;">
                        Dokončete registraci Oprávněné aniž i odstoupil o <span class="underline">snadno osoby</span> vede grafikou osobami
                    </span>
                </div>
                <button class="warning-banner-close text-[#DD3888] font-bold absolute right-4 max-[426px]:top-[10px] max-[426px]:right-[10px]">X</button>
            </div>
            
            <!-- Container to reorder on mobile -->
            <div class="flex flex-col max-[426px]:flex-col-reverse items-center max-[426px]:w-full">
                <!-- Stats Cards & Basic Info -->
                <div class="mb-[88px] stats-container flex flex-nowrap justify-end gap-4 mx-auto w-[1134px] max-[426px]:flex-col max-[426px]:items-center max-[426px]:w-full max-[426px]:gap-[30px]">
                    <?php if (isset($component)) { $__componentOriginalc196470d5436dac6266616cef2a92302 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc196470d5436dac6266616cef2a92302 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stats-card','data' => ['icon' => ''.e(asset('images/icons/Eye.svg')).'','alt' => 'Eye','value' => '10 458','label' => 'Celkové zobrazení profilu']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stats-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => ''.e(asset('images/icons/Eye.svg')).'','alt' => 'Eye','value' => '10 458','label' => 'Celkové zobrazení profilu']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $attributes = $__attributesOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__attributesOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $component = $__componentOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__componentOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginalc196470d5436dac6266616cef2a92302 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc196470d5436dac6266616cef2a92302 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stats-card','data' => ['icon' => ''.e(asset('images/icons/ThumbsUp.svg')).'','alt' => 'Thumbsup','value' => '4.78/5','label' => 'Moje hodnocení']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stats-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => ''.e(asset('images/icons/ThumbsUp.svg')).'','alt' => 'Thumbsup','value' => '4.78/5','label' => 'Moje hodnocení']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $attributes = $__attributesOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__attributesOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $component = $__componentOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__componentOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginalc196470d5436dac6266616cef2a92302 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc196470d5436dac6266616cef2a92302 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stats-card','data' => ['icon' => ''.e(asset('images/icons/MessageCircleMore.svg')).'','alt' => 'Message','value' => '12','label' => 'moje recenze']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stats-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => ''.e(asset('images/icons/MessageCircleMore.svg')).'','alt' => 'Message','value' => '12','label' => 'moje recenze']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $attributes = $__attributesOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__attributesOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $component = $__componentOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__componentOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
                </div>
                
                <!-- Title & Basic Info -->
                <div class="flex flex-col items-end w-[1134px] mx-auto max-[426px]:items-center max-[426px]:w-full max-[426px]:my-8">
                    <div class="w-[843px] max-[426px]:w-[310px] flex items-center justify-center relative">
                        <h2 class="font-bold text-[36px] text-[#5C2D62]" style="font-family: 'Poppins', sans-serif;">Základní údaje</h2>

                        <div class="absolute right-0 flex flex-col items-end gap-0.5 max-[426px]:hidden">
                            <div class="flex items-center gap-1.5">
                                <div class="w-[14px] h-[14px] rounded-full bg-[#00B80F]"></div>
                                <span class="text-[13px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">Nyní si online</span>
                            </div>
                            <span class="text-[13px] text-[#505050] underline" style="font-family: 'Poppins', sans-serif; color: #DD3888;">nastavení</span>
                        </div>
                    </div>
                    <?php if (isset($component)) { $__componentOriginal8c86fdab6daf352b9101d50077cb8984 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c86fdab6daf352b9101d50077cb8984 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.section-divider','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.section-divider'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c86fdab6daf352b9101d50077cb8984)): ?>
<?php $attributes = $__attributesOriginal8c86fdab6daf352b9101d50077cb8984; ?>
<?php unset($__attributesOriginal8c86fdab6daf352b9101d50077cb8984); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c86fdab6daf352b9101d50077cb8984)): ?>
<?php $component = $__componentOriginal8c86fdab6daf352b9101d50077cb8984; ?>
<?php unset($__componentOriginal8c86fdab6daf352b9101d50077cb8984); ?>
<?php endif; ?>
                    
                    <!-- Mobile status indicator (rendered below the divider) -->
                    <div class="hidden max-[426px]:flex flex-row justify-between w-[310px] items-center mt-6 mx-auto">
                        <div class="flex items-center gap-1.5">
                            <div class="w-[14px] h-[14px] rounded-full bg-[#00B80F]"></div>
                            <span class="text-[13px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">Nyní si online</span>
                        </div>
                        <span class="text-[13px] text-[#505050] underline" style="font-family: 'Poppins', sans-serif; color: #DD3888;">nastavení</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mb-8 gap-x-12 w-[1134px] mx-auto max-[426px]:!w-[310px] max-[426px]:mx-auto">
                <div class="max-[426px]:hidden">
                    <?php if (isset($component)) { $__componentOriginal060abe2a9b4511e378911474e77b046d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal060abe2a9b4511e378911474e77b046d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.sidebar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal060abe2a9b4511e378911474e77b046d)): ?>
<?php $attributes = $__attributesOriginal060abe2a9b4511e378911474e77b046d; ?>
<?php unset($__attributesOriginal060abe2a9b4511e378911474e77b046d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal060abe2a9b4511e378911474e77b046d)): ?>
<?php $component = $__componentOriginal060abe2a9b4511e378911474e77b046d; ?>
<?php unset($__componentOriginal060abe2a9b4511e378911474e77b046d); ?>
<?php endif; ?>
                </div>
                
                <div class="w-[843px] max-[426px]:w-[310px] max-[426px]:mx-auto flex flex-col items-center mt-12 max-[426px]:items-center">
                    <?php if (isset($component)) { $__componentOriginal9f0158aacaa7306e9511a7f8238e19f9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f0158aacaa7306e9511a7f8238e19f9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.basic-info-form','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.basic-info-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f0158aacaa7306e9511a7f8238e19f9)): ?>
<?php $attributes = $__attributesOriginal9f0158aacaa7306e9511a7f8238e19f9; ?>
<?php unset($__attributesOriginal9f0158aacaa7306e9511a7f8238e19f9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f0158aacaa7306e9511a7f8238e19f9)): ?>
<?php $component = $__componentOriginal9f0158aacaa7306e9511a7f8238e19f9; ?>
<?php unset($__componentOriginal9f0158aacaa7306e9511a7f8238e19f9); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal8c86fdab6daf352b9101d50077cb8984 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c86fdab6daf352b9101d50077cb8984 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.section-divider','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.section-divider'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c86fdab6daf352b9101d50077cb8984)): ?>
<?php $attributes = $__attributesOriginal8c86fdab6daf352b9101d50077cb8984; ?>
<?php unset($__attributesOriginal8c86fdab6daf352b9101d50077cb8984); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c86fdab6daf352b9101d50077cb8984)): ?>
<?php $component = $__componentOriginal8c86fdab6daf352b9101d50077cb8984; ?>
<?php unset($__componentOriginal8c86fdab6daf352b9101d50077cb8984); ?>
<?php endif; ?>

                    <!-- Moje tělo -->
                    <div x-data="{
                        vek: '',
                        vaha: '',
                        vyska: '',
                        prsa: '',
                        openPrsa: false,
                        prsaOptions: ['A', 'B', 'C', 'D', 'E', 'F']
                    }" class="flex flex-col w-[400px] max-[426px]:w-[310px] mx-auto mt-8">
                        <h3 class="font-bold text-[24px] text-[#5C2D62] mb-8" style="font-family: 'Poppins', sans-serif;">Moje tělo</h3>

                        <!-- Věk -->
                        <div class="w-[400px] max-[426px]:w-[310px] h-[84px] flex flex-col gap-2 items-center">
                            <label class="text-[13px] text-[#505050] self-start" style="font-family: 'Poppins', sans-serif;">Věk</label>
                            <input x-model="vek" type="number" class="w-[400px] max-[426px]:!w-[310px] h-[50px] rounded-[8px] border-[2px] border-[#E6E6E6] px-4 font-bold text-[15px] text-[#505050]" style="font-family: 'Poppins', sans-serif;" placeholder="25">
                        </div>

                        <!-- Váha -->
                        <div class="w-[400px] max-[426px]:w-[310px] h-auto flex flex-col gap-2 items-center mt-4">
                            <div class="flex justify-between w-[400px] max-[426px]:w-[310px]">
                                <label class="text-[13px] text-[#505050] self-start" style="font-family: 'Poppins', sans-serif;">Váha (kg)</label>
                                <div x-show="vaha === ''" class="flex items-center gap-1.5">
                                    <img src="<?php echo e(asset('images/icons/OctagonAlert.svg')); ?>" class="w-[20px] h-[20px]" alt="Alert">
                                    <span class="text-[13px] text-[#D80027]" style="font-family: 'Poppins', sans-serif;">Povinná položka</span>
                                </div>
                            </div>
                            <input x-model="vaha" type="number" class="w-[400px] max-[426px]:!w-[310px] h-[50px] rounded-[8px] border-[2px] px-4 font-bold text-[15px] text-[#505050]" :class="vaha === '' ? '!border-[#D80027]' : '!border-[#E6E6E6]'" style="font-family: 'Poppins', sans-serif;" placeholder="60">
                        </div>

                        <!-- Výška -->
                        <div class="w-[400px] max-[426px]:w-[310px] h-[84px] flex flex-col gap-2 items-center mt-4">
                            <label class="text-[13px] text-[#505050] self-start" style="font-family: 'Poppins', sans-serif;">Výška (cm)</label>
                            <input x-model="vyska" type="number" class="w-[400px] max-[426px]:!w-[310px] h-[50px] rounded-[8px] border-[2px] border-[#E6E6E6] px-4 font-bold text-[15px] text-[#505050]" style="font-family: 'Poppins', sans-serif;" placeholder="170">
                        </div>

                        <!-- Prsa -->
                        <div class="w-[400px] max-[426px]:w-[310px] h-auto flex flex-col gap-2 items-center mt-4">
                            <label class="text-[13px] text-[#505050] self-start" style="font-family: 'Poppins', sans-serif;">Prsa</label>
                            <div class="relative w-[400px] max-[426px]:w-[310px]">
                                <input x-model="prsa" type="text" readonly class="w-[400px] max-[426px]:!w-[310px] h-[50px] rounded-[8px] border-[2px] border-[#E6E6E6] px-4 font-bold text-[15px] text-[#505050] cursor-pointer" style="font-family: 'Poppins', sans-serif;" placeholder="Vyberte" @click="openPrsa = !openPrsa">
                                <button @click="openPrsa = !openPrsa" class="absolute right-1 top-1 w-[42px] h-[42px] rounded-[4px] bg-[#DD3888] flex items-center justify-center">
                                    <svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 1L5 4L9 1" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                            <div x-show="openPrsa" class="w-[400px] max-[426px]:w-[310px] border-[2px] border-t-0 border-[#E6E6E6] rounded-b-[8px] bg-white z-10">
                                <template x-for="option in prsaOptions" :key="option">
                                    <div class="p-2 cursor-pointer hover:bg-[#FFE0E5]" @click="prsa = option; openPrsa = false">
                                        <span x-text="option"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Save Button -->
                        <button class="group w-[400px] max-[426px]:!w-[310px] h-[50px] bg-[#E8E8E8] hover:bg-[#5C2D62] rounded-[8px] flex items-center justify-center gap-2 mt-8 mb-8 transition-colors duration-300">
                            <img src="<?php echo e(asset('images/icons/Save.svg')); ?>" class="w-[20px] h-[20px] group-hover:hidden" alt="Save">
                            <img src="<?php echo e(asset('images/icons/SaveWhite.svg')); ?>" class="w-[20px] h-[20px] hidden group-hover:block" alt="Save">
                            <span class="group-hover:text-white" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #A4A4A4;">Uložit změny</span>
                        </button>
                    </div>

                    <?php if (isset($component)) { $__componentOriginal8c86fdab6daf352b9101d50077cb8984 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c86fdab6daf352b9101d50077cb8984 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.section-divider','data' => ['noDesktopMargin' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.section-divider'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['no-desktop-margin' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c86fdab6daf352b9101d50077cb8984)): ?>
<?php $attributes = $__attributesOriginal8c86fdab6daf352b9101d50077cb8984; ?>
<?php unset($__attributesOriginal8c86fdab6daf352b9101d50077cb8984); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c86fdab6daf352b9101d50077cb8984)): ?>
<?php $component = $__componentOriginal8c86fdab6daf352b9101d50077cb8984; ?>
<?php unset($__componentOriginal8c86fdab6daf352b9101d50077cb8984); ?>
<?php endif; ?>

                    <!-- O mně -->
                    <div class="flex flex-col w-[400px] max-[426px]:w-[310px] mx-auto mt-8">
                        <h3 class="font-bold text-[24px] text-[#5C2D62] mb-8" style="font-family: 'Poppins', sans-serif;">O mně</h3>

                        <div class="w-[400px] max-[426px]:w-[310px] h-[440px] flex flex-col gap-2 items-center">
                            <label class="text-[13px] text-[#505050] self-start" style="font-family: 'Poppins', sans-serif;">Napište o sobě (max. 640 znaků)</label>
                            <textarea maxlength="640" class="w-[400px] max-[426px]:!w-[310px] h-[396px] rounded-[8px] border-[2px] border-[#E6E6E6] p-4 font-medium text-[15px] text-[#505050] resize-none" style="font-family: 'Poppins', sans-serif;" placeholder="Povězte o sobě něco zajímavého..."></textarea>
                        </div>
                        
                        <!-- Save Button -->
                        <button class="group w-[400px] max-[426px]:!w-[310px] h-[50px] bg-[#E8E8E8] hover:bg-[#5C2D62] rounded-[8px] flex items-center justify-center gap-2 mt-8 mb-8 transition-colors duration-300">
                            <img src="<?php echo e(asset('images/icons/Save.svg')); ?>" class="w-[20px] h-[20px] group-hover:hidden" alt="Save">
                            <img src="<?php echo e(asset('images/icons/SaveWhite.svg')); ?>" class="w-[20px] h-[20px] hidden group-hover:block" alt="Save">
                            <span class="group-hover:text-white" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #A4A4A4;">Uložit změny</span>
                        </button>
                    </div>

                    <?php if (isset($component)) { $__componentOriginal8c86fdab6daf352b9101d50077cb8984 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c86fdab6daf352b9101d50077cb8984 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.section-divider','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.section-divider'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c86fdab6daf352b9101d50077cb8984)): ?>
<?php $attributes = $__attributesOriginal8c86fdab6daf352b9101d50077cb8984; ?>
<?php unset($__attributesOriginal8c86fdab6daf352b9101d50077cb8984); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c86fdab6daf352b9101d50077cb8984)): ?>
<?php $component = $__componentOriginal8c86fdab6daf352b9101d50077cb8984; ?>
<?php unset($__componentOriginal8c86fdab6daf352b9101d50077cb8984); ?>
<?php endif; ?>

                    <!-- InCall / OutCall -->
                    <div x-data="{ incall: false, outcall: false }" class="flex flex-col w-[400px] max-[426px]:w-[310px] mx-auto mt-8">
                        <h3 class="font-bold text-[24px] text-[#5C2D62] mb-8" style="font-family: 'Poppins', sans-serif;">InCall / OutCall</h3>
                        
                        <div class="flex flex-col gap-[21px]">
                            <div class="flex items-center gap-3">
                                <button @click="incall = !incall" 
                                        class="w-[44px] h-[24px] rounded-full flex items-center p-[2px] transition-colors duration-300"
                                        :class="incall ? 'bg-[#00B80F]' : 'bg-[#E4E4E7]'">
                                    <div class="w-[20px] h-[20px] bg-white rounded-full shadow-sm transform transition-transform duration-300"
                                         :class="incall ? 'translate-x-[20px]' : 'translate-x-0'"></div>
                                </button>
                                <span class="text-[15px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">InCall</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <button @click="outcall = !outcall" 
                                        class="w-[44px] h-[24px] rounded-full flex items-center p-[2px] transition-colors duration-300"
                                        :class="outcall ? 'bg-[#00B80F]' : 'bg-[#E4E4E7]'">
                                    <div class="w-[20px] h-[20px] bg-white rounded-full shadow-sm transform transition-transform duration-300"
                                         :class="outcall ? 'translate-x-[20px]' : 'translate-x-0'"></div>
                                </button>
                                <span class="text-[15px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">OutCall</span>
                            </div>
                        </div>

                        <!-- Save Button -->
                        <button class="group w-[400px] max-[426px]:!w-[310px] h-[50px] bg-[#E8E8E8] hover:bg-[#5C2D62] rounded-[8px] flex items-center justify-center gap-2 mt-8 mb-8 transition-colors duration-300">
                            <img src="<?php echo e(asset('images/icons/Save.svg')); ?>" class="w-[20px] h-[20px] group-hover:hidden" alt="Save">
                            <img src="<?php echo e(asset('images/icons/SaveWhite.svg')); ?>" class="w-[20px] h-[20px] hidden group-hover:block" alt="Save">
                            <span class="group-hover:text-white" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #A4A4A4;">Uložit změny</span>
                        </button>
                    </div>

                    <?php if (isset($component)) { $__componentOriginal8c86fdab6daf352b9101d50077cb8984 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c86fdab6daf352b9101d50077cb8984 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.section-divider','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.section-divider'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c86fdab6daf352b9101d50077cb8984)): ?>
<?php $attributes = $__attributesOriginal8c86fdab6daf352b9101d50077cb8984; ?>
<?php unset($__attributesOriginal8c86fdab6daf352b9101d50077cb8984); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c86fdab6daf352b9101d50077cb8984)): ?>
<?php $component = $__componentOriginal8c86fdab6daf352b9101d50077cb8984; ?>
<?php unset($__componentOriginal8c86fdab6daf352b9101d50077cb8984); ?>
<?php endif; ?>

                    <!-- Moje Ceny pro ČR -->
                    <div x-data="{ mena: 'CZK', openMena: false, menaOptions: ['CZK', 'EUR', 'USD'] }" class="flex flex-col w-[843px] max-[426px]:!w-[310px] mx-auto mt-8">
                        <div class="flex items-center gap-3 mb-8">
                            <img src="https://flagcdn.com/cz.svg" class="w-[30px] h-[30px] rounded-full" alt="CZ Flag">
                            <h3 class="font-bold text-[24px] text-[#5C2D62]" style="font-family: 'Poppins', sans-serif;">Moje Ceny pro ČR</h3>
                        </div>

                        <!-- Měna -->
                        <div class="relative w-[240px] max-[426px]:!w-[310px]">
                            <label class="text-[13px] text-[#505050] mb-2 block" style="font-family: 'Poppins', sans-serif;">Měna pro ČR</label>
                            <button @click="openMena = !openMena" class="w-[240px] max-[426px]:!w-[310px] h-[50px] rounded-[8px] border-[2px] border-[#E6E6E6] px-4 flex items-center justify-between font-bold text-[15px] text-[#505050] cursor-pointer" style="font-family: 'Poppins', sans-serif;">
                                <span x-text="mena"></span>
                                <div class="w-[42px] h-[42px] rounded-[4px] bg-[#DD3888] flex items-center justify-center -mr-3">
                                    <svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 1L5 4L9 1" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </button>
                            <div x-show="openMena" class="w-[240px] max-[426px]:!w-[310px] border-[2px] border-t-0 border-[#E6E6E6] rounded-b-[8px] bg-white z-10 absolute">
                                <template x-for="option in menaOptions" :key="option">
                                    <div class="p-2 cursor-pointer hover:bg-[#FFE0E5]" @click="mena = option; openMena = false">
                                        <span x-text="option"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Price Inputs Row -->
                        <div x-data="{ 
                            rows: [{ id: Date.now() }],
                            addRow() { this.rows.push({ id: Date.now() }) },
                            removeRow(index) { this.rows.splice(index, 1) }
                        }">
                            <template x-for="(row, index) in rows" :key="row.id">
                                <div class="flex items-end gap-4 mt-6 max-[426px]:flex-wrap">
                                    <div class="flex flex-col gap-2 relative max-[426px]:!w-[310px]">
                                        <label class="text-[13px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">časová dotace v hodinách</label>
                                        <input type="number" class="w-[240px] max-[426px]:!w-[310px] h-[50px] rounded-[8px] border-[2px] border-[#E6E6E6] px-4 font-bold text-[15px] text-[#505050]" style="font-family: 'Poppins', sans-serif;" placeholder="1">
                                        <button @click="removeRow(index)" class="hidden max-[426px]:flex absolute right-2 bottom-2 w-[30px] h-[30px] rounded-full bg-[#DD3888] items-center justify-center">
                                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="flex items-end gap-4 max-[426px]:mt-4">
                                        <div class="flex flex-col gap-2">
                                            <label class="text-[13px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">InCall v KČ</label>
                                            <input type="number" class="w-[240px] max-[426px]:!w-[143px] h-[50px] rounded-[8px] border-[2px] border-[#E6E6E6] px-4 font-bold text-[15px] text-[#505050]" style="font-family: 'Poppins', sans-serif;" placeholder="0">
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <label class="text-[13px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">OutCall v KČ</label>
                                            <input type="number" class="w-[240px] max-[426px]:!w-[143px] h-[50px] rounded-[8px] border-[2px] border-[#E6E6E6] px-4 font-bold text-[15px] text-[#505050]" style="font-family: 'Poppins', sans-serif;" placeholder="0">
                                        </div>
                                    </div>
                                    <button @click="removeRow(index)" class="max-[426px]:hidden w-[35px] h-[35px] rounded-full bg-[#DD3888] flex items-center justify-center mb-[7px]">
                                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>

                            <!-- Add Button -->
                            <button @click="addRow" class="w-[781px] max-[426px]:!w-[310px] h-[50px] bg-[#F8F9F9] rounded-[8px] flex items-center justify-center mt-6 font-bold text-[16px] text-[#5C2D62]" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                                + Přidat další
                            </button>
                            <!-- Save Button -->
                            <button class="group w-[240px] max-[426px]:!w-[310px] h-[50px] bg-[#E8E8E8] hover:bg-[#5C2D62] rounded-[8px] flex items-center justify-center gap-2 mt-8 mb-8 transition-colors duration-300">
                                <img src="<?php echo e(asset('images/icons/Save.svg')); ?>" class="w-[20px] h-[20px] group-hover:hidden" alt="Save">
                                <img src="<?php echo e(asset('images/icons/SaveWhite.svg')); ?>" class="w-[20px] h-[20px] hidden group-hover:block" alt="Save">
                                <span class="group-hover:text-white" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #A4A4A4;">Uložit změny</span>
                            </button>
                        </div>
                        <?php if (isset($component)) { $__componentOriginal8c86fdab6daf352b9101d50077cb8984 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c86fdab6daf352b9101d50077cb8984 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.section-divider','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.section-divider'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c86fdab6daf352b9101d50077cb8984)): ?>
<?php $attributes = $__attributesOriginal8c86fdab6daf352b9101d50077cb8984; ?>
<?php unset($__attributesOriginal8c86fdab6daf352b9101d50077cb8984); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c86fdab6daf352b9101d50077cb8984)): ?>
<?php $component = $__componentOriginal8c86fdab6daf352b9101d50077cb8984; ?>
<?php unset($__componentOriginal8c86fdab6daf352b9101d50077cb8984); ?>
<?php endif; ?>
                    </div>

                    <!-- Moje Ceny pro zahraničí -->
                    <div x-data="{ mena: 'EUR', openMena: false, menaOptions: ['CZK', 'EUR', 'USD'] }" class="flex flex-col w-[843px] max-[426px]:!w-[310px] mx-auto mt-8">
                        <div class="flex items-center gap-3 mb-8">
                            <img src="https://flagcdn.com/eu.svg" class="w-[30px] h-[30px] rounded-full object-cover" alt="EU Flag">
                            <h3 class="font-bold text-[24px] text-[#5C2D62]" style="font-family: 'Poppins', sans-serif;">Moje Ceny pro zahraničí</h3>
                        </div>

                        <!-- Měna -->
                        <div class="relative w-[240px] max-[426px]:!w-[310px]">
                            <label class="text-[13px] text-[#505050] mb-2 block" style="font-family: 'Poppins', sans-serif;">Měna pro zahraničí</label>
                            <button @click="openMena = !openMena" class="w-[240px] max-[426px]:!w-[310px] h-[50px] rounded-[8px] border-[2px] border-[#E6E6E6] px-4 flex items-center justify-between font-bold text-[15px] text-[#505050] cursor-pointer" style="font-family: 'Poppins', sans-serif;">
                                <span x-text="mena"></span>
                                <div class="w-[42px] h-[42px] rounded-[4px] bg-[#DD3888] flex items-center justify-center -mr-3">
                                    <svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 1L5 4L9 1" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </button>
                            <div x-show="openMena" class="w-[240px] max-[426px]:!w-[310px] border-[2px] border-t-0 border-[#E6E6E6] rounded-b-[8px] bg-white z-10 absolute">
                                <template x-for="option in menaOptions" :key="option">
                                    <div class="p-2 cursor-pointer hover:bg-[#FFE0E5]" @click="mena = option; openMena = false">
                                        <span x-text="option"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Price Inputs Row -->
                        <div x-data="{ 
                            rows: [{ id: Date.now() }],
                            addRow() { this.rows.push({ id: Date.now() }) },
                            removeRow(index) { this.rows.splice(index, 1) }
                        }">
                            <template x-for="(row, index) in rows" :key="row.id">
                                <div class="flex items-end gap-4 mt-6 max-[426px]:flex-wrap">
                                    <div class="flex flex-col gap-2 relative max-[426px]:!w-[310px]">
                                        <label class="text-[13px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">časová dotace v hodinách</label>
                                        <input type="number" class="w-[240px] max-[426px]:!w-[310px] h-[50px] rounded-[8px] border-[2px] border-[#E6E6E6] px-4 font-bold text-[15px] text-[#505050]" style="font-family: 'Poppins', sans-serif;" placeholder="1">
                                        <button @click="removeRow(index)" class="hidden max-[426px]:flex absolute right-2 bottom-2 w-[30px] h-[30px] rounded-full bg-[#DD3888] items-center justify-center">
                                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="flex items-end gap-4 max-[426px]:mt-4">
                                        <div class="flex flex-col gap-2">
                                            <label class="text-[13px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">InCall v EUR</label>
                                            <input type="number" class="w-[240px] max-[426px]:!w-[143px] h-[50px] rounded-[8px] border-[2px] border-[#E6E6E6] px-4 font-bold text-[15px] text-[#505050]" style="font-family: 'Poppins', sans-serif;" placeholder="0">
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <label class="text-[13px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">OutCall v EUR</label>
                                            <input type="number" class="w-[240px] max-[426px]:!w-[143px] h-[50px] rounded-[8px] border-[2px] border-[#E6E6E6] px-4 font-bold text-[15px] text-[#505050]" style="font-family: 'Poppins', sans-serif;" placeholder="0">
                                        </div>
                                    </div>
                                    <button @click="removeRow(index)" class="max-[426px]:hidden w-[35px] h-[35px] rounded-full bg-[#DD3888] flex items-center justify-center mb-[7px]">
                                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 1L9 9M9 1L1 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>

                            <!-- Add Button -->
                            <button @click="addRow" class="w-[781px] max-[426px]:!w-[310px] h-[50px] bg-[#F8F9F9] rounded-[8px] flex items-center justify-center mt-6 font-bold text-[16px] text-[#5C2D62]" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                                + Přidat další
                            </button>
                            <!-- Save Button -->
                            <button class="group w-[240px] max-[426px]:!w-[310px] h-[50px] bg-[#E8E8E8] hover:bg-[#5C2D62] rounded-[8px] flex items-center justify-center gap-2 mt-8 mb-8 transition-colors duration-300">
                                <img src="<?php echo e(asset('images/icons/Save.svg')); ?>" class="w-[20px] h-[20px] group-hover:hidden" alt="Save">
                                <img src="<?php echo e(asset('images/icons/SaveWhite.svg')); ?>" class="w-[20px] h-[20px] hidden group-hover:block" alt="Save">
                                <span class="group-hover:text-white" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #A4A4A4;">Uložit změny</span>
                            </button>
                        </div>
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
    </div>
</body>
</html>
<?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/account/dashboard_preview.blade.php ENDPATH**/ ?>