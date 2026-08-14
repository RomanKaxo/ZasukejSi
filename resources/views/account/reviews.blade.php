@extends('layouts.account')

@section('title', __('front.account.sidebar.reviews'))

@php
    $activeItem = 'reviews';
@endphp

@section('account-content')
    <div class="mb-4 md:mb-8">
        <h1 class="text-2xl md:text-4xl font-semibold text-secondary py-3 md:py-6 text-center">
            {{ __('front.account.sidebar.reviews') }}
        </h1>
        <hr>
    </div>

    {{-- Summary tiles. Per the empty-value convention the tiles stay even with
         no ratings; only the value is replaced by a neutral dash. --}}
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 p-6 text-center">
            <div class="text-sm text-gray-500 mb-1">{{ __('front.account.reviews.average') }}</div>
            <div class="text-3xl font-semibold text-secondary">
                @if($averageRating !== null)
                    {{ $averageRating }}/5
                    <span class="block text-sm font-normal text-gray-500">{{ $averagePercentage }} %</span>
                @else
                    <x-empty-value />
                @endif
            </div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-6 text-center">
            <div class="text-sm text-gray-500 mb-1">{{ __('front.account.reviews.total') }}</div>
            <div class="text-3xl font-semibold text-secondary">{{ $totalRatings }}</div>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        @if($ratings && $ratings->count() > 0)
            <ul class="divide-y divide-gray-200">
                @foreach($ratings as $rating)
                    <li class="flex items-center justify-between p-4 md:p-6">
                        <div>
                            <div class="font-medium text-gray-900">
                                {{ $rating->user?->name ?: __('front.account.reviews.anonymous') }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $rating->created_at->format('d.m.Y') }}
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            {{-- The percentage is what the member chose; the
                                 stars are its projection onto the 1-5 scale. --}}
                            <span class="text-sm font-semibold" style="color: {{ $rating->color }};">
                                {{ $rating->percentage }} %
                            </span>
                            <div class="flex items-center gap-1" aria-label="{{ __('front.account.reviews.stars', ['count' => $rating->stars]) }}">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= $rating->rating ? 'text-[#DD3888]' : 'text-gray-300' }}"
                                         fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 00-.363 1.118l1.285 3.958c.3.921-.755 1.688-1.539 1.118l-3.366-2.446a1 1 0 00-1.176 0l-3.366 2.446c-.784.57-1.838-.197-1.539-1.118l1.285-3.958a1 1 0 00-.363-1.118L2.06 8.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.951-.69l1.286-3.957z"/>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            @if($ratings->hasPages())
                <div class="p-4 border-t border-gray-200">
                    {{ $ratings->links() }}
                </div>
            @endif
        @else
            <div class="p-8 md:p-12 text-center">
                <svg class="mx-auto h-12 w-12 md:h-16 md:w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('front.profiles.rating.no_ratings') }}</h3>
                <p class="text-gray-500">{{ __('front.account.reviews.empty_hint') }}</p>
            </div>
        @endif
    </div>
@endsection
