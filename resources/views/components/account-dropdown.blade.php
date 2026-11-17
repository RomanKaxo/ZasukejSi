<div 
    x-data="{ open: false }" 
    @click.away="open = false" 
    @keydown.escape.window="open = false"
    class="relative"
>
    <!-- User Button -->
    <button type="button" @click.stop="open = !open" :aria-expanded="open.toString()" aria-label="{{ __('front.nav.myaccount') }}" class="w-[60px] h-[60px] bg-[#DD3888] flex items-center justify-center"
        :class="open ? 'rounded-t-[8px] rounded-b-none' : 'rounded-[8px]'">
        {{-- pointer-events:none: without it, Safari can resolve the click's
             target as this <img> rather than the button, which confuses
             Alpine's open/away toggling and made the dropdown flash open
             and immediately close. --}}
        <img src="{{ asset('images/icons/User.svg') }}" class="w-[26px] h-[26px]" alt="User" style="pointer-events: none;">
    </button>

    <!-- Dropdown Menu -->
    <div
        x-show="open"
        x-cloak
        class="absolute mt-0 z-50 flex flex-col items-center"
        {{-- Figma home-12: 270px wide, spanning the bell button's left edge
             to the language button's right edge (140px left of this button). --}}
        style="left: -140px; width: 270px; margin-top: 10px; padding: 20px 0 21px; border-radius: 8px; background-color: #DD3888;"
    >
        {{-- home-12: the open button runs down into the panel as a tab — the
             panel starts 10px under the button and the gap is bridged by a
             60px pink strip with 13x10px concave corners on both sides. --}}
        <span aria-hidden="true" class="absolute" style="top: -10px; left: 127px; width: 13px; height: 10px; background: radial-gradient(13px 10px at 0 0, transparent 98%, #DD3888 100%);"></span>
        <span aria-hidden="true" class="absolute" style="top: -10px; left: 140px; width: 60px; height: 10px; background-color: #DD3888;"></span>
        <span aria-hidden="true" class="absolute" style="top: -10px; left: 200px; width: 13px; height: 10px; background: radial-gradient(13px 10px at 100% 0, transparent 98%, #DD3888 100%);"></span>
        <nav class="w-full px-5">
            <ul>
                @php
                    // Podle pohlaví, ne podle „není muž".
                    //
                    // Účet bez vyplněného pohlaví padal do ženské větve a
                    // dostal odkazy na stránky inzerátu, které pro něj jsou
                    // hlídané — takže menu nabízelo cesty končící
                    // přesměrováním.
                    $accountUser = auth()->user();
                    $accountGender = $accountUser->gender;

                    $links = $accountGender === 'male'
                        ? [
                            ['url' => route('account.member.ratings'), 'label' => __('front.account.member.ratings'), 'icon' => 'BarChart4.svg'],
                            ['url' => route('account.member.favorites'), 'label' => __('front.account.member.favorites'), 'icon' => 'heart.svg'],
                            ['url' => route('account.member.girls-of-month'), 'label' => __('front.account.member.girls_of_month'), 'icon' => 'calendar.svg'],
                            ['url' => route('account.member.archive'), 'label' => __('front.account.member.archive'), 'icon' => 'History.svg'],
                            ['url' => route('account.member.reported'), 'label' => __('front.account.member.reported'), 'icon' => 'TriangleAlert.svg'],
                            ['url' => route('account.member.membership.index'), 'label' => __('front.account.member.membership'), 'icon' => 'BadgeCheck.svg'],
                            ['url' => route('account.member.dashboard'), 'label' => __('front.account.member.settings'), 'icon' => 'Settings.svg'],
                        ]
                        : ($accountGender === 'female'
                            ? [
                                // Její veřejný profil; bez vytvořeného profilu
                                // není kam odkazovat, tak se položka nenabídne.
                                ...($accountUser->profile
                                    ? [['url' => route('profiles.show', $accountUser->profile->id), 'label' => __('front.nav.my_profile'), 'icon' => 'Eye.svg']]
                                    : []),
                                ['url' => route('account.dashboard'), 'label' => __('front.account.sidebar.basic'), 'icon' => 'User.svg'],
                                ['url' => route('account.photos'), 'label' => __('front.account.sidebar.photos'), 'icon' => 'Images.svg'],
                                ['url' => route('account.services'), 'label' => __('front.account.sidebar.services'), 'icon' => 'List.svg'],
                                // Figma home-12 lists Recenze (thumbs-up) before Statistiky.
                                ['url' => route('account.reviews'), 'label' => __('front.account.sidebar.reviews'), 'icon' => 'ThumbsUp.svg'],
                                ['url' => route('account.statistics'), 'label' => __('front.account.sidebar.statistics'), 'icon' => 'BarChart4.svg'],
                            ]
                            : [
                                // Pohlaví nevyplněné: nabídnout jde jedině to,
                                // co platí pro každého.
                                ['url' => route('account.edit'), 'label' => __('front.account.sidebar.basic'), 'icon' => 'User.svg'],
                            ]);
                    $links[] = ['url' => route('notifications.index'), 'label' => __('front.nav.notifications'), 'icon' => 'bell.svg'];
                @endphp

                @foreach($links as $link)
                    <li>
                        {{-- Figma home-12: 230x50 rows with no gap, icon 20px in and
                             10px before 14/500 text; the current page sits on a
                             #5C2D62 pill. --}}
                        @php $isCurrent = url()->current() === $link['url']; @endphp
                        <a href="{{ $link['url'] }}"
                           @if($isCurrent) aria-current="page" @endif
                           class="flex items-center transition-colors duration-200"
                           style="width: 230px; height: 50px; padding: 0 20px; gap: 10px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-variant-ligatures: none; font-size: 14px; font-weight: 500; color: #FFFFFF; text-decoration: none; background-color: {{ $isCurrent ? '#5C2D62' : 'transparent' }};"
                           onmouseover="this.style.backgroundColor='#5C2D62'; this.style.color='#FFFFFF';"
                           onmouseout="this.style.backgroundColor='{{ $isCurrent ? '#5C2D62' : 'transparent' }}'; this.style.color='#FFFFFF';">
                            <img src="{{ asset('images/icons/' . $link['icon']) }}" class="w-[20px] h-[20px]" alt="" aria-hidden="true" style="filter: brightness(0) invert(1);">
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
                <li>
                    <hr class="rounded-none" style="width:230px;border:0;border-top:1px solid #E6E6E6;border-radius:0;margin:12px 0 18px;">
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                           class="flex items-center transition-colors duration-200"
                           style="width: 230px; height: 50px; padding: 0 20px; gap: 10px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-variant-ligatures: none; font-size: 14px; font-weight: 500; color: #FFFFFF; text-decoration: none; background-color: transparent;"
                           onmouseover="this.style.backgroundColor='#5C2D62'; this.style.color='#FFFFFF';"
                           onmouseout="this.style.backgroundColor='transparent'; this.style.color='#FFFFFF';">
                            <img src="{{ asset('images/icons/LogOut.svg') }}" class="w-[20px] h-[20px]" alt="" aria-hidden="true" style="filter: brightness(0) invert(1);">
                            {{ __('front.nav.logout') }}
                        </button>
                    </form>
                </li>
            </ul>
        </nav>
    </div>
</div>
