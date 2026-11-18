@php
    $isEnglishHomepage = app()->getLocale() === 'en';
    $profileGridClasses = $isEnglishHomepage
        // EN has the 208 px country sidebar: from 1024 to 1279 px only three
        // 214 px cards fit beside it (four overlapped by up to 46 px).
        ? 'flex flex-col md:grid md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-4 profile-list-cards-grid'
        : 'flex flex-col md:grid md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 profile-list-cards-grid';
    $ecoBadgeInsertAt = $isEnglishHomepage ? 5 : 6;
    $advertInsertAt = $isEnglishHomepage ? 9 : 11;
    $hasSelectedLocation = filled($region) || filled($country);
    $selectedLocationTitle = filled($region) ? $region : $country;
    $selectedLocationSubtitle = filled($region) && filled($country) ? $country : null;
    $showSelectedCountryFlag = blank($region) && filled($countryCode);
    $selectedCountryFlagUrl = $showSelectedCountryFlag ? 'https://flagcdn.com/' . mb_strtolower($countryCode) . '.svg' : null;
@endphp

<div>
    @unless($isEnglishHomepage)
    <div class="mt-1 md:mt-2 md:px-8 lg:px-12 py-4 md:py-9 flex items-center gap-2 md:gap-4 pl-pad pl-heading">
        <x-icons name="search" class="w-5 h-5 md:w-7 md:h-7 pl-heading__icon" style="color: #DD3888;" />
        <{{ $headingTag }} class="text-2xl md:text-4xl font-bold pl-heading__title" style="color: #5C2D62;">{{ $listTitle ?? __('front.profiles.list.topresults') }}</{{ $headingTag }}>
    </div>
    @endunless

    <!-- Quick Filters -->
    <div class="mb-4 md:mb-8 md:px-8 lg:px-12 pl-pad pl-filters" x-data="{
        init() {
            Livewire.on('filters-updated', () => {
                this.$nextTick(() => {
                    // Re-evaluate Alpine bindings if needed
                });
            });
        }
    }">
        <style>
            .filter-pill {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                /* 4px + 21px line-height + 4px + 4px border = 33px, matching the
                   design's pill. Was 8px, which made them 41px tall. */
                padding: 4px 16px;
                border-radius: 9999px;
                font-size: 14px;
                font-weight: 500;
                transition: all 0.2s ease-in-out;
                cursor: pointer;
                border: 2px solid transparent;
                box-shadow: 0 0 0 rgba(92, 45, 98, 0);
            }

            .filter-pill:hover {
                transform: translateY(-3px);
                border-color: #ECDDE6;
                box-shadow: 0 14px 24px rgba(92, 45, 98, 0.08);
            }

            .filter-pill .icon {
                transition: transform 0.2s ease-in-out;
            }

            .filter-pill:hover .icon {
                transform: scale(1.08) rotate(-6deg);
            }

            .filter-pill.inactive {
                background-color: #FFFFFF;
                border-color: #F2F2F2;
                color: #374151;
            }

            .filter-pill.active {
                background-color: #FFFFFF;
                border-color: #DD3888;
                color: #374151;
            }

            .filter-pill.active:hover {
                box-shadow: 0 16px 28px rgba(221, 56, 136, 0.14);
            }

            .filter-pill .icon {
                width: 16px;
                height: 16px;
                margin-right: 8px;
            }

            .filter-switch {
                width: 33px;
                height: 18px;
                border-radius: 7500px;
                background-color: #E4E4E7;
                position: relative;
                transition: background-color 0.2s ease-in-out;
                margin-left: 8px;
            }

            .filter-switch-thumb {
                position: absolute;
                top: 1.5px;
                left: 1.5px;
                width: 15px;
                height: 15px;
                background-color: #FFFFFF;
                border-radius: 9999px;
                transition: transform 0.2s ease-in-out;
            }

            .filter-pill.active .filter-switch {
                background-color: #DD3888;
            }

            .filter-pill.active .filter-switch-thumb {
                transform: translateX(15px);
            }

            /* Figma home.jpg at 1920: heading, pills and cards share the
               1134 px column from x 393 (cards 1136 from x 392: 5 × 210 with
               21.5 px gaps); loupe 28 px, title ink from x 448 with +0.55 px
               tracking; pills on y 814, cards on y 935. */
            @media (min-width: 1280px) {
                .pl-pad { padding-left: 57px !important; padding-right: 57px !important; }
                .pl-pad--grid { padding-left: 56px !important; padding-right: 56px !important; }
                .pl-news__grid { column-gap: 40px !important; }
                /* The advert runs as wide as the hero, 1331 px (x 294–1626). */
                .pl-pad--grid .pl-advert { margin-left: -97.5px !important; margin-right: -97.5px !important; }
                /* home.jpg: the next card row starts 50 px above the panel's
                   bottom edge (panel 2032–2683, cards from 2633). */
                html[lang="cs"] .pl-pad--grid .pl-advert { margin-top: -80px !important; margin-bottom: -71px !important; }
                .pl-news__grid .news-image,
                .pl-news__grid .news-button { width: 100%; max-width: none; }
            }

            @media (min-width: 768px) {
                .pl-heading { gap: 21px !important; padding-top: 37px; padding-bottom: 47px; }
                /* Ring centre 2 px higher than the title box centre, ~1.8 px stroke (home.jpg, home-1.jpg). */
                .pl-heading__icon { width: 30px !important; height: 30px !important; margin-left: 2px; position: relative; top: -2px; }
                .pl-heading__icon svg path { stroke-width: 2.4; }
                .pl-heading__title { letter-spacing: -0.1px; line-height: 40px; }
                .pl-filters { margin-bottom: 41px !important; }
                /* Poslední novinky (home.jpg): 19 px from the icon to the
                   title, images 86 px under the icon's top, two 547 px
                   columns 40 px apart. */
                .pl-news__head { gap: 18px !important; margin-bottom: 46px !important; }
                /* home.jpg: loupe-like newspaper ink 31 px from x 393, title ink
                   from x 447 level with the icon's top; 37 px from the photo
                   to the post title, 14/24 px #505050 excerpt. */
                .pl-news__head .news-icon { width: 35px; height: 35px; margin-left: -2px; }
                .pl-news__head .news-title { position: relative; top: 2px; }
                .pl-news__grid .news-card-title { margin-top: 21px; }
                .pl-news__grid .news-card-desc { font-size: 14px; line-height: 24px; margin-top: -1px; }
            }

            /* Desktop rows (Figma home.jpg): see the markup comment. */
            /* Pill widths are the frame's (fixed per pill, 9 px apart). */
            /* Pill starts measured on home.jpg: 393/489/582/675/768/860/953
               and 393/553/705/812/964/1046, i.e. 10 px apart. */
            .desk-filter-row {
                flex-wrap: wrap;
                gap: 10px;
            }

            .desk-filter-row + .desk-filter-row {
                margin-top: 10px;
            }

            .desk-filter-row .filter-pill { padding-left: 0 !important; padding-right: 0 !important; justify-content: center; }
            .desk-filter-row .desk-filter-clear { width: 102px; }

            .desk-filter-row .filter-pill {
                height: 35px;
                padding: 0 16px;
                gap: 0;
                border-width: 2px;
                font-family: 'Poppins', sans-serif;
                font-size: 11px;
                font-weight: 400;
                line-height: 1;
                color: #505050;
                white-space: nowrap;
            }

            .desk-filter-row .filter-pill.inactive,
            .desk-filter-row .filter-pill.active {
                color: #505050;
            }

            .desk-filter-row .filter-pill.is-neutral {
                background-color: #F2F2F2;
                border-color: #F2F2F2;
            }

            .desk-filter-row .filter-pill .icon {
                width: 16px;
                height: 16px;
                margin: 0 6px 0 -4px;
            }

            /* Switch pills are not centred: the switch starts 10 px inside
               the outer edge and the label follows 7 px after it. */
            .desk-filter-row .filter-pill.has-switch {
                padding-left: 8px !important;
                padding-right: 0 !important;
                justify-content: flex-start;
            }

            .desk-filter-row .filter-switch {
                display: inline-block;
                flex: 0 0 33px;
                margin: 0 7px 0 0;
                background-color: #E6E6E6;
            }

            .desk-filter-row .filter-pill.is-on .filter-switch {
                background-color: #DD3888;
            }

            .desk-filter-row .filter-pill.is-on .filter-switch-thumb {
                transform: translateX(15px);
            }

            .pill-icon-mask {
                display: inline-block;
                flex: 0 0 auto;
                -webkit-mask: var(--pill-icon) center / contain no-repeat;
                mask: var(--pill-icon) center / contain no-repeat;
            }

            .desk-filter-clear {
                margin-left: auto;
            }

            .desk-filter-clear__x {
                color: #DD3888;
                margin-right: 8px;
            }

            /* home-1.jpg: once a filter is on, the label is pink like the cross. */
            .desk-filter-row .filter-pill.desk-filter-clear:not(.is-disabled) {
                color: #DD3888;
            }

            .desk-filter-clear.is-disabled {
                color: #D4D4D4 !important;
                cursor: default;
                pointer-events: none;
            }

            .desk-filter-clear.is-disabled .desk-filter-clear__x {
                color: #D4D4D4;
            }

            .mobile-top-results {
                padding: {{ $isEnglishHomepage ? '58px 9px 0 9px' : '421.5px 9px 23.5px 9px' }};
            }

            /* Frame: 31px pink loupe, 13px to the 28px title, the loupe sat
               2px above the title's centre line; 28px down to the pills. */
            .mobile-top-results-heading {
                display: flex;
                align-items: center;
                gap: 13px;
                flex-wrap: nowrap;
                margin-bottom: 26.5px !important;
            }

            .mobile-top-results-icon {
                position: relative;
                top: -2px;
            }

            .mobile-top-results-title {
                margin: 0;
                line-height: 1;
                font-family: 'Poppins', sans-serif;
                font-size: 28px;
                font-weight: 700;
                color: #5C2D62;
                white-space: nowrap;
            }

            .mobile-top-results-search {
                display: flex;
                justify-content: center;
                margin: 0 0 24px;
            }

            /* Mobile opener overlay button to reliably open fullscreen country search */
            #mobile-country-open {
                display: none;
            }

            @media (max-width: 425px) {
                .mobile-top-results-search {
                    width: 100%;
                }

                #mobile-country-open {
                    display: block;
                    position: absolute;
                    left: 50%;
                    transform: translateX(-50%);
                    width: calc(100vw - 32px);
                    height: auto;
                    min-height: 60px;
                    border: 0;
                    background: transparent;
                    z-index: 190;
                    top: unset;
                    bottom: unset;
                    margin: 0 auto 12px;
                }

                .mobile-top-results-search .search-hero-card {
                    width: min(360px, calc(100vw - 32px)) !important;
                    height: auto !important;
                    left: auto !important;
                    min-height: 0 !important;
                    max-height: none !important;
                    min-width: 0 !important;
                    transform: none !important;
                }

                .mobile-top-results-search .search-select-wrap.is-open {
                    height: auto !important;
                }

                .mobile-top-results-search .search-dropdown-panel,
                .mobile-top-results-search .search-dropdown-panel--region {
                    position: static !important;
                    left: auto !important;
                    top: auto !important;
                    width: 100% !important;
                    max-height: 240px !important;
                    margin-top: -2px !important;
                    padding: 12px 16px 14px !important;
                    border-top: 0 !important;
                    border-radius: 0 0 16px 16px !important;
                    box-shadow: none !important;
                }

                .mobile-top-results-search .search-dropdown-item,
                .mobile-top-results-search .search-dropdown-item--region,
                .mobile-top-results-search .search-dropdown-item--region::after {
                    width: 100% !important;
                }

                .mobile-top-results-search .search-dropdown-item--region::after {
                    left: 0 !important;
                    transform: none !important;
                }
            }

            /* Phone frame (phone 360 px-11): 35px pills, 11px labels, 10px
               apart (11px between the switch rows), a 2px #F0F0F0 rule 19px
               above and below. Icons are the frame's pale #E4E4E6 until the
               pill is active, then pink. */
            .mobile-filter-group {
                display: flex;
                flex-wrap: wrap;
                gap: 10px 9px;
            }

            .mobile-filter-group--switches {
                gap: 11px 10px;
            }

            /* The age pills share one width in the frame (83px), the label
               centred, whatever its length ("50 let +" included). */
            .mobile-filter-pill.age-pill {
                min-width: 83px;
                padding: 0 8px;
            }

            .mobile-filter-pill {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                height: 35px;
                min-height: 35px;
                padding: 0 16px;
                border-radius: 999px;
                background: #FFFFFF;
                border: 2px solid #F2F2F2;
                box-sizing: border-box;
                font-family: 'Poppins', sans-serif;
                font-weight: 500;
                font-size: 11px;
                line-height: 1;
                color: #505050;
                white-space: nowrap;
                transition: all 0.2s ease-in-out;
            }

            .mobile-filter-pill.is-active {
                border-color: #DD3888;
            }

            .mobile-filter-pill.is-highlighted {
                border-color: #2490FF;
            }

            .mobile-filter-pill.icon-pill {
                gap: 6px;
                padding: 0 13px 0 12px;
            }

            .mobile-filter-pill.icon-pill.arrow-pill {
                gap: 4px;
            }

            /* Switch first, then the label; an active switch pill turns grey
               rather than getting the pink outline. */
            .mobile-filter-pill.switch-pill {
                gap: 6px;
                padding: 0 13px 0 9px;
                background: #FFFFFF;
            }

            .mobile-filter-pill.switch-pill.is-active {
                background: #F2F2F2;
                border-color: #F2F2F2;
            }

            .mobile-filter-icon {
                width: 16px;
                height: 16px;
                color: #E4E4E6;
                flex: 0 0 16px;
            }

            .mobile-filter-pill.is-active .mobile-filter-icon {
                color: #DD3888;
            }

            .mobile-filter-divider {
                width: 100%;
                height: 2px;
                background: #F0F0F0;
                margin: 19px 0;
            }

            .mobile-filter-icon--lock {
                width: 13px;
                height: 15px;
                flex: 0 0 13px;
                margin: 0 1px 0 1px;
            }

            /* English phone frames (phone 360 px-18…20): #F2F2F2 rules. */
            /* The English frames draw both 2 px rules #F2F2F2 and the switch
               rows 10 px apart (phone 360 px-18…20). */
            html[lang="en"] .mobile-filter-divider {
                background: #F2F2F2;
            }

            html[lang="en"] .mobile-filter-group--switches {
                row-gap: 10px;
            }

            /* "All Girls" is 87 px wide in phone 360 px-19 (icon 18 px in). */
            html[lang="en"] .mobile-filter-group:not(.mobile-filter-group--switches) > .icon-pill:first-child {
                padding: 0 9.5px 0 11px;
            }

            /* Arrow pills: icon 13 px in, text 5 px after it (-18, "New"). */
            html[lang="en"] .mobile-filter-pill.icon-pill.arrow-pill {
                padding-left: 10px;
                gap: 5px;
            }

            html[lang="en"] .mobile-filter-divider:not(.mobile-filter-divider--en) {
                margin-bottom: 20px;
            }

            .mobile-top-results-search:has(+ .mobile-filter-divider--en) {
                margin-bottom: 0;
            }

            /* phone 360 px-16: without a picked place the cards start 50 px
               under the last filter row. */
            html[lang="en"] .mobile-top-results:not(:has(.mobile-location-heading)) {
                padding-bottom: 33px;
            }

            .mobile-location-rule {
                height: 1px;
                background: #EDEDED;
                margin: 20px 0 0;
            }

            .mobile-location-heading {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                padding: 50px 0 30px;
            }

            .mobile-location-heading__flag {
                position: relative;
                left: -2px;
                width: 32px;
                height: 32px;
                border-radius: 50%;
                object-fit: cover;
            }

            /* -20: pin 44 px under the rule (the flag 51), title cap 14 px under it. */
            .mobile-location-heading__pin {
                position: relative;
                left: -2px;
                width: 30px;
                height: 38px;
                margin-top: -7px;
            }

            .mobile-location-heading__pin + .mobile-location-heading__title {
                margin-top: 10px;
            }

            /* -20: cards 62 px under the pink country line's cap. */
            .mobile-location-heading:has(.mobile-location-heading__subtitle) {
                padding-bottom: 31.5px;
            }

            .mobile-location-heading__title {
                margin-top: 15px;
                font-family: 'Poppins', sans-serif;
                font-size: 28px;
                font-weight: 700;
                line-height: 28px;
                color: #5C2D62;
                max-width: 260px;
            }

            .mobile-location-heading__subtitle {
                margin-top: 2px;
                font-family: 'Poppins', sans-serif;
                font-size: 14px;
                font-weight: 400;
                line-height: 20px;
                color: #DD3888;
            }

            .mobile-filter-clear {
                width: 35px;
                min-width: 35px;
                padding: 0;
                color: #DD3888;
                border-color: #DD3888;
            }

            .mobile-filter-switch {
                width: 33px;
                height: 18px;
                flex: 0 0 33px;
                border-radius: 999px;
                background: #E4E4E6;
                position: relative;
                transition: background-color 0.2s ease-in-out;
            }

            .mobile-filter-switch::after {
                content: '';
                position: absolute;
                top: 1.5px;
                left: 1.5px;
                width: 15px;
                height: 15px;
                border-radius: 999px;
                background: #FFFFFF;
                transition: transform 0.2s ease-in-out;
            }

            .selected-location-banner {
                animation: selectedLocationBannerIn 280ms cubic-bezier(0.22, 1, 0.36, 1);
                transform-origin: top center;
                box-shadow: 0 18px 42px rgba(92, 45, 98, 0.08);
            }

            .selected-location-banner__title {
                font-family: 'Poppins', sans-serif;
                font-size: 30px;
                font-weight: 700;
                line-height: 1.05;
                color: #5C2D62;
            }

            .selected-location-banner__subtitle {
                font-family: 'Poppins', sans-serif;
                font-size: 14px;
                font-weight: 400;
                line-height: 1.3;
                color: #DD3888;
            }

            @keyframes selectedLocationBannerIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px) scale(0.98);
                }

                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            .mobile-filter-pill.is-active .mobile-filter-switch {
                background: #DD3888;
            }

            .mobile-filter-pill.is-active .mobile-filter-switch::after {
                transform: translateX(15px);
            }

            /* Phone grid (2 columns up to 767px): resources/css/components.css,
               shared with the country listing. */

            /* Desktop grid pinned to the design: five 210 px cards 21.5 px
               apart (231.5 px pitch) = 1136 px from x 392 in the 1920 frame.
               Measured on the white card edge in home.jpg (x 392–601, next
               card from 623.5). An earlier pass read the soft shadow as card
               and pinned 214 + 17.5 – the same pitch, a wider card.
               auto-fill: the English homepage's 208 px country sidebar leaves
               room for four. Other pages (favourites, archive …) keep their
               214 px card; only this grid narrows it. */
            @media (min-width: 1280px) {
                .profile-list-cards-grid {
                    grid-template-columns: repeat(auto-fill, 210px);
                    /* Rows 531 px apart (Detail tops 2964/3495/4026). */
                    gap: 21px 21.5px;
                    justify-content: center;
                }

                .profile-list-cards-grid .home-profile-card,
                .profile-list-cards-grid .home-profile-card-media,
                .profile-list-cards-grid .home-profile-card-image { width: 210px !important; }
            }

            /* Card body as in home.jpg (510 px tall cards): 20 px gutters,
               VIP pill 20 px under the photo, a 45 px Detail button 20 px
               below it, 6 px to the 30 px stat chips, the place, and a 30 px
               rating bar (two-tone with a padlock while locked). The frame's
               first row alone puts the rating bar above the chips; rows 2–5,
               home-1 and the phone frames keep this order, so it stays. */
            @media (min-width: 768px) {
                .profile-list-cards-grid .home-profile-card { height: 510px !important; }
                .profile-list-cards-grid .home-profile-card-content {
                    padding: 20px 20px 0 !important;
                }
                .profile-list-cards-grid .home-profile-card-content > * { margin-top: 0 !important; margin-bottom: 0 !important; }
                .profile-list-cards-grid .home-profile-card-header { padding: 0 !important; height: 26px; }
                .profile-list-cards-grid .home-profile-card-cta {
                    width: 100% !important;
                    /* home.jpg x 500: purple from y 1265.9 to 1310.9 = 45 px. */
                    height: 45px !important;
                    margin-top: 20px !important;
                }
                .profile-list-cards-grid .home-profile-card-rating-wrap {
                    display: flex;
                    flex-direction: column;
                    margin-top: 6px !important;
                }
                .profile-list-cards-grid .home-profile-card-stats { gap: 6px !important; }
                .profile-list-cards-grid .home-profile-card-name { color: #505050 !important; }
            }

            /* Eco strip: 51 px clear of the cards above and below (Figma
               home.jpg y 1496–1530 between card rows ending 1444 / from 1581). */
            @media (min-width: 1024px) {
                .profile-list-cards-grid > .profile-list-eco { margin: 30px 0 29px !important; padding: 0 !important; }
                .profile-list-cards-grid .home-profile-card-location { column-gap: 3px !important; padding-top: 12px !important; padding-bottom: 12px !important; }
                .profile-list-cards-grid .home-profile-card-stat { flex: 1 1 0; width: auto !important; }
                .profile-list-cards-grid .home-profile-card-rating-badge--locked {
                    height: 30px !important;
                }
                .profile-list-cards-grid .home-profile-card-rating-badge--locked > div:first-child {
                    background: #F2F2F2 !important;
                    border-radius: 0 8px 8px 0;
                    position: relative;
                    z-index: 1;
                    flex: 0 0 82px !important;
                }
                .profile-list-cards-grid .home-profile-card-rating-badge--locked > div:last-child {
                    margin-left: -8px;
                    padding-left: 8px;
                }
                .profile-list-cards-grid .home-profile-card-rating-badge:not(.home-profile-card-rating-badge--locked) {
                    height: 30px !important;
                }
            }

            @media (max-width: 320px) {
                .mobile-top-results {
                    padding-left: 4px;
                    padding-right: 12px;
                }

                .selected-location-banner__title {
                    font-size: 24px;
                }

                .mobile-top-results-heading {
                    gap: 8px;
                }

                .mobile-top-results-icon {
                    width: 24px !important;
                    height: 24px !important;
                }

                .mobile-top-results-title {
                    font-size: 24px;
                }
            }

            @media (min-width: 768px) {
                .mobile-top-results {
                    display: none;
                }
            }

            /* vybraná země.jpg: 903 × 80 box, 2 px #F2F2F2, 8 px corners, no
               shadow; 32 px flag 22 px in, 30 px title 15 px after it with its
               cap at y 549 (27 px under the top), a 34 px ✕ 20 px from the
               right edge. */
            @media (min-width: 768px) {
                .selected-location-banner { margin-top: -1px; box-shadow: none; border-radius: 8px; padding-left: 22px !important; padding-right: 20px !important; gap: 15px !important; }
                .selected-location-banner > img.h-8 { width: 32px; height: 32px; }
                .selected-location-banner__title { position: relative; top: -3px; }
                .selected-location-banner > button { width: 34px !important; height: 34px !important; }
                /* vybrané město v zemi.jpg: a town banner is 100 px tall, 42 px pin. */
                .selected-location-banner:has(.selected-location-banner__subtitle) { height: 100px !important; }
                .selected-location-banner:has(.selected-location-banner__subtitle) > img { position: relative; top: 1px; }
                .selected-location-banner:has(.selected-location-banner__subtitle) .selected-location-banner__title { top: -2px; }
                .selected-location-banner > button { position: relative; left: 1px; }
            }

            @media (max-width: 767px) {
                .selected-location-banner {
                    height: auto;
                    min-height: 100px;
                    padding-top: 18px;
                    padding-bottom: 18px;
                }

                .selected-location-banner__title {
                    font-size: 28px;
                }
            }
        </style>

        @if ($isEnglishHomepage && $hasSelectedLocation)
            {{-- vybraná země.jpg: 80 px tall, 2 px #F2F2F2 outline, 31 px to the pills. --}}
            <div class="mb-5 md:mb-[32px] hidden md:block">
                <div class="selected-location-banner mx-auto flex w-full max-w-[904px] items-center gap-4 rounded-xl border-2 border-[#F2F2F2] bg-white px-5 md:h-[80px] md:px-6">
                    @if ($showSelectedCountryFlag)
                        <img src="{{ $selectedCountryFlagUrl }}" alt="{{ $selectedLocationTitle }}" class="h-8 w-8 shrink-0 rounded-full object-cover">
                    @else
                        <img src="{{ asset('images/icons/MapPinned.svg') }}" alt="Selected location" class="h-12 w-12 shrink-0">
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="selected-location-banner__title">{{ $selectedLocationTitle }}</div>
                        @if (filled($selectedLocationSubtitle))
                            <div class="selected-location-banner__subtitle">{{ $selectedLocationSubtitle }}</div>
                        @endif
                    </div>
                    <button wire:click="clearLocation"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#DD3888] text-white transition-transform duration-200 hover:scale-105"
                        title="{{ __('front.profiles.list.clear_all_filters') }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        <div class="mobile-top-results md:hidden">
            <div class="mobile-top-results-heading mb-5">
                <x-icons name="search" class="mobile-top-results-icon w-[31px] h-[31px] text-[#DD3888]" />
                <{{ $headingTag }} class="mobile-top-results-title">{{ __('front.profiles.list.topresults') }}</{{ $headingTag }}>
            </div>

            @if ($isEnglishHomepage)
                <div class="mobile-top-results-search">
                    <!-- Mobile-only opener for fullscreen country search (fallback) -->
                    <button id="mobile-country-open" type="button" class="mobile-country-open-button md:hidden" aria-label="{{ __('front.profiles.search.open_mobile_search') }}" onclick="(function(){var p=document.querySelector('.search-country-mobile-picker'); if(p){p.style.display='block'; document.documentElement.style.overflow='hidden'; document.body.style.overflow='hidden';}})()"></button>
                    <script>
                        (function(){
                            try {
                                var b = document.getElementById('mobile-country-open');
                                if (!b) return;
                                b.addEventListener('click', function (e) {
                                    try {
                                        var p = document.querySelector('.search-country-mobile-picker');
                                        if (p) {
                                            p.style.display = 'block';
                                            document.documentElement.style.overflow = 'hidden';
                                            document.body.style.overflow = 'hidden';
                                        }
                                    } catch (err2) {}
                                }, true);
                            } catch (err) {}
                        })();
                    </script>
                    <livewire:search-profiles :key="'en-mobile-search'" />
                </div>
                {{-- phone 360 px-18…20: a 2 px rule 19 px under the fields. --}}
                <div class="mobile-filter-divider mobile-filter-divider--en"></div>
            @endif

            <div class="mobile-filter-group">
                <button wire:click.debounce.300ms="toggleAgeGroup('')"
                    class="mobile-filter-pill icon-pill {{ $ageGroup === '' ? 'is-active' : '' }}">
                    <svg class="mobile-filter-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <circle cx="8" cy="5" r="3" stroke="currentColor" stroke-width="1.4"/>
                        <path d="M2.7 14.3C3.3 11.6 5.3 10.2 8 10.2C10.7 10.2 12.7 11.6 13.3 14.3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                    </svg>
                    {{ __('front.profiles.list.filter_all') }}
                </button>

                @foreach ([
                    '18-25' => __('front.profiles.list.age_18_25'),
                    '26-30' => __('front.profiles.list.age_26_30'),
                    '31-35' => __('front.profiles.list.age_31_35'),
                    '36-40' => __('front.profiles.list.age_36_40'),
                    '40-50' => __('front.profiles.list.age_40_50'),
                    '50+' => __('front.profiles.list.age_50_plus'),
                ] as $value => $label)
                    <button wire:click.debounce.300ms="toggleAgeGroup('{{ $value }}')"
                        class="mobile-filter-pill age-pill {{ $ageGroup === $value ? ($value === '40-50' ? 'is-highlighted' : 'is-active') : '' }}">
                        {{ $label }}
                    </button>
                @endforeach

                @if ($this->activeFiltersCount() > 0)
                    <button wire:click.debounce.300ms="resetFilters" wire:loading.attr="disabled" wire:target="resetFilters"
                        class="mobile-filter-pill mobile-filter-clear" title="{{ __('front.profiles.list.clear_all_filters') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                @endif
            </div>

            <div class="mobile-filter-divider"></div>

            <div class="mobile-filter-group mobile-filter-group--switches">
                <button wire:click.debounce.300ms="toggleRecommendation"
                    class="mobile-filter-pill icon-pill arrow-pill {{ ($sortRecommendation !== '' || ($sortNew === '' && ! $hasRating)) ? 'is-active' : '' }}">
                    <svg class="mobile-filter-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M6 3.5V12.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                        <path d="M2 8.75L6 12.75L10 8.75" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ __('front.profiles.list.filter_recommendation') }}
                </button>

                <button wire:click.debounce.300ms="toggleVerifiedPhoto"
                    aria-pressed="{{ $hasVerifiedPhoto ? 'true' : 'false' }}"
                    class="mobile-filter-pill switch-pill {{ $hasVerifiedPhoto ? 'is-active' : '' }}">
                    <span class="mobile-filter-switch"></span>
                    {{ __('front.profiles.list.filter_verified_photo') }}
                </button>

                <button wire:click.debounce.300ms="toggleVideo"
                    aria-pressed="{{ $hasVideo ? 'true' : 'false' }}"
                    class="mobile-filter-pill switch-pill {{ $hasVideo ? 'is-active' : '' }}">
                    <span class="mobile-filter-switch"></span>
                    {{ __('front.profiles.list.video') }}
                </button>

                <button wire:click.debounce.300ms="togglePornActress"
                    aria-pressed="{{ $isPornActress ? 'true' : 'false' }}"
                    class="mobile-filter-pill switch-pill {{ $isPornActress ? 'is-active' : '' }}">
                    <span class="mobile-filter-switch"></span>
                    {{ __('front.profiles.list.filter_porn_actress') }}
                </button>

                <button wire:click.debounce.300ms="toggleNew"
                    class="mobile-filter-pill icon-pill arrow-pill {{ $sortNew !== '' ? 'is-active' : '' }}">
                    <svg class="mobile-filter-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M6 3.5V12.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                        <path d="M2 8.75L6 12.75L10 8.75" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ __('front.profiles.list.new') }}
                </button>

                <button wire:click.debounce.300ms="toggleRating"
                    class="mobile-filter-pill icon-pill {{ $hasRating ? 'is-active' : '' }}">
                    @if ($hasRating)
                    <svg class="mobile-filter-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M1.75 1.5V14.25H14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M4.75 9V11.75M8.25 6V11.75M11.75 3V11.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    @else
                    {{-- phone 360 px-18: a pink padlock until the filter is on, as on desktop. --}}
                    <img src="{{ asset('images/icons/lock.svg') }}" alt="" class="mobile-filter-icon mobile-filter-icon--lock" aria-hidden="true">
                    @endif
                    {{ __('front.profiles.list.filter_rating') }}
                </button>

                {{-- The segment dropdown is not in the design's filter row. It
                     stayed a filter you can still reach: ?segment=<id> keeps
                     working, and the admin's segment links rely on it. --}}

                @if ($this->activeFiltersCount() > 0)
                    <button wire:click.debounce.300ms="resetFilters" wire:loading.attr="disabled" wire:target="resetFilters"
                        class="mobile-filter-pill mobile-filter-clear" title="{{ __('front.profiles.list.clear_all_filters') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                @endif
            </div>

            @if ($isEnglishHomepage && $hasSelectedLocation)
                {{-- phone 360 px-18…20: under the filters a 1 px rule, then the
                     picked country (32 px flag) or town (pink pin, country in
                     pink under it) centred above the cards — the desktop
                     banner is not on the phone. --}}
                <div class="mobile-location-rule"></div>
                <div class="mobile-location-heading">
                    @if ($showSelectedCountryFlag)
                        <img src="{{ $selectedCountryFlagUrl }}" alt="" class="mobile-location-heading__flag">
                    @else
                        <svg class="mobile-location-heading__pin" viewBox="0 0 30 38" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M29 15C29 26 15 37 15 37S1 26 1 15C1 7.27 7.27 1 15 1s14 6.27 14 14Z" stroke="#DD3888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="15" cy="15" r="5" stroke="#DD3888" stroke-width="2"/>
                        </svg>
                    @endif
                    <div class="mobile-location-heading__title">{{ $selectedLocationTitle }}</div>
                    @if (filled($selectedLocationSubtitle))
                        <div class="mobile-location-heading__subtitle">{{ $selectedLocationSubtitle }}</div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Desktop filter rows, Figma home.jpg / home-1.jpg: 35 px pills with
             a 2 px #F2F2F2 outline, 11 px Poppins, 10 px apart in both
             directions. The neutral choice ("Všechny", "Doporučujeme" while no
             other order is picked, a switched-on toggle) is a grey fill; a
             picked age or order is a pink outline. Switches sit before the
             label. "zrušit filtr" closes the first row on the right and is
             greyed out until a filter is on. Wording is the desktop
             frame's own (desk_* keys); the phone row keeps the phone frame's
             (filter_*), as the client asked for every viewport. --}}
        @php
            $activeFilterCount = $this->activeFiltersCount();
            $noOrderPicked = $sortRecommendation === '' && $sortNew === '' && ! $hasRating;
        @endphp
        <div class="hidden md:flex desk-filter-row">
            <button type="button" wire:click.debounce.300ms="toggleAgeGroup('')"
                class="filter-pill {{ $ageGroup === '' ? 'is-neutral' : 'inactive' }}" style="width:88px;margin-right:-2px;">
                {{-- The colour filter turned the white icon salmon (224,116,111);
                     a mask paints it the frame's exact #DD3888. --}}
                @if ($ageGroup === '')
                    <span class="icon pill-icon-mask" style="--pill-icon: url('{{ asset('images/icons/profile.svg') }}'); background-color: #DD3888;" aria-hidden="true"></span>
                @else
                    {{-- home-1.jpg: with an age picked the figure stays, light grey. --}}
                    <span class="icon pill-icon-mask" style="--pill-icon: url('{{ asset('images/icons/profile.svg') }}'); background-color: #E4E4E6;" aria-hidden="true"></span>
                @endif
                {{ __('front.profiles.list.desk_filter_all') }}
            </button>

            @foreach ([
                '18-25' => __('front.profiles.list.desk_age_18_25'),
                '26-30' => __('front.profiles.list.desk_age_26_30'),
                '31-35' => __('front.profiles.list.desk_age_31_35'),
                '36-40' => __('front.profiles.list.desk_age_36_40'),
                '40-50' => __('front.profiles.list.desk_age_40_50'),
                '50+' => __('front.profiles.list.desk_age_50_plus'),
            ] as $value => $label)
                <button type="button" wire:click.debounce.300ms="toggleAgeGroup('{{ $value }}')"
                    class="filter-pill {{ $ageGroup === $value ? 'active' : 'inactive' }}" style="width:83px;{{ $value === '36-40' ? 'margin-right:-1px;' : '' }}">
                    {{ $label }}
                </button>
            @endforeach

            <button type="button" wire:click.debounce.300ms="resetFilters" wire:loading.attr="disabled" wire:target="resetFilters"
                class="filter-pill inactive desk-filter-clear {{ $activeFilterCount > 0 ? '' : 'is-disabled' }}"
                @if ($activeFilterCount === 0) disabled aria-disabled="true" @endif
                title="{{ __('front.profiles.list.clear_all_filters') }}">
                <svg class="desk-filter-clear__x" width="10" height="10" viewBox="0 0 10 10" fill="none" aria-hidden="true">
                    <path d="M1 1l8 8M9 1L1 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                </svg>
                {{ __('front.profiles.list.desk_clear_filter') }}
            </button>
        </div>

        <div class="hidden md:flex desk-filter-row">
            <button type="button" wire:click.debounce.300ms="toggleRecommendation"
                class="filter-pill {{ $sortRecommendation !== '' ? 'active' : ($noOrderPicked ? 'is-neutral' : 'inactive') }}" style="width:150px;">
                <img src="{{ asset('images/icons/ArrowUp.svg') }}" alt="" class="icon" style="{{ ($sortRecommendation !== '' || $noOrderPicked) ? '' : 'filter: grayscale(1) opacity(.35);' }}">
                {{ __('front.profiles.list.desk_filter_recommendation') }}
            </button>

            {{-- The switch is a <div>, so the pressed state is invisible to
                 assistive tech without aria-pressed. --}}
            <button type="button" wire:click.debounce.300ms="toggleVerifiedPhoto"
                aria-pressed="{{ $hasVerifiedPhoto ? 'true' : 'false' }}"
                class="filter-pill has-switch {{ $hasVerifiedPhoto ? 'is-on is-neutral' : 'inactive' }}" style="width:143px;">
                <span class="filter-switch"><span class="filter-switch-thumb"></span></span>
                {{ __('front.profiles.list.desk_filter_verified_photo') }}
            </button>

            <button type="button" wire:click.debounce.300ms="toggleVideo"
                aria-pressed="{{ $hasVideo ? 'true' : 'false' }}"
                class="filter-pill has-switch {{ $hasVideo ? 'is-on is-neutral' : 'inactive' }}" style="width:97px;margin-left:-1px;">
                <span class="filter-switch"><span class="filter-switch-thumb"></span></span>
                {{ __('front.profiles.list.desk_filter_video') }}
            </button>

            <button type="button" wire:click.debounce.300ms="togglePornActress"
                aria-pressed="{{ $isPornActress ? 'true' : 'false' }}"
                class="filter-pill has-switch {{ $isPornActress ? 'is-on is-neutral' : 'inactive' }}" style="width:142px;{{ $isEnglishHomepage ? 'order:2;' : '' }}">
                <span class="filter-switch"><span class="filter-switch-thumb"></span></span>
                {{ __('front.profiles.list.desk_filter_porn_actress') }}
            </button>

            {{-- The English frames (home-24, vybraná země) put "New" before
                 "Porno actress"; the Czech ones after it. --}}
            <button type="button" wire:click.debounce.300ms="toggleNew"
                class="filter-pill {{ $sortNew !== '' ? 'active' : 'inactive' }}" style="width:72px;{{ $isEnglishHomepage ? 'order:1;' : '' }}">
                <img src="{{ asset('images/icons/ArrowUp.svg') }}" alt="" class="icon" style="filter: grayscale(1) opacity(.35);">
                {{ __('front.profiles.list.desk_filter_new') }}
            </button>

            <button type="button" wire:click.debounce.300ms="toggleRating"
                class="filter-pill {{ $hasRating ? 'active' : 'inactive' }}" style="width:87px;{{ $isEnglishHomepage ? 'order:3;' : '' }}">
                @if ($hasRating)
                    <span class="icon pill-icon-mask" style="--pill-icon: url('{{ asset('images/icons/BarChart4.svg') }}'); background-color: #DD3888;" aria-hidden="true"></span>
                @else
                    <img src="{{ asset('images/icons/lock.svg') }}" alt="" class="icon">
                @endif
                {{ __('front.profiles.list.desk_filter_rating') }}
            </button>
        </div>
    </div>

    @if ($this->profiles && $this->profiles->count() > 0)
        <!-- Profiles Grid -->
        <div class="space-y-4 md:space-y-6 relative px-0 md:px-8 lg:px-12 pl-pad pl-pad--grid">
            <!-- Loading Overlay -->
            <div wire:loading
                wire:target="toggleAgeGroup,toggleRecommendation,toggleVerifiedPhoto,toggleVideo,togglePornActress,toggleNew,toggleRating,resetFilters,updateFilters"
                class="absolute inset-0 bg-white/80 z-10 flex items-center justify-center pt-12">
                <div class="flex flex-col items-center">
                    <svg class="animate-spin h-12 w-12 text-primary-600" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>
            </div>

            <div class="{{ $profileGridClasses }}">
                @foreach ($this->profiles as $profile)
                    @if ($loop->iteration === $ecoBadgeInsertAt)
                        <div class="col-span-full my-4 flex justify-center px-2 profile-list-eco">
                            <x-ecobadge />
                        </div>
                    @endif

                    @if ($loop->iteration === $advertInsertAt)
                        <div class="col-span-full my-6 lg:-my-20 -mx-2 md:-mx-8 lg:-mx-12 relative z-0 pl-advert">
                            <x-advert-hero />
                        </div>
                    @endif

                    {{-- Photos are never blurred here.

                         This used to pick roughly every second card by
                         `crc32($id) % 2` and render it in the blurred
                         `vip-detail` variant. The design does the opposite: the
                         photo is always sharp and it is the *rating* that is
                         locked, which x-profile-card now handles through the
                         `view-ratings` gate. --}}
                    <x-profile-card :profile="$profile" />
                @endforeach
            </div>

            <!-- Pagination -->
            {{ $this->profiles->links('livewire.pagination-links') }}
        </div>
    @else
        <!-- Empty State -->
        <x-filter-empty-state wire:loading.remove class="mt-6 md:mt-[50.5px]" />
    @endif


<!-- Latest News Section -->
{{-- home-21.jpg: a search with no results ends with the empty state; the
     news block only follows a list that has cards. --}}
@if (! ($listTitle && ! ($this->profiles && $this->profiles->count() > 0)))
<section class="news-section py-12 md:py-16 lg:py-20">
    <div class="px-4 md:px-8 lg:px-12 mx-auto pl-pad pl-news">
        <div class="flex items-center gap-4 mb-6 pl-news__head">
            <img src="{{ asset('images/icons/Newspaper.svg') }}" alt="Novinky" width="36" height="36" class="news-icon" />
            <h2 class="news-title m-0">{{ __('blogs.latest_news') }}</h2>
        </div>

        {{-- Real blog posts, managed in the admin under "Blog příspěvky".

             These two cards were hardcoded: fixed images, a fixed "25. 4. 2025"
             date, a fixed "5 min" reading time, filler body text and a <button>
             that led nowhere — while the Page model, the blog admin resource and
             aproximateReadingTime() all already existed. --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pl-news__grid">
            @forelse($this->latestPosts as $post)
                <article class="news-card">
                    <a href="{{ route('pages.show', $post->slug) }}" class="block relative">
                        @if($post->hasMedia('header-image'))
                            <img src="{{ $post->getFirstMediaUrl('header-image') }}" alt="{{ $post->title }}" class="news-image" />
                        @else
                            <div class="news-image" style="display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#f9edf4,#efe4f2);">
                                <img src="{{ asset('images/icons/Newspaper.svg') }}" alt="" aria-hidden="true" width="48" height="48" style="opacity:.5;" />
                            </div>
                        @endif

                        <div class="news-badges">
                            <div class="news-badge badge-date">
                                <img src="{{ asset('images/icons/calendar.svg') }}" alt="calendar" width="16" height="16" />
                                <span class="badge-text">{{ $post->created_at->translatedFormat('j. n. Y') }}</span>
                            </div>
                            @if($post->aproximateReadingTime() > 0)
                                <div class="news-badge badge-time">
                                    <img src="{{ asset('images/icons/clock.svg') }}" alt="clock" width="16" height="16" />
                                    <span class="badge-text">{{ $post->aproximateReadingTime() }} {{ __('blogs.min_read') }}</span>
                                </div>
                            @endif
                        </div>
                    </a>

                    <h3 class="news-card-title">
                        <a href="{{ route('pages.show', $post->slug) }}">{{ $post->title }}</a>
                    </h3>

                    @if($post->description)
                        <p class="news-card-desc">{{ $post->description }}</p>
                    @endif

                    <a href="{{ route('pages.show', $post->slug) }}" class="news-button">{{ __('blogs.read_article') }}</a>
                </article>
            @empty
                <p class="news-card-desc md:col-span-2"><x-empty-value variant="text" /></p>
            @endforelse
        </div>
    </div>
</section>
@endif

</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if Swiper is available
            if (typeof Swiper === 'undefined') {
                console.error('Swiper is not loaded. Make sure to build assets with npm run build');
                return;
            }

            initializeSwipers();
        });

        // Initialize Swiper instances
        function initializeSwipers() {
            // Find all profile swipers and initialize them
            document.querySelectorAll('[class*="profile-swiper-"]').forEach(function(swiperEl) {
                const profileId = swiperEl.className.match(/profile-swiper-(\d+)/)[1];

                // Destroy existing swiper instance if any
                if (swiperEl.swiper) {
                    swiperEl.swiper.destroy(true, true);
                }

                // Initialize new Swiper instance
                new Swiper(swiperEl, {
                    loop: true,
                    pagination: {
                        el: `.swiper-pagination-${profileId}`,
                        clickable: true,
                        dynamicBullets: true,
                    },
                    preloadImages: true,
                });
            });
        }

        // Re-initialize Swiper when Livewire updates content
        document.addEventListener('livewire:navigated', function() {
            setTimeout(initializeSwipers, 100);
        });

        // Register Livewire hooks after Livewire is loaded
        document.addEventListener('livewire:load', function() {
            Livewire.hook('morph.updated', () => {
                setTimeout(initializeSwipers, 100);
            });

            // Also listen for specific Livewire events
            Livewire.on('profiles-updated', () => {
                setTimeout(initializeSwipers, 100);
            });
        });
    </script>
@endpush
