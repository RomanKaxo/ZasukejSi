<div x-data="{ show: false, closing: false }"
    x-on:show-reset-modal.window="show = true; $wire.show()"
    x-on:hide-reset-modal.window="show = false; closing = false"
    x-init="$watch('show', v => { document.body.style.overflow = v ? 'hidden' : ''; if (v) { document.body.classList.add('modal-open') } else { document.body.classList.remove('modal-open') } ; window.dispatchEvent(new CustomEvent('modal-visibility-changed', { detail: { open: v } })); })"
    x-on:keydown.escape.window="if (show) { closing = true; show = false; $wire.hide() }">
    <!-- Reset Password Modal -->
    <div x-show="show"
        x-transition.opacity.duration.300ms
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-1 sm:p-2 md:p-4">

        <!-- Modal Backdrop -->
        <div class="modal-backdrop"
            @click="closing = true; show = false; $wire.hide()"></div>

        <style>
            .form-container { width:100%; max-width:520px; height:353px; }
            .form-field { width:100%; max-width:460px; height:50px; }
            .modal-btn-primary, .modal-btn-secondary { width:100%; max-width:460px; }
            .modal-btn-primary { height:60px; }
            .modal-container { width:600px !important; max-width:600px !important; height:810px !important; border-radius:24px; overflow:hidden; background:white; box-sizing:border-box; }
            @media (max-width:480px) {
                .modal-container { width:320px !important; max-width:320px !important; height:711px !important; }
                .form-container { width:280px !important; max-width:280px !important; height:337px !important; }
                .form-field { max-width:240px !important; }
                .modal-btn-primary, .modal-btn-secondary { max-width:240px !important; height:50px !important; }
                .modal-title, .modal-subtitle { font-size:28px !important; line-height:1.05 !important; }
                .modal-close-btn { top:20px !important; right:20px !important; }
                .modal-btn-primary { margin-top:18px !important; }
                .modal-subtitle { margin-bottom:32px !important; }
            }
        </style>

        <!-- Modal Content -->
        <div class="modal-container">
            <!-- Close Button -->
            <button @click="closing = true; show = false; $wire.hide()"
                class="modal-close-btn" style="width:35px;height:35px;border-radius:50%;display:flex;align-items:center;justify-content:center;position:absolute;right:35px;top:35px;background:#DD3888;border:none">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            @if(!$emailSent)
            <!-- Header -->
            <div class="modal-header">
                <h1 class="modal-title">{{ __('auth.reset.subtitle') }}</h1>
                <p class="modal-description">{{ __('auth.reset.description') }}</p>
            </div>

            <!-- Form -->
            <form wire:submit="sendResetLink" class="form-container">
                <!-- Reset Error -->
                @error('reset')
                <div class="form-alert">
                    {{ $message }}
                </div>
                @enderror

                <!-- Email Field -->
                <div>
                    <label class="form-label">{{ __('auth.reset.email_label') }}</label>
                    <input wire:model="email"
                        type="email"
                        required
                        class="form-field {{ $errors->has('email') ? 'form-field-error' : '' }}">
                    @error('email')
                    <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="sendResetLink"
                    x-bind:disabled="closing"
                    class="modal-btn-primary">
                    <span wire:loading.remove wire:target="sendResetLink">{{ __('auth.reset.send_button') }}</span>
                    <span wire:loading wire:target="sendResetLink">{{ __('auth.reset.sending') }}</span>
                </button>
            </form>

            @else
            <!-- Success State -->
            <div class="text-center">
                <!-- Success Icon -->
                <div class="modal-success-icon">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 md:w-7 md:h-7 lg:w-8 lg:h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <!-- Success Message -->
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-secondary mb-2 sm:mb-3 md:mb-4">{{ __('auth.reset.success_title') }}</h1>
                <p class="text-gray-600 mb-4 sm:mb-6 md:mb-8 text-xs sm:text-sm md:text-base lg:text-lg leading-relaxed">{{ __('auth.reset.success_message') }}</p>

                <!-- Action Buttons -->
                <div class="space-y-2 sm:space-y-2.5 md:space-y-3">
                    <button type="button"
                        wire:click="backToLogin"
                        x-bind:disabled="closing"
                        class="modal-btn-primary">
                        {{ __('auth.reset.back_to_login') }}
                    </button>
                    
                    <button type="button"
                        @click="closing = true; show = false; $wire.hide()"
                        x-bind:disabled="closing"
                        class="modal-btn-secondary">
                        {{ __('auth.reset.close') }}
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>