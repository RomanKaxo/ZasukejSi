<div class="flex flex-col w-[211px] gap-[10px]">
    <div class="flex flex-col w-[211px] h-[290px] gap-[10px]">
        <a href="<?php echo e(request()->routeIs('preview.*') ? route('preview.dashboard') : route('account.dashboard')); ?>" class="w-[210px] h-[50px] rounded-[8px] <?php echo e(request()->routeIs('account.dashboard') || request()->routeIs('preview.dashboard') ? 'bg-[#DD3888] text-white' : 'border border-[#E6E6E6] text-[#505050]'); ?> flex items-center px-4 gap-3 font-medium text-[14px]" style="font-family: 'Poppins', sans-serif;">
            <img src="<?php echo e(asset('images/icons/User.svg')); ?>" class="w-[20px] h-[20px]" alt="User" style="<?php echo e(request()->routeIs('account.dashboard') || request()->routeIs('preview.dashboard') ? 'filter: brightness(0) invert(1);' : 'filter: invert(36%) sepia(87%) saturate(2222%) hue-rotate(309deg) brightness(90%) contrast(92%);'); ?>">
            základní udaje
        </a>
        <a href="<?php echo e(request()->routeIs('preview.*') ? route('preview.photos') : route('account.photos')); ?>" class="w-[210px] h-[50px] rounded-[8px] <?php echo e(request()->routeIs('account.photos') || request()->routeIs('preview.photos') ? 'bg-[#DD3888] text-white' : 'border border-[#E6E6E6] text-[#505050]'); ?> flex items-center px-4 gap-3 font-medium text-[14px]" style="font-family: 'Poppins', sans-serif;">
            <img src="<?php echo e(asset('images/icons/Images.svg')); ?>" class="w-[20px] h-[20px]" alt="Images" style="<?php echo e(request()->routeIs('account.photos') || request()->routeIs('preview.photos') ? 'filter: brightness(0) invert(1);' : 'filter: invert(36%) sepia(87%) saturate(2222%) hue-rotate(309deg) brightness(90%) contrast(92%);'); ?>">
            Fotografie a video
        </a>
        <a href="<?php echo e(request()->routeIs('preview.*') ? route('preview.services') : route('account.services')); ?>" class="w-[210px] h-[50px] rounded-[8px] <?php echo e(request()->routeIs('account.services') || request()->routeIs('preview.services') ? 'bg-[#DD3888] text-white' : 'border border-[#E6E6E6] text-[#505050]'); ?> flex items-center px-4 gap-3 font-medium text-[14px]" style="font-family: 'Poppins', sans-serif;">
            <img src="<?php echo e(asset('images/icons/List.svg')); ?>" class="w-[20px] h-[20px]" alt="List" style="<?php echo e(request()->routeIs('account.services') || request()->routeIs('preview.services') ? 'filter: brightness(0) invert(1);' : 'filter: invert(36%) sepia(87%) saturate(2222%) hue-rotate(309deg) brightness(90%) contrast(92%);'); ?>">
            Moje služby a ceny
        </a>
        <a href="<?php echo e(request()->routeIs('preview.*') ? route('preview.statistics') : route('account.statistics')); ?>" class="w-[210px] h-[50px] rounded-[8px] <?php echo e(request()->routeIs('account.statistics') || request()->routeIs('preview.statistics') ? 'bg-[#DD3888] text-white' : 'border border-[#E6E6E6] text-[#505050]'); ?> flex items-center px-4 gap-3 font-medium text-[14px]" style="font-family: 'Poppins', sans-serif;">
            <img src="<?php echo e(asset('images/icons/BarChart4.svg')); ?>" class="w-[20px] h-[20px]" alt="BarChart" style="<?php echo e(request()->routeIs('account.statistics') || request()->routeIs('preview.statistics') ? 'filter: brightness(0) invert(1);' : 'filter: invert(36%) sepia(87%) saturate(2222%) hue-rotate(309deg) brightness(90%) contrast(92%);'); ?>">
            Statistiky
        </a>
        <div class="w-[210px] h-[50px] rounded-[8px] border border-[#E6E6E6] flex items-center px-4 gap-3 font-medium text-[14px] text-[#A4A4A4]" style="font-family: 'Poppins', sans-serif;">
            <img src="<?php echo e(asset('images/icons/ThumbsUp.svg')); ?>" class="w-[20px] h-[20px]" alt="Thumbsup" style="filter: invert(75%) sepia(0%) saturate(0%) hue-rotate(186deg) brightness(91%) contrast(85%);">
            recenze - již brzy
        </div>
    </div>
    <div class="block w-[210px] h-[464px] mt-4">
        <img src="<?php echo e(asset('images/dvert2.png')); ?>?v=<?php echo e(time()); ?>" class="w-full h-full object-cover rounded-[8px]" alt="Advertisement">
    </div>
</div><?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/components/dashboard/sidebar.blade.php ENDPATH**/ ?>