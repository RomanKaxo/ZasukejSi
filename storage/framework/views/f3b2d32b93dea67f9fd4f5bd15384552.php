<div class="faq-block my-8">
    <?php if(!empty($heading)): ?>
        <h2 class="text-3xl font-bold text-gray-900 mb-6"><?php echo e($heading); ?></h2>
    <?php endif; ?>

    <?php if(!empty($items) && is_array($items)): ?>
        <div class="space-y-4">
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="faq-item border border-gray-200 rounded-lg overflow-hidden">
                    <button 
                        class="faq-question w-full text-left px-6 py-4 bg-white hover:bg-gray-50 transition-colors duration-200 flex items-center justify-between group"
                        onclick="this.closest('.faq-item').classList.toggle('active')"
                        type="button"
                    >
                        <span class="text-lg font-semibold text-gray-900 pr-4"><?php echo e($item['question'] ?? ''); ?></span>
                        <svg 
                            class="faq-icon w-5 h-5 text-pink-500 transition-transform duration-200 flex-shrink-0" 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="faq-answer overflow-hidden transition-all duration-200" style="max-height: 0;">
                        <div class="px-6 py-4 bg-gray-50 text-gray-700 leading-relaxed">
                            <?php echo e($item['answer'] ?? ''); ?>

                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .faq-item.active .faq-answer {
        max-height: 1000px !important;
    }

    .faq-item.active .faq-icon {
        transform: rotate(180deg);
    }

    .faq-answer {
        transition: max-height 0.3s ease-in-out;
    }

    .faq-question:focus {
        outline: 2px solid #ec4899;
        outline-offset: -2px;
    }
</style>
<?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/blocks/faq.blade.php ENDPATH**/ ?>