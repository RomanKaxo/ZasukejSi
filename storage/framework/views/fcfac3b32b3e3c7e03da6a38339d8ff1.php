<div x-data>

<button 
    @click="$store.memberSidebar.toggle()"
    class="fixed top-20 left-4 z-50 md:hidden bg-primary text-white p-3 rounded-lg shadow-lg hover:bg-primary-600 transition-colors"
    aria-label="Toggle menu"
>
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
</button>


<div 
    x-show="$store.memberSidebar.isOpen" 
    @click="$store.memberSidebar.close()"
    x-transition.opacity
    x-cloak
    class="fixed inset-0 bg-black/50 z-40 md:hidden"
></div>


<aside 
    class="w-full h-full md:w-80 md:relative fixed top-0 left-0 z-40 bg-white transition-transform duration-300 md:translate-x-0 overflow-y-auto pt-28 md:pt-0 md:mt-10"
    :class="$store.memberSidebar.isOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
>
    <!-- Navigation Menu -->
    <nav class="p-6">
        <ul class="space-y-3">
            
            <li>
                <a href="<?php echo e(route('account.member.dashboard')); ?>" 
                   class="nav-button <?php echo e(request()->routeIs('account.member.dashboard') ? 'active' : ''); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <?php echo e(__('front.account.member.settings')); ?>

                </a>
            </li>

            
            <li>
                <a href="<?php echo e(route('account.member.ratings')); ?>" 
                   class="nav-button <?php echo e(request()->routeIs('account.member.ratings') ? 'active' : ''); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    <?php echo e(__('front.account.member.ratings')); ?>

                </a>
            </li>

            
            <li>
                <a href="<?php echo e(route('account.member.favorites')); ?>" 
                   class="nav-button <?php echo e(request()->routeIs('account.member.favorites') ? 'active' : ''); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <?php echo e(__('front.account.member.favorites')); ?>

                </a>
            </li>

            
            <li>
                <a href="<?php echo e(route('account.member.girls-of-month')); ?>" 
                   class="nav-button <?php echo e(request()->routeIs('account.member.girls-of-month') ? 'active' : ''); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    <?php echo e(__('front.account.member.girls_of_month')); ?>

                </a>
            </li>

            

            
        </ul>

        <!-- Advert for VIP (hidden on mobile) -->
        <?php if (! (request()->routeIs('preview.*'))): ?>
        <div class="mt-6 relative hidden md:block">
            <!-- VIP Image -->
            <img src="<?php echo e(asset('images/vip-advert.png')); ?>" alt="VIP" class="w-full rounded-t-xl">
            
            <!-- Golden Background Section -->
            <div class="relative p-5 rounded-b-xl border-b-3 border-gold-light" style="background: linear-gradient(180deg, #F5E4B8 0%, #FFFFFF 100%);">
            <!-- Gold Star - Absolutely Positioned -->
            <img src="<?php echo e(asset('images/gold-star.png')); ?>" alt="Gold Star" class="absolute -top-10 left-1/2 -translate-x-1/2 w-16 h-16">
            
            <h3 class="text-3xl py-3font-bold text-gold mb-2 text-center"><?php echo e(__('front.account.sidebar.vip_title')); ?></h3>
            <a href="#" class="btn-gold w-full text-center">
                <?php echo e(__('front.account.sidebar.vip_button')); ?>

            </a>
            </div>
        </div>
        <?php endif; ?>
    </nav>
</aside>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('memberSidebar', {
            isOpen: false,
            toggle() {
                this.isOpen = !this.isOpen;
            },
            close() {
                this.isOpen = false;
            }
        });
    });
</script>
<?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/components/member-sidebar.blade.php ENDPATH**/ ?>