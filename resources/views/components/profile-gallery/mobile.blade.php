@props(['profile', 'gallerySlides'])

<div class="block min-[840px]:hidden">
    @if($gallerySlides->count() > 1)
        <button type="button" class="absolute top-1/2 -translate-y-1/2 z-[3] w-[34px] h-[34px] border-0 rounded-[10px] bg-primary-500 text-white inline-flex items-center justify-center shadow-[0_14px_26px_rgba(221,56,136,0.25)] -left-[15px]" aria-label="Previous image">&#10094;</button>
        <button type="button" class="absolute top-1/2 -translate-y-1/2 z-[3] w-[34px] h-[34px] border-0 rounded-[10px] bg-primary-500 text-white inline-flex items-center justify-center shadow-[0_14px_26px_rgba(221,56,136,0.25)] -right-[15px]" aria-label="Next image">&#10095;</button>

        <div class="swiper vip-profile-gallery-swiper overflow-visible">
            <div class="swiper-wrapper">
                @foreach($gallerySlides as $index => $imageUrl)
                    <div class="swiper-slide opacity-70 scale-94 transition-[transform,opacity] duration-200">
                        <button type="button" class="relative w-full h-[410px] border-0 rounded-[26px] overflow-hidden p-0 bg-[#eee7f0] cursor-pointer block lightbox-trigger" data-index="{{ $index }}">
                            <img src="{{ $imageUrl }}" alt="{{ $profile->display_name }}" class="w-full h-full object-cover transition-[transform,opacity] duration-200">
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif($gallerySlides->count() === 1)
        <button type="button" class="relative w-full h-[410px] border-0 rounded-[26px] overflow-hidden p-0 bg-[#eee7f0] cursor-pointer block lightbox-trigger" data-index="0">
            <img src="{{ $gallerySlides->first() }}" alt="{{ $profile->display_name }}" class="w-full h-full object-cover">
        </button>
    @else
        <div class="relative w-full h-[410px] border-0 rounded-[26px] overflow-hidden p-0 bg-[#eee7f0] cursor-pointer block flex items-center justify-center">
            <svg class="h-20 w-20 text-[#d6c7dc]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
    @endif
</div>
