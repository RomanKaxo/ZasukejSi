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

        <div class="age-gate__card">
            {{-- Křížek v návrhu je vpravo nahoře. Vede pryč ze stránky, ne
                 k tichému zavření — brána, kterou lze odklepnout křížkem,
                 by nebyla brána. --}}
            <a href="{{ $ageGateLeaveUrl }}" class="age-gate__close" rel="noopener nofollow"
               aria-label="{{ __('front.age_gate.leave') }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M1 1L15 15M15 1L1 15" stroke="#FFFFFF" stroke-width="2.4" stroke-linecap="round"/>
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

        .age-gate__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(92, 45, 98, 0.88);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
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

        @media (max-width: 480px) {
            .age-gate__numeral { font-size: 104px; }
            .age-gate__heading { font-size: 30px; }
        }
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
                enter.focus();
            }
        })();
    </script>
@endif
