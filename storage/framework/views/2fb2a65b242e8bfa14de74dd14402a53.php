<div 
    x-data="{ open: false }" 
    @click.away="open = false" 
    class="relative"
>
    <!-- User Button -->
    <button @click="open = !open" class="w-[60px] h-[60px] bg-[#DD3888] rounded-[8px] flex items-center justify-center">
        <img src="<?php echo e(asset('images/icons/User.svg')); ?>" class="w-[26px] h-[26px]" alt="User">
    </button>

    <!-- Dropdown Menu -->
    <div 
        x-show="open" 
        x-cloak
        class="absolute right-0 mt-2 shadow-lg z-50 flex flex-col items-center pt-6"
        style="width: 270px; height: 372px; border-radius: 8px; background-color: #DD3888;"
    >
        <nav class="w-full px-5">
            <ul class="space-y-2">
                <?php
                    $links = [
                        ['url' => route('account.dashboard'), 'label' => 'Basic Information', 'icon' => 'User.svg'],
                        ['url' => route('account.photos'), 'label' => 'Photos & Videos', 'icon' => 'Images.svg'],
                        ['url' => route('account.services'), 'label' => 'My Services & Prices', 'icon' => 'List.svg'],
                        ['url' => route('account.statistics'), 'label' => 'Statistics', 'icon' => 'BarChart4.svg'],
                    ];
                ?>

                <?php $__currentLoopData = $links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        <a href="<?php echo e($link['url']); ?>" 
                           class="flex items-center px-4 gap-3 transition-colors duration-200"
                           style="width: 230px; height: 50px; border-radius: 8px; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 600; color: #FFFFFF; text-decoration: none; background-color: transparent;"
                           onmouseover="this.style.backgroundColor='#5C2D62'; this.style.color='#FFFFFF';"
                           onmouseout="this.style.backgroundColor='transparent'; this.style.color='#FFFFFF';">
                            <img src="<?php echo e(asset('images/icons/' . $link['icon'])); ?>" class="w-[20px] h-[20px]" alt="<?php echo e($link['label']); ?>" style="filter: brightness(0) invert(1);">
                            <?php echo e($link['label']); ?>

                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </nav>
    </div>
</div>
<?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/components/account-dropdown.blade.php ENDPATH**/ ?>