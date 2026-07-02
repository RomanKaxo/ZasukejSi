<div class="inline-block">
    <button
        wire:click="toggleFavorite"
        wire:loading.attr="disabled"
        class="group flex items-center gap-2 px-4 py-2 rounded-lg transition-all duration-200 
            {{ $isFavorited 
                ? 'bg-pink-100 text-pink-600 hover:bg-pink-200' 
                : 'bg-gray-100 text-gray-600 hover:bg-pink-50 hover:text-pink-500' }}"
        title="{{ $isFavorited ? __('front.favorites.remove') : __('front.favorites.add') }}"
    >
        <svg 
            class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ $isFavorited ? 'fill-current' : '' }}" 
            fill="{{ $isFavorited ? 'currentColor' : 'none' }}" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
        >
            <path 
                stroke-linecap="round" 
                stroke-linejoin="round" 
                stroke-width="2" 
                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" 
            />
        </svg>
        <span class="text-sm font-medium">
            <span wire:loading.remove>
                {{ $isFavorited ? __('front.favorites.in_favorites') : __('front.favorites.add') }}
            </span>
            <span wire:loading>
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </span>
        </span>
    </button>

    @if($message)
    <div 
        x-data="{ show: true }" 
        x-show="show" 
        x-init="setTimeout(() => show = false, 3000)"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="mt-2 text-sm {{ str_contains($message, 'error') || str_contains($message, 'Error') ? 'text-red-500' : 'text-green-600' }}"
    >
        {{ $message }}
    </div>
    @endif
</div>
