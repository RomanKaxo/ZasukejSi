<!--[if BLOCK]><![endif]--><?php if($paginator->hasPages()): ?>
    <div class="flex items-center justify-center gap-3 mt-16 md:mt-20 lg:mt-24">
        
        <!--[if BLOCK]><![endif]--><?php if($paginator->onFirstPage()): ?>
            <button type="button" disabled aria-disabled="true" class="flex items-center justify-center" style="width:45px;height:45px;border-radius:8px;background:#F2F2F2;">
                <span class="inline-block text-[#5C2D62]">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 12.6668V3.3335L3.33335 8.00016L10.6667 12.6668Z"/></svg>
                </span>
            </button>
        <?php else: ?>
            <button type="button" wire:click="previousPage" wire:loading.attr="disabled" class="flex items-center justify-center" style="width:45px;height:45px;border-radius:8px;background:#5C2D62;">
                <span class="inline-block text-white">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 12.6668V3.3335L3.33335 8.00016L10.6667 12.6668Z"/></svg>
                </span>
            </button>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        
        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            
            <!--[if BLOCK]><![endif]--><?php if(is_string($element)): ?>
                <span class="flex items-center justify-center font-semibold" style="width:45px;height:45px;color:#505050;"><?php echo e($element); ?></span>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            
                <!--[if BLOCK]><![endif]--><?php if(is_array($element)): ?>
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <!--[if BLOCK]><![endif]--><?php if($page == $paginator->currentPage()): ?>
                        <a href="<?php echo e($url); ?>" aria-current="page" class="flex items-center justify-center font-semibold" style="width:45px;height:45px;border-radius:8px;background:#DD3888;color:#FFFFFF;font-family: 'Poppins', sans-serif; font-size:14px;"><?php echo e($page); ?></a>
                    <?php else: ?>
                        <a href="<?php echo e($url); ?>" wire:click.prevent="gotoPage(<?php echo e($page); ?>)" class="flex items-center justify-center font-semibold" style="width:45px;height:45px;border-radius:8px;background:transparent;color:#505050;font-family: 'Poppins', sans-serif; font-size:14px;"><?php echo e($page); ?></a>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

        
        <!--[if BLOCK]><![endif]--><?php if($paginator->hasMorePages()): ?>
            <button type="button" wire:click="nextPage" wire:loading.attr="disabled" class="flex items-center justify-center" style="width:45px;height:45px;border-radius:8px;background:#5C2D62;">
                <span class="inline-block transform rotate-180 text-white">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 12.6668V3.3335L3.33335 8.00016L10.6667 12.6668Z"/></svg>
                </span>
            </button>
        <?php else: ?>
            <button type="button" disabled aria-disabled="true" class="flex items-center justify-center" style="width:45px;height:45px;border-radius:8px;background:#F2F2F2;">
                <span class="inline-block transform rotate-180 text-[#5C2D62]">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 12.6668V3.3335L3.33335 8.00016L10.6667 12.6668Z"/></svg>
                </span>
            </button>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/livewire/pagination-links.blade.php ENDPATH**/ ?>