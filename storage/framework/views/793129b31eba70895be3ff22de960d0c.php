<div class="inline-block">
    <button
        wire:click="toggleFavorite"
        wire:loading.attr="disabled"
        class="group flex flex-col items-center justify-center gap-1 px-4 py-2 rounded-lg transition-all duration-200 
            <?php echo e($isFavorited 
                ? 'bg-pink-100 text-pink-600 hover:bg-pink-200' 
                : 'bg-gray-100 text-gray-600 hover:bg-pink-50 hover:text-pink-500'); ?>"
    >
        <svg 
            width="38" height="38" viewBox="0 0 19 18" 
            class="transition-transform duration-300 group-hover:scale-110"
            stroke="#DD3888" 
            stroke-width="2" 
            stroke-linecap="round" 
            stroke-linejoin="round"
        >
            <!-- Background stroked path -->
            <path d="M15.3814 10.6667C16.6231 9.45 17.8814 7.99167 17.8814 6.08333C17.8814 4.86776 17.3985 3.70197 16.539 2.84243C15.6795 1.98289 14.5137 1.5 13.2981 1.5C11.8314 1.5 10.7981 1.91667 9.54809 3.16667C8.29809 1.91667 7.26475 1.5 5.79809 1.5C4.58251 1.5 3.41672 1.98289 2.55718 2.84243C1.69764 3.70197 1.21475 4.86776 1.21475 6.08333C1.21475 8 2.46475 9.45833 3.71475 10.6667L9.54809 16.5L15.3814 10.6667Z" fill="none" />
            
            <!-- Foreground filled path -->
            <path d="M15.3814 10.6667C16.6231 9.45 17.8814 7.99167 17.8814 6.08333C17.8814 4.86776 17.3985 3.70197 16.539 2.84243C15.6795 1.98289 14.5137 1.5 13.2981 1.5C11.8314 1.5 10.7981 1.91667 9.54809 3.16667C8.29809 1.91667 7.26475 1.5 5.79809 1.5C4.58251 1.5 3.41672 1.98289 2.55718 2.84243C1.69764 3.70197 1.21475 4.86776 1.21475 6.08333C1.21475 8 2.46475 9.45833 3.71475 10.6667L9.54809 16.5L15.3814 10.6667Z" 
                  fill="#DD3888" 
                  class="transition-opacity duration-150 ease-in-out <?php echo e($isFavorited ? 'opacity-100' : 'opacity-0'); ?>" 
            />
        </svg>
        <span style="font-family:'Plus Jakarta Sans', sans-serif; font-weight:600; font-size:12px; color:#71717A; text-decoration: underline;">
            Uložit
        </span>
    </button>

    <!--[if BLOCK]><![endif]--><?php if($message): ?>
    <div 
        x-data="{ show: true }" 
        x-show="show" 
        x-init="setTimeout(() => show = false, 3000)"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="mt-2 text-sm <?php echo e(str_contains($message, 'error') || str_contains($message, 'Error') ? 'text-red-500' : 'text-green-600'); ?>"
    >
        <?php echo e($message); ?>

    </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/livewire/favorite-button.blade.php ENDPATH**/ ?>