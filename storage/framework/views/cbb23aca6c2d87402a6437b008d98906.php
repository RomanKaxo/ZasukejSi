

<?php $__env->startSection('title', $profile->display_name . ' - ' . __('front.profiles.detail')); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalaa46d5be215306ec2541719a5f9cbd5d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaa46d5be215306ec2541719a5f9cbd5d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.profile-detail','data' => ['profile' => $profile]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('profile-detail'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['profile' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($profile)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaa46d5be215306ec2541719a5f9cbd5d)): ?>
<?php $attributes = $__attributesOriginalaa46d5be215306ec2541719a5f9cbd5d; ?>
<?php unset($__attributesOriginalaa46d5be215306ec2541719a5f9cbd5d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaa46d5be215306ec2541719a5f9cbd5d)): ?>
<?php $component = $__componentOriginalaa46d5be215306ec2541719a5f9cbd5d; ?>
<?php unset($__componentOriginalaa46d5be215306ec2541719a5f9cbd5d); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/profiles/show.blade.php ENDPATH**/ ?>