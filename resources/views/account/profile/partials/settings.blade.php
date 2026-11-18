{{--
    Obsah stránky „Základní nastavení“ (/account/profile). Rozměry podle
    Figma home-8: sloupec 400 px (na mobilu 312 px), popisky 13/400 odsazené
    8 px, pole 50 px, nadpisy sekcí 24/700 #5C2D62, šedé tlačítko 50 px,
    sekce oddělené linkou #E9E9E9. Všechno v toku, bez pevného left/right.
--}}
@php
    $heading = "font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; line-height:30px; color:#5C2D62;";
    $text = "font-family:'Poppins',sans-serif; font-size:14px; line-height:22px; color:#505050;";
    $needsGender = $user->gender === null;
@endphp

<style>
    .account-settings label { padding-left: 8px; padding-bottom: 0; }
    .account-settings .input-control { margin-top: 0; padding-left: 17px; }
    .account-settings select.input-control { padding-right: 48px; }
    .account-settings .field-hint { padding-left: 8px; margin-top: 6px; font-family: 'Poppins', sans-serif; font-size: 12px; line-height: 18px; color: #8C8C8C; }
    .account-settings hr.account-settings-rule { border: 0; border-top: 1px solid #E9E9E9; border-radius: 0; }
</style>

<div class="account-settings">
    @if (session('status') === 'profile-updated')
        <div role="status" class="mx-auto mb-[30px] flex min-h-[50px] w-[312px] max-w-full items-center justify-center gap-3 rounded-[8px] bg-[#01B810] px-6 py-3 md:w-[400px]">
            <img src="{{ asset('images/icons/SaveWhite.svg') }}" class="h-[20px] w-[20px] shrink-0" alt="">
            <span class="text-center text-[14px] font-medium text-white" style="font-family: 'Poppins', sans-serif;">{{ __('front.profiles.form.success') }}</span>
        </div>
    @endif

    {{-- Moje údaje --}}
    <section aria-labelledby="settings-data-title" class="profile-form-narrow mx-auto w-[312px] max-w-full md:w-[400px]">
        <h2 id="settings-data-title" style="{{ $heading }} margin-bottom:15px;">{{ __('front.profiles.form.my_data') }}</h2>

        <form method="POST" action="{{ route('account.update') }}" novalidate>
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 gap-[10px]">
                <div>
                    <label for="name" class="block">{{ __('front.profiles.form.nickname') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required autocomplete="nickname"
                        class="input-control @error('name') border-[#D80027] @enderror"
                        @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                    @error('name') <p id="name-error" role="alert" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block">{{ __('front.profiles.form.your_email') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required autocomplete="email"
                        class="input-control @error('email') border-[#D80027] @enderror"
                        @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    @error('email') <p id="email-error" role="alert" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email_change_password" class="block">{{ __('front.profiles.form.password_to_confirm') }}</label>
                    <input type="password" name="email_change_password" id="email_change_password" autocomplete="current-password"
                        aria-describedby="email-change-hint"
                        class="input-control @error('email_change_password') border-[#D80027] @enderror">
                    @error('email_change_password') <p role="alert" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <p id="email-change-hint" class="field-hint">{{ __('front.profiles.form.email_change_notice') }}</p>
                </div>

                <div>
                    <label for="phone" class="block">{{ __('front.profiles.form.your_phone') }}</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel"
                        aria-describedby="phone-hint"
                        class="input-control @error('phone') border-[#D80027] @enderror">
                    @error('phone') <p role="alert" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <p id="phone-hint" class="field-hint">{{ __('front.account_settings.phone_hint') }}</p>
                </div>

                @if ($needsGender)
                    {{-- Účet bez pohlaví (založený mimo registraci) nemá
                         přístup k inzerátu ani k členství a předplatné ho
                         posílá sem. Pohlaví se volí jednou, pak už pole
                         zmizí — mění ho jen administrátor. --}}
                    <div>
                        <label for="gender" class="block">{{ __('front.profiles.form.gender') }}</label>
                        <select name="gender" id="gender" aria-describedby="gender-hint"
                            class="input-control @error('gender') border-[#D80027] @enderror">
                            <option value="">{{ __('front.profiles.form.selectgender') }}</option>
                            <option value="female" @selected(old('gender') === 'female')>{{ __('front.auth.register.female') }}</option>
                            <option value="male" @selected(old('gender') === 'male')>{{ __('front.auth.register.male') }}</option>
                        </select>
                        @error('gender') <p role="alert" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        <p id="gender-hint" class="field-hint">{{ __('front.account_settings.gender_hint') }}</p>
                    </div>
                @endif
            </div>

            <button type="submit" class="group mt-[31px] flex h-[50px] w-full items-center justify-center gap-2 rounded-[8px] bg-[#E8E8E8] transition-colors duration-200 hover:bg-[#5C2D62] focus-visible:bg-[#5C2D62]">
                <img src="{{ asset('images/icons/Save.svg') }}" class="h-[20px] w-[20px] group-hover:hidden group-focus-visible:hidden" alt="">
                <img src="{{ asset('images/icons/SaveWhite.svg') }}" class="hidden h-[20px] w-[20px] group-hover:block group-focus-visible:block" alt="">
                <span class="text-[#A4A4A4] group-hover:text-white group-focus-visible:text-white" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px;">{{ __('front.profiles.form.save_changes') }}</span>
            </button>
        </form>
    </section>

    <hr class="account-settings-rule mx-auto my-[60px] w-[313px] max-w-full md:my-[79px] md:w-full">

    {{-- Heslo: vlastní stránka (home-9/10), sem jen odkaz, ať se chyby dvou
         formulářů s polem „password“ nepletou. --}}
    <section aria-labelledby="settings-password-title" class="mx-auto w-[312px] max-w-full md:w-[400px]">
        <h2 id="settings-password-title" style="{{ $heading }} margin-bottom:10px;">{{ __('front.account.password.title') }}</h2>
        <p style="{{ $text }}">{{ __('front.account_settings.password_text') }}</p>
        <a href="{{ route('account.password.edit') }}"
           class="mt-[20px] flex h-[50px] w-full items-center justify-center gap-2 rounded-[8px] border-2 border-[#E6E6E6] bg-white transition-colors duration-200 hover:border-[#DD3888]"
           style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #505050; text-decoration: none;">
            {{ __('front.profiles.form.change_password') }}
        </a>
    </section>

    <hr class="account-settings-rule mx-auto my-[60px] w-[313px] max-w-full md:my-[79px] md:w-full">

    {{-- Smazání účtu --}}
    <section aria-labelledby="settings-delete-title" class="mx-auto mb-[60px] w-[312px] max-w-full md:mb-[100px] md:w-[400px]"
             x-data="{ open: @js($errors->has('password')) }"
             x-on:keydown.escape.window="open = false">
        <h2 id="settings-delete-title" style="{{ $heading }} margin-bottom:10px;">{{ __('front.profiles.form.delete_account') }}</h2>
        <p style="{{ $text }}">{{ __('front.profiles.form.delete_account_desc') }}</p>
        <button type="button" x-on:click="open = true; $nextTick(() => $refs.deletePassword.focus())"
            class="mt-[20px] flex h-[50px] w-full items-center justify-center rounded-[8px] border-2 border-[#D80027] bg-white transition-colors duration-200 hover:bg-[#D80027] hover:text-white text-[#D80027]"
            style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px;">
            {{ __('front.profiles.form.delete_account') }}
        </button>

        <div x-show="open" x-cloak style="display: none;"
             class="fixed inset-0 z-[110] flex items-center justify-center overflow-y-auto bg-black/50 p-4"
             x-on:click.self="open = false">
            <div role="dialog" aria-modal="true" aria-labelledby="confirm-user-deletion-title"
                 class="profile-form-narrow w-[400px] max-w-full rounded-[8px] bg-white p-[25px] md:p-[30px]">
                <h3 id="confirm-user-deletion-title" style="{{ $heading }} margin-bottom:10px;">{{ __('front.profiles.form.delete_account') }}</h3>
                <p style="{{ $text }}">{{ __('front.profiles.form.delete_account_confirm') }}</p>

                <form method="POST" action="{{ route('account.destroy') }}" class="mt-[15px]">
                    @csrf
                    @method('DELETE')
                    <label for="delete_password" class="block">{{ __('front.auth.login.password') }}</label>
                    <input type="password" name="password" id="delete_password" x-ref="deletePassword" required autocomplete="current-password"
                        placeholder="{{ __('front.profiles.form.password_confirm') }}"
                        class="input-control @error('password') border-[#D80027] @enderror">
                    @error('password') <p role="alert" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                    <div class="mt-[25px] flex flex-col gap-[10px] sm:flex-row-reverse">
                        <button type="submit"
                            class="flex h-[50px] w-full items-center justify-center rounded-[8px] bg-[#D80027] px-4 text-white transition-opacity hover:opacity-90"
                            style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px;">
                            {{ __('front.profiles.form.delete_account') }}
                        </button>
                        <button type="button" x-on:click="open = false"
                            class="flex h-[50px] w-full items-center justify-center rounded-[8px] bg-[#E8E8E8] px-4 text-[#505050] transition-colors hover:bg-[#DDDDDD]"
                            style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px;">
                            {{ __('front.profiles.form.cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
