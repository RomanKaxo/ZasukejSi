{{--
    Provider account sidebar.

    The previous version branched on `request()->routeIs('preview.*')` and linked
    to a set of "preview" named routes. No such routes exist anywhere in the
    application, so that branch was unreachable dead code which would have thrown
    RouteNotFoundException the moment such a route was ever added.
--}}
<div class="flex flex-col w-[211px] gap-[10px]">
    <div class="flex flex-col w-[211px] h-[290px] gap-[10px]">
        @php
            $dashboardItems = [
                ['route' => 'account.dashboard',  'icon' => 'User.svg',       'label' => __('front.account.sidebar.basic')],
                ['route' => 'account.photos',     'icon' => 'Images.svg',     'label' => __('front.account.sidebar.photos')],
                ['route' => 'account.services',   'icon' => 'List.svg',       'label' => __('front.account.sidebar.services')],
                ['route' => 'account.statistics', 'icon' => 'BarChart4.svg',  'label' => __('front.account.sidebar.statistics')],
            ];
        @endphp

        @foreach($dashboardItems as $item)
            @php $isActive = request()->routeIs($item['route']); @endphp
            <a href="{{ route($item['route']) }}"
               class="w-[210px] h-[50px] rounded-[8px] {{ $isActive ? 'bg-[#DD3888] text-white' : 'border border-[#E6E6E6] text-[#505050]' }} flex items-center px-4 gap-3 font-medium text-[14px]"
               style="font-family: 'Poppins', sans-serif;">
                <img src="{{ asset('images/icons/' . $item['icon']) }}" class="w-[20px] h-[20px]" alt=""
                     style="{{ $isActive ? 'filter: brightness(0) invert(1);' : 'filter: invert(36%) sepia(87%) saturate(2222%) hue-rotate(309deg) brightness(90%) contrast(92%);' }}">
                {{ $item['label'] }}
            </a>
        @endforeach

        <div class="w-[210px] h-[50px] rounded-[8px] border border-[#E6E6E6] flex items-center px-4 gap-3 font-medium text-[14px] text-[#A4A4A4]" style="font-family: 'Poppins', sans-serif;">
            <img src="{{ asset('images/icons/ThumbsUp.svg') }}" class="w-[20px] h-[20px]" alt="" style="filter: invert(75%) sepia(0%) saturate(0%) hue-rotate(186deg) brightness(91%) contrast(85%);">
            {{ __('front.account.sidebar.reviews') }}
        </div>
    </div>
    <div class="block w-[210px] h-[464px] mt-4">
        <img src="{{ asset('images/dvert2.png') }}" class="w-full h-full object-cover rounded-[8px]" alt="Advertisement">
    </div>
</div>
