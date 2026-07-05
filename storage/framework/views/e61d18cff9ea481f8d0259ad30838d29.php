<div x-data="{ show: false, closing: false }"
    x-on:show-login-modal.window="console.log('Login modal event received'); show = true; $wire.show()"
    x-on:hide-login-modal.window="show = false; closing = false"
    x-init="console.log('Login modal Alpine initialized'); $watch('show', v => { document.body.style.overflow = v ? 'hidden' : ''; if (v) { document.body.classList.add('modal-open') } else { document.body.classList.remove('modal-open') } ; window.dispatchEvent(new CustomEvent('modal-visibility-changed', { detail: { open: v } })); })"
    x-on:keydown.escape.window="if (show) { closing = true; show = false; $wire.hide() }">
    <!-- Login Modal -->
    <div x-show="show"
        x-transition.opacity.duration.300ms
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-1 sm:p-2 md:p-4">

        <style>
            /* Use max-width approach: form fills available width but is capped */
            .form-container {
                width: 100%;
                max-width: 520px;
                height: 353px;
            }
            /* default modal size set in CSS so media queries can reliably override */
            .modal-container { width:600px !important; max-width:600px !important; height:810px !important; border-radius:24px; overflow:hidden; background:white; box-sizing:border-box; box-shadow:0 8px 30px rgba(0,0,0,0.12); transform:translateY(30px); }
            @media (max-width: 480px) {
                .form-container { max-width: 280px; height: 337px; }
                .modal-container { width: auto !important; max-width:95% !important; }
            }
            @media (max-width: 480px) {
                .modal-container { width: auto !important; max-width:95% !important; }
            }
            /* form fields and primary/outline buttons fill available width but are capped */
            .form-field { width: 100%; max-width: 460px; height:50px; }
            .modal-btn-primary, .modal-btn-outline { width: 100%; max-width: 460px; }
            .modal-btn-primary { height:60px; }

            @media (max-width: 480px) {
                .form-container { width:280px !important; max-width:280px !important; height:337px !important; }
                .form-field { max-width: 240px !important; }
                .modal-btn-primary, .modal-btn-outline { max-width: 240px !important; height:50px !important; }
                /* register button slightly wider */
                .modal-btn-outline.register-btn { max-width:245px !important; }
                .modal-container { width: auto !important; max-width:95% !important; }
                .modal-title, .modal-subtitle { font-size:28px !important; line-height:1.05 !important; }
                .modal-close-btn { top:20px !important; right:20px !important; }
                .register-cta { margin-top:55px !important; }
                .modal-btn-primary { margin-top:18px !important; }
                .modal-subtitle { margin-bottom:32px !important; }
            }
            /* Exact mobile modal size (320x711) when screen is small */
            /* Force exact mobile modal size for typical mobile widths */
            @media (max-width: 480px) {
                .modal-container { width:320px !important; max-width:320px !important; height:711px !important; }
            }
        </style>

        <!-- Modal Backdrop -->
        <div class="modal-backdrop"
            @click="closing = true; show = false; $wire.hide()"></div>

        <!-- Modal Content -->
        <div class="modal-container">
            <!-- Close Button -->
            <button @click="closing = true; show = false; $wire.hide()"
                class="modal-close-btn" style="width:35px;height:35px;border-radius:50%;display:flex;align-items:center;justify-content:center;position:absolute;right:35px;top:35px;background:#DD3888;border:none">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Content inner (shift down) -->
            <div style="padding-top:85px;">
            <!-- Header -->
            <div class="modal-header" style="margin-top:-30px;">
                <h1 class="modal-title"><?php echo e(__('auth.login.title')); ?></h1>
                <h2 class="modal-subtitle"><?php echo e(__('auth.login.subtitle')); ?></h2>
            </div>

            <!-- Form -->
            <form wire:submit="authenticate" class="form-container" style="width:520px;height:353px;background:#F2F2F2;border-radius:15px;padding:24px;box-sizing:border-box;margin:0 auto;">
                <!-- Login Error -->
                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['login'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="form-alert">
                    <?php echo e($message); ?>

                </div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                <!-- Username Field -->
                <div>
                    <label class="form-label" style="font-family:'Poppins',sans-serif;font-weight:400;font-size:13px;color:#505050">Vaše uživatelské jméno</label>
                    <input wire:model="email"
                        type="email"
                        required
                        class="form-field <?php echo e($errors->has('email') ? 'form-field-error' : ''); ?>"
                        style="background:#FFFFFF;border-radius:8px;border:2px solid #E6E6E6;padding:12px 14px;box-sizing:border-box;">
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="form-error"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>

                <!-- Password Field -->
                <div>
                    <label class="form-label" style="font-family:'Poppins',sans-serif;font-weight:400;font-size:13px;color:#505050"><?php echo e(__('auth.login.password_label')); ?></label>
                    <input wire:model="password"
                        type="password"
                        required
                        class="form-field <?php echo e($errors->has('password') ? 'form-field-error' : ''); ?>"
                        style="background:#FFFFFF;border-radius:8px;border:2px solid #E6E6E6;padding:12px 14px;box-sizing:border-box;">
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="form-error"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>

                <!-- Forgot Password -->
                <div class="text-left">
                    <button type="button" 
                        @click="show = false; $wire.hide(); $dispatch('show-reset-modal')"
                        class="text-xs sm:text-sm text-gray-500 hover:text-gray-700"
                        style="font-family:'Poppins',sans-serif;font-weight:400;font-size:11px;text-decoration:underline;color:#5C5C5C;margin-left:8px">
                        <?php echo e(__('auth.login.forgot_password')); ?>

                    </button>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="authenticate"
                    x-bind:disabled="closing"
                    class="modal-btn-primary"
                    style="border-radius:8px;background:#DD3888;border:none;display:flex;align-items:center;justify-content:center;margin-top:12px;">
                    <span wire:loading.remove wire:target="authenticate" style="font-family:'Poppins',sans-serif;font-weight:600;font-size:16px;color:#FFFFFF"><?php echo e(__('auth.login.login_button')); ?></span>
                    <span wire:loading wire:target="authenticate" style="font-family:'Poppins',sans-serif;font-weight:600;font-size:16px;color:#FFFFFF"><?php echo e(__('auth.login.logging_in')); ?></span>
                </button>
            </form>

            <!-- Register Section -->
            <div class="mt-4 sm:mt-6 md:mt-8 text-center px-2 sm:px-4 md:px-6">
                <p class="text-sm sm:text-base md:text-lg font-semibold text-secondary mb-2 sm:mb-3 md:mb-4 register-cta" style="font-family:'Poppins',sans-serif;font-weight:700;font-size:20px;color:#5C2D62"><?php echo e(__('auth.unknown_text')); ?></p>
                <button type="button"
                    @click="show = false; $wire.hide(); $dispatch('show-register-modal')"
                    x-bind:disabled="closing"
                    class="modal-btn-outline register-btn"
                    style="border-radius:15px;border:1px solid #DD3888;background:transparent;color:#DD3888;font-size:16px;font-family:'Poppins',sans-serif;font-weight:600;display:flex;margin:0 auto;box-sizing:border-box;align-items:center;justify-content:center;text-align:center;">
                    <?php echo e(__('auth.switch_to_register')); ?>

                </button>
            </div>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/livewire/login-modal.blade.php ENDPATH**/ ?>