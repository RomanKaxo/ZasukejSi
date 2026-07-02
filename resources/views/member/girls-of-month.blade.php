@extends('layouts.member')

@section('member-content')
<!-- Page Title -->
<div class="mb-4 md:mb-8">
    <h1 class="text-xl md:text-2xl font-bold text-gray-900">{{ __('front.account.member.girls_of_month') }}</h1>
    <p class="mt-1 md:mt-2 text-sm text-gray-600">{{ __('front.account.member.girls_of_month_description') }}</p>
</div>

<!-- Placeholder Content -->
<div class="bg-white rounded-lg border border-gray-200 p-8 md:p-12 text-center">
    <svg class="mx-auto h-12 w-12 md:h-16 md:w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
    </svg>
    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('front.account.member.coming_soon') }}</h3>
    <p class="text-gray-500">{{ __('front.account.member.feature_coming_soon') }}</p>
</div>
@endsection
