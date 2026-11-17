@extends('layouts.account')

@section('title', __('front.account.password.title'))

@php
    $activeItem = 'password';
@endphp

@section('account-content')
    {{-- Figma home-8..10 ("Základní nastavení" › "Změna hesla"): the same
         400px column, 13/400 labels and 50px fields as the profile form, the
         password rule in grey after the label, one grey "Uložit změny" button.
         The tips box and the cancel link are not in the design. --}}
    <style>
        .account-password label { padding-left: 8px; padding-bottom: 0; }
        .account-password .input-control { margin-top: 0; padding-left: 17px; }
    </style>

    @include('account.partials.page-header', ['title' => __('front.account.member.basic_settings'), 'ruleGap' => 'mb-[80px]'])

    @if (session('status'))
        <div role="status" class="relative mx-auto mb-6 flex min-h-[50px] w-[310px] max-w-full items-center justify-center gap-3 rounded-[8px] bg-[#01B810] px-10 py-3 md:w-full">
            <img src="{{ asset('images/icons/SaveWhite.svg') }}" class="h-[20px] w-[20px]" alt="">
            <span class="text-center text-[14px] font-medium text-white" style="font-family: 'Poppins', sans-serif;">
                @if(session('status') === 'password-updated')
                    {{ __('front.account.password.success') }}
                @else
                    {{ session('status') }}
                @endif
            </span>
        </div>
    @endif

    <form method="POST" action="{{ route('account.password.update') }}" class="account-password profile-form-narrow mx-auto w-[312px] max-w-full md:w-[400px]">
        @csrf
        @method('PATCH')

        <h3 style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62; margin-bottom:15px;">{{ __('front.account.password.title') }}</h3>

        <div class="grid grid-cols-1 gap-[10px]">
            <div>
                <label for="current_password" class="block">{{ __('front.profiles.form.current_password') }}</label>
                <input type="password" name="current_password" id="current_password" autocomplete="current-password"
                    class="input-control @error('current_password') border-red-500 @enderror">
                @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="block">{{ __('front.profiles.form.new_password') }} <span class="text-[#A4A4A4]">{{ __('front.profiles.form.new_password_hint') }}</span></label>
                <input type="password" name="password" id="password" autocomplete="new-password"
                    class="input-control @error('password') border-red-500 @enderror">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block">{{ __('front.profiles.form.confirm_password') }}</label>
                <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password"
                    class="input-control @error('password_confirmation') border-red-500 @enderror">
                @error('password_confirmation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <button type="submit" class="mt-[31px] flex h-[50px] w-full items-center justify-center gap-2 rounded-[8px] bg-[#E8E8E8] transition-colors duration-200 hover:bg-[#5C2D62] group">
            <img src="{{ asset('images/icons/Save.svg') }}" class="h-[20px] w-[20px] group-hover:hidden" alt="">
            <img src="{{ asset('images/icons/SaveWhite.svg') }}" class="hidden h-[20px] w-[20px] group-hover:block" alt="">
            <span class="text-[#A4A4A4] group-hover:text-white" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px;">{{ __('front.profiles.form.save_changes') }}</span>
        </button>
    </form>
@endsection
