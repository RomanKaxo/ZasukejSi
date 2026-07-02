@extends('layouts.app')

@section('title', __('front.auth.login.title'))

@section('content')
<!-- Add top padding to account for fixed navbar -->
<div class="pt-28 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="p-10 rounded-3xl max-w-2xl w-full space-y-8 border-2 border-gray-200">
        <div class="text-center">
            <!-- Logo -->
            <a href="{{ route('profiles.index') }}" class="inline-block mb-6">
                <span class="text-2xl xl:text-3xl font-extrabold">
                    <span class="text-secondary-500">ZAŠUKEJ</span><span class="text-primary-500">SI</span><span class="text-dark-gray">.CZ</span>
                </span>
            </a>
            
            <h2 class="text-3xl font-extrabold text-text-default">
                {{ __('front.auth.login.signin') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                {{ __('front.auth.login.welcome') }}
            </p>
        </div>

        {{-- Success/Status Messages --}}
        @if (session('status'))
            <div class="rounded-lg p-4 {{ session('status') === 'email-verified' || session('status') === 'email-already-verified' ? 'bg-green-50 border border-green-200' : 'bg-blue-50 border border-blue-200' }}">
                <div class="flex items-center">
                    <svg class="w-5 h-5 {{ session('status') === 'email-verified' || session('status') === 'email-already-verified' ? 'text-green-600' : 'text-blue-600' }} mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="{{ session('status') === 'email-verified' || session('status') === 'email-already-verified' ? 'text-green-800' : 'text-blue-800' }} font-medium">
                        @if (session('status') === 'email-verified')
                            {{ __('front.auth.email_verified_success') }}
                        @elseif (session('status') === 'email-already-verified')
                            {{ __('front.auth.email_already_verified') }}
                        @else
                            {{ session('status') }}
                        @endif
                    </p>
                </div>
            </div>
        @endif

        <div class="p-8">
            <form class="space-y-6" action="{{ route('login') }}" method="POST">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email">{{ __('front.auth.login.email') }}</label>
                    <input id="email" name="email" type="email" autocomplete="email" required 
                           class="input-control mt-1 @error('email') border-red-500 @enderror" 
                           placeholder="{{ __('front.auth.login.enteremail') }}" 
                           value="{{ old('email') }}">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password">{{ __('front.auth.login.password') }}</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required 
                           class="input-control mt-1 @error('password') border-red-500 @enderror" 
                           placeholder="{{ __('front.auth.login.enterpassword') }}">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me and Submit Button -->
                <div class="w-full flex items-center justify-between space-x-6">
                    <div class="flex items-center justify-start flex-grow-1 max-w-sm">
                        <div class="-translate-y-px">
                            <input id="remember" name="remember" type="checkbox" 
                                   {{ old('remember') ? 'checked' : '' }}
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        </div>
                        <label for="remember" class="ml-2 block text-sm text-gray-700 min-w-30">
                            {{ __('front.auth.login.remember') }}
                        </label>
                    </div>
                    <button type="submit" class="btn-primary btn-small justify-center">
                        {{ __('front.auth.login.signinbutton') }}
                    </button>
                </div>

                <!-- Register Link -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        {{ __('front.auth.login.noaccount') }}
                        <a href="{{ route('register') }}" class="font-medium text-primary-600 hover:text-primary-500">
                            {{ __('front.auth.login.registerhere') }}
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection