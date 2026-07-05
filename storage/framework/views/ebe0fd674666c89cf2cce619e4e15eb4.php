<div>
<style>
    .search-hero-card {
        width: 1134px;
        max-width: 100%;
        height: 218px;
        background: #FFFFFF;
        border-radius: 24px;
        border: 1px solid #EDE7EE;
        box-shadow: 0 10px 25px 0 rgba(220, 214, 221, 0.95);
        padding: 30px 42px;
        box-sizing: border-box;
        margin: 0 auto;
        position: relative;
        z-index: 25;
        opacity: 1;
        overflow: visible;
        transition: transform 320ms cubic-bezier(.2,.9,.3,1), box-shadow 320ms cubic-bezier(.2,.9,.3,1), border-color 220ms ease;
    }

    .search-hero-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        clip-path: inset(0 round 24px);
        background: linear-gradient(110deg, rgba(255,255,255,0) 20%, rgba(255,255,255,0.6) 50%, rgba(255,255,255,0) 80%);
        transform: translateX(-140%);
        transition: transform 680ms cubic-bezier(.2,.9,.3,1);
        pointer-events: none;
    }

    .search-hero-top,
    .search-hero-form-row {
        position: relative;
        z-index: 1;
    }

    .search-hero-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 24px 48px rgba(92, 45, 98, 0.16);
    }

    .search-hero-card:hover::before {
        transform: translateX(140%);
    }

    .search-hero-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .search-hero-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 18px;
        color: #505050;
        margin: 0;
    }

    .search-hero-heart {
        width: 20px;
        height: 20px;
        stroke: #DD3888;
        transition: transform 240ms ease, filter 240ms ease;
        animation: heartBeat 3.8s ease-in-out infinite;
    }

    .search-hero-card:hover .search-hero-heart {
        transform: scale(1.12);
        filter: drop-shadow(0 6px 12px rgba(221, 56, 136, 0.25));
    }

    .search-hero-badges {
        display: flex;
        gap: 10px;
    }

    .search-badge {
            width: 167px;
            height: 26px;
            border-radius: 999px;
            background: #F2F2F2;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-sizing: border-box;
            padding: 0 10px;
            transition: transform 220ms ease, box-shadow 220ms ease, background-color 220ms ease;
        }

        @media (max-width: 425px) {
            .search-hero-badges {
                display: none !important;
            }

            .search-badge {
                width: 310px !important;
                height: 35px !important;
                background: rgba(242, 242, 242, 0.8) !important;
                backdrop-filter: blur(4px);
            }
        }
    .search-badge:hover {
        transform: translateY(-2px);
        background: #FFF4F9;
        box-shadow: 0 10px 20px rgba(92, 45, 98, 0.08);
    }

    .search-badge-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #00B80F;
        flex: 0 0 10px;
    }

    .search-badge-strong {
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 11px;
        color: #505050;
        line-height: 1;
        white-space: nowrap;
    }

    .search-badge-soft {
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 11px;
        color: #A6A6A6;
        line-height: 1;
        white-space: nowrap;
    }

    .search-hero-form-row {
        display: flex;
        align-items: flex-end;
        gap: 12px;
    }

    .search-hero-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .search-hero-field.region { width: 601px; }
    .search-hero-field.age { width: 205px; }
    .search-hero-field.country,
    .search-hero-field.town { width: 394px; }

    .search-hero-label {
        font-family: 'Poppins', sans-serif;
        font-weight: 400;
        font-size: 14px;
        color: #505050;
        margin: 0;
    }

    .search-select-wrap {
        position: relative;
        width: 100%;
        height: 60px;
        background: #FFFFFF;
        border: 2px solid #E6E6E6;
        border-radius: 8px;
        box-sizing: border-box;
        overflow: visible;
        transition: transform 240ms cubic-bezier(.2,.9,.3,1), box-shadow 240ms cubic-bezier(.2,.9,.3,1), border-color 240ms cubic-bezier(.2,.9,.3,1);
    }

    .search-select-wrap.is-open {
        border-color: #DD3888;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(92, 45, 98, 0.10);
    }

    .search-select-wrap:hover {
        transform: translateY(-2px);
        border-color: #E9C9DA;
        box-shadow: 0 10px 22px rgba(92, 45, 98, 0.08);
    }

    .search-select-trigger {
        width: 100%;
        height: 100%;
        border: 0;
        outline: 0;
        background: transparent;
        padding: 0 64px 0 16px;
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 14px;
        color: #505050;
        cursor: pointer;
        text-align: left;
        display: flex;
        align-items: center;
    }

    .search-arrow-box {
        position: absolute;
        right: 4px;
        top: 4px;
        width: 50px;
        height: 50px;
        border-radius: 6px;
        background: #F2F2F2;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        transition: background-color 220ms ease;
    }

    .search-arrow-box svg {
        transition: transform 260ms cubic-bezier(.2,.9,.3,1);
        transform-origin: center;
    }

    .search-select-wrap.is-open .search-arrow-box {
        background: #FBE7F2;
    }

    .search-select-wrap.is-open .search-arrow-box svg {
        transform: rotate(180deg);
    }

    .search-dropdown-panel {
        position: absolute;
        left: -2px;
        top: calc(100% - 2px);
        width: calc(100% + 4px);
        max-height: 430px;
        overflow-y: auto;
        background: #FFFFFF;
        border: 2px solid #E6E6E6;
        border-top: 0;
        border-radius: 0 0 24px 24px;
        box-shadow: 0 18px 34px rgba(92, 45, 98, 0.12);
        z-index: 95;
        padding: 24px 54px 18px;
        box-sizing: border-box;
    }

    .search-dropdown-panel--region {
        padding-left: 24px;
        padding-right: 24px;
    }

    .search-dropdown-item {
        width: 100%;
        border: 0;
        border-radius: 0;
        background: transparent;
        padding: 12px 0;
        text-align: left;
        font-family: 'Poppins', sans-serif;
        font-weight: 400;
        font-size: 14px;
        line-height: 1.3;
        color: #505050;
        cursor: pointer;
        position: relative;
        transition: color 160ms ease;
    }

    .search-dropdown-item::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 1px;
        background: #E9E9E9;
    }

    .search-dropdown-item--region {
        width: 518px;
        max-width: 100%;
        margin: 0 auto;
    }

    .search-dropdown-item--region::after {
        left: 50%;
        right: auto;
        width: min(518px, 100%);
        transform: translateX(-50%);
    }

    .search-dropdown-item:hover {
        background: transparent;
        color: #DD3888;
    }

    .search-dropdown-item.is-active {
        background: transparent;
        color: #DD3888;
    }

    .search-dropdown-item:last-child {
        border-bottom: 0;
    }

    .search-dropdown-item:last-child::after {
        display: none;
    }

    .search-mobile-picker {
        position: fixed;
        top: 56px;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 180;
        background: #FFFFFF;
        overflow-y: auto;
    }

    .search-country-mobile-picker {
    }

    .search-country-mobile-picker.open {
        display: block !important;
    }

    .search-town-mobile-picker.open {
        display: block !important;
    }

    .search-country-mobile-picker__inner {
        width: min(100%, 425px);
        min-height: 100%;
        margin: 0 auto;
        padding: 24px 25px 32px;
        box-sizing: border-box;
    }

    .search-country-mobile-picker__actions {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 20px;
    }

    .search-country-mobile-picker__close {
        width: 48px;
        height: 48px;
        border: 2px solid #DD3888;
        border-radius: 999px;
        background: #FFFFFF;
        color: #DD3888;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: transform 180ms ease, box-shadow 180ms ease;
    }

    .search-mobile-country-chip {
        width: 100%;
        border: 2px solid #DD3888;
        border-radius: 10px;
        padding: 12px 14px;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        gap: 12px;
        background: #FFFFFF;
        margin-bottom: 12px;
    }

    .search-mobile-country-chip__text {
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        font-weight: 600;
        color: #323232;
    }

    .search-mobile-country-chip__clear {
        margin-left: auto;
        border: 0;
        background: transparent;
        padding: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .search-country-mobile-picker__close:hover {
        transform: scale(1.04);
        box-shadow: 0 10px 24px rgba(221, 56, 136, 0.18);
    }

    .search-country-mobile-picker__search {
        position: relative;
        margin-bottom: 20px;
    }

    .search-country-mobile-picker__search-input {
        width: 100%;
        height: 60px;
        border: 2px solid #E6E6E6;
        border-radius: 10px;
        background: #FFFFFF;
        padding: 0 52px 0 44px;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        font-weight: 600;
        color: #323232;
        outline: none;
    }

    .search-country-mobile-picker__search-input:focus {
        border-color: #DD3888;
    }

    .search-country-mobile-picker__search-icon,
    .search-country-mobile-picker__clear {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #DD3888;
    }

    .search-country-mobile-picker__search-icon {
        left: 16px;
        pointer-events: none;
    }

    .search-country-mobile-picker__clear {
        right: 14px;
        width: 24px;
        height: 24px;
        border: 0;
        background: transparent;
        padding: 0;
        cursor: pointer;
    }

    .search-country-mobile-picker__list {
        display: flex;
        flex-direction: column;
    }

    .search-country-mobile-picker__item {
        width: 100%;
        border: 0;
        border-bottom: 1px solid #EAEAEA;
        background: transparent;
        padding: 14px 0;
        display: flex;
        align-items: center;
        gap: 12px;
        text-align: left;
    }

    .search-country-mobile-picker__flag {
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        border-radius: 999px;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .search-country-mobile-picker__flag img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .search-country-mobile-picker__name {
        min-width: 0;
        flex: 1;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.3;
        color: #505050;
    }

    .search-country-mobile-picker__count {
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.3;
        color: #B8B8B8;
    }

    .search-country-mobile-picker__empty {
        padding: 18px 0;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        color: #8F8F8F;
    }

    .search-submit {
        width: 210px;
        height: 60px;
        border: 0;
        border-radius: 8px;
        background: #DD3888;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 16px;
        color: #FFFFFF;
        cursor: pointer;
        transition: transform 240ms cubic-bezier(.2,.9,.3,1), box-shadow 240ms ease, background-color 220ms ease;
    }

    .search-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 32px rgba(221, 56, 136, 0.28);
        background: #C92F7A;
    }

    .search-submit:active {
        transform: translateY(-1px);
    }

    .search-submit-icon {
        width: 24px;
        height: 24px;
        stroke: #FFFFFF;
        transition: transform 220ms ease;
    }

    .search-submit:hover .search-submit-icon {
        transform: translateX(3px) scale(1.05);
    }

    @keyframes heartBeat {
        0%, 100% {
            transform: scale(1);
        }
        10% {
            transform: scale(1.08);
        }
        18% {
            transform: scale(0.98);
        }
        26% {
            transform: scale(1.12);
        }
        34% {
            transform: scale(1);
        }
    }

    @media (max-width: 1024px) {
        .search-hero-card {
            height: auto;
            padding: 22px 18px;
        }

        .search-hero-top {
            flex-direction: column;
            gap: 12px;
            margin-bottom: 12px;
        }

        .search-hero-form-row {
            flex-wrap: wrap;
        }

        .search-hero-field.region,
        .search-hero-field.age,
        .search-hero-field.country,
        .search-hero-field.town,
        .search-submit {
            width: 100%;
        }

        .search-hero-badges {
            display: none;
        }
    }

    @media (max-width: 425px) {
        .search-hero-badges {
            display: none !important;
        }

        .search-badge {
            width: 310px !important;
            max-width: 310px !important;
            height: 35px !important;
            background: rgba(242, 242, 242, 0.9) !important;
            border-radius: 999px !important;
            padding: 0 12px !important;
        }

        .search-hero-card {
            width: 360px !important;
            min-width: 360px !important;
            max-width: 360px !important;
            height: 386px !important;
            min-height: 386px !important;
            max-height: 386px !important;
            padding: 24px !important;
            box-sizing: border-box !important;
            margin: 0 auto !important;
            border-radius: 16px !important;
            position: relative !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: flex-start !important;
            overflow: visible !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
        }

        .search-hero-top {
            margin-bottom: 20px !important;
        }

        .search-hero-title {
            font-size: 18px !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
        }

        .search-hero-heart {
            width: 20px !important;
            height: 20px !important;
        }

        .search-hero-form-row {
            display: flex !important;
            flex-direction: column !important;
            gap: 15px !important;
            align-items: center !important;
            width: 100% !important;
        }

        .search-hero-field.region,
        .search-hero-field.age,
        .search-hero-field.country,
        .search-hero-field.town {
            width: 310px !important;
            max-width: 310px !important;
            margin: 0 auto !important;
        }

        .search-select-wrap {
            width: 310px !important;
            height: 60px !important;
            margin: 0 auto !important;
        }

        .search-select-trigger {
            width: 310px !important;
            height: 60px !important;
            padding: 0 64px 0 16px !important;
            font-size: 14px !important;
        }

        .search-submit {
            width: 310px !important;
            height: 60px !important;
            margin-top: 5px !important;
        }

        .search-hero-card--english {
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            padding: 0 !important;
            height: auto !important;
            min-height: 0 !important;
            max-height: none !important;
        }

        .search-hero-card--english::before {
            display: none !important;
        }

        .search-hero-card--english:hover {
            transform: none !important;
            box-shadow: none !important;
        }

        .search-hero-card--english .search-hero-top {
            display: none !important;
        }

        .search-hero-card--english .search-hero-title,
        .search-hero-card--english .search-hero-heart {
            display: none !important;
        }

        .search-hero-card--english .search-submit {
            display: none !important;
        }

        .search-hero-card--english .search-select-wrap.is-open {
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .search-hero-card--english .search-arrow-box {
            pointer-events: none;
        }

        .search-hero-card--english .search-country-mobile-picker {
            display: block;
        }
    }

    @media (max-width: 359px) {
        .search-hero-card {
            width: calc(100vw - 16px) !important;
            min-width: 0 !important;
            max-width: calc(100vw - 16px) !important;
            left: auto !important;
            transform: none !important;
        }

        .search-hero-field.region,
        .search-hero-field.age,
        .search-hero-field.country,
        .search-hero-field.town,
        .search-select-wrap,
        .search-select-trigger,
        .search-submit,
        .search-badge {
            width: 100% !important;
            max-width: 100% !important;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .search-hero-card,
        .search-hero-card::before,
        .search-hero-heart,
        .search-badge,
        .search-select-wrap,
        .search-submit,
        .search-submit-icon {
            animation: none;
            transition: none;
        }
    }
</style>

<?php ($isEnglishLocationSearch = app()->getLocale() === 'en'); ?>

<!--[if BLOCK]><![endif]--><?php if($isEnglishLocationSearch): ?>
<div class="search-hero-card search-hero-card--english"
            x-data="{
            openCountry: false,
            openTown: false,
            fullscreenCountrySearch: false,
            countryQuery: '',
            countries: <?php echo \Illuminate\Support\Js::from($this->englishCountries)->toHtml() ?>,
            serverTowns: <?php echo \Illuminate\Support\Js::from($this->countryTowns ?? [])->toHtml() ?>,
            countryValue: <?php echo \Illuminate\Support\Js::from($country ?: ($this->englishCountries[0]['name'] ?? ''))->toHtml() ?>,
            countryCodeValue: <?php echo \Illuminate\Support\Js::from($country_code ?: ($this->englishCountries[0]['code'] ?? ''))->toHtml() ?>,
            townValue: <?php echo \Illuminate\Support\Js::from($region ?: '')->toHtml() ?>,
            shouldAutoSearch() {
                return window.innerWidth <= 425;
            },
            usesFullscreenCountryPicker() {
                return window.innerWidth <= 767;
            },
            fullscreenTownSearch: false,
            townQuery: '',
            openTownPicker() {
                if (!this.towns || this.towns.length === 0) return;

                if (this.usesFullscreenCountryPicker()) {
                    this.fullscreenTownSearch = true;
                    this.townQuery = '';
                    this.openTown = false;
                    this.openCountry = false;
                    document.documentElement.style.overflow = 'hidden';
                    document.body.style.overflow = 'hidden';

                    this.$nextTick(() => {
                        if (this.$refs.townSearchInput) {
                            this.$refs.townSearchInput.focus();
                        }
                    });

                    return;
                }

                this.openTown = !this.openTown;
                this.openCountry = false;
            },
            closeTownPicker() {
                this.fullscreenTownSearch = false;
                document.documentElement.style.overflow = '';
                document.body.style.overflow = '';
            },
            clearTownQuery() {
                this.townQuery = '';

                this.$nextTick(() => {
                    if (this.$refs.townSearchInput) {
                        this.$refs.townSearchInput.focus();
                    }
                });
            },
            get filteredTowns() {
                const q = this.townQuery.trim().toLowerCase();
                if (!q) return this.towns;
                return this.towns.filter((t) => t.toLowerCase().includes(q));
            },
            openCountryPicker() {
                if (this.usesFullscreenCountryPicker()) {
                    this.fullscreenCountrySearch = true;
                    this.countryQuery = '';
                    this.openCountry = false;
                    this.openTown = false;
                    document.documentElement.style.overflow = 'hidden';
                    document.body.style.overflow = 'hidden';

                    this.$nextTick(() => {
                        if (this.$refs.countrySearchInput) {
                            this.$refs.countrySearchInput.focus();
                        }
                    });

                    return;
                }

                this.openCountry = !this.openCountry;
                this.openTown = false;
            },
            closeCountryPicker() {
                this.fullscreenCountrySearch = false;
                document.documentElement.style.overflow = '';
                document.body.style.overflow = '';
            },
            clearCountryQuery() {
                this.countryQuery = '';

                this.$nextTick(() => {
                    if (this.$refs.countrySearchInput) {
                        this.$refs.countrySearchInput.focus();
                    }
                });
            },
            clearCountrySelection() {
                this.countryValue = '';
                this.countryCodeValue = '';
                this.serverTowns = [];
                this.townValue = '';
                $wire.set('country', '');
                $wire.set('country_code', '');
                $wire.set('region', '');
            },
            runSearch() {
                $wire.search();
            },
            get selectedCountry() {
                return this.countries.find((country) => country.code === this.countryCodeValue)
                    || this.countries.find((country) => country.name === this.countryValue)
                    || null;
            },
            get filteredCountries() {
                const query = this.countryQuery.trim().toLowerCase();

                if (!query) {
                    return this.countries;
                }

                return this.countries.filter((country) => country.name.toLowerCase().includes(query));
            },
            get towns() {
                // prefer explicit regions declared on the static country object
                if (this.selectedCountry && Array.isArray(this.selectedCountry.regions) && this.selectedCountry.regions.length) {
                    return this.selectedCountry.regions;
                }

                // fall back to server-provided towns for the selected country
                return this.serverTowns || [];
            },
            selectCountry(country) {
                this.countryValue = country.name;
                this.countryCodeValue = country.code;
                this.townValue = '';
                // immediately set client-side towns from known static regions (if any)
                this.serverTowns = country.regions && country.regions.length ? country.regions : [];
                this.openCountry = false;
                this.openTown = false;
                this.closeCountryPicker();
                $wire.set('country', country.name);
                $wire.set('country_code', country.code);
                $wire.set('region', '');

                if (this.shouldAutoSearch() && (!country.regions || country.regions.length === 0)) {
                    this.runSearch();
                }
            },
            selectTown(town) {
                this.townValue = town;
                this.openTown = false;
                $wire.set('region', town);

                // close fullscreen town picker if open
                if (this.fullscreenTownSearch) {
                    this.closeTownPicker();
                }

                if (this.shouldAutoSearch()) {
                    this.runSearch();
                }
            }
        }"
            @click.outside="if (!fullscreenCountrySearch && !fullscreenTownSearch) { openCountry = false; openTown = false }"
            @keydown.escape.window="if (fullscreenTownSearch) { closeTownPicker() } else if (fullscreenCountrySearch) { closeCountryPicker() } else { openCountry = false; openTown = false }">
<?php else: ?>
<div class="search-hero-card"
        x-data="{ openRegion: false, openAge: false, regionValue: <?php echo \Illuminate\Support\Js::from($region ?: ($this->allRegions[0] ?? ''))->toHtml() ?>, ageValue: <?php echo \Illuminate\Support\Js::from($age_range ?: '18')->toHtml() ?>, ages: <?php echo \Illuminate\Support\Js::from($this->ageRangeOptions)->toHtml() ?> }"
        @click.outside="openRegion = false; openAge = false"
        @keydown.escape.window="openRegion = false; openAge = false">
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    <div class="search-hero-top">
        <h4 class="search-hero-title">
            <svg class="search-hero-heart" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M12 21s-6.716-4.35-9.11-8.028C.927 10.024 2.08 5.96 5.82 5.14 8.001 4.67 9.83 5.5 11 7.01c1.17-1.51 2.999-2.34 5.18-1.87 3.74.82 4.893 4.885 2.93 7.832C18.716 16.65 12 21 12 21Z" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span><?php echo e(__('front.profiles.search.title')); ?></span>
        </h4>

        <div class="search-hero-badges" aria-hidden="true">
            <div class="search-badge">
                <span class="search-badge-dot"></span>
                <span class="search-badge-strong">1 420 <?php echo e(__('front.profiles.search.girls')); ?></span>
                <span class="search-badge-soft"><?php echo e(__('front.profiles.search.registered')); ?></span>
            </div>
            <div class="search-badge">
                <span class="search-badge-dot"></span>
                <span class="search-badge-strong">382 <?php echo e(__('front.profiles.search.men')); ?></span>
                <span class="search-badge-soft"><?php echo e(__('front.profiles.search.registered')); ?></span>
            </div>
        </div>
    </div>

    <form wire:submit.prevent="search">
        <div class="search-hero-form-row">
            <!--[if BLOCK]><![endif]--><?php if($isEnglishLocationSearch): ?>
                <div class="search-hero-field country">
                    <label class="search-hero-label" for="country-select"><?php echo e(__('front.profiles.search.country_and_town')); ?></label>
                    <div class="search-select-wrap" x-bind:class="{ 'is-open': openCountry }">
                        <button id="country-select" type="button" class="search-select-trigger" @click="openCountryPicker()">
                            <span x-text="countryValue || <?php echo \Illuminate\Support\Js::from(__('front.profiles.search.select_country'))->toHtml() ?>"></span>
                        </button>
                        <span class="search-arrow-box">
                            <svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M1 1L5 4L9 1" stroke="#DD3888" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>

                        <div class="search-dropdown-panel search-dropdown-panel--region" x-show="openCountry && !usesFullscreenCountryPicker()" x-cloak x-transition:enter="transition ease-out duration-220" x-transition:enter-start="opacity-0 -translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-160" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-1">
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->englishCountries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $countryOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" class="search-dropdown-item search-dropdown-item--region" x-bind:class="{ 'is-active': countryCodeValue === <?php echo \Illuminate\Support\Js::from($countryOption['code'])->toHtml() ?> }" @click="selectCountry({ name: <?php echo \Illuminate\Support\Js::from($countryOption['name'])->toHtml() ?>, code: <?php echo \Illuminate\Support\Js::from($countryOption['code'])->toHtml() ?>, regions: <?php echo \Illuminate\Support\Js::from($countryOption['regions'] ?? [])->toHtml() ?> })"><?php echo e($countryOption['name']); ?></button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                </div>

                <div class="search-hero-field town">
                    <label class="sr-only" for="town-select"><?php echo e(__('front.profiles.search.select_town')); ?></label>
                    <div class="search-select-wrap" x-bind:class="{ 'is-open': openTown }">
                        <button id="town-select" type="button" class="search-select-trigger" @click="openTownPicker()">
                            <span x-text="townValue || <?php echo \Illuminate\Support\Js::from(__('front.profiles.search.select_town'))->toHtml() ?>"></span>
                        </button>
                        <span class="search-arrow-box">
                            <svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M1 1L5 4L9 1" stroke="#DD3888" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>

                        <div class="search-dropdown-panel" x-show="openTown && towns.length" x-cloak x-transition:enter="transition ease-out duration-220" x-transition:enter-start="opacity-0 -translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-160" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-1">
                            <template x-for="town in towns" :key="town">
                                <button type="button" class="search-dropdown-item flex items-center gap-3" x-bind:class="{ 'is-active': townValue === town }" @click="selectTown(town)">
                                    <span class="search-dropdown-item__icon flex-shrink-0">
                                        <img src="<?php echo e(asset('images/icons/location.svg')); ?>" alt="" class="w-4 h-4" />
                                    </span>
                                    <span class="search-dropdown-item__name" x-text="town"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="search-hero-field region">
                    <label class="search-hero-label" for="region-select"><?php echo e(__('front.profiles.search.select_region')); ?></label>
                    <div class="search-select-wrap" x-bind:class="{ 'is-open': openRegion }">
                        <button id="region-select" type="button" class="search-select-trigger" @click="openRegion = !openRegion; openAge = false">
                            <span x-text="regionValue"></span>
                        </button>
                        <span class="search-arrow-box">
                            <svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M1 1L5 4L9 1" stroke="#DD3888" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>

                        <div class="search-dropdown-panel search-dropdown-panel--region" x-show="openRegion" x-cloak x-transition:enter="transition ease-out duration-220" x-transition:enter-start="opacity-0 -translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-160" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-1">
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->allRegions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $regionOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" class="search-dropdown-item search-dropdown-item--region" x-bind:class="{ 'is-active': regionValue === <?php echo \Illuminate\Support\Js::from($regionOption)->toHtml() ?> }" @click="regionValue = <?php echo \Illuminate\Support\Js::from($regionOption)->toHtml() ?>; openRegion = false; $wire.set('region', <?php echo \Illuminate\Support\Js::from($regionOption)->toHtml() ?>)"><?php echo e($regionOption); ?></button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                </div>

                <div class="search-hero-field age">
                    <label class="search-hero-label" for="age-select"><?php echo e(__('front.profiles.search.girl_age')); ?></label>
                    <div class="search-select-wrap" x-bind:class="{ 'is-open': openAge }">
                        <button id="age-select" type="button" class="search-select-trigger" @click="openAge = !openAge; openRegion = false">
                            <span x-text="ages[ageValue] || ''"></span>
                        </button>
                        <span class="search-arrow-box">
                            <svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M1 1L5 4L9 1" stroke="#DD3888" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>

                        <div class="search-dropdown-panel" x-show="openAge" x-cloak x-transition:enter="transition ease-out duration-220" x-transition:enter-start="opacity-0 -translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-160" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-1">
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->ageRangeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ageKey => $ageLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" class="search-dropdown-item" x-bind:class="{ 'is-active': ageValue === <?php echo \Illuminate\Support\Js::from($ageKey)->toHtml() ?> }" @click="ageValue = <?php echo \Illuminate\Support\Js::from($ageKey)->toHtml() ?>; openAge = false; $wire.set('age_range', <?php echo \Illuminate\Support\Js::from($ageKey)->toHtml() ?>)"><?php echo e($ageLabel); ?></button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <button type="submit" class="search-submit" wire:loading.attr="disabled" wire:loading.class="opacity-80">
                <span wire:loading.remove wire:target="search"><?php echo e(__('front.profiles.search.search_button')); ?></span>
                <span wire:loading wire:target="search"><?php echo e(__('front.profiles.search.searching_button')); ?></span>
                <svg wire:loading.remove wire:target="search" class="search-submit-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" stroke-width="2"/>
                    <path d="M16.5 16.5L21 21" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    </form>

    <!--[if BLOCK]><![endif]--><?php if($isEnglishLocationSearch): ?>
        <div class="search-mobile-picker search-town-mobile-picker" x-show="fullscreenTownSearch" x-cloak x-transition.opacity.duration.180ms>
            <div class="search-country-mobile-picker__inner">
                <div class="search-country-mobile-picker__actions">
                    <button type="button" class="search-country-mobile-picker__close" @click="closeTownPicker()" aria-label="<?php echo e(__('front.profiles.search.close_mobile_search')); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M6 6L18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                <div class="search-town-header">
                    <div class="search-mobile-country-chip" x-show="countryValue" x-cloak>
                        <span class="search-mobile-country-chip__icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="11" cy="11" r="7" stroke="#DD3888" stroke-width="1.6"/>
                                <path d="M16.5 16.5L21 21" stroke="#DD3888" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span class="search-mobile-country-chip__text" x-text="countryValue"></span>
                        <button type="button" class="search-mobile-country-chip__clear" @click="clearCountrySelection()" aria-label="<?php echo e(__('front.profiles.search.clear_mobile_search')); ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M6 6L18 18" stroke="#DD3888" stroke-width="1.6" stroke-linecap="round"/>
                                <path d="M18 6L6 18" stroke="#DD3888" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="search-country-mobile-picker__search">
                    <span class="search-country-mobile-picker__search-icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                            <path d="M16.5 16.5L21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <input
                        x-ref="townSearchInput"
                        x-model="townQuery"
                        type="text"
                        class="search-country-mobile-picker__search-input"
                        placeholder="<?php echo e(__('front.profiles.search.select_town')); ?>"
                        autocomplete="off"
                    >
                    <button type="button" class="search-country-mobile-picker__clear" x-show="townQuery.length" x-cloak @click="clearTownQuery()" aria-label="<?php echo e(__('front.profiles.search.clear_mobile_search')); ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M6 6L18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                <div class="search-country-mobile-picker__list">
                    <template x-for="town in filteredTowns" :key="town">
                        <button type="button" class="search-country-mobile-picker__item" @click="selectTown(town)">
                            <span class="search-country-mobile-picker__flag">
                                <img src="<?php echo e(asset('images/icons/location.svg')); ?>" alt="">
                            </span>
                            <span class="search-country-mobile-picker__name" x-text="town"></span>
                            <span class="search-country-mobile-picker__count"></span>
                        </button>
                    </template>

                    <div class="search-country-mobile-picker__list-server" x-cloak>
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->countryTowns ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $townOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('countries.index', ['locale' => app()->getLocale(), 'country' => $country ?? $this->country, 'country_code' => $country_code ?? $this->country_code, 'region' => $townOption])); ?>" class="search-country-mobile-picker__item">
                                <span class="search-country-mobile-picker__flag">
                                    <img src="<?php echo e(asset('images/icons/location.svg')); ?>" alt="">
                                </span>
                                <span class="search-country-mobile-picker__name"><?php echo e($townOption); ?></span>
                                <span class="search-country-mobile-picker__count"></span>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="search-country-mobile-picker__empty" x-show="filteredTowns.length === 0" x-cloak>
                        <?php echo e(__('front.profiles.search.no_mobile_countries_found')); ?>

                    </div>
                </div>
            </div>
        </div>

        <div class="search-mobile-picker search-country-mobile-picker" x-show="fullscreenCountrySearch" x-cloak x-transition.opacity.duration.180ms>
            <div class="search-country-mobile-picker__inner">
                <div class="search-country-mobile-picker__actions">
                    <button type="button" class="search-country-mobile-picker__close" @click="closeCountryPicker()" aria-label="<?php echo e(__('front.profiles.search.close_mobile_search')); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M6 6L18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                <div class="search-country-mobile-picker__search">
                    <span class="search-country-mobile-picker__search-icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                            <path d="M16.5 16.5L21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <input
                        x-ref="countrySearchInput"
                        x-model="countryQuery"
                        type="text"
                        class="search-country-mobile-picker__search-input"
                        placeholder="<?php echo e(__('front.profiles.search.search_country_placeholder')); ?>"
                        autocomplete="off"
                    >
                    <button type="button" class="search-country-mobile-picker__clear" x-show="countryQuery.length" x-cloak @click="clearCountryQuery()" aria-label="<?php echo e(__('front.profiles.search.clear_mobile_search')); ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M6 6L18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                <div class="search-country-mobile-picker__list">
                    <template x-for="country in filteredCountries" :key="country.code">
                        <button type="button" class="search-country-mobile-picker__item" @click="selectCountry(country)">
                            <span class="search-country-mobile-picker__flag">
                                <img :src="'https://flagcdn.com/' + country.code + '.svg'" :alt="country.name">
                            </span>
                            <span class="search-country-mobile-picker__name" x-text="country.name"></span>
                            <span class="search-country-mobile-picker__count" x-text="country.count ?? ''"></span>
                        </button>
                    </template>

                    
                    <div class="search-country-mobile-picker__list-server" x-cloak>
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->englishCountries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $countryOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('countries.index', ['locale' => app()->getLocale(), 'country' => $countryOption['name'], 'country_code' => $countryOption['code']])); ?>" class="search-country-mobile-picker__item">
                                <span class="search-country-mobile-picker__flag">
                                    <img src="https://flagcdn.com/<?php echo e($countryOption['code']); ?>.svg" alt="<?php echo e($countryOption['name']); ?>">
                                </span>
                                <span class="search-country-mobile-picker__name"><?php echo e($countryOption['name']); ?></span>
                                <span class="search-country-mobile-picker__count"><?php echo e($countryOption['count'] ?? ''); ?></span>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="search-country-mobile-picker__empty" x-show="filteredCountries.length === 0" x-cloak>
                        <?php echo e(__('front.profiles.search.no_mobile_countries_found')); ?>

                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    <script>
        (function () {
            function openPicker() {
                var picker = document.querySelector('.search-country-mobile-picker');
                if (!picker) return;
                picker.classList.add('open');
                document.documentElement.style.overflow = 'hidden';
                document.body.style.overflow = 'hidden';
            }

            function closePicker() {
                var picker = document.querySelector('.search-country-mobile-picker');
                if (picker) {
                    picker.classList.remove('open');
                }

                var townPicker = document.querySelector('.search-town-mobile-picker');
                if (townPicker) {
                    townPicker.classList.remove('open');
                }

                document.documentElement.style.overflow = '';
                document.body.style.overflow = '';
            }

            function openTownPicker() {
                var picker = document.querySelector('.search-town-mobile-picker');
                if (!picker) return;
                picker.classList.add('open');
                document.documentElement.style.overflow = 'hidden';
                document.body.style.overflow = 'hidden';
            }

            function closeTownPicker() {
                var picker = document.querySelector('.search-town-mobile-picker');
                if (!picker) return;
                picker.classList.remove('open');
                document.documentElement.style.overflow = '';
                document.body.style.overflow = '';
            }

            document.addEventListener('click', function (e) {
                // open on small screens when country select is tapped
                if (window.innerWidth > 767) return;
                if (e.target.closest && e.target.closest('#country-select')) {
                    e.preventDefault();
                    openPicker();
                }

                if (e.target.closest && e.target.closest('#town-select')) {
                    e.preventDefault();
                    openTownPicker();
                }

                // close when close button is pressed
                if (e.target.closest && e.target.closest('.search-country-mobile-picker__close')) {
                    e.preventDefault();
                    closePicker();
                }

                // close when tapping outside inner area
                var picker = document.querySelector('.search-country-mobile-picker');
                if (picker && picker.style.display === 'block' && e.target.closest) {
                    var inner = e.target.closest('.search-country-mobile-picker__inner');
                    var isTrigger = e.target.closest && e.target.closest('#country-select');
                    if (!inner && !isTrigger && !e.target.closest('.search-country-mobile-picker__close')) {
                        closePicker();
                    }
                }
            });
        })();
    </script>
</div>
</div><?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/livewire/search-profiles.blade.php ENDPATH**/ ?>