<div x-data="{ show: false, closing: false, expanded: false, selectedGender: null, emailValue: '', emailHasAt: false }"
    x-on:show-register-modal.window="show = true; $wire.show()"
    x-on:hide-register-modal.window="show = false; closing = false; expanded = false"
    x-init="$watch('show', v => { document.body.style.overflow = v ? 'hidden' : ''; if (v) { document.body.classList.add('modal-open') } else { document.body.classList.remove('modal-open'); selectedGender = null; expanded = false; } ; window.dispatchEvent(new CustomEvent('modal-visibility-changed', { detail: { open: v } })); }); $watch('selectedGender', value => { $wire.set('gender', value ? value : '') })"
    x-on:keydown.escape.window="if (show) { closing = true; show = false; $wire.hide() }">
    <!-- Registration Modal -->
    <div x-show="show"
        x-transition.opacity.duration.300ms
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-1 sm:p-2 md:p-4">

        <!-- Modal Backdrop -->
        <div class="modal-backdrop"
            @click="closing = true; show = false; $wire.hide()"></div>

        <style>
            .form-container { width:100%; max-width:520px; height:353px; }
            .form-container form { display:flex; flex-direction:column; gap:10px; align-items:flex-start; }
            .form-container form > div { width:460px !important; max-width:460px !important; height:84px !important; display:flex; flex-direction:column; justify-content:center; gap:6px; box-sizing:border-box; position:relative; }
            .form-label { font-family: 'Poppins', sans-serif; font-weight:400; font-size:13px; color:#505050; text-align:left; margin-left:8px; margin-bottom:0px; }
            .form-field { width:460px !important; max-width:460px !important; height:50px !important; background:#FFFFFF !important; border:2px solid #E6E6E6 !important; border-radius:8px !important; padding:10px 12px !important; box-sizing:border-box; transition: border-color 120ms ease, box-shadow 120ms ease; }
            .form-field:focus, .form-field:focus-visible { border-color:#DD3888 !important; box-shadow:none !important; outline:none !important; }
            .form-field-error { border-color: #D80027 !important; }
            .form-error { font-family:'Poppins',sans-serif; font-weight:400; font-size:13px; color:#D80027; position:absolute; right:8px; top:8px; margin:0; }
            .modal-btn-primary, .modal-btn-outline { width:100%; max-width:460px; }
            .modal-btn-primary { height:60px; }
            .modal-container { width:600px !important; max-width:600px !important; height:810px !important; border-radius:24px; overflow:hidden; background:white; box-sizing:border-box; transition: width 200ms ease, height 200ms ease; }

            /* Register step 1: smaller modal */
            .modal-container.register-step1 { height:427px !important; }
            /* Expanded desktop register modal */
            .modal-container.register-step1.expanded { width:600px !important; max-width:600px !important; height:947px !important; }
            .modal-container.register-step1 .modal-header { margin-top:44px !important; }

            /* Register success step */
            .modal-container.register-success {
                width:600px !important;
                max-width:600px !important;
                height:480px !important;
                border-radius:24px !important;
            }
            .modal-container.register-success .modal-header {
                margin-top:44px !important;
                margin-bottom:24px !important;
            }
            .register-success-card {
                width:520px;
                height:227px;
                border-radius:15px;
                background:#F2F2F2;
                margin:0 auto;
                display:flex;
                flex-direction:column;
                align-items:center;
                justify-content:flex-start;
                box-sizing:border-box;
                padding-top:38px;
            }
            .register-success-icon {
                width:36px;
                height:36px;
                display:block;
                margin-bottom:18px;
            }
            .register-success-title {
                margin:0;
                width:255px;
                max-width:255px;
                height:60px;
                text-align:center;
                font-family:'Poppins', sans-serif;
                font-weight:700;
                font-size:18px;
                line-height:1.22;
                color:#5C5C5C;
            }
            .register-success-message {
                margin:16px 0 0 0;
                width:326px;
                max-width:326px;
                height:54px;
                text-align:center;
                font-family:'Poppins', sans-serif;
                font-weight:400;
                font-size:11px;
                line-height:1.45;
                color:#5C5C5C;
            }

            /* Gender option styles */
            .gender-option { width:520px; height:100px; border-radius:15px; background:#FFFFFF; box-shadow:0 5px 15px rgba(92,45,98,0.20); display:flex; align-items:center; justify-content:space-between; padding:18px 20px; box-sizing:border-box; margin:0 auto; border:1px solid rgba(0,0,0,0.02); transition: transform 160ms ease, opacity 160ms ease; position:relative; z-index:50; }
            .gender-icon { background:#F2F2F2 !important; transition: background-color 220ms cubic-bezier(.2,.9,.3,1); }
            .gender-icon.active { background:#DD3888 !important; color:#FEFEFE !important; }
            .gender-icon svg { transform:rotate(0deg); transform-origin:center; transition: transform 220ms cubic-bezier(.2,.9,.3,1), stroke 220ms cubic-bezier(.2,.9,.3,1); }
            .modal-container.register-step1.expanded .gender-icon.active svg { stroke:#FEFEFE !important; transform:rotate(180deg); }
            .gender-option > .flex { width:100%; }
            .gender-options { display:flex; flex-direction:column; align-items:center; gap:10px; }
            .gender-title { font-family:'Poppins',sans-serif; font-size:20px; color:#5C2D62; margin:0; font-weight:700; }
            .gender-subtitle { font-family:'Poppins',sans-serif; font-size:13px; color:#A6A6A6; margin:0; font-weight:400; }
            .gender-icon { width:40px !important; height:40px !important; border-radius:50% !important; display:flex; align-items:center; justify-content:center; }

            @media (max-width:480px) {
                .modal-container { width:320px !important; max-width:320px !important; height:711px !important; }
                form[wire\:submit] { margin-top:12px !important; }
                .consent-text { font-size:9px !important; }
                /* Register step 1 specific mobile sizing */
                .modal-container.register-step1 { width:320px !important; max-width:320px !important; height:342px !important; }
                /* When a gender option is expanded (clicked) enlarge modal to show more content */
                .modal-container.register-step1.expanded { width:320px !important; max-width:320px !important; height:895px !important; }
                .modal-container.register-success {
                    width:320px !important;
                    max-width:320px !important;
                    height:468px !important;
                    border-radius:24px !important;
                }
                .register-success-card {
                    width:280px !important;
                    max-width:280px !important;
                    height:263px !important;
                    border-radius:15px !important;
                    background:#EBF8EC !important;
                    padding-top:28px !important;
                }
                .register-success-title {
                    width:255px !important;
                    max-width:255px !important;
                    height:60px !important;
                    font-family:'Poppins', sans-serif !important;
                    font-weight:700 !important;
                    font-size:18px !important;
                    color:#5C5C5C !important;
                }
                .register-success-message {
                    width:195px !important;
                    max-width:195px !important;
                    height:54px !important;
                    font-family:'Poppins', sans-serif !important;
                    font-weight:400 !important;
                    font-size:11px !important;
                    color:#5C5C5C !important;
                }
                .form-container { width:280px !important; max-width:280px !important; height:337px !important; }
                .form-container form > div { width:240px !important; max-width:240px !important; height:74px !important; }
                .form-field { max-width:240px !important; height:50px !important; width:240px !important; }
                .modal-btn-primary, .modal-btn-outline { max-width:240px !important; height:50px !important; }
                /* Gender option mobile size inside register step 1 */
                .modal-container.register-step1 .gender-option { width:280px !important; max-width:280px !important; height:80px !important; border-radius:15px !important; }
                .modal-title, .modal-subtitle { font-size:28px !important; line-height:1.05 !important; }
                .modal-close-btn { top:20px !important; right:20px !important; }
                .modal-btn-primary { margin-top:18px !important; }
                .modal-subtitle { margin-bottom:32px !important; }
                .register-cta { margin-top:55px !important; }
                /* On mobile, for registration step, add bottom spacing under header */
                .modal-container.register-step1 .modal-header { margin-bottom:40px !important; }
            }
            /* Inline register form shown under a selected gender on desktop */
            .form-container.inline-register { width:520px !important; max-width:520px !important; height:645px !important; background:#F2F2F2; border-radius:15px; padding:24px; box-sizing:border-box; margin:12px auto; transition: opacity 160ms ease, transform 160ms ease; transform: translateY(-50px); position:relative; z-index:10; }
            .form-container.inline-register > form { margin-top:75px !important; }
            .consent-text { font-family:'Poppins',sans-serif; font-weight:400; font-size:11px; color:#5C5C5C; margin-top:0px; text-align:left; margin-left:8px; width:440px !important; height:54px !important; box-sizing:border-box; }
            /* Lift primary register button slightly in register modal only */
            .modal-container.register-step1 .modal-btn-primary,
            .modal-container.register-step1 .form-container.inline-register .modal-btn-primary {
                margin-top: -18px !important;
                margin-bottom: 12px !important;
            }
            @media (max-width:480px) {
                .form-container.inline-register { width:280px !important; max-width:280px !important; height:520px !important; }
                .form-container.inline-register > form { margin-top:12px !important; }
            }
        </style>

        <!-- Modal Content -->
        <div class="modal-container pb-4 sm:pb-5 md:pb-7 <?php if($currentStep === 1): ?> register-step1 <?php endif; ?> <?php if($currentStep === 3): ?> register-success <?php endif; ?>" x-bind:class="{ 'expanded': expanded }">
            <!-- Step Back Button (only on step 2, hide on success step) -->
            <!--[if BLOCK]><![endif]--><?php if($currentStep === 2): ?>
            <button wire:click="previousStep"
                class="modal-back-btn">
                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <!-- Close Button (hide on success step or show with different behavior) -->
            <!--[if BLOCK]><![endif]--><?php if($currentStep !== 3): ?>
            <button @click="expanded = false; closing = true; show = false; $wire.hide()"
                class="modal-close-btn" style="width:35px;height:35px;border-radius:50%;display:flex;align-items:center;justify-content:center;position:absolute;right:35px;top:35px;background:#DD3888;border:none">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <?php else: ?>
            <button @click="expanded = false; closing = true; show = false; $wire.hide()"
                class="modal-close-btn" style="width:35px;height:35px;border-radius:50%;display:flex;align-items:center;justify-content:center;position:absolute;right:35px;top:35px;background:#DD3888;border:none">
                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <!--[if BLOCK]><![endif]--><?php if($currentStep === 1): ?>
            <!-- Step 1: Gender Selection -->
            <div class="text-center">
                <!-- Header -->
                <div class="modal-header">
                    <h1 class="modal-title"><?php echo e(__('auth.register.title')); ?></h1>
                </div>

                <!-- Gender Options -->
                <div class="gender-options">
                    <!-- Female Option -->
                    <button x-show="selectedGender === null || selectedGender === 'female'" x-cloak @click="selectedGender === 'female' && expanded ? (expanded = false, selectedGender = null) : (expanded = true, selectedGender = 'female')" type="button"
                        class="gender-option <?php echo e($gender === 'female' ? 'border-primary-500' : 'border-gray-200'); ?>">
                        <div class="flex items-center justify-between">
                            <div class="text-left">
                                <h3 class="gender-title"><?php echo e(__('auth.register.gender_selection.female_title')); ?></h3>
                                <p class="gender-subtitle"><?php echo e(__('auth.register.gender_selection.female_subtitle')); ?></p>
                            </div>
                            <div class="flex items-center">
                                <span x-bind:class="{ 'active': selectedGender === 'female' }" class="gender-icon bg-gray-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 md:w-5 md:h-5 text-primary transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </button>

                    <!-- Inline form shown under selected gender (desktop) -->
                    <div x-show="selectedGender === 'female'" x-cloak class="form-container inline-register"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform -translate-y-1 scale-98"
                        x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0">
                        <form wire:submit="register">
                            <div>
                                <label class="form-label"><?php echo e(__('auth.register.form.username_label')); ?></label>
                                <input wire:model="name" type="text" required class="form-field <?php echo e($errors->has('name') ? 'form-field-error' : ''); ?>">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div>
                                <label class="form-label"><?php echo e(__('auth.register.form.email_label')); ?></label>
                                <input wire:model="email" type="email" required
                                    @input="emailValue = $event.target.value; emailHasAt = emailValue.includes('@')"
                                    class="form-field <?php echo e($errors->has('email') ? 'form-field-error' : ''); ?>" x-bind:class="{ 'form-field-error': (emailValue.length > 0 && !emailHasAt) || <?php echo e($errors->has('email') ? 'true' : 'false'); ?> }">
                                <p x-show="(emailValue.length > 0 && !emailHasAt) || <?php echo e($errors->has('email') ? 'true' : 'false'); ?>" class="form-error" x-cloak>Chybný formát e-mailu</p>
                            </div>
                            <div>
                                <label class="form-label">Vymyslete si heslo do platformy</label>
                                <input wire:model="password" type="password" required class="form-field <?php echo e($errors->has('password') ? 'form-field-error' : ''); ?>">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div>
                                <label class="form-label">Potvrďte heslo</label>
                                <input wire:model="password_confirmation" type="password" required class="form-field <?php echo e($errors->has('password_confirmation') ? 'form-field-error' : ''); ?>">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="consent-text">
                                <?php echo e(__('auth.register.terms')); ?>

                            </div>
                            <div class="pt-2">
                                <button type="submit" wire:loading.attr="disabled" wire:target="register" class="modal-btn-primary">
                                    <span wire:loading.remove wire:target="register"><?php echo e(__('auth.register.register_button')); ?></span>
                                    <span wire:loading wire:target="register"><?php echo e(__('auth.register.creating')); ?></span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Male Option -->
                    <button x-show="selectedGender === null || selectedGender === 'male'" x-cloak @click="selectedGender === 'male' && expanded ? (expanded = false, selectedGender = null) : (expanded = true, selectedGender = 'male')" type="button"
                        x-transition:enter="transition ease-out duration-160"
                        x-transition:enter-start="opacity-0 transform scale-98"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-120"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="gender-option <?php echo e($gender === 'male' ? 'border-primary-500' : 'border-gray-200'); ?>">
                        <div class="flex items-center justify-between">
                            <div class="text-left">
                                <h3 class="gender-title"><?php echo e(__('auth.register.gender_selection.male_title')); ?></h3>
                                <p class="gender-subtitle"><?php echo e(__('auth.register.gender_selection.male_subtitle')); ?></p>
                            </div>
                            <div class="flex items-center">
                                <span x-bind:class="{ 'active': selectedGender === 'male' }" class="gender-icon bg-gray-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 md:w-5 md:h-5 text-primary transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </button>
                    <!-- Inline form for male option (shows under male) -->
                    <div x-show="selectedGender === 'male'" x-cloak class="form-container inline-register"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform -translate-y-1 scale-98"
                        x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0">
                        <form wire:submit="register">
                            <div>
                                <label class="form-label"><?php echo e(__('auth.register.form.username_label')); ?></label>
                                <input wire:model="name" type="text" required class="form-field <?php echo e($errors->has('name') ? 'form-field-error' : ''); ?>">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div>
                                <label class="form-label"><?php echo e(__('auth.register.form.email_label')); ?></label>
                                <input wire:model="email" type="email" required
                                    @input="emailValue = $event.target.value; emailHasAt = emailValue.includes('@')"
                                    class="form-field <?php echo e($errors->has('email') ? 'form-field-error' : ''); ?>" x-bind:class="{ 'form-field-error': (emailValue.length > 0 && !emailHasAt) || <?php echo e($errors->has('email') ? 'true' : 'false'); ?> }">
                                <p x-show="(emailValue.length > 0 && !emailHasAt) || <?php echo e($errors->has('email') ? 'true' : 'false'); ?>" class="form-error" x-cloak>Chybný formát e-mailu</p>
                            </div>
                            <div>
                                <label class="form-label">Vymyslete si heslo do platformy</label>
                                <input wire:model="password" type="password" required class="form-field <?php echo e($errors->has('password') ? 'form-field-error' : ''); ?>">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div>
                                <label class="form-label">Potvrďte heslo</label>
                                <input wire:model="password_confirmation" type="password" required class="form-field <?php echo e($errors->has('password_confirmation') ? 'form-field-error' : ''); ?>">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="consent-text">
                                <?php echo e(__('auth.register.terms')); ?>

                            </div>
                            <div class="pt-2">
                                <button type="submit" wire:loading.attr="disabled" wire:target="register" class="modal-btn-primary">
                                    <span wire:loading.remove wire:target="register"><?php echo e(__('auth.register.register_button')); ?></span>
                                    <span wire:loading wire:target="register"><?php echo e(__('auth.register.creating')); ?></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="form-error mt-4"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            <?php elseif($currentStep === 2): ?>
            <!-- Step 2: Registration Form -->
            <div>
                <!-- Header -->
                <div class="modal-header">
                    <h1 class="modal-title"><?php echo e(__('auth.register.title')); ?></h1>
                    <h2 class="modal-subtitle">
                        <!--[if BLOCK]><![endif]--><?php if($gender === 'female'): ?>
                            <?php echo e(__('auth.register.subtitle_female')); ?>

                        <?php elseif($gender === 'male'): ?>
                            <?php echo e(__('auth.register.subtitle_male')); ?>

                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </h2>
                </div>

                <!-- Form -->
                <form wire:submit="register" class="form-container space-y-2 sm:space-y-2.5 md:space-y-3">
                    <!-- Registration Error -->
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['registration'];
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
                    <!-- Name -->
                    <div>
                        <label class="form-label"><?php echo e(__('auth.register.form.username_label')); ?></label>
                        <input wire:model="name"
                            type="text"
                            required
                            class="form-field <?php echo e($errors->has('name') ? 'form-field-error' : ''); ?>">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['name'];
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

                    <!-- Email -->
                    <div>
                        <label class="form-label"><?php echo e(__('auth.register.form.email_label')); ?></label>
                        <input wire:model="email"
                            type="email"
                            required
                            @input="emailValue = $event.target.value; emailHasAt = emailValue.includes('@')"
                            class="form-field <?php echo e($errors->has('email') ? 'form-field-error' : ''); ?>" x-bind:class="{ 'form-field-error': (emailValue.length > 0 && !emailHasAt) || <?php echo e($errors->has('email') ? 'true' : 'false'); ?> }">
                        <p x-show="(emailValue.length > 0 && !emailHasAt) || <?php echo e($errors->has('email') ? 'true' : 'false'); ?>" class="form-error" x-cloak>Chybný formát e-mailu</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="form-label">Vymyslete si heslo do platformy</label>
                        <input wire:model="password"
                            type="password"
                            required
                            class="form-field <?php echo e($errors->has('password') ? 'form-field-error' : ''); ?>">
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

                    <!-- Confirm Password -->
                    <div>
                        <label class="form-label">Potvrďte heslo</label>
                        <input wire:model="password_confirmation"
                            type="password"
                            required
                            class="form-field <?php echo e($errors->has('password_confirmation') ? 'form-field-error' : ''); ?>">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['password_confirmation'];
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

                    <!-- Terms -->
                    <div class="consent-text">
                        <?php echo e(__('auth.register.terms')); ?>

                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2 sm:pt-3">
                        <button type="submit"
                            wire:loading.attr="disabled"
                            wire:target="register"
                            x-bind:disabled="closing"
                            class="modal-btn-primary">
                            <span wire:loading.remove wire:target="register"><?php echo e(__('auth.register.register_button')); ?></span>
                            <span wire:loading wire:target="register"><?php echo e(__('auth.register.creating')); ?></span>
                        </button>
                    </div>
                </form>
            </div>

            <?php elseif($currentStep === 3): ?>
            <!-- Step 3: Success Message -->
            <div class="text-center">
                <div class="modal-header">
                    <h1 class="modal-title"><?php echo e(__('auth.register.title')); ?></h1>
                    <h2 class="modal-subtitle">
                        <!--[if BLOCK]><![endif]--><?php if($gender === 'female'): ?>
                            <?php echo e(__('auth.register.subtitle_female')); ?>

                        <?php elseif($gender === 'male'): ?>
                            <?php echo e(__('auth.register.subtitle_male')); ?>

                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </h2>
                </div>

                <div class="register-success-card">
                    <img src="<?php echo e(asset('images/icons/MailCheck.svg')); ?>" alt="Mail check" class="register-success-icon">
                    <p class="register-success-title">Registrace proběhla<br>úspěšně, děkujeme</p>
                    <p class="register-success-message">Potvrďte, prosím, ověřovací odkaz, který jsme poslali na <?php echo e($this->maskedEmail); ?>. Pak můžete začít platformu plně využívat.</p>
                </div>
            </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>
</div><?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/livewire/register-modal.blade.php ENDPATH**/ ?>