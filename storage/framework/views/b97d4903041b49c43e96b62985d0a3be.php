

<?php $__env->startSection('member-content'); ?>
<!-- Page Title -->
<div class="mb-4 md:mb-8">
    <h1 class="text-xl md:text-2xl font-bold text-gray-900"><?php echo e(__('front.account.member.favorites')); ?></h1>
    <p class="mt-1 md:mt-2 text-sm text-gray-600"><?php echo e(__('front.account.member.favorites_description')); ?></p>
</div>

<!-- Favorites Grid -->
<?php if($favorites->count() > 0): ?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
    <?php $__currentLoopData = $favorites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $profile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="relative">
        <!-- Profile Card Component -->
        <?php if (isset($component)) { $__componentOriginal2299f79f212ad7b2f1b6f23328beba2f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2299f79f212ad7b2f1b6f23328beba2f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.profile-card','data' => ['profile' => $profile]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('profile-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['profile' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($profile)]); ?>
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
        
        <!-- Remove from Favorites Button (Overlay) -->
        <div class="absolute top-3 right-3 z-30">
            <form action="<?php echo e(route('account.member.favorites.remove', $profile)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" 
                    class="p-2 rounded-lg bg-pink-100 text-pink-600 hover:bg-pink-200 transition-colors duration-200 shadow-sm"
                    title="<?php echo e(__('front.favorites.remove')); ?>">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<!-- Pagination -->
<?php if($favorites->hasPages()): ?>
<div class="mt-8">
    <?php echo e($favorites->links()); ?>

</div>
<?php endif; ?>

<?php else: ?>
<!-- Empty State -->
<div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
    </svg>
    <h3 class="text-lg font-medium text-gray-900 mb-2"><?php echo e(__('front.favorites.no_favorites')); ?></h3>
    <p class="text-gray-500 mb-6"><?php echo e(__('front.favorites.no_favorites_description')); ?></p>
    <a href="<?php echo e(route('profiles.index')); ?>" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <?php echo e(__('front.favorites.browse_profiles')); ?>

    </a>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.member', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/member/favorites.blade.php ENDPATH**/ ?>