<nav class="fixed top-0 left-0 right-0 z-100 bg-transparent rounded-b-3xl transition-all duration-300" id="navbar" x-data="{ mobileMenuOpen: false }" @click.outside="mobileMenuOpen = false">
    <style>
        body.modal-open #navbar { display: none !important; }

        #navbar {
            width: 100%;
            max-width: 100vw;
            overflow-x: clip;
        }

        .navbar-shell {
            width: 1136px;
            max-width: calc(100% - 32px);
            height: 80px;
            margin: 0 auto;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-desktop-grid {
            width: 100%;
            height: 100%;
            grid-template-columns: auto 440px 1fr;
            align-items: center;
            column-gap: 12px;
        }

        .brand-mark {
            font-family: 'Bungee', cursive;
            font-weight: 400;
            font-size: 24px;
            line-height: 1;
            white-space: nowrap;
        }

        #nav-logo {
            padding-right: 97px;
        }

        .brand-mark .brand-main { color: #5C2D62; }
        .brand-mark .brand-si { color: #DD3888; }
        .brand-mark .brand-cz { color: rgba(50, 50, 50, 0.78); }

        .navbar-links {
            width: 440px;
            height: 100%;
            align-self: stretch;
            display: flex;
            align-items: stretch;
            justify-content: flex-start;
            gap: 20px;
            margin-left: 18px;
        }

        .navbar-links .nav-link {
            display: flex;
            align-items: center;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 16px;
            line-height: 1;
            color: #323232;
            text-decoration: none;
            height: 80px;
            padding: 0 10px;
            white-space: nowrap;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            transition: color 140ms ease, background-color 140ms ease;
        }

        .navbar-links .nav-link.active,
        .navbar-links .nav-link:hover {
            color: #DD3888;
            background: #FFFFFF;
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
        }

        @media (max-width: 1023px) {
            #navbar {
                background: #FFFFFF !important;
            }

            .navbar-shell {
                width: 100%;
                max-width: 100%;
                height: 56px;
                padding-left: 25px;
                padding-right: 25px;
            }

            #nav-logo {
                padding-right: 0;
            }

            #nav-logo-mobile {
                display: inline-flex;
                align-items: center;
                margin-left: 0;
                min-width: 0;
                max-width: calc(100% - 44px);
            }

            #nav-logo-mobile .brand-mark {
                font-size: 24px !important;
            }

            #mobile-menu-button {
                flex: 0 0 auto;
                width: 32px;
                min-width: 32px;
            }
        }
    </style>
    <div class="container mx-auto px-0 sm:px-4">
        <div class="navbar-shell">
            <!-- Left Side: Logo + Navigation Links -->
            <div class="navbar-desktop-grid hidden lg:grid">
                <!-- Logo -->
                <a href="<?php echo e(route('profiles.index')); ?>" class="text-xl font-bold text-text-default hover:text-primary-600 transition-colors justify-self-start" id="nav-logo">
                    <span class="brand-mark">
                        <span class="brand-main">ZAŠUKEJ</span><span class="brand-si">SI</span><span class="brand-cz">.CZ</span>
                    </span>
                </a>

                <!-- Navigation Links - Desktop -->
                <div class="navbar-links justify-self-center">
                    <?php
                        $navTranslationKeys = [
                            'home' => 'front.nav.home',
                            'countries' => 'front.nav.countries',
                            'vip' => 'front.nav.vip',
                            'vip-premium' => 'front.nav.vip',
                            'faq' => 'front.nav.faq',
                            'ethics' => 'front.nav.ethics',
                            'etika' => 'front.nav.ethics',
                            'contact' => 'front.nav.contact',
                            'kontakt' => 'front.nav.contact',
                        ];

                        $resolvedNavPages = collect($navPages ?? [])->values();
                        if ($resolvedNavPages->isEmpty()) {
                            $resolvedNavPages = collect([
                                (object) ['id' => 'home', 'slug' => '', 'title' => __('front.nav.home')],
                                (object) ['id' => 'vip', 'slug' => 'vip-premium', 'title' => __('front.nav.vip')],
                                (object) ['id' => 'faq', 'slug' => 'faq', 'title' => __('front.nav.faq')],
                                (object) ['id' => 'ethics', 'slug' => 'etika', 'title' => __('front.nav.ethics')],
                                (object) ['id' => 'contact', 'slug' => 'kontakt', 'title' => __('front.nav.contact')],
                            ]);
                        } else {
                            $resolvedNavPages = $resolvedNavPages->map(function ($page) use ($navTranslationKeys) {
                                $pageId = (string) data_get($page, 'id', '');
                                $pageSlug = trim((string) data_get($page, 'slug', ''), '/');
                                $translationKey = $navTranslationKeys[$pageId] ?? $navTranslationKeys[$pageSlug] ?? null;

                                return (object) [
                                    'id' => $pageId !== '' ? $pageId : ($pageSlug !== '' ? \Illuminate\Support\Str::slug($pageSlug) : 'page'),
                                    'slug' => $pageSlug,
                                    'title' => $translationKey ? __($translationKey) : data_get($page, 'title'),
                                ];
                            })->values();
                        }
                    ?>
                    <?php $__currentLoopData = $resolvedNavPages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $normalizedSlug = trim($page->slug, '/');
                            $isHomeSlug = $normalizedSlug === '';
                            $isActive = $isHomeSlug ? request()->path() === '/' : request()->is($normalizedSlug);
                        ?>
                        <a href="<?php echo e(url('/' . $page->slug)); ?>" class="nav-link <?php echo e($isActive ? 'active' : ''); ?>" id="nav-link-<?php echo e($page->id); ?>">
                            <?php echo e($page->title); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <!-- Right Side: Register, Login, Language Switcher -->
                <div class="navbar-actions justify-self-end flex items-center space-x-3">
                    
                    <?php if(auth()->guard()->check()): ?>
                    <!-- Custom Icon Buttons - Auth Only -->
                    <div class="flex items-center space-x-3">
                        <!-- Notifications Button -->
                        <div class="relative w-[60px] h-[60px]">
                            <div class="w-[60px] h-[60px] border border-[#DD3888] rounded-[8px] flex items-center justify-center">
                                <img src="<?php echo e(asset('images/icons/bell.svg')); ?>" class="w-[26px] h-[26px]" alt="Bell">
                            </div>
                            <div class="absolute -top-1 -right-1 w-[26px] h-[26px] bg-[#00B80F] rounded-full flex items-center justify-center font-bold text-[10px] text-white" style="font-family: 'Poppins', sans-serif;">14</div>
                        </div>
                        
                        <!-- Mail Button -->
                        <a href="<?php echo e(route('messages.index')); ?>" class="w-[60px] h-[60px] border border-[#DD3888] rounded-[8px] flex items-center justify-center relative">
                            <img src="<?php echo e(asset('images/icons/mail.svg')); ?>" class="w-[26px] h-[26px]" alt="Mail">
                            <div class="absolute -top-1 -right-1 w-[26px] h-[26px] bg-[#00B80F] rounded-full flex items-center justify-center font-bold text-[10px] text-white" style="font-family: 'Poppins', sans-serif;">654</div>
                        </a>
                        
                        <!-- User Button -->
                        <?php if (isset($component)) { $__componentOriginal96d248c4b629118b462f1f3c8ddb0bea = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal96d248c4b629118b462f1f3c8ddb0bea = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.account-dropdown','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('account-dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal96d248c4b629118b462f1f3c8ddb0bea)): ?>
<?php $attributes = $__attributesOriginal96d248c4b629118b462f1f3c8ddb0bea; ?>
<?php unset($__attributesOriginal96d248c4b629118b462f1f3c8ddb0bea); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal96d248c4b629118b462f1f3c8ddb0bea)): ?>
<?php $component = $__componentOriginal96d248c4b629118b462f1f3c8ddb0bea; ?>
<?php unset($__componentOriginal96d248c4b629118b462f1f3c8ddb0bea); ?>
<?php endif; ?>
                    </div>
                    <?php else: ?>
                    <!-- Auth Buttons - Guest Only -->
                    <div class="flex items-center space-x-3">
                        <button @click="$dispatch('show-register-modal')" type="button" class="px-6 py-3 bg-[#DD3888] text-white rounded-lg font-semibold hover:opacity-90 transition">
                            <?php echo e(__('front.nav.register')); ?>

                        </button>
                        <button @click="$dispatch('show-login-modal')" type="button" class="px-6 py-3 border-2 border-[#DD3888] bg-white text-[#DD3888] rounded-lg font-semibold hover:bg-[#DD3888] hover:text-white transition">
                            <?php echo e(__('front.nav.login')); ?>

                        </button>
                    </div>
                    <?php endif; ?>

                    <!-- Language Switcher - Desktop Only -->
                    <div class="hidden lg:inline" x-data="{ langOpen: false }" @click.outside="langOpen = false">
                        <div class="language-dropdown relative">
                            <button class="language-dropdown-toggle flex items-center" id="nav-language" @click="langOpen = !langOpen" type="button">
                                <?php if(app()->getLocale() === 'cs'): ?>
                                    <img src="<?php echo e(asset('flags/cs.png')); ?>" alt="Czech" class="w-6 h-6 rounded">
                                <?php else: ?>
                                    <img src="<?php echo e(asset('flags/en.png')); ?>" alt="English" class="w-6 h-6 rounded">
                                <?php endif; ?>
                            </button>
                            
                            <div class="language-dropdown-menu absolute top-full right-0 bg-white p-2 rounded-lg shadow-lg transition-opacity duration-200 z-50"
                                 x-show="langOpen"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95">
                                <a href="<?php echo e(url()->current()); ?>?locale=en" 
                                   class="language-dropdown-item flex items-center gap-2 mb-1"
                                   title="English">
                                    <img src="<?php echo e(asset('flags/en.png')); ?>" alt="English" class="w-6 h-6">
                                </a>
                                <a href="<?php echo e(url()->current()); ?>?locale=cs" 
                                   class="language-dropdown-item flex items-center gap-2"
                                   title="<?php echo e(__('front.nav.czech')); ?>">
                                    <img src="<?php echo e(asset('flags/cs.png')); ?>" alt="Czech" class="w-6 h-6">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex w-full items-center justify-between lg:hidden">
                <a href="<?php echo e(route('profiles.index')); ?>" class="text-xl font-bold text-text-default hover:text-primary-600 transition-colors" id="nav-logo-mobile">
                    <span class="brand-mark" style="font-size:20px;">
                        <span class="brand-main">ZAŠUKEJ</span><span class="brand-si">SI</span><span class="brand-cz">.CZ</span>
                    </span>
                </a>
                <!-- Mobile menu button -->
                <div class="lg:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="flex items-center justify-center text-text-default hover:text-primary-600 focus:outline-none focus:text-primary-600" id="mobile-menu-button" :aria-expanded="mobileMenuOpen.toString()" aria-controls="mobile-menu">
                        <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'burger','xShow' => '!mobileMenuOpen','strokeWidth' => '2','class' => 'h-2 w-6','block' => 'false']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'burger','x-show' => '!mobileMenuOpen','strokeWidth' => '2','class' => 'h-2 w-6','block' => 'false']); ?>
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
                        <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'close','xShow' => 'mobileMenuOpen','strokeWidth' => '2','class' => 'h-6 w-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'close','x-show' => 'mobileMenuOpen','strokeWidth' => '2','class' => 'h-6 w-6']); ?>
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
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="lg:hidden" id="mobile-menu" x-show="mobileMenuOpen" x-cloak x-transition.opacity.duration.180ms>
            <div class="flex flex-wrap p-4 py-5 pt-6 space-y-2 bg-white rounded-2xl">
                
                <?php if(auth()->guard()->check()): ?>
                    <!-- Icon Buttons - Mobile Only -->
                    <div class="w-full flex items-center justify-center gap-3 pb-4 border-b border-gray-200 mb-2">
                        <!-- Notifications Button -->
                        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('notifications-dropdown');

$__html = app('livewire')->mount($__name, $__params, 'lw-2222819059-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
                        
                        <!-- Mail Button -->
                        <a href="<?php echo e(route('messages.index')); ?>" class="btn nav-button bg-gray-50 !px-4 !py-4 !border-1 !text-primary !border-primary relative rounded-lg" title="<?php echo e(__('front.nav.mail')); ?>">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </a>
                        
                    </div>
                <?php endif; ?>
                
                <?php $__currentLoopData = $resolvedNavPages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(url('/' . $page->slug)); ?>" class="nav-link-mobile group">
                        <?php echo e($page->title); ?>

                        <span class="underline"></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('account.dashboard')); ?>" class="nav-link-mobile group">
                        <?php echo e(__('front.nav.accountdashboard')); ?>

                        <span class="underline"></span>
                    </a>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="w-full">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="nav-link-mobile group text-left w-full">
                            <?php echo e(__('front.nav.logout')); ?>

                            <span class="underline"></span>
                        </button>
                    </form>
                <?php else: ?>
                    <!-- Auth Buttons -->
                    <div class="w-full space-y-3 pt-4">
                        <button @click="$dispatch('show-register-modal')" class="w-full btn-primary py-3 text-center">
                            <?php echo e(__('front.nav.register')); ?>

                        </button>
                        <button @click="$dispatch('show-login-modal')" class="w-full btn-light py-3 text-center">
                            <?php echo e(__('front.nav.login')); ?>

                        </button>
                    </div>
                <?php endif; ?>
                
                <!-- Language Switcher -->
                <div class="w-full pt-4 border-t border-gray-300 mt-4">
                    <div class="flex justify-center gap-4">
                        <a href="<?php echo e(url()->current()); ?>?locale=cs" class="flex items-center gap-2 <?php echo e(app()->getLocale() === 'cs' ? 'opacity-100' : 'opacity-50'); ?>">
                            <img src="<?php echo e(asset('flags/cs.png')); ?>" alt="Czech" class="w-8 h-8 rounded-full">
                            <span class="text-sm"><?php echo e(__('front.nav.czech')); ?></span>
                        </a>
                        <a href="<?php echo e(url()->current()); ?>?locale=en" class="flex items-center gap-2 <?php echo e(app()->getLocale() === 'en' ? 'opacity-100' : 'opacity-50'); ?>">
                            <img src="<?php echo e(asset('flags/en.png')); ?>" alt="English" class="w-8 h-8 rounded-full">
                            <span class="text-sm"><?php echo e(__('front.nav.english')); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    // Listen for global modal visibility events and toggle body.modal-open accordingly.
    window.addEventListener('modal-visibility-changed', function(e) {
        try {
            const open = e && e.detail && e.detail.open;
            if (open) {
                document.body.classList.add('modal-open');
            } else {
                // Delay briefly and check if any modal-container is still visible
                setTimeout(function() {
                    const modals = Array.from(document.querySelectorAll('.modal-container'));
                    const anyVisible = modals.some(m => window.getComputedStyle(m).display !== 'none' && m.getBoundingClientRect().height > 0);
                    if (!anyVisible) {
                        document.body.classList.remove('modal-open');
                    }
                }, 10);
            }
        } catch (err) {
            console.error('modal-visibility-changed handler error', err);
        }
    });
</script><?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/components/navbar.blade.php ENDPATH**/ ?>