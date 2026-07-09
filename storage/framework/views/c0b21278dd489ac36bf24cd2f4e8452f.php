<?php
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

    $countries = [
        ['code' => 'al', 'name' => 'Albánie', 'count' => 484, 'regions' => []],
        ['code' => 'ad', 'name' => 'Andorra', 'count' => 45, 'regions' => []],
        ['code' => 'am', 'name' => 'Arménie', 'count' => 24, 'regions' => []],
        ['code' => 'be', 'name' => 'Belgie', 'count' => 114, 'regions' => []],
        ['code' => 'by', 'name' => 'Bělorusko', 'count' => 20, 'regions' => []],
        [
            'code' => 'ba',
            'name' => 'Bosna a Hercegovina',
            'count' => 50,
            'regions' => [
                ['name' => 'Bihać', 'count' => 484],
                ['name' => 'Brčko', 'count' => 45],
                ['name' => 'Doboj', 'count' => 24],
                ['name' => 'Foča', 'count' => 114],
                ['name' => 'Jahorina', 'count' => 457],
                ['name' => 'Konjic', 'count' => 87],
                ['name' => 'Neum', 'count' => 70],
                ['name' => 'Prijedor', 'count' => 457],
                ['name' => 'Šamac', 'count' => 87],
            ],
        ],
        ['code' => 'bg', 'name' => 'Bulharsko', 'count' => 457, 'regions' => []],
        ['code' => 'me', 'name' => 'Černá Hora', 'count' => 87, 'regions' => []],
        ['code' => 'cz', 'name' => 'Česká republika', 'count' => 70, 'regions' => []],
        ['code' => 'al', 'name' => 'Albánie', 'count' => 484, 'regions' => []],
        ['code' => 'ad', 'name' => 'Andorra', 'count' => 45, 'regions' => []],
        ['code' => 'am', 'name' => 'Arménie', 'count' => 24, 'regions' => []],
        ['code' => 'be', 'name' => 'Belgie', 'count' => 114, 'regions' => []],
        ['code' => 'by', 'name' => 'Bělorusko', 'count' => 20, 'regions' => []],
        ['code' => 'bg', 'name' => 'Bulharsko', 'count' => 50, 'regions' => []],
        ['code' => 'me', 'name' => 'Černá Hora', 'count' => 457, 'regions' => []],
        ['code' => 'cz', 'name' => 'Česká republika', 'count' => 87, 'regions' => []],
        ['code' => 'al', 'name' => 'Albánie', 'count' => 70, 'regions' => []],
        ['code' => 'ad', 'name' => 'Andorra', 'count' => 484, 'regions' => []],
        ['code' => 'am', 'name' => 'Arménie', 'count' => 45, 'regions' => []],
        ['code' => 'be', 'name' => 'Belgie', 'count' => 24, 'regions' => []],
        ['code' => 'by', 'name' => 'Bělorusko', 'count' => 114, 'regions' => []],
        ['code' => 'bg', 'name' => 'Bulharsko', 'count' => 20, 'regions' => []],
        ['code' => 'me', 'name' => 'Černá Hora', 'count' => 50, 'regions' => []],
        ['code' => 'cz', 'name' => 'Česká republika', 'count' => 457, 'regions' => []],
    ];

    $openCountries = [];

    foreach ($countries as $country) {
        $openCountries[$country['code']] = $selectedCountryCode === $country['code'] && $selectedRegion !== '';
    }

?>

<aside class="hidden lg:block w-[208px] shrink-0">
    <div class="sticky top-24">
        <div class="mb-6 flex h-20 w-[208px] items-center justify-center rounded-[8px] border-2 border-[#F2F2F2] bg-transparent">
            <span style="font-family:'Poppins', sans-serif;font-weight:700;font-size:18px;color:#5C2D62;line-height:1;"><?php echo e(__('front.profiles.list.topresults')); ?></span>
        </div>

        <div class="space-y-[6px]">
            <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div>
                    <?php
                        $isActiveCountry = $selectedCountry === $country['name'] && $selectedRegion === '';
                        $hasRegions = filled($country['regions']);
                    ?>
                    <div class="flex items-center gap-2">
                                <a href="<?php echo e($queryFor(['country' => $country['name'], 'country_code' => $country['code'], 'region' => null], ['page'])); ?>"
                                    <?php if($hasRegions): ?> data-has-regions="1" data-country-code="<?php echo e($country['code']); ?>" <?php endif; ?>
                                    class="flex min-w-0 flex-1 items-center gap-[10px] rounded-[6px] px-[6px] py-[4px] text-left transition-all duration-200 hover:translate-x-[2px] <?php echo e($isActiveCountry ? 'bg-[#F9EDF4]' : ''); ?>">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center overflow-hidden rounded-full">
                                <img src="https://flagcdn.com/<?php echo e($country['code']); ?>.svg" alt="<?php echo e($country['name']); ?>" class="h-full w-full object-cover">
                            </span>
                            <span style="font-family:'Poppins', sans-serif;font-size:14px;line-height:1.3;<?php echo e($isActiveCountry ? 'font-weight:600;color:#5C2D62;' : 'font-weight:400;color:#505050;'); ?>"><?php echo e($country['name']); ?></span>
                            <span class="ml-auto" style="font-family:'Poppins', sans-serif;font-weight:400;font-size:12px;line-height:1.3;<?php echo e($isActiveCountry ? 'color:#DD3888;' : 'color:#B8B8B8;'); ?>"><?php echo e($country['count']); ?></span>
                        </a>
                        
                    </div>

                    <?php if($hasRegions): ?>
                        <div data-regions data-country="<?php echo e($country['code']); ?>" style="display:none;" class="mt-2 ml-[30px] w-[178px] space-y-1">
                            <?php $__currentLoopData = $country['regions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $region): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $isActiveRegion = $selectedRegion === $region['name'];
                                ?>
                                <a href="<?php echo e($queryFor(['country' => $country['name'], 'country_code' => $country['code'], 'region' => $region['name']], ['page'])); ?>"
                                   class="flex items-center justify-between rounded-[4px] px-[10px] py-[6px] transition-all duration-200 hover:-translate-y-[1px] <?php echo e($isActiveRegion ? 'bg-[#DD3888] shadow-[0_10px_24px_rgba(221,56,136,0.22)]' : 'bg-[#F2F2F2] hover:bg-[#ECECEC]'); ?>">
                                    <span style="font-family:'Poppins', sans-serif;font-weight:400;font-size:14px;line-height:1.3;<?php echo e($isActiveRegion ? 'color:#FFFFFF;' : 'color:#505050;'); ?>"><?php echo e($region['name']); ?></span>
                                    <span style="font-family:'Poppins', sans-serif;font-weight:400;font-size:12px;line-height:1.3;<?php echo e($isActiveRegion ? 'color:#FFFFFF;' : 'color:#B8B8B8;'); ?>"><?php echo e($region['count']); ?></span>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
</script><?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/components/english-country-sidebar.blade.php ENDPATH**/ ?>