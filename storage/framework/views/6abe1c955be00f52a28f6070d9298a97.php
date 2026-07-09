<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - Hodnocení dívek</title>
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
                            <!-- Hodnocení dívek – ACTIVE -->
                            <a href="<?php echo e(route('preview.ratings')); ?>"
                               class="w-[210px] h-[50px] rounded-[8px] bg-[#DD3888] text-white flex items-center px-4 gap-3 font-medium text-[14px]"
                               style="font-family: 'Poppins', sans-serif;">
                                <img src="<?php echo e(asset('images/icons/star.svg')); ?>" class="w-[20px] h-[20px]" alt="Star"
                                     style="filter: brightness(0) invert(1);">
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
                            <!-- Nahlášené dívky -->
                            <a href="#"
                               class="w-[210px] h-[50px] rounded-[8px] border border-[#E6E6E6] text-[#505050] flex items-center px-4 gap-3 font-medium text-[14px]"
                               style="font-family: 'Poppins', sans-serif;">
                                <img src="<?php echo e(asset('images/icons/OctagonAlert.svg')); ?>" class="w-[20px] h-[20px]" alt="Report"
                                     style="filter: invert(36%) sepia(87%) saturate(2222%) hue-rotate(309deg) brightness(90%) contrast(92%);">
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
                            <span style="color: #D4D4D4;">Vítejte, </span><span style="color: #5C2D62;">Hercules94</span>
                        </h1>
                    </div>

                    <!-- Divider -->
                    <div class="w-[843px] max-[426px]:w-[310px] h-[1px] mb-6" style="background-color: #E6E6E6;"></div>

                    <!-- Subtitle -->
                    <div class="w-[843px] max-[426px]:w-[310px] flex justify-center mb-8 mt-[14px]">
                        <h2 class="font-bold text-[24px] max-[426px]:text-[18px] text-center" style="font-family: 'Poppins', sans-serif; color: #5C2D62;">
                            Podívejte se na dívky v okolí a dejte jim hodnocení
                        </h2>
                    </div>

                    <!-- Two-column layout -->
                    <div class="flex gap-6 max-[426px]:flex-col max-[426px]:gap-4">

                        <!-- ── Left Column (510px) ── -->
                        <div class="flex-shrink-0 max-[426px]:!w-[310px]" style="width: 510px;">

                            <!-- Vyberte kraj accordion -->
                            <div x-data="{
                                    kraj: 'Hlavní město Praha',
                                    openKraj: false,
                                    krajOptions: [
                                        'Hlavní město Praha',
                                        'Středočeský kraj',
                                        'Jihočeský kraj',
                                        'Plzeňský kraj',
                                        'Karlovarský kraj',
                                        'Ústecký kraj',
                                        'Liberecký kraj',
                                        'Královéhradecký kraj',
                                        'Pardubický kraj',
                                        'Kraj Vysočina',
                                        'Jihomoravský kraj',
                                        'Olomoucký kraj',
                                        'Zlínský kraj',
                                        'Moravskoslezský kraj'
                                    ]
                                 }"
                                 class="mb-8">
                                <label class="block text-[13px] text-[#505050] mb-2"
                                       style="font-family: 'Poppins', sans-serif;">Vyberte kraj</label>
                                <div class="relative w-[510px] max-[426px]:w-[310px]">
                                    <input x-model="kraj" type="text" readonly
                                           class="w-[510px] max-[426px]:w-[310px] h-[60px] rounded-[8px] border-[2px] border-[#E6E6E6] px-4 font-bold text-[15px] text-[#505050] cursor-pointer bg-white"
                                           style="font-family: 'Poppins', sans-serif;"
                                           @click="openKraj = !openKraj">
                                    <button @click="openKraj = !openKraj"
                                            class="absolute right-1 top-1 w-[48px] h-[48px] rounded-[4px] bg-[#F2F2F2] flex items-center justify-center">
                                        <svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 1L5 4L9 1" stroke="#DD3888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                                <div x-show="openKraj"
                                     class="w-[510px] max-[426px]:w-[310px] border-[2px] border-t-0 border-[#E6E6E6] rounded-b-[8px] bg-white z-20 max-h-[220px] overflow-y-auto relative">
                                    <template x-for="option in krajOptions" :key="option">
                                        <div class="px-4 py-2 cursor-pointer hover:bg-[#FFE0E5] text-[15px] font-bold text-[#505050]"
                                             style="font-family: 'Poppins', sans-serif;"
                                             @click="kraj = option; openKraj = false">
                                            <span x-text="option"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Photo Card (510×642) -->
                            <div x-data="{ 
                                activeDot: 0, 
                                images: [
                                    '<?php echo e(asset('images/rating/main1.png')); ?>',
                                    '<?php echo e(asset('images/rating/main2.png')); ?>',
                                    '<?php echo e(asset('images/rating/main3.png')); ?>'
                                ]
                            }"
                                 class="overflow-hidden relative max-[426px]:!w-[310px] max-[426px]:!h-[434px]"
                                 style="width: 510px; height: 642px; border-radius: 15px 15px 0 0;">

                                <!-- Vertical carousel - všechny fotky vertikálně pod sebou -->
                                <div class="w-full transition-transform duration-500 ease-in-out"
                                     :style="{ transform: `translateY(-${activeDot * 610}px)` }">
                                    <template x-for="(img, idx) in images" :key="idx">
                                        <div class="w-full overflow-hidden max-[426px]:!h-[390px]"
                                             style="height: 610px; border-radius: 15px 15px 0 0;">
                                            <img :src="img"
                                                 class="w-full h-full object-cover"
                                                 :alt="'Photo ' + (idx + 1)">
                                        </div>
                                    </template>
                                </div>

                                <!-- Služby overlay (when activeDot === 2) - nadpis + pilulky flexem zalomeny -->
                                <div x-show="activeDot === 2"
                                     class="absolute left-6 top-[115px] z-30">
                                    <!-- Služby heading -->
                                    <h3 class="font-bold text-[16px] mb-4" style="font-family: 'Poppins', sans-serif; color: #505050;">
                                        Služby
                                    </h3>
                                    <!-- Services pills flex wrap - automatické zalomení podle textu -->
                                    <div class="flex flex-wrap gap-[8px]" style="width: 365px;">
                                        <button class="rounded-full bg-white border-2 whitespace-nowrap"
                                                style="padding: 10px 17px; border-color: #F2F2F2; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 500; color: #505050;">
                                            Běžné fotografie
                                        </button>
                                        <button class="rounded-full bg-white border-2 whitespace-nowrap"
                                                style="padding: 10px 17px; border-color: #F2F2F2; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 500; color: #505050;">
                                            Vaginální sex
                                        </button>
                                        <button class="rounded-full bg-white border-2 whitespace-nowrap"
                                                style="padding: 10px 17px; border-color: #F2F2F2; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 500; color: #505050;">
                                            Páry
                                        </button>
                                        <button class="rounded-full bg-white border-2 whitespace-nowrap"
                                                style="padding: 10px 17px; border-color: #F2F2F2; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 500; color: #505050;">
                                            Výstřik na tělo
                                        </button>
                                        <button class="rounded-full bg-white border-2 whitespace-nowrap"
                                                style="padding: 10px 17px; border-color: #F2F2F2; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 500; color: #505050;">
                                            Lízání
                                        </button>
                                        <button class="rounded-full bg-white border-2 whitespace-nowrap max-[426px]:hidden"
                                                style="padding: 10px 17px; border-color: #F2F2F2; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 500; color: #505050;">
                                            Nadávání
                                        </button>
                                        <button class="rounded-full bg-white border-2 whitespace-nowrap max-[426px]:hidden"
                                                style="padding: 10px 17px; border-color: #F2F2F2; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 500; color: #505050;">
                                            Lízání
                                        </button>
                                        <button class="rounded-full bg-white border-2 whitespace-nowrap max-[426px]:hidden"
                                                style="padding: 10px 17px; border-color: #F2F2F2; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 500; color: #505050;">
                                            Nadávání
                                        </button>
                                        <button class="rounded-full bg-white border-2 whitespace-nowrap max-[426px]:hidden"
                                                style="padding: 10px 17px; border-color: #F2F2F2; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 500; color: #505050;">
                                            Dominantní
                                        </button>
                                        <button class="rounded-full bg-white border-2 whitespace-nowrap max-[426px]:hidden"
                                                style="padding: 10px 17px; border-color: #F2F2F2; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 500; color: #505050;">
                                            Běžné fotografie
                                        </button>
                                        <button class="rounded-full bg-white border-2 whitespace-nowrap max-[426px]:hidden"
                                                style="padding: 10px 17px; border-color: #F2F2F2; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 500; color: #505050;">
                                            Páry
                                        </button>
                                        <button class="rounded-full bg-white border-2 whitespace-nowrap max-[426px]:hidden"
                                                style="padding: 10px 17px; border-color: #F2F2F2; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 500; color: #505050;">
                                            Lízání
                                        </button>
                                        <button class="rounded-full bg-white border-2 whitespace-nowrap max-[426px]:hidden"
                                                style="padding: 10px 17px; border-color: #F2F2F2; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 500; color: #505050;">
                                            Výstřik na tělo
                                        </button>
                                        <button class="rounded-full bg-white border-2 whitespace-nowrap max-[426px]:hidden"
                                                style="padding: 10px 17px; border-color: #F2F2F2; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 500; color: #505050;">
                                            Erotická masáž
                                        </button>
                                        <button class="rounded-full bg-white border-2 whitespace-nowrap"
                                                style="padding: 10px 17px; border-color: #F2F2F2; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 500; color: #505050;">
                                            + dalších 12 služeb...
                                        </button>
                                    </div>
                                </div>

                                <!-- Gradient overlay pro main3 fotku -->
                                <div x-show="activeDot === 2" class="absolute inset-0 pointer-events-none z-20 transition-opacity duration-500"
                                     style="background: linear-gradient(to bottom, #A4A4A4 0%, #E8E8E8 100%); border-radius: 15px 15px 0 0;"></div>

                                <!-- Dark gradient overlay (absolute) - skrytý pro main3 -->
                                <div x-show="activeDot !== 2" class="absolute bottom-0 left-0 right-0 h-[180px] pointer-events-none z-10"
                                     style="background: linear-gradient(to top, rgba(0,0,0,0.75) 0%, transparent 100%);"></div>

                                <!-- Top overlay: Name + action buttons + X button -->
                                <div class="absolute top-8 left-4 right-4 flex items-start justify-between max-[426px]:items-center z-20">
                                    <!-- Name & buttons -->
                                    <div class="flex flex-col gap-2 max-[426px]:justify-center">
                                        <h3 class="font-bold text-[24px] max-[426px]:text-[18px] text-white"
                                            style="font-family: 'Poppins', sans-serif; text-shadow: 0 2px 8px rgba(0,0,0,0.5);">
                                            Anlexandrina (25)
                                        </h3>
                                        <div class="flex gap-2 max-[426px]:hidden">
                                            <button class="flex items-center justify-center rounded-[8px] bg-[#DD3888] text-white text-[13px] max-[426px]:text-[11px] font-semibold max-[426px]:!w-[90px] max-[426px]:!h-[26px]"
                                                    style="width: 120px; height: 30px; font-family: 'Poppins', sans-serif;">
                                                Přejít na profil
                                            </button>
                                            <button class="flex items-center justify-center rounded-[8px] bg-[#DD3888] text-white text-[13px] max-[426px]:text-[11px] font-semibold max-[426px]:!w-[60px] max-[426px]:!h-[26px]"
                                                    style="width: 76px; height: 30px; font-family: 'Poppins', sans-serif;">
                                                Zpráva
                                            </button>
                                        </div>
                                    </div>
                                    <!-- X / skip button -->
                                    <button class="flex items-center justify-center rounded-[8px] flex-shrink-0 max-[426px]:!w-[40px] max-[426px]:!h-[40px]"
                                            style="width: 65px; height: 65px; background-color: #5C2D62;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="max-[426px]:!w-[16px] max-[426px]:!h-[16px]">
                                            <path d="M5 5L19 19M19 5L5 19" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Navigation radio buttons (right-center) -->
                                <div class="absolute right-4 top-[305px] -translate-y-1/2 max-[426px]:bottom-[50%] max-[426px]:translate-y-1/2 max-[426px]:right-4 max-[426px]:top-auto flex flex-col gap-[8px] z-20">
                                    <template x-for="i in 3" :key="i">
                                        <button @click="activeDot = i - 1"
                                                class="flex items-center justify-center rounded-full border-2"
                                                style="width: 16px; height: 16px; border-color: #FFFFFF; background-color: #FFFFFF;">
                                            <div x-show="activeDot === i - 1"
                                                 class="rounded-full"
                                                 style="width: 12px; height: 12px; background-color: #DD3888;"></div>
                                        </button>
                                    </template>
                                </div>

                                <!-- Bottom overlay: rating label + badges -->
                                <div class="absolute bottom-8 left-0 right-0 px-4 pb-4 max-[426px]:px-2 max-[426px]:pb-2 z-20">
                                    <!-- "Vaše hodnocení" label (hidden on mobile) -->
                                    <p class="text-[13px] text-white font-medium text-center mb-2 max-[426px]:hidden"
                                       style="font-family: 'Poppins', sans-serif; text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.4);">
                                        Vaše hodnocení
                                    </p>
                                    <!-- 4 Rating badges -->
                                    <div class="flex gap-[7px] justify-center max-[426px]:gap-[45px]">
                                        <!-- 1. Uložit -->
                                        <button class="flex items-center justify-center gap-[6px] max-[426px]:gap-[4px] rounded-[8px] max-[426px]:!w-[80px] max-[426px]:!h-[40px]"
                                                style="width: 100px; height: 45px; background-color: #DD3888;">
                                            <span class="font-semibold text-[16px] max-[426px]:text-[14px] text-white"
                                                  style="font-family: 'Poppins', sans-serif;">Ulož</span>
                                            <img src="<?php echo e(asset('images/icons/heart.svg')); ?>" class="w-[24px] h-[24px] max-[426px]:!w-[16px] max-[426px]:!h-[16px]" alt="Heart"
                                                 style="filter: brightness(0) invert(1);">
                                        </button>
                                        <!-- Mobile buttons wrapper -->
                                        <div class="hidden max-[426px]:flex gap-[10px] items-center">
                                            <!-- 2. Profil (mobile only) -->
                                            <button class="flex items-center justify-center gap-[4px] rounded-[8px]"
                                                    style="width: 97px; height: 40px; background-color: #FFFFFF;">
                                                <span class="font-semibold text-[16px]"
                                                      style="font-family: 'Poppins', sans-serif; color: #DD3888;">Profil</span>
                                                <img src="<?php echo e(asset('images/icons/User.svg')); ?>" class="w-[24px] h-[24px]" alt="Profile"
                                                     style="filter: invert(36%) sepia(87%) saturate(2222%) hue-rotate(309deg) brightness(90%) contrast(92%);">
                                            </button>
                                            <!-- 3. Message (mobile only) -->
                                            <button class="flex items-center justify-center rounded-[8px]"
                                                    style="width: 40px; height: 40px; background-color: #FFFFFF;">
                                                <img src="<?php echo e(asset('images/icons/MessageCircleMore.svg')); ?>" class="w-[24px] h-[24px]" alt="Message"
                                                     style="filter: invert(36%) sepia(87%) saturate(2222%) hue-rotate(309deg) brightness(90%) contrast(92%);">
                                            </button>
                                        </div>
                                        <!-- 2. 100% (desktop only) -->
                                        <button class="flex items-center justify-center gap-[6px] rounded-[8px] max-[426px]:hidden"
                                                style="width: 100px; height: 45px; background-color: #00B80F;">
                                            <span class="font-semibold text-[16px] text-white"
                                                  style="font-family: 'Poppins', sans-serif;">100 %</span>
                                            <img src="<?php echo e(asset('images/icons/happy-smile.svg')); ?>" class="w-[24px] h-[24px]" alt="Happy"
                                                 style="filter: brightness(0) invert(1);">
                                        </button>
                                        <!-- 3. 70% (desktop only) -->
                                        <button class="flex items-center justify-center gap-[6px] rounded-[8px] max-[426px]:hidden"
                                                style="width: 100px; height: 45px; background-color: #FFB700;">
                                            <span class="font-semibold text-[16px] text-white"
                                                  style="font-family: 'Poppins', sans-serif;">70 %</span>
                                            <img src="<?php echo e(asset('images/icons/smile.svg')); ?>" class="w-[24px] h-[24px]" alt="Smile"
                                                 style="filter: brightness(0) invert(1);">
                                        </button>
                                        <!-- 4. 30% (desktop only) -->
                                        <button class="flex items-center justify-center gap-[6px] rounded-[8px] max-[426px]:hidden"
                                                style="width: 100px; height: 45px; background-color: #F47216;">
                                            <span class="font-semibold text-[16px] text-white"
                                                  style="font-family: 'Poppins', sans-serif;">30 %</span>
                                            <img src="<?php echo e(asset('images/icons/meh.svg')); ?>" class="w-[24px] h-[24px]" alt="Meh"
                                                 style="filter: brightness(0) invert(1);">
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile: Rating badges under photo container -->
                        <!-- "Vaše hodnocení" label (mobile only) -->
                        <p class="hidden max-[426px]:block text-[13px] text-center mb-2"
                           style="font-family: 'Poppins', sans-serif; font-weight: 500; color: #A4A4A4;">
                            Vaše hodnocení
                        </p>
                        <div class="hidden max-[426px]:flex gap-[4px] justify-center mb-4 max-[426px]:w-[310px]">
                            <!-- 100% -->
                            <button class="flex items-center justify-center gap-[4px] rounded-[8px] w-[100px] h-[50px]"
                                    style="background-color: #00B80F;">
                                <span class="font-semibold text-[16px] text-white"
                                      style="font-family: 'Poppins', sans-serif;">100 %</span>
                                <img src="<?php echo e(asset('images/icons/happy-smile.svg')); ?>" class="w-[24px] h-[24px]" alt="Happy"
                                     style="filter: brightness(0) invert(1);">
                            </button>
                            <!-- 70% -->
                            <button class="flex items-center justify-center gap-[4px] rounded-[8px] w-[100px] h-[50px]"
                                    style="background-color: #FFB700;">
                                <span class="font-semibold text-[16px] text-white"
                                      style="font-family: 'Poppins', sans-serif;">70 %</span>
                                <img src="<?php echo e(asset('images/icons/smile.svg')); ?>" class="w-[24px] h-[24px]" alt="Smile"
                                     style="filter: brightness(0) invert(1);">
                            </button>
                            <!-- 30% -->
                            <button class="flex items-center justify-center gap-[4px] rounded-[8px] w-[100px] h-[50px]"
                                    style="background-color: #F47216;">
                                <span class="font-semibold text-[16px] text-white"
                                      style="font-family: 'Poppins', sans-serif;">30 %</span>
                                <img src="<?php echo e(asset('images/icons/meh.svg')); ?>" class="w-[24px] h-[24px]" alt="Meh"
                                     style="filter: brightness(0) invert(1);">
                            </button>
                        </div>

                        <!-- ── Right Column (flex-1, ~309px) ── -->
                        <div class="flex-1 flex flex-col min-h-0 max-[426px]:w-[310px]">

                            <!-- Header -->
                            <h3 class="font-normal text-[14px] mb-4" style="font-family: 'Poppins', sans-serif; color: #505050;">
                                Vaše historie hodnocení:
                            </h3>

                            <!-- Profile history list -->
                            <?php
                            $historyEntries = [
                                ['name' => 'Alexandrina', 'photo' => 'pfp1.png', 'my' => 70,  'myColor' => '#FFB700', 'others' => 57, 'othersColor' => '#FFB700'],
                                ['name' => 'Marcela',     'photo' => 'pfp2.png', 'my' => 100, 'myColor' => '#00B80F', 'others' => 82, 'othersColor' => '#00B80F'],
                                ['name' => 'Yana',        'photo' => 'pfp3.png', 'my' => 30,  'myColor' => '#F47216', 'others' => 57, 'othersColor' => '#FFB700'],
                                ['name' => 'Alexandrina', 'photo' => 'pfp1.png', 'my' => 70,  'myColor' => '#FFB700', 'others' => 57, 'othersColor' => '#FFB700'],
                                ['name' => 'Marcela',     'photo' => 'pfp2.png', 'my' => 100, 'myColor' => '#00B80F', 'others' => 82, 'othersColor' => '#00B80F'],
                                ['name' => 'Yana',        'photo' => 'pfp3.png', 'my' => 30,  'myColor' => '#F47216', 'others' => 57, 'othersColor' => '#FFB700'],
                                ['name' => 'Alexandrina', 'photo' => 'pfp1.png', 'my' => 70,  'myColor' => '#FFB700', 'others' => 57, 'othersColor' => '#FFB700'],
                                ['name' => 'Marcela',     'photo' => 'pfp2.png', 'my' => 100, 'myColor' => '#00B80F', 'others' => 82, 'othersColor' => '#00B80F'],
                                ['name' => 'Yana',        'photo' => 'pfp3.png', 'my' => 30,  'myColor' => '#F47216', 'others' => 57, 'othersColor' => '#FFB700'],
                                ['name' => 'Alexandrina', 'photo' => 'pfp1.png', 'my' => 70,  'myColor' => '#FFB700', 'others' => 57, 'othersColor' => '#FFB700'],
                                ['name' => 'Marcela',     'photo' => 'pfp2.png', 'my' => 100, 'myColor' => '#00B80F', 'others' => 82, 'othersColor' => '#00B80F'],
                                ['name' => 'Yana',        'photo' => 'pfp3.png', 'my' => 30,  'myColor' => '#F47216', 'others' => 57, 'othersColor' => '#FFB700'],
                            ];
                            ?>

                            <style>
                                .custom-scrollbar::-webkit-scrollbar {
                                    width: 12px;
                                }
                                .custom-scrollbar::-webkit-scrollbar-track {
                                    background: #E8E8E8;
                                    border-radius: 99px;
                                }
                                .custom-scrollbar::-webkit-scrollbar-thumb {
                                    background: #5C2D62;
                                    border-radius: 99px;
                                    width: 8px;
                                }
                                @media (max-width: 426px) {
                                    .custom-scrollbar::-webkit-scrollbar {
                                        width: 0;
                                    }
                                }
                            </style>

                            <div class="relative">
                                <div class="flex flex-col gap-2 overflow-y-auto custom-scrollbar max-[426px]:max-h-[400px]" style="max-height: 644px; padding-bottom: 20px;">
                                    <?php $__currentLoopData = $historyEntries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center gap-3 rounded-[8px] max-[426px]:!w-[310px]" style="width: 280px; height: 100px; box-shadow: 0 4px 15px #5C2D6226; padding: 12px;">
                                    <!-- Thumbnail -->
                                    <div class="flex-shrink-0 rounded-[8px] overflow-hidden" style="width: 63px; height: 80px;">
                                        <img src="<?php echo e(asset('images/rating/' . $entry['photo'])); ?>"
                                             class="w-full h-full object-cover" alt="<?php echo e($entry['name']); ?>">
                                    </div>
                                    <!-- Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-[6px]">
                                            <span class="font-bold text-[18px] truncate" style="font-family: 'Poppins', sans-serif; color: #505050;"><?php echo e($entry['name']); ?></span>
                                            <a href="#"
                                               class="text-[11px] font-semibold ml-2 flex-shrink-0 underline"
                                               style="font-family: 'Jakarta Sans', sans-serif; color: #DD3888;">profil</a>
                                        </div>
                                        <!-- My rating bar -->
                                        <div class="flex items-center gap-2 mb-[4px]">
                                            <div class="rounded-full overflow-hidden" style="width: 70px; height: 10px; background-color: #D9D9D9;">
                                                <div class="h-full rounded-full"
                                                     style="width: <?php echo e($entry['my']); ?>%; background-color: <?php echo e($entry['myColor']); ?>; border-radius: 99px;"></div>
                                            </div>
                                            <span class="flex-shrink-0" style="font-family: 'Poppins', sans-serif; font-size: 14px; color: #505050;">
                                                <span style="font-weight: normal;">Ty:</span>
                                                <span style="font-weight: bold;"><?php echo e($entry['my']); ?> %</span>
                                            </span>
                                        </div>
                                        <!-- Others rating bar -->
                                        <div class="flex items-center gap-2">
                                            <div class="rounded-full overflow-hidden" style="width: 70px; height: 10px; background-color: #D9D9D9;">
                                                <div class="h-full rounded-full"
                                                     style="width: <?php echo e($entry['others']); ?>%; background-color: <?php echo e($entry['othersColor']); ?>; border-radius: 99px;"></div>
                                            </div>
                                            <span class="flex-shrink-0" style="font-family: 'Poppins', sans-serif; font-size: 14px; color: #505050;">
                                                <span style="font-weight: normal;">Ostatní:</span>
                                                <span style="font-weight: bold;"><?php echo e($entry['others']); ?> %</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <!-- Fade gradient overlay at bottom -->
                            <div class="absolute bottom-0 left-0 right-0 pointer-events-none" style="height: 80px; background: linear-gradient(to bottom, transparent 0%, white 100%);"></div>
                        </div>

                            <!-- Favorites CTA button -->
                            <div class="mt-6">
                                <button class="rounded-[8px] text-white font-semibold text-[16px] max-[426px]:w-[310px]"
                                        style="width: 311px; height: 60px; font-family: 'Poppins', sans-serif; background-color: #5C2D62;">
                                    Otevřít moje favoritky (42)
                                </button>
                            </div>

                        </div>
                    </div>
                    <!-- /Two-column layout -->

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
<?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/account/ratings_preview.blade.php ENDPATH**/ ?>