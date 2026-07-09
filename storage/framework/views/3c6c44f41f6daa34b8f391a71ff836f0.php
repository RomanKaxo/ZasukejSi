

<?php $__env->startSection('content'); ?>
<!-- Add top padding to account for fixed navbar -->
<div class="container mx-auto pt-24 md:pt-38 min-h-screen px-4 md:px-0">
    <div class="flex flex-col md:flex-row">
        <!-- Sidebar -->
        <?php if (isset($component)) { $__componentOriginald1a102d8a3a3a73cead74b21758f1d23 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald1a102d8a3a3a73cead74b21758f1d23 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.member-sidebar','data' => ['activeItem' => $activeItem ?? 'dashboard']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('member-sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['activeItem' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activeItem ?? 'dashboard')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald1a102d8a3a3a73cead74b21758f1d23)): ?>
<?php $attributes = $__attributesOriginald1a102d8a3a3a73cead74b21758f1d23; ?>
<?php unset($__attributesOriginald1a102d8a3a3a73cead74b21758f1d23); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald1a102d8a3a3a73cead74b21758f1d23)): ?>
<?php $component = $__componentOriginald1a102d8a3a3a73cead74b21758f1d23; ?>
<?php unset($__componentOriginald1a102d8a3a3a73cead74b21758f1d23); ?>
<?php endif; ?>
        
        <!-- Main Content -->
        <main class="flex-1 pt-10 px-0 md:px-8 md:pt-0">
          
                <?php echo $__env->yieldContent('member-content'); ?>
      
        </main>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/layouts/member.blade.php ENDPATH**/ ?>