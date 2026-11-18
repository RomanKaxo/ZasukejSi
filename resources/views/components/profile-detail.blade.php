@props(['profile'])

@php
    // Only the profile's own photos. This used to be padded out to three slides
    // with images/models/model{6,10,12}.png, i.e. a stranger's photos shown on
    // someone else's profile page.
    $images = $profile->getAllImages();
    $gallerySlides = $images->map(fn ($image) => $image->getUrl())->values();
    $averageRating = $profile->getAverageRating();
    $totalRatings = $profile->getTotalRatings();
    $ratingsVisible = \Illuminate\Support\Facades\Gate::allows('view-ratings');
    $isOnline = $profile->isOnline();
    $isNewProfile = $totalRatings === 0 || optional($profile->created_at)->gt(now()->subDays(30));
    // Contacts are a single phone number (from the account) plus toggles
    // saying whether that same number also accepts WhatsApp/Telegram.
    $contactPhone = $profile->user->phone ?? null;
    $contactDigits = $contactPhone ? preg_replace('/\D+/', '', $contactPhone) : null;
    $prices = collect($profile->local_prices ?? [])->filter(fn ($price) => filled($price['time_hours'] ?? null))->values();
    // A profile with no price list shows no prices. It used to fall back to an
    // invented 4 000 / 6 000 / 14 000 / 18 000 Kč tariff presented as the
    // provider's own — a price the visitor could be quoted and the provider had
    // never agreed to.
    $displayPrices = $prices;
    // International (EUR) prices are only shown on the English version of the
    // page — Czech visitors see local_prices (Kč) only.
    $globalPrices = collect($profile->global_prices ?? [])->filter(fn ($price) => filled($price['time_hours'] ?? null))->values();
    $globalCurrency = (is_array($profile->content) ? ($profile->content['global_currency'] ?? null) : null) ?: 'EUR';
    // Services, languages and the about text are the provider's own or absent.
    // Each of these used to fall back to a canned list/paragraph from the
    // translation files, which reads as the provider's own content.
    $displayServices = ($profile->services && $profile->services->count() > 0)
        ? $profile->services->pluck('name')
        : collect();
    $languagesList = collect(explode(',', (string) ($profile->languages ?? '')))
        ->map(fn ($lang) => trim($lang))
        ->filter()
        ->map(fn ($lang) => \Illuminate\Support\Facades\Lang::has('front.account.services.language_names.' . $lang) ? __('front.account.services.language_names.' . $lang) : $lang)
        ->values();
    // Figma (VIP, ne VIP, phone 360 px-12) lists every language and lets the
    // value wrap ("Česky, Rusky / Anglicky"); it never shortens to "a další 1".
    $languages = $languagesList->isEmpty()
        ? null
        : $languagesList->implode(', ');
    $aboutText = trim((string) ($profile->about ?? '')) !== '' ? $profile->about : null;
    // Weight/height live in the `content` JSON column and are exposed via
    // accessors on the Profile model, which also derive the imperial values.
    $weightKg = $profile->weight;
    $heightCm = $profile->height;
    $weightLbs = $profile->weight_lbs;
    $heightFeet = $profile->height_feet;
    // No stock-photo poster: if there is no real photo the player renders
    // without one rather than borrowing someone else's picture.
    $videoPoster = $images->first()?->getUrl();
    $premiumPage = \App\Models\Page::published()->where('slug', 'vip-premium')->first();
    $premiumPageUrl = $premiumPage ? url('/' . $premiumPage->slug) : url('/');

    $accessUser = auth()->user();
    $accessIsMember = $accessUser && $accessUser->isMale() && ! $accessUser->hasRole('admin');
    $accessHasMembership = $accessIsMember && $accessUser->hasActiveMembership();

    // "Obnovit přístup" is password recovery — someone locked out of their
    // account. It pointed at the VIP & Premium page, which is a different
    // thing entirely and left the button reading as marketing.
    //
    // The forgot-password form is behind the `guest` middleware, so a visitor
    // who is already signed in has to be sent to their own password screen
    // instead — otherwise the link bounces them back to the homepage.
    $accessUrl = match (true) {
        ! $accessUser => route('password.request'),
        $accessIsMember => route('account.member.password.edit'),
        default => route('account.password.edit'),
    };

    $accessLabel = __('front.profiles.detail_page.refresh_access');

    // The "Premium unlocks ratings" note is a different promise: it is about
    // buying. A guest sent to the login modal had no way to even see what
    // Premium costs, so it points at the public plans page — which now lists
    // them — and a signed-in member straight at his own.
    $premiumBuyUrl = $accessIsMember
        ? route('account.member.membership.index')
        : $premiumPageUrl;

    // Shown beside the sliders to a member who already has Premium: not an
    // offer to buy, just how long what he has runs for.
    $membershipValidLabel = __('front.profiles.detail_page.access_valid_until', [
        'date' => optional($accessUser?->membershipEndsAt())->format('d.m.Y') ?? '',
    ]);
    // "Dát hodnocení" opens the member rating screen on this profile.
    $rateUrl = \Illuminate\Support\Facades\Route::has('account.member.ratings')
        ? route('account.member.ratings', ['profile' => $profile->id])
        : url('/');

    $messageRouteAvailable = \Illuminate\Support\Facades\Route::has('messages.show');
    $registerRouteAvailable = \Illuminate\Support\Facades\Route::has('register');
    // availability_hours shape: {'always_online': bool, 'schedule': {day => {from, to}}}
    // (see ServicesManager::saveAvailability()).
    $availability = is_array($profile->availability_hours) ? $profile->availability_hours : [];
    // Null until a real schedule says otherwise. These were seeded with '18',
    // so a provider who had never set her hours advertised 18:00–18:00 daily.
    $availabilityStart = null;
    $availabilityEnd = null;
    $availabilityCaption = __('front.profiles.detail_page.availability_every_day');
    $isVerifiedProfile = $profile->isVerified();
    $photoStatusLabel = $isVerifiedProfile ? __('front.profiles.photos.verified_badge') : __('front.profiles.detail_page.photos_unverified');
    // The VIP pill used to be printed on every profile, VIP or not, and the
    // photo pill always used the grey crossed-out camera, so a verified
    // profile read as "not verified" on its own page while the list card
    // showed it as verified. Figma: VIP pill only for VIP; green "FOTO
    // OVĚŘENO" for a verified profile; the grey "FOTO NEOVĚŘENO" only beside
    // the VIP pill (the non-VIP frame shows no pills at all).
    $isVipProfile = $profile->isVip();
    $showPhotoStatus = $isVerifiedProfile || $isVipProfile;
    // One line under the rating bar: the kraj, or the city when no kraj is
    // known. No invented fallback place.
    $locationLabel = filled($profile->region) ? $profile->region : (filled($profile->city) ? $profile->city : null);

    if (!empty($availability['always_online'])) {
        $availabilityCaption = __('front.profiles.detail_page.availability_always_online');
    } elseif (!empty($availability['schedule']) && is_array($availability['schedule'])) {
        $todayKey = strtolower(now()->format('l'));
        $todaySchedule = $availability['schedule'][$todayKey] ?? collect($availability['schedule'])->first();

        if (is_array($todaySchedule) && filled($todaySchedule['from'] ?? null) && filled($todaySchedule['to'] ?? null)) {
            $availabilityStart = (string) explode(':', $todaySchedule['from'])[0];
            $availabilityEnd = (string) explode(':', $todaySchedule['to'])[0];
        }
    }

    // Up to three real photos (four on a non-VIP profile: ne VIP.jpg shows
    // four equal 337 px tiles instead of VIP.jpg's 337 / 537 / 337).
    $heroSlides = $gallerySlides->take($isVipProfile ? 3 : 4)->values();
    $hasGallery = $gallerySlides->isNotEmpty();
@endphp

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

    .vip-profile-status-bar:not(:has(span)) {
        display: none !important;
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

    .vip-profile-name-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
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

    .vip-profile-online-badge {
        width: 80px;
        height: 22px;
        margin: 0 auto 10px;
        border-radius: 10px;
        background: #00B80F;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .vip-profile-online-badge span {
        font-family: 'Poppins', sans-serif;
        font-weight: 900;
        font-size: 10px;
        color: #FFFFFF;
        line-height: 1;
    }

    .vip-profile-online-dot {
        display: none;
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

    .vip-profile-desktop-actions {
        grid-template-columns: minmax(180px, 0.88fr) minmax(280px, 1.18fr) minmax(150px, 0.78fr);
        gap: 14px;
        margin-bottom: 16px;
    }

    .vip-profile-desktop-actions-inner {
        grid-column: 2;
        display: flex;
        justify-content: center;
        gap: 0;
    }

    .vip-profile-desktop-action {
        width: 120px;
        height: 60px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        color: #71717A;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.2;
        text-decoration: underline;
        text-decoration-thickness: 1px;
        text-underline-offset: 2px;
    }

    .vip-profile-desktop-action img {
        width: 16px;
        height: 16px;
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
        font-size: 16px;
        font-weight: 600;
        text-align: center;
    }

    .vip-profile-meta-location img {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
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
        background-color: #E6E6E6;
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
        font-weight: 600;
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
        justify-content: center;
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

    .vip-gallery-next-hint {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        pointer-events: none;
        background: linear-gradient(to right, #FFFFFF00 0%, #FFFFFF 65%);
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
        right: 120px !important;
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
        /* VIP.jpg: „uložit", lowercase and underlined (own key, desktop only). */
        content: '{{ __('front.profiles.detail_page.save_heart') }}';
        font-family: 'Poppins', sans-serif;
        font-size: 12px;
        font-weight: 400;
        color: #71717A;
        text-decoration: underline;
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
        /* 7px + 21px line-height + 7px + 1px border = 36px, the row pitch the
           design uses (separators at y = 1223 / 1259 / 1295 / 1331). Was 12px,
           which made each row 45.5px and the table 38px too tall. */
        padding: 7px 6px;
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
        width: 100%;
    }

    .vip-slider-kicker {
        color: #dd3888;
        font-size: 30px;
        line-height: 0.95;
        font-weight: 800;
        letter-spacing: -0.04em;
    }

    /* Figma desktop: 36px Poppins Bold, "tento měsíc" on its own pink line. */
    .vip-slider-title {
        margin: 0;
        font-size: 36px;
        line-height: 43px;
        font-weight: 700;
        letter-spacing: 0;
    }

    .vip-slider-title .vip-slider-kicker {
        display: block;
        font-size: inherit;
        line-height: inherit;
        font-weight: inherit;
        letter-spacing: inherit;
        color: #DD3888;
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
        width: 1150px;
        height: 540px;
        /* Deliberately uncapped: the column this sits in (.vip-slider-section,
           margin-left: 315px) only has ~1029px to give it, short of the
           1150px five 214px cards + four 20px gaps need. The section and its
           ancestors are `overflow: visible`, so the slider keeps its left
           edge (and the prev-arrow position) in place and bleeds past the
           page's right padding instead of the cards themselves shrinking. */
        overflow: visible;
    }

    .vip-rec-slider .profile-slider-container,
    .vip-rec-slider .swiper {
        width: 1150px !important;
        height: 540px !important;
    }

    .vip-rec-slider [class*="swiper-button-prev-"],
    .vip-rec-slider [class*="swiper-button-next-"] {
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        /* Aligned to the card image band (image-height 265px + the
           swiper-wrapper's 6px top padding) instead of the old 50%, which
           centered on the whole 520px card and sat lower than the photos. */
        top: 140px;
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
        background: linear-gradient(rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5)), url('{{ asset('images/models/vipVideo2.png') }}') center/cover;
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

    /* Tablet/small desktop: the fixed header is 100px tall here (96 on phones),
       but the page only had 32px top padding, so the badges and the name sat
       under the header. Same 12px clearance as the phone layout. */
    @media (min-width: 840px) and (max-width: 1279px) {
        .vip-profile-page {
            padding-top: 112px;
        }
    }

    @media (min-width: 840px) {
        .vip-gallery-desktop {
            position: relative;
            display: grid;
            grid-template-columns: minmax(180px, 0.88fr) minmax(280px, 1.18fr) minmax(150px, 0.78fr);
            /* 10px in the design: tiles end/start at 1020.33→1030.32 and
               1567.57→1577.57. */
            gap: 10px;
            align-items: stretch;
        }

        .vip-profile-gallery-mobile {
            display: none;
        }

        .vip-gallery-nav {
            display: none;
        }
    }

    /* Pass 5: the panel links (Obnovit přístup / Dát hodnocení / Nahlásit)
       were hidden from 840 px but their desktop replacement only appears at
       lg (1024 px), so an unfolded Fold or a landscape tablet had no way to
       rate or report a profile. */
    @media (min-width: 1024px) {
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
            min-height: 575px;
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
            font-size: 15px;
        }

        .vip-profile-meta-table {
            /* No gap: each row is a fixed 36px tall (see below), which is the
               row pitch the design's separators describe. An 18px gap on top of
               that pushed them 54px apart. */
            gap: 0;
            margin-bottom: 12px;
        }

        .vip-profile-meta-row {
            gap: 12px;
            padding-bottom: 6px;
            font-size: 12px;
            /* The design spaces these rows 36px apart (separators in Group 397
               at y = 296.6 / 332.6 / 368.6 / 404.6 / 440.6); they were 21.6px,
               leaving the panel 72px short over five rows. */
            min-height: 36px;
            align-items: center;
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
            /* Exactly the design's tile widths; the gutter is 10px, not 14px —
               tiles end/start at 1020.33→1030.32 and 1567.57→1577.57. */
            grid-template-columns: 337px 537px 337px;
            gap: 10px;
        }

        .vip-gallery-desktop-left,
        .vip-gallery-desktop-main,
        .vip-gallery-desktop-right {
            min-height: 537px;
            height: 537px;
        }
    }

    @media (max-width: 767px) {
        .vip-gallery-next-hint {
            display: none !important;
        }

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
            text-align: left;
        }

        .vip-profile-name-row {
            justify-content: flex-start;
        }

        .vip-profile-online-badge {
            display: none;
        }

        .vip-profile-online-dot {
            display: inline-block;
            width: 20px;
            height: 20px;
            flex: 0 0 20px;
            border-radius: 50%;
            background: #00B80F;
        }

        .vip-profile-rating-summary {
            margin-bottom: 12px;
            height: 40px;
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

    /* Phone layout (Figma "phone 360 px-12/-14/-24/-25/-26").
       Used to stop at 425px, so a 430px phone (iPhone Pro Max) fell into the
       tablet layout: the VIP / FOTO OVĚŘENO pills slid under the fixed menu,
       the gallery ended up below the info table and the "Uložit" box sat on
       the photo. It now covers every width below the desktop gallery (840px).
       Values are the 360px frame's; widths are fluid (frame content = 310px =
       viewport minus 2 × 25px). */
    @media (max-width: 839px) {
        .vip-profile-page {
            max-width: none;
            padding: 104px 0 48px 0 !important;
            margin: 0 auto;
            overflow: visible;
            overflow-x: clip;
        }

        .vip-profile-hero {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) auto;
            column-gap: 6px;
            row-gap: 0;
            align-items: start;
            margin-bottom: 0 !important;
            padding: 0 25px;
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
        .vip-gallery-desktop,
        .vip-profile-desktop-actions,
        .vip-gallery-next-hint {
            display: none !important;
        }

        /* Row 1: pills left, heart right (heart box 38px, pills 30px). */
        .vip-profile-status-bar {
            grid-column: 1;
            grid-row: 1;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            flex-wrap: nowrap;
            gap: 9px;
            margin: 0;
            min-height: 38px;
            min-width: 0;
            padding-left: 1px;
        }

        .vip-profile-status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            width: auto;
            min-width: 0;
            height: 30px;
            padding: 0 10px !important;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 10px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: 0;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .vip-profile-status-pill img {
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
            margin: 0;
        }

        .vip-profile-status-pill--primary {
            width: 101px;
            background: #FFB700;
            color: #FFFFFF;
        }

        .vip-profile-status-pill--verification {
            width: 124px;
            background: #E8E8E8;
            color: #A4A4A4;
        }

        .vip-profile-status-pill--verification.is-verified {
            background: #CCF1CF;
            color: #00B80F;
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
            margin: -2px 2px 0 0;
        }

        .vip-profile-favorite button,
        .vip-profile-static-favorite {
            width: 38px !important;
            min-width: 38px !important;
            height: 38px !important;
            padding: 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            gap: 0 !important;
            display: flex !important;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .vip-profile-favorite button span,
        .vip-profile-static-favorite::after {
            display: none !important;
        }

        .vip-profile-favorite button svg,
        .vip-profile-static-favorite img {
            width: 36px !important;
            height: 36px !important;
        }

        .vip-profile-favorite .inline-block > div {
            display: none;
        }

        /* Row 2: (online dot) name. */
        .vip-profile-name-row {
            grid-column: 1 / -1;
            grid-row: 2;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 14px;
            margin-top: 19px;
            min-width: 0;
        }

        .vip-profile-name {
            margin: 0;
            min-width: 0;
            text-align: left;
            font-family: 'Poppins', sans-serif;
            font-size: 38px;
            line-height: 46px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #5C2D62;
            overflow-wrap: anywhere;
        }

        .vip-profile-online-dot {
            display: inline-block;
            width: 20px;
            height: 20px;
            flex: 0 0 20px;
            margin-left: 1px;
            border-radius: 50%;
            background: #00B80F;
        }

        .vip-profile-online-badge {
            display: none;
        }

        /* Row 3: three underlined links, the report one on the right. */
        .vip-profile-links {
            grid-column: 1 / -1;
            grid-row: 3;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            align-items: baseline;
            gap: 4px 20px;
            margin: 7px 0 0;
        }

        .vip-profile-link {
            color: #71717A;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 12px;
            font-weight: 600;
            line-height: 17px;
            text-decoration: underline;
            text-decoration-thickness: 1px;
            text-underline-offset: 1px;
        }

        .vip-profile-link--report {
            margin-left: auto;
        }

        /* Row 4: one photo at full content width; the next one peeks in 3px
           to its right and runs off the screen. Arrows 45 × 45, 13px outside
           the photo. */
        .vip-profile-gallery-mobile {
            grid-column: 1 / -1;
            grid-row: 4;
            position: relative !important;
            display: block;
            width: 100%;
            margin: 18px 0 0;
        }

        .vip-profile-gallery-swiper {
            width: 100% !important;
            margin: 0 !important;
            overflow: visible !important;
        }

        .vip-profile-gallery-swiper .swiper-slide {
            width: 100% !important;
            opacity: 1 !important;
            transform: none !important;
        }

        .vip-gallery-slide {
            display: block;
            width: 100% !important;
            height: auto !important;
            aspect-ratio: 310 / 444;
            border-radius: 12px;
        }

        .vip-gallery-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            border: 0;
            border-radius: 8px;
            background: #DD3888;
            box-shadow: none;
        }

        .vip-gallery-nav--prev {
            left: -13px;
        }

        .vip-gallery-nav--next {
            right: -13px;
        }

        .vip-gallery-nav.swiper-button-disabled {
            opacity: 1;
        }

        /* Row 5: rating bar, 40px; the label half is a lighter pill. */
        .vip-profile-rating-summary {
            grid-column: 1 / -1;
            grid-row: 5;
            display: grid;
            grid-template-columns: 51% 1fr;
            gap: 0;
            height: 40px;
            margin: 19px 0 0;
            border-radius: 8px;
            overflow: hidden;
            background: #E8E8E8;
        }

        .vip-profile-rating-summary strong {
            min-height: 40px;
            border-radius: 8px;
            background: #F2F2F2;
            color: #505050;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
        }

        .vip-profile-rating-icons {
            min-height: 40px;
            background: transparent;
            color: #505050;
            font-size: 14px;
        }

        .vip-profile-rating-icons svg {
            width: 19px !important;
            height: 19px !important;
            color: #DD3888 !important;
        }

        /* Row 6: pin + kraj, centred. */
        .vip-profile-meta-location {
            grid-column: 1 / -1;
            grid-row: 6;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            margin: 14px 0 0;
            padding: 0;
            color: #505050;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            line-height: 18px;
        }

        .vip-profile-meta-location svg {
            width: 12px;
            height: 14px;
            flex: 0 0 12px;
        }

        /* Row 7: the table — 36px rows between full-width #F0F0F0 rules,
           text inset 21px. */
        .vip-profile-meta-table {
            grid-column: 1 / -1;
            grid-row: 7;
            display: block;
            margin: 13px 0 0;
            padding: 0;
            border-top: 1px solid #F0F0F0;
            background: transparent;
        }

        .vip-profile-meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 36px;
            /* Figma sets the text 2px above the row's middle. */
            padding: 0 21px 4px !important;
            border-bottom: 1px solid #F0F0F0;
            box-sizing: border-box;
        }

        .vip-profile-meta-row:last-child {
            border-bottom: 0;
        }

        .vip-profile-meta-row::after {
            display: none !important;
        }

        .vip-profile-meta-label,
        .vip-profile-meta-value {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            line-height: 18px;
        }

        .vip-profile-meta-label {
            color: #DD3888;
        }

        .vip-profile-meta-value {
            color: #505050;
            text-align: right;
        }

        /* Row 8: InCall / OutCall. */
        .vip-profile-flags {
            grid-column: 1 / -1;
            grid-row: 8;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 27px 0 0;
        }

        .vip-profile-flag {
            width: auto;
            min-width: 0;
            height: 40px;
            gap: 8px;
            padding: 2px 8px 0;
            border: 0 !important;
            border-radius: 8px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
        }

        .vip-profile-flag--incall {
            background: #E9FFEB;
            color: #DD3888;
        }

        .vip-profile-flag--outcall {
            background: transparent;
            color: #A6A6A6;
        }

        .vip-profile-flag-status,
        .vip-profile-flag-status img {
            width: 19px;
            height: 19px;
            flex: 0 0 19px;
        }

        /* Row 9: message button, full width, 60px. */
        .vip-profile-message {
            grid-column: 1 / -1;
            grid-row: 9;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            max-width: none;
            height: 60px;
            margin: 20px 0 0;
            border-radius: 8px;
            background: #DD3888;
            color: #FFFFFF;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
        }

        /* Row 10: WhatsApp / Telegram circles and the number. */
        .vip-profile-contacts {
            grid-column: 1 / -1;
            grid-row: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 5px;
            margin: 20px 0 0;
        }

        .vip-profile-contact-circle {
            display: flex;
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

        .vip-profile-contact-circle img {
            width: 20px !important;
            height: 20px !important;
        }

        .vip-profile-phone {
            display: flex;
            align-items: center;
            margin-left: 9px;
            color: #505050;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
        }

        .vip-about-card,
        .vip-video-card-wrap,
        .vip-pricing-card {
            grid-column: 1 / -1;
            width: 100%;
            max-width: none;
            margin-left: 0;
            margin-right: 0;
        }

        .vip-video-play__inner {
            width: 60px !important;
            height: 60px !important;
        }

        #vip-profile-video-play-icon {
            width: 22px !important;
            height: 22px !important;
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
            font-size: 24px;
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
            padding: 0 14px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 14px;
            background: white;
            border: 2px solid #F2F2F2;
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
            align-items: center;
            gap: 4px;
            margin-top: 10px;
        }

        /* phone 360 px-12: 10 px #E8E8E8 dots, active 8 px. */
        .vip-rec-slider .swiper-pagination-bullet {
            width: 10px;
            height: 10px;
            background: #E8E8E8 !important;
            opacity: 1 !important;
        }

        .vip-rec-slider .swiper-pagination-bullet-active {
            width: 8px;
            height: 8px;
        }

        .vip-rec-slider .swiper-pagination-bullet-active {
            background: #DD3888 !important;
            opacity: 1 !important;
        }

        .vip-video-section {
            margin: 12px auto 0 !important;
            width: calc(100% - 47px) !important;
            max-width: none !important;
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
            width: calc(100% - 3px) !important;
            max-width: none !important;
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
            width: 100% !important;
            max-width: none !important;
            margin: 0;
        }

        .vip-video-badge {
            width: 100% !important;
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
            width: 100% !important;
            max-width: none !important;
            height: 60px !important;
            margin: 0 !important;
            font-size: 16px !important;
            border-radius: 8px;
            background: #DD3888;
            color: white;
        }

        /* Lower sections (phone frame): 24px Poppins Bold headings,
           26px body lines, 25px side margins. */
        .vip-about-card .vip-section-title,
        .vip-pricing-card h3 {
            margin: 0 0 20px;
            font-size: 24px;
            line-height: 30px;
            font-weight: 700;
            letter-spacing: 0;
        }

        .vip-about-copy {
            font-size: 14px;
            line-height: 26px;
        }

        .vip-slider-section {
            width: auto !important;
            max-width: none !important;
            margin: 0 25px 24px !important;
        }

        .vip-slider-title {
            font-size: 28px;
            line-height: 32px;
        }

        .vip-slider-title .vip-slider-kicker {
            display: inline;
        }

        .vip-about-card {
            margin-top: 52px;
        }

        .vip-pricing-card {
            gap: 0 !important;
        }

        .vip-services-block {
            margin-top: 48px !important;
        }

        .vip-prices-block {
            margin-top: 60px !important;
        }

        .vip-services-grid {
            gap: 10px !important;
        }

        .vip-service-pill {
            height: 34px;
        }

        .vip-slider-section {
            margin-top: 47px !important;
        }

        .vip-slider-head {
            gap: 28px;
        }

        .vip-slider-note {
            font-size: 12px;
            white-space: nowrap;
        }
    }

    /* Tablets (no Figma frame): keep the phone column instead of a
       1000px-tall photo. */
    @media (min-width: 600px) and (max-width: 839px) {
        .vip-profile-hero {
            max-width: 520px;
            margin-left: auto !important;
            margin-right: auto !important;
        }
    }

    /* Desktop 1:1 with the Figma "VIP" / "ne VIP" frames (1920px).
       Panel content x 392–623, gallery from x 683 (tiles 337 / 537 / 337,
       10px gutters) and y 159, action links above it (y 122–137), right
       aligned to x 1526. */
    @media (min-width: 1280px) {
        .vip-profile-hero {
            gap: 44px;
        }

        .vip-profile-panel {
            /* Natural top 146 (pills at 158); still sticks at 106 on scroll. */
            margin-top: 114px;
            padding: 12px 16px 12px 14px;
        }

        .vip-profile-status-bar {
            gap: 5px;
            margin: 0 0 28px;
        }

        .vip-profile-status-pill {
            gap: 7px;
            height: 30px;
            padding: 0;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0;
        }

        .vip-profile-status-pill img {
            width: 16px;
            height: 16px;
            flex: 0 0 16px;
        }

        .vip-profile-status-pill--primary {
            width: 93px;
            min-width: 93px;
            background: #FFB700;
        }

        .vip-profile-status-pill--verification {
            width: 131px;
            min-width: 131px;
        }

        .vip-profile-status-pill--verification.is-verified {
            background: #CCF1CF;
            color: #00B80F;
        }

        .vip-profile-name {
            margin: 0;
            font-size: 36px;
            line-height: 44px;
            letter-spacing: -0.02em;
        }

        .vip-profile-online-badge {
            margin: 10px auto -5px;
        }

        .vip-profile-rating-summary {
            height: 40px;
            margin: 24px 0 0;
            gap: 0;
            border-radius: 8px;
            background: #E8E8E8;
        }

        .vip-profile-rating-summary strong {
            min-height: 40px;
            border-radius: 8px;
            background: #F2F2F2;
            font-size: 14px;
        }

        .vip-profile-rating-icons {
            min-height: 40px;
            background: transparent;
        }

        .vip-profile-rating-icons svg {
            width: 19px !important;
            height: 19px !important;
            color: #DD3888 !important;
        }

        .vip-profile-meta-location {
            gap: 7px;
            margin: 14px 0 0;
            font-size: 13px;
            line-height: 17px;
        }

        .vip-profile-meta-location svg {
            width: 14px;
            height: 16px;
        }

        /* VIP-1, VIP-3 and "ne VIP" draw a rule above the first row (the
           plain "VIP" frame is the odd one out). */
        .vip-profile-meta-table {
            margin: 14px 0 0;
            border-top: 1px solid #E6E6E6;
        }

        .vip-profile-meta-row {
            min-height: 36px;
            padding: 0 21px 0 20px;
        }

        .vip-profile-meta-row::after {
            width: 100%;
            background-color: #E6E6E6;
        }

        .vip-profile-meta-label,
        .vip-profile-meta-value {
            font-size: 13px;
        }

        .vip-profile-flags {
            flex-wrap: nowrap;
            gap: 7px;
            margin: 20px 0 0;
        }

        .vip-profile-flag {
            width: 112px;
            min-width: 112px;
            /* VIP.jpg: green InCall box 577–617 = 41 px. */
            height: 41px;
            gap: 8px;
            padding: 0;
            font-size: 13px;
        }

        .vip-profile-flag-status,
        .vip-profile-flag-status img {
            width: 19px;
            height: 19px;
            flex: 0 0 19px;
        }

        .vip-profile-message {
            width: 100%;
            /* Flags end 618, button 638–687. */
            margin: 20px 0 0;
        }

        /* VIP.jpg: button ends at y 688, circles 708–747 (20 px), WhatsApp
           392–432, Telegram 437–477, number ink 492–622 — one row. With the
           old 10 px + 21 px the row was 232.5 px in a 231 px column and the
           number wrapped under the icons. */
        .vip-profile-contacts {
            justify-content: flex-start;
            flex-wrap: nowrap;
            gap: 5px;
            margin: 20px 0 0;
        }

        .vip-profile-contact-circle {
            flex: 0 0 40px;
        }

        .vip-profile-phone {
            margin-left: 8px;
            white-space: nowrap;
        }

        /* Jazyky wraps to two lines in every frame: the second line sits 20 px
           under the first (ink 526 / 546) and the flags start at 577, so the
           row is top-aligned with a 20 px line and no bottom padding. */
        .vip-profile-meta-row:last-child {
            align-items: flex-start;
            padding-top: 8px;
            padding-bottom: 0;
        }

        .vip-profile-meta-row:last-child .vip-profile-meta-label,
        .vip-profile-meta-row:last-child .vip-profile-meta-value {
            line-height: 20px;
        }

        /* "Česky, Rusky" (ink 521–602) fits, ", Anglicky" goes below. */
        .vip-profile-meta-row:last-child .vip-profile-meta-value {
            max-width: 110px;
        }

        .vip-profile-meta-table + .vip-profile-flags {
            margin-top: 15px;
        }

        .vip-profile-main {
            /* Action row 101–159, gallery from 159. */
            padding-top: 69px;
        }

        .vip-profile-desktop-actions {
            display: flex !important;
            justify-content: flex-end;
            margin: 0;
            padding-right: 395px;
        }

        .vip-profile-desktop-actions-inner {
            gap: 23px;
        }

        .vip-profile-desktop-action {
            width: auto;
            height: 58px;
            gap: 6px;
            text-underline-offset: 2px;
        }

        .vip-gallery-desktop-nav.vip-gallery-desktop-prev {
            left: -22px !important;
        }

        .vip-gallery-desktop-nav {
            top: 246px;
            transform: none;
        }

        .vip-gallery-desktop-nav.vip-gallery-desktop-next {
            /* x 1835 at 1920 (85 px from the window edge). Below 1920 the
               1239 px gallery runs past the window and "right: 42px" put the
               arrow off-screen, so it follows the window edge instead:
               main column starts at 505 px (page < 1564) or centred. */
            left: min(calc(100vw - 590px), calc(50vw + 192px)) !important;
            right: auto !important;
        }

        .vip-gallery-desktop .vip-gallery-next-hint {
            background: linear-gradient(to right, rgba(255, 255, 255, 0) 0%, #FFFFFF 80%);
        }

        /* The right tile fades into white (VIP.jpg: nothing after x ≈ 1845).
           Its rounded right corners were still drawn as an anti-aliased
           photo edge at x 1900–1914 under the white hint. */
        .vip-gallery-desktop-right,
        .vip-gallery-desktop-right img {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        /* VIP.jpg / ne VIP.jpg: tile corners are 15 px (curve from y 681 to
           the bottom edge at 696), not 26. */
        .vip-gallery-desktop-card {
            border-radius: 15px;
        }

        /* ne VIP.jpg: four 337 px tiles at x 683 / 1030 / 1377 / 1725; only
           the fourth fades (≈ 57 % white at its left edge, white by x 1847)
           and it is cut at the content edge instead of widening the page. */
        .vip-gallery-desktop--four {
            grid-template-columns: 337px 337px 337px minmax(0, 1fr);
        }

        .vip-gallery-desktop--four .vip-gallery-desktop-right,
        .vip-gallery-desktop--four .vip-gallery-desktop-right img {
            border-top-right-radius: 15px !important;
            border-bottom-right-radius: 15px !important;
        }

        .vip-gallery-desktop-extra {
            grid-column: 4;
            grid-row: 1 / span 2;
            position: relative;
            height: 537px;
            overflow: hidden;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        .vip-gallery-desktop-extra img {
            width: 337px !important;
            max-width: none;
            height: 100%;
            object-fit: cover;
        }

        .vip-gallery-desktop--four .vip-gallery-desktop-extra .vip-gallery-next-hint {
            background: linear-gradient(to right, rgba(255, 255, 255, 0.57) 0, #FFFFFF 122px);
        }
    }

    .vip-gallery-desktop-extra {
        display: none;
    }

    @media (min-width: 1280px) {
        .vip-gallery-desktop--four .vip-gallery-desktop-extra {
            display: block;
        }
    }
</style>

{{-- Lower part of the detail at desktop width, measured on VIP.jpg / ne VIP.jpg
     (1920): everything below the gallery sits in the 844 px content column
     that starts at x 683, like "Více o mně". Kept last so it wins over the
     older rules above without rewriting them. --}}
<style>
@media (min-width: 1280px) {
    .vip-about-card .vip-section-title { font-size: 24px; line-height: 36px; font-weight: 700; letter-spacing: 0; }
    .vip-slider-section { margin-left: 305px; }
    .vip-slider-head { width: 844px; margin-bottom: 31px; }
    .vip-slider-note { font-size: 13px; font-weight: 500; gap: 11px; margin-bottom: 9px; }
    .vip-slider-note img { width: 20px; height: 20px; }
    .vip-rec-slider .swiper-slide { width: 214px !important; }
    .vip-rec-slider [class*="swiper-button-prev-"] { left: -22px; }
    /* x 1835 at 1920; follows the window edge below that (see the gallery arrow). */
    .vip-rec-slider [class*="swiper-button-next-"] { right: auto; left: min(calc(100vw - 590px), calc(50vw + 192px)); }
    .vip-rec-slider [class*="swiper-button-prev-"],
    .vip-rec-slider [class*="swiper-button-next-"] { top: 137px; }
    .vip-video-section { align-items: flex-start; margin-left: 305px; }
    .vip-video-heading { margin-left: 0; }
    .vip-video-container { width: 844px; height: 450px; }
    .vip-video-badges { width: 844px; justify-content: space-between; }
    .vip-video-register-btn { align-self: flex-start; margin-left: 192px; }
    .vip-video-badges { gap: 20px; }
    .vip-video-badge { width: 268px !important; min-height: 76px; height: 76px !important; }

    /* Pass 1 overlay (VIP.jpg / ne VIP.jpg, 1920): "Více o mně" ink at
       y 744, 48 px under the gallery (was 34). */
    /* ne VIP.jpg (no pills): name ink 162 → rating box 229, 3 px more than
       under the pills in VIP.jpg (220 → 284). */
    .vip-profile-status-bar:not(:has(span)) ~ .vip-profile-rating-summary { margin-top: 27px; }
    .vip-about-card { padding-top: 14px; }
    /* Lines 26 px apart (ink 794 / 820 / 847 …), wrapping as late as x 1513. */
    .vip-about-copy { line-height: 26px; max-width: 844px; }

    /* VIP.jpg, profile with a video: video 685–939 × 1127–1587 (radius 12),
       "Moje ceny" + table + "Služby" in one 526 px column from x 1000.
       "Video" and "Moje ceny" share the heading line (ink y 1075). */
    .vip-media-grid:not(.vip-media-grid--single) {
        grid-template-columns: 256px 526px;
        column-gap: 61px;
        /* collapses with the about card's 34 px bottom margin */
        margin-top: 102px;
    }
    .vip-video-card-wrap { width: 256px; }
    .vip-video-card-title { padding-bottom: 25px; line-height: 30px; }
    .vip-video-card { width: 255px; margin-left: 2px; border-radius: 12px; box-shadow: none; }
    .vip-video-surface { width: 255px; border-radius: 12px; }
    .vip-pricing-card { padding-top: 0; }
    .vip-pricing-card h3 { line-height: 30px; margin-bottom: 25px; }
    /* Header pills end 20 px above the first row (pills 1127–1166, first
       separator 1222, row pitch 36). Time reads "0.5 h". */
    .vip-pricing-table tbody tr:first-child td { padding-top: 27px; }
    .vip-pricing-table th:nth-child(2) .vip-price-pill { margin-left: 9px; }
    /* Služby: 36 px under the last separator, pills 53 px under its ink,
       34 px tall pills with 10 px gaps. */
    .vip-services-block { margin-top: 31px !important; }
    .vip-services-block h3 { margin-bottom: 27px !important; }
    .vip-services-grid { margin-top: 0; gap: 10px; }
    .vip-service-pill { height: 34px; padding: 0 14px; display: inline-flex; align-items: center; }

    /* ne VIP.jpg, no video: prices 683–1041 (pills 92 / 127 px), services in
       a second 431 px column from x 1096, both headings at ink y 1008. */
    .vip-media-grid--single { margin-top: 35px; }
    .vip-media-grid--single .vip-pricing-card {
        display: grid;
        grid-template-columns: 358px 431px;
        column-gap: 55px;
        align-items: start;
    }
    .vip-media-grid--single .vip-services-block { margin-top: 0 !important; }
    .vip-media-grid--single .vip-prices-block h3 { margin-bottom: 26px; }
    .vip-media-grid--single .vip-pricing-table { width: 358px; }
    .vip-media-grid--single .vip-pricing-table th:first-child { width: 92px; }
    .vip-media-grid--single .vip-pricing-table th:nth-child(2) .vip-price-pill { width: 127px; margin-left: 7px; }
    .vip-media-grid--single .vip-pricing-table th:nth-child(3) .vip-price-pill { width: 127px; }
    .vip-media-grid--single .vip-services-grid { width: 431px; }

    /* Gallery/grid bottom → slider title ink is 104 px in both frames
       (VIP: video ends 1587, title 1692; ne VIP: table ends 1265, title 1368). */
    .vip-media-grid { margin-bottom: 35px; }
    /* Card row → next card row 736 px (was 728), → "Nový svět" ink 620. */
    .vip-slider-section { margin-bottom: 79px; }
    /* Its top margin collapses into the slider's 79 px, so the extra 5 px
       to the frame's 620 is padding. */
    .vip-video-section { padding-top: 5px; }
    /* Button ends 3651, footer logo ink 3872 (was 6 px lower). */
    .vip-profile-page { padding-bottom: 90px; }
    /* The last visible card fades into white like the gallery's right tile
       (VIP.jpg: white overlay 0 at x ≈ 1566 → 1 at x ≈ 1847). */
    .vip-rec-slider { position: relative; }
    .vip-rec-slider::after {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 883px;
        width: 281px;
        z-index: 3;
        pointer-events: none;
        background: linear-gradient(to right, rgba(255, 255, 255, 0) 0%, #FFFFFF 100%);
    }
    /* ne VIP.jpg: heading → video 122 px, video → badges 45, badges 80 px
       tall, → button 53. Badge copy 14/20 px, icon 36 px at x + 23, text
       at x + 81, broken exactly as the frame does. */
    .vip-video-container { margin-top: 15px; }
    .vip-video-badges { margin-top: 21px; }
    .vip-video-badge { height: 80px !important; min-height: 80px; padding: 0 16px 0 23px; gap: 22px; box-shadow: 0 8px 18px rgba(92, 45, 98, 0.12); }
    .vip-video-badge img { width: 36px !important; height: 36px !important; flex: 0 0 36px; }
    .vip-video-badge span { font-size: 14px !important; line-height: 20px !important; flex: 0 1 auto; }
    .vip-video-badge:nth-child(1) span { max-width: 112px; }
    .vip-video-badge:nth-child(2) span { max-width: 125px; }
    .vip-video-badge:nth-child(3) span { max-width: 140px; }
    .vip-video-register-btn { margin-top: 28px; }
}
/* Pass 2 element measurement (VIP.jpg, VIP-1, ne VIP.jpg at 1920). */
@media (min-width: 1280px) {
    /* Rating box 1 px lower (VIP 285, VIP-1 312), location line unchanged;
       ne VIP (no pills): name ink 165, box 229. */
    .vip-profile-rating-summary { margin-top: 25px; }
    .vip-profile-meta-location { margin-top: 13px; gap: 6px; }
    .vip-profile-status-bar:not(:has(span)) ~ .vip-profile-name-row { margin-top: 1px; }
    .vip-profile-status-bar:not(:has(span)) ~ .vip-profile-rating-summary { margin-top: 26px; }
    /* Grey half ends at x 510 (118 px), "Rating:" ink 428–473 / 301–313,
       lock stays 558–572. */
    .vip-profile-rating-summary { grid-template-columns: 118px minmax(0, 1fr); }
    .vip-profile-rating-summary strong { padding-top: 2px; }
    .vip-profile-rating-icons { padding-right: 15px; }
    /* Pin 1 px above the text line (pin 338–353, text 341–353). */
    .vip-profile-meta-location svg { transform: translateY(-1px); }
    /* Values end at ink x 602; the wrapped languages row starts 12 px under
       its rule like the other rows. */
    .vip-profile-meta-row { padding-right: 19px; }
    .vip-profile-meta-row:last-child { padding-top: 6px; padding-bottom: 2px; }

    /* "Video" / "Moje ceny" ink 1074, video card 1127–1587. */
    .vip-media-grid:not(.vip-media-grid--single) { margin-top: 101px; }
    .vip-video-card-title { padding-bottom: 26px; }
    .vip-media-grid:not(.vip-media-grid--single) .vip-pricing-card h3 { margin-bottom: 26px; }
    .vip-video-card, .vip-video-surface { height: 461px; }
    /* Price table: Čas 134 px, pills 188 px from x 1142 and 1337; times
       right-aligned on the widest ("0.5 h" centred), prices right-aligned
       (ink to 1272), dash centred 5 px left of the OutCall pill; #E6E6E6
       rules. */
    .vip-pricing-table { table-layout: fixed; }
    .vip-pricing-table th:first-child { width: 134px; }
    .vip-pricing-table th:nth-child(2), .vip-pricing-table th:nth-child(3) { width: 196px; }
    .vip-pricing-table th:nth-child(2) .vip-price-pill,
    .vip-pricing-table th:nth-child(3) .vip-price-pill { margin-left: 8px; width: 188px; }
    .vip-pricing-table td,
    .vip-pricing-table td:nth-child(2),
    .vip-pricing-table td:nth-child(3) { border-bottom-color: #E6E6E6; }
    .vip-pricing-table td:first-child { text-align: right; padding-right: 48px; }
    .vip-pricing-table td:nth-child(2) { text-align: right; padding-right: 56px; }
    .vip-pricing-table td:nth-child(3) { text-align: center; padding-left: 0; padding-right: 2px; }
    .vip-media-grid--single .vip-pricing-table th:first-child { width: 92px; }
    .vip-media-grid--single .vip-pricing-table th:nth-child(2) { width: 134px; }
    .vip-media-grid--single .vip-pricing-table th:nth-child(3) { width: 132px; }
    .vip-media-grid--single .vip-pricing-table th:nth-child(2) .vip-price-pill { width: 127px; margin-left: 7px; }
    .vip-media-grid--single .vip-pricing-table th:nth-child(3) .vip-price-pill { width: 127px; }
    .vip-media-grid--single .vip-pricing-table td:first-child { padding-right: 27px; }
    .vip-media-grid--single .vip-pricing-table td:nth-child(2) { padding-right: 32px; }
    .vip-media-grid--single .vip-pricing-table td:nth-child(3) { padding-right: 2px; }
    /* Pill contents: icon → text ink 8 px (InCall, group 2 px left of the
       pill centre) and 6–7 px (OutCall); "Čas" 1–2 px left of centre. */
    .vip-pricing-table th:nth-child(2) .vip-price-pill { gap: 5px; padding: 0 4px 0 0; }
    .vip-pricing-table th:nth-child(3) .vip-price-pill { gap: 6px; padding: 0; }
    .vip-pricing-table th:first-child { padding-right: 3px; }
    .vip-media-grid--single .vip-pricing-table th:first-child { padding-right: 2px; }
    .vip-media-grid--single .vip-pricing-table th:nth-child(3) .vip-price-pill { margin-left: 2px; }
    .vip-media-grid--single .vip-pricing-table tbody tr:first-child td { padding-bottom: 8px; }
    /* Služby pills: text 17 px from either edge, block 1 px right (VIP). */
    .vip-service-pill { padding: 0 15px; }
    .vip-media-grid:not(.vip-media-grid--single) .vip-services-block { margin-left: 1px; }

    /* Slider heads: title ink 1691, cards 1813 (122 under), arrows 110 px
       under the card top. Note: diamond 23 × 20 at x 1224–1246 (bottom
       1764), 10 px to the text, baseline 1759, thin underline 3 px under. */
    .vip-media-grid:not(.vip-media-grid--single) { margin-bottom: 36px; }
    .vip-slider-head { margin-bottom: 33px; }
    /* Title 1691 → 2426 (735), cards 1813 → 2549 (736). */
    .vip-slider-section { margin-bottom: 76px; }
    .vip-slider-section + .vip-slider-section .vip-slider-head { margin-bottom: 34px; }
    .vip-rec-slider [class*="swiper-button-prev-"],
    .vip-rec-slider [class*="swiper-button-next-"] { top: 138px; }
    .vip-slider-note { gap: 10px; align-items: flex-end; }
    /* ne VIP.jpg (no video) spaces the same block 1–2 px tighter: table
       rule 1265 → title 1367 → cards 1487 → title 2101 → cards 2223. */
    .vip-media-grid--single { margin-bottom: 34px; }
    .vip-profile-page:has(.vip-media-grid--single) .vip-slider-head { margin-bottom: 31px; }
    .vip-profile-page:has(.vip-media-grid--single) .vip-slider-section { margin-bottom: 77px; }
    .vip-profile-page:has(.vip-media-grid--single) .vip-slider-section + .vip-slider-section .vip-slider-head { margin-bottom: 33px; }
    /* "Nový svět" ink 619 px under the card row (ne VIP), video 740 under
       it (741 in VIP.jpg), button 628 under the video top; footer logo 275
       under the button (274 in VIP.jpg). */
    .vip-video-container { margin-top: 14px; }
    .vip-profile-page:has(.vip-media-grid:not(.vip-media-grid--single)) .vip-video-container { margin-top: 16px; }
    .vip-video-register-btn { margin-top: 29px; }
    .vip-profile-page:has(.vip-media-grid:not(.vip-media-grid--single)) { padding-bottom: 89px; }
    /* Badge icons: banana / thumb 36 px ink, ice cream 29 × 32, 1 px up. */
    .vip-video-badge:nth-child(1) img, .vip-video-badge:nth-child(2) img { transform: translateY(-1px) scale(1.125); }
    .vip-video-badge:nth-child(3) img { transform: translate(2.5px, -1px) scale(0.889); }
    .vip-slider-note img { width: 22px !important; height: 21px !important; transform: scale(1.09, 1); }
    .vip-slider-note a { text-underline-offset: 2px; text-decoration-color: rgba(221, 56, 136, 0.6) !important; }
}
/* Pass 2 width sweep, 840–1279 px (no frame): the page kept the 1920
   layout's 200 px left padding and the sliders/video their 315 px desktop
   indent, so the slider titles wrapped word by word, the premium note and
   the 850 px video ran past the right edge. One even content column. */
@media (min-width: 840px) and (max-width: 1279px) {
    .vip-profile-page { padding-left: 32px; padding-right: 32px; }
    .vip-slider-section, .vip-video-section { margin-left: 0 !important; margin-right: 0 !important; width: auto !important; max-width: none !important; }
    .vip-slider-head { width: auto; flex-wrap: wrap; gap: 12px 24px; }
    .vip-video-section { align-items: stretch; }
    .vip-video-heading { margin-left: 0 !important; }
    .vip-video-container, .vip-video-badges { width: 100% !important; max-width: 100% !important; }
    .vip-video-register-btn { align-self: center; }
    /* Pass 5: the 231 px rule under each attribute belongs to the 261 px
       desktop panel; in this ~500 px panel it stopped halfway under "Věk". */
    .vip-profile-meta-row::after { width: 100%; }
}
/* Pass 5, under 360 px (iPhone SE 1st gen): the one-line 13 px premium
   note under the slider title ran 4 px off the screen. */
@media (max-width: 359px) {
    .vip-slider-note { font-size: 12px !important; }
}
/* Pass 5, 768–839 px (iPad portrait, no frame): the phone rules still fixed
   the badge text to one 15 px line, so two-line labels ("Hodnocení dívek od
   komunity") hung 16 px below their centred row; the three badges stacked in
   a narrow centred column, and the video was a 718 × 165 strip. Three
   columns and the desktop 844 : 450 video like 840 px+, text box sized by
   its lines. */
@media (min-width: 768px) and (max-width: 839px) {
    .vip-video-container { height: auto !important; aspect-ratio: 844 / 450; }
    .vip-video-badges { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .vip-video-badge { justify-content: flex-start !important; }
    .vip-video-badge span:not(.vip-badge-icon) { width: auto !important; height: auto !important; flex: 1 1 auto; min-width: 0; }
}
/* Lightbox: lightbox.jpg / lightbox-1.jpg (1920) and lightbox-2/-3 (360).
   Above the fixed header (the frames blur it with the page). */
.vip-lightbox { z-index: 1000; }
/* Frames mark the current thumbnail by full opacity only, no outline. */
.vip-lightbox-thumbnail { border: 0; box-shadow: 0 10px 20px rgba(92, 45, 98, 0.12); }
.vip-lightbox-thumbnail.active { border-color: transparent; }
@media (min-width: 768px) {
    .vip-lightbox { align-items: flex-start; padding: 70px 0 0; }
    .vip-lightbox-stage { width: auto; gap: 27px; }
    .vip-lightbox-main { width: auto; padding: 0 75px; }
    .vip-lightbox .swiper-slide img { width: var(--vip-lightbox-img-w, auto); height: var(--vip-lightbox-img-h, calc(100vh - 291px)); max-height: none; max-width: 100%; object-fit: cover; border-radius: 15px; box-shadow: 0 20px 40px rgba(92, 45, 98, 0.15); }
    .vip-lightbox-prev, .vip-lightbox-next { top: calc(50% + 21px); display: flex; align-items: center; justify-content: center; box-shadow: none; }
    .vip-lightbox-prev { left: 0; }
    .vip-lightbox-next { right: 0; }
    .vip-lightbox-close { top: 0; right: 6px; width: 34px; height: 34px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; box-shadow: none; }
    .vip-lightbox-thumbnails { gap: 12px; }
}
@media (max-width: 767px) {
    .vip-lightbox { align-items: flex-start; padding: 18px; }
    .vip-lightbox-stage { gap: 22px; }
    .vip-lightbox-main { padding: 0; }
    .vip-lightbox .swiper-slide { height: 512px; }
    .vip-lightbox .swiper-slide img { width: 100%; height: 100%; max-height: none; object-fit: contain; border-radius: 15px; }
    .vip-lightbox-close { top: 22px; right: 23px; width: 34px; height: 34px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; box-shadow: none; }
    .vip-lightbox-thumbnails { justify-content: flex-start; gap: 12px; overflow-x: auto; scrollbar-width: none; }
    .vip-lightbox-thumbnail { flex: 0 0 80px; }
    /* lightbox-2: the arrows sit under the thumbnails, at both edges. */
    .vip-lightbox-prev, .vip-lightbox-next { display: flex !important; align-items: center; justify-content: center; top: 677px; transform: none; box-shadow: none; }
    .vip-lightbox-prev { left: 0; }
    .vip-lightbox-next { right: 2px; }
}

/* phone 360 px-12: the three badges run the full 313 px width; the grid had
   an auto track centred by the desktop rule and shrank them to their text. */
@media (max-width: 767px) {
    .vip-video-badges { grid-template-columns: minmax(0, 1fr); justify-content: stretch; }
    .vip-video-badge { justify-content: flex-start !important; padding: 0 20px 0 23px !important; gap: 17px !important; border: 0 !important; box-shadow: 0 10px 20px rgba(92, 45, 98, 0.08); }
    .vip-video-badge span:not(.vip-badge-icon) { width: auto !important; height: auto !important; flex: 1 1 auto; }
    .vip-video-badge .vip-badge-icon { display: block; width: 40px !important; height: 40px !important; flex: 0 0 40px; color: #DD3888; }
    .vip-video-badge .vip-badge-icon svg { width: 100%; height: 100%; }
    .vip-slider-note { font-size: 13px; font-weight: 500; gap: 11px; }
    .vip-slider-note img { width: 20px; height: 20px; }

    /* Pass 1 overlay, phone 360 px-12 (no video) and -14 (video).
       Contact row: circles 61 / 106, number ends 291 (was 3 px right). */
    .vip-profile-contacts { padding-right: 6px; }
    /* -24 / -25 (online): the name row sits 4 px higher than offline (-12). */
    .vip-profile-name-row:has(.vip-profile-online-dot) { margin-top: 15px; }
    .vip-profile-name-row:has(.vip-profile-online-dot) ~ .vip-profile-links { margin-top: 11px; }
    /* First lower heading ink 58 px under the contact row (1185 → 1243). */
    .vip-about-card { margin-top: 56px; }
    .vip-video-card-wrap { margin-top: 56px; }
    .vip-profile-main:has(.vip-video-card-wrap) .vip-about-card { margin-top: 61px; }
    /* -14: "Video" ink 1243–1263, card 25–334 × 1297–1857. */
    .vip-video-card-title { margin-bottom: 26px; }
    /* "Více o mně" ink → first line ink 49 px (was 53). */
    .vip-about-card .vip-section-title { margin-bottom: 16px; }
    .vip-video-card, .vip-video-surface { width: 100%; border-radius: 15px; }
    /* Služby 60 px under the copy, pills 27 px under its ink. */
    .vip-services-block { margin-top: 51px !important; }
    .vip-services-block h3 { margin-bottom: 26px !important; }
    .vip-prices-block { margin-top: 63px !important; }
    .vip-prices-block h3 { margin-bottom: 26px !important; }
    /* Last separator → slider title ink 63 px; title → note 28, note →
       cards 28. */
    .vip-slider-section { margin-top: 61px !important; }
    .vip-slider-head { gap: 18px; }
    /* Price rows 36 px apart, first one 20 px under the header pills, no
       rule under the header, #E9E9E9 separators. */
    .vip-pricing-table { border-spacing: 0; }
    .vip-pricing-table th { padding: 0 0 20px; border-bottom: 0; vertical-align: top; }
    .vip-pricing-table th:first-child { display: table-cell; height: auto; background: transparent; }
    .vip-pricing-table th:nth-child(2) { padding-left: 5px; }
    .vip-pricing-table tbody tr { height: 36px; }
    .vip-pricing-table td,
    .vip-pricing-table td:nth-child(2),
    .vip-pricing-table td:nth-child(3) { padding: 0 8px; height: 36px; border-bottom: 1px solid #E9E9E9; }

    /* Pass 2 element measurement (phone 360 px-12 / -14).
       Lock 16 px, 5 px left of the half's centre (ink 246–261). */
    .vip-profile-rating-icons svg { width: 18px !important; height: 18px !important; }
    .vip-profile-rating-icons { padding-right: 23px; }
    .vip-profile-rating-icons svg { transform: translateY(1px); }
    /* Pin 14 × 16 (ink 111–124 × 766–781), 6 px to the text. */
    .vip-profile-meta-location { gap: 6px; }
    .vip-profile-meta-location svg { flex: 0 0 14px; width: 14px; height: 16px; transform: translateY(-1px); }
    /* Flags: icons 18 px ink, OutCall group 3 px left of centre, 7 px
       icon → text. */
    .vip-profile-flag img { flex: 0 0 22px; width: 22px !important; height: 22px !important; }
    .vip-profile-flag { gap: 6px; }
    .vip-profile-flag:nth-child(2) { padding-right: 14px; }
    /* Times end at ink x 82, prices at 202, dash 277–282; OutCall pill
       content from x 240. */
    .vip-pricing-table td:first-child { text-align: right; padding-right: 22px; }
    .vip-pricing-table td:nth-child(2) { text-align: right; padding-right: 16px; }
    .vip-pricing-table td:nth-child(3) { padding: 0; }
    .vip-pricing-table th:nth-child(3) .vip-price-pill { margin-left: 4px; }
    /* -14: video card 1297–1857 (561 px), "Více o mně" ink 1922. */
    .vip-video-card, .vip-video-surface { height: 561px; }
    .vip-profile-main:has(.vip-video-card-wrap) .vip-about-card { margin-top: 62px; }
    /* Slider note: diamond 22 px, 89 px under the title ink top (was 91),
       cards stay 139 px under it. */
    .vip-slider-head { gap: 16px; margin-bottom: 22px; }
    .vip-slider-note a { text-underline-offset: 2px; text-decoration-color: rgba(221, 56, 136, 0.6) !important; }
    .vip-slider-note img { width: 22px !important; height: 22px !important; }
    /* Dots: 10 px slots 3 px apart (x 149–210), the active one 8 px. */
    .vip-rec-slider .swiper-pagination { gap: 3px !important; }
    .vip-rec-slider .swiper-pagination-bullet { margin: 0 !important; }
    .vip-rec-slider .swiper-pagination-bullet-active { margin: 0 1px !important; }
    /* Arrows → next title ink 83 px (both sliders and "Nový svět"). */
    .vip-slider-section + .vip-slider-section { margin-top: 82px !important; }
    /* "Nový svět" block (phone -12): heading ink from x 27, lines 32 px;
       video 25–334 × 166, 32 px under the heading ink; badges 22–334,
       75 px tall, 11 px apart and under the video, text 2 px below the
       centre, shadow a 10 px band under each; button 40 px under. */
    .vip-video-section { margin: 82px 0 0 22px !important; gap: 0 !important; }
    .vip-video-heading { padding-left: 3px !important; }
    .vip-video-heading-line1, .vip-video-heading-line2 { line-height: 32px !important; }
    .vip-video-container { margin: 32px 0 0 3px !important; height: 166px !important; }
    .vip-video-badges { margin-top: 11px !important; gap: 11px !important; }
    .vip-video-badge { padding: 0 20px 0 21px !important; gap: 19px !important; box-shadow: 0 10px 2px rgba(92, 45, 98, 0.14), 0 0 2px rgba(92, 45, 98, 0.12) !important; }
    .vip-video-badge span:not(.vip-badge-icon) { line-height: 20px !important; position: relative; top: 2px; }
    .vip-video-register-btn { margin-top: 39px !important; }
    /* Button → footer logo ink 107 px (was 86). */
    .vip-profile-page { padding-bottom: 69px !important; }
}
</style>

<div class="vip-profile-page">
    @if(auth()->id() === $profile->user_id && (!$profile->is_public || $profile->status !== 'approved'))
        <p role="status" class="rounded-lg bg-purple-50 p-4 text-center text-purple-900">{{ __('front.profiles.detail_page.owner_preview') }}</p>
    @endif
    <section class="vip-profile-hero">
        <aside class="vip-profile-panel">
            <div class="vip-profile-status-bar">
                @if($isVipProfile)
                <span class="vip-profile-status-pill vip-profile-status-pill--primary">
                    <img src="{{ asset('images/icons/star.svg') }}" alt="" aria-hidden="true">
                    {{ __('front.profiles.detail_page.vip_profile_badge') }}
                </span>
                @endif
                @if($showPhotoStatus)
                <span class="vip-profile-status-pill vip-profile-status-pill--verification {{ $isVerifiedProfile ? 'is-verified' : '' }}">
                    <img src="{{ asset('images/icons/' . ($isVerifiedProfile ? 'camera.svg' : 'CameraOff.svg')) }}" alt="" aria-hidden="true">
                    {{ $photoStatusLabel }}
                </span>
                @endif
                @foreach($profile->allSegments()->reject(fn ($segment) => $segment['is_vip']) as $segment)
                <span class="vip-profile-status-pill" style="background: {{ $segment['color'] }}1A; color: {{ $segment['color'] }};">
                    {{ $segment['name'] }}
                </span>
                @endforeach
            </div>


            <div class="vip-profile-name-row">
                @if($isOnline)
                    <span class="vip-profile-online-dot" aria-hidden="true"></span>
                @endif
                <h1 class="vip-profile-name">{{ $profile->display_name }}</h1>
            </div>

            @if($isOnline)
            <div class="vip-profile-online-badge">
                <span>{{ __('front.profiles.list.online') }}</span>
            </div>
            @endif

            <div class="vip-profile-links" aria-label="Profile actions">
                {{-- Both used to be href="#". "Obnovit přístup" is password
                     recovery, "Dát hodnocení" opens the rating screen with
                     this profile preselected; a guest gets the login modal.
                     Figma phone frame: three plain underlined links, the
                     report link short ("Nahlásit") and pushed to the right. --}}
                <a href="{{ $accessUrl }}" class="vip-profile-link">{{ $accessLabel }}</a>
                @auth
                    <a href="{{ $rateUrl }}" class="vip-profile-link">{{ __('front.profiles.detail_page.give_rating') }}</a>
                @else
                    <a href="#" class="vip-profile-link" x-data @click.prevent="$dispatch('show-login-modal')">{{ __('front.profiles.detail_page.give_rating') }}</a>
                @endauth
                <a href="#" class="vip-profile-link vip-profile-link--report" x-data @click.prevent="$dispatch('show-report-modal', { profileId: {{ $profile->id }} })" aria-label="{{ __('front.profiles.detail_page.report_profile') }}">{{ __('front.profiles.detail_page.report_short') }}</a>
            </div>

            <div class="vip-profile-rating-summary">
                <strong>{{ __('front.profiles.detail_page.rating_label') }}</strong>
                <span class="vip-profile-rating-icons">
                    @if($ratingsVisible && $totalRatings > 0)
                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="color:#FFC107;">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span>{{ number_format($averageRating, 1) }}</span>
                    @elseif(!$ratingsVisible)
                        <x-icons name="lock" class="inline-block" style="width:18px;height:18px;color:#FF4DA6;" />
                    @else
                        <x-empty-value />
                    @endif
                </span>
            </div>

            @if($locationLabel)
            <div class="vip-profile-meta-location">
                <svg width="14" height="16" viewBox="0 0 14 16" fill="none" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M7 0C3.13 0 0 3.05 0 6.82c0 2.2 1.3 4.33 2.63 5.86A20.6 20.6 0 0 0 6.4 15.8a1.05 1.05 0 0 0 1.2 0 20.6 20.6 0 0 0 3.77-3.12C12.7 11.15 14 9.02 14 6.82 14 3.05 10.87 0 7 0Zm0 4.2a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5Z" fill="#DD3888"/></svg>
                <span>{{ $locationLabel }}</span>
            </div>
            @endif

            <div class="vip-profile-meta-table">
                <div class="vip-profile-meta-row">
                    <span class="vip-profile-meta-label">{{ __('front.profiles.detail_page.age') }}</span>
                    <span class="vip-profile-meta-value">
                        @if(filled($profile->age))
                            {{ $profile->age }} {{ __('front.profiles.detail_page.years') }}
                        @else
                            <x-empty-value />
                        @endif
                    </span>
                </div>
                <div class="vip-profile-meta-row">
                    <span class="vip-profile-meta-label">{{ __('front.profiles.detail_page.weight') }}</span>
                    <span class="vip-profile-meta-value">
                        @if($weightKg)
                            {{ $weightKg . ' ' . __('front.profiles.detail_page.kg') . ($weightLbs ? ' / ' . $weightLbs . ' ' . __('front.profiles.detail_page.lbs') : '') }}
                        @else
                            &mdash;
                        @endif
                    </span>
                </div>
                <div class="vip-profile-meta-row">
                    <span class="vip-profile-meta-label">{{ __('front.profiles.detail_page.height') }}</span>
                    <span class="vip-profile-meta-value">
                        @if($heightCm)
                            {{ $heightCm . ' ' . __('front.profiles.detail_page.cm') . ($heightFeet ? ' / ' . $heightFeet : '') }}
                        @else
                            &mdash;
                        @endif
                    </span>
                </div>
                <div class="vip-profile-meta-row">
                    <span class="vip-profile-meta-label">{{ __('front.profiles.detail_page.bust') }}</span>
                    <span class="vip-profile-meta-value">{{ $profile->bust_size ?: '—' }}</span>
                </div>
                <div class="vip-profile-meta-row">
                    <span class="vip-profile-meta-label">{{ __('front.profiles.detail_page.languages') }}</span>
                    <span class="vip-profile-meta-value">
                        @if(filled($languages))
                            {{ $languages }}
                        @else
                            <x-empty-value />
                        @endif
                    </span>
                </div>
            </div>

            <div class="vip-profile-flags">
                <div class="vip-profile-flag {{ $profile->incall ? 'vip-profile-flag--incall' : 'vip-profile-flag--outcall' }}">
                    <span class="vip-profile-flag-status">
                        <img src="{{ asset('images/icons/' . ($profile->incall ? 'CircleCheck.svg' : 'CircleX.svg')) }}" alt="" aria-hidden="true">
                    </span>
                    <span>InCall</span>
                </div>
                <div class="vip-profile-flag {{ $profile->outcall ? 'vip-profile-flag--incall' : 'vip-profile-flag--outcall' }}">
                    <span class="vip-profile-flag-status">
                        <img src="{{ asset('images/icons/' . ($profile->outcall ? 'CircleCheck.svg' : 'CircleX.svg')) }}" alt="" aria-hidden="true">
                    </span>
                    <span>OutCall</span>
                </div>
            </div>

            @auth
                @if($profile->user_id && $profile->user_id !== auth()->id() && $messageRouteAvailable)
                    <a href="{{ route('messages.show', $profile->user) }}" class="vip-profile-message">
                        {{ __('front.profiles.detail_page.send_message') }}
                        <img src="{{ asset('images/icons/message.svg') }}" alt="" aria-hidden="true" class="h-4 w-4 brightness-0 invert">
                    </a>
                @else
                    <span class="vip-profile-message" style="opacity:.6;pointer-events:none;">{{ __('front.profiles.detail_page.send_message') }}</span>
                @endif
            @else
                <a href="{{ $registerRouteAvailable ? route('register') : route('profiles.index') }}" class="vip-profile-message">
                    {{ __('front.profiles.detail_page.send_message') }}
                    <img src="{{ asset('images/icons/message.svg') }}" alt="" aria-hidden="true" class="h-4 w-4 brightness-0 invert">
                </a>
            @endauth

            <div class="vip-profile-contacts">
                @if($contactDigits && $profile->has_whatsapp)
                    <a class="vip-profile-contact-circle vip-profile-contact-circle--whatsapp" href="https://wa.me/{{ $contactDigits }}" target="_blank" rel="noreferrer" title="WhatsApp">
                        <img src="{{ asset('images/icons/whatsapp.svg') }}" alt="whatsapp" style="width: 20px; height: 20px;">
                    </a>
                @endif
                @if($contactDigits && $profile->has_telegram)
                    <a class="vip-profile-contact-circle vip-profile-contact-circle--telegram" href="https://t.me/+{{ $contactDigits }}" target="_blank" rel="noreferrer" title="Telegram">
                        <img src="{{ asset('images/icons/telegram.svg') }}" alt="telegram" style="width: 20px; height: 20px;">
                    </a>
                @endif
                @if($contactPhone)
                    <a href="tel:{{ preg_replace('/\s+/', '', $contactPhone) }}" class="vip-profile-phone">
                        <span>{{ $contactPhone }}</span>
                    </a>
                @endif
            </div>
        </aside>

        <div class="vip-profile-main">
            <div class="vip-profile-desktop-actions hidden lg:grid" aria-label="Profile actions">
                <div class="vip-profile-desktop-actions-inner">
                    @auth
                        <a href="{{ $rateUrl }}" class="vip-profile-desktop-action">
                            <img src="{{ asset('images/icons/pinkStar.svg') }}" alt="" aria-hidden="true">
                            <span>{{ __('front.profiles.detail_page.give_rating') }}</span>
                        </a>
                    @else
                        <a href="#" class="vip-profile-desktop-action" x-data @click.prevent="$dispatch('show-login-modal')">
                            <img src="{{ asset('images/icons/pinkStar.svg') }}" alt="" aria-hidden="true">
                            <span>{{ __('front.profiles.detail_page.give_rating') }}</span>
                        </a>
                    @endauth
                    <a href="{{ $accessUrl }}" class="vip-profile-desktop-action">
                        <img src="{{ asset('images/icons/KeySquare.svg') }}" alt="" aria-hidden="true">
                        <span>{{ $accessLabel }}</span>
                    </a>
                    <a href="#" class="vip-profile-desktop-action" x-data @click.prevent="$dispatch('show-report-modal', { profileId: {{ $profile->id }} })">
                        <img src="{{ asset('images/icons/TriangleAlert.svg') }}" alt="" aria-hidden="true">
                        <span>{{ __('front.profiles.detail_page.report_profile') }}</span>
                    </a>
                </div>
            </div>

            <div class="vip-profile-gallery-card">
                <div class="vip-profile-favorite">
                    @auth
                        @if(auth()->user()->isMale())
                            @livewire('favorite-button', ['profile' => $profile], key('favorite-'.$profile->id))
                        @else
                            <button type="button" class="vip-profile-static-favorite" disabled title="{{ __('front.favorites.members_only') }}" aria-label="{{ __('front.favorites.members_only') }}">
                                <img src="{{ asset('images/icons/heart.svg') }}" alt="" aria-hidden="true">
                            </button>
                        @endif
                    @else
                        <button type="button" class="vip-profile-static-favorite" x-data @click="$dispatch('show-login-modal')" aria-label="{{ __('front.favorites.login_required') }}">
                            <img src="{{ asset('images/icons/heart.svg') }}" alt="" aria-hidden="true">
                        </button>
                    @endauth
                </div>

                {{-- The three-card desktop gallery keeps its layout whether the
                     profile has three photos, one, or none. It used to be padded
                     out with stock model images so it always looked full. --}}
                <div class="vip-gallery-desktop {{ $isVipProfile ? '' : 'vip-gallery-desktop--four' }}" data-slides='@json($gallerySlides->values())'>
                    @if($hasGallery)
                        <button type="button" class="vip-gallery-desktop-card vip-gallery-desktop-left lightbox-trigger" data-index="0">
                            <img src="{{ $heroSlides->get(0, $heroSlides->first()) }}" alt="{{ $profile->display_name }}">
                        </button>
                        <button type="button" class="vip-gallery-desktop-card vip-gallery-desktop-main lightbox-trigger" data-index="{{ $heroSlides->count() > 1 ? 1 : 0 }}">
                            <img src="{{ $heroSlides->get(1, $heroSlides->first()) }}" alt="{{ $profile->display_name }}">
                        </button>
                        <button type="button" class="vip-gallery-desktop-card vip-gallery-desktop-right lightbox-trigger" data-index="{{ $heroSlides->count() > 2 ? 2 : 0 }}">
                            <img src="{{ $heroSlides->get(2, $heroSlides->first()) }}" alt="{{ $profile->display_name }}">
                            @if($gallerySlides->count() > 1 && $isVipProfile)
                                <span class="vip-gallery-next-hint" aria-hidden="true"></span>
                            @endif
                        </button>
                        @unless($isVipProfile)
                            <button type="button" class="vip-gallery-desktop-card vip-gallery-desktop-extra lightbox-trigger" data-index="{{ $heroSlides->count() > 3 ? 3 : 0 }}">
                                <img src="{{ $heroSlides->get(3, $heroSlides->first()) }}" alt="{{ $profile->display_name }}">
                                <span class="vip-gallery-next-hint" aria-hidden="true"></span>
                            </button>
                        @endunless
                        @if($gallerySlides->count() > 1)
                            <button type="button" class="vip-gallery-desktop-nav vip-gallery-desktop-prev" id="vip-gallery-desktop-prev" aria-label="Previous slide"><svg width="7" height="9" viewBox="0 0 7 9" aria-hidden="true"><path d="M0 4.5 7 0v9z" fill="#fff"/></svg></button>
                            <button type="button" class="vip-gallery-desktop-nav vip-gallery-desktop-next" id="vip-gallery-desktop-next" aria-label="Next slide"><svg width="7" height="9" viewBox="0 0 7 9" aria-hidden="true"><path d="M7 4.5 0 9V0z" fill="#fff"/></svg></button>
                        @endif
                    @else
                        @foreach (['left', 'main', 'right'] as $slot)
                            <div class="vip-gallery-desktop-card vip-gallery-desktop-{{ $slot }}" style="display:flex;align-items:center;justify-content:center;background:#f6eef7;">
                                <svg class="h-16 w-16 text-[#d6c7dc]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="vip-profile-gallery-mobile">
                    @if($gallerySlides->count() > 1)
                        <button type="button" class="vip-gallery-nav vip-gallery-nav--prev" aria-label="Previous image"><svg width="7" height="9" viewBox="0 0 7 9" aria-hidden="true"><path d="M0 4.5 7 0v9z" fill="#fff"/></svg></button>
                        <button type="button" class="vip-gallery-nav vip-gallery-nav--next" aria-label="Next image"><svg width="7" height="9" viewBox="0 0 7 9" aria-hidden="true"><path d="M7 4.5 0 9V0z" fill="#fff"/></svg></button>

                        <div class="swiper vip-profile-gallery-swiper">
                            <div class="swiper-wrapper">
                                @foreach($gallerySlides as $index => $imageUrl)
                                    <div class="swiper-slide">
                                        <button type="button" class="vip-gallery-slide lightbox-trigger" data-index="{{ $index }}">
                                            <img src="{{ $imageUrl }}" alt="{{ $profile->display_name }}">
                                            @unless($loop->last)
                                                <span class="vip-gallery-next-hint" aria-hidden="true"></span>
                                            @endunless
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @elseif($gallerySlides->count() === 1)
                        <button type="button" class="vip-gallery-slide lightbox-trigger" data-index="0">
                            <img src="{{ $gallerySlides->first() }}" alt="{{ $profile->display_name }}">
                        </button>
                    @else
                        <div class="vip-gallery-slide" style="display:flex;align-items:center;justify-content:center;">
                            <svg class="h-20 w-20 text-[#d6c7dc]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

            <section class="vip-about-card">
                <h2 class="vip-section-title">{{ __('front.profiles.detail_page.about_me') }}</h2>
                <div class="vip-about-copy">
                    @if(filled($aboutText))
                        {{ $aboutText }}
                    @else
                        <x-empty-value variant="text" />
                    @endif
                </div>
            </section>

            {{-- Always rendered. The section used to be hidden unless the profile
                 had a video, prices or services — but the prices themselves were
                 invented when missing, so in practice it was always "full". The
                 section now stays put and each block states plainly when the
                 provider has not filled it in. --}}
                {{-- The video block only exists when there is a video (Figma:
                     the "Eva" phone frame has one, "Alexandrina" does not). It
                     used to fall back to the first photo with a play button
                     that played nothing. --}}
                <section class="vip-media-grid {{ $profile->hasVideo() ? '' : 'vip-media-grid--single' }}">
                    @if($profile->hasVideo())
                    <div class="vip-video-card-wrap">
                        <h3 class="vip-video-card-title">Video</h3>
                        <div class="vip-video-card">
                            <div class="vip-video-surface">
                                <video id="vip-profile-video" src="{{ $profile->getVideoUrl() }}" preload="metadata" playsinline @if($videoPoster) poster="{{ $videoPoster }}" @endif></video>
                                <button type="button" class="vip-video-play" id="vip-profile-video-toggle" aria-label="Play video">
                                    <span class="vip-video-play__inner">
                                        <img id="vip-profile-video-play-icon" src="{{ asset('images/icons/arrowFilled.svg') }}" alt="" class="w-7 h-7" style="filter: brightness(0) invert(1); transform: rotate(180deg);">
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="vip-pricing-card">
                        @if(app()->getLocale() === 'en' && $globalPrices->isNotEmpty())
                            <div class="vip-prices-block">
                                <h3>{{ __('front.profiles.detail_page.my_prices') }}</h3>
                                <table class="vip-pricing-table">
                                    <thead>
                                        <tr>
                                            <th><span class="vip-price-pill">{{ __('front.profiles.detail_page.time') }}</span></th>
                                            <th>
                                                <span class="vip-price-pill">
                                                    @if($profile->incall)
                                                        <img src="{{ asset('images/icons/CircleCheck.svg') }}" alt="" aria-hidden="true" class="w-5 h-5">
                                                    @else
                                                        <img src="{{ asset('images/icons/CircleX.svg') }}" alt="" aria-hidden="true" class="w-5 h-5">
                                                    @endif
                                                    InCall
                                                </span>
                                            </th>
                                            <th>
                                                <span class="vip-price-pill">
                                                    @if($profile->outcall)
                                                        <img src="{{ asset('images/icons/CircleCheck.svg') }}" alt="" aria-hidden="true" class="w-5 h-5">
                                                    @else
                                                        <img src="{{ asset('images/icons/CircleX.svg') }}" alt="" aria-hidden="true" class="w-5 h-5">
                                                    @endif
                                                    OutCall
                                                </span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($globalPrices as $price)
                                            <tr>
                                                <td>{{ (float) ($price['time_hours'] ?? 0) }}&nbsp;h</td>
                                                <td>
                                                    @if(!empty($price['incall_price']) && $profile->incall)
                                                        {{ number_format($price['incall_price'], 0, ',', ' ') }} {{ $globalCurrency }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(!empty($price['outcall_price']) && $profile->outcall)
                                                        {{ number_format($price['outcall_price'], 0, ',', ' ') }} {{ $globalCurrency }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif($displayPrices->isNotEmpty())
                            <div class="vip-prices-block">
                                <h3>{{ __('front.profiles.detail_page.my_prices') }}</h3>
                                <table class="vip-pricing-table">
                                    <thead>
                                        <tr>
                                            <th><span class="vip-price-pill">{{ __('front.profiles.detail_page.time') }}</span></th>
                                            <th>
                                                <span class="vip-price-pill">
                                                    @if($profile->incall)
                                                        <img src="{{ asset('images/icons/CircleCheck.svg') }}" alt="" aria-hidden="true" class="w-5 h-5">
                                                    @else
                                                        <img src="{{ asset('images/icons/CircleX.svg') }}" alt="" aria-hidden="true" class="w-5 h-5">
                                                    @endif
                                                    InCall
                                                </span>
                                            </th>
                                            <th>
                                                <span class="vip-price-pill">
                                                    @if($profile->outcall)
                                                        <img src="{{ asset('images/icons/CircleCheck.svg') }}" alt="" aria-hidden="true" class="w-5 h-5">
                                                    @else
                                                        <img src="{{ asset('images/icons/CircleX.svg') }}" alt="" aria-hidden="true" class="w-5 h-5">
                                                    @endif
                                                    OutCall
                                                </span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($displayPrices as $price)
                                            <tr>
                                                <td>{{ (float) ($price['time_hours'] ?? 0) }}&nbsp;h</td>
                                                <td>
                                                    @if(!empty($price['incall_price']) && $profile->incall)
                                                        {{ number_format($price['incall_price'], 0, ',', ' ') }} Kč
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(!empty($price['outcall_price']) && $profile->outcall)
                                                        {{ number_format($price['outcall_price'], 0, ',', ' ') }} Kč
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="vip-prices-block">
                                <h3>{{ __('front.profiles.detail_page.my_prices') }}</h3>
                                <p><x-empty-value variant="text" /></p>
                            </div>
                        @endif

                        <div class="vip-services-block" style="margin-top:24px;">
                            <h3 style="margin-bottom:14px;">{{ __('front.profiles.detail_page.services') }}</h3>
                            @if($displayServices->isNotEmpty())
                                <div class="vip-services-grid">
                                    @foreach($displayServices as $serviceName)
                                        <span class="vip-service-pill">{{ $serviceName }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p><x-empty-value variant="text" /></p>
                            @endif
                        </div>
                    </div>
                </section>
        </div>
    </section>

    <section class="vip-slider-section">
        <div class="vip-slider-head">
            <div>
                {{-- One heading: the phone frame runs "tento měsíc" on in pink
                     after "dívky", the desktop frame breaks it onto a line of
                     its own. --}}
                <h2 class="vip-section-title vip-slider-title">{{ __('front.profiles.detail_page.top_rated_girls') }} <span class="vip-slider-kicker">{{ __('front.profiles.detail_page.this_month') }}</span></h2>
            </div>
            {{-- Looked like a link, went nowhere, and told a member who already
                 has Premium to go and get it. It now states what is true for
                 this visitor and, when there is something to do about it,
                 takes them there. --}}
            <div class="vip-slider-note">
                <img src="{{ asset('images/icons/diamond.svg') }}" alt="" aria-hidden="true" class="h-4 w-4">
                @if($accessHasMembership)
                    <span style="color: #505050;">{{ $membershipValidLabel }}</span>
                @else
                    <a href="{{ $premiumBuyUrl }}" style="text-decoration: underline; color: #DD3888;">{{ __('front.profiles.detail_page.premium_unlocks_rating') }}</a>
                @endif
            </div>
        </div>
        <div class="vip-rec-slider">
            <livewire:profile-slider sort-by="rating_this_month" sort-direction="desc" :limit="30" card-variant="vip-detail" :exclude-profile-id="$profile->id" :key="'vip-month-'.$profile->id" />
        </div>
    </section>

    <section class="vip-slider-section">
        <div class="vip-slider-head">
            <div>
                {{-- One heading: the phone frame runs "tento měsíc" on in pink
                     after "dívky", the desktop frame breaks it onto a line of
                     its own. --}}
                <h2 class="vip-section-title vip-slider-title">{{ __('front.profiles.detail_page.top_rated_girls') }} <span class="vip-slider-kicker">{{ __('front.profiles.detail_page.all_time') }}</span></h2>
            </div>
            {{-- Looked like a link, went nowhere, and told a member who already
                 has Premium to go and get it. It now states what is true for
                 this visitor and, when there is something to do about it,
                 takes them there. --}}
            <div class="vip-slider-note">
                <img src="{{ asset('images/icons/diamond.svg') }}" alt="" aria-hidden="true" class="h-4 w-4">
                @if($accessHasMembership)
                    <span style="color: #505050;">{{ $membershipValidLabel }}</span>
                @else
                    <a href="{{ $premiumBuyUrl }}" style="text-decoration: underline; color: #DD3888;">{{ __('front.profiles.detail_page.premium_unlocks_rating') }}</a>
                @endif
            </div>
        </div>
        <div class="vip-rec-slider">
            {{-- The heading beside this slider says "all time", but it was
                 given the same rating_this_month sort as the section above, so
                 both listed exactly the same profiles. --}}
            <livewire:profile-slider sort-by="rating" sort-direction="desc" :limit="30" card-variant="vip-detail" :exclude-profile-id="$profile->id" :key="'vip-all-'.$profile->id" />
        </div>
    </section>

    <section class="vip-video-section">
        <div class="vip-video-heading">
            <div class="vip-video-heading-line1">{{ __('front.profiles.detail_page.new_world_heading') }}</div>
            <div class="vip-video-heading-line2">{{ __('front.profiles.detail_page.become_member', ['brand' => app()->getLocale() === 'en' ? 'Escort-Online.com' : 'ZašukejSi.cz']) }}</div>
        </div>
        <div class="vip-video-container">
            <div class="vip-video-play-btn">
                <img src="{{ asset('images/icons/arrowFilled.svg') }}" alt="" aria-hidden="true" style="width:50px;height:50px;transform:rotate(180deg);filter:brightness(0) invert(1);">
            </div>
        </div>
        <div class="vip-video-badges">
            <div class="vip-video-badge">
                <img src="{{ asset('images/icons/Banana.svg') }}" alt="" aria-hidden="true" style="width:40px;height:40px;">
                <span style="font-family:'Poppins', sans-serif;font-weight:400;font-size:13px;color:#505050;text-align:left;line-height:1.3;">{{ __('front.profiles.detail_page.swingers_events') }}</span>
            </div>
            <div class="vip-video-badge">
                <img src="{{ asset('images/icons/ThumbsUp.svg') }}" alt="" aria-hidden="true" style="width:40px;height:40px;">
                <span style="font-family:'Poppins', sans-serif;font-weight:400;font-size:13px;color:#505050;text-align:left;line-height:1.3;">{{ __('front.profiles.detail_page.community_ratings') }}</span>
            </div>
            <div class="vip-video-badge">
                {{-- Desktop frame: ice-cream icon; phone frame: two people. --}}
                <img src="{{ asset('images/icons/icecreamPink.svg') }}" alt="" aria-hidden="true" class="hidden md:block" style="width:40px;height:40px;">
                <x-icons name="users" class="vip-badge-icon md:hidden" />
                <span style="font-family:'Poppins', sans-serif;font-weight:400;font-size:13px;color:#505050;text-align:left;line-height:1.3;">{{ __('front.profiles.detail_page.girls_database') }}</span>
            </div>
        </div>
        <button type="button" class="vip-video-register-btn" x-data @click="$dispatch('show-register-modal')">{{ __('auth.register.register_button') }}</button>
    </section>
</div>

@if(true)
    <div class="vip-lightbox" id="vip-lightbox">
        <div class="vip-lightbox-stage">
            <div class="vip-lightbox-main">
                <button type="button" class="vip-lightbox-close" id="vip-lightbox-close" aria-label="Close">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true"><path d="M1.5 1.5l9 9m0-9l-9 9" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <button type="button" class="vip-lightbox-prev" id="vip-lightbox-prev" aria-label="Previous"><svg width="8" height="10" viewBox="0 0 8 10" aria-hidden="true"><path d="M0 5l8-5v10z" fill="#FFFFFF"/></svg></button>
                <button type="button" class="vip-lightbox-next" id="vip-lightbox-next" aria-label="Next"><svg width="8" height="10" viewBox="0 0 8 10" aria-hidden="true"><path d="M8 5L0 0v10z" fill="#FFFFFF"/></svg></button>

                <div class="swiper vip-lightbox-swiper">
                    <div class="swiper-wrapper">
                        @foreach($gallerySlides as $imageUrl)
                            <div class="swiper-slide">
                                <img src="{{ $imageUrl }}" alt="{{ $profile->display_name }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="vip-lightbox-thumbnails">
                @foreach($gallerySlides as $index => $imageUrl)
                    <button type="button" class="vip-lightbox-thumbnail {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}" aria-label="Slide {{ $index + 1 }}">
                        <img src="{{ $imageUrl }}" alt="{{ $profile->display_name }}">
                    </button>
                @endforeach
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script>
    (function() {
        function initProfileDetailSwiper() {
            if (typeof Swiper === 'undefined') {
                // Swiper not yet loaded — wait for the ready event
                document.addEventListener('swiper:ready', initProfileDetailSwiper, { once: true });
                return;
            }

            const galleryEl = document.querySelector('.vip-profile-gallery-swiper');
            let gallerySwiper = null;
            if (galleryEl) {
                gallerySwiper = new Swiper(galleryEl, {
                    loop: {{ $gallerySlides->count() > 3 ? 'true' : 'false' }},
                    // Phone frame: one photo at full width, the next one
                    // 3px to its right, peeking in from the edge.
                    slidesPerView: 1,
                    spaceBetween: 3,
                    centeredSlides: false,
                    navigation: {
                        nextEl: '.vip-gallery-nav--next',
                        prevEl: '.vip-gallery-nav--prev',
                    },
                });
            }

            // Desktop tiles: the arrows used to drive only the (hidden) phone
            // swiper, so on desktop they did nothing. Rotate the tiles instead.
            const desktopGallery = document.querySelector('.vip-gallery-desktop[data-slides]');
            if (desktopGallery) {
                let slides = [];
                try { slides = JSON.parse(desktopGallery.dataset.slides || '[]'); } catch (e) { slides = []; }
                const tiles = ['left', 'main', 'right', 'extra']
                    .map(function (slot) { return desktopGallery.querySelector('.vip-gallery-desktop-' + slot); })
                    .filter(Boolean);
                let offset = 0;
                function showDesktopTiles() {
                    tiles.forEach(function (tile, i) {
                        const index = (offset + i) % slides.length;
                        const img = tile.querySelector('img');
                        if (img) img.src = slides[index];
                        tile.dataset.index = index;
                    });
                }
                if (slides.length > 1) {
                    const prev = desktopGallery.querySelector('.vip-gallery-desktop-prev');
                    const next = desktopGallery.querySelector('.vip-gallery-desktop-next');
                    if (prev) prev.addEventListener('click', function () { offset = (offset - 1 + slides.length) % slides.length; showDesktopTiles(); });
                    if (next) next.addEventListener('click', function () { offset = (offset + 1) % slides.length; showDesktopTiles(); });
                }
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

                // lightbox.jpg / lightbox-1.jpg: the photo is as tall as the
                // window allows (70 px above, thumbnails below) and the arrows
                // and the round close button hug its edges, so the stage is
                // sized to the photo on screen, portrait or landscape.
                const lightboxMain = lightbox.querySelector('.vip-lightbox-main');
                // update() inside a looping swiper can emit slideChange, whose
                // handler fits again: without this guard every arrow click
                // ended in "Maximum call stack size exceeded" and the
                // thumbnail strip never followed the photo.
                let fittingLightbox = false;
                function fitLightbox() {
                    if (fittingLightbox) return;
                    fittingLightbox = true;
                    try { fitLightboxNow(); } finally { fittingLightbox = false; }
                }
                function fitLightboxNow() {
                    if (!lightboxMain || window.innerWidth < 768) {
                        if (lightboxMain) lightboxMain.style.width = '';
                        lightboxSwiper.update();
                        return;
                    }
                    const slide = lightboxSwiper.slides[lightboxSwiper.activeIndex];
                    const img = slide ? slide.querySelector('img') : null;
                    const h = Math.min(954, window.innerHeight - 291);
                    let w = h;
                    if (img && img.naturalWidth && img.naturalHeight) {
                        w = h * img.naturalWidth / img.naturalHeight;
                    }
                    w = Math.min(w, window.innerWidth - 210);
                    lightbox.style.setProperty('--vip-lightbox-img-w', Math.round(w) + 'px');
                    lightbox.style.setProperty('--vip-lightbox-img-h', Math.round(Math.min(h, w * (img && img.naturalWidth ? img.naturalHeight / img.naturalWidth : 1))) + 'px');
                    lightboxMain.style.width = (Math.round(w) + 150) + 'px';
                    lightboxSwiper.update();
                }
                lightbox.querySelectorAll('.swiper-slide img').forEach(function (img) {
                    img.addEventListener('load', function () { if (lightbox.classList.contains('is-open')) fitLightbox(); });
                });
                window.addEventListener('resize', function () { if (lightbox.classList.contains('is-open')) fitLightbox(); });

                document.querySelectorAll('.lightbox-trigger').forEach(function (trigger) {
                    trigger.addEventListener('click', function () {
                        const index = Number(this.dataset.index || 0);
                        lightbox.classList.add('is-open');
                        document.body.style.overflow = 'hidden';
                        lightboxSwiper.slideToLoop(index, 0);
                        // A jump with speed 0 while the box was hidden does
                        // not always fire slideChange: the first thumbnail
                        // stayed marked whichever photo was opened.
                        document.querySelectorAll('.vip-lightbox-thumbnail').forEach(function (thumb, i) {
                            thumb.classList.toggle('active', i === index);
                        });
                        fitLightbox();
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
                        // The swiper loops, so its own indices are shifted by
                        // the cloned slides; slideTo() opened the wrong photo.
                        lightboxSwiper.slideToLoop(slideIndex, 0);
                        
                        // Update active state
                        document.querySelectorAll('.vip-lightbox-thumbnail').forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                    });
                });

                // Update thumbnail active state on slide change
                lightboxSwiper.on('slideChange', function () {
                    const currentIndex = lightboxSwiper.realIndex;
                    document.querySelectorAll('.vip-lightbox-thumbnail').forEach((thumb, index) => {
                        thumb.classList.toggle('active', index === currentIndex);
                    });
                    if (lightbox.classList.contains('is-open')) fitLightbox();
                });
            }

            const desktopNext = document.getElementById('vip-gallery-desktop-next');

            // The tiles rotate on this arrow (above); it also used to open the
            // lightbox on top of them, so one click did two things.
            if (desktopNext && !desktopGallery) {
                desktopNext.addEventListener('click', function () {
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
@endpush
