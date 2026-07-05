<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['profile', 'photoStatusLabel', 'messageRouteAvailable', 'registerRouteAvailable']));

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

foreach (array_filter((['profile', 'photoStatusLabel', 'messageRouteAvailable', 'registerRouteAvailable']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<aside class="vip-profile-panel">
    <div class="vip-profile-status-bar" style="display:flex; justify-content:space-between; align-items:center;">
        <div style="display:flex; gap:6px; align-items:center;">
            <span class="vip-profile-status-pill vip-profile-status-pill--primary">
                <img src="<?php echo e(asset('images/icons/star.svg')); ?>" alt="star" style="width: 18px; height: 18px; margin-right: 6px;">
                VIP PROFIL
            </span>
            <span class="vip-profile-status-pill vip-profile-status-pill--verification">
                <img src="<?php echo e(asset('images/icons/CameraOff.svg')); ?>" alt="camera-off" style="width: 18px; height: 18px; margin-right: 4px;">
                <?php echo e($photoStatusLabel); ?>

            </span>
        </div>
        
        <div class="vip-profile-favorite-mobile" style="display:none;">
            <?php if(auth()->guard()->check()): ?>
                <?php if(auth()->user()->isMale()): ?>
                    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('favorite-button', ['profile' => $profile]);

$__html = app('livewire')->mount($__name, $__params, 'favorite-'.$profile->id, $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
                <?php else: ?>
                    <button type="button" class="vip-profile-static-favorite">
                        <img src="<?php echo e(asset('images/icons/heart.svg')); ?>" alt="" aria-hidden="true">
                    </button>
                <?php endif; ?>
            <?php else: ?>
                <button type="button" class="vip-profile-static-favorite">
                    <img src="<?php echo e(asset('images/icons/heart.svg')); ?>" alt="" aria-hidden="true">
                </button>
            <?php endif; ?>
        </div>
    </div>

    <h1 class="vip-profile-name"><?php echo e($profile->display_name ?? 'Alexandrina'); ?></h1>

    <div class="vip-profile-rating-summary">
        <strong><?php echo e(__('front.profiles.list.rating')); ?></strong>
        <span class="vip-profile-rating-icons">
            <?php if($profile->getTotalRatings() > 0): ?>
                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="color:#FFC107;">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <span><?php echo e(number_format($profile->getAverageRating(), 1)); ?></span>
            <?php else: ?>
                <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'lock','class' => 'inline-block','style' => 'width:18px;height:18px;color:#FF4DA6;']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'lock','class' => 'inline-block','style' => 'width:18px;height:18px;color:#FF4DA6;']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd)): ?>
<?php $attributes = $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd; ?>
<?php unset($__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd)): ?>
<?php $component = $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd; ?>
<?php unset($__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd); ?>
<?php endif; ?>
            <?php endif; ?>
        </span>
    </div>

    <div class="vip-profile-meta-location">
        <img src="<?php echo e(asset('images/icons/location.svg')); ?>" alt="" aria-hidden="true">
        <span><?php echo e($profile->city ?? 'Jihomoravský kraj'); ?></span>
    </div>

    <div class="vip-profile-meta-table">
        <div class="vip-profile-meta-row">
            <span class="vip-profile-meta-label">Věk</span>
            <span class="vip-profile-meta-value"><?php echo e($profile->age ?? '19'); ?> let</span>
        </div>
        <div class="vip-profile-meta-row">
            <span class="vip-profile-meta-label">Váha</span>
            <span class="vip-profile-meta-value">
                <?php echo e(($profile->weight ?? '57') . ' kg'); ?>

            </span>
        </div>
        <div class="vip-profile-meta-row">
            <span class="vip-profile-meta-label">Výška</span>
            <span class="vip-profile-meta-value">
                <?php echo e(($profile->height ?? '168') . ' cm'); ?>

            </span>
        </div>
        <div class="vip-profile-meta-row">
            <span class="vip-profile-meta-label">Prsa</span>
            <span class="vip-profile-meta-value"><?php echo e($profile->bust_size ?? 'C'); ?></span>
        </div>
        <div class="vip-profile-meta-row">
            <span class="vip-profile-meta-label">Jazyky</span>
            <span class="vip-profile-meta-value"><?php echo e($profile->languages ?? 'Česky, Rusky, Anglicky'); ?></span>
        </div>
    </div>

    <div class="vip-profile-flags">
        <div class="vip-profile-flag vip-profile-flag--incall">
            <span class="vip-profile-flag-status">
                <img src="<?php echo e(asset('images/icons/CircleCheck.svg')); ?>" alt="" aria-hidden="true">
            </span>
            <span>InCall</span>
        </div>
        <div class="vip-profile-flag vip-profile-flag--outcall">
            <span class="vip-profile-flag-status">
                <img src="<?php echo e(asset('images/icons/CircleX.svg')); ?>" alt="" aria-hidden="true">
            </span>
            <span>OutCall</span>
        </div>
    </div>

    <?php if(auth()->guard()->check()): ?>
        <?php if($profile->user_id && $profile->user_id !== auth()->id() && $messageRouteAvailable): ?>
            <a href="<?php echo e(route('messages.show', $profile->user)); ?>" class="vip-profile-message">
                Poslat zprávu
                <img src="<?php echo e(asset('images/icons/message.svg')); ?>" alt="" aria-hidden="true" class="h-4 w-4 brightness-0 invert">
            </a>
        <?php else: ?>
            <span class="vip-profile-message" style="opacity:.6;pointer-events:none;">Poslat zprávu</span>
        <?php endif; ?>
    <?php else: ?>
        <a href="<?php echo e($registerRouteAvailable ? route('register') : route('profiles.index')); ?>" class="vip-profile-message">
            Poslat zprávu
            <img src="<?php echo e(asset('images/icons/message.svg')); ?>" alt="" aria-hidden="true" class="h-4 w-4 brightness-0 invert">
        </a>
    <?php endif; ?>

    <div class="vip-profile-contacts">
        <a class="vip-profile-contact-circle vip-profile-contact-circle--whatsapp" href="https://wa.me/420737155457" target="_blank" rel="noreferrer" title="WhatsApp">
            <img src="<?php echo e(asset('images/icons/whatsapp.svg')); ?>" alt="whatsapp" style="width: 20px; height: 20px;">
        </a>
        <a class="vip-profile-contact-circle vip-profile-contact-circle--telegram" href="https://t.me/alexandraprofil" target="_blank" rel="noreferrer" title="Telegram">
            <img src="<?php echo e(asset('images/icons/telegram.svg')); ?>" alt="telegram" style="width: 20px; height: 20px;">
        </a>
        <a href="tel:+420737155457" class="vip-profile-phone">
            <span>+420 737 155 457</span>
        </a>
    </div>
</aside><?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/components/profile/hero-panel.blade.php ENDPATH**/ ?>