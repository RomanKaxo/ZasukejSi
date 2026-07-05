<footer x-data class="site-footer py-8 md:py-12 pt-12 md:pt-20">
    <div class="site-footer-container container mx-auto px-4">
        <!-- Logo -->
        <div class="text-center mb-6 md:mb-8">
            <h2 class="text-xl md:text-2xl font-extrabold">
                <span style="color:#5C2D62">ZAŠUKEJ</span><span style="color:#DD3888">SI</span><span style="color:#8C8C8C;opacity:0.78">.CZ</span>
            </h2>
        </div>

        <!-- Footer Content -->
        <div class="footer-main mx-auto h-[275px] flex items-center justify-between mb-6 md:mb-8">
            <!-- Left: Registration Button -->
            <div class="flex-shrink-0">
                <?php if(auth()->guard()->guest()): ?>
                <button @click="$dispatch('show-register-modal')" class="btn-primary footer-register px-6 md:px-8 py-2.5 md:py-3 rounded-lg font-semibold text-sm md:text-base">
                    <?php echo e(__('front.footer.registration')); ?>

                </button>
                <?php endif; ?>
            </div>

            <!-- Center: Static Links (3 columns, left-aligned vertically stacked) -->
            <div class="footer-links">
                <div class="footer-col">
                    <a href="#" class="footer-link">Časté dotazy</a>
                    <a href="#" class="footer-link">Kontakt</a>
                </div>
                <div class="footer-col">
                    <a href="#" class="footer-link">Ochrana osobních údajů</a>
                    <a href="#" class="footer-link">Etika a bezpečnost</a>
                </div>
                <div class="footer-col">
                    <a href="#" class="footer-link">VIP účet pro dívky</a>
                    <a href="#" class="footer-link">Prémium účet pro pány</a>
                </div>
            </div>

            <!-- Right: Security box -->
            <div class="hidden lg:block flex-shrink-0">
                <div class="footer-security flex items-center" role="note">
                    <img src="<?php echo e(asset('images/icons/lock.svg')); ?>" alt="lock" width="25" height="25" />
                    <div class="ml-3 footer-security-text">Jsme 100% diskrétní platforma s<br>profesionální ochranou osobních údajů</div>
                </div>
            </div>
        </div>

        <div class="footer-mobile-languages lg:hidden">
            <a href="<?php echo e(url()->current()); ?>?locale=cs" class="footer-lang-card <?php echo e(app()->getLocale() === 'cs' ? 'is-active' : ''); ?>">
                <img src="<?php echo e(asset('flags/cs.png')); ?>" alt="Czech" class="footer-lang-flag">
                <span class="footer-lang-label"><?php echo e(__('front.nav.czech')); ?></span>
            </a>
            <a href="<?php echo e(url()->current()); ?>?locale=en" class="footer-lang-card <?php echo e(app()->getLocale() === 'en' ? 'is-active' : ''); ?>">
                <img src="<?php echo e(asset('flags/en.png')); ?>" alt="English" class="footer-lang-flag">
                <span class="footer-lang-label"><?php echo e(__('front.nav.english')); ?></span>
            </a>
        </div>

        <!-- Security Info -->
        <div class="footer-meta pt-6 md:pt-8 border-t md:border-t-0">
            <!-- Mobile: Stacked Layout -->
            <div class="footer-mobile-meta flex flex-col items-center gap-2 lg:hidden">
                <div class="footer-security footer-security-mobile flex items-center" role="note">
                    <img src="<?php echo e(asset('images/icons/lock.svg')); ?>" alt="lock" width="27" height="30" />
                    <div class="ml-3 footer-security-text">Jsme 100% diskrétní platforma s<br>profesionální ochranou osobních údajů</div>
                </div>

                <div class="footer-eco-card" role="note">
                    <div class="footer-eco-icon-wrap">
                        <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'eco','class' => 'footer-eco-icon']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'eco','class' => 'footer-eco-icon']); ?>
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
                    </div>
                    <div class="footer-eco-copy">
                        <span class="footer-eco-title"><?php echo e(__('front.footer.ecological')); ?></span>
                        <span class="footer-eco-sub"><?php echo e(ltrim(__('front.footer.verification'), '- ')); ?></span>
                    </div>
                </div>

                <!-- Copyright -->
                <div class="footer-copyright footer-copyright-mobile text-center leading-relaxed pt-5 px-4">
                    <?php echo e(__('front.footer.copyright')); ?>

                </div>
            </div>

            <!-- Desktop: Horizontal Layout -->
            <div class="hidden lg:flex footer-meta-row">
                <div class="footer-meta-copy">
                    <span class="footer-eco-title"><?php echo e(__('front.footer.ecological')); ?></span>
                    <span class="footer-eco-sub"><?php echo e(__('front.footer.verification')); ?></span>
                </div>

                <hr class="footer-sep">

                <div class="footer-copyright">
                    <?php echo e(__('front.footer.copyright')); ?>

                </div>
            </div>
        </div>
    </div>
</footer><?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/components/footer.blade.php ENDPATH**/ ?>