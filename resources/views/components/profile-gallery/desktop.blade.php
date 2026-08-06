@props(['profile', 'gallerySlides'])

{{-- Desktop 3-card grid --}}
<div class="hidden min-[840px]:grid grid-cols-[minmax(180px,0.88fr),minmax(280px,1.18fr),minmax(150px,0.78fr)] gap-[14px] items-stretch relative min-[1280px]:grid-cols-[337px,537px,337px]">
    
    @foreach([0, 1, 2] as $index)
        <button type="button" 
                class="relative w-full h-full border-0 rounded-[26px] overflow-hidden p-0 cursor-pointer bg-[#eee7f0] lightbox-trigger transition-opacity duration-300" 
                data-index="{{ $index }}">
            <img src="{{ $gallerySlides[$index] ?? asset('images/models/vip'.($index+1).'.png') }}" 
                 alt="{{ $profile->display_name }}" 
                 class="w-full h-full object-cover block transition-opacity duration-300">
        </button>
    @endforeach

    @if($gallerySlides->count() > 1)
        <button type="button" class="absolute top-1/2 -translate-y-1/2 z-[5] w-[45px] h-[45px] border-0 rounded-[8px] bg-primary-500 text-white inline-flex items-center justify-center -left-[14px]" id="vip-gallery-desktop-prev" aria-label="Previous slide">
            <svg class="w-4 h-4 rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button type="button" class="absolute top-1/2 -translate-y-1/2 z-[5] w-[45px] h-[45px] border-0 rounded-[8px] bg-primary-500 text-white inline-flex items-center justify-center -right-[14px]" id="vip-gallery-desktop-next" aria-label="Next slide">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </button>
    @endif
</div>
