@props(['profile', 'gallerySlides'])

<div class="vip-lightbox fixed inset-0 z-[90] bg-[rgba(255,255,255,0.74)] backdrop-blur-[16px] hidden items-center justify-center p-[24px] max-[767px]:p-[12px]" id="vip-lightbox">
    <div class="relative w-full max-w-[1120px] flex flex-col items-center gap-[16px]">
        <div class="relative w-full flex items-center justify-center px-[75px]">
            <div class="swiper vip-lightbox-swiper w-full">
                <div class="swiper-wrapper">
                    {{-- JS will populate this --}}
                </div>
            </div>
        </div>

        <button type="button" class="absolute -top-[8px] -right-[8px] z-[3] border-0 w-[45px] h-[45px] rounded-[8px] bg-primary-500 text-white shadow-[0_16px_28px_rgba(221,56,136,0.24)] cursor-pointer" id="vip-lightbox-close" aria-label="Close">X</button>
        <button type="button" class="absolute top-1/2 -translate-y-1/2 z-[3] border-0 w-[45px] h-[45px] rounded-[8px] bg-primary-500 text-white shadow-[0_16px_28px_rgba(221,56,136,0.24)] cursor-pointer left-[30px] max-[767px]:hidden" id="vip-lightbox-prev" aria-label="Previous">&#10094;</button>
        <button type="button" class="absolute top-1/2 -translate-y-1/2 z-[3] border-0 w-[45px] h-[45px] rounded-[8px] bg-primary-500 text-white shadow-[0_16px_28px_rgba(221,56,136,0.24)] cursor-pointer right-[30px] max-[767px]:hidden" id="vip-lightbox-next" aria-label="Next">&#10095;</button>

        <div class="flex gap-[8px] justify-center w-full">
            {{-- Thumbnails --}}
        </div>
    </div>
</div>
