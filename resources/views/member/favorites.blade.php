@extends('layouts.member')

@section('member-content')
<!-- Page Title -->
<div class="mb-4 md:mb-8">
    <h1 class="text-xl md:text-2xl font-bold text-gray-900">{{ __('front.account.member.favorites') }}</h1>
    <p class="mt-1 md:mt-2 text-sm text-gray-600">{{ __('front.account.member.favorites_description') }}</p>
</div>

<!-- Favorites Grid -->
@if($favorites->count() > 0)
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
    @foreach($favorites as $profile)
    <div class="relative">
        <!-- Profile Card Component -->
        <x-profile-card :profile="$profile" />
        
        <!-- Remove from Favorites Button (Overlay) -->
        <div class="absolute top-3 right-3 z-30">
            <form action="{{ route('account.member.favorites.remove', $profile) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" 
                    class="p-2 rounded-lg bg-pink-100 text-pink-600 hover:bg-pink-200 transition-colors duration-200 shadow-sm"
                    title="{{ __('front.favorites.remove') }}">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>

<!-- Pagination -->
@if($favorites->hasPages())
<div class="mt-8">
    {{ $favorites->links() }}
</div>
@endif

@else
<!-- Empty State -->
<div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
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
@endsection
