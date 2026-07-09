@props(['profile', 'imageOverride' => null, 'imagesOverride' => null, 'variant' => null, 'showRemoveButton' => false, 'cardHeight' => '520px', 'imageHeight' => '265px', 'simpleMode' => false, 'isReported' => false])

@php
    $shouldBlur = $variant === 'vip-detail';
    // Zajistíme, že $isReported je vždy bool
    $isReported = (bool)($isReported ?? false);
    
    $cardContent = (isset($profile->content) && is_array($profile->content)) ? $profile->content : [];
    $cardLocation = $cardContent['card_location'] ?? ($profile->city ?? null);
    $cardHeightCm = $cardContent['card_height_cm'] ?? 168;
    
    // Check if profile is a model instance (has methods) or a plain object
    $isModel = method_exists($profile, 'isVerified');
    
    // Compute profile URL - handle both model and plain object cases
    $profileUrl = $shouldBlur ? '#' : ($isModel ? route('profiles.show', $profile) : route('profiles.show', $profile->id ?? 0));
    
    // Safe property accessors for plain objects
    $profileName = $profile->display_name ?? 'Profile';
    $profileAge = $profile->age ?? null;
    
    // Check if profile is verified or VIP (works for both models and plain objects)
    $isVerified = $isModel ? $profile->isVerified() : ($profile->is_verified ?? false);
    $isVip = $isModel ? $profile->isVip() : ($profile->is_vip ?? false);
@endphp

@php
    // Prepare image URLs for simple Alpine-based slideshow
    $imageUrls = [];
    if($imagesOverride && is_array($imagesOverride)) {
        $imageUrls = $imagesOverride;
    } elseif($imageOverride) {
        $imageUrls = [$imageOverride];
    } elseif($isModel && $profile->getAllImages()->count() > 0) {
        $imageUrls = $profile->getAllImages()->map(function($i){ return $i->getUrl('thumb'); })->all();
    } elseif($isModel && method_exists($profile, 'getFirstImageThumbUrl') && $profile->getFirstImageThumbUrl()) {
        $imageUrls = [$profile->getFirstImageThumbUrl()];
    } elseif(isset($profile->image_url)) {
        $imageUrls = [$profile->image_url];
    }
    
    // Fallback if no images found
    if (empty($imageUrls)) {
        $imageUrls = [
            asset('images/models/model6.png'),
            asset('images/models/model10.png'),
            asset('images/models/model12.png')
        ];
    }
@endphp

<div class="{{ $isReported ? 'h-[510px]' : '' }} bg-white rounded-lg overflow-hidden transition-all duration-300 cursor-pointer group relative z-10 home-profile-card" 
     style="width: 210px; {{ !$isReported ? 'height: ' . ($simpleMode ? '340px' : $cardHeight) . ';' : '' }} border-radius: 15px; box-shadow: 0 15px 15px 0 rgba(92, 45, 98, 0.1);" 
     x-cloak x-data="{ removed: false, showBtn: false, currentIndex: 0, imageUrls: [] }" data-image-urls='@json($imageUrls)' x-init="imageUrls = JSON.parse($el.getAttribute('data-image-urls') || '[]')" x-show="!removed" @mouseenter="showBtn = true" @mouseleave="showBtn = false">
    @if($showRemoveButton)
    <!-- Remove Button - Hidden by default, shown on hover -->
    <button @click.stop="removed = true" x-show="showBtn" class="absolute top-2 right-2 z-30 w-7 h-7 flex items-center justify-center rounded-full transition-opacity duration-200" style="background-color: #DD3888;">
        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1 1L9 9M9 1L1 9" stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
    </button>
    @endif
    
    <!-- Profile Image -->
    <div class="relative overflow-hidden home-profile-card-media" style="width: 210px; height: {{ $imageHeight }}; border-radius: 15px;">

        @if((!$shouldBlur) && ($isVerified || $isVip) && !$simpleMode)
        <div class="absolute {{ $isReported ? 'top-1' : 'top-3' }} left-3 z-20 home-profile-card-badge-stack">
            <!-- Verified Badge -->
            @if($isVerified)
            <div class="home-profile-card-badge">
                <div class="bg-green-100 text-green-500 p-1 px-0.5 rounded-xl flex flex-wrap justify-center home-profile-card-verified-badge">
                    <x-icons name="camera" class="w-5 h-5 home-profile-card-verified-camera" />
                    <p class="text-xs font-bold w-full text-center home-profile-card-verified-copy">
                        {{ __('front.profiles.list.verified') }}
                    </p>
                    <span class="home-profile-card-verified-check" aria-hidden="true">
                        <img src="{{ asset('images/icons/check.svg') }}" alt="" />
                    </span>
                </div>
            </div>
            @endif

            @if($isVip)
            <div class="home-profile-card-vip home-profile-card-vip-mobile" style="width:50px;height:26px;border-radius:999px;background:#FFB700;">
                <x-icons name="star" class="inline-block" style="width:14px;height:14px;color:#FFFFFF;" />
                <span style="font-family:'Poppins', sans-serif; font-weight:900; font-size:10px; color:#FFFFFF; line-height:1;">VIP</span>
            </div>
            @endif
        </div>
        @endif

        @if($shouldBlur)
        <div class="absolute inset-0 z-30 flex items-center justify-center pointer-events-none">
            <span class="inline-flex items-center justify-center bg-white rounded-full p-5 shadow-lg">
                <x-icons name="lock" strokeWidth="1" class="w-8 h-8 -translate-y-0.5" style="color: #DD3888;" />
            </span>
        </div>
        @endif

        <!-- Profile Photo (Alpine-driven simple slideshow) -->
        <div class="w-full h-full bg-gradient-to-br from-primary-100 to-secondary-100 relative overflow-hidden {{ $shouldBlur ? 'blur-md' : '' }}">
            @php $firstImageUrl = $imageUrls[0] ?? null; @endphp
            @if($firstImageUrl)
                <img src="{{ $firstImageUrl }}" x-bind:src="imageUrls[currentIndex]" alt="{{ $profileName }}" class="w-[210px] h-[265px] object-cover home-profile-card-image absolute inset-0" />
            @else
                <div class="flex items-center justify-center w-full h-full">
                    <svg class="w-16 h-16 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            @endif

        </div>
        
        @php
            $imagesCount = null;
            if($imagesOverride && is_array($imagesOverride)) {
                $imagesCount = count($imagesOverride);
            } elseif($isModel) {
                $imagesCount = $profile->getAllImages()->count();
            } else {
                $imagesCount = $profile->images_count ?? 1;
            }
            $visibleDots = min(5, max(1, (int) $imagesCount));
        @endphp

        <!-- Photo count dots (5 total) -->
        @if(!$simpleMode)
        <div class="absolute left-0 right-0 bottom-3 flex justify-center z-30" style="gap:3px;">
            @for($i = 0; $i < 5; $i++)
                <button type="button" @click.prevent="currentIndex = {{ min($i, $visibleDots - 1) }}" class="w-2.5 h-2.5 rounded-full bg-white flex items-center justify-center" style="box-shadow: 0 0 0 1px rgba(0,0,0,0.04);">
                    <span class="w-1.5 h-1.5 rounded-full" :class="{ 'bg-transparent': currentIndex !== {{ $i }}, 'bg-[#DD3888]': currentIndex === {{ $i }} }" style="display:block;"></span>
                </button>
            @endfor
        </div>
        @endif
    </div>

    <!-- Profile Info -->
    <div class="p-4 space-y-3 home-profile-card-content {{ $isReported ? 'h-[245px] flex flex-col justify-between' : '' }}">
        <!-- Name and VIP Badge -->
        <div class="flex items-center justify-between py-1 home-profile-card-header">
            <h4 class="text-gray-700 flex-grow-0 truncate max-w-[80%] home-profile-card-name {{ $shouldBlur ? 'blur-md' : '' }}" style="font-family: 'Poppins', sans-serif; font-weight:700; font-size:18px; color:#333;">
                {{ $profileName }}
            </h4>
            @if($isVip && !$simpleMode)
            <div class="home-profile-card-vip home-profile-card-vip-desktop" style="width:50px;height:26px;border-radius:999px;background:#FFB700;display:flex;align-items:center;justify-content:center;gap:6px;">
                <x-icons name="star" class="inline-block" style="width:14px;height:14px;color:#FFFFFF;" />
                <span style="font-family:'Poppins', sans-serif; font-weight:900; font-size:10px; color:#FFFFFF; line-height:1;">VIP</span>
            </div>
            @endif
        </div>

        @if(!$simpleMode)
        <!-- Details Button -->
            <a href="{{ $profileUrl }}"
            class="flex items-center justify-between home-profile-card-cta"
            style="width:170px;height:45px;border-radius:8px;background:#5C2D62;color:#FFFFFF; display:inline-flex; align-items:center; justify-content:space-between; padding:0 16px; font-family:'Poppins', sans-serif; font-weight:600; font-size:16px; text-decoration:none; {{ $shouldBlur ? 'pointer-events-none;' : '' }}">
            <span>{{ __('front.profiles.list.detail') }}</span>
            <x-icons name="search" class="inline-block" style="width:24px;height:24px;color:#FFFFFF;" />
        </a>
        @endif

            <!-- Age and Height Stats -->
        <div class="home-profile-card-rating-wrap {{ $isReported ? 'mt-auto' : '' }}">
            <div class="flex justify-between gap-x-3 home-profile-card-stats">
                <div class="home-profile-card-stat" style="width:{{ $simpleMode ? '95px' : '82px' }};height:30px;border-radius:8px;background:#F2F2F2;display:flex;align-items:center;justify-content:center;">
                    <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:600;font-size:11px;color:#505050;">{{ $cardHeightCm }} cm</div>
                </div>
                <div class="home-profile-card-stat" style="width:{{ $simpleMode ? '95px' : '82px' }};height:30px;border-radius:8px;background:#F2F2F2;display:flex;align-items:center;justify-content:center;">
                    <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:600;font-size:11px;color:#505050;">{{ $profileAge }} {{ __('front.profiles.list.years') }}</div>
                </div>
            </div>

            <!-- Location -->
            <div class="flex py-2 justify-center items-center gap-x-2 home-profile-card-location {{ $isReported ? '-mt-2' : '' }}">
                @if($cardLocation)
                    <img src="{{ asset('images/icons/location.svg') }}" alt="" aria-hidden="true" class="inline-block" style="width:20px;height:20px;" />
                    <h5 style="margin:0;font-family:'Plus Jakarta Sans', sans-serif;font-weight:600;font-size:11px;color:#505050;">{{ $cardLocation }}</h5>
                @endif
            </div>

            @if(!$simpleMode)
            <!-- Rating Badge -->
            @php
                $rating = $isModel && $profile->getTotalRatings() > 0 
                    ? $profile->getAverageRating() 
                    : (4.5 + ((isset($profile->id) ? $profile->id : 0) % 5) * 0.1);
            @endphp
            <div class="home-profile-card-rating-badge" style="display:flex;align-items:center;justify-content:center;gap:8px;height:40px;border-radius:8px;{{ $rating < 4 ? 'background:transparent;border: 2px solid #F2F2F2;' : 'background:#E6FEE8;' }}padding:0 12px;">
                <div style="font-family:'Plus Jakarta Sans', sans-serif;font-weight:600;font-size:11px;color:#505050;line-height:1;">Hodnocení:</div>
                <div style="font-family:'Poppins', sans-serif;font-weight:600;font-size:14px;color:#5C2D62;line-height:1;">{{ number_format($rating, 1) }}/5</div>
                <x-icons name="HeartFilled" class="inline-block flex-shrink-0" style="width:20px;height:20px;" preserveColors="true" />
            </div>
            @endif

        </div>
    </div>

</div>