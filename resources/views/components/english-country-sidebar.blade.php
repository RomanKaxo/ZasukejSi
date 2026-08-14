@php
    $selectedRegion = request('region', '');
    $selectedCountry = request('country', '');
    $selectedCountryCode = request('country_code', '');
    $queryFor = function (array $overrides = [], array $remove = []) {
        $query = request()->query();

        foreach ($remove as $key) {
            unset($query[$key]);
        }

        foreach ($overrides as $key => $value) {
            if ($value === null || $value === '') {
                unset($query[$key]);
                continue;
            }

            $query[$key] = $value;
        }

        $query['locale'] = app()->getLocale();

        return url()->current() . '?' . http_build_query($query);
    };

    // This list used to be hardcoded here: the same eight countries repeated
    // three times over (24 entries), each repetition carrying a different
    // invented count for the same country. It now comes from
    // CountryStatsService — the single source of truth shared with the search
    // component and the countries page — so every country appears exactly once
    // with its real profile count.
    $countries = app(\App\Services\CountryStatsService::class)
        ->countries()
        ->map(fn ($country) => [
            // flagcdn URLs below need the lowercase code.
            'code' => strtolower($country->code),
            'name' => $country->name,
            'count' => $country->profiles_count,
            'regions' => $country->regions->map(fn ($region) => [
                'name' => $region->name,
                'count' => $region->profiles_count,
            ])->all(),
        ])
        ->all();

    $openCountries = [];

    foreach ($countries as $country) {
        $openCountries[$country['code']] = $selectedCountryCode === $country['code'] && $selectedRegion !== '';
    }

@endphp

<aside class="hidden lg:block w-[208px] shrink-0">
    <div class="sticky top-24">
        <div class="mb-6 flex h-20 w-[208px] items-center justify-center rounded-[8px] border-2 border-[#F2F2F2] bg-transparent">
            <span style="font-family:'Poppins', sans-serif;font-weight:700;font-size:18px;color:#5C2D62;line-height:1;">{{ __('front.profiles.list.topresults') }}</span>
        </div>

        <div class="space-y-[6px]">
            @foreach($countries as $country)
                <div>
                    @php
                        $isActiveCountry = $selectedCountry === $country['name'] && $selectedRegion === '';
                        $hasRegions = filled($country['regions']);
                    @endphp
                    <div class="flex items-center gap-2">
                                <a href="{{ $queryFor(['country' => $country['name'], 'country_code' => $country['code'], 'region' => null], ['page']) }}"
                                    @if($hasRegions) data-has-regions="1" data-country-code="{{ $country['code'] }}" @endif
                                    class="flex min-w-0 flex-1 items-center gap-[10px] rounded-[6px] px-[6px] py-[4px] text-left transition-all duration-200 hover:translate-x-[2px] {{ $isActiveCountry ? 'bg-[#F9EDF4]' : '' }}">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center overflow-hidden rounded-full">
                                <img src="https://flagcdn.com/{{ $country['code'] }}.svg" alt="{{ $country['name'] }}" class="h-full w-full object-cover">
                            </span>
                            <span style="font-family:'Poppins', sans-serif;font-size:14px;line-height:1.3;{{ $isActiveCountry ? 'font-weight:600;color:#5C2D62;' : 'font-weight:400;color:#505050;' }}">{{ $country['name'] }}</span>
                            <span class="ml-auto" style="font-family:'Poppins', sans-serif;font-weight:400;font-size:12px;line-height:1.3;{{ $isActiveCountry ? 'color:#DD3888;' : 'color:#B8B8B8;' }}">{{ $country['count'] }}</span>
                        </a>
                        {{-- chevron removed: regions render inline when present --}}
                    </div>

                    @if($hasRegions)
                        <div data-regions data-country="{{ $country['code'] }}" style="display:none;" class="mt-2 ml-[30px] w-[178px] space-y-1">
                            @foreach($country['regions'] as $region)
                                @php
                                    $isActiveRegion = $selectedRegion === $region['name'];
                                @endphp
                                <a href="{{ $queryFor(['country' => $country['name'], 'country_code' => $country['code'], 'region' => $region['name']], ['page']) }}"
                                   class="flex items-center justify-between rounded-[4px] px-[10px] py-[6px] transition-all duration-200 hover:-translate-y-[1px] {{ $isActiveRegion ? 'bg-[#DD3888] shadow-[0_10px_24px_rgba(221,56,136,0.22)]' : 'bg-[#F2F2F2] hover:bg-[#ECECEC]' }}">
                                    <span style="font-family:'Poppins', sans-serif;font-weight:400;font-size:14px;line-height:1.3;{{ $isActiveRegion ? 'color:#FFFFFF;' : 'color:#505050;' }}">{{ $region['name'] }}</span>
                                    <span style="font-family:'Poppins', sans-serif;font-weight:400;font-size:12px;line-height:1.3;{{ $isActiveRegion ? 'color:#FFFFFF;' : 'color:#B8B8B8;' }}">{{ $region['count'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</aside>

<script>
    // Sidebar region toggles (delegated) — keeps UI simple without chevrons or Alpine
    (function () {
        function toggleRegionsFor(anchor) {
            var code = anchor.dataset.countryCode;
            var wrapper = anchor.closest('div');
            // climb up until we find a wrapper that contains the regions list
            while (wrapper && !wrapper.querySelector('[data-regions]') && wrapper.parentElement) {
                wrapper = wrapper.parentElement;
            }
            var container = wrapper ? wrapper.querySelector('[data-regions][data-country="' + code + '"]') : null;
            if (!container) return;
            container.style.display = container.style.display === 'none' || container.style.display === '' ? 'block' : 'none';
        }

        document.addEventListener('click', function (e) {
            var a = e.target.closest('a[data-has-regions]');
            if (!a) return;
            e.preventDefault();
            toggleRegionsFor(a);
        });
    })();
</script>