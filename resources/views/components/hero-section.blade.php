@props([
    'title' => '',
    'subtitle' => '',
    'showSearch' => false,
    'backgroundImage' => '/images/header.png',
])

<style>
    .hero-bg {
        width: min(1331px, calc(100% - 24px));
        height: 602px;
        margin: 0 auto;
        border-bottom-left-radius: 24px;
        border-bottom-right-radius: 24px;
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
        overflow: visible;
        position: relative;
        z-index: 30;
    }

    .hero-bg .hero-inner {
        height: 100%;
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
        line-height: 1.08;
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
        font-size: 26px;
        line-height: 1.3;
        color: #5C5C5C;
        max-width: 430px;
        margin-bottom: 0;
    }

    .hero-copy-block {
        transform: translateX(15px) translateY(45px);
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

    @media (max-width: 640px) {
        .hero-bg {
            width: 100%;
            height: 463px;
            min-height: 463px;
            background-image: none !important;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
            overflow: visible !important;
        }

        .hero-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url('/images/icons/mobile/mobile.png');
            background-repeat: no-repeat;
            background-size: 488px 463px;
            background-position: center top;
            filter: blur(5px);
            clip-path: inset(0 round 0 0 24px 24px);
            z-index: 0;
            pointer-events: none;
        }

        .hero-main-title { font-size: 36px; }
        .hero-subtitle { font-size: 18px; }
        .hero-inner { display: flex !important; flex-direction: column !important; }
        .hero-copy-block { order: 1 !important; transform: none !important; padding-bottom: 0 !important; }
        .hero-text-block { width: 100%; min-height: auto; }
        .hero-search-wrap { order: 2 !important; transform: translateY(40px) !important; margin-top: 0 !important; }
    }
</style>

<div class="hero-bg homepage-hero" style="background-image: url('{{ $backgroundImage }}');">
    <div class="hero-inner container mx-auto px-4 pt-16 md:pt-24 pb-8 flex flex-col min-h-[420px] md:min-h-[520px]">
        <div class="max-w-2xl px-4 md:pl-16 py-10 md:py-16 hero-copy-block hero-animate hero-animate-delay-1">
            <div class="hero-text-block">
                <h1 class="hero-main-title py-4 md:py-5">
                    {!! $title !!}
                </h1>

                <p class="hero-subtitle">
                    {!! $subtitle !!}
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
