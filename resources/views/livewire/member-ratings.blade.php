<div class="flex flex-col lg:flex-row gap-3 lg:gap-6">
    {{-- Left Side - Region Filter + Selected Profile with Quick Actions --}}
    <div class="w-full mx-auto lg:mx-0" style="max-width:510px;">
        {{-- Region Filter --}}
        <div class="flex justify-center" style="padding-bottom:32px;">
            <div>
                <label for="kraj" class="block mb-2" style="font-family:'Poppins',sans-serif;font-weight:400;font-size:14px;color:#505050;">
                    {{ __('front.account.member.select_region') }}
                </label>
                <div class="relative rating-region-select">
                    <select id="kraj" name="kraj" class="appearance-none member-select" style="width:100%;height:60px;border-radius:8px;border:1px solid #E6E6E6;padding:0 66px 0 16px;font-family:'Plus Jakarta Sans',sans-serif;font-weight:600;font-size:14px;color:#505050;background:#FFFFFF;">
                        <option value="">{{ __('front.account.member.select_region') }}</option>
                        @foreach($regions as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <span class="absolute pointer-events-none flex items-center justify-center" style="top:50%;right:5px;transform:translateY(-50%);width:50px;height:50px;background:#F2F2F2;border-radius:6px;">
                        <svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 1L5 4L9 1" stroke="#DD3888" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </div>
            </div>
        </div>

    <div class="w-full bg-white rounded-2xl overflow-hidden mx-auto lg:mx-0 rating-photo-card" style="max-width:510px;">
        @if($selectedProfile)
            @php
                // Real photos only — no stock model image standing in for a
                // profile that has none, and no canned service list.
                $ratingImageUrls = $selectedProfile->getAllImages()->map(fn($i) => $i->getUrl())->values()->all();
                if (empty($ratingImageUrls) && $selectedProfile->getFirstImageUrl()) {
                    $ratingImageUrls = [$selectedProfile->getFirstImageUrl()];
                }
                $ratingServices = ($selectedProfile->services && $selectedProfile->services->count() > 0)
                    ? $selectedProfile->services->pluck('name')
                    : collect();
                $ratingVisibleServices = $ratingServices->take(10);
                $ratingMoreServicesCount = $ratingServices->count() - $ratingVisibleServices->count();
                $ratingSlideCount = count($ratingImageUrls) + 1;
            @endphp
            {{-- Profile Image Container with Overlays --}}
            <div class="relative h-full" wire:key="rating-card-{{ $selectedProfile->id }}" x-data="{ currentIndex: 0, images: @js($ratingImageUrls), slideCount: {{ $ratingSlideCount }}, slideHeight: 610, updateSlideHeight() { const slide = this.$refs.track ? this.$refs.track.querySelector('.rating-photo-slide') : null; if (slide) { this.slideHeight = slide.offsetHeight; } } }" x-init="updateSlideHeight(); window.addEventListener('resize', () => updateSlideHeight())">
                {{-- Profile Photo (vertical slide filmstrip) --}}
                <div class="absolute inset-0" style="overflow:hidden;">
                    <div class="transition-transform duration-300 ease-in-out" x-ref="track" :style="`transform:translateY(-${currentIndex * slideHeight}px);`">
                        <template x-for="(url, index) in images" :key="index">
                            <img
                                :src="url"
                                alt="{{ $selectedProfile->display_name }}"
                                class="w-full object-cover rating-photo-slide"
                            >
                        </template>

                        {{-- Final slide: Services --}}
                        <div class="w-full rating-photo-slide rating-services-slide" style="background:linear-gradient(180deg, #A4A4A4 0%, #E8E8E8 100%);box-sizing:border-box;">
                            <h3 style="font-family:'Poppins',sans-serif;font-weight:700;font-size:18px;color:#505050;margin-bottom:16px;padding-top:23px;">
                                {{ __('front.profiles.detail_page.services') }}
                            </h3>
                            <div class="flex flex-wrap" style="gap:10px;">
                                @foreach($ratingVisibleServices as $serviceName)
                                    <span class="inline-flex items-center justify-center" style="height:35px;padding:0 16px;border:2px solid #F2F2F2;border-radius:999px;background:#FFFFFF;font-family:'Poppins',sans-serif;font-weight:500;font-size:11px;color:#505050;">
                                        {{ $serviceName }}
                                    </span>
                                @endforeach
                                @if($ratingMoreServicesCount > 0)
                                    <span class="inline-flex items-center justify-center" style="width:138px;height:35px;border:2px solid #F2F2F2;border-radius:999px;background:transparent;font-family:'Poppins',sans-serif;font-weight:500;font-size:11px;color:#505050;">
                                        + {{ __('front.profiles.detail_page.more_services', ['count' => $ratingMoreServicesCount]) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Gradient Overlay --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" x-show="currentIndex < images.length"></div>

                {{-- Vertical photo pagination dots (side) --}}
                <div class="absolute top-1/2 -translate-y-1/2 flex flex-col z-10 rating-pagination-dots" style="right:16px;gap:6px;" x-show="slideCount > 1">
                    <template x-for="index in slideCount" :key="index">
                        <button
                            type="button"
                            @click="currentIndex = index - 1"
                            class="rounded-full flex items-center justify-center transition-all duration-200 rating-pagination-dot"
                            style="width:10px;height:10px;box-shadow:0 0 0 1px rgba(0,0,0,0.04);"
                            x-bind:class="{ 'bg-white': true }"
                        >
                            <span class="rounded-full rating-pagination-dot-inner" style="width:6px;height:6px;" x-bind:class="currentIndex === (index - 1) ? 'bg-[#DD3888]' : 'bg-transparent'"></span>
                        </button>
                    </template>
                </div>

                {{-- Mobile carousel nav arrows --}}
                <button
                    type="button"
                    @click="currentIndex = currentIndex > 0 ? currentIndex - 1 : slideCount - 1"
                    x-show="slideCount > 1"
                    class="rating-nav-arrow absolute top-1/2 -translate-y-1/2 items-center justify-center z-10"
                    style="left:12px;width:36px;height:36px;border-radius:8px;background:#DD3888;"
                    aria-label="Previous"
                >
                    <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7 1L1 7L7 13" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button
                    type="button"
                    @click="currentIndex = currentIndex < slideCount - 1 ? currentIndex + 1 : 0"
                    x-show="slideCount > 1"
                    class="rating-nav-arrow absolute top-1/2 -translate-y-1/2 items-center justify-center z-10"
                    style="right:12px;width:36px;height:36px;border-radius:8px;background:#DD3888;"
                    aria-label="Next"
                >
                    <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 1L7 7L1 13" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                {{-- Profile Info & Actions Overlay (Top) --}}
                <div class="absolute top-3 md:top-4 left-0 right-0 py-3 md:py-5 lg:py-6 z-10 rating-overlay-top">
                    {{-- Profile Name + Action Badges (name+badges left column, close button stretched right column) --}}
                    <div class="flex items-stretch gap-2">
                        <div class="flex flex-col justify-between gap-2 min-w-0">
                            <h2 class="truncate rating-profile-name" style="font-family:'Poppins',sans-serif;font-weight:700;color:#FFFFFF;filter:drop-shadow(0 0 8px rgba(0,0,0,0.4));">
                                {{ $selectedProfile->display_name }}
                                @if($selectedProfile->age)
                                    <span style="font-weight:700;color:#FFFFFF;">({{ $selectedProfile->age }})</span>
                                @endif
                            </h2>

                            <div class="hidden md:flex items-center gap-2">
                                <a href="{{ route('profiles.show', $selectedProfile) }}"
                                   class="flex items-center justify-center"
                                   style="width:120px;height:30px;border-radius:8px;background:#DD3888;font-family:'Poppins',sans-serif;font-weight:600;font-size:13px;color:#FFFFFF;">
                                    {{ __('front.profiles.list.detail_visit') }}
                                </a>
                                @if($messageRouteAvailable ?? \Illuminate\Support\Facades\Route::has('messages.show'))
                                    <a href="{{ route('messages.show', $selectedProfile->user) }}"
                                       class="flex items-center justify-center"
                                       style="width:76px;height:30px;border-radius:8px;background:#DD3888;font-family:'Poppins',sans-serif;font-weight:600;font-size:13px;color:#FFFFFF;">
                                        {{ __('front.member.ratings.message_short') }}
                                    </a>
                                @endif
                            </div>
                        </div>

                        <button
                            wire:click="skipProfile"
                            wire:loading.attr="disabled"
                            class="flex items-center justify-center ml-auto flex-shrink-0 rating-close-btn"
                            style="border-radius:8px;background:#5C2D62;"
                            title="{{ __('front.member.ratings.skip') }}"
                        >
                            <svg width="24" height="24" fill="none" stroke="#FFFFFF" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                </div>

                {{-- Mobile-only Quick Actions overlay (Uložit / Profil / Zpráva) --}}
                <div class="absolute left-0 right-0 flex md:hidden items-center justify-start z-10" style="bottom:16px;padding-left:20px;">
                    <button
                        wire:click="toggleFavorite"
                        wire:loading.attr="disabled"
                        class="flex items-center justify-center flex-shrink-0
                            {{ $isFavorited ? 'ring-2 ring-white' : '' }}"
                        style="height:40px;padding:0 18px;border-radius:8px;background:#DD3888;margin-right:44px;"
                        title="{{ $isFavorited ? __('front.favorites.remove') : __('front.favorites.add') }}"
                    >
                        <span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:16px;color:#FFFFFF;">{{ __('front.member.ratings.save') }}</span>
                    </button>

                    <a href="{{ route('profiles.show', $selectedProfile) }}"
                       class="flex items-center justify-center gap-1.5 flex-shrink-0"
                       style="height:40px;padding:0 18px;border-radius:8px;background:#FFFFFF;box-shadow:4px 4px 4px #00000040;margin-right:8px;">
                        <span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:16px;color:#DD3888;">{{ __('front.member.ratings.profile_link') }}</span>
                        <x-icons name="User" class="w-6 h-6 text-[#DD3888]" :strokeWidth="3" />
                    </a>

                    @if($messageRouteAvailable ?? \Illuminate\Support\Facades\Route::has('messages.show'))
                        <a href="{{ route('messages.show', $selectedProfile->user) }}"
                           class="flex items-center justify-center flex-shrink-0"
                           style="width:40px;height:40px;border-radius:8px;background:#FFFFFF;box-shadow:4px 4px 4px #00000040;">
                            <x-icons name="MessageCircleMore" class="w-6 h-6 text-[#DD3888]" :strokeWidth="2" />
                        </a>
                    @endif
                </div>

                {{-- Rating refusal message (e.g. own profile / members only) --}}
                @if($ratingError)
                    <div class="absolute left-0 right-0 z-20 flex justify-center" style="bottom:110px;">
                        <p class="text-center px-4 py-2 rounded-lg" style="max-width:90%;background:#FFFFFFEE;font-family:'Poppins',sans-serif;font-weight:500;font-size:13px;color:#DD3888;">
                            {{ $ratingError }}
                        </p>
                    </div>
                @endif

                {{-- Rating & Favorite Buttons (Bottom, desktop only) --}}
                <div class="absolute left-0 right-0 hidden md:flex items-center justify-center gap-2 z-10" style="bottom:47px;">
                    {{-- Favorite Button (Heart) --}}
                    <div class="relative" wire:key="rating-action-heart">
                        @if($isFavorited)
                            <p class="absolute left-0 right-0 text-center whitespace-nowrap" style="bottom:100%;margin-bottom:8px;font-family:'Poppins',sans-serif;font-weight:500;font-size:13px;color:#FFFFFF;text-shadow:4px 4px 4px #00000066;">
                                {{ __('front.member.ratings.your_rating') }}
                            </p>
                        @endif
                        <button
                            wire:click="toggleFavorite"
                            wire:loading.attr="disabled"
                            class="flex items-center justify-center gap-1.5 shrink-0 text-white
                                {{ $isFavorited ? 'ring-2 ring-white' : '' }}"
                            style="width:100px;height:45px;border-radius:8px;background:#DD3888;box-shadow:4px 4px 4px #00000040;"
                            title="{{ $isFavorited ? __('front.favorites.remove') : __('front.favorites.add') }}"
                        >
                            <span wire:loading wire:target="toggleFavorite" class="text-xs text-white">...</span>
                            <span wire:loading.remove wire:target="toggleFavorite" style="font-family:'Poppins',sans-serif;font-weight:600;font-size:16px;color:#FFFFFF;">{{ __('front.member.ratings.save') }}</span>
                            <x-icons wire:loading.remove wire:target="toggleFavorite" name="heart" class="w-5 h-5 text-white" :strokeWidth="2" :fill="$isFavorited" />
                        </button>
                    </div>

                    {{-- 100% Rating --}}
                    <div class="relative" wire:key="rating-action-high">
                        @if($userPercentage === $ratingOptions['high'])
                            <p class="absolute left-0 right-0 text-center whitespace-nowrap" style="bottom:100%;margin-bottom:8px;font-family:'Poppins',sans-serif;font-weight:500;font-size:13px;color:#FFFFFF;text-shadow:4px 4px 4px #00000066;">
                                {{ __('front.member.ratings.your_rating') }}
                            </p>
                        @endif
                        <button
                            wire:click="rateProfile({{ $ratingOptions['high'] }})"
                            wire:loading.attr="disabled"
                            class="flex items-center justify-center gap-1.5 shrink-0 text-white
                                {{ $userPercentage === $ratingOptions['high'] ? 'ring-2 ring-white' : '' }}"
                            style="width:100px;height:45px;border-radius:8px;background:#00B80F;box-shadow:4px 4px 4px #00000040;"
                            title="{{ __('front.member.ratings.rate_100') }}"
                        >
                            <span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:16px;color:#FFFFFF;">{{ $ratingOptions['high'] }} %</span>
                            <x-icons name="happy-smile" class="w-6 h-6 text-white" />
                        </button>
                    </div>

                    {{-- 70% Rating --}}
                    <div class="relative" wire:key="rating-action-mid">
                        @if($userPercentage === $ratingOptions['mid'])
                            <p class="absolute left-0 right-0 text-center whitespace-nowrap" style="bottom:100%;margin-bottom:8px;font-family:'Poppins',sans-serif;font-weight:500;font-size:13px;color:#FFFFFF;text-shadow:4px 4px 4px #00000066;">
                                {{ __('front.member.ratings.your_rating') }}
                            </p>
                        @endif
                        <button
                            wire:click="rateProfile({{ $ratingOptions['mid'] }})"
                            wire:loading.attr="disabled"
                            class="flex items-center justify-center gap-1.5 shrink-0 text-white
                                {{ $userPercentage === $ratingOptions['mid'] ? 'ring-2 ring-white' : '' }}"
                            style="width:100px;height:45px;border-radius:8px;background:#FFB700;box-shadow:4px 4px 4px #00000040;"
                            title="{{ __('front.member.ratings.rate_70') }}"
                        >
                            <span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:16px;color:#FFFFFF;">{{ $ratingOptions['mid'] }} %</span>
                            <x-icons name="smile" class="w-5 h-5 text-white" />
                        </button>
                    </div>

                    {{-- 30% Rating --}}
                    <div class="relative" wire:key="rating-action-low">
                        @if($userPercentage === $ratingOptions['low'])
                            <p class="absolute left-0 right-0 text-center whitespace-nowrap" style="bottom:100%;margin-bottom:8px;font-family:'Poppins',sans-serif;font-weight:500;font-size:13px;color:#FFFFFF;text-shadow:4px 4px 4px #00000066;">
                                {{ __('front.member.ratings.your_rating') }}
                            </p>
                        @endif
                        <button
                            wire:click="rateProfile({{ $ratingOptions['low'] }})"
                            wire:loading.attr="disabled"
                            class="flex items-center justify-center gap-1.5 shrink-0 text-white
                                {{ $userPercentage === $ratingOptions['low'] ? 'ring-2 ring-white' : '' }}"
                            style="width:100px;height:45px;border-radius:8px;background:#F47216;box-shadow:4px 4px 4px #00000040;"
                            title="{{ __('front.member.ratings.rate_30') }}"
                        >
                            <span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:16px;color:#FFFFFF;">{{ $ratingOptions['low'] }} %</span>
                            <x-icons name="meh" class="w-5 h-5 text-white" />
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

    @if($selectedProfile)
        {{-- Mobile-only Rating Buttons (100% / 70% / 30%) --}}
        <div class="flex md:hidden flex-col items-center" style="margin-top:14px;" wire:key="rating-mobile-rate-{{ $selectedProfile->id }}">
            <p style="font-family:'Poppins',sans-serif;font-weight:500;font-size:13px;color:#505050;min-height:16px;">
                @if(in_array($userPercentage, array_values($ratingOptions), true))
                    {{ __('front.member.ratings.your_rating') }}
                @endif
            </p>
            <div class="flex items-center justify-center gap-[6px]" style="margin-top:4px;">
                <button
                    wire:click="rateProfile({{ $ratingOptions['high'] }})"
                    wire:loading.attr="disabled"
                    class="flex items-center justify-center gap-1 text-white flex-shrink-0
                        {{ $userPercentage === $ratingOptions['high'] ? 'ring-2 ring-[#00B80F]' : '' }}"
                    style="width:96px;height:45px;border-radius:8px;background:#00B80F;"
                    title="{{ __('front.member.ratings.rate_100') }}"
                >
                    <span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:14px;color:#FFFFFF;">{{ $ratingOptions['high'] }} %</span>
                    <x-icons name="happy-smile" class="w-5 h-5 text-white" />
                </button>
                <button
                    wire:click="rateProfile({{ $ratingOptions['mid'] }})"
                    wire:loading.attr="disabled"
                    class="flex items-center justify-center gap-1 text-white flex-shrink-0
                        {{ $userPercentage === $ratingOptions['mid'] ? 'ring-2 ring-[#FFB700]' : '' }}"
                    style="width:96px;height:45px;border-radius:8px;background:#FFB700;"
                    title="{{ __('front.member.ratings.rate_70') }}"
                >
                    <span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:14px;color:#FFFFFF;">{{ $ratingOptions['mid'] }} %</span>
                    <x-icons name="smile" class="w-5 h-5 text-white" />
                </button>
                <button
                    wire:click="rateProfile({{ $ratingOptions['low'] }})"
                    wire:loading.attr="disabled"
                    class="flex items-center justify-center gap-1 text-white flex-shrink-0
                        {{ $userPercentage === $ratingOptions['low'] ? 'ring-2 ring-[#F47216]' : '' }}"
                    style="width:96px;height:45px;border-radius:8px;background:#F47216;"
                    title="{{ __('front.member.ratings.rate_30') }}"
                >
                    <span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:14px;color:#FFFFFF;">{{ $ratingOptions['low'] }} %</span>
                    <x-icons name="meh" class="w-5 h-5 text-white" />
                </button>
            </div>
        </div>
    @endif
    </div>

    {{-- Right Side - Profile List --}}
    <div class="w-full lg:flex-1 lg:min-w-0 flex flex-col">
    <div class="w-full bg-white rounded-2xl p-3 md:p-4 flex flex-col relative rating-history-panel">
        {{-- Header --}}
        <div class="mb-2 md:mb-3 flex-shrink-0">
            <p class="text-xs md:text-sm text-gray-500">{{ __('front.member.ratings.your_history') }}</p>
        </div>

        {{-- Scrollable Profile List --}}
        <div class="flex-1 overflow-y-auto min-h-0 -ml-3 pl-3 -mr-3 pr-0 md:-ml-4 md:pl-4 md:-mr-4 md:pr-1 ratings-history-scroll">
            @forelse($profiles as $profile)
                @php
                    // All three figures come from the eager-loaded query in
                    // MemberRatings::availableProfilesQuery(), so this loop
                    // issues no additional database queries.
                    // The percentage the member actually chose. Deriving it back
                    // from the star mirror drew a 70% rating as 80%.
                    $userRatingPercent = (int) ($profile->ratings->first()?->percentage ?? 0);
                    $avgRatingPercent = $profile->ratings_count > 0 ? round((float) ($profile->ratings_avg_percentage ?? 0), 1) : 0;
                    $ratingBarColor = fn ($percent) => \App\Support\RatingScale::color((float) $percent);
                @endphp
                <button
                    wire:click="selectProfile({{ $profile->id }})"
                    wire:key="profile-{{ $profile->id }}"
                    class="flex items-center gap-3 mx-auto hover:shadow transition-shadow duration-150 shadow-sm mb-2
                        {{ $selectedProfileId === $profile->id ? 'ring-2 ring-primary-300' : '' }}"
                    style="width:280px;height:100px;background:#FFFFFF;border-radius:16px;padding:0 12px;"
                >
                    {{-- Thumbnail --}}
                    <div class="overflow-hidden flex-shrink-0 bg-gradient-to-br from-primary-100 to-secondary-100" style="width:64px;height:80px;border-radius:8px;">
                        @if($profile->getAllImages()->count() > 0)
                            <img
                                src="{{ $profile->getAllImages()->first()->getUrl() }}"
                                alt="{{ $profile->display_name }}"
                                class="w-full h-full object-cover"
                            >
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Profile Info and Rating Bars --}}
                    <div class="flex-1 min-w-0 text-left">
                        {{-- Name and Profile Link --}}
                        <div class="flex items-center gap-2 mb-2">
                            <h4 class="truncate" style="font-family:'Poppins',sans-serif;font-weight:700;font-size:18px;color:#505050;">{{ $profile->display_name }}</h4>
                            <a href="{{ route('profiles.show', $profile) }}"
                               onclick="event.stopPropagation()"
                               class="whitespace-nowrap"
                               style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:600;font-size:11px;color:#DD3888;text-decoration:underline;">
                                {{ __('front.member.ratings.profile_link') }}
                            </a>
                        </div>

                        {{-- Rating Bars --}}
                        <div class="space-y-1.5">
                            {{-- Your Rating --}}
                            <div class="flex items-center gap-2">
                                <div class="rounded-full overflow-hidden bg-gray-200 flex-shrink-0" style="width:70px;height:10px;">
                                    <div class="h-full rounded-full" style="width: {{ $userRatingPercent }}%;background:{{ $ratingBarColor($userRatingPercent) }};"></div>
                                </div>
                                <span style="font-family:'Poppins',sans-serif;font-weight:400;font-size:14px;color:#505050;">
                                    {{ __('front.member.ratings.your_rating_label') }}: <span style="font-weight:700;">{{ $userRatingPercent > 0 ? number_format($userRatingPercent, 0) : '0' }} %</span>
                                </span>
                            </div>
                            {{-- Others Rating --}}
                            <div class="flex items-center gap-2">
                                <div class="rounded-full overflow-hidden bg-gray-200 flex-shrink-0" style="width:70px;height:10px;">
                                    <div class="h-full rounded-full" style="width: {{ $avgRatingPercent }}%;background:{{ $ratingBarColor($avgRatingPercent) }};"></div>
                                </div>
                                <span style="font-family:'Poppins',sans-serif;font-weight:400;font-size:14px;color:#505050;">
                                    {{ __('front.member.ratings.others_rating_label') }}: <span style="font-weight:700;">{{ number_format($avgRatingPercent, 0) }} %</span>
                                </span>
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

            @if($profiles->hasPages())
                <div class="flex items-center justify-center gap-2" style="padding:12px 0 24px;">
                    <button
                        type="button"
                        wire:click="previousPage"
                        wire:loading.attr="disabled"
                        @disabled($profiles->onFirstPage())
                        class="flex items-center justify-center disabled:opacity-40 disabled:cursor-not-allowed"
                        style="width:36px;height:36px;border-radius:8px;background:#F2F2F2;">
                        <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 1L1 7L7 13" stroke="#5C2D62" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <span style="font-family:'Poppins',sans-serif;font-weight:500;font-size:13px;color:#505050;">
                        {{ $profiles->currentPage() }} / {{ $profiles->lastPage() }}
                    </span>

                    <button
                        type="button"
                        wire:click="nextPage"
                        wire:loading.attr="disabled"
                        @disabled(! $profiles->hasMorePages())
                        class="flex items-center justify-center disabled:opacity-40 disabled:cursor-not-allowed"
                        style="width:36px;height:36px;border-radius:8px;background:#F2F2F2;">
                        <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 1L7 7L1 13" stroke="#5C2D62" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            @endif
        </div>

        {{-- Bottom fade/blur mask hinting more content below (mobile only) --}}
        <div class="rating-history-fade absolute left-0 right-0 bottom-0 pointer-events-none"></div>
    </div>

    <div class="flex justify-center" style="margin-top:16px;">
        <a href="{{ route('account.member.favorites') }}"
           class="flex items-center justify-center w-[309px] md:w-[311px]"
           style="height:60px;border-radius:8px;background:#5C2D62;font-family:'Poppins',sans-serif;font-weight:600;font-size:16px;color:#FFFFFF;">
            {{ __('front.member.ratings.open_favorites', ['count' => auth()->user()->favoriteProfiles()->count()]) }}
        </a>
    </div>
    </div>
</div>
