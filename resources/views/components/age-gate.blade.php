@php
    // Věková brána podle rámce `phone 360 px-10`.
    //
    // Vypínatelná v administraci (Obsah → Věková brána). Když je vypnutá,
    // nevykreslí se vůbec — ne skrytá CSSkem, ale vynechaná ze stránky.
    $ageGateOn = \App\Models\Setting::getBool('age_gate_enabled', true);
@endphp

@if($ageGateOn)
    @php
        // Verze obsahu: když provozovatel text změní, musí souhlas proběhnout
        // znovu. Bez toho by návštěvník, který odsouhlasil starou verzi,
        // novou už nikdy neviděl.
        $ageGateVersion = substr(sha1(__('front.age_gate.body') . __('front.age_gate.agreement')), 0, 12);
        $ageGateLeaveUrl = \App\Models\Setting::get('age_gate_leave_url', 'https://www.google.com');
    @endphp

    <div id="age-gate" data-version="{{ $ageGateVersion }}" hidden
         role="dialog" aria-modal="true" aria-labelledby="age-gate-heading">
        <div class="age-gate__backdrop"></div>

        <div class="age-gate__card" tabindex="-1">
            {{-- Křížek v návrhu je vpravo nahoře. Vede pryč ze stránky, ne
                 k tichému zavření — brána, kterou lze odklepnout křížkem,
                 by nebyla brána. --}}
            <a href="{{ $ageGateLeaveUrl }}" class="age-gate__close" rel="noopener nofollow"
               aria-label="{{ __('front.age_gate.leave') }}">
                <svg width="11" height="11" viewBox="0 0 11 11" fill="none" aria-hidden="true">
                    <path d="M1 1L10 10M10 1L1 10" stroke="#FFFFFF" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </a>

            <div class="age-gate__scroll">
                <p class="age-gate__numeral" aria-hidden="true">{{ __('front.age_gate.numeral') }}</p>

                <h2 class="age-gate__heading" id="age-gate-heading">{{ __('front.age_gate.heading') }}</h2>

                <p class="age-gate__body">
                    <strong>{{ __('front.age_gate.brand') }}</strong>{{ __('front.age_gate.body') }}
                </p>

                <p class="age-gate__agreement">{{ __('front.age_gate.agreement') }}</p>
            </div>

            <div class="age-gate__actions">
                <button type="button" class="age-gate__enter" data-age-gate-enter>
                    {{ __('front.age_gate.enter') }}
                </button>
                <a href="{{ $ageGateLeaveUrl }}" class="age-gate__leave" rel="noopener nofollow">
                    {{ __('front.age_gate.leave') }}
                </a>
            </div>
        </div>
    </div>

    <style>
        #age-gate {
            position: fixed;
            inset: 0;
            z-index: 2147483000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 16px;
        }
        #age-gate[hidden] { display: none; }

        /* Figma phone 360 px-10: #5C2D62CC like the other modals (was 88 %). */
        .age-gate__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(92, 45, 98, 0.8);
            /* Same veil as the auth modals (pass 4 fit): 4 px on phones
               (phone 360 px-10), 16 px on desktop (home-22/23). */
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        @media (max-width: 767px) {
            .age-gate__backdrop {
                backdrop-filter: blur(4px);
                -webkit-backdrop-filter: blur(4px);
            }
        }

        .age-gate__card {
            position: relative;
            width: 100%;
            max-width: 560px;
            max-height: calc(100vh - 40px);
            display: flex;
            flex-direction: column;
            background: #FFFFFF;
            border-radius: 20px;
            padding: 28px 24px 24px;
        }

        .age-gate__close {
            position: absolute;
            top: 18px;
            right: 18px;
            width: 36px;
            height: 36px;
            border-radius: 999px;
            background: #DD3888;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        .age-gate__close:hover { background: #CA2474; }

        .age-gate__scroll {
            overflow-y: auto;
            /* Text je dlouhý; na malém displeji roluje karta, ne stránka. */
            -webkit-overflow-scrolling: touch;
        }

        .age-gate__numeral {
            margin: 12px 0 0;
            text-align: center;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 128px;
            line-height: 1;
            color: #C9AECC;
            letter-spacing: -0.02em;
        }

        .age-gate__heading {
            margin: 4px 0 20px;
            text-align: center;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 34px;
            line-height: 1.2;
            color: #5C2D62;
        }

        .age-gate__body {
            margin: 0 0 18px;
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            font-size: 15px;
            line-height: 1.62;
            color: #505050;
        }
        .age-gate__body strong { font-weight: 700; color: #241E28; }

        .age-gate__agreement {
            margin: 0 0 22px;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 15px;
            line-height: 1.62;
            color: #5C2D62;
        }

        .age-gate__actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding-top: 4px;
        }

        .age-gate__enter,
        .age-gate__leave {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 56px;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 18px;
            text-decoration: none;
            cursor: pointer;
        }
        .age-gate__enter {
            background: #DD3888;
            color: #FFFFFF;
            border: 1px solid #DD3888;
        }
        .age-gate__enter:hover { background: #CA2474; border-color: #CA2474; }
        .age-gate__leave {
            background: transparent;
            color: #DD3888;
            border: 1px solid #DD3888;
        }
        .age-gate__leave:hover { background: #FBEAF2; }

        /* Dokud brána stojí, stránka pod ní neroluje. */
        html.age-gate-open, body.age-gate-open { overflow: hidden; }

        /* Figma `phone 360 px-10`: card 320 wide with 30px sides, close 35px
           20px from the corner, a 204px "18" fading to white, 28px heading,
           11/18.5 body copy, 12/20 bold agreement, two 260x50 buttons 10px apart. */
        @media (max-width: 480px) {
            #age-gate { padding: 0 20px; }
            .age-gate__card { max-width: 320px; max-height: calc(100vh - 20px); border-radius: 24px; padding: 0 30px 51px; }
            .age-gate__close { top: 19px; right: 20px; width: 35px; height: 35px; }
            .age-gate__numeral {
                margin: 54px 0 0; font-size: 210px; line-height: 180px; height: 155px; overflow: hidden;
                letter-spacing: 0;
                background: linear-gradient(180deg, #B39FB8 22%, #FFFFFF 62%);
                -webkit-background-clip: text; background-clip: text; color: transparent;
            }
            .age-gate__heading { margin: 0 0 17px; font-size: 28px; line-height: 42px; }
            .age-gate__body { margin: 0 0 15px; font-size: 11px; line-height: 18.5px; }
            .age-gate__agreement { margin: 0 0 24px; font-size: 12px; line-height: 20px; }
            .age-gate__actions { gap: 10px; padding-top: 0; }
            .age-gate__enter, .age-gate__leave { height: 50px; font-size: 16px; }
            /* pass 1 overlay (phone 360 px-10): card 921 tall (88..1009), close
               20px down, heading 214px and body 271px into the card, body on an
               18px grid. */
            .age-gate__close { top: 20px; }
            .age-gate__numeral {
                margin-top: 64px; height: 143px;
                background: linear-gradient(180deg, #B49DB7 8%, #BEACC2 31%, #CFC3D1 53%, #E9E2EA 75.5%, #FFFFFF 92%);
                -webkit-background-clip: text; background-clip: text;
            }
            .age-gate__card { transform: translateY(5px); }
            .age-gate__body { line-height: 18px; margin-bottom: 15px; }
            .age-gate__agreement { margin-bottom: 22.5px; }
            .age-gate__card { padding-bottom: 50.5px; }
            /* pass 2 element measurement: close 1px higher, body 1px lower,
               buttons 1px higher, card height unchanged. */
            .age-gate__close { top: 19px; }
            .age-gate__heading { margin-bottom: 18px; }
        }
        /* Focus moves into the dialog without painting a ring on "Vstoupit"
           (the frame shows none); keyboard users still get it on Tab. */
        .age-gate__card:focus { outline: none; }
    </style>

    <script>
        (function () {
            var gate = document.getElementById('age-gate');
            if (!gate) { return; }

            var KEY = 'age_gate_ack';
            var version = gate.getAttribute('data-version');

            function acknowledged() {
                try {
                    return window.localStorage.getItem(KEY) === version;
                } catch (e) {
                    // Privátní režim může localStorage zakázat. Pak se brána
                    // ukáže pokaždé — což je bezpečnější než ji přeskočit.
                    return false;
                }
            }

            if (acknowledged()) {
                gate.parentNode.removeChild(gate);
                return;
            }

            gate.hidden = false;
            document.documentElement.classList.add('age-gate-open');
            document.body.classList.add('age-gate-open');

            var enter = gate.querySelector('[data-age-gate-enter]');
            if (enter) {
                enter.addEventListener('click', function () {
                    try { window.localStorage.setItem(KEY, version); } catch (e) {}
                    document.documentElement.classList.remove('age-gate-open');
                    document.body.classList.remove('age-gate-open');
                    gate.parentNode.removeChild(gate);
                });
                var card = gate.querySelector('.age-gate__card');
                if (card) { card.focus({ preventScroll: true }); }
            }
        })();
    </script>
@endif
