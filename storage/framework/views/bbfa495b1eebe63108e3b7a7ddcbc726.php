

<?php $__env->startSection('member-content'); ?>
    <!-- Page Title -->
    <div class="mb-4 md:mb-8">
        <h1 class="text-xl md:text-2xl font-bold text-gray-900"><?php echo e(__('front.account.member.settings')); ?></h1>
        <p class="mt-1 md:mt-2 text-sm text-gray-600"><?php echo e(__('front.account.member.settings_description')); ?></p>
    </div>

    <!-- Status Messages -->
    <?php if(session('status') === 'settings-updated'): ?>
        <div class="alert alert-success flex items-center justify-between mb-4 md:mb-6">
            <div class="flex items-center font-semibold">
                <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'bell','class' => 'w-5 h-5 mr-2.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bell','class' => 'w-5 h-5 mr-2.5']); ?>
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
                <span><?php echo e(__('front.account.member.settings_saved')); ?></span>
            </div>
            <button type="button" class="flex items-center ml-2 text-gray-400 hover:text-gray-600"
                onclick="this.parentElement.remove()">
                <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'cross','class' => 'text-green-800 w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cross','class' => 'text-green-800 w-3 h-3']); ?>
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
    <?php endif; ?>

    <?php if(session('status') === 'password-updated'): ?>
        <div class="alert alert-success flex items-center justify-between mb-4 md:mb-6">
            <div class="flex items-center font-semibold">
                <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'bell','class' => 'w-5 h-5 mr-2.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bell','class' => 'w-5 h-5 mr-2.5']); ?>
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
                <span><?php echo e(__('front.account.password.updated')); ?></span>
            </div>
            <button type="button" class="flex items-center ml-2 text-gray-400 hover:text-gray-600"
                onclick="this.parentElement.remove()">
                <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'cross','class' => 'text-green-800 w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cross','class' => 'text-green-800 w-3 h-3']); ?>
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
    <?php endif; ?>

    <!-- User Settings Form -->
    <div class="bg-white rounded-lg border border-gray-200 p-4 md:p-6">
        <form method="POST" action="<?php echo e(route('account.member.settings.update')); ?>" class="space-y-4 md:space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PATCH'); ?>

            <!-- Name -->
            <div>
                <label for="name"
                    class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('front.profiles.form.name')); ?> *</label>
                <input type="text" id="name" name="name" value="<?php echo e(old('name', $user->name)); ?>"
                    class="input-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Email (read-only display) -->
            <div>
                <label for="email_display"
                    class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('front.profiles.form.email')); ?> *</label>
                <input type="email" id="email_display" value="<?php echo e($user->email); ?>" disabled
                    class="input-control bg-gray-50 text-gray-500 cursor-not-allowed">
            </div>

            <!-- Phone -->
            <div>
                <label for="phone"
                    class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('front.profiles.form.phone')); ?></label>
                <input type="tel" id="phone" name="phone" value="<?php echo e(old('phone', $user->phone)); ?>"
                    class="input-control <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Email Change Section -->
            <div x-data="{ expanded: <?php echo e($errors->has('new_email') || $errors->has('email_change_password') ? 'true' : 'false'); ?> }">
                <button type="button" @click="expanded = !expanded"
                    class="w-full flex items-center justify-between py-3 px-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                            </path>
                        </svg>
                        <?php echo e(__('front.profiles.form.change_email')); ?>

                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': expanded }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div x-show="expanded" x-collapse x-cloak class="mt-4 space-y-4 px-1">
                    <!-- New Email -->
                    <div>
                        <label for="new_email"
                            class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('front.profiles.form.new_email')); ?></label>
                        <input type="email" id="new_email" name="new_email" value="<?php echo e(old('new_email')); ?>"
                            class="input-control <?php $__errorArgs = ['new_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            placeholder="<?php echo e(__('front.profiles.form.email_placeholder')); ?>">
                        <?php $__errorArgs = ['new_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Password Confirmation for Email Change -->
                    <div>
                        <label for="email_change_password"
                            class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('front.profiles.form.password_to_confirm')); ?></label>
                        <input type="password" id="email_change_password" name="email_change_password"
                            class="input-control <?php $__errorArgs = ['email_change_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            placeholder="••••••••">
                        <?php $__errorArgs = ['email_change_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <p class="mt-2 text-xs text-gray-500"><?php echo e(__('front.profiles.form.email_change_notice')); ?></p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-4">
                        <button type="submit" class="btn-primary btn-small justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <?php echo e(__('front.profiles.form.savechanges')); ?>

                        </button>
                    </div>
                </div>
            </div>


            <!-- Password Change Section -->
            <div class="my-6">
                <div x-data="{ expanded: <?php echo e($errors->has('current_password') || $errors->has('password') ? 'true' : 'false'); ?> }">
                    <button type="button" @click="expanded = !expanded"
                        class="w-full flex items-center justify-between py-3 px-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                        <span class="flex items-center gap-2 text-sm font-medium text-gray-700">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                                </path>
                            </svg>
                            <?php echo e(__('front.profiles.form.change_password')); ?>

                        </span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                            :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>

                    <div x-show="expanded" x-collapse x-cloak class="mt-4">
                        <p class="text-sm text-gray-600 mb-6"><?php echo e(__('front.account.password.description')); ?></p>

                        <form method="POST" action="<?php echo e(route('account.member.password.update')); ?>" class="space-y-4">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>

                            <!-- Current Password -->
                            <div>
                                <label for="current_password"
                                    class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('front.profiles.form.current_password')); ?></label>
                                <input type="password" id="current_password" name="current_password"
                                    class="input-control <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    placeholder="••••••••" required>
                                <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- New Password -->
                                <div>
                                    <label for="password"
                                        class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('front.profiles.form.new_password')); ?></label>
                                    <input type="password" id="password" name="password"
                                        class="input-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        placeholder="••••••••" required>
                                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <label for="password_confirmation"
                                        class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('front.profiles.form.confirm_password')); ?></label>
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                        class="input-control" placeholder="••••••••" required>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end pt-4">
                                <button type="submit" class="btn-primary btn-small justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <?php echo e(__('front.profiles.form.savechanges')); ?>

                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="btn-primary btn-small justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <?php echo e(__('front.profiles.form.savechanges')); ?>

                </button>
            </div>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.member', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/member/dashboard.blade.php ENDPATH**/ ?>