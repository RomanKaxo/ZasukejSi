@extends('layouts.account')

@section('title', __('front.account.password.title'))

@php
    $activeItem = 'password';
@endphp

@section('account-content')
    <div class="mb-8">
        <h1 class="text-4xl font-semibold text-secondary py-6 text-center">
            {{ __('front.account.password.title') }}
        </h1>
        <hr>
    </div>

    @if (session('status'))
    <div class="alert alert-success flex items-center justify-between my-3">
        <div class="flex items-center font-semibold">
            <x-icons name="bell" class="w-5 h-5 mr-2.5" />
            <span>
                @if(session('status') === 'password-updated')
                    {{ __('front.account.password.success') }}
                @else
                    {{ session('status') }}
                @endif
            </span>
        </div>
        <button type="button" class="flex items-center ml-2 text-gray-400 hover:text-gray-600" onclick="this.parentElement.remove()">
            <x-icons name="cross" class="text-green-800 w-3 h-3" />
        </button>
    </div>
    @endif

    <form method="POST" action="{{ route('account.password.update') }}" class="space-y-8">
        @csrf
        @method('PATCH')
        
        <!-- Password Section -->
        <div class="space-y-6">

            <!-- Current Password -->
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.account.password.current') }}</label>
                <input
                    type="password"
                    name="current_password" 
                    id="current_password" 
                    autocomplete="current-password"
                    class="input-control mt-1 @error('current_password') border-red-500 @enderror">
                @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- New Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.account.password.new') }}</label>
                <input
                    type="password"
                    name="password" 
                    id="password" 
                    autocomplete="new-password"
                    class="input-control mt-1 @error('password') border-red-500 @enderror">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">{{ __('front.account.password.confirm') }}</label>
                <input
                    type="password"
                    name="password_confirmation" 
                    id="password_confirmation" 
                    autocomplete="new-password"
                    class="input-control mt-1 @error('password_confirmation') border-red-500 @enderror">
                @error('password_confirmation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <p class="text-xs text-gray-500">{{ __('front.account.password.requirements') }}</p>
        </div>

        <!-- Password Tips -->
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h4 class="font-medium text-gray-900 mb-2">{{ __('front.account.password.tips_title') }}</h4>
            <ul class="list-disc list-inside text-sm text-gray-700 space-y-1 ml-2">
                <li>{{ __('front.account.password.tips.unique') }}</li>
                <li>{{ __('front.account.password.tips.mix') }}</li>
                <li>{{ __('front.account.password.tips.length') }}</li>
                <li>{{ __('front.account.password.tips.manager') }}</li>
                <li>{{ __('front.account.password.tips.share') }}</li>
            </ul>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('account.dashboard') }}" class="btn-secondary btn-small justify-center">
                {{ __('front.account.password.cancel') }}
            </a>
            <button
                type="submit"
                class="btn-primary btn-small justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ __('front.account.password.save') }}
            </button>
        </div>
    </form>
@endsection