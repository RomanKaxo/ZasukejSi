@extends('layouts.member')

@section('member-content')
<!-- Page Title -->
<div class="mb-4 md:mb-8">
    <h1 class="text-xl md:text-2xl font-bold text-gray-900">{{ __('front.account.member.archive') }}</h1>
    <p class="mt-1 md:mt-2 text-sm text-gray-600">{{ __('front.account.member.archive_description') }}</p>
</div>

<!-- Placeholder Content -->
<div class="bg-white rounded-lg border border-gray-200 p-8 md:p-12 text-center">
    <svg class="mx-auto h-12 w-12 md:h-16 md:w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
    </svg>
    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('front.account.member.coming_soon') }}</h3>
    <p class="text-gray-500">{{ __('front.account.member.feature_coming_soon') }}</p>
</div>
@endsection
