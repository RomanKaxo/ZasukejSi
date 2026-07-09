<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['profile']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['profile']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $images = $profile->getAllImages();
    $gallerySlides = $images->map(fn ($image) => $image->getUrl())->values();
    $galleryFallbacks = collect([
        asset('images/models/model6.png'),
        asset('images/models/model10.png'),
        asset('images/models/model12.png'),
    ]);
    while ($gallerySlides->count() < 3) {
        $gallerySlides->push($galleryFallbacks[$gallerySlides->count() % $galleryFallbacks->count()]);
    }
    $averageRating = $profile->getAverageRating();
    $totalRatings = $profile->getTotalRatings();
    $isNewProfile = $totalRatings === 0 || optional($profile->created_at)->gt(now()->subDays(30));
    $contacts = collect($profile->contacts ?? []);
    $phoneContact = $contacts->firstWhere('type', 'phone');
    $whatsAppContact = $contacts->firstWhere('type', 'whatsapp');
    $telegramContact = $contacts->firstWhere('type', 'telegram');
    $prices = collect($profile->local_prices ?? [])->filter(fn ($price) => filled($price['time_hours'] ?? null))->values();
    $displayPrices = $prices->isNotEmpty()
        ? $prices
        : collect([
            ['time_hours' => 0.5, 'incall_price' => 4000, 'outcall_price' => null],
            ['time_hours' => 1, 'incall_price' => 6000, 'outcall_price' => null],
            ['time_hours' => 2, 'incall_price' => 14000, 'outcall_price' => null],
            ['time_hours' => 3, 'incall_price' => 18000, 'outcall_price' => null],
        ]);
    $displayServices = ($profile->services && $profile->services->count() > 0)
        ? $profile->services->pluck('name')
        : collect(['Běžné fotografie', 'Vaginální sex', 'Páry', 'Běžné fotografie', 'Páry', 'Výstřik na tělo', 'Lízaní', 'Nadávání', 'Výstřik na tělo', 'Lízaní', 'Lízaní', 'Nadávání', 'Dominantní', 'Erotická masáž']);
    $languages = $profile->languages ?? 'Česky, Rusky, Anglicky';
    $aboutText = trim((string) ($profile->about ?? '')) !== '' ? $profile->about : 'Profesionální VIP profil připravený pro nové klienty.';
    $weightLbs = $profile->weight_lbs ?? ($profile->weight ? (string) round($profile->weight * 2.20462) : null);
    $heightFeet = $profile->height_feet ?? null;
    $videoPoster = $images->first()?->getUrl() ?: asset('images/models/model16.png');
    $messageRouteAvailable = \Illuminate\Support\Facades\Route::has('messages.show');
    $registerRouteAvailable = \Illuminate\Support\Facades\Route::has('register');
    $availabilityEntries = collect($profile->availability_hours ?? [])->filter(function ($value) {
        return filled($value);
    })->values();
    $availabilityStart = '18';
    $availabilityEnd = '18';
    $availabilityCaption = 'Každý den';
    $isVerifiedProfile = $profile->isVerified();
    $photoStatusLabel = $isVerifiedProfile ? 'FOTO OVĚŘENO' : 'FOTO NEOVĚŘENO';

    if ($availabilityEntries->isNotEmpty()) {
        $rawAvailability = $availabilityEntries->first();

        if (is_array($rawAvailability)) {
            $rawAvailability = implode(' - ', array_values($rawAvailability));
        }

        if (preg_match('/(\d{1,2})(?::\d{2})?\D+(\d{1,2})(?::\d{2})?/', (string) $rawAvailability, $matches)) {
            $availabilityStart = $matches[1];
            $availabilityEnd = $matches[2];
        } elseif ($availabilityEntries->count() >= 2) {
            $availabilityStart = (string) $availabilityEntries->get(0);
            $availabilityEnd = (string) $availabilityEntries->get(1);
        } elseif (filled($rawAvailability)) {
            $availabilityCaption = (string) $rawAvailability;
        }
    }

    $heroSlides = $gallerySlides->take(3)->values();
    while ($heroSlides->count() < 3) {
        $heroSlides->push($galleryFallbacks[$heroSlides->count() % $galleryFallbacks->count()]);
    }
?>

<style>
    .vip-profile-page {
        position: relative;
        max-width: 1564px;
        margin: 0 auto;
        padding: 32px 20px 96px 200px;
        overflow: visible;
    }

    .vip-profile-page::before,
    .vip-profile-page::after {
        display: none;
    }

    .vip-profile-page > * {
        position: relative;
        z-index: 1;
    }

    .vip-profile-hero {
        display: grid;
        grid-template-columns: 198px minmax(0, 1fr);
        gap: 58px;
        align-items: start;
        margin-bottom: 64px;
    }

    .vip-profile-panel {
        background: #ffffff;
        border-radius: 24px;
        padding: 14px 14px 18px;
        position: sticky;
        top: 106px;
        width: 100%;
        display: flex;
        flex-direction: column;
    }

    .vip-profile-status-bar {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .vip-profile-status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 93px;
        min-width: 93px;
        height: 30px;
        border-radius: 8px;
        padding: 0 10px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        line-height: 1;
        white-space: nowrap;
    }

    .vip-profile-status-pill::before {
        content: none;
    }

    .vip-profile-status-pill--primary {
        background: #FFB81C;
        color: #ffffff;
    }


    .vip-profile-status-pill--verification {
        width: 131px;
        min-width: 131px;
        background: #E8E8E8;
        color: #A4A4A4;
        font-family: 'Poppins', sans-serif;
        font-size: 10px;
    }

    .vip-profile-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 12px;
    }

    .vip-profile-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .vip-profile-chip--warm {
        background: linear-gradient(135deg, #ffcf48 0%, #ffb700 100%);
        color: #ffffff;
    }

    .vip-profile-chip--soft {
        background: #f5f5f7;
        color: #8c8795;
    }

    .vip-profile-name {
        margin: 0 0 10px;
        font-family: 'Poppins', sans-serif;
        font-size: 38px;
        line-height: 1;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #5c2d62;
        text-align: center;
    }

    .vip-profile-links {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 8px 16px;
        margin: 0 0 18px;
    }

    .vip-profile-link {
        color: #71717A;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.2;
        text-decoration: underline;
        text-decoration-thickness: 1px;
        text-underline-offset: 2px;
    }

    .vip-profile-rating-summary {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        align-items: stretch;
        gap: 1px;
        margin-bottom: 14px;
        padding: 0;
        border: 0;
        border-radius: 12px;
        overflow: hidden;
        background: #f2f2f2;
        font-size: 13px;
        color: #505050;
    }

    .vip-profile-rating-summary strong {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        padding: 0 12px;
        background: #f7f7f7;
        color: #505050;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        font-weight: 600;
        line-height: 1;
    }

    .vip-profile-rating-icons {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 36px;
        padding: 0 12px;
        background: #f2f2f2;
        color: #505050;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        font-weight: 600;
        line-height: 1;
    }

    .vip-profile-meta-location {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-bottom: 12px;
        color: #505050;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        font-weight: 600;
        text-align: center;
    }

    .vip-profile-meta-location img {
        width: 20px;
        height: 20px;
        flex: 0 0 20px;
    }

    .vip-profile-meta-table {
        display: grid;
        gap: 18px;
        margin-bottom: 16px;
    }

    .vip-profile-meta-row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding-bottom: 8px;
        font-size: 13px;
        position: relative;
        padding-bottom: 16px;
    }

    .vip-profile-meta-row::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 231px;
        height: 1px;
        background-color: #f3edf5;
    }

    .vip-profile-meta-row:last-child {
        padding-bottom: 0;
    }

    .vip-profile-meta-row:last-child::after {
        display: none;
    }

    .vip-profile-meta-label {
        color: #dd3888;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.2;
    }

    .vip-profile-meta-value {
        text-align: right;
        color: #505050;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        font-weight: 400;
        line-height: 1.2;
    }

    .vip-profile-flags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 16px;
    }

    .vip-profile-flag {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 113px;
        min-width: 113px;
        height: 40px;
        border-radius: 8px;
        padding: 0 12px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        font-weight: 600;
        line-height: 1;
        box-sizing: border-box;
    }

    .vip-profile-flag--incall {
        background: #e9ffeb;
        color: #dd3888;
    }

    .vip-profile-flag--outcall {
        background: transparent;
        color: #a6a6a6;
    }

    .vip-profile-flag-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        flex: 0 0 20px;
    }

    .vip-profile-flag-status img {
        width: 20px;
        height: 20px;
        display: block;
    }

    .vip-profile-flag-status--no {
        color: inherit;
    }

    .vip-profile-message {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 231px;
        height: 50px;
        gap: 8px;
        border-radius: 8px;
        padding: 0;
        background: #DD3888;
        color: #ffffff;
        font-size: 16px;
        font-weight: 600;
        font-family: 'Poppins', sans-serif;
        text-decoration: none;
        transition: transform 180ms ease;
        margin-top: 12px;
    }

    .vip-profile-message img {
        width: 20px;
        height: 20px;
        filter: none;
    }

    .vip-profile-contacts {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }

    .vip-profile-contact-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 999px;
        text-decoration: none;
    }

    .vip-profile-contact-circle--whatsapp {
        background: #25D366;
    }

    .vip-profile-contact-circle--telegram {
        background: #2AABEE;
    }

    .vip-profile-phone {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #505050;
        font-size: 16px;
        font-weight: 600;
        font-family: 'Plus Jakarta Sans', sans-serif;
        text-decoration: none;
    }

    .vip-profile-main {
        min-width: 0;
        padding-top: 120px;
    }

    .vip-profile-gallery-card {
        position: relative;
        margin-bottom: 28px;
    }

    .vip-profile-gallery-mobile {
        display: block;
    }

    .vip-gallery-desktop {
        display: none;
    }

    .vip-profile-gallery-card .swiper {
        overflow: visible;
    }

    .vip-gallery-slide {
        position: relative;
        width: 100%;
        height: 410px;
        border: 0;
        border-radius: 26px;
        overflow: hidden;
        padding: 0;
        background: #eee7f0;
        cursor: pointer;
        display: block;
    }

    .vip-gallery-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 220ms ease, opacity 220ms ease;
    }

    .vip-profile-gallery-swiper .swiper-slide {
        opacity: 0.7;
        transform: scale(0.94);
        transition: transform 220ms ease, opacity 220ms ease;
    }

    .vip-profile-gallery-swiper .swiper-slide-active,
    .vip-profile-gallery-swiper .swiper-slide-next,
    .vip-profile-gallery-swiper .swiper-slide-prev {
        opacity: 1;
    }

    .vip-profile-gallery-swiper .swiper-slide-active {
        transform: scale(1);
    }

    .vip-gallery-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 3;
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 10px;
        background: #dd3888;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 14px 26px rgba(221, 56, 136, 0.25);
    }

    .vip-gallery-nav--prev {
        left: -15px;
    }

    .vip-gallery-nav--next {
        right: -15px;
    }

    .vip-gallery-desktop-card {
        position: relative;
        width: 100%;
        height: 100%;
        border: 0;
        border-radius: 26px;
        overflow: hidden;
        padding: 0;
        cursor: pointer;
        background: #eee7f0;
    }

    .vip-gallery-desktop-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .vip-gallery-desktop-main {
        grid-column: 2;
        grid-row: 1 / span 2;
        min-height: 462px;
    }

    .vip-gallery-desktop-left {
        grid-column: 1;
        grid-row: 1 / span 2;
        min-height: 462px;
    }

    .vip-gallery-desktop-right {
        grid-column: 3;
        grid-row: 1 / span 2;
        min-height: 462px;
    }

    .vip-gallery-desktop-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 5;
        width: 45px;
        height: 45px;
        border: 0;
        border-radius: 8px;
        background: #dd3888;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .vip-gallery-desktop-nav.vip-gallery-desktop-prev {
        left: -20px !important;
    }

    .vip-gallery-desktop-nav.vip-gallery-desktop-next {
        right: 50px !important;
    }

    .vip-profile-favorite {
        position: absolute;
        top: 0;
        left: 16px;
        z-index: 4;
    }

    .vip-profile-favorite button {
        width: 80px !important;
        height: 90px !important;
        padding: 0 !important;
        border-radius: 0 0 8px 8px !important;
        background: #FFFFFF !important;
        color: #d54b92 !important;
        border: none !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
    }

    .vip-profile-favorite button span {
        display: block !important;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px !important;
        font-weight: 600 !important;
        color: #71717A !important;
        margin-top: 2px;
    }

    .vip-profile-static-favorite {
        width: 80px;
        height: 90px;
        border-radius: 0 0 8px 8px;
        border: 0;
        background: #FFFFFF;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    .vip-profile-static-favorite img {
        width: 38px !important;
        height: 38px !important;
        transition: opacity 0.2s ease, transform 0.2s ease;
    }

    .vip-profile-static-favorite img.heart-animate {
        animation: heartSwap 0.4s ease-in-out;
    }

    @keyframes heartSwap {
        0% {
            opacity: 1;
            transform: scale(1);
        }
        50% {
            opacity: 0;
            transform: scale(0.8);
        }
        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    .vip-profile-static-favorite::after {
        content: 'uložit';
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        font-weight: 600;
        color: #71717A;
        margin-top: 2px;
    }

    .vip-about-card {
        margin-bottom: 34px;
    }

    .vip-section-title {
        margin: 0 0 14px;
        color: #5c2d62;
        font-size: 34px;
        line-height: 0.98;
        font-weight: 800;
        letter-spacing: -0.04em;
    }

    .vip-about-copy {
        font-family: 'Poppins', sans-serif;
        font-weight: 400;
        font-size: 14px;
        color: #5C5C5C;
        line-height: 1.85;
        max-width: 820px;
    }

    .vip-media-grid {
        display: grid;
        grid-template-columns: 242px minmax(0, 1fr);
        gap: 26px;
        align-items: start;
        margin-bottom: 72px;
    }

    .vip-media-grid--single {
        grid-template-columns: minmax(0, 1fr);
    }

    .vip-video-card,
    .vip-pricing-card,
    .vip-cta,
    .vip-slider-shell {
        background: #ffffff;
        border-radius: 28px;
    }

    .vip-video-card {
        padding: 0;
        overflow: hidden;
        box-shadow: 0 24px 54px rgba(92, 45, 98, 0.08);
    }

    .vip-pricing-card h3 {
        margin: 0 0 18px;
        font-family: 'Poppins', sans-serif;
        color: #5C2D62;
        font-size: 24px;
        font-weight: 700;
        letter-spacing: 0;
    }

    .vip-video-card-title {
        padding: 0 0 24px;
        font-family: 'Poppins', sans-serif;
        color: #5C2D62;
        font-size: 24px;
        font-weight: 700;
        letter-spacing: 0;
    }

    .vip-video-surface {
        position: relative;
        width: 254px;
        height: 460px;
        overflow: hidden;
        border-radius: 15px;
        background: #241c2a;
    }

    .vip-video-surface video,
    .vip-video-surface img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .vip-video-play {
        position: absolute;
        inset: 0;
        border: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.06) 0%, rgba(0, 0, 0, 0.24) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .vip-video-play__inner {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: #5C2D62;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .vip-pricing-card {
        padding: 10px 0 0;
        background: transparent;
        border-radius: 0;
    }

    .vip-pricing-head,
    .vip-pricing-body,
    .vip-services-wrap {
        padding-left: 0;
        padding-right: 0;
    }

    .vip-pricing-table {
        width: 526px;
        border-collapse: collapse;
    }

    .vip-pricing-table th,
    .vip-pricing-table td {
        padding: 12px 6px;
        border-bottom: 1px solid #f0e7f3;
        font-size: 14px;
    }

    .vip-pricing-table th {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 600;
        color: #505050;
        text-align: center;
        padding: 0;
        border-bottom: none;
    }

    .vip-pricing-table th:first-child {
        background: #F2F2F2;
        border-radius: 8px;
        width: 137px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .vip-pricing-table th:nth-child(2),
    .vip-pricing-table th:nth-child(3),
    .vip-pricing-table td:nth-child(2),
    .vip-pricing-table td:nth-child(3) {
        text-align: center;
    }

    .vip-pricing-table th:nth-child(2) .vip-price-pill,
    .vip-pricing-table th:nth-child(3) .vip-price-pill {
        margin: 0 auto;
    }

    .vip-pricing-table td:first-child {
        text-align: center;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #DD3888;
        font-weight: 600;
        font-size: 14px;
    }

    .vip-pricing-table td:nth-child(2),
    .vip-pricing-table td:nth-child(3) {
        text-align: center;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #505050;
        font-weight: 600;
        font-size: 14px;
    }

    .vip-price-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 8px;
        background: #f7f4f8;
        padding: 8px 12px;
        width: auto;
        height: 40px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 600;
        font-size: 14px;
    }

    .vip-pricing-table th:nth-child(2) .vip-price-pill {
        background: #E9FFEB;
        color: #DD3888;
        width: 188px;
    }

    .vip-pricing-table th:nth-child(3) .vip-price-pill {
        background: transparent;
        color: #A6A6A6;
        width: 187px;
    }

    .vip-services-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 16px;
        width: 526px;
    }

    .vip-service-pill {
        border-radius: 999px;
        border: 2px solid #F2F2F2;
        padding: 8px 14px;
        color: #505050;
        font-family: 'Poppins', sans-serif;
        font-size: 11px;
        font-weight: 500;
        background: #ffffff;
    }

    .vip-slider-section {
        margin-bottom: 70px;
        margin-left: 315px;
        overflow: visible;
    }

    .vip-slider-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 22px;
    }

    .vip-slider-kicker {
        color: #dd3888;
        font-size: 30px;
        line-height: 0.95;
        font-weight: 800;
        letter-spacing: -0.04em;
    }

    .vip-slider-note {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #d5a31b;
        font-size: 14px;
        font-weight: 600;
        white-space: nowrap;
    }

    .vip-rec-slider {
        position: relative;
        width: 1050px;
        height: 540px;
        max-width: 100%;
        overflow: visible;
    }

    .vip-rec-slider .profile-slider-container,
    .vip-rec-slider .swiper {
        width: 1050px !important;
        height: 540px !important;
        max-width: 100%;
    }

    .vip-rec-slider [class*="swiper-button-prev-"],
    .vip-rec-slider [class*="swiper-button-next-"] {
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        cursor: pointer;
    }

    .vip-rec-slider [class*="swiper-button-prev-"] {
        left: -60px;
    }

    .vip-rec-slider [class*="swiper-button-next-"] {
        right: -60px;
    }

    .vip-rec-slider [class*="swiper-button-prev-"] > div,
    .vip-rec-slider [class*="swiper-button-next-"] > div {
        width: 45px;
        height: 45px;
        border-radius: 8px;
        background: #dd3888;
        box-shadow: 0 14px 26px rgba(221, 56, 136, 0.25);
    }

    .vip-rec-slider .swiper-wrapper {
        padding-top: 6px;
        padding-bottom: 18px;
        gap: 0px;
    }

    .vip-video-section {
        margin-top: 20px;
        margin-left: 135px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 24px;
    }

    .vip-video-heading {
        text-align: left;
        display: flex;
        flex-direction: column;
        gap: 0;
        margin-left: -335px;
    }

    .vip-video-heading-line1 {
        font-family: 'Poppins', sans-serif !important;
        font-weight: 700 !important;
        font-size: 36px !important;
        color: #5C2D62 !important;
        line-height: 1.2 !important;
    }

    .vip-video-heading-line2 {
        font-family: 'Poppins', sans-serif !important;
        font-weight: 700 !important;
        font-size: 36px !important;
        color: #DD3888 !important;
        line-height: 1.2 !important;
    }

    .vip-video-container {
        position: relative;
        width: 850px;
        height: 450px;
        border-radius: 15px;
        background: linear-gradient(rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5)), url('<?php echo e(asset('images/models/vipVideo2.png')); ?>') center/cover;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .vip-video-play-btn {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: #5C2D62;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 14px 26px rgba(92, 45, 98, 0.25);
        cursor: pointer;
    }

    .vip-video-badges {
        display: flex;
        gap: 24px;
        justify-content: center;
    }

    .vip-video-register-btn {
        width: 460px;
        height: 60px;
        border-radius: 8px;
        background: #DD3888;
        border: none;
        color: #FFFFFF;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: background 200ms ease;
    }

    .vip-video-register-btn:hover {
        background: #c4286f;
    }

    .vip-video-badge {
        width: 268px;
        height: 85px;
        border-radius: 8px;
        background: #FFFFFF;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 12px;
        padding: 12px 16px;
        box-shadow: 0 12px 24px rgba(92, 45, 98, 0.06);
    }

    .vip-video-badge img {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
    }

    .vip-video-badge span {
        flex: 1;
        text-align: left;
    }

    .vip-cta {
        padding: 22px;
        background: linear-gradient(180deg, #ffffff 0%, #fbf8fc 100%);
        box-shadow: 0 24px 54px rgba(92, 45, 98, 0.08);
        margin-top: 20px;
    }

    .vip-cta-media {
        position: relative;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
        overflow: hidden;
        border-radius: 24px;
        height: 220px;
        margin-bottom: 18px;
    }

    .vip-cta-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        filter: saturate(0.82);
    }

    .vip-cta-play {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 82px;
        height: 82px;
        border-radius: 999px;
        background: #6f2d77;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 24px 40px rgba(111, 45, 119, 0.25);
    }

    .vip-cta-benefits {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 22px;
    }

    .vip-cta-benefit {
        display: flex;
        align-items: center;
        gap: 12px;
        border-radius: 18px;
        background: #ffffff;
        padding: 16px 18px;
        box-shadow: 0 12px 24px rgba(92, 45, 98, 0.06);
        color: #7a7380;
        font-size: 13px;
        font-weight: 500;
    }

    .vip-cta-register {
        display: block;
        width: min(320px, 100%);
        margin: 0 auto;
        border-radius: 12px;
        padding: 14px 16px;
        background: linear-gradient(135deg, #dd3888 0%, #c72f7a 100%);
        color: #ffffff;
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 16px 28px rgba(221, 56, 136, 0.24);
    }

    .vip-lightbox {
        position: fixed;
        inset: 0;
        z-index: 90;
        background: rgba(255, 255, 255, 0.74);
        backdrop-filter: blur(16px);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    .vip-lightbox.is-open {
        display: flex;
    }

    .vip-lightbox-stage {
        position: relative;
        width: min(1120px, 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
    }

    .vip-lightbox-main {
        position: relative;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 75px;
    }

    .vip-lightbox-swiper {
        width: 100%;
    }

    .vip-lightbox .swiper-slide {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .vip-lightbox .swiper-slide img {
        max-width: 100%;
        max-height: 60vh;
        object-fit: contain;
        border-radius: 24px;
    }

    .vip-lightbox-close,
    .vip-lightbox-prev,
    .vip-lightbox-next {
        position: absolute;
        z-index: 3;
        border: 0;
        width: 45px;
        height: 45px;
        border-radius: 8px;
        background: #dd3888;
        color: #ffffff;
        box-shadow: 0 16px 28px rgba(221, 56, 136, 0.24);
        cursor: pointer;
    }

    .vip-lightbox-close {
        top: -8px;
        right: -8px;
    }

    .vip-lightbox-prev,
    .vip-lightbox-next {
        top: 50%;
        transform: translateY(-50%);
    }

    .vip-lightbox-prev {
        left: 30px;
    }

    .vip-lightbox-next {
        right: 30px;
    }

    .vip-lightbox-thumbnails {
        display: flex;
        gap: 8px;
        justify-content: center;
        width: 100%;
    }

    .vip-lightbox-thumbnail {
        width: 80px;
        height: 123px;
        border-radius: 8px;
        border: 2px solid transparent;
        background: none;
        padding: 0;
        cursor: pointer;
        overflow: hidden;
        opacity: 0.3;
        transition: opacity 0.2s ease;
    }

    .vip-lightbox-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .vip-lightbox-thumbnail.active {
        border-color: #dd3888;
        opacity: 1;
    }

    .blurred {
        position: relative;
    }

    .blurred img {
        filter: blur(10px);
    }

    .blurred [class*="name"],
    .blurred [class*="title"] {
        filter: blur(10px);
    }

    .blurred::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 70px;
        height: 70px;
        background: white;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }

    .blurred .lock-icon {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 32px;
        height: 32px;
        z-index: 11;
    }

    @media (max-width: 1100px) {
        .vip-profile-hero {
            grid-template-columns: 1fr;
        }

        .vip-profile-panel {
            position: static;
            max-width: 520px;
        }

        .vip-media-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (min-width: 840px) {
        .vip-gallery-desktop {
            position: relative;
            display: grid;
            grid-template-columns: minmax(180px, 0.88fr) minmax(280px, 1.18fr) minmax(150px, 0.78fr);
            gap: 14px;
            align-items: stretch;
        }

        .vip-profile-gallery-mobile {
            display: none;
        }

        .vip-gallery-nav {
            display: none;
        }

        .vip-profile-links {
            display: none;
        }
    }

    @media (min-width: 1280px) {
        .vip-profile-hero {
            grid-template-columns: 261px minmax(0, 1239px);
            gap: 58px;
        }

        .vip-profile-panel {
            width: 261px;
            min-width: 261px;
            height: 575px;
            padding: 12px;
        }


        .vip-profile-status-bar {
            margin-bottom: 16px;
        }

        .vip-profile-badges {
            gap: 6px;
            margin-bottom: 10px;
        }

        .vip-profile-chip {
            padding: 4px 8px;
            font-size: 8px;
        }

        .vip-profile-name {
            margin-bottom: 10px;
            font-size: 36px;
            line-height: 1.05;
            letter-spacing: -0.02em;
        }

        .vip-profile-rating-summary {
            margin-bottom: 10px;
            font-size: 12px;
        }

        .vip-profile-meta-location {
            gap: 6px;
            margin-bottom: 10px;
            font-size: 12px;
        }

        .vip-profile-meta-table {
            gap: 18px;
            margin-bottom: 12px;
        }

        .vip-profile-meta-row {
            gap: 12px;
            padding-bottom: 6px;
            font-size: 12px;
        }

        .vip-profile-flags {
            gap: 8px;
            margin-bottom: 12px;
        }

        .vip-profile-flag {
            width: 113px;
            min-width: 113px;
            height: 40px;
            border-radius: 8px;
            padding: 0 10px;
            font-size: 12px;
        }

        .vip-profile-flag-status {
            width: 20px;
            height: 20px;
        }

        .vip-profile-message {
            width: 231px;
            height: 50px;
            border-radius: 8px;
            padding: 0;
            font-size: 16px;
        }

        .vip-profile-contacts {
            gap: 8px;
            margin-top: 8px;
        }

        .vip-profile-contact-circle {
            width: 40px;
            height: 40px;
        }

        .vip-profile-phone {
            gap: 8px;
            font-size: 16px;
        }

        .vip-profile-main {
            width: 1239px;
            max-width: 1239px;
        }

        .vip-gallery-desktop {
            grid-template-columns: 337px 537px 337px;
            gap: 14px;
        }

        .vip-gallery-desktop-left,
        .vip-gallery-desktop-main,
        .vip-gallery-desktop-right {
            min-height: 537px;
            height: 537px;
        }
    }

    @media (max-width: 767px) {
        .vip-profile-page {
            padding: 18px 14px 64px;
        }

        .vip-section-title,
        .vip-slider-kicker {
            font-size: 28px;
        }

        .vip-profile-panel {
            padding: 8px 10px 14px;
        }


        .vip-profile-status-bar {
            margin-bottom: 16px;
        }

        .vip-profile-badges {
            display: none;
        }

        .vip-profile-name {
            margin-bottom: 14px;
            font-size: 36px;
        }

        .vip-profile-rating-summary {
            margin-bottom: 12px;
        }

        .vip-profile-meta-location {
            margin-bottom: 14px;
        }

        .vip-profile-meta-table {
            margin-bottom: 14px;
        }

        .vip-profile-gallery-card {
            margin-left: -4px;
            margin-right: -4px;
        }

        .vip-profile-availability-hours {
            font-size: 34px;
        }

        .vip-gallery-slide {
            height: 280px;
            border-radius: 20px;
        }

        .vip-gallery-nav--prev {
            left: 6px;
        }

        .vip-gallery-nav--next {
            right: 6px;
        }

        .vip-slider-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .vip-slider-note {
            white-space: normal;
        }

        .vip-cta-benefits {
            grid-template-columns: 1fr;
        }

        .vip-cta-media {
            height: 180px;
        }

        .vip-lightbox {
            padding: 12px;
        }

        .vip-lightbox-prev,
        .vip-lightbox-next {
            display: none;
        }
    }

    /* Mobile layout for 425px and lower */
    @media (max-width: 425px) {
        .vip-profile-page {
            max-width: 425px;
            padding: 64px 0 48px 0 !important;
            margin: 0 auto;
            overflow: visible;
        }

        .vip-profile-hero {
            display: grid !important;
            grid-template-columns: 1fr auto;
            column-gap: 6px;
            row-gap: 8px;
            align-items: start;
            margin-bottom: 12px !important;
            padding: 0 25px 0 25px;
            width: 100%;
            box-sizing: border-box;
        }

        .vip-profile-panel,
        .vip-profile-main,
        .vip-profile-gallery-card,
        .vip-media-grid {
            display: contents !important;
        }

        .vip-profile-availability-card,
        .vip-gallery-desktop {
            display: none !important;
        }

        .vip-profile-status-bar {
            grid-column: 1;
            grid-row: 1;
            gap: 6px;
            margin: 0;
            align-self: center;
            justify-content: flex-start;
            flex-wrap: nowrap;
            display: flex;
            align-items: center;
            min-height: 30px;
        }

        .vip-profile-status-pill {
            height: 30px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            white-space: nowrap;
            padding: 0 !important;
            font-family: 'Poppins', sans-serif;
            flex-shrink: 0;
        }

        .vip-profile-status-pill--primary {
            width: 100px;
            min-width: 100px;
            background: #FFB700;
            color: #FFFFFF;
        }

        .vip-profile-status-pill--primary img {
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
        }

        .vip-profile-status-pill--verification {
            width: 131px;
            min-width: 131px;
            background: #E8E8E8;
            color: #A4A4A4;
        }

        .vip-profile-status-pill--verification img {
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
        }

        .vip-profile-favorite {
            position: relative;
            top: auto;
            left: auto;
            grid-column: 2;
            grid-row: 1;
            justify-self: end;
            align-self: center;
            z-index: 2;
        }

        .vip-profile-favorite button,
        .vip-profile-static-favorite {
            width: 38px !important;
            min-width: 38px !important;
            height: 38px !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 20px rgba(221, 56, 136, 0.14);
            gap: 0 !important;
            padding: 0 !important;
            background: #FFFFFF;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
        }

        .vip-profile-favorite button span,
        .vip-profile-static-favorite::after {
            display: none !important;
        }

        .vip-profile-static-favorite img,
        .vip-profile-favorite button img {
            width: 20px !important;
            height: 20px !important;
        }

        .vip-profile-name {
            grid-column: 1 / -1;
            grid-row: 2;
            margin: 8px 0 0 0;
            text-align: left;
            font-size: 38px;
            line-height: 1.05;
            font-weight: 800;
            color: #5C2D62;
        }

        .vip-profile-links {
            grid-column: 1 / -1;
            grid-row: 3;
            justify-content: flex-start;
            margin: 8px 0 0;
        }

        .vip-profile-gallery-mobile {
            grid-column: 1 / -1;
            grid-row: 4;
            width: 310px;
            margin: 12px auto 0;
            display: flex;
            justify-content: center;
        }

        .vip-profile-gallery-swiper {
            width: 310px !important;
            margin: 0 !important;
            display: flex;
            justify-content: center;
            overflow: visible !important;
        }

        .vip-profile-gallery-swiper .swiper-wrapper {
            display: flex !important;
            gap: 3px !important;
            align-items: stretch;
        }

        .vip-profile-gallery-swiper .swiper-slide {
            width: 310px !important;
            flex: 0 0 310px !important;
            opacity: 1 !important;
            transform: none !important;
            display: flex;
            justify-content: center;
            margin: 0 !important;
            padding: 0 !important;
        }

        .vip-gallery-slide {
            width: 310px !important;
            height: 443px !important;
            border-radius: 12px;
            object-fit: cover;
        }

        .vip-gallery-nav {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            background: #DD3888;
            border: none;
            color: white;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 5;
        }

        .vip-gallery-nav--prev {
            left: -50px;
        }

        .vip-gallery-nav--next {
            right: -50px;
        }

        .vip-profile-rating-summary,
        .vip-profile-meta-location,
        .vip-profile-meta-table,
        .vip-profile-flags,
        .vip-profile-message,
        .vip-profile-contacts,
        .vip-about-card,
        .vip-video-card-wrap,
        .vip-pricing-card {
            grid-column: 1 / -1;
            width: 100%;
            max-width: 425px;
            margin-left: auto;
            margin-right: auto;
        }

        .vip-profile-rating-summary {
            grid-row: 5;
            margin-top: 12px;
            margin-bottom: 0;
            font-size: 12px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1px;
            border-radius: 8px;
            overflow: hidden;
        }

        .vip-profile-meta-location {
            grid-row: 6;
            margin-top: 12px;
            margin-bottom: 0;
            justify-content: center;
            gap: 6px;
            padding: 0;
            background: transparent;
            border-radius: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #505050;
        }

        .vip-profile-meta-table {
            grid-row: 7;
            gap: 0;
            margin-top: 12px;
            margin-bottom: 0;
            padding: 0 12px;
            background: white;
        }

        .vip-profile-meta-row {
            padding: 12px 0;
            font-size: 12px;
            gap: 12px;
        }

        .vip-profile-meta-row::after {
            width: 100%;
            height: 1px;
            background: #F0F0F0;
        }

        .vip-profile-meta-label {
            color: #DD3888;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
        }

        .vip-profile-meta-value {
            text-align: right;
            color: #505050;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
        }

        .vip-profile-flags {
            grid-row: 8;
            margin-top: 12px;
            margin-bottom: 0;
            gap: 8px;
            display: flex;
            flex-wrap: wrap;
        }

        .vip-profile-flag {
            flex: 1 1 calc(50% - 4px);
            min-width: calc(50% - 4px);
            height: 36px;
            padding: 0 12px;
            font-size: 12px;
            gap: 6px;
            border-radius: 8px;
            border: 1.5px solid #F2F2F2;
        }

        .vip-profile-flag--incall {
            background: #E9FFEB;
            color: #DD3888;
            border: none !important;
            border-color: transparent !important;
        }

        .vip-profile-flag--outcall {
            background: transparent;
            color: #A6A6A6;
            border: none !important;
            border-color: transparent !important;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
        }

        .vip-profile-flag-status,
        .vip-profile-flag-status img {
            width: 16px;
            height: 16px;
        }

        .vip-profile-message {
            grid-row: 9;
            width: 100%;
            max-width: 425px;
            margin-top: 12px;
            height: 40px;
            font-size: 13px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #DD3888;
            color: white;
            text-decoration: none;
        }

        .vip-profile-contacts {
            grid-row: 10;
            margin-top: 12px;
            gap: 12px;
            justify-content: center;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }

        .vip-profile-contact-circle {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            background: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: none;
        }

        .vip-profile-contact-circle--whatsapp {
            background: #25D366;
        }

        .vip-profile-contact-circle--telegram {
            background: #2AABEE;
        }

        .vip-profile-contact-circle img {
            width: 20px !important;
            height: 20px !important;
        }

        .vip-profile-phone {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 16px;
            font-weight: 600;
            color: #505050;
            text-decoration: none;
            gap: 4px;
            display: flex;
            align-items: center;
        }

        .vip-video-card-wrap {
            grid-row: 11;
        }

        .vip-video-card-title {
            padding: 0;
            margin: 0 0 10px;
            font-size: 24px;
        }

        .vip-video-card {
            background: transparent;
            border-radius: 0;
            box-shadow: none;
        }

        .vip-video-surface {
            width: 300px;
            height: 560px;
            border-radius: 10px;
            margin: 0 auto;
        }

        .vip-about-card {
            grid-row: 12;
            margin-bottom: 0;
        }

        .vip-section-title {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .vip-about-copy {
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 400;
            color: #5C5C5C;
            line-height: 1.7;
        }

        .vip-pricing-card {
            grid-row: 13;
            display: flex;
            flex-direction: column;
            gap: 24px;
            padding: 0;
        }

        .vip-services-block {
            order: 1;
            margin-top: 12px !important;
            width: 100%;
            max-width: 425px;
            margin-left: auto;
            margin-right: auto;
        }

        .vip-prices-block {
            order: 2;
            width: 100%;
            max-width: 425px;
            margin-left: auto;
            margin-right: auto;
            margin-top: 12px;
        }

        .vip-pricing-card h3 {
            margin: 0 0 12px;
            font-size: 18px;
            font-weight: 700;
            color: #5C2D62;
        }

        .vip-about-card {
            margin-top: 12px;
            width: 100%;
            max-width: 425px;
            margin-left: auto;
            margin-right: auto;
        }

        .vip-about-card h3 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .vip-about-card p {
            font-size: 13px;
            line-height: 1.6;
        }

        .vip-services-grid,
        .vip-pricing-table {
            width: 100%;
        }

        .vip-services-grid {
            gap: 8px;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
        }

        .vip-service-pill {
            padding: 8px 14px;
            font-size: 11px;
            font-weight: 600;
            background: white;
            border: 1.5px solid #E0E0E0;
            border-radius: 20px;
            color: #505050;
            white-space: nowrap;
        }

        .vip-pricing-table {
            width: 307px;
            border-collapse: separate;
            border-spacing: 0 6px;
            font-size: 12px;
        }

        .vip-pricing-table th {
            text-align: center;
            padding: 0 0 5px 0;
            background: transparent;
            border-bottom: 1px solid #E8E8E8;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #505050;
        }

        .vip-pricing-table th:first-child {
            width: 80px;
            min-width: 80px;
            height: 40px;
            text-align: left;
        }

        .vip-pricing-table th:nth-child(2) {
            padding-left: 6px;
        }

        .vip-pricing-table th:nth-child(3) {
            padding-right: 6px;
        }

        .vip-pricing-table tbody tr {
            height: 38px;
        }

        .vip-pricing-table td {
            padding: 6px 8px;
            text-align: center;
            border-bottom: 1px solid #F0F0F0;
            color: #505050;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
        }

        .vip-pricing-table td:last-child,
        .vip-pricing-table td:nth-child(2) {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #505050;
        }

        .vip-pricing-table td:first-child {
            text-align: center;
            font-weight: 600;
        }

        .vip-price-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0 10px;
            gap: 6px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            line-height: 1;
            background: white;
            border: none;
            border-radius: 8px;
        }

        .vip-pricing-table th:first-child .vip-price-pill,
        .vip-pricing-table td:first-child .vip-price-pill {
            width: 80px !important;
            min-width: 80px !important;
            height: 40px !important;
            border-radius: 9px;
            background: #F2F2F2;
            border: none;
            color: #505050;
        }

        .vip-pricing-table th:nth-child(2) .vip-price-pill,
        .vip-pricing-table td:nth-child(2) .vip-price-pill {
            width: 110px;
            min-width: 110px;
            height: 40px;
            border-radius: 8px;
            background: #E9FFEB;
            border: none;
            color: #DD3888;
        }

        .vip-pricing-table th:nth-child(3) .vip-price-pill,
        .vip-pricing-table td:nth-child(3) .vip-price-pill {
            width: 110px;
            min-width: 110px;
            height: 40px;
            border-radius: 8px;
            background: transparent;
            border: none;
            color: #A6A6A6;
        }

        .vip-pricing-table .vip-price-pill img {
            width: 20px !important;
            height: 20px !important;
            flex: 0 0 20px;
        }

        .vip-pricing-table th:nth-child(2) .vip-price-pill,
        .vip-pricing-table th:nth-child(3) .vip-price-pill {
            width: 110px;
            min-width: 110px;
            height: 40px;
        }
        .vip-pricing-table td:nth-child(2),
        .vip-pricing-table td:nth-child(3) {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #505050;
            padding: 0 8px 5px 8px;
        }

        .vip-slider-section {
            margin: 12px auto 24px;
            width: 314px;
            max-width: 314px;
            overflow: hidden;
        }

        .vip-rec-slider {
            width: 100%;
            max-width: 425px;
            height: auto;
        }

        .vip-rec-slider .profile-slider-container,
        .vip-rec-slider .swiper {
            width: 100% !important;
            max-width: 425px !important;
            height: auto !important;
        }

        .vip-rec-slider [class*="swiper-button-prev-"] {
            position: static;
            left: auto;
            transform: none;
            display: inline-flex;
            margin-top: 8px;
        }

        .vip-rec-slider [class*="swiper-button-next-"] {
            position: static;
            right: auto;
            transform: none;
            display: inline-flex;
            margin-top: 8px;
        }

        .vip-rec-slider .swiper-pagination {
            position: relative;
            bottom: auto;
            display: flex;
            justify-content: center;
            gap: 6px;
            margin-top: 10px;
        }

        .vip-rec-slider .swiper-pagination-bullet {
            width: 8px;
            height: 8px;
            background: #E0E0E0 !important;
            opacity: 1 !important;
        }

        .vip-rec-slider .swiper-pagination-bullet-active {
            background: #DD3888 !important;
            opacity: 1 !important;
        }

        .vip-video-section {
            margin: 12px auto 0 !important;
            width: 313px !important;
            max-width: 313px !important;
            align-items: stretch;
            gap: 12px;
            display: flex;
            flex-direction: column;
        }

        .vip-video-heading {
            width: 100%;
            max-width: 425px;
            margin: 0 !important;
            padding: 0;
        }

        .vip-video-heading-line1 {
            font-size: 28px !important;
            font-weight: 700 !important;
            color: #5C2D62 !important;
            margin: 0 !important;
        }

        .vip-video-heading-line2 {
            font-size: 28px !important;
            font-weight: 700 !important;
            color: #DD3888 !important;
            margin: 0 !important;
        }

        .vip-video-container {
            width: 310px !important;
            max-width: 310px !important;
            height: 165px !important;
            margin: 0 !important;
            border-radius: 12px;
        }

        .vip-video-play-btn {
            width: 60px !important;
            height: 60px !important;
            background: #5C2D62 !important;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .vip-video-play-btn img {
            width: 23px !important;
            height: 23px !important;
        }

        .vip-video-badges {
            display: grid;
            gap: 10px;
            width: 313px !important;
            max-width: 313px !important;
            margin: 0;
        }

        .vip-video-badge {
            width: 313px !important;
            min-height: 75px;
            height: 75px;
            padding: 10px 12px;
            gap: 8px;
            justify-content: center;
            background: #FFFFFF;
            border-radius: 8px;
            border: 1px solid #F0F0F0;
            box-sizing: border-box;
        }

        .vip-video-badge img {
            width: 40px !important;
            height: 40px !important;
            flex-shrink: 0;
        }

        .vip-video-badge span {
            width: 207px !important;
            height: 15px !important;
            font-size: 14px !important;
            line-height: 1.3;
            text-align: left !important;
        }

        .vip-video-register-btn {
            width: 313px !important;
            max-width: 313px !important;
            height: 60px !important;
            margin: 0 !important;
            font-size: 16px !important;
            border-radius: 8px;
            background: #DD3888;
            color: white;
        }
    }
</style>

<div class="vip-profile-page">
    <section class="vip-profile-hero">
        <aside class="vip-profile-panel">
            <div class="vip-profile-status-bar">
                <span class="vip-profile-status-pill vip-profile-status-pill--primary">
                    <img src="<?php echo e(asset('images/icons/star.svg')); ?>" alt="star" style="width: 18px; height: 18px; margin-right: 6px;">
                    VIP PROFIL
                </span>
                <span class="vip-profile-status-pill vip-profile-status-pill--verification">
                    <img src="<?php echo e(asset('images/icons/CameraOff.svg')); ?>" alt="camera-off" style="width: 18px; height: 18px; margin-right: 4px;">
                    <?php echo e($photoStatusLabel); ?>

                </span>
            </div>


            <h1 class="vip-profile-name"><?php echo e($profile->display_name ?? 'Alexandrina'); ?></h1>

            <div class="vip-profile-links lg:hidden" aria-label="Profilové akce">
                <a href="#" class="vip-profile-link">Obnovit přístup</a>
                <a href="#" class="vip-profile-link">Dát hodnocení</a>
                <a href="#" class="vip-profile-link">Nahlásit</a>
            </div>

            <div class="vip-profile-rating-summary">
                <strong><?php echo e(__('front.profiles.list.rating')); ?></strong>
                <span class="vip-profile-rating-icons">
                    <?php if($totalRatings > 0): ?>
                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="color:#FFC107;">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span><?php echo e(number_format($averageRating, 1)); ?></span>
                    <?php else: ?>
                        <?php if (isset($component)) { $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons','data' => ['name' => 'lock','class' => 'inline-block','style' => 'width:18px;height:18px;color:#FF4DA6;']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'lock','class' => 'inline-block','style' => 'width:18px;height:18px;color:#FF4DA6;']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd)): ?>
<?php $attributes = $__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd; ?>
<?php unset($__attributesOriginal114a4750071386a6a5d0e0f9aca3c6cd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd)): ?>
<?php $component = $__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd; ?>
<?php unset($__componentOriginal114a4750071386a6a5d0e0f9aca3c6cd); ?>
<?php endif; ?>
                    <?php endif; ?>
                </span>
            </div>

            <div class="vip-profile-meta-location">
                <img src="<?php echo e(asset('images/icons/location.svg')); ?>" alt="" aria-hidden="true">
                <span><?php echo e($profile->city ?? 'Jihomoravský kraj'); ?></span>
            </div>

            <div class="vip-profile-meta-table">
                <div class="vip-profile-meta-row">
                    <span class="vip-profile-meta-label">Věk</span>
                    <span class="vip-profile-meta-value"><?php echo e($profile->age ?? '19'); ?> let</span>
                </div>
                <div class="vip-profile-meta-row">
                    <span class="vip-profile-meta-label">Váha</span>
                    <span class="vip-profile-meta-value">
                        <?php echo e(($profile->weight ?? '57') . ' kg' . ($weightLbs ? ' / ' . $weightLbs . ' lbs' : '')); ?>

                    </span>
                </div>
                <div class="vip-profile-meta-row">
                    <span class="vip-profile-meta-label">Výška</span>
                    <span class="vip-profile-meta-value">
                        <?php echo e(($profile->height ?? '168') . ' cm' . ($heightFeet ? ' / ' . $heightFeet : '')); ?>

                    </span>
                </div>
                <div class="vip-profile-meta-row">
                    <span class="vip-profile-meta-label">Prsa</span>
                    <span class="vip-profile-meta-value"><?php echo e($profile->bust_size ?? 'C'); ?></span>
                </div>
                <div class="vip-profile-meta-row">
                    <span class="vip-profile-meta-label">Jazyky</span>
                    <span class="vip-profile-meta-value"><?php echo e($languages); ?></span>
                </div>
            </div>

            <div class="vip-profile-flags">
                <div class="vip-profile-flag vip-profile-flag--incall">
                    <span class="vip-profile-flag-status">
                        <img src="<?php echo e(asset('images/icons/CircleCheck.svg')); ?>" alt="" aria-hidden="true">
                    </span>
                    <span>InCall</span>
                </div>
                <div class="vip-profile-flag vip-profile-flag--outcall">
                    <span class="vip-profile-flag-status">
                        <img src="<?php echo e(asset('images/icons/CircleX.svg')); ?>" alt="" aria-hidden="true">
                    </span>
                    <span>OutCall</span>
                </div>
            </div>

            <?php if(auth()->guard()->check()): ?>
                <?php if($profile->user_id && $profile->user_id !== auth()->id() && $messageRouteAvailable): ?>
                    <a href="<?php echo e(route('messages.show', $profile->user)); ?>" class="vip-profile-message">
                        Poslat zprávu
                        <img src="<?php echo e(asset('images/icons/message.svg')); ?>" alt="" aria-hidden="true" class="h-4 w-4 brightness-0 invert">
                    </a>
                <?php else: ?>
                    <span class="vip-profile-message" style="opacity:.6;pointer-events:none;">Poslat zprávu</span>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?php echo e($registerRouteAvailable ? route('register') : route('profiles.index')); ?>" class="vip-profile-message">
                    Poslat zprávu
                    <img src="<?php echo e(asset('images/icons/message.svg')); ?>" alt="" aria-hidden="true" class="h-4 w-4 brightness-0 invert">
                </a>
            <?php endif; ?>

            <div class="vip-profile-contacts">
                <a class="vip-profile-contact-circle vip-profile-contact-circle--whatsapp" href="https://wa.me/420737155457" target="_blank" rel="noreferrer" title="WhatsApp">
                    <img src="<?php echo e(asset('images/icons/whatsapp.svg')); ?>" alt="whatsapp" style="width: 20px; height: 20px;">
                </a>
                <a class="vip-profile-contact-circle vip-profile-contact-circle--telegram" href="https://t.me/alexandraprofil" target="_blank" rel="noreferrer" title="Telegram">
                    <img src="<?php echo e(asset('images/icons/telegram.svg')); ?>" alt="telegram" style="width: 20px; height: 20px;">
                </a>
                <a href="tel:+420737155457" class="vip-profile-phone">
                    <span>+420 737 155 457</span>
                </a>
            </div>
        </aside>

        <div class="vip-profile-main">
            <div class="vip-profile-gallery-card">
                <div class="vip-profile-favorite">
                    <?php if(auth()->guard()->check()): ?>
                        <?php if(auth()->user()->isMale()): ?>
                            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('favorite-button', ['profile' => $profile]);

$__html = app('livewire')->mount($__name, $__params, 'favorite-'.$profile->id, $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
                        <?php else: ?>
                            <button type="button" class="vip-profile-static-favorite">
                                <img src="<?php echo e(asset('images/icons/heart.svg')); ?>" alt="" aria-hidden="true">
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <button type="button" class="vip-profile-static-favorite">
                            <img src="<?php echo e(asset('images/icons/heart.svg')); ?>" alt="" aria-hidden="true">
                        </button>
                    <?php endif; ?>
                </div>

                <div class="vip-gallery-desktop">
                    <button type="button" class="vip-gallery-desktop-card vip-gallery-desktop-left lightbox-trigger" data-index="0">
                        <img src="<?php echo e(asset('images/models/vip1.png')); ?>" alt="<?php echo e($profile->display_name); ?>">
                    </button>
                    <button type="button" class="vip-gallery-desktop-card vip-gallery-desktop-main lightbox-trigger" data-index="1">
                        <img src="<?php echo e(asset('images/models/vip2.png')); ?>" alt="<?php echo e($profile->display_name); ?>">
                    </button>
                    <button type="button" class="vip-gallery-desktop-card vip-gallery-desktop-right lightbox-trigger" data-index="2">
                        <img src="<?php echo e(asset('images/models/vip3.png')); ?>" alt="<?php echo e($profile->display_name); ?>">
                    </button>
                    <?php if($gallerySlides->count() > 1): ?>
                        <button type="button" class="vip-gallery-desktop-nav vip-gallery-desktop-prev" id="vip-gallery-desktop-prev" aria-label="Previous slide">&#10094;</button>
                        <button type="button" class="vip-gallery-desktop-nav vip-gallery-desktop-next" id="vip-gallery-desktop-next" aria-label="Next slide">&#10095;</button>
                    <?php endif; ?>
                </div>

                <div class="vip-profile-gallery-mobile">
                    <?php if($gallerySlides->count() > 1): ?>
                        <button type="button" class="vip-gallery-nav vip-gallery-nav--prev" aria-label="Previous image">&#10094;</button>
                        <button type="button" class="vip-gallery-nav vip-gallery-nav--next" aria-label="Next image">&#10095;</button>

                        <div class="swiper vip-profile-gallery-swiper">
                            <div class="swiper-wrapper">
                                <?php $__currentLoopData = $gallerySlides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $imageUrl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="swiper-slide">
                                        <button type="button" class="vip-gallery-slide lightbox-trigger" data-index="<?php echo e($index); ?>">
                                            <img src="<?php echo e($imageUrl); ?>" alt="<?php echo e($profile->display_name); ?>">
                                        </button>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php elseif($gallerySlides->count() === 1): ?>
                        <button type="button" class="vip-gallery-slide lightbox-trigger" data-index="0">
                            <img src="<?php echo e($gallerySlides->first()); ?>" alt="<?php echo e($profile->display_name); ?>">
                        </button>
                    <?php else: ?>
                        <div class="vip-gallery-slide" style="display:flex;align-items:center;justify-content:center;">
                            <svg class="h-20 w-20 text-[#d6c7dc]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <section class="vip-about-card">
                <h2 class="vip-section-title">Více o mně</h2>
                <div class="vip-about-copy">FOTO je original – koukni na můj web 🙂  MUJ INSTAGRAM    @arianka.a_     KDE NAJDEŠ MUJ ONLYFANS? NA INSTAGRAMU 🔥  Vítej můj drahý  ☺️  Já jsem Sára Pocházím z České republiky, 🙂 Jsem jedinečná zkušenost, pokud hledáš jedinečné vzrušující potěšení, dobrodružství, jsem tu pro tvoje potřeby. Jako důvěrná, diskrétní, elegantní dáma, která ráda sdílí potěšení, štěstí, jsem připravená společně vytvorit nezapomenutelné okamžiky. Jsem skutečný mladý poklad. Moje teplo, elegance a vášeň učiní  náš společný čas zvlástním. Miluji setkání s novými lidmi a užívám si kazdou chvíli naplno. Napiš mi na WhatsApp, můj  drahý ! Rychle reaguji a muzeme snadno Uspořádat Schůzku, abychom společně zažily něco opravdu úžasného.  🙂</div>
            </section>

            <?php if($profile->hasVideo() || $displayPrices->isNotEmpty() || $displayServices->isNotEmpty()): ?>
                <section class="vip-media-grid">
                    <div class="vip-video-card-wrap">
                        <h3 class="vip-video-card-title">Video</h3>
                        <div class="vip-video-card">
                            <div class="vip-video-surface">
                                <?php if($profile->hasVideo()): ?>
                                    <video id="vip-profile-video" src="<?php echo e($profile->getVideoUrl()); ?>" preload="metadata" playsinline poster="<?php echo e(asset('images/models/vipVideo.png')); ?>"></video>
                                <?php else: ?>
                                    <img src="<?php echo e(asset('images/models/vipVideo.png')); ?>" alt="<?php echo e($profile->display_name); ?>">
                                <?php endif; ?>
                                <button type="button" class="vip-video-play" id="vip-profile-video-toggle" aria-label="Play video">
                                    <span class="vip-video-play__inner">
                                        <img id="vip-profile-video-play-icon" src="<?php echo e(asset('images/icons/arrowFilled.svg')); ?>" alt="" class="w-7 h-7" style="filter: brightness(0) invert(1); transform: rotate(180deg);">
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="vip-pricing-card">
                        <?php if($displayPrices->isNotEmpty()): ?>
                            <div class="vip-prices-block">
                                <h3>Moje ceny</h3>
                                <table class="vip-pricing-table">
                                    <thead>
                                        <tr>
                                            <th><span class="vip-price-pill">Čas</span></th>
                                            <th>
                                                <span class="vip-price-pill">
                                                    <?php if($profile->incall || $prices->isEmpty()): ?>
                                                        <img src="<?php echo e(asset('images/icons/CircleCheck.svg')); ?>" alt="" aria-hidden="true" class="w-5 h-5">
                                                    <?php else: ?>
                                                        <img src="<?php echo e(asset('images/icons/CircleX.svg')); ?>" alt="" aria-hidden="true" class="w-5 h-5">
                                                    <?php endif; ?>
                                                    InCall
                                                </span>
                                            </th>
                                            <th>
                                                <span class="vip-price-pill">
                                                    <?php if($profile->outcall): ?>
                                                        <img src="<?php echo e(asset('images/icons/CircleCheck.svg')); ?>" alt="" aria-hidden="true" class="w-5 h-5">
                                                    <?php else: ?>
                                                        <img src="<?php echo e(asset('images/icons/CircleX.svg')); ?>" alt="" aria-hidden="true" class="w-5 h-5">
                                                    <?php endif; ?>
                                                    OutCall
                                                </span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $displayPrices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $price): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e(rtrim(rtrim((string) ($price['time_hours'] ?? ''), '0'), '.')); ?>h</td>
                                                <td>
                                                    <?php if(!empty($price['incall_price']) && ($profile->incall || $prices->isEmpty())): ?>
                                                        <?php echo e(number_format($price['incall_price'], 0, ',', ' ')); ?> Kč
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if(!empty($price['outcall_price']) && $profile->outcall): ?>
                                                        <?php echo e(number_format($price['outcall_price'], 0, ',', ' ')); ?> Kč
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <?php if($displayServices->isNotEmpty()): ?>
                            <div class="vip-services-block" style="margin-top:24px;">
                                <h3 style="margin-bottom:14px;">Služby</h3>
                                <div class="vip-services-grid">
                                    <?php $__currentLoopData = $displayServices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $serviceName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="vip-service-pill"><?php echo e($serviceName); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </section>

    <section class="vip-slider-section">
        <div class="vip-slider-head">
            <div>
                <h2 class="vip-section-title" style="margin-bottom:4px;">Nejlépe hodnocené dívky</h2>
                <div class="vip-slider-kicker">tento měsíc</div>
            </div>
            <div class="vip-slider-note">
                <img src="<?php echo e(asset('images/icons/diamond.svg')); ?>" alt="" aria-hidden="true" class="h-4 w-4">
                <span>Premium účet vám odemkne hodnocení</span>
            </div>
        </div>
        <div class="vip-rec-slider">
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('profile-slider', ['sortBy' => 'rating_this_month','sortDirection' => 'desc','limit' => 30,'cardVariant' => 'vip-detail']);

$__html = app('livewire')->mount($__name, $__params, 'vip-month-'.$profile->id, $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        </div>
    </section>

    <section class="vip-slider-section">
        <div class="vip-slider-head">
            <div>
                <h2 class="vip-section-title" style="margin-bottom:4px;">Nejlépe hodnocené dívky</h2>
                <div class="vip-slider-kicker">za celou dobu</div>
            </div>
            <div class="vip-slider-note">
                <img src="<?php echo e(asset('images/icons/diamond.svg')); ?>" alt="" aria-hidden="true" class="h-4 w-4">
                <span>Premium účet vám odemkne hodnocení</span>
            </div>
        </div>
        <div class="vip-rec-slider">
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('profile-slider', ['sortBy' => 'rating_this_month','sortDirection' => 'desc','limit' => 30,'cardVariant' => 'vip-detail']);

$__html = app('livewire')->mount($__name, $__params, 'vip-all-'.$profile->id, $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        </div>
    </section>

    <section class="vip-video-section">
        <div class="vip-video-heading">
            <div class="vip-video-heading-line1">Nový svět na dosah...</div>
            <div class="vip-video-heading-line2">Staň se členem ZašukejSi.cz</div>
        </div>
        <div class="vip-video-container">
            <div class="vip-video-play-btn">
                <img src="<?php echo e(asset('images/icons/arrowFilled.svg')); ?>" alt="" aria-hidden="true" style="width:50px;height:50px;transform:rotate(180deg);filter:brightness(0) invert(1);">
            </div>
        </div>
        <div class="vip-video-badges">
            <div class="vip-video-badge">
                <img src="<?php echo e(asset('images/icons/Banana.svg')); ?>" alt="" aria-hidden="true" style="width:40px;height:40px;">
                <span style="font-family:'Poppins', sans-serif;font-weight:400;font-size:13px;color:#505050;text-align:left;line-height:1.3;">Swingers akce pro členy</span>
            </div>
            <div class="vip-video-badge">
                <img src="<?php echo e(asset('images/icons/ThumbsUp.svg')); ?>" alt="" aria-hidden="true" style="width:40px;height:40px;">
                <span style="font-family:'Poppins', sans-serif;font-weight:400;font-size:13px;color:#505050;text-align:left;line-height:1.3;">Hodnocení dívek od komunity</span>
            </div>
            <div class="vip-video-badge">
                <img src="<?php echo e(asset('images/icons/icecreamPink.svg')); ?>" alt="" aria-hidden="true" style="width:40px;height:40px;">
                <span style="font-family:'Poppins', sans-serif;font-weight:400;font-size:13px;color:#505050;text-align:left;line-height:1.3;">Databáze dívek na jednom místě</span>
            </div>
        </div>
        <button class="vip-video-register-btn">Registrovat se ZDARMA</button>
    </section>
</div>

<?php if(true): ?>
    <div class="vip-lightbox" id="vip-lightbox">
        <div class="vip-lightbox-stage">
            <div class="vip-lightbox-main">
                <button type="button" class="vip-lightbox-close" id="vip-lightbox-close" aria-label="Close">&times;</button>
                <button type="button" class="vip-lightbox-prev" id="vip-lightbox-prev" aria-label="Previous">&#10094;</button>
                <button type="button" class="vip-lightbox-next" id="vip-lightbox-next" aria-label="Next">&#10095;</button>

                <div class="swiper vip-lightbox-swiper">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <img src="<?php echo e(asset('images/models/vip1.png')); ?>" alt="<?php echo e($profile->display_name); ?>">
                        </div>
                        <div class="swiper-slide">
                            <img src="<?php echo e(asset('images/models/vip2.png')); ?>" alt="<?php echo e($profile->display_name); ?>">
                        </div>
                        <div class="swiper-slide">
                            <img src="<?php echo e(asset('images/models/vip3.png')); ?>" alt="<?php echo e($profile->display_name); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="vip-lightbox-thumbnails">
                <button type="button" class="vip-lightbox-thumbnail active" data-slide="0" aria-label="Slide 1">
                    <img src="<?php echo e(asset('images/models/vip1.png')); ?>" alt="<?php echo e($profile->display_name); ?>">
                </button>
                <button type="button" class="vip-lightbox-thumbnail" data-slide="1" aria-label="Slide 2">
                    <img src="<?php echo e(asset('images/models/vip2.png')); ?>" alt="<?php echo e($profile->display_name); ?>">
                </button>
                <button type="button" class="vip-lightbox-thumbnail" data-slide="2" aria-label="Slide 3">
                    <img src="<?php echo e(asset('images/models/vip3.png')); ?>" alt="<?php echo e($profile->display_name); ?>">
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
    (function() {
        function initProfileDetailSwiper() {
            // Handle static favorite button clicks
            document.querySelectorAll('.vip-profile-static-favorite').forEach(button => {
                if (button._favInitialized) return;
                button._favInitialized = true;
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const img = this.querySelector('img');
                    if (img) {
                        img.classList.add('heart-animate');
                        if (img.src.includes('heart.svg') && !img.src.includes('HeartFilled')) {
                            img.src = "<?php echo e(asset('images/icons/HeartFilled.svg')); ?>";
                        } else {
                            img.src = "<?php echo e(asset('images/icons/heart.svg')); ?>";
                        }
                        setTimeout(() => { img.classList.remove('heart-animate'); }, 400);
                    }
                });
            });

            if (typeof Swiper === 'undefined') {
                // Swiper not yet loaded — wait for the ready event
                document.addEventListener('swiper:ready', initProfileDetailSwiper, { once: true });
                return;
            }

            const galleryEl = document.querySelector('.vip-profile-gallery-swiper');
            let gallerySwiper = null;
            if (galleryEl) {
                gallerySwiper = new Swiper(galleryEl, {
                    loop: <?php echo e($gallerySlides->count() > 3 ? 'true' : 'false'); ?>,
                    slidesPerView: window.innerWidth <= 425 ? 1 : 1.2,
                    spaceBetween: window.innerWidth <= 425 ? 0 : 14,
                    centeredSlides: window.innerWidth > 425,
                    navigation: {
                        nextEl: '.vip-gallery-nav--next, .vip-gallery-desktop-next',
                        prevEl: '.vip-gallery-nav--prev, .vip-gallery-desktop-prev',
                    },
                    breakpoints: {
                        426: {
                            slidesPerView: 1.2,
                            spaceBetween: 14,
                            centeredSlides: true,
                        },
                        768: {
                            slidesPerView: 2.1,
                            spaceBetween: 18,
                        },
                        1100: {
                            slidesPerView: 2.75,
                            spaceBetween: 20,
                        }
                    }
                });
            }

            const lightboxEl = document.querySelector('.vip-lightbox-swiper');
            let lightboxSwiper = null;
            const lightbox = document.getElementById('vip-lightbox');
            const closeLightbox = document.getElementById('vip-lightbox-close');

            if (lightboxEl && lightbox) {
                lightboxSwiper = new Swiper(lightboxEl, {
                    loop: true,
                    slidesPerView: 1,
                    navigation: {
                        nextEl: '#vip-lightbox-next',
                        prevEl: '#vip-lightbox-prev',
                    },
                    keyboard: {
                        enabled: true,
                    },
                });

                document.querySelectorAll('.lightbox-trigger').forEach(function (trigger) {
                    trigger.addEventListener('click', function () {
                        const index = Number(this.dataset.index || 0);
                        lightbox.classList.add('is-open');
                        document.body.style.overflow = 'hidden';
                        lightboxSwiper.slideToLoop(index, 0);
                    });
                });

                function hideLightbox() {
                    lightbox.classList.remove('is-open');
                    document.body.style.overflow = '';
                }

                if (closeLightbox) {
                    closeLightbox.addEventListener('click', hideLightbox);
                }

                lightbox.addEventListener('click', function (event) {
                    if (event.target === lightbox) {
                        hideLightbox();
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && lightbox.classList.contains('is-open')) {
                        hideLightbox();
                    }
                });

                // Thumbnail navigation
                document.querySelectorAll('.vip-lightbox-thumbnail').forEach(function (thumb) {
                    thumb.addEventListener('click', function () {
                        const slideIndex = Number(this.dataset.slide);
                        lightboxSwiper.slideTo(slideIndex, 0);
                        
                        // Update active state
                        document.querySelectorAll('.vip-lightbox-thumbnail').forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                    });
                });

                // Update thumbnail active state on slide change
                lightboxSwiper.on('slideChange', function () {
                    const currentIndex = lightboxSwiper.activeIndex;
                    document.querySelectorAll('.vip-lightbox-thumbnail').forEach((thumb, index) => {
                        thumb.classList.toggle('active', index === currentIndex);
                    });
                });
            }

            const desktopNext = document.getElementById('vip-gallery-desktop-next');

            if (desktopNext) {
                desktopNext.addEventListener('click', function () {
                    if (lightbox && lightboxSwiper) {
                        lightbox.classList.add('is-open');
                        document.body.style.overflow = 'hidden';
                        lightboxSwiper.slideToLoop(1, 0);
                        return;
                    }

                    if (gallerySwiper) {
                        gallerySwiper.slideNext();
                    }
                });
            }
        }

        // Trigger Swiper init
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initProfileDetailSwiper);
        } else {
            initProfileDetailSwiper();
        }

        const video = document.getElementById('vip-profile-video');
        const toggle = document.getElementById('vip-profile-video-toggle');
        const playIcon = document.getElementById('vip-profile-video-play-icon');
        const pauseIcon = document.getElementById('vip-profile-video-pause-icon');

        if (video && toggle) {
            const syncIcons = function () {
                const paused = video.paused;
                if (playIcon) {
                    playIcon.classList.toggle('hidden', !paused);
                }
                if (pauseIcon) {
                    pauseIcon.classList.toggle('hidden', paused);
                }
                toggle.style.background = paused
                    ? 'linear-gradient(180deg, rgba(0, 0, 0, 0.06) 0%, rgba(0, 0, 0, 0.24) 100%)'
                    : 'transparent';
            };

            toggle.addEventListener('click', function (event) {
                event.preventDefault();
                if (video.paused) {
                    video.play();
                } else {
                    video.pause();
                }
                syncIcons();
            });

            video.addEventListener('play', syncIcons);
            video.addEventListener('pause', syncIcons);
            video.addEventListener('ended', syncIcons);
            syncIcons();
        } else if (toggle) {
            toggle.addEventListener('click', function (event) {
                event.preventDefault();
            });
        }
    })();
</script>
<?php $__env->stopPush(); ?><?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/components/profile-detail.blade.php ENDPATH**/ ?>