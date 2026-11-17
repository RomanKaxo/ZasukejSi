<div>
<style>
    .search-hero-card {
        width: 1134px;
        max-width: 100%;
        height: 218px;
        background: #FFFFFF;
        border-radius: 24px;
        /* Figma home.jpg: no outline, white straight into the shadow; content
           48 px in from the left (field at x 441), 49 px from the right. */
        border: 0;
        box-shadow: 0 10px 25px 0 rgba(220, 214, 221, 0.95);
        padding: 49px 49px 0 48px;
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
        margin-bottom: 20px;
    }

    .search-hero-title {
        display: flex;
        align-items: center;
        gap: 9px;
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 18px;
        color: #505050;
        margin: 0;
    }

    .search-hero-heart {
        width: 23px;
        height: 23px;
        stroke: #DD3888;
        transition: transform 240ms ease, filter 240ms ease;
        animation: heartBeat 3.8s ease-in-out infinite;
    }

    .search-hero-card:hover .search-hero-heart {
        transform: scale(1.12);
        filter: drop-shadow(0 6px 12px rgba(221, 56, 136, 0.25));
    }

    /* Figma home.jpg: content-wide pills 25 px tall on y 468, 13 px apart,
       dot 8 px from the left edge, 12 px type, ~13 px right padding. */
    .search-hero-badges {
        display: flex;
        gap: 13px;
    }

    @media (min-width: 768px) {
        .search-hero-badges { position: relative; top: -2px; }
        /* Heading ink on y 473, labels on y 519 (Figma home.jpg). */
        .search-hero-top { margin-top: -3px; }
        .search-hero-heart { position: relative; top: -2px; left: 1px; }
    }

    .search-badge {
            width: auto;
            height: 25px;
            border-radius: 999px;
            background: #F2F2F2;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 0;
            box-sizing: border-box;
            padding: 0 13px 0 8px;
            transition: transform 220ms ease, box-shadow 220ms ease, background-color 220ms ease;
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
        margin-right: 7px;
    }

    .search-badge-strong {
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 12px;
        color: #505050;
        line-height: 1;
        white-space: nowrap;
    }

    .search-badge-soft {
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
        font-weight: 400;
        font-size: 12px;
        color: #A6A6A6;
        line-height: 1;
        white-space: nowrap;
        margin-left: 4px;
    }

    .search-hero-form-row {
        display: flex;
        align-items: flex-end;
        gap: 10px;
    }

    .search-hero-field {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    @media (min-width: 768px) {
        /* Label ink on y 519, field outline on y 542 (Figma home.jpg). */
        .search-hero-label { padding-top: 1px; padding-left: 7px; }
    }

    /* Figma: region 602, age 205, button 210, 10 px apart. */
    .search-hero-field.region { width: 602px; }
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
        padding: 0 64px 0 19.5px;
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 14px;
        color: #505050;
        cursor: pointer;
        text-align: left;
        display: flex;
        align-items: center;
    }

    /* Grey tile with a pink chevron, chosen value or not (home.jpg, home-1);
       it turns pink with a white chevron only while the list is open
       (home-25.jpg). 3 px inside the 2 px border. */
    .search-arrow-box {
        position: absolute;
        right: 3px;
        top: 3px;
        width: 50px;
        height: 50px;
        border-radius: 6px;
        background: #F2F2F2;
        color: #DD3888;
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
        background: #DD3888;
        color: #FFFFFF;
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

    /* home-25.jpg, region list open (desktop): the select itself does not
       change (grey 2 px frame, no lift), only its chevron tile turns pink.
       Under it hangs a borderless white panel with a soft shadow, 317 px
       tall and scrolling, rows 35 px apart (14 px text, 518 px #ECECEC
       rules centred, first label 31 px under the select). The chosen
       region is not highlighted; the hovered row is pink. */
    @media (min-width: 1024px) {
        .search-hero-field.region .search-select-wrap.is-open,
        .search-hero-field.age .search-select-wrap.is-open {
            border-color: #E6E6E6;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            transform: none;
            box-shadow: none;
        }
        /* The frame has the pointer on the list and nothing lifted. */
        .search-hero-card:has(.search-select-wrap.is-open),
        .search-hero-card:has(.search-select-wrap.is-open):hover,
        .search-hero-field .search-select-wrap.is-open:hover { transform: none; }
        .search-hero-field.region .search-dropdown-panel--region {
            top: calc(100% + 1px);
            max-height: 317px;
            border: 0;
            border-radius: 0 0 16px 16px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.14);
            padding: 20px 0 0;
        }
        .search-hero-field.region .search-dropdown-item--region {
            display: block;
            /* 35.15 px pitch: rules at y 655, 690, 725 … 901 in the frame. */
            padding: 8px 0 9.15px;
            line-height: 18px;
        }
        .search-hero-field.region .search-dropdown-item--region::after { background: #ECECEC; }
        .search-hero-field.region .search-dropdown-item--region.is-active { color: #505050; }
        .search-hero-field.region .search-dropdown-item--region.is-active:hover { color: #DD3888; }
    }

    .search-mobile-picker {
        position: fixed;
        top: 96px;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 180;
        font-family: 'Poppins', sans-serif;
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

    /* phone 360 px-21…23: 40 px close ring from y 92, x 293; a 308 × 60
       search box 27 px under it (2 px #E6E6E6, 14 px loupe 21 px in, 15 px
       semibold text 47 px in); rows 44 px apart from y 243 — 30 px flag,
       15 px name 14 px after it, #A6A6A6 count on the right, a 1 px rule
       from the name to the edge. */
    .search-mobile-picker { top: 92px; }
    .search-country-mobile-picker__inner { padding: 0 26px 32px; }
    .search-country-mobile-picker__actions { margin-bottom: 27px; }
    .search-country-mobile-picker__close { width: 40px; height: 40px; }
    .search-country-mobile-picker__close svg { width: 17px; height: 17px; }
    .search-country-mobile-picker__close svg path { stroke-width: 3; }
    .search-country-mobile-picker__search { margin-bottom: 24px; }
    .search-country-mobile-picker__search-input { padding-left: 44px; font-size: 15px; }
    .search-country-mobile-picker__search-input::placeholder { color: #A5A5A5; opacity: 1; }
    .search-country-mobile-picker__search-icon { left: 19px; }
    /* -21: the ✕ that empties the query, 9 px, ink 26 px from the right. */
    .search-country-mobile-picker__clear { right: 21px; }
    .search-country-mobile-picker__clear svg { width: 16px; height: 16px; }
    .search-country-mobile-picker__clear svg path { stroke-width: 3; }
    /* The frames keep the search box grey while typing (-21, -23). */
    .search-country-mobile-picker__search-input:focus { border-color: #E6E6E6; }
    /* -23: the picked country above the town search — 60 px, 2 px pink,
       loupe ink 22 px in, 15 px semibold name, ✕ 26 px from the right,
       10 px above the town box. */
    .search-mobile-country-chip { height: 60px; padding: 0 14px 0 17px; gap: 10px; margin-bottom: 10px; }
    .search-mobile-country-chip__text { font-size: 15px; }
    .search-mobile-country-chip__clear svg { width: 17px; height: 17px; }
    .search-mobile-country-chip__clear svg path { stroke-width: 2.8; }
    .search-town-pin { width: 22px; height: 27px; }
    .search-town-mobile-picker .search-country-mobile-picker__search { margin-bottom: 23px; }
    .search-town-mobile-picker .search-country-mobile-picker__flag { overflow: visible; border-radius: 0; }
    .search-country-mobile-picker__item {
        padding: 7px 0 6px;
        gap: 13px;
        border-bottom: 0;
        background: linear-gradient(#EAEAEA, #EAEAEA) no-repeat right bottom / calc(100% - 44px) 1px;
        padding-bottom: 7px;
    }
    .search-country-mobile-picker__name,
    .search-country-mobile-picker__count { font-size: 15px; line-height: 20px; }
    .search-country-mobile-picker__count { color: #A6A6A6; }

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

    /* Phone layout up to the tablet breakpoint (md = 768px). Was 425px, so at
       430px (iPhone Plus / Pro Max) and up to 767px the counters vanished and
       the card fell back to the tablet layout. Every value below is measured
       on the 360px phone frame (phone 360 px-11); widths are fluid so the
       same 25px gutters hold on wider phones. */
    /* home.jpg / home-1.jpg at 1920: the 60 px fields start 1 px lower
       (2 px #E6E6E6 border from y 542.5) and the picked value's cap sits
       at y 568, 2 px under the line box's centred position. */
    @media (min-width: 1280px) {
        .search-hero-card .search-select-wrap { margin-top: 1px; }
        .search-hero-card .search-select-trigger > span { position: relative; top: 2px; }
    }

    @media (max-width: 767px) {
        /* Both counters stacked on the hero photo just above the white search
           card: 35px tall, 5px apart, 32px clear of the card, 25px gutters.
           Static so the card, not the title row, becomes the containing block
           the badges are positioned against. */
        .search-hero-top {
            position: static !important;
        }

        .search-hero-badges {
            display: flex !important;
            flex-direction: column !important;
            gap: 5px !important;
            position: absolute !important;
            left: 24px !important;
            right: 24px !important;
            transform: none !important;
            bottom: 100% !important;
            margin: 0 0 33px 0 !important;
            width: auto !important;
            z-index: 26 !important;
        }

        .search-badge {
            width: 100% !important;
            max-width: none !important;
            height: 35px !important;
            background: #F2F2F2 !important;
            backdrop-filter: none !important;
            border-radius: 999px !important;
            padding: 0 12px !important;
            /* Centred in the phone pill (desktop pills are content-wide). */
            justify-content: center !important;
            gap: 0 !important;
        }

        .search-badge-dot {
            margin-right: 6px;
        }

        /* 13px counters in the frame ("<count> dívek registrováno"), the
           number and the word one space apart. Comments here reach the page
           source, so the frame's sample numbers are not quoted. */
        .search-badge-strong,
        .search-badge-soft {
            font-size: 13px !important;
        }

        .search-badge-soft {
            margin-left: 4px;
        }

        /* Edge to edge: 360 wide at 360px, as in the frame. 42px to the
           heading, 29px under the button = 385px tall. */
        .search-hero-card {
            width: 100vw !important;
            min-width: 0 !important;
            max-width: 100vw !important;
            height: auto !important;
            min-height: 0 !important;
            max-height: none !important;
            padding: 43px 24px 30px !important;
            box-sizing: border-box !important;
            margin: 0 !important;
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
            flex-direction: row !important;
            gap: 0 !important;
            margin-bottom: 20px !important;
        }

        .search-hero-title {
            font-size: 18px !important;
            line-height: 22px !important;
            display: flex !important;
            align-items: center !important;
            gap: 7px !important;
            position: relative;
            top: -1px;
        }

        .search-hero-heart {
            width: 22px !important;
            height: 22px !important;
            margin-left: 1px;
            position: relative;
            top: -1px;
        }

        .search-hero-form-row {
            display: flex !important;
            flex-direction: column !important;
            gap: 20px !important;
            align-items: stretch !important;
            width: 100% !important;
        }

        .search-hero-field {
            gap: 5px !important;
        }

        .search-hero-label {
            padding: 0 0 0 10px !important;
            line-height: 20px !important;
            position: relative;
            top: -1px;
        }

        .search-hero-field.region,
        .search-hero-field.age,
        .search-hero-field.country,
        .search-hero-field.town {
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
        }

        .search-select-wrap {
            width: 100% !important;
            height: 60px !important;
            margin: 0 !important;
        }

        .search-select-trigger {
            width: 100% !important;
            height: 100% !important;
            padding: 0 64px 0 19px !important;
            font-size: 14px !important;
        }

        /* The frame keeps the chevron box grey with a pink chevron even with
           a value chosen; the solid pink box is a desktop state. 3px inside
           the 2px border on every side: 50x50 in the 56px inner height. */
        .search-arrow-box {
            top: 3px !important;
            right: 3.5px !important;
            color: #DD3888;
        }

        .search-arrow-box.is-selected {
            background: #F2F2F2 !important;
            color: #DD3888 !important;
        }

        .search-arrow-box.is-selected svg {
            stroke: #DD3888 !important;
        }


        .search-submit {
            width: 100% !important;
            height: 60px !important;
            margin-top: 1px !important;
        }

        /* phone 360 px-11: heading ink at y 570 (43 + 2 px under the card's
           top), the picked values' cap 2 px under the centred line box. */
        .search-hero-card:not(.search-hero-card--english) .search-select-trigger > span { position: relative; top: 2px; }

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

        /* The English card is transparent and padless; its fields keep their
           310px phone width, centred, as before the breakpoint moved. */
        .search-hero-card--english .search-hero-field.country,
        .search-hero-card--english .search-hero-field.town {
            width: 310px !important;
            max-width: 100% !important;
            margin: 0 auto !important;
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

        /* phone 360 px-18/-20: picked country / town — 34 px pink ring with
           a ✕, 12 px left of a solid pink 50 px box with a white chevron. */
        /* No stacking context of its own on the phone: the full-screen
           pickers (z 180) inside must rise above the hero (z 30), which
           otherwise covered their search field and close button. */
        .search-hero-card--english {
            z-index: auto !important;
        }

        /* phone 360 px-19/-20: label 2 px above, fields 10 px apart. */
        .search-hero-card--english .search-hero-form-row {
            gap: 10px !important;
        }

        .search-hero-card--english .search-hero-field.country {
            gap: 6px !important;
        }

        .search-hero-card--english .search-arrow-box {
            right: 4.5px !important;
        }

        .search-hero-card--english .search-select-placeholder {
            color: #A5A5A5;
        }

        .search-hero-card--english .search-arrow-box.is-picked {
            background: #DD3888 !important;
            color: #FFFFFF !important;
        }

        .search-hero-card--english .search-arrow-box.is-picked svg {
            stroke: #FFFFFF !important;
        }

        .search-hero-card--english .search-field-clear {
            position: absolute;
            top: 50%;
            right: 65.5px;
            width: 34px;
            height: 34px;
            margin-top: -17px;
            border: 2px solid #DD3888;
            border-radius: 50%;
            color: #DD3888;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #FFFFFF;
            z-index: 2;
        }

        .search-hero-card--english .search-field-clear svg {
            width: 10px;
            height: 10px;
        }
    }

    .search-field-clear { display: none; }
    .search-field-clear[x-cloak] { display: none !important; }

    @media (max-width: 359px) {
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

@php($isEnglishLocationSearch = app()->getLocale() === 'en')

@if($isEnglishLocationSearch)
<div class="search-hero-card search-hero-card--english"
            x-data="{
            openCountry: false,
            openTown: false,
            fullscreenCountrySearch: false,
            countryQuery: '',
            countries: @js($this->englishCountries),
            serverTowns: @js($this->countryTowns ?? []),
            countryValue: @js($country ?: ($this->englishCountries[0]['name'] ?? '')),
            countryCodeValue: @js($country_code ?: ($this->englishCountries[0]['code'] ?? '')),
            townValue: @js($region ?: ''),
            {{-- A country is "picked" once it came in the URL or was chosen;
                 the default first country is only a placeholder value. --}}
            countryPicked: @js(filled(request('country')) || filled(request('country_code'))),
            clearCountry() {
                this.countryPicked = false;
                this.clearCountrySelection();
                this.runSearch();
            },
            clearTown() {
                this.townValue = '';
                $wire.set('region', '', false);
                this.runSearch();
            },
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
                // Livewire 3.8 drops every other queued update in a request
                // once one of them is an empty string (mergeQueuedUpdates
                // compares keys with the *value*, and '' prefixes every key):
                // clearing the country only ever cleared the town. Setting
                // the values without `live` and committing once sends them as
                // a plain diff instead.
                $wire.set('country', '', false);
                $wire.set('country_code', '', false);
                $wire.set('region', '', false);
                $wire.$commit();
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
                this.countryPicked = true;
                this.countryValue = country.name;
                this.countryCodeValue = country.code;
                this.townValue = '';
                // immediately set client-side towns from known static regions (if any)
                this.serverTowns = country.regions && country.regions.length ? country.regions : [];
                this.openCountry = false;
                this.openTown = false;
                this.closeCountryPicker();
                // Not `live`: see clearCountrySelection(). The trailing
                // set('region', '') used to discard the country, so picking
                // e.g. Germany searched the default first country (Albania).
                $wire.set('country', country.name, false);
                $wire.set('country_code', country.code, false);
                $wire.set('region', '', false);

                if (this.shouldAutoSearch() && (!country.regions || country.regions.length === 0)) {
                    this.runSearch();
                } else {
                    $wire.$commit();
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
@else
<div class="search-hero-card"
        x-data="{ openRegion: false, openAge: false, regionValue: @js($region ?: ($this->allRegions[0] ?? '')), regionLabels: @js($this->regionLabels), ageValue: @js($age_range ?: '18-25'), ages: @js($this->ageRangeOptions) }"
        @click.outside="openRegion = false; openAge = false"
        @keydown.escape.window="openRegion = false; openAge = false">
@endif
    <div class="search-hero-top">
        <h4 class="search-hero-title">
            <svg class="search-hero-heart" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M12 21s-6.716-4.35-9.11-8.028C.927 10.024 2.08 5.96 5.82 5.14 8.001 4.67 9.83 5.5 11 7.01c1.17-1.51 2.999-2.34 5.18-1.87 3.74.82 4.893 4.885 2.93 7.832C18.716 16.65 12 21 12 21Z" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>{{ __('front.profiles.search.title') }}</span>
        </h4>

        <div class="search-hero-badges" aria-hidden="true">
            <div class="search-badge">
                <span class="search-badge-dot"></span>
                <span class="search-badge-strong">{{ number_format($this->girlsCount, 0, ',', ' ') }} {{ __('front.profiles.search.girls') }}</span>
                <span class="search-badge-soft">{{ __('front.profiles.search.registered') }}</span>
            </div>
            <div class="search-badge">
                <span class="search-badge-dot"></span>
                <span class="search-badge-strong">{{ number_format($this->gentsCount, 0, ',', ' ') }} {{ __('front.profiles.search.men') }}</span>
                <span class="search-badge-soft">{{ __('front.profiles.search.registered') }}</span>
            </div>
        </div>
    </div>

    <form wire:submit.prevent="search">
        <div class="search-hero-form-row">
            @if($isEnglishLocationSearch)
                <div class="search-hero-field country">
                    <label class="search-hero-label" for="country-select">{{ __('front.profiles.search.country_and_town') }}</label>
                    <div class="search-select-wrap" x-bind:class="{ 'is-open': openCountry }">
                        <button id="country-select" type="button" class="search-select-trigger" @click="openCountryPicker()">
                            {{-- phone 360 px-16: nothing picked yet reads "All Countries" in grey. --}}
                            <span x-text="countryPicked ? (countryValue || @js(__('front.profiles.search.select_country'))) : @js(__('front.profiles.search.all_countries'))" x-bind:class="{ 'search-select-placeholder': !countryPicked }"></span>
                        </button>
                        {{-- phone 360 px-18/-20: a picked value gets a pink ✕ ring and a solid pink chevron box. --}}
                        <button type="button" class="search-field-clear" x-show="countryPicked" x-cloak @click.stop="clearCountry()" aria-label="{{ __('front.profiles.list.clear_all_filters') }}">
                            <svg viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M2 2L10 10M10 2L2 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        </button>
                        <span class="search-arrow-box" x-bind:class="{ 'is-selected': countryValue, 'is-picked': countryPicked }">
                            <svg width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>

                        <div class="search-dropdown-panel search-dropdown-panel--region" x-show="openCountry && !usesFullscreenCountryPicker()" x-cloak x-transition:enter="transition ease-out duration-220" x-transition:enter-start="opacity-0 -translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-160" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-1">
                            @foreach($this->englishCountries as $countryOption)
                                <button type="button" class="search-dropdown-item search-dropdown-item--region" x-bind:class="{ 'is-active': countryCodeValue === @js($countryOption['code']) }" @click="selectCountry({ name: @js($countryOption['name']), code: @js($countryOption['code']), regions: @js($countryOption['regions'] ?? []) })">{{ $countryOption['name'] }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- phone 360 px-16: no town field until a country is picked. --}}
                <div class="search-hero-field town" x-show="countryPicked" x-cloak>
                    <label class="sr-only" for="town-select">{{ __('front.profiles.search.select_town') }}</label>
                    <div class="search-select-wrap" x-bind:class="{ 'is-open': openTown }">
                        <button id="town-select" type="button" class="search-select-trigger" @click="openTownPicker()">
                            <span x-text="townValue || @js(__('front.profiles.search.select_town'))"></span>
                        </button>
                        <button type="button" class="search-field-clear" x-show="townValue" x-cloak @click.stop="clearTown()" aria-label="{{ __('front.profiles.list.clear_all_filters') }}">
                            <svg viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M2 2L10 10M10 2L2 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        </button>
                        <span class="search-arrow-box" x-bind:class="{ 'is-selected': townValue, 'is-picked': townValue }">
                            <svg width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>

                        <div class="search-dropdown-panel" x-show="openTown && towns.length" x-cloak x-transition:enter="transition ease-out duration-220" x-transition:enter-start="opacity-0 -translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-160" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-1">
                            <template x-for="town in towns" :key="town">
                                <button type="button" class="search-dropdown-item flex items-center gap-3" x-bind:class="{ 'is-active': townValue === town }" @click="selectTown(town)">
                                    <span class="search-dropdown-item__icon flex-shrink-0">
                                        <img src="{{ asset('images/icons/location.svg') }}" alt="" class="w-4 h-4" />
                                    </span>
                                    <span class="search-dropdown-item__name" x-text="town"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            @else
                <div class="search-hero-field region">
                    <label class="search-hero-label" for="region-select">{{ __('front.profiles.search.select_region') }}</label>
                    <div class="search-select-wrap" x-bind:class="{ 'is-open': openRegion }">
                        <button id="region-select" type="button" class="search-select-trigger" @click="openRegion = !openRegion; openAge = false">
                            <span x-text="regionLabels[regionValue] || regionValue"></span>
                        </button>
                        <span class="search-arrow-box" x-bind:class="{ 'is-selected': regionValue }">
                            <svg width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>

                        <div class="search-dropdown-panel search-dropdown-panel--region" x-show="openRegion" x-cloak x-transition:enter="transition ease-out duration-220" x-transition:enter-start="opacity-0 -translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-160" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-1">
                            @foreach($this->regionOptions as $regionOption)
                                <button type="button" class="search-dropdown-item search-dropdown-item--region" x-bind:class="{ 'is-active': regionValue === @js($regionOption) }" @click="regionValue = @js($regionOption); openRegion = false; $wire.set('region', @js($regionOption))">{{ $this->regionLabels[$regionOption] ?? $regionOption }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="search-hero-field age">
                    <label class="search-hero-label" for="age-select">{{ __('front.profiles.search.girl_age') }}</label>
                    <div class="search-select-wrap" x-bind:class="{ 'is-open': openAge }">
                        <button id="age-select" type="button" class="search-select-trigger" @click="openAge = !openAge; openRegion = false">
                            <span x-text="ages[ageValue] || ''"></span>
                        </button>
                        <span class="search-arrow-box" x-bind:class="{ 'is-selected': ageValue }">
                            <svg width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>

                        <div class="search-dropdown-panel" x-show="openAge" x-cloak x-transition:enter="transition ease-out duration-220" x-transition:enter-start="opacity-0 -translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-160" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-1">
                            @foreach($this->ageRangeOptions as $ageKey => $ageLabel)
                                <button type="button" class="search-dropdown-item" x-bind:class="{ 'is-active': ageValue === @js($ageKey) }" @click="ageValue = @js($ageKey); openAge = false; $wire.set('age_range', @js($ageKey))">{{ $ageLabel }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <button type="submit" class="search-submit" wire:loading.attr="disabled" wire:loading.class="opacity-80">
                <span wire:loading.remove wire:target="search">{{ __('front.profiles.search.search_button') }}</span>
                <span wire:loading wire:target="search">{{ __('front.profiles.search.searching_button') }}</span>
                <svg wire:loading.remove wire:target="search" class="search-submit-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" stroke-width="2"/>
                    <path d="M16.5 16.5L21 21" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    </form>

    @if($isEnglishLocationSearch)
        {{-- Teleported to <body>: inside the page's z-0 wrapper the fixed
             pickers sat under the 96 px header (z 100), which cut the top
             of the close ring at y 92 (phone 360 px-21…23). --}}
        <template x-teleport="body">
        <div class="search-mobile-picker search-town-mobile-picker" x-show="fullscreenTownSearch" x-cloak x-transition.opacity.duration.180ms>
            <div class="search-country-mobile-picker__inner">
                <div class="search-country-mobile-picker__actions">
                    <button type="button" class="search-country-mobile-picker__close" @click="closeTownPicker()" aria-label="{{ __('front.profiles.search.close_mobile_search') }}">
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
                        <button type="button" class="search-mobile-country-chip__clear" @click="clearCountrySelection()" aria-label="{{ __('front.profiles.search.clear_mobile_search') }}">
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
                        placeholder="{{ __('front.profiles.search.choose_town') }}"
                        autocomplete="off"
                    >
                    <button type="button" class="search-country-mobile-picker__clear" x-show="townQuery.length" x-cloak @click="clearTownQuery()" aria-label="{{ __('front.profiles.search.clear_mobile_search') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M6 6L18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                {{-- Pass 3: a "server-side fallback" list of anchors used to sit
                     here and in the country picker. Inside x-teleport it only
                     ever rendered once Alpine ran, so every town and country
                     was listed twice and the copies ignored the search box. --}}
                <div class="search-country-mobile-picker__list">
                    <template x-for="town in filteredTowns" :key="town">
                        <button type="button" class="search-country-mobile-picker__item" @click="selectTown(town)">
                            <span class="search-country-mobile-picker__flag">
                                <svg class="search-town-pin" viewBox="0 0 22 27" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M21 11c0 7.5-10 15-10 15S1 18.5 1 11a10 10 0 0 1 20 0Z" stroke="#DD3888" stroke-width="2" stroke-linejoin="round"/><circle cx="11" cy="11" r="3.5" stroke="#DD3888" stroke-width="2"/></svg>
                            </span>
                            <span class="search-country-mobile-picker__name" x-text="town"></span>
                            <span class="search-country-mobile-picker__count"></span>
                        </button>
                    </template>


                    <div class="search-country-mobile-picker__empty" x-show="filteredTowns.length === 0" x-cloak>
                        {{ __('front.profiles.search.no_mobile_countries_found') }}
                    </div>
                </div>
            </div>
        </div>
        </template>

        <template x-teleport="body">
        <div class="search-mobile-picker search-country-mobile-picker" x-show="fullscreenCountrySearch" x-cloak x-transition.opacity.duration.180ms>
            <div class="search-country-mobile-picker__inner">
                <div class="search-country-mobile-picker__actions">
                    <button type="button" class="search-country-mobile-picker__close" @click="closeCountryPicker()" aria-label="{{ __('front.profiles.search.close_mobile_search') }}">
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
                        placeholder="{{ __('front.profiles.search.choose_country') }}"
                        autocomplete="off"
                    >
                    <button type="button" class="search-country-mobile-picker__clear" x-show="countryQuery.length" x-cloak @click="clearCountryQuery()" aria-label="{{ __('front.profiles.search.clear_mobile_search') }}">
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


                    <div class="search-country-mobile-picker__empty" x-show="filteredCountries.length === 0" x-cloak>
                        {{ __('front.profiles.search.no_mobile_countries_found') }}
                    </div>
                </div>
            </div>
        </div>
        </template>
    @endif
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
</div>