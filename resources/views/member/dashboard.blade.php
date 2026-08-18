@extends('layouts.member')

@section('member-content')
    <!-- Page Title -->
    <div class="mb-8">
        <h1 class="text-2xl md:text-4xl text-center" style="font-family:'Poppins',sans-serif; font-weight:700; color:#5C2D62;">
            {{ __('front.account.member.settings') }}
        </h1>
    </div>
    <hr class="mb-8">

    <!-- Status Messages -->
    @if (session('status') === 'settings-updated')
        <div class="alert alert-success flex items-center justify-between mb-4 md:mb-6">
            <div class="flex items-center font-semibold">
                <x-icons name="bell" class="w-5 h-5 mr-2.5" />
                <span>{{ __('front.account.member.settings_saved') }}</span>
            </div>
            <button type="button" class="flex items-center ml-2 text-gray-400 hover:text-gray-600"
                onclick="this.parentElement.remove()">
                <x-icons name="cross" class="text-green-800 w-3 h-3" />
            </button>
        </div>
    @endif

    @if (session('status') === 'password-updated')
        <div class="alert alert-success flex items-center justify-between mb-4 md:mb-6">
            <div class="flex items-center font-semibold">
                <x-icons name="bell" class="w-5 h-5 mr-2.5" />
                <span>{{ __('front.account.password.updated') }}</span>
            </div>
            <button type="button" class="flex items-center ml-2 text-gray-400 hover:text-gray-600"
                onclick="this.parentElement.remove()">
                <x-icons name="cross" class="text-green-800 w-3 h-3" />
            </button>
        </div>
    @endif

    {{-- Cokoli dalšího, co sem někdo pošle.
         Vypisovaly se jen dvě konkrétní hlášky a všechno ostatní se tiše
         zahodilo — takže zpráva o zaplaceném členství sem dorazila a nikdo ji
         nikdy neviděl. Stránka, které se pošle zpráva, ji má ukázat. --}}
    @if (session('status') && ! in_array(session('status'), ['settings-updated', 'password-updated'], true))
        <div class="alert alert-success flex items-center justify-between mb-4 md:mb-6">
            <div class="flex items-center font-semibold">
                <x-icons name="bell" class="w-5 h-5 mr-2.5" />
                <span>{{ session('status') }}</span>
            </div>
            <button type="button" class="flex items-center ml-2 text-gray-400 hover:text-gray-600"
                onclick="this.parentElement.remove()">
                <x-icons name="cross" class="text-green-800 w-3 h-3" />
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger flex items-center justify-between mb-4 md:mb-6">
            <div class="flex items-center font-semibold">
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Moje udaje Section -->
    <div class="profile-form-narrow max-w-[400px] mx-auto">
        <h2 class="mb-4 text-left" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">
            {{ __('front.profiles.form.my_data') }}
        </h2>

        <form method="POST" action="{{ route('account.member.settings.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <!-- Nickname -->
            <div>
                <label for="name" class="block mb-2">{{ __('front.profiles.form.nickname') }}</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                    class="input-control !w-[400px] !h-[50px] max-w-full @error('name') border-red-500 @enderror" required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email (read-only display) -->
            <div>
                <label for="email_display" class="block mb-2">{{ __('front.profiles.form.your_email') }}</label>
                <input type="email" id="email_display" value="{{ $user->email }}" disabled
                    class="input-control !w-[400px] !h-[50px] max-w-full bg-gray-50 text-gray-500 cursor-not-allowed">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="!w-[400px] max-w-full h-[50px] rounded-[8px] flex items-center justify-center gap-2 bg-[#E8E8E8] hover:bg-[#5C2D62] transition-colors duration-200 group">
                <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px] group-hover:hidden" alt="Save">
                <img src="{{ asset('images/icons/SaveWhite.svg') }}" class="w-[20px] h-[20px] hidden group-hover:block" alt="Save">
                <span class="text-[#A4A4A4] group-hover:text-white" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px;">{{ __('front.profiles.form.savechanges') }}</span>
            </button>
        </form>
    </div>

    <hr class="my-8">

    <!-- Zmena hesla Section -->
    <div class="profile-form-narrow max-w-[400px] mx-auto">
        <h2 class="mb-4 text-left" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">
            {{ __('front.profiles.form.change_password') }}
        </h2>

        <form method="POST" action="{{ route('account.member.password.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <!-- Current Password -->
            <div>
                <label for="current_password" class="block mb-2">{{ __('front.profiles.form.current_password') }}</label>
                <input type="password" id="current_password" name="current_password"
                    class="input-control !w-[400px] !h-[50px] max-w-full @error('current_password') border-red-500 @enderror"
                    placeholder="••••••••" required>
                @error('current_password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- New Password -->
            <div>
                <label for="password" class="block mb-2">
                    {{ __('front.profiles.form.new_password') }}
                    <span class="text-[#A4A4A4]">{{ __('front.profiles.form.new_password_hint') }}</span>
                </label>
                <input type="password" id="password" name="password"
                    class="input-control !w-[400px] !h-[50px] max-w-full @error('password') border-red-500 @enderror"
                    placeholder="••••••••" required>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block mb-2">{{ __('front.profiles.form.confirm_password') }}</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                    class="input-control !w-[400px] !h-[50px] max-w-full" placeholder="••••••••" required>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="!w-[400px] max-w-full h-[50px] rounded-[8px] flex items-center justify-center gap-2 bg-[#E8E8E8] hover:bg-[#5C2D62] transition-colors duration-200 group">
                <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px] group-hover:hidden" alt="Save">
                <img src="{{ asset('images/icons/SaveWhite.svg') }}" class="w-[20px] h-[20px] hidden group-hover:block" alt="Save">
                <span class="text-[#A4A4A4] group-hover:text-white" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px;">{{ __('front.profiles.form.savechanges') }}</span>
            </button>
        </form>
    </div>
@endsection
