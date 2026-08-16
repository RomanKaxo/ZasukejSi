<div class="contact-form-wrap">
    <style>
        /* Typography, colours and control shapes are taken from the report
           modal so the contact form reads as part of the same site. */
        .contact-form-wrap {
            margin-top: 40px;
        }

        .contact-form-panel {
            width: 100%;
            max-width: 720px;
            border-radius: 15px;
            background: #F2F2F2;
            box-sizing: border-box;
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .contact-form-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 28px;
            color: #5C2D62;
            margin-bottom: 8px;
        }

        .contact-form-intro {
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #505050;
            margin-bottom: 20px;
            max-width: 720px;
        }

        .contact-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 640px) {
            .contact-form-row {
                grid-template-columns: 1fr;
            }

            .contact-form-panel {
                padding: 20px;
            }
        }

        .contact-form-label {
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            font-size: 13px;
            color: #505050;
            display: block;
            margin-bottom: 6px;
        }

        .contact-form-input,
        .contact-form-textarea {
            width: 100%;
            background: #FFFFFF;
            border-radius: 8px;
            border: 2px solid #E6E6E6;
            padding: 12px 14px;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #333;
        }

        .contact-form-input:focus,
        .contact-form-textarea:focus {
            outline: none;
            border-color: #DD3888;
        }

        .contact-form-textarea {
            height: 161px;
            resize: vertical;
        }

        .contact-form-error {
            color: #DD3888;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            margin-top: 4px;
        }

        .contact-form-submit {
            align-self: flex-start;
            padding: 12px 28px;
            border-radius: 8px;
            background: #DD3888;
            border: none;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 16px;
            color: #FFFFFF;
            cursor: pointer;
            transition: background-color 150ms ease, transform 150ms ease;
        }

        .contact-form-submit:hover {
            background: #c4286f;
            transform: translateY(-1px);
        }

        .contact-form-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .contact-form-signed-in {
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: #505050;
            background: #FFFFFF;
            border-radius: 8px;
            border: 2px solid #E6E6E6;
            padding: 12px 14px;
        }

        .contact-form-success {
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #2F6F3E;
            background: #EAF6EC;
            border: 2px solid #B7E0C0;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 16px;
        }
    </style>

    <h2 class="contact-form-title">{{ __('contact.form.title') }}</h2>
    <p class="contact-form-intro">{{ __('contact.form.intro') }}</p>

    @if($submitted)
        <div class="contact-form-success" role="status">{{ __('contact.form.success') }}</div>
    @endif

    <form wire:submit="submit" class="contact-form-panel">
        @auth
            {{-- Says out loud what gets attached to the message, so a signed-in
                 sender is not surprised that we know who they are. --}}
            <p class="contact-form-signed-in">
                {{ __('contact.form.signed_in_as', ['name' => auth()->user()->name]) }}
                @if(auth()->user()->profile)
                    {{ __('contact.form.signed_in_profile', ['profile' => auth()->user()->profile->display_name]) }}
                @endif
            </p>
        @endauth

        <div class="contact-form-row">
            <div>
                <label class="contact-form-label" for="contact-first-name">{{ __('contact.form.first_name') }}</label>
                <input id="contact-first-name" type="text" wire:model="firstName" class="contact-form-input" required>
                @error('firstName')
                    <p class="contact-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="contact-form-label" for="contact-last-name">{{ __('contact.form.last_name') }}</label>
                <input id="contact-last-name" type="text" wire:model="lastName" class="contact-form-input" required>
                @error('lastName')
                    <p class="contact-form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="contact-form-row">
            <div>
                <label class="contact-form-label" for="contact-phone">{{ __('contact.form.phone') }}</label>
                <input id="contact-phone" type="tel" wire:model="phone" class="contact-form-input">
                @error('phone')
                    <p class="contact-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="contact-form-label" for="contact-email">{{ __('contact.form.email') }}</label>
                <input id="contact-email" type="email" wire:model="email" class="contact-form-input" required>
                @error('email')
                    <p class="contact-form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label class="contact-form-label" for="contact-message">{{ __('contact.form.message') }}</label>
            <textarea id="contact-message" wire:model="message" class="contact-form-textarea" required></textarea>
            @error('message')
                <p class="contact-form-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="contact-form-submit" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="submit">{{ __('contact.form.submit') }}</span>
            <span wire:loading wire:target="submit">{{ __('contact.form.sending') }}</span>
        </button>
    </form>
</div>
