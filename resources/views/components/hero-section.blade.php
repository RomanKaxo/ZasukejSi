@props([
    'title' => '',
    'subtitle' => '',
    'showSearch' => false,
    // JPEG q95 4:4:4 of the former 3.6 MB header.png (mean channel difference < 0.5 of 255): 337 KB.
    'backgroundImage' => '/images/header.jpg',
    'compact' => false,
])

@if($compact)
{{-- Results state after a search (Figma home-1.jpg / home-21.jpg): no photo
     or claim, just a #F9F9F9 1331x278 panel under the header with the
     search card on y 100 (x 393–1526). On a phone the card simply follows
     the 96 px header. --}}
<style>
    .hero-compact {
        width: min(1331px, calc(100% - 24px));
        height: 278px;
        margin: 0 auto;
        background: #F9F9F9;
        border-bottom-left-radius: 24px;
        border-bottom-right-radius: 24px;
        position: relative;
        z-index: 30;
    }

    .hero-compact__card {
        position: absolute;
        top: 100px;
        left: 50%;
        transform: translateX(-50%);
        width: min(1134px, calc(100vw - 48px));
        z-index: 60;
    }

    @media (max-width: 1024px) {
        .hero-compact { height: auto; padding: 120px 0 24px; }
        .hero-compact__card { position: static; transform: none; margin: 0 auto; }
    }

    @media (max-width: 767px) {
        .hero-compact { width: 100%; padding: 96px 0 0; background: #FFFFFF; border-radius: 0; }
        .hero-compact__card { width: 100%; }
        /* phone 360 px-15: no counter pills over the results card. */
        .hero-compact .search-hero-badges { display: none !important; }
        /* phone 360 px-15: heading ink 33 px under the card's top edge. */
        .hero-compact .search-hero-card { padding-top: 32px !important; }
    }
</style>
<div class="hero-compact">
    <div class="hero-compact__card">
        <livewire:search-profiles />
    </div>
</div>
@else

<style>
    .hero-bg {
        width: min(1331px, calc(100% - 24px));
        height: 602px;
        margin: 0 auto;
        border-bottom-left-radius: 24px;
        border-bottom-right-radius: 24px;
        /* Figma home.jpg crops the photo 77 px further left than a centred
           cover (the "BIG" watermarks sit at x 788 / 1056 / 1326). */
        background-position: calc(50% - 77px) center;
        background-size: cover;
        background-repeat: no-repeat;
        overflow: visible;
        position: relative;
        z-index: 30;
    }

    .hero-bg .hero-inner {
        width: min(1331px, 100%);
        height: 477px;
        position: relative;
        z-index: 31;
    }

    .hero-search-wrap {
        margin-top: auto;
        padding-left: 0;
        padding-right: 0;
        transform: translateY(90px);
        position: relative;
        z-index: 60;
    }

    .hero-main-title {
        font-family: 'Poppins', sans-serif;
        font-weight: 800;
        font-size: 50px;
        /* Figma home.jpg: lines 59 px apart, "Jsme komunita lidí," inks
           489 px wide (-1.2 px tracking). */
        line-height: 58px;
        letter-spacing: -1.2px;
        color: #5C2D62;
    }

    .hero-main-title .hero-main-highlight {
        color: #DD3888;
        white-space: nowrap;
    }

    .hero-main-title .hero-main-period {
        color: #5C2D62;
    }

    .hero-subtitle {
        font-family: 'Poppins', sans-serif;
        font-weight: 400;
        font-size: 20px;
        line-height: 30px;
        color: #5C5C5C;
        max-width: 430px;
        margin-bottom: 0;
    }

    .hero-copy-block {
        /* The reveal animation owns `transform`, so the offset is positional:
           title ink starts at x 443 / y 181 in the 1920 frame. */
        position: relative;
        left: 42px;
        top: -2px;
    }

    @media (min-width: 768px) {
        /* Subtitle cap line at y 319, 30 px leading (Figma home.jpg). */
        .hero-subtitle { margin-top: -2px; }
        /* The reveal animation overrides the translate above on desktop, so
           the card is placed by offset: top edge on y 424 (Figma home.jpg). */
        .hero-search-wrap { top: -14px; }
    }

    .hero-text-block {
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: min(750px, 100%);
        min-height: 112px;
    }

    .homepage-hero {
        isolation: isolate;
        box-shadow: 0 32px 80px rgba(92, 45, 98, 0.14);
    }

    .homepage-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        border-bottom-left-radius: 24px;
        border-bottom-right-radius: 24px;
        background: linear-gradient(120deg, rgba(255,255,255,0.18), rgba(255,255,255,0) 42%);
        opacity: 0.75;
        pointer-events: none;
        animation: heroGlow 10s ease-in-out infinite alternate;
    }

    .hero-animate {
        opacity: 0;
        transform: translateY(32px) scale(0.98);
        animation: heroReveal 760ms cubic-bezier(.2,.9,.3,1) forwards;
    }

    .hero-animate-delay-1 { animation-delay: 120ms; }
    .hero-animate-delay-2 { animation-delay: 260ms; }

    @keyframes heroReveal {
        from { opacity: 0; transform: translateY(32px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    @keyframes heroGlow {
        from { opacity: 0.45; transform: translateX(-2%) scale(1); }
        to { opacity: 0.92; transform: translateX(2%) scale(1.03); }
    }

    @media (max-width: 1024px) {
        .hero-bg { width: calc(100% - 12px); height: auto; min-height: 520px; }
    }

    /* Phone layout, up to the tablet breakpoint (md = 768px). It used to stop
       at 640px (photo, gutters) and 425px (room for the counter pills), so an
       iPhone Plus / Pro Max at 430px — and anything up to 767px — got a
       half-tablet hero with no counters. Measured against the 360px phone
       frame (phone 360 px-11), 1:1. */
    @media (max-width: 767px) {
        .hero-bg {
            width: 100%;
            height: 463px;
            min-height: 463px;
            background-image: none !important;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
            overflow: visible !important;
        }

        /* The frame's photo: 488x463, starting under the 96px header and
           hanging 76px past the right edge (x = -52 at 360px). The design shows
           it lightly blurred under a 50% white veil — registered against the
           export it is 0.5 x photo + 0.5 x white with a ~2.5px blur. It runs
           on under the search card, which covers its lower edge. */
        .hero-bg::before {
            content: '';
            position: absolute;
            top: 96px;
            left: 0;
            right: 0;
            height: 463px;
            background-image: linear-gradient(rgba(255,255,255,0.5), rgba(255,255,255,0.5)), url('/images/icons/mobile/mobile.jpg'); /* was a 1.1 MB PNG */
            background-repeat: no-repeat;
            background-size: 100% 100%, 488px 463px;
            background-position: 0 0, right -76px top 0;
            filter: blur(2.5px);
            clip-path: inset(0);
            z-index: 0;
            pointer-events: none;
        }

        /* The animated sheen is a desktop flourish; on the phone it lightened
           the photo away from the design's tone. */
        .homepage-hero::after { display: none; }
        .homepage-hero { box-shadow: none; }

        /* Four lines in the frame: "Jsme / komunita / lidí, co rádi /
           šukají." The <br> is a desktop line break, so it is hidden here and
           the 250px measure does the wrapping. Baselines are 41-42px apart. */
        .hero-main-title { font-size: 36px; line-height: 41.67px; letter-spacing: 0; max-width: 250px; padding-top: 14.6px !important; }
        .hero-main-title br,
        .hero-subtitle br { display: none; }
        /* Three subtitle lines, 28px apart, 20px type, wrapping at the same
           words as the frame ("…ještě / dnes a … nové / zákazníky."). */
        .hero-subtitle { font-size: 20px; line-height: 28px; max-width: 250px; margin-top: 4px; margin-left: 1px; }
        .hero-inner { display: flex !important; flex-direction: column !important; }
        /* 16px from .hero-inner + 9px here = the 25px gutter the phone frame
           uses for the heading, subtitle and counter pills alike. */
        .hero-copy-block { order: 1 !important; transform: none !important; left: 0; top: 0; padding-bottom: 0 !important; padding-left: 9px !important; padding-right: 9px !important; }
        .hero-text-block { width: 100%; min-height: auto; }
        /* The counter pills hang above the card (search-profiles), so the card
           clears the subtitle by the space they occupy. */
        .hero-search-wrap { order: 2 !important; transform: translateY(136px) !important; margin-top: 0 !important; }
    }
</style>

<div class="hero-bg homepage-hero" style="background-image: url('{{ $backgroundImage }}');">
    <div class="hero-inner container mx-auto px-4 pt-16 md:pt-24 pb-8 flex flex-col min-h-[420px] md:min-h-[520px]">
        <div class="max-w-2xl px-4 md:pl-16 py-10 md:py-16 hero-copy-block hero-animate hero-animate-delay-1">
            <div class="hero-text-block">
                <h1 class="hero-main-title py-4 md:py-5">
                    {!! str_replace('<br>', ' <br>', $title) !!}
                </h1>

                <p class="hero-subtitle">
                    {!! str_replace('<br>', ' <br>', $subtitle) !!}
                </p>
            </div>
        </div>

        @if($showSearch)
            <div class="hero-search-wrap hero-animate hero-animate-delay-2">
                <livewire:search-profiles />
            </div>
        @else
            {{ $slot }}
        @endif
    </div>
</div>
@endif
