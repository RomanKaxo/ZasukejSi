<div x-data="{ show: false }"
    x-on:show-report-modal.window="show = true; $wire.show($event.detail)"
    x-on:hide-report-modal.window="show = false; $wire.hide()"
    x-init="$watch('show', v => { document.body.style.overflow = v ? 'hidden' : ''; if (v) { document.body.classList.add('modal-open') } else { document.body.classList.remove('modal-open') } })"
    x-on:keydown.escape.window="if (show) { show = false; $wire.hide() }">
    <div x-show="show"
        x-transition.opacity.duration.300ms
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4">

        <style>
            .report-modal-container {
                width: 600px;
                max-width: 100%;
                border-radius: 24px;
                background: #FFFFFF;
                box-shadow: 0 10px 25px 0 #00000033;
                box-sizing: border-box;
                padding: 0 45px 30px;
                position: relative;
            }

            @media (max-width: 640px) {
                .report-modal-container {
                    width: 100%;
                    padding: 0 20px 24px;
                }
            }

            .report-modal-panel {
                width: 510px;
                max-width: 100%;
                height: 621px;
                border-radius: 15px;
                background: #F2F2F2;
                box-sizing: border-box;
                padding: 20px;
                margin: 0 auto;
                display: flex;
                flex-direction: column;
                gap: 14px;
                overflow-y: auto;
            }

            .report-modal-field-label {
                font-family: 'Poppins', sans-serif;
                font-weight: 400;
                font-size: 13px;
                color: #505050;
                display: block;
                margin-bottom: 6px;
            }

            .report-modal-input,
            .report-modal-textarea {
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

            .report-modal-textarea {
                height: 161px;
                resize: none;
            }

            .report-modal-file {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                padding: 8px;
                cursor: pointer;
                transition: border-color 150ms ease, box-shadow 150ms ease;
            }

            .report-modal-file:hover {
                border-color: #DD3888;
                box-shadow: 0 4px 12px rgba(221, 56, 136, 0.12);
            }

            .report-modal-file-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                flex-shrink: 0;
                padding: 8px 16px;
                border-radius: 6px;
                background: #DD3888;
                font-family: 'Poppins', sans-serif;
                font-size: 12px;
                font-weight: 600;
                color: #FFFFFF;
                transition: background-color 150ms ease, transform 150ms ease;
            }

            .report-modal-file:hover .report-modal-file-btn {
                background: #c4286f;
                transform: translateY(-1px);
            }

            .report-modal-file-name {
                font-family: 'Poppins', sans-serif;
                font-size: 10px;
                color: #A6A6A6;
                text-align: right;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                min-width: 0;
            }
        </style>

        <div class="modal-backdrop" @click="show = false; $wire.hide()"></div>

        <div class="report-modal-container">
            <button @click="show = false; $wire.hide()"
                style="width:35px;height:35px;border-radius:50%;display:flex;align-items:center;justify-content:center;position:absolute;right:35px;top:35px;background:#DD3888;border:none;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <div style="padding-top:88px;">
                @if(!$submitted)
                    <div style="width:60px;height:60px;margin:0 auto 2px;display:flex;align-items:center;justify-content:center;">
                        <x-icons name="TriangleAlert" class="w-full h-full" style="color:#DD3888;" />
                    </div>

                    <h2 style="width:280px;height:40px;margin:0 auto 16px;font-family:'Poppins',sans-serif;font-weight:700;font-size:36px;color:#5C2D62;text-align:center;">
                        {{ __('front.profiles.detail_page.report_modal.title') }}
                    </h2>

                    <form wire:submit="submit">
                        <div class="report-modal-panel">
                            <div style="flex-shrink:0;">
                                <label class="report-modal-field-label">{{ __('front.profiles.detail_page.report_modal.email_label') }}</label>
                                <input type="email" wire:model="email" class="report-modal-input" required>
                                @error('email')
                                    <p style="color:#DD3888;font-size:12px;margin-top:4px;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div style="flex-shrink:0;">
                                <label class="report-modal-field-label">{{ __('front.profiles.detail_page.report_modal.message_label') }}</label>
                                <textarea wire:model="message" class="report-modal-textarea" required></textarea>
                                @error('message')
                                    <p style="color:#DD3888;font-size:12px;margin-top:4px;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div style="flex-shrink:0;" x-data="{ fileName: '' }">
                                <label class="report-modal-field-label">{{ __('front.profiles.detail_page.report_modal.screenshot_label') }}</label>
                                <label class="report-modal-input report-modal-file">
                                    <span class="report-modal-file-btn">{{ __('front.profiles.detail_page.report_modal.choose_file') }}</span>
                                    <span class="report-modal-file-name" x-text="fileName || '{{ __('front.profiles.detail_page.report_modal.no_file') }}'"></span>
                                    <input type="file" wire:model="screenshot" accept="image/*" class="hidden"
                                        @change="fileName = $event.target.files[0]?.name || ''">
                                </label>
                                <div wire:loading wire:target="screenshot" style="font-size:11px;color:#5C5C5C;margin-top:4px;">{{ __('front.profiles.detail_page.report_modal.uploading') }}</div>
                                @error('screenshot')
                                    <p style="color:#DD3888;font-size:12px;margin-top:4px;">{{ $message }}</p>
                                @enderror
                            </div>

                            <p style="width:421px;max-width:100%;min-height:54px;margin:0 auto;font-family:'Poppins',sans-serif;font-weight:400;font-size:11px;color:#5C5C5C;line-height:1.3;flex-shrink:0;">
                                {{ __('front.profiles.detail_page.report_modal.consent') }}
                            </p>

                            <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="submit"
                                style="width:450px;max-width:100%;height:60px;display:flex;align-items:center;justify-content:center;margin:0 auto;border-radius:8px;background:#DD3888;border:none;flex-shrink:0;">
                                <span wire:loading.remove wire:target="submit" style="font-family:'Poppins',sans-serif;font-weight:600;font-size:16px;color:#FFFFFF;">{{ __('front.profiles.detail_page.report_modal.submit') }}</span>
                                <span wire:loading wire:target="submit" style="font-family:'Poppins',sans-serif;font-weight:600;font-size:16px;color:#FFFFFF;">{{ __('front.profiles.detail_page.report_modal.submitting') }}</span>
                            </button>
                        </div>
                    </form>
                @else
                    <div style="width:60px;height:60px;margin:0 auto 2px;display:flex;align-items:center;justify-content:center;">
                        <x-icons name="TriangleAlert" class="w-full h-full" style="color:#DD3888;" />
                    </div>

                    <h2 style="width:280px;height:40px;margin:0 auto 16px;font-family:'Poppins',sans-serif;font-weight:700;font-size:36px;color:#5C2D62;text-align:center;">
                        {{ __('front.profiles.detail_page.report_modal.title') }}
                    </h2>

                    <div class="report-modal-panel" style="align-items:center;justify-content:center;text-align:center;">
                        <div style="width:36px;height:36px;margin:0 auto;">
                            <x-icons name="MailCheck" class="w-full h-full" style="color:#00B80F;" />
                        </div>

                        <h3 style="width:255px;max-width:100%;margin:16px auto 0;font-family:'Poppins',sans-serif;font-weight:700;font-size:18px;color:#5C5C5C;">
                            {{ __('front.profiles.detail_page.report_modal.success_title') }}
                        </h3>

                        <p style="max-width:400px;margin:12px auto 0;font-family:'Poppins',sans-serif;font-weight:400;font-size:11px;color:#5C5C5C;line-height:1.5;">
                            {{ __('front.profiles.detail_page.report_modal.success_message') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
