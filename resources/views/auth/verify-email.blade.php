@extends('layouts.app')

@section('title', __('front.auth.verify_email_title'))

@section('content')
<!-- Add top padding to account for fixed navbar -->
<div class="pt-28 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="p-10 rounded-3xl max-w-2xl w-full space-y-8 border-2 border-gray-200">
        <div class="text-center">
            <!-- Logo -->
            <a href="{{ route('profiles.index') }}" class="inline-block mb-6">
                <span class="text-2xl xl:text-3xl font-extrabold">
                    <span class="text-secondary-500">ZAŠUKEJ</span><span class="text-primary-500">SI</span><span class="text-dark-gray">.CZ</span>
                </span>
            </a>
            
            <h2 class="text-3xl font-extrabold text-text-default">
                {{ __('front.auth.verify_email_title') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                {{ __('front.auth.verify_email_sent') }}
            </p>
        </div>

        <div class="p-8">
            <div class="text-center mb-6"></div>
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                
                <h3 class="text-lg font-medium text-text-default mb-2">{{ __('front.auth.email_verification_required') }}</h3>
                <p class="text-sm text-gray-500 mb-6">
                    {{ __('front.auth.email_verification_text') }}
                </p>
            </div>

            @if (session('status') == 'verification-link-sent')
                <x-alert type="success" class="mb-6">
                    {{ __('A new verification link has been sent to your email address.') }}
                </x-alert>
            @endif

            <div class="space-y-6">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn-primary w-full justify-center text-white">
                        {{ __('front.auth.resend_verification') }}
                    </button>
                </form>

                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        {{ __('front.auth.want_different_account') }}
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="font-medium text-primary-600 hover:text-primary-500 bg-none border-none p-0 underline">
                                {{ __('front.auth.log_out') }}
                            </button>
                        </form>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection