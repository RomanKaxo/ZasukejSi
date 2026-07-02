<div class="bg-gray-100 rounded-lg p-4">
    <div class="flex flex-col gap-3">
        <!-- Average Rating Display -->
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">{{ __('front.profiles.list.rating') }}</span>
            <div class="flex items-center gap-2">
                @if($totalRatings > 0)
                <div class="flex items-center gap-1">
                    <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <span class="text-lg font-bold text-gray-900">{{ number_format($averageRating, 1) }}</span>
                    <span class="text-sm text-gray-500">({{ $totalRatings }})</span>
                </div>
                @else
                <span class="text-sm text-gray-500">{{ __('front.profiles.rating.no_ratings') }}</span>
                @endif
            </div>
        </div>

        <!-- Star Rating Input -->
        <div class="flex flex-col gap-2 items-center">
            <span class="text-xs text-gray-600">{{ __('front.profiles.rating.your_rating') }}</span>
            <div class="flex items-center gap-1">
                @for($i = 1; $i <= 5; $i++)
                    <button
                    wire:click="rate({{ $i }})"
                    @if(!Auth::check()) disabled @endif
                    class="transition-all duration-200 hover:scale-110 focus:outline-none {{ !Auth::check() ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' }}">
                    <svg class="w-7 h-7 {{ $currentRating >= $i ? 'text-yellow-400 fill-current' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    </button>
                    @endfor
            </div>

            @if(!Auth::check())
            <span class="text-xs text-gray-500">{{ __('front.profiles.rating.login_to_rate') }}</span>
            @elseif(!$userRating)
            <span class="text-xs text-gray-500">{{ __('front.profiles.rating.click_to_rate') }}</span>
            @endif
        </div>
    </div>
</div>