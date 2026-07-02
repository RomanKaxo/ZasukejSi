@extends('layouts.app')

@section('title', __('front.reset_modal.forgot_password'))

@section('content')
<div class="min-h-[calc(100vh-200px)] flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-md rounded-lg p-8">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">{{ __('front.reset_modal.forgot_password') }}</h1>
                <p class="text-sm text-gray-600 mt-2">{{ __('front.reset_modal.instruction') }}</p>
            </div>

            @if (session('status'))
                <div class="mb-4 p-4 text-sm text-green-800 bg-green-100 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('front.reset_modal.email_label') }}
                    </label>
                    <input 
                        id="email" 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        required 
                        autocomplete="email" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary @error('email') border-red-500 @enderror"
                        placeholder="{{ __('front.reset_modal.email_placeholder') }}"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-primary hover:bg-primary-dark text-white font-medium py-2 px-4 rounded-md transition duration-200"
                >
                    {{ __('front.reset_modal.send_button') }}
                </button>
            </form>

            <div class="text-center mt-6">
                <a href="{{ route('login') }}" class="text-sm text-primary hover:text-primary-dark">
                    {{ __('front.auth.back_to_login') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection