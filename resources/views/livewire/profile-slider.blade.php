<div class="profile-slider-container w-full overflow-visible" wire:ignore>
    @if($title)
    <div class="mb-6">
        <h2 class="text-2xl md:text-3xl font-bold text-secondary">{{ $title }}</h2>
    </div>
    @endif

    @if($this->profiles->count() > 0)
    <div class="relative overflow-visible">
        <!-- Swiper Container -->
        <div class="swiper {{ $sliderId }} overflow-visible">
            <div class="swiper-wrapper py-4">
                @foreach($this->profiles as $profile)
                @if($profile->display_name && ($profile->getAllImages()->count() > 0 || $profile->getFirstImageUrl()))
                <div class="swiper-slide h-auto">
                    <x-profile-card :profile="$profile" :variant="$cardVariant" card-height="782px" image-height="537px" />
                </div>
                @endif
                @endforeach
            </div>
            <!-- Pagination -->
        </div>

        <!-- Navigation Buttons Below (Mobile) -->
        <div class="slider-nav-controls-{{ $sliderId }} mt-4 md:hidden" style="display:grid; grid-template-columns:45px minmax(0, 1fr) 45px; align-items:center; width:100%; column-gap:12px;">
            <button class="swiper-button-prev-{{ $sliderId }} w-[45px] h-[45px] bg-primary text-white rounded-lg flex items-center justify-center hover:shadow-lg transition-all duration-200 cursor-pointer" style="background:#DD3888; color:#fff; width:45px; height:45px; border-radius:8px; margin:0;">
                ⏴
            </button>
            <div class="swiper-pagination-{{ $sliderId }} swiper-pagination" style="position:static; margin:0 auto; width:auto; justify-self:center;"></div>
            <button class="swiper-button-next-{{ $sliderId }} w-[45px] h-[45px] bg-primary text-white rounded-lg flex items-center justify-center hover:shadow-lg transition-all duration-200 cursor-pointer" style="background:#DD3888; color:#fff; width:45px; height:45px; border-radius:8px; margin:0;">
                ⏵
            </button>
        </div>
    </div>
    @else
    <!-- Empty State -->
    <div class="text-center py-12">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <p class="text-gray-500">{{ __('front.profiles.list.nofound') }}</p>
    </div>
    @endif
</div>

@push('scripts')
<script>
    (function() {
        const sliderId = '{{ $sliderId }}';
        
        function initProfileSlider() {
            // Check if Swiper is available
            if (typeof Swiper === 'undefined') {
                return;
            }

            // Find the swiper element for this slider ID
            const swiperElement = document.querySelector('.swiper.' + sliderId);
            if (!swiperElement) return;

            // Destroy existing instance if any
            if (swiperElement.swiper) {
                swiperElement.swiper.destroy(true, true);
            }

            // Initialize Swiper - pass the element directly
            new Swiper(swiperElement, {
                slidesPerView: 4, // Default to 4 on desktop
                spaceBetween: 20,
                
                // Observer enabled for dynamic updates
                observer: true,
                observeParents: true,
                
                // Responsive breakpoints
                breakpoints: {
                    0: {
                        slidesPerView: 2,
                        spaceBetween: 8
                    },
                    480: {
                        slidesPerView: 2,
                        spaceBetween: 16
                    },
                    768: {
                        slidesPerView: 3,
                        spaceBetween: 16
                    },
                    1024: {
                        slidesPerView: 4,
                        spaceBetween: 20
                    },
                    1280: {
                        slidesPerView: 4,
                        spaceBetween: 20
                    }
                },

                pagination: {
                    el: document.querySelector('.swiper-pagination-' + sliderId),
                    clickable: true,
                    type: 'bullets',
                    dynamicBullets: false,
                },

                navigation: {
                    nextEl: document.querySelector('.swiper-button-next-' + sliderId),
                    prevEl: document.querySelector('.swiper-button-prev-' + sliderId),
                },

                preloadImages: true,
            });

            // Initialize inner swipers for profile cards with multiple images
            swiperElement.querySelectorAll('[class*="profile-swiper-"]').forEach(function(innerSwiperEl) {
                const profileId = innerSwiperEl.className.match(/profile-swiper-(\d+)/)?.[1];
                if (!profileId) return;

                // Destroy existing instance if any
                if (innerSwiperEl.swiper) {
                    innerSwiperEl.swiper.destroy(true, true);
                }

                // Initialize new Swiper instance for profile images
                new Swiper(innerSwiperEl, {
                    loop: false,
                    slidesPerView: 1,
                    spaceBetween: 0,
                    centeredSlides: false,
                    slidesOffsetBefore: 0,
                    slidesOffsetAfter: 0,
                    initialSlide: 0,
                    pagination: {
                        el: innerSwiperEl.querySelector('.swiper-pagination-' + profileId),
                        clickable: true,
                        dynamicBullets: true,
                    },
                    preloadImages: true,
                });
            });
        }

        // Initialize on DOMContentLoaded or immediately if DOM is ready
        // Also listen for swiper:ready event in case the ES module loads after DOMContentLoaded
        function tryInit() {
            if (typeof Swiper !== 'undefined') {
                initProfileSlider();
            } else {
                // Swiper module not yet available — wait for it
                document.addEventListener('swiper:ready', initProfileSlider, { once: true });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', tryInit);
        } else {
            tryInit();
        }

        // Re-initialize when Livewire navigates
        document.addEventListener('livewire:navigated', function() {
            setTimeout(initProfileSlider, 100);
        });

        // For Livewire v3 - when content is updated via AJAX
        if (typeof Livewire !== 'undefined') {
            Livewire.hook('morph.updated', () => {
                setTimeout(initProfileSlider, 100);
            });
        }

        // Re-initialize on window resize
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(initProfileSlider, 250);
        });
    })();
</script>
@endpush

@push('styles')
<style>
    .profile-slider-container [class*="swiper-button-prev-"],
    .profile-slider-container [class*="swiper-button-next-"] {
        border: none !important;
        background: #DD3888 !important;
        color: #fff !important;
        padding: 0 !important;
        box-shadow: 0 10px 18px rgba(221, 56, 136, 0.25) !important;
    }

    .profile-slider-container [class*="swiper-button-prev-"]:hover,
    .profile-slider-container [class*="swiper-button-next-"]:hover {
        background: #c71d6f !important;
    }

    /* Hide nav buttons on desktop, show only below on mobile */
    .slider-nav-controls-{{ $sliderId }} {
        margin-top: 12px;
        width: 100%;
    }

    @media (min-width: 768px) {
        .slider-nav-controls-{{ $sliderId }} {
            display: none;
        }
    }

    /* Pagination styling */
    .profile-slider-container .swiper-pagination,
    .slider-nav-controls-{{ $sliderId }} .swiper-pagination {
        position: static;
        bottom: auto;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        margin: 0;
        width: auto !important;
        flex: 0 0 auto;
        transform: none !important;
        left: auto !important;
    }

    .profile-slider-container .swiper-pagination-bullet {
        width: 8px;
        height: 8px;
        background: #E0E0E0 !important;
        opacity: 1 !important;
        border-radius: 50% !important;
    }

    .profile-slider-container .swiper-pagination-bullet-active {
        background: #DD3888 !important;
        opacity: 1 !important;
    }

    @media (max-width: 425px) {
        .profile-slider-container .swiper-slide {
            width: calc(50% - 4px) !important;
            flex: 0 0 calc(50% - 4px) !important;
        }
    }
</style>
@endpush
