<div class="flex flex-col lg:flex-row gap-3 lg:gap-6">
    {{-- Left Side - Selected Profile with Quick Actions --}}
    <div class="w-full lg:flex-[2] lg:min-w-0 bg-white rounded-2xl overflow-hidden h-[55vh] md:h-[60vh] lg:h-[calc(100vh-200px)]">
        @if($selectedProfile)
            {{-- Profile Image Container with Overlays --}}
            <div class="relative h-full">
                {{-- Profile Photo --}}
                <div class="absolute inset-0">
                    @if($selectedProfile->getAllImages()->count() > 0)
                        <img 
                            src="{{ $selectedProfile->getFirstImageUrl() }}" 
                            alt="{{ $selectedProfile->display_name }}"
                            class="w-full h-full object-cover"
                        >
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-primary-100 to-secondary-100 flex items-center justify-center">
                            <svg class="w-20 h-20 md:w-32 md:h-32 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Gradient Overlay --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>

                {{-- Top Action Badges --}}
                <div class="absolute top-3 left-3 md:top-4 md:left-4 flex gap-1.5 md:gap-2 z-10">
                    <a href="{{ route('profiles.show', $selectedProfile) }}" 
                       class="text-white px-3 py-1.5 md:px-4 md:py-2 rounded-full transition-colors duration-200 text-xs md:text-sm font-semibold"
                       style="background:#5C2D62;">
                        {{ __('front.profiles.list.detail') }}
                    </a>
                    
                    @if($selectedProfile->isVerified())
                        <div class="bg-green-100/90 backdrop-blur text-green-600 px-2 py-1.5 md:px-3 md:py-2 rounded-full flex items-center gap-1 md:gap-2">
                            <x-icons name="camera" class="w-3.5 h-3.5 md:w-4 md:h-4" />
                            <span class="text-[10px] md:text-xs font-bold hidden sm:inline">{{ __('front.profiles.list.verified') }}</span>
                        </div>
                    @endif
                    
                    @if($selectedProfile->isVip())
                        <div class="bg-gold-500/90 backdrop-blur text-white px-2 py-1.5 md:px-3 md:py-2 rounded-full flex items-center gap-1 md:gap-2">
                            <x-icons name="star" class="w-3.5 h-3.5 md:w-4 md:h-4" />
                            <span class="text-[10px] md:text-xs font-bold hidden sm:inline">VIP</span>
                        </div>
                    @endif
                </div>

                {{-- Close/Skip Button (Top Right) --}}
                <button 
                    wire:click="skipProfile"
                    wire:loading.attr="disabled"
                    class="absolute top-3 right-3 md:top-4 md:right-4 z-10 w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-full bg-gray-900/50 backdrop-blur hover:bg-gray-900/70 text-white transition-all duration-200"
                    title="{{ __('front.member.ratings.skip') }}"
                >
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                {{-- Profile Info & Actions Overlay (Bottom) --}}
                <div class="absolute bottom-0 left-0 right-0 p-3 md:p-5 lg:p-6 z-10">
                    {{-- Profile Name --}}
                    <div class="mb-3 md:mb-4">
                        <h2 class="text-xl md:text-2xl lg:text-3xl font-bold text-white">
                            {{ $selectedProfile->display_name }}
                            @if($selectedProfile->age)
                                <span class="text-white/80 font-normal text-lg md:text-xl lg:text-2xl">({{ $selectedProfile->age }})</span>
                            @endif
                        </h2>
                    </div>

                    {{-- "Your Rating" Label --}}
                    @if(!$userRating)
                        <p class="text-white/70 text-xs md:text-sm mb-2 md:mb-3 text-center">{{ __('front.member.ratings.your_rating') }}</p>
                    @endif

                    {{-- Action Buttons - Tinder-style circular buttons --}}
                    <div class="flex items-center justify-center gap-3 md:gap-4">
                        {{-- 30% Rating (Red - Thumbs down) --}}
                        <button 
                            wire:click="rateProfile(30)"
                            wire:loading.attr="disabled"
                            class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center rounded-full transition-all duration-200 transform hover:scale-110 active:scale-95 shrink-0
                                {{ $userRating == 2 
                                    ? 'bg-red-500 text-white ring-4 ring-red-400/50 shadow-lg shadow-red-500/30' 
                                    : 'bg-white/15 backdrop-blur-sm text-white border border-white/30 hover:bg-red-500 hover:border-red-500' }}"
                            title="{{ __('front.member.ratings.rate_30') }}"
                        >
                            <x-icons name="meh" class="w-5 h-5 md:w-6 md:h-6" />
                        </button>


                        {{-- 70% Rating (Yellow - OK) --}}
                        <button 
                            wire:click="rateProfile(70)"
                            wire:loading.attr="disabled"
                            class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center rounded-full transition-all duration-200 transform hover:scale-110 active:scale-95 shrink-0
                                {{ $userRating == 4 
                                    ? 'bg-yellow-500 text-white ring-4 ring-yellow-400/50 shadow-lg shadow-yellow-500/30' 
                                    : 'bg-white/15 backdrop-blur-sm text-white border border-white/30 hover:bg-yellow-500 hover:border-yellow-500' }}"
                            title="{{ __('front.member.ratings.rate_70') }}"
                        >
                            <x-icons name="smile" class="w-5 h-5 md:w-6 md:h-6" />
                        </button>

                        {{-- 100% Rating (Green - Super like) --}}
                        <button 
                            wire:click="rateProfile(100)"
                            wire:loading.attr="disabled"
                            class="w-14 h-14 md:w-16 md:h-16 flex items-center justify-center rounded-full transition-all duration-200 transform hover:scale-110 active:scale-95 shrink-0
                                {{ $userRating == 5 
                                    ? 'bg-green-500 text-white ring-4 ring-green-400/50 shadow-lg shadow-green-500/30' 
                                    : 'bg-white/15 backdrop-blur-sm text-white border-2 border-white/40 hover:bg-green-500 hover:border-green-500' }}"
                            title="{{ __('front.member.ratings.rate_100') }}"
                        >
                            <x-icons name="happy-smile" class="w-6 h-6 md:w-7 md:h-7" />
                        </button>

                        {{-- Favorite Button (Heart) --}}
                        <button 
                            wire:click="toggleFavorite"
                            wire:loading.attr="disabled"
                            class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-full transition-all duration-200 transform hover:scale-110 active:scale-95 shrink-0
                                {{ $isFavorited 
                                    ? 'bg-pink-500 text-white shadow-lg shadow-pink-500/40' 
                                    : 'bg-white/15 backdrop-blur-sm text-white border border-white/30 hover:bg-pink-500 hover:border-pink-500' }}"
                            title="{{ $isFavorited ? __('front.favorites.remove') : __('front.favorites.add') }}"
                        >
                            <span wire:loading wire:target="toggleFavorite" class="text-xs">...</span>
                            <x-icons wire:loading.remove wire:target="toggleFavorite" name="heart" class="w-4 h-4 md:w-5 md:h-5" :strokeWidth="2" :fill="$isFavorited" />
                        </button>
                    </div>
                </div>
            </div>
        @else
            {{-- No Profile Selected State --}}
            <div class="flex-1 flex items-center justify-center">
                <div class="text-center p-6 md:p-8">
                    <svg class="mx-auto h-14 w-14 md:h-20 md:w-20 text-gray-300 mb-3 md:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    <h3 class="text-lg md:text-xl font-medium text-gray-700 mb-2">{{ __('front.member.ratings.select_profile') }}</h3>
                    <p class="text-sm md:text-base text-gray-500">{{ __('front.member.ratings.select_profile_desc') }}</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Right Side - Profile List --}}
    <div class="w-full lg:flex-1 lg:min-w-0 bg-white rounded-2xl p-3 md:p-4 flex flex-col max-h-[35vh] md:max-h-[40vh] lg:max-h-[calc(100vh-200px)]">
        {{-- Header --}}
        <div class="mb-2 md:mb-3 flex-shrink-0">
            <p class="text-xs md:text-sm text-gray-500">{{ __('front.member.ratings.your_history') }}</p>
        </div>

        {{-- Scrollable Profile List --}}
        <div class="flex-1 overflow-y-auto min-h-0 -mx-3 px-3 md:-mx-4 md:px-4">
            @forelse($profiles as $profile)
                @php
                    $userRatingForProfile = auth()->user() ? $profile->getUserRating(auth()->id()) : null;
                    $userRatingPercent = $userRatingForProfile ? ($userRatingForProfile / 5) * 100 : 0;
                    $avgRatingPercent = $profile->getTotalRatings() > 0 ? ($profile->getAverageRating() / 5) * 100 : 0;
                @endphp
                <button 
                    wire:click="selectProfile({{ $profile->id }})"
                    wire:key="profile-{{ $profile->id }}"
                    class="w-full p-2 md:p-3 hover:bg-gray-50 active:bg-gray-100 transition-colors duration-150 shadow-sm hover:shadow rounded-xl md:rounded-2xl mb-1.5 md:mb-2
                        {{ $selectedProfileId === $profile->id ? 'bg-primary-50 border border-primary-200 shadow-md' : 'border border-transparent' }}"
                >
                    <div class="flex items-center gap-2.5 md:gap-3">
                        {{-- Thumbnail --}}
                        <div class="w-10 h-12 md:w-14 md:h-16 rounded-lg md:rounded-xl overflow-hidden flex-shrink-0 bg-gradient-to-br from-primary-100 to-secondary-100">
                            @if($profile->getAllImages()->count() > 0)
                                <img 
                                    src="{{ $profile->getAllImages()->first()->getUrl() }}" 
                                    alt="{{ $profile->display_name }}"
                                    class="w-full h-full object-cover"
                                >
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-5 h-5 md:w-6 md:h-6 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Profile Info and Rating Bars --}}
                        <div class="flex-1 min-w-0">
                            {{-- Name and Profile Link --}}
                            <div class="flex items-center justify-between mb-1 md:mb-1.5">
                                <h4 class="font-semibold text-gray-900 truncate text-sm md:text-base">{{ $profile->display_name }}</h4>
                                <a href="{{ route('profiles.show', $profile) }}" 
                                   onclick="event.stopPropagation()"
                                   class="text-[10px] md:text-xs text-pink-500 hover:text-pink-600 font-medium whitespace-nowrap ml-2">
                                    {{ __('front.profiles.list.detail') }}
                                </a>
                            </div>

                            {{-- Rating Bars --}}
                            <div class="space-y-0.5 md:space-y-1">
                                {{-- Your Rating --}}
                                <div class="flex items-center gap-1.5 md:gap-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="h-1.5 md:h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-orange-400 to-orange-500 rounded-full transition-all duration-300" 
                                                 style="width: {{ $userRatingPercent }}%"></div>
                                        </div>
                                    </div>
                                    <span class="text-[10px] md:text-xs font-bold text-gray-900 whitespace-nowrap w-8 text-right">{{ $userRatingPercent > 0 ? number_format($userRatingPercent, 0) : '0' }}%</span>
                                </div>
                                {{-- Others Rating --}}
                                <div class="flex items-center gap-1.5 md:gap-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="h-1.5 md:h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-gray-400 to-gray-500 rounded-full transition-all duration-300" 
                                                 style="width: {{ $avgRatingPercent }}%"></div>
                                        </div>
                                    </div>
                                    <span class="text-[10px] md:text-xs font-bold text-gray-500 whitespace-nowrap w-8 text-right">{{ number_format($avgRatingPercent, 0) }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </button>
            @empty
                <div class="p-6 md:p-8 text-center">
                    <svg class="mx-auto h-10 w-10 md:h-12 md:w-12 text-gray-300 mb-2 md:mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-sm text-gray-500">{{ __('front.member.ratings.no_profiles') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
