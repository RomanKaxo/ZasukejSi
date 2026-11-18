{{--
    The chart data rides on this root element, not inside the <script> below.

    Livewire does not re-execute inline scripts on update, so a script that
    closed over server-rendered values could never redraw — and the canvas
    itself sits inside a wire:ignore block (Chart.js owns that DOM), so the
    attribute cannot live there either. The root element is re-rendered on every
    month change, so reading from it is what makes the arrows work.
--}}
<div id="profileStatsRoot-{{ $instanceId }}"
     data-chart="{{ json_encode([
         'labels' => $chartLabels,
         'values' => $chartValues,
         'colors' => $chartColors,
         'vip' => $chartVip,
         'yAxisMax' => $yAxisMax,
         'yAxisStep' => $yAxisStep,
     ]) }}"
     {{-- home-18 (a month with VIP days) vs home-19 (none): without the VIP
          pill row the month arrows sit 35px higher and the next block starts
          50px sooner. The page passes the spacing per variant. --}}
     @php $statsHasVip = in_array(true, $chartVip, true); @endphp
     class="profile-stats {{ $statsHasVip ? 'profile-stats--vip' : 'profile-stats--novip' }} profile-stats--{{ $variant }}">
    <style>
        @media (max-width: 426px) {
            .profile-stats-mobile { width: 310px; max-width: 100%; margin-left: auto; margin-right: auto; }
            .profile-stats-mobile > h3 { line-height: 34px; margin-top: -2.5px; margin-bottom: 41.5px !important; }
            .profile-stats-mobile .space-y-\[7px\] > * + * { margin-top: 9.6px !important; }
            .profile-stats-mobile .stats-day-label { width: 41px !important; }
            .profile-stats-mobile .stats-day-value { margin-left: 8px; }
            /* A rule 41px under the month arrows closes each chart; the next
               heading's glyph is 86px under it, the footer wordmark 143px. */
            .profile-stats-mobile .stats-mobile-rule { margin-top: 20px; margin-bottom: 34px; border-color: #E9E9E9; }
            .profile-stats--detail .profile-stats-mobile .stats-mobile-rule { margin-bottom: 80px; }
        }
        @media (min-width: 768px) {
            .profile-stats--novip .profile-stats-box { height: 522px !important; }
            .profile-stats--novip .profile-stats-box [data-chart-viewport] { bottom: 8px !important; }
            /* The second chart's arrows sit 3px lower than the first's in both frames. */
            .profile-stats--detail.profile-stats--vip .profile-stats-box { height: 560px !important; }
            .profile-stats--detail.profile-stats--novip .profile-stats-box { height: 525px !important; }
            .profile-stats--detail .profile-stats-box [data-chart-viewport] { bottom: 46px !important; }
            .profile-stats--detail.profile-stats--novip .profile-stats-box [data-chart-viewport] { bottom: 11px !important; }
            .profile-stats--homepage.profile-stats--vip { padding-bottom: 153px; }
            .profile-stats--homepage.profile-stats--novip { padding-bottom: 103px; }
            .profile-stats--detail.profile-stats--vip { padding-bottom: 24px; }
            .profile-stats--detail.profile-stats--novip { margin-bottom: -24px; }
        }
    </style>
    @php
        $chartId = 'profileStatsChart-' . $instanceId;
        $badgesId = 'profileStatsBadges-' . $instanceId;
        $controlsId = 'profileStatsControls-' . $instanceId;
        $chartGlobalName = '__zs_profileStatsChart_' . $instanceId;
        $mobileMaxValue = max($chartValues ?: [1]);
    @endphp

    {{-- Figma home-18: heading box 4px above its glyph, top grid line 81px
         below the glyph, baseline at +437, month arrows 45px tall ending at
         +585. The chart area is 47.6px per day with 30px bars. --}}
    <h3 class="mb-4 md:mb-0 text-left max-[426px]:hidden" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">
        {{ $variant === 'detail' ? __('front.account.statistics.detail_views_title') : __('front.account.statistics.profile_views_title') }}
    </h3>

    @unless($this->hasProfile)
        {{-- No profile means no statistics. This used to fall back to
             Profile::first(), i.e. it charted a stranger's traffic. --}}
        <div class="rounded-[15px] border border-[#F2F2F2] p-8 text-center" style="font-family:'Poppins',sans-serif;color:#505050;">
            {{ __('front.account.statistics.no_profile') }}
        </div>
    @else

    <div class="profile-stats-box w-full h-[510px] md:h-[557px] rounded-[15px] relative mx-auto max-[426px]:hidden">
    {{-- pass 1: the scroll viewport reaches 30px into the gutter on the left
         so a wide three-digit y label ("250", "300") is not clipped; the
         y scale is 30px wider (Chart.js clips labels to it), so the bars stay put. --}}
    <div class="absolute inset-x-0 top-0 bottom-[65px] md:bottom-[43px] overflow-x-auto" style="left:-30px;" data-chart-viewport tabindex="0" aria-label="{{ $variant === 'detail' ? __('front.account.statistics.detail_views_title') : __('front.account.statistics.profile_views_title') }}">
        <div class="relative h-full" data-chart-content>
            <div class="absolute inset-0 z-10" wire:ignore>
                <canvas id="{{ $chartId }}" class="w-full h-full block"></canvas>
            </div>
            <div id="{{ $badgesId }}" class="absolute inset-0 z-20 pointer-events-none" wire:ignore></div>
        </div>
    </div>

    <!-- Month controls stay visible while the date range scrolls. -->
    <div id="{{ $controlsId }}" class="absolute bottom-0 left-1/2 transform -translate-x-1/2 z-30 max-[426px]:hidden" style="pointer-events:auto; display:block; width:100%; max-width:843px;">
        <div class="w-full mx-auto flex items-center justify-center gap-6 min-[1180px]:grid min-[1180px]:grid-cols-[256px_minmax(0,1fr)_256px] min-[1180px]:gap-0">
            <div class="min-w-0 flex-1 min-[1180px]:w-auto h-px bg-[#E9E9E9]"></div>

            <div class="flex shrink-0 items-center gap-4 min-[1180px]:justify-between min-[1180px]:px-[30px]">
                {{-- These two arrows were inert markup with no handler at all,
                     so the chart could only ever show the current month. --}}
                <button id="stats-prev" type="button" wire:click="previousMonth" aria-label="{{ __('front.account.statistics.previous_month') }}" class="w-[45px] h-[45px] rounded-[8px] bg-[#DD3888] flex items-center justify-center text-white transition">
                    <svg width="7" height="9" viewBox="0 0 7 9" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M6 0L0 4.5L6 9V0Z" fill="currentColor"/>
                    </svg>
                </button>

                <div class="text-[#5C2D62] font-bold text-[16px] whitespace-nowrap" style="font-family:Poppins, sans-serif;">
                    {{ $this->currentMonth->locale(app()->getLocale())->translatedFormat('F Y') }}
                </div>

                {{-- home-18: on the current month the next arrow is the pale #FFF4F9 tile
                     at full strength; home-19: on a past month it is filled pink like
                     the previous arrow. --}}
                <button id="stats-next" type="button" wire:click="nextMonth" @disabled($this->isCurrentMonth) aria-label="{{ __('front.account.statistics.next_month') }}" class="w-[45px] h-[45px] rounded-[8px] flex items-center justify-center transition @if($this->isCurrentMonth) bg-[#FFF4F9] text-[#DD3888] cursor-not-allowed @else bg-[#DD3888] text-white @endif">
                    <svg width="7" height="9" viewBox="0 0 7 9" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M1 0L7 4.5L1 9V0Z" fill="currentColor"/>
                    </svg>
                </button>
            </div>

            <div class="min-w-0 flex-1 min-[1180px]:w-auto h-px bg-[#E9E9E9]"></div>
        </div>
    </div>

    </div>

    {{-- pass 1 overlay (phone 360 px-5): a 310px column, 34px heading lines,
         20px bars 29.6px apart starting 49px in, the value 14px after the bar. --}}
    <div class="profile-stats-mobile hidden max-[426px]:block">
        <h3 class="mb-4 text-left" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:24px; color:#5C2D62;">
            {{ $variant === 'detail' ? __('front.account.statistics.detail_views_title') : __('front.account.statistics.profile_views_title') }}
        </h3>

        <div class="space-y-[7px]">
            @foreach($chartLabels as $i => $label)
                @php
                    $value = $chartValues[$i] ?? 0;
                    $barColor = $chartColors[$i] ?? '#DD3888';
                    $barWidth = max(20, (int) round(($value / max($mobileMaxValue, 1)) * 100));
                @endphp
                <div>
                    <div class="flex items-center gap-[8px]">
                        <div class="stats-day-label w-[38px] shrink-0 text-[14px] leading-[14px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">
                            {{ $label }}
                        </div>

                        <div class="flex min-w-0 flex-1 items-center pr-[28px]">
                            <div class="relative flex h-[20px] flex-1 items-center" style="--bar-width: {{ $barWidth }}%;">
                                <div class="h-[20px] rounded-r-[999px] rounded-l-[4px]" style="width: var(--bar-width); background-color: {{ $barColor }};"></div>

                                @if(!empty($chartVip[$i]))
                                    <div class="absolute -left-[6px] top-1/2 z-10 -translate-y-1/2">
                                        <div class="h-[26px] w-[47px] rounded-[999px] bg-[#FFB700] flex items-center justify-center gap-[4px] px-[6px]" style="font-family: 'Poppins', sans-serif;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" fill="#FFFFFF"/>
                                            </svg>
                                            <span class="text-[10px] font-black leading-none text-white">VIP</span>
                                        </div>
                                    </div>
                                @endif

                                <div class="stats-day-value absolute top-1/2 -translate-y-1/2 text-[14px] leading-none" style="left: calc(var(--bar-width) + 6px); font-family: 'Poppins', sans-serif; color: {{ $barColor }};">
                                    {{ $value }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- phone 360 px-5: plain row, no tinted panel behind the arrows --}}
        <div class="mt-6 flex h-[85px] items-center justify-between">
            <button type="button" wire:click="previousMonth" aria-label="{{ __('front.account.statistics.previous_month') }}" class="h-[45px] w-[45px] rounded-[8px] bg-[#DD3888] flex items-center justify-center text-white transition">
                <svg width="7" height="9" viewBox="0 0 7 9" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M6 0L0 4.5L6 9V0Z" fill="currentColor"/>
                </svg>
            </button>

            <div class="flex-1 text-center text-[#5C2D62] text-[16px] font-bold leading-none" style="font-family: 'Poppins', sans-serif;">
                {{ $this->currentMonth->locale(app()->getLocale())->translatedFormat('F Y') }}
            </div>

            <button type="button" wire:click="nextMonth" @disabled($this->isCurrentMonth) aria-label="{{ __('front.account.statistics.next_month') }}" class="h-[45px] w-[45px] rounded-[8px] bg-[#FFF4F9] flex items-center justify-center text-[#DD3888] transition">
                <svg width="7" height="9" viewBox="0 0 7 9" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M1 0L7 4.5L1 9V0Z" fill="currentColor"/>
                </svg>
            </button>
        </div>
        <hr class="stats-mobile-rule">
    </div>

<!-- VIP badges grid under the chart (one column per label) -->
{{-- badges are rendered by JS into #profileStatsBadges using Chart.js coordinates --}}

<!-- Chart.js (CDN) + init script (re-inits after Livewire updates) -->
 
<!-- single controls block above is used; removed duplicate centered controls -->
{{-- Chart.js is bundled into app.js and exposed as window.Chart, so this page
     no longer reaches out to a public CDN on every load. --}}
<script>
(function () {
    function initChart() {
        if (!window.Chart || window.matchMedia('(max-width: 426px)').matches) {
            return;
        }

        const chartId = @json($chartId);
        const badgesId = @json($badgesId);
        const chartGlobalName = @json($chartGlobalName);
        const canvas = document.getElementById(chartId);
        if (!canvas) { console.warn('profileStatsChart canvas not found'); return; }

        // Read the freshly rendered data off the component root, so switching
        // months redraws with the new values instead of the ones present at
        // page load.
        const root = document.getElementById(@json('profileStatsRoot-' . $instanceId));
        let chartData = {};
        try { chartData = JSON.parse((root && root.dataset.chart) || '{}'); } catch (e) { console.warn('Invalid chart data', e); }

        const labels = chartData.labels || [];
        const values = chartData.values || [];
        const barColors = chartData.colors || [];
        const yAxisMax = chartData.yAxisMax ?? 10;
        const yAxisStep = chartData.yAxisStep ?? 2;

        // Keep approximately 15 legible days in the desktop viewport. Longer
        // months scroll horizontally without skipping dates or inventing data.
        const content = canvas.closest('[data-chart-content]');
        // Figma home-18: 54px axis column, 47.6px per day, 27px right margin
        // (16 days fill the 843px column exactly).
        content.style.minWidth = `${Math.round(labels.length * 47.6 + 54 + 27 + 30)}px`;

        // Ensure canvas has explicit pixel size so Chart.js can draw correctly
        const parentRect = canvas.parentElement.getBoundingClientRect();
        canvas.width = Math.round(parentRect.width);
        canvas.height = Math.round(parentRect.height);
        const ctx = canvas.getContext('2d');

        // use a namespaced global to avoid collisions with other scripts/extensions
        if (window[chartGlobalName]) {
            if (typeof window[chartGlobalName].destroy === 'function') {
                try { window[chartGlobalName].destroy(); } catch (err) { console.warn('Error destroying previous chart', err); }
            } else {
                console.warn(chartGlobalName + ' exists but has no destroy method, resetting.', window[chartGlobalName]);
                window[chartGlobalName] = null;
            }
        }

        // plugin to draw values above bars (wrapped in try/catch to avoid breaking chart)
        const valueLabelPlugin = {
            id: 'valueLabels',
            afterDatasetsDraw(chart) {
                try {
                    const ctx = chart.ctx;
                    ctx.save();
                    const dataset = chart.data.datasets[0];
                    ctx.font = '500 14px Poppins';
                    ctx.textAlign = 'center';

                    chart.getDatasetMeta(0).data.forEach((bar, index) => {
                        const value = dataset.data[index];
                        if (value === undefined || value === null) return;
                        const x = bar.x;
                        const y = (bar.y || 0) - 12; // Figma: glyph bottom 12px above the bar
                        // pick label color from dataset.backgroundColor per-bar if provided
                        // Figma home-18 prints every value in pink, also
                        // above the purple VIP bars.
                        ctx.fillStyle = '#DD3888';
                        ctx.fillText(value, x, y);
                    });

                    ctx.restore();
                } catch (err) {
                    // don't crash the chart on plugin errors
                    console.error('valueLabelPlugin error', err);
                }
            }
        };

        // defensive checks
        if (!Array.isArray(labels) || !Array.isArray(values) || labels.length !== values.length) {
            console.warn('labels/values mismatch or invalid - skipping chart init', labels, values);
            return;
        }

        // We'll rely on Chart.js to draw the horizontal grid lines so they align
        // perfectly with bar values. Increase bottom padding to move the
        // x-axis date labels further down so badges can sit below them.
        const bottomPadding = 39;

        // plugin to draw baseline at y=0 (horizontal) so we don't rely on axis borders
        const baselinePlugin = {
            id: 'baseline',
            afterDraw(chart) {
                try {
                    const ctx = chart.ctx;
                    const yScale = chart.scales.y;
                    const yPixel = yScale.getPixelForValue(0);
                    ctx.save();
                    ctx.beginPath();
                    ctx.strokeStyle = '#E8E8E8';
                    ctx.lineWidth = 3;
                    // Starts at the column edge, not in the 30px label gutter.
                    ctx.moveTo(30, yPixel - 1);
                    ctx.lineTo(chart.width, yPixel - 1);
                    ctx.stroke();
                    ctx.restore();
                } catch (e) {
                    console.warn('baseline plugin error', e);
                }
            }
        };

        // Figma grid: 1px #E8E8E8 hairlines (home-19 second chart; the first
        // chart draws them on half pixels) from 15px left of the bars to the
        // right edge of the column, no line at zero (the baseline covers it).
        const gridPlugin = {
            id: 'zsGrid',
            beforeDatasetsDraw(chart) {
                const yScale = chart.scales.y;
                const g = chart.ctx;
                g.save();
                g.strokeStyle = '#E8E8E8';
                g.lineWidth = 1;
                yScale.ticks.forEach((tick) => {
                    if (!tick.value) return;
                    const y = Math.round(yScale.getPixelForValue(tick.value)) + 0.5;
                    g.beginPath();
                    g.moveTo(chart.chartArea.left - 15, y);
                    g.lineTo(chart.width, y);
                    g.stroke();
                });
                g.restore();
            }
        };

        window[chartGlobalName] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: barColors,
                    barThickness: 30,
                    borderRadius: { topLeft: 8, topRight: 8, bottomLeft: 0, bottomRight: 0 },
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        min: 0,
                        max: yAxisMax,
                        ticks: {
                            stepSize: yAxisStep,
                            callback: function(value) { return value === 0 ? '' : value; },
                            color: '#505050',
                            font: { family: 'Poppins', size: 14, weight: '400' },
                            padding: 33
                        },
                        // Fixed axis column so the bars start at the same x
                        // whatever the widest tick label is.
                        // 54px column + the 30px the viewport reaches into the gutter:
                        // Chart.js clips tick labels to the scale box.
                        afterFit(scale) { scale.width = 84; },
                        border: { display: false },
                        grid: { display: false, drawTicks: false }
                    },
                    x: {
                        ticks: {
                            display: true,
                            autoSkip: false,
                            minRotation: 0,
                            maxRotation: 0,
                            color: '#505050',
                            font: { family: 'Poppins', size: 14, weight: '400' },
                            padding: 24
                        },
                        border: { display: false },
                        grid: { display: false, drawTicks: false },
                        offset: true
                    }
                },
                plugins: { legend: { display: false }, tooltip: { enabled: true } },
                layout: { padding: { top: 53, bottom: bottomPadding, left: 0, right: 27 } }
            },
            plugins: [gridPlugin, valueLabelPlugin, baselinePlugin]
        });

        // VIP flags from server
        const vipFlags = chartData.vip || [];

        // Render VIP badges positioned precisely under each bar using Chart.js coordinates
        function renderVipBadges(chart) {
            const container = document.getElementById(badgesId);
            if (!container || !chart) return;
            // clear existing
            container.innerHTML = '';
            if (!Array.isArray(vipFlags) || vipFlags.length === 0) return;

            const meta = chart.getDatasetMeta(0);
            if (!meta || !meta.data) return;

            meta.data.forEach((bar, i) => {
                // One pill where a VIP period starts (Figma home-18), not
                // one under every VIP day.
                if (!vipFlags[i] || vipFlags[i - 1]) return;

                const wrapper = document.createElement('div');
                wrapper.className = 'zs-vip-wrapper';
                wrapper.style.position = 'absolute';
                wrapper.style.left = `${Math.round(bar.x)}px`;
                // position badge relative to chart bottom; increase offset to move it down by ~15px
                const badgeOffset = 57; // Figma: badge top 57px under the baseline
                const topPx = (chart.chartArea && chart.chartArea.bottom) ? Math.round(chart.chartArea.bottom + badgeOffset) : (canvas.height + badgeOffset);
                wrapper.style.top = `${topPx}px`;
                wrapper.style.transform = 'translateX(-50%)';
                wrapper.style.pointerEvents = 'auto';

                // badge element (centered content)
                const badge = document.createElement('div');
                badge.setAttribute('role', 'img');
                badge.style.width = '47px';
                badge.style.height = '26px';
                badge.style.borderRadius = '999px';
                badge.style.background = '#FFB700';
                badge.style.display = 'flex';
                badge.style.alignItems = 'center';
                badge.style.justifyContent = 'center';
                badge.style.gap = '4px';
                badge.style.padding = '0 6px';
                badge.style.fontFamily = 'Poppins, sans-serif';

                badge.innerHTML = `
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="flex:0 0 14px;">
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" fill="#FFFFFF"/>
                    </svg>
                    <span style="color:#fff;font-weight:900;font-size:10px;line-height:1;margin-left:2px;">VIP</span>
                `;

                wrapper.appendChild(badge);
                container.appendChild(wrapper);
            });

        }

        // initial render
        renderVipBadges(window[chartGlobalName]);

        // update badges on resize to keep them aligned
        window[chartGlobalName].options.onResize = () => requestAnimationFrame(() => renderVipBadges(window[chartGlobalName]));
    }

    document.addEventListener('livewire:initialized', function () {
        initChart();
        const root = document.getElementById(@json('profileStatsRoot-' . $instanceId));
        if (root) {
            new MutationObserver(initChart).observe(root, { attributes: true, attributeFilter: ['data-chart'] });
        }
    });
    document.addEventListener('DOMContentLoaded', initChart);
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(initChart, 150);
    });
    initChart();
})();
</script>
    @endunless
</div>
