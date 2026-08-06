<div 
    x-data="{ open: false }" 
    @click.away="open = false" 
    class="relative"
>
    <!-- User Button -->
    <button @click="open = !open" class="w-[60px] h-[60px] bg-[#DD3888] flex items-center justify-center"
        :class="open ? 'rounded-t-[8px] rounded-b-none' : 'rounded-[8px]'">
        <img src="{{ asset('images/icons/User.svg') }}" class="w-[26px] h-[26px]" alt="User">
    </button>

    <!-- Dropdown Menu -->
    <div
        x-show="open"
        x-cloak
        class="absolute left-1/2 -translate-x-1/2 mt-0 shadow-lg z-50 flex flex-col items-center pt-6 pb-3"
        style="width: 270px; border-radius: 8px; background-color: #DD3888;"
    >
        <nav class="w-full px-5">
            <ul class="space-y-2">
                @php
                    $links = auth()->user()->gender === 'male'
                        ? [
                            ['url' => route('account.member.ratings'), 'label' => __('front.account.member.ratings'), 'icon' => 'BarChart4.svg'],
                            ['url' => route('account.member.favorites'), 'label' => __('front.account.member.favorites'), 'icon' => 'heart.svg'],
                            ['url' => route('account.member.girls-of-month'), 'label' => __('front.account.member.girls_of_month'), 'icon' => 'calendar.svg'],
                            ['url' => route('account.member.archive'), 'label' => __('front.account.member.archive'), 'icon' => 'History.svg'],
                            ['url' => route('account.member.reported'), 'label' => __('front.account.member.reported'), 'icon' => 'TriangleAlert.svg'],
                            ['url' => route('account.member.dashboard'), 'label' => __('front.account.member.settings'), 'icon' => 'Settings.svg'],
                        ]
                        : [
                            ['url' => route('account.dashboard'), 'label' => __('front.account.sidebar.basic'), 'icon' => 'User.svg'],
                            ['url' => route('account.photos'), 'label' => __('front.account.sidebar.photos'), 'icon' => 'Images.svg'],
                            ['url' => route('account.services'), 'label' => __('front.account.sidebar.services'), 'icon' => 'List.svg'],
                            ['url' => route('account.statistics'), 'label' => __('front.account.sidebar.statistics'), 'icon' => 'BarChart4.svg'],
                        ];
                @endphp

                @foreach($links as $link)
                    <li>
                        <a href="{{ $link['url'] }}"
                           class="flex items-center px-4 gap-3 transition-colors duration-200"
                           style="width: 230px; height: 50px; border-radius: 8px; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 600; color: #FFFFFF; text-decoration: none; background-color: transparent;"
                           onmouseover="this.style.backgroundColor='#5C2D62'; this.style.color='#FFFFFF';"
                           onmouseout="this.style.backgroundColor='transparent'; this.style.color='#FFFFFF';">
                            <img src="{{ asset('images/icons/' . $link['icon']) }}" class="w-[20px] h-[20px]" alt="{{ $link['label'] }}" style="filter: brightness(0) invert(1);">
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
                <li>
                    <hr class="rounded-none" style="width:230px;border-color:#E6E6E6;border-radius:0;padding-top:30px;padding-bottom:6px;">
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                           class="flex items-center px-4 gap-3 transition-colors duration-200"
                           style="width: 230px; height: 50px; border-radius: 8px; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 600; color: #FFFFFF; text-decoration: none; background-color: transparent;"
                           onmouseover="this.style.backgroundColor='#5C2D62'; this.style.color='#FFFFFF';"
                           onmouseout="this.style.backgroundColor='transparent'; this.style.color='#FFFFFF';">
                            <img src="{{ asset('images/icons/User.svg') }}" class="w-[20px] h-[20px]" alt="{{ __('front.nav.logout') }}" style="filter: brightness(0) invert(1);">
                            {{ __('front.nav.logout') }}
                        </button>
                    </form>
                </li>
            </ul>
        </nav>
    </div>
</div>
