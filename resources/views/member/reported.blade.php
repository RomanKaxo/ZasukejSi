@extends('layouts.member')

@section('member-content')
<!-- Page Title -->
<div class="mb-4 md:mb-8">
    <h1 class="text-xl md:text-2xl font-bold text-gray-900">{{ __('front.account.member.reported') }}</h1>
    <p class="mt-1 md:mt-2 text-sm text-gray-600">{{ __('front.account.member.reported_description') }}</p>
</div>

<!-- Placeholder Content -->
<div class="bg-white rounded-lg border border-gray-200 p-8 md:p-12 text-center">
    <svg class="mx-auto h-12 w-12 md:h-16 md:w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
    </svg>
    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('front.account.member.coming_soon') }}</h3>
    <p class="text-gray-500">{{ __('front.account.member.feature_coming_soon') }}</p>
</div>
@endsection
