<nav class="fixed top-0 left-0 right-0 z-100 bg-transparent rounded-b-3xl transition-all duration-300" id="navbar"
     x-data="{ mobileMenuOpen: false, scrolled: false }"
     x-init="scrolled = window.scrollY > 8; window.addEventListener('scroll', () => scrolled = window.scrollY > 8, { passive: true })"
     :class="scrolled ? 'is-scrolled' : ''"
     @click.outside="mobileMenuOpen = false">
    <style>
        body.modal-open #navbar { display: none !important; }

        #navbar {
            width: 100%;
            max-width: 100vw;
            overflow-x: clip;
        }

        /* Odrolovaná lišta stojí nad obsahem a bez podkladu se s ním slévala.
           Lehce zašedlé sklo, ne plná barva — pod ním má být obsah znát.

           Podklad nese vnitřní obal, ne celá lišta: končí tam, kde končí
           obsah, ne u okraje okna. */
        #navbar.is-scrolled .navbar-shell {
            background: rgba(245, 243, 246, 0.86);
            backdrop-filter: saturate(140%) blur(10px);
            -webkit-backdrop-filter: saturate(140%) blur(10px);
            border-bottom-left-radius: 16px;
            border-bottom-right-radius: 16px;
            box-shadow: 0 6px 20px 0 rgba(92, 45, 98, 0.07);
        }

        /* Na mobilu je lišta bílá přes celou šířku vždy, takže by zaoblení
           a průhlednost jen zašpinily barvu. */
        @media (max-width: 1023px) {
            #navbar.is-scrolled .navbar-shell {
                background: #FFFFFF;
                backdrop-filter: none;
                -webkit-backdrop-filter: none;
                border-radius: 0;
                box-shadow: 0 1px 6px 0 rgba(92, 45, 98, 0.08);
            }
        }

        .navbar-shell {
            /* Obsah zůstává v 1140 px jako hlavička i patička; navíc jsou
               postranní odsazení, aby odrolovaný šedý podklad nezačínal
               přesně u loga, ale kousek před ním. Proto je pole širší
               přesně o ta odsazení. */
            --navbar-gutter: 24px;
            width: calc(1140px + 2 * var(--navbar-gutter));
            max-width: calc(100% - 32px);
            padding-left: var(--navbar-gutter);
            padding-right: var(--navbar-gutter);
            height: 80px;
            margin: 0 auto;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-desktop-grid {
            width: 100%;
            height: 100%;
            grid-template-columns: auto 440px 1fr;
            align-items: center;
            column-gap: 12px;
        }

        .brand-mark {
            font-family: 'Bungee', cursive;
            font-weight: 400;
            font-size: 24px;
            line-height: 1;
            white-space: nowrap;
        }

        #nav-logo {
            padding-right: 97px;
        }

        #nav-logo.nav-logo-en {
            padding-right: 8px;
        }

        .brand-mark .brand-main { color: #5C2D62; }
        .brand-mark .brand-si { color: #DD3888; }
        .brand-mark .brand-cz { color: rgba(50, 50, 50, 0.78); }

        .navbar-links {
            width: 440px;
            height: 100%;
            align-self: stretch;
            display: flex;
            align-items: stretch;
            justify-content: flex-start;
            gap: 20px;
            margin-left: 18px;
        }

        .navbar-links .nav-link {
            display: flex;
            align-items: center;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 16px;
            line-height: 1;
            color: #323232;
            text-decoration: none;
            height: 80px;
            padding: 0 10px;
            white-space: nowrap;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            transition: color 140ms ease, background-color 140ms ease;
        }

        .navbar-links .nav-link.active,
        .navbar-links .nav-link:hover {
            color: #DD3888;
            background: #FFFFFF;
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
        }

        @media (max-width: 1023px) {
            #navbar {
                background: #FFFFFF !important;
            }

            .navbar-shell {
                width: 100%;
                max-width: 100%;
                height: 56px;
                padding-left: 25px;
                padding-right: 25px;
                position: relative;
                z-index: 1;
                background: #FFFFFF;
            }

            #nav-logo {
                padding-right: 0;
            }

            #nav-logo-mobile {
                display: inline-flex;
                align-items: center;
                margin-left: 0;
                min-width: 0;
                max-width: calc(100% - 44px);
            }

            #nav-logo-mobile .brand-mark {
                font-size: 24px !important;
            }

            #mobile-menu-button {
                flex: 0 0 auto;
                width: 32px;
                min-width: 32px;
            }
        }
    </style>
    <div class="lg:hidden fixed inset-0"
         x-show="mobileMenuOpen"
         x-cloak
         x-transition.opacity.duration.180ms
         @click="mobileMenuOpen = false"
         style="background:#5C2D62CC;backdrop-filter:blur(9px);-webkit-backdrop-filter:blur(9px);z-index:0;"
    ></div>
    <div class="container mx-auto px-0 sm:px-4 relative" style="z-index:10;">
        <div class="navbar-shell">
            <!-- Left Side: Logo + Navigation Links -->
            <div class="navbar-desktop-grid hidden lg:grid">
                <!-- Logo -->
                <a href="{{ route('profiles.index') }}" class="text-xl font-bold text-text-default hover:text-primary-600 transition-colors justify-self-start{{ app()->getLocale() === 'en' ? ' nav-logo-en' : '' }}" id="nav-logo">
                    <span class="brand-mark">
                        @if(app()->getLocale() === 'en')
                            <span class="brand-main">ESCORT</span><span class="brand-si">-ONLINE</span><span class="brand-cz">.COM</span>
                        @else
                            <span class="brand-main">ZAŠUKEJ</span><span class="brand-si">SI</span><span class="brand-cz">.CZ</span>
                        @endif
                    </span>
                </a>

                <!-- Navigation Links - Desktop -->
                <div class="navbar-links justify-self-center">
                    @php
                        $navTranslationKeys = [
                            'home' => 'front.nav.home',
                            'countries' => 'front.nav.countries',
                            'vip' => 'front.nav.vip',
                            'vip-premium' => 'front.nav.vip',
                            'faq' => 'front.nav.faq',
                            'ethics' => 'front.nav.ethics',
                            'etika' => 'front.nav.ethics',
                            'contact' => 'front.nav.contact',
                            'kontakt' => 'front.nav.contact',
                        ];

                        $resolvedNavPages = collect($navPages ?? [])->values();
                        if ($resolvedNavPages->isEmpty()) {
                            $resolvedNavPages = collect([
                                (object) ['id' => 'home', 'slug' => '', 'title' => __('front.nav.home')],
                                (object) ['id' => 'vip', 'slug' => 'vip-premium', 'title' => __('front.nav.vip')],
                                (object) ['id' => 'faq', 'slug' => 'faq', 'title' => __('front.nav.faq')],
                                (object) ['id' => 'ethics', 'slug' => 'etika', 'title' => __('front.nav.ethics')],
                                (object) ['id' => 'contact', 'slug' => 'kontakt', 'title' => __('front.nav.contact')],
                            ]);
                        } else {
                            $resolvedNavPages = $resolvedNavPages->map(function ($page) use ($navTranslationKeys) {
                                $pageId = (string) data_get($page, 'id', '');
                                $pageSlug = trim((string) data_get($page, 'slug', ''), '/');
                                $translationKey = $navTranslationKeys[$pageId] ?? $navTranslationKeys[$pageSlug] ?? null;

                                return (object) [
                                    'id' => $pageId !== '' ? $pageId : ($pageSlug !== '' ? \Illuminate\Support\Str::slug($pageSlug) : 'page'),
                                    'slug' => $pageSlug,
                                    'title' => $translationKey ? __($translationKey) : data_get($page, 'title'),
                                ];
                            })->values();

                            // "Úvod" (home) is a static link, not managed via the CMS pages table —
                            // always show it first unless it somehow already made it into the list.
                            if (! $resolvedNavPages->contains(fn ($page) => trim($page->slug, '/') === '')) {
                                $resolvedNavPages = collect([
                                    (object) ['id' => 'home', 'slug' => '', 'title' => __('front.nav.home')],
                                ])->concat($resolvedNavPages);
                            }
                        }
                    @endphp
                    @foreach($resolvedNavPages as $page)
                        @php
                            $normalizedSlug = trim($page->slug, '/');
                            $isHomeSlug = $normalizedSlug === '';
                            $isActive = $isHomeSlug ? request()->path() === '/' : request()->is($normalizedSlug);
                        @endphp
                        <a href="{{ url('/' . $page->slug) }}" class="nav-link {{ $isActive ? 'active' : '' }}" id="nav-link-{{ $page->id }}">
                            {{ $page->title }}
                        </a>
                    @endforeach
                </div>

                <!-- Right Side: Register, Login, Language Switcher -->
                <div class="navbar-actions justify-self-end flex items-center space-x-3">
                    
                    @auth
                    <!-- Custom Icon Buttons - Auth Only -->
                    <div class="flex items-center space-x-3">
                        {{-- Notifications: a fully working dropdown (unread count,
                             mark read, archive) that reuses this button's exact
                             geometry. It replaced a static div whose badge read
                             "14" and which had no click handler at all, while
                             App\Livewire\NotificationsDropdown sat unused. --}}
                        <livewire:notifications-dropdown />

                        {{-- Inbox link with the real unread count (was a literal "654"). --}}
                        <livewire:messages-badge />

                        <!-- User Button -->
                        <x-account-dropdown />
                    </div>
                    @else
                    <!-- Auth Buttons - Guest Only -->
                    <div class="flex items-center space-x-3">
                        <button @click="$dispatch('show-register-modal')" type="button" class="px-6 py-3 bg-[#DD3888] text-white rounded-lg font-semibold hover:opacity-90 transition{{ app()->getLocale() === 'en' ? ' flex items-center justify-center' : '' }}" @if(app()->getLocale() === 'en') style="width:133px;height:60px;padding:0;" @endif>
                            {{ __('front.nav.register') }}
                        </button>
                        <button @click="$dispatch('show-login-modal')" type="button" class="px-6 py-3 border-2 border-[#DD3888] bg-white text-[#DD3888] rounded-lg font-semibold hover:bg-[#DD3888] hover:text-white transition{{ app()->getLocale() === 'en' ? ' flex items-center justify-center' : '' }}" @if(app()->getLocale() === 'en') style="width:86px;height:60px;padding:0;" @endif>
                            {{ __('front.nav.login') }}
                        </button>
                    </div>
                    @endauth

                    <!-- Language Switcher - Desktop Only -->
                    <div class="hidden lg:inline" x-data="{ langOpen: false }" @click.outside="langOpen = false">
                        <div class="language-dropdown relative">
                            <button class="language-dropdown-toggle flex items-center" id="nav-language" @click.stop="langOpen = !langOpen" type="button">
                                {{-- Current language; the flag comes from
                                     config/locales.php rather than an if-chain
                                     that only knew two languages. --}}
                                <img src="{{ asset(\App\Support\Locales::flag(app()->getLocale())) }}"
                                     alt="{{ \App\Support\Locales::nativeName(app()->getLocale()) }}"
                                     class="w-6 h-6 rounded">
                            </button>
                            
                            <div class="language-dropdown-menu absolute top-full right-0 bg-white p-2 rounded-lg shadow-lg transition-opacity duration-200 z-50"
                                 x-show="langOpen"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95">
                                @foreach(\App\Support\Locales::all() as $code => $meta)
                                    @continue($code === app()->getLocale())
                                    <a href="{{ url()->current() }}?locale={{ $code }}"
                                       class="language-dropdown-item flex items-center gap-2 {{ ! $loop->last ? 'mb-1' : '' }}"
                                       title="{{ $meta['native'] }}">
                                        <img src="{{ asset($meta['flag']) }}" alt="{{ $meta['native'] }}" class="w-6 h-6">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex w-full items-center justify-between lg:hidden bg-white" style="position:relative;z-index:1;">
                <a href="{{ route('profiles.index') }}" class="text-xl font-bold text-text-default hover:text-primary-600 transition-colors" id="nav-logo-mobile">
                    <span class="brand-mark" style="font-size:20px;">
                        @if(app()->getLocale() === 'en')
                            <span class="brand-main">ESCORT</span><span class="brand-si">-ONLINE</span><span class="brand-cz">.COM</span>
                        @else
                            <span class="brand-main">ZAŠUKEJ</span><span class="brand-si">SI</span><span class="brand-cz">.CZ</span>
                        @endif
                    </span>
                </a>
                <!-- Mobile menu button -->
                <div class="lg:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="flex items-center justify-center text-text-default hover:text-primary-600 focus:outline-none focus:text-primary-600" id="mobile-menu-button" :aria-expanded="mobileMenuOpen.toString()" aria-controls="mobile-menu">
                        <span x-show="!mobileMenuOpen" class="inline-flex flex-col justify-between" style="width:40px;height:17px;">
                            <svg width="40" height="17" viewBox="0 0 40 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0.0678711 1.5H39.9997" stroke="#5C2D62" stroke-width="3" stroke-linecap="round"/>
                                <path d="M0 15.5L40 15.5" stroke="#5C2D62" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span x-show="mobileMenuOpen" style="display:none;width:28px;height:28px;">
                            <svg width="28" height="28" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1.06055 29.3449L29.3448 1.06066" stroke="#DD3888" stroke-width="3" stroke-linecap="round"/>
                                <path d="M29.3821 29.3449L1.09781 1.06066" stroke="#DD3888" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="lg:hidden" id="mobile-menu" x-show="mobileMenuOpen" x-cloak x-transition.opacity.duration.180ms>
            <div class="flex flex-col items-center p-4 py-5 pt-6 bg-white" style="border-radius:0 0 24px 24px;">

                @php
                    // Real unread count, matching the desktop badge. This was a
                    // literal 654 for every account.
                    $mobileMailCount = auth()->check()
                        ? \App\Models\Message::where('to_user_id', auth()->id())->whereNull('read_at')->count()
                        : 0;
                    $mobileMailBadge = $mobileMailCount > 99 ? '99+' : $mobileMailCount;

                    // The bell only exists in the desktop header, so on mobile
                    // notifications had no entry point at all. Same unread rule
                    // as NotificationsDropdown: personal ones count while
                    // read_at is null, global ones until this user has a read
                    // state of their own.
                    $mobileNotificationCount = auth()->check()
                        ? \App\Models\Notification::activeForUser(auth()->id())
                            ->where(function ($query) {
                                $query->where(fn ($q) => $q->where('is_global', false)->whereNull('read_at'))
                                    ->orWhere(fn ($q) => $q->where('is_global', true)
                                        ->whereDoesntHave('userStates', fn ($inner) => $inner
                                            ->where('user_id', auth()->id())
                                            ->whereNotNull('read_at')));
                            })
                            ->count()
                        : 0;
                    $mobileNotificationBadge = $mobileNotificationCount > 99 ? '99+' : $mobileNotificationCount;
                @endphp

                @foreach($resolvedNavPages as $page)
                    @php
                        $mobileNormalizedSlug = trim($page->slug, '/');
                        $mobileIsHomeSlug = $mobileNormalizedSlug === '';
                        $mobileIsActive = $mobileIsHomeSlug ? request()->path() === '/' : request()->is($mobileNormalizedSlug);
                    @endphp
                    {{-- Phone frame: 310px wide rows 2px apart, which puts them
                         in the same 25px gutter as the buttons below. Was 304px
                         with mb-2, so the rows sat 3px in and 6px too far apart. --}}
                    <a href="{{ url('/' . $page->slug) }}"
                       class="flex items-center w-full"
                       style="width:310px;max-width:100%;height:60px;margin-bottom:2px;padding:0 16px;border-radius:8px;font-family:'Poppins',sans-serif;font-weight:500;font-size:18px;
                           background:{{ $mobileIsActive ? '#F2F2F2' : 'transparent' }};
                           color:{{ $mobileIsActive ? '#DD3888' : '#505050' }};">
                        {{ $page->title }}
                    </a>
                @endforeach

                <hr class="rounded-none" style="width:310px;max-width:100%;height:2px;border:none;background:#F2F2F2;border-radius:0;padding-top:27px;padding-bottom:17px;background-clip:content-box;">

                @auth
                    @php
                        // Male members get the "account.member.*" routes (ratings, favorites,
                        // girls of month, archive, reported, settings); female profile owners
                        // get the "account.*" routes (dashboard, photos, services, statistics).
                        // Admins fall through to the female/profile set, mirroring the redirect
                        // logic in routes/web.php.
                        $isMobileMemberAccount = auth()->user()->isMale() && !auth()->user()->hasRole('admin');

                        // Pořadí podle rámce `menu logged-in muž`: Můj profil,
                        // Moje zprávy, Moje favoritky, Základní nastavení.
                        //
                        // Návrh za nimi končí. Zbylé sekce účtu ale nechávám
                        // pod nimi, protože na mobilu k nim jiná cesta nevede —
                        // doslovné převzetí čtyř položek by Archiv dívek,
                        // Dívky měsíce a Nahlášené dívky odřízlo úplně.
                        $mobileTabs = $isMobileMemberAccount ? [
                            ['route' => 'account.member.ratings', 'label' => __('front.nav.my_profile'), 'icon' => 'User'],
                            ['route' => 'messages.index', 'label' => __('front.account.member.messages'), 'icon' => 'mail', 'badge' => $mobileMailBadge],
                            ['route' => 'account.member.favorites', 'label' => __('front.account.member.favorites'), 'icon' => 'heart'],
                            ['route' => 'account.member.dashboard', 'label' => __('front.account.member.settings'), 'icon' => 'Settings'],
                            // Za návrhem:
                            ['route' => 'notifications.archived', 'label' => __('front.nav.notifications'), 'icon' => 'bell', 'badge' => $mobileNotificationBadge],
                            ['route' => 'account.member.girls-of-month', 'label' => __('front.account.member.girls_of_month'), 'icon' => 'CalendarDays'],
                            ['route' => 'account.member.archive', 'label' => __('front.account.member.archive'), 'icon' => 'History'],
                            ['route' => 'account.member.reported', 'label' => __('front.account.member.reported'), 'icon' => 'OctagonAlert'],
                        ] : [
                            ['route' => 'account.dashboard', 'label' => __('front.nav.my_profile'), 'icon' => 'User'],
                            ['route' => 'messages.index', 'label' => __('front.account.member.messages'), 'icon' => 'mail', 'badge' => $mobileMailBadge],
                            ['route' => 'account.edit', 'label' => __('front.account.member.settings'), 'icon' => 'Settings'],
                            // Za návrhem:
                            ['route' => 'account.photos', 'label' => __('front.account.sidebar.photos'), 'icon' => 'Images'],
                            ['route' => 'account.services', 'label' => __('front.account.sidebar.services'), 'icon' => 'List'],
                            ['route' => 'account.statistics', 'label' => __('front.account.sidebar.statistics'), 'icon' => 'BarChart4'],
                            ['route' => 'notifications.archived', 'label' => __('front.nav.notifications'), 'icon' => 'bell', 'badge' => $mobileNotificationBadge],
                        ];
                    @endphp
                    @foreach($mobileTabs as $tab)
                        @php $mobileTabActive = request()->routeIs($tab['route']); @endphp
                        <a href="{{ route($tab['route']) }}"
                           class="relative flex items-center w-full"
                           style="width:304px;max-width:100%;height:60px;margin-bottom:10px;padding:0 16px;gap:12px;border-radius:8px;font-family:'Poppins',sans-serif;font-weight:500;font-size:18px;
                               background:{{ $mobileTabActive ? '#DD3888' : 'transparent' }};
                               border:1px solid {{ $mobileTabActive ? '#DD3888' : '#E6E6E6' }};
                               color:{{ $mobileTabActive ? '#FFFFFF' : '#505050' }};">
                            <x-icons :name="$tab['icon']" class="w-6 h-6" style="color:{{ $mobileTabActive ? '#FFFFFF' : '#DD3888' }};" />
                            {{ $tab['label'] }}
                            {{-- Only render the badge when there is something to
                                 report; a green "0" is noise, not information. --}}
                            @if(!empty($tab['badge']))
                                <span class="absolute flex items-center justify-center" style="right:16px;top:50%;transform:translateY(-50%);width:30px;height:30px;border-radius:999px;background:#00B80F;font-family:'Poppins',sans-serif;font-weight:700;font-size:11px;color:#FFFFFF;">
                                    {{ $tab['badge'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach

                    <form method="POST" action="{{ route('logout') }}" class="w-full flex justify-center">
                        @csrf
                        <button type="submit" class="flex items-center justify-center" style="width:311px;max-width:100%;height:60px;border-radius:8px;border:1px solid #DD3888;gap:7px;background:transparent;">
                            <x-icons name="User" style="width:24px;height:24px;color:#DD3888;" />
                            <span style="font-family:'Poppins', sans-serif; font-weight:600; font-size:18px; line-height:1; color:#DD3888;">{{ __('front.nav.logout_mobile') }}</span>
                        </button>
                    </form>
                @else
                    <!-- Auth Buttons -->
                    <button @click="$dispatch('show-register-modal')" type="button" class="flex items-center justify-center mb-3" style="width:311px;max-width:100%;height:60px;border-radius:8px;background:#DD3888;font-family:'Poppins',sans-serif;font-weight:600;font-size:18px;color:#FFFFFF;">
                        {{ __('front.nav.register') }}
                    </button>
                    <button @click="$dispatch('show-login-modal')" type="button" class="flex items-center justify-center" style="width:311px;max-width:100%;height:60px;border-radius:8px;border:1px solid #DD3888;background:transparent;gap:7px;font-family:'Poppins',sans-serif;font-weight:600;font-size:18px;color:#DD3888;">
                        <x-icons name="User" class="w-6 h-6" style="color:#DD3888;" />
                        {{ __('front.nav.login_mobile') }}
                    </button>
                @endauth

                {{-- Language switcher — the design shows three (Česky, English,
                     Русский). Driven by config/locales.php, so a new language
                     appears here without touching this markup. --}}
                <div class="w-full flex justify-center gap-3 mt-4">
                    @foreach(\App\Support\Locales::all() as $code => $meta)
                        <a href="{{ url()->current() }}?locale={{ $code }}" class="flex flex-col items-center justify-center gap-1" style="width:96px;height:94px;border-radius:8px;background:{{ app()->getLocale() === $code ? '#F2F2F2' : '#FFFFFF' }};">
                            <img src="{{ asset($meta['flag']) }}" alt="{{ $meta['native'] }}" style="width:35px;height:35px;border-radius:999px;object-fit:cover;">
                            <span style="font-family:'Poppins',sans-serif;font-weight:400;font-size:13px;color:#505050;">{{ $meta['native'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    // Listen for global modal visibility events and toggle body.modal-open accordingly.
    window.addEventListener('modal-visibility-changed', function(e) {
        try {
            const open = e && e.detail && e.detail.open;
            if (open) {
                document.body.classList.add('modal-open');
            } else {
                // Delay briefly and check if any modal-container is still visible
                setTimeout(function() {
                    const modals = Array.from(document.querySelectorAll('.modal-container'));
                    const anyVisible = modals.some(m => window.getComputedStyle(m).display !== 'none' && m.getBoundingClientRect().height > 0);
                    if (!anyVisible) {
                        document.body.classList.remove('modal-open');
                    }
                }, 10);
            }
        } catch (err) {
            console.error('modal-visibility-changed handler error', err);
        }
    });
</script>