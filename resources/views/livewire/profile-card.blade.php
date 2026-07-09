@props(['profile', 'variant' => 'default', 'isReported' => false])

@php
    $shouldBlur = $variant === 'vip-detail';
@endphp

@if($variant === 'vip-detail')
<div class="vip-rec-card group relative overflow-hidden rounded-[22px] border border-[#f1e8ef] bg-white shadow-[0_18px_40px_rgba(92,45,98,0.08)] transition-transform duration-300 hover:-translate-y-1">
    <div class="vip-rec-card__media relative aspect-[0.92] overflow-hidden bg-[#f6eef7]">
        @if($profile->getFirstImageUrl())
            <img src="{{ $profile->getFirstImageUrl() }}" alt="{{ $profile->display_name }}" class="h-full w-full scale-110 object-cover blur-md saturate-[0.88]">
        @else
            <div class="flex h-full w-full items-center justify-center">
                <svg class="h-16 w-16 text-[#cfbdd4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
        @endif

        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(255,255,255,0.10)_0%,rgba(255,255,255,0.26)_100%)]"></div>

        <div class="absolute left-1/2 top-1/2 flex h-14 w-14 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-white shadow-[0_12px_30px_rgba(92,45,98,0.18)]">
            <img src="{{ asset('images/icons/lock.svg') }}" alt="" aria-hidden="true" class="h-6 w-6 opacity-80">
        </div>

        @if($profile->isVip())
            <div class="absolute right-3 top-3 rounded-full bg-[#ffcf48] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-white shadow-sm">
                VIP
            </div>
        @endif
    </div>

    <div class="space-y-3 px-3 pb-4 pt-3">
        <div class="flex items-center justify-between gap-3">
            <h4 class="truncate text-[15px] font-semibold text-[#5c2d62]">{{ $profile->display_name }}</h4>
            @if($profile->isVerified())
                <span class="rounded-full bg-[#fff4c9] px-2 py-1 text-[10px] font-semibold text-[#d3a100]">TOP</span>
            @endif
        </div>

        <a href="{{ route('profiles.show', $profile) }}" class="flex items-center justify-between rounded-xl bg-[#5c2d62] px-4 py-2.5 text-sm font-semibold text-white shadow-[0_10px_22px_rgba(92,45,98,0.22)] transition-colors duration-200 hover:bg-[#4f2455]">
            <span>{{ __('front.profiles.list.detail') }}</span>
            <img src="{{ asset('images/icons/search.svg') }}" alt="" aria-hidden="true" class="h-4 w-4 brightness-0 invert">
        </a>

        <div class="flex items-center justify-between rounded-xl bg-[#faf6fb] px-3 py-2 text-[12px] text-[#8c6c93]">
            <span>{{ $profile->city ?: __('front.profiles.list.detail') }}</span>
            <span>{{ $profile->age }} {{ __('front.profiles.list.years') }}</span>
        </div>

        <div class="flex items-center justify-between rounded-xl border border-[#f3e9f5] px-3 py-2 text-[12px]">
            <span class="font-medium text-[#8c6c93]">{{ __('front.profiles.list.rating') }}</span>
            <span class="font-semibold text-[#54b26b]">{{ $profile->getTotalRatings() > 0 ? (float)$profile->getAverageRating() . '/5' : '4,9/5' }}</span>
        </div>
    </div>
</div>
@else

<div class="{{ $isReported ? 'w-[210px] h-[510px] flex flex-col' : '' }} bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 cursor-pointer group relative z-10">
    <!-- Profile Image -->
    <div class="relative overflow-hidden">
        <!-- Verified Badge -->
        @if($profile->isVerified()) 
        <div class="absolute top-3 left-3 flex flex-col items-start gap-1 z-20">
            <div class="bg-green-100 text-green-500 p-1 px-0.5 rounded-xl flex flex-wrap justify-center">
                <x-icons name="camera" class="w-5 h-5" />
                <p class="text-xs font-bold w-full text-center">
                    {{ __('front.profiles.list.verified') }}
                </p>
            </div>
        </div>
        @endif

        @if($shouldBlur)
        <div class="absolute inset-0 z-30 flex items-center justify-center">
             <div class="bg-white rounded-full p-3 shadow-lg">
                 <x-icons name="lock" class="w-6 h-6 text-primary-500" />
             </div>
        </div>
        @endif

        <!-- Profile Photo -->
        <div class="{{ $isReported ? 'w-[210px] h-[265px]' : 'aspect-[4/5]' }} bg-gradient-to-br from-primary-100 to-secondary-100 relative overflow-hidden {{ $shouldBlur ? 'blur-md' : '' }}">
            @if($profile->getAllImages()->count() > 0)
            @if($profile->hasMultipleImages())
            <!-- Swiper for multiple images -->
            <div class="swiper profile-swiper-{{ $profile->id }} w-full h-full">
                <div class="swiper-wrapper">
                    @foreach($profile->getAllImages() as $image)
                    <div class="swiper-slide">
                        <img src="{{ $image->getUrl() }}" alt="{{ $profile->display_name }}"
                            class="w-full h-full object-cover">
                    </div>
                    @endforeach
                </div>
                <!-- Pagination -->
                <div class="swiper-pagination swiper-pagination-{{ $profile->id }}"></div>
            </div>
            @else
            <!-- Single image -->
            <img src="{{ $profile->getFirstImageUrl() }}" alt="{{ $profile->display_name }}"
                class="w-full h-full object-cover">
            @endif
            @else
            <!-- No image placeholder -->
            <div class="flex items-center justify-center w-full h-full">
                <svg class="w-16 h-16 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            @endif

        </div>
    </div>


    <!-- Profile Info -->
    <div class="p-4 {{ $isReported ? 'flex-1 flex flex-col justify-between' : 'space-y-3' }}">

        <!-- Name and VIP Badge -->
        <div class="flex items-center justify-between py-3">
            <h4 class="text-gray-700 flex-grow-0 truncate max-w-[80%]">{{ $profile->display_name }}</h4>
            @if($profile->isVip())
            <div class="bg-gold-500 text-white px-2 py-1 rounded-full text-xs font-bold flex items-center gap-1">
                <x-icons name="star" class="w-3 h-3" />
                VIP
            </div>
            @endif
        </div>

        <!-- Details Button -->
        <a href="{{ $shouldBlur ? '#' : route('profiles.show', $profile) }}"
            class="w-full py-3 px-5 rounded-lg text-white font-semibold transition-colors duration-200 flex items-center justify-between {{ $shouldBlur ? 'pointer-events-none opacity-50' : '' }}"
            style="background:#5C2D62;">
            <span class="text-lg">{{ __('front.profiles.list.detail') }}</span>
            <x-icons name="search" class="w-5 h-5 text-white" strokeWidth="3" />
        </a>

        <!-- Rating/Evaluation -->
        <div class="{{ $isReported ? 'mt-auto' : '' }}">
            <div class="flex bg-gray-200 rounded-lg" style="height:30px;">
                <div class="bg-gray-100 rounded-lg flex items-center justify-center" style="width:82px;height:100%;padding:0 12px;">
                    <div class="text-sm font-medium text-gray-700 leading-none">{{ __('front.profiles.list.rating') }}</div>
                </div>
                <div class="rounded-r-lg flex items-center justify-center gap-1" style="width:88px;height:100%;padding:0 10px;">
                    @if($profile->getTotalRatings() > 0)
                        <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span class="text-sm font-bold text-gray-900">{{ (float)$profile->getAverageRating() . '/5' }}</span>
                    @else
                        <span class="text-xs text-gray-500">—</span>
                    @endif
                </div>
            </div>


            <!-- Location -->
            <div class="flex py-2 justify-center items-center gap-x-2 text-sm text-primary-600">
                @if($profile->city)
                <img src="{{ asset('images/icons/location.svg') }}" alt="" aria-hidden="true" class="w-4 h-4 -translate-y-0.5 inline-block" />
                <h5 class="py-1 text-center">{{ $profile->city }}</h5>
                @endif
            </div>

            <div class="flex justify-between gap-x-3">
                <div class="flex-1 bg-gray-100 rounded-lg p-3 text-center">
                    <div class="text-xs ">168 cm</div>
                </div>
                <div class="flex-1 bg-gray-100 rounded-lg p-3 text-center">
                    <div class="text-xs ">{{ $profile->age }} {{ __('front.profiles.list.years') }}</div>
                </div>
            </div>

        </div>
    </div>

</div>
@endif
