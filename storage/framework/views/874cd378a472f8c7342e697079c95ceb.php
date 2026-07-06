<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['profile', 'gallerySlides']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['profile', 'gallerySlides']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="profile-gallery-wrapper" data-slides="<?php echo e($gallerySlides->toJson()); ?>">
    <?php if (isset($component)) { $__componentOriginal502dff5545f9b3a352a57545aefc8e13 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal502dff5545f9b3a352a57545aefc8e13 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.profile-gallery.desktop','data' => ['profile' => $profile,'gallerySlides' => $gallerySlides]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('profile-gallery.desktop'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['profile' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($profile),'gallery-slides' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($gallerySlides)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal502dff5545f9b3a352a57545aefc8e13)): ?>
<?php $attributes = $__attributesOriginal502dff5545f9b3a352a57545aefc8e13; ?>
<?php unset($__attributesOriginal502dff5545f9b3a352a57545aefc8e13); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal502dff5545f9b3a352a57545aefc8e13)): ?>
<?php $component = $__componentOriginal502dff5545f9b3a352a57545aefc8e13; ?>
<?php unset($__componentOriginal502dff5545f9b3a352a57545aefc8e13); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal217570c45970cb99c05c8cde8c03d436 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal217570c45970cb99c05c8cde8c03d436 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.profile-gallery.mobile','data' => ['profile' => $profile,'gallerySlides' => $gallerySlides]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('profile-gallery.mobile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['profile' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($profile),'gallery-slides' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($gallerySlides)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal217570c45970cb99c05c8cde8c03d436)): ?>
<?php $attributes = $__attributesOriginal217570c45970cb99c05c8cde8c03d436; ?>
<?php unset($__attributesOriginal217570c45970cb99c05c8cde8c03d436); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal217570c45970cb99c05c8cde8c03d436)): ?>
<?php $component = $__componentOriginal217570c45970cb99c05c8cde8c03d436; ?>
<?php unset($__componentOriginal217570c45970cb99c05c8cde8c03d436); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginalab63bc04dbf31bbabd0d3ed33ee3e14d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab63bc04dbf31bbabd0d3ed33ee3e14d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.profile-gallery.lightbox','data' => ['profile' => $profile,'gallerySlides' => $gallerySlides]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('profile-gallery.lightbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['profile' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($profile),'gallery-slides' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($gallerySlides)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab63bc04dbf31bbabd0d3ed33ee3e14d)): ?>
<?php $attributes = $__attributesOriginalab63bc04dbf31bbabd0d3ed33ee3e14d; ?>
<?php unset($__attributesOriginalab63bc04dbf31bbabd0d3ed33ee3e14d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab63bc04dbf31bbabd0d3ed33ee3e14d)): ?>
<?php $component = $__componentOriginalab63bc04dbf31bbabd0d3ed33ee3e14d; ?>
<?php unset($__componentOriginalab63bc04dbf31bbabd0d3ed33ee3e14d); ?>
<?php endif; ?>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/profile-gallery.js')); ?>"></script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/components/profile-gallery/index.blade.php ENDPATH**/ ?>