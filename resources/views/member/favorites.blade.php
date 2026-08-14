@extends('layouts.member')

@section('member-content')
<div class="w-[310px] md:w-auto mx-auto">
<!-- Page Title -->
<div class="mb-4 md:mb-8 text-center">
    <h1 style="font-family:'Poppins',sans-serif;font-weight:700;font-size:36px;color:#5C2D62;">{{ __('front.account.member.favorites') }}</h1>
</div>
<hr class="mb-8 rounded-none">

<!-- Favorites Grid -->
@if($favorites->count() > 0)
<div class="mt-6 grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
    @foreach($favorites as $profile)
    <div class="relative">
        <!-- Profile Card Component -->
        <x-profile-card :profile="$profile" :showRemoveButton="true" :removeUrl="route('account.member.favorites.remove', $profile)" />
    </div>
    @endforeach
</div>

<!-- Pagination -->
@if($favorites->hasPages())
<div class="mt-8 flex justify-center">
    {{ $favorites->links('vendor.pagination.member') }}
</div>
@endif

@else
<!-- Empty State -->
<div class="mt-6 bg-white rounded-lg border border-gray-200 p-12 text-center">
    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
    </svg>
    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('front.favorites.no_favorites') }}</h3>
    <p class="text-gray-500 mb-6">{{ __('front.favorites.no_favorites_description') }}</p>
    <a href="{{ route('profiles.index') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        {{ __('front.favorites.browse_profiles') }}
    </a>
</div>
@endif
</div>
@endsection
