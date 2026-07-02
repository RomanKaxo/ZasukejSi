@extends('layouts.member')

@section('member-content')
    <!-- Page Title -->
    <div class="mb-4 md:mb-8">
        <h1 class="text-xl md:text-2xl font-bold text-gray-900">{{ __('front.account.member.settings') }}</h1>
        <p class="mt-1 md:mt-2 text-sm text-gray-600">{{ __('front.account.member.settings_description') }}</p>
    </div>

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

    <!-- User Settings Form -->
    <div class="bg-white rounded-lg border border-gray-200 p-4 md:p-6">
        <form method="POST" action="{{ route('account.member.settings.update') }}" class="space-y-4 md:space-y-6">
            @csrf
            @method('PATCH')

            <!-- Name -->
            <div>
                <label for="name"
                    class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.name') }} *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                    class="input-control @error('name') border-red-500 @enderror" required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email (read-only display) -->
            <div>
                <label for="email_display"
                    class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.email') }} *</label>
                <input type="email" id="email_display" value="{{ $user->email }}" disabled
                    class="input-control bg-gray-50 text-gray-500 cursor-not-allowed">
            </div>

            <!-- Phone -->
            <div>
                <label for="phone"
                    class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.phone') }}</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                    class="input-control @error('phone') border-red-500 @enderror">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Change Section -->
            <div x-data="{ expanded: {{ $errors->has('new_email') || $errors->has('email_change_password') ? 'true' : 'false' }} }">
                <button type="button" @click="expanded = !expanded"
                    class="w-full flex items-center justify-between py-3 px-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                            </path>
                        </svg>
                        {{ __('front.profiles.form.change_email') }}
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
                            class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.new_email') }}</label>
                        <input type="email" id="new_email" name="new_email" value="{{ old('new_email') }}"
                            class="input-control @error('new_email') border-red-500 @enderror"
                            placeholder="{{ __('front.profiles.form.email_placeholder') }}">
                        @error('new_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Confirmation for Email Change -->
                    <div>
                        <label for="email_change_password"
                            class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.password_to_confirm') }}</label>
                        <input type="password" id="email_change_password" name="email_change_password"
                            class="input-control @error('email_change_password') border-red-500 @enderror"
                            placeholder="••••••••">
                        @error('email_change_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">{{ __('front.profiles.form.email_change_notice') }}</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-4">
                        <button type="submit" class="btn-primary btn-small justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('front.profiles.form.savechanges') }}
                        </button>
                    </div>
                </div>
            </div>


            <!-- Password Change Section -->
            <div class="my-6">
                <div x-data="{ expanded: {{ $errors->has('current_password') || $errors->has('password') ? 'true' : 'false' }} }">
                    <button type="button" @click="expanded = !expanded"
                        class="w-full flex items-center justify-between py-3 px-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                        <span class="flex items-center gap-2 text-sm font-medium text-gray-700">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                                </path>
                            </svg>
                            {{ __('front.profiles.form.change_password') }}
                        </span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                            :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>

                    <div x-show="expanded" x-collapse x-cloak class="mt-4">
                        <p class="text-sm text-gray-600 mb-6">{{ __('front.account.password.description') }}</p>

                        <form method="POST" action="{{ route('account.member.password.update') }}" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            <!-- Current Password -->
                            <div>
                                <label for="current_password"
                                    class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.current_password') }}</label>
                                <input type="password" id="current_password" name="current_password"
                                    class="input-control @error('current_password') border-red-500 @enderror"
                                    placeholder="••••••••" required>
                                @error('current_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- New Password -->
                                <div>
                                    <label for="password"
                                        class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.new_password') }}</label>
                                    <input type="password" id="password" name="password"
                                        class="input-control @error('password') border-red-500 @enderror"
                                        placeholder="••••••••" required>
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <label for="password_confirmation"
                                        class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.profiles.form.confirm_password') }}</label>
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
                                    {{ __('front.profiles.form.savechanges') }}
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
                    {{ __('front.profiles.form.savechanges') }}
                </button>
            </div>
        </form>
    </div>
@endsection
