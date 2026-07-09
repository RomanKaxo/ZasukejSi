<div id="profileStatsRoot-<?php echo e($instanceId); ?>">
    <?php
        $chartId = 'profileStatsChart-' . $instanceId;
        $badgesId = 'profileStatsBadges-' . $instanceId;
        $controlsId = 'profileStatsControls-' . $instanceId;
        $chartGlobalName = '__zs_profileStatsChart_' . $instanceId;
        $mobileMaxValue = max($chartValues ?: [1]);
    ?>

    <div class="w-full h-[510px] rounded-[15px] relative mx-auto max-[426px]:hidden">
    <!-- Chart will draw its own grid; removed static HTML grid to avoid duplication -->

    <!-- Chart canvas (Chart.js will draw grid & labels) -->
    <div class="absolute inset-0 z-10" wire:ignore>
        <canvas id="<?php echo e($chartId); ?>" class="w-full h-full block"></canvas>
    </div>

    <!-- badges overlay: we'll position badges exactly under each bar using Chart.js coordinates -->
    <div id="<?php echo e($badgesId); ?>" class="absolute left-0 top-0 w-full h-full z-20 pointer-events-none"></div>

    <!-- profile label removed to avoid clashing with page heading -->

    <!-- Controls block INSIDE the chart container; JS will position it directly under the VIP badge -->
    <div id="<?php echo e($controlsId); ?>" class="absolute left-1/2 transform -translate-x-1/2 z-30 max-[426px]:hidden" style="pointer-events:auto; display:block; width:100%; max-width:843px;">
        <div class="w-full mx-auto flex items-center justify-center gap-6">
            <div class="w-[255px] h-px bg-[#E6E6E6]"></div>

            <div class="flex items-center gap-4">
                <button id="stats-prev" type="button" aria-label="Previous month" class="w-[45px] h-[45px] rounded-[8px] bg-[#DD3888] flex items-center justify-center text-white hover:bg-[#FFF4F9] hover:text-[#DD3888] transition">
                    <svg width="7" height="9" viewBox="0 0 7 9" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M6 0L0 4.5L6 9V0Z" fill="currentColor"/>
                    </svg>
                </button>

                <div class="text-[#5C2D62] font-bold text-[16px]" style="font-family:Poppins, sans-serif;">
                    <?php echo e(isset($currentMonth) ? $currentMonth->locale('cs')->translatedFormat('F Y') : now()->locale('cs')->translatedFormat('F Y')); ?>

                </div>

                <button id="stats-next" type="button" aria-label="Next month" class="w-[45px] h-[45px] rounded-[8px] bg-[#DD3888] flex items-center justify-center text-white hover:bg-[#FFF4F9] hover:text-[#DD3888] transition">
                    <svg width="7" height="9" viewBox="0 0 7 9" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M1 0L7 4.5L1 9V0Z" fill="currentColor"/>
                    </svg>
                </button>
            </div>

            <div class="w-[255px] h-px bg-[#E6E6E6]"></div>
        </div>
    </div>

    </div>

    <div class="hidden max-[426px]:block">
        <div class="space-y-[7px]">
            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $chartLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $value = $chartValues[$i] ?? 0;
                    $barColor = $chartColors[$i] ?? '#DD3888';
                    $barWidth = max(20, (int) round(($value / max($mobileMaxValue, 1)) * 100));
                ?>
                <div>
                    <div class="flex items-center gap-[8px]">
                        <div class="w-[38px] shrink-0 text-[14px] leading-[14px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">
                            <?php echo e($label); ?>

                        </div>

                        <div class="flex min-w-0 flex-1 items-center">
                            <div class="relative flex h-[20px] flex-1 items-center" style="--bar-width: <?php echo e($barWidth); ?>%;">
                                <div class="h-[20px] rounded-r-[999px] rounded-l-[4px]" style="width: var(--bar-width); background-color: <?php echo e($barColor); ?>;"></div>

                                <!--[if BLOCK]><![endif]--><?php if(!empty($chartVip[$i])): ?>
                                    <div class="absolute -left-[6px] top-1/2 z-10 -translate-y-1/2">
                                        <div class="h-[26px] w-[47px] rounded-[999px] bg-[#FFB700] flex items-center justify-center gap-[4px] px-[6px]" style="font-family: 'Poppins', sans-serif;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" fill="#FFFFFF"/>
                                            </svg>
                                            <span class="text-[10px] font-black leading-none text-white">VIP</span>
                                        </div>
                                    </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                                <div class="absolute top-1/2 -translate-y-1/2 text-[14px] leading-none" style="left: calc(var(--bar-width) + 6px); font-family: 'Poppins', sans-serif; color: <?php echo e($barColor); ?>;">
                                    <?php echo e($value); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <div class="mt-6 flex h-[85px] items-center justify-between rounded-[15px] bg-[#FFF8FB] px-[16px]">
            <button type="button" aria-label="Previous month" class="h-[45px] w-[45px] rounded-[8px] bg-[#DD3888] flex items-center justify-center text-white hover:bg-[#FFF4F9] hover:text-[#DD3888] transition">
                <svg width="7" height="9" viewBox="0 0 7 9" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M6 0L0 4.5L6 9V0Z" fill="currentColor"/>
                </svg>
            </button>

            <div class="flex-1 text-center text-[#5C2D62] text-[16px] font-bold leading-none" style="font-family: 'Poppins', sans-serif;">
                <?php echo e(isset($currentMonth) ? $currentMonth->locale('cs')->translatedFormat('F Y') : now()->locale('cs')->translatedFormat('F Y')); ?>

            </div>

            <button type="button" aria-label="Next month" class="h-[45px] w-[45px] rounded-[8px] bg-[#FFF4F9] flex items-center justify-center text-[#DD3888] transition">
                <svg width="7" height="9" viewBox="0 0 7 9" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M1 0L7 4.5L1 9V0Z" fill="currentColor"/>
                </svg>
            </button>
        </div>
    </div>

<!-- VIP badges grid under the chart (one column per label) -->


<!-- Chart.js (CDN) + init script (re-inits after Livewire updates) -->
 
<!-- single controls block above is used; removed duplicate centered controls -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    function initChart() {
        if (window.matchMedia('(max-width: 426px)').matches) {
            return;
        }

        const chartId = <?php echo json_encode($chartId, 15, 512) ?>;
        const badgesId = <?php echo json_encode($badgesId, 15, 512) ?>;
        const controlsId = <?php echo json_encode($controlsId, 15, 512) ?>;
        const chartGlobalName = <?php echo json_encode($chartGlobalName, 15, 512) ?>;
        const labels = <?php echo json_encode($chartLabels ?? [], 15, 512) ?>;
        const values = <?php echo json_encode($chartValues ?? [], 15, 512) ?>;
        const yAxisMax = <?php echo json_encode($yAxisMax ?? 120, 15, 512) ?>;
        const yAxisStep = <?php echo json_encode($yAxisStep ?? 20, 15, 512) ?>;
        const canvas = document.getElementById(chartId);
        if (!canvas) { console.warn('profileStatsChart canvas not found'); return; }

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
                    ctx.font = '600 14px Poppins';
                    ctx.textAlign = 'center';

                    chart.getDatasetMeta(0).data.forEach((bar, index) => {
                        const value = dataset.data[index];
                        if (value === undefined || value === null) return;
                        const x = bar.x;
                        const y = (bar.y || 0) - 8; // slightly above the bar
                        // pick label color from dataset.backgroundColor per-bar if provided
                        try {
                            const bg = Array.isArray(dataset.backgroundColor) ? dataset.backgroundColor[index] : dataset.backgroundColor;
                            ctx.fillStyle = bg || '#DD3888';
                        } catch (e) {
                            ctx.fillStyle = '#DD3888';
                        }
                        ctx.fillText(value, x, y);
                    });

                    ctx.restore();
                } catch (err) {
                    // don't crash the chart on plugin errors
                    console.error('valueLabelPlugin error', err);
                }
            }
        };

        // defensive checks and logs
        console.log('initChart labels/values', labels.length, values.length);
        if (!Array.isArray(labels) || !Array.isArray(values) || labels.length !== values.length) {
            console.warn('labels/values mismatch or invalid - skipping chart init', labels, values);
            return;
        }

        // We'll rely on Chart.js to draw the horizontal grid lines so they align
        // perfectly with bar values. Increase bottom padding to move the
        // x-axis date labels further down so badges can sit below them.
        const bottomPadding = 120;

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
                    ctx.moveTo(chart.chartArea.left, yPixel + 0.5);
                    ctx.lineTo(chart.chartArea.right, yPixel + 0.5);
                    ctx.stroke();
                    ctx.restore();
                } catch (e) {
                    console.warn('baseline plugin error', e);
                }
            }
        };

        window[chartGlobalName] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: <?php echo json_encode($chartColors ?? [], 15, 512) ?>,
                    barThickness: 30,
                    maxBarThickness: 30,
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
                            font: { family: 'Poppins', size: 14, weight: '500' }
                        },
                        grid: {
                            display: true,
                            color: '#E8E8E8',
                            lineWidth: 1,
                            drawBorder: false,
                            drawTicks: false
                        }
                    },
                    x: {
                        ticks: {
                            display: true,
                            color: '#505050',
                            font: { family: 'Poppins', size: 14, weight: '500' },
                            padding: 8
                        },
                        grid: { display: false, drawBorder: false, drawTicks: false },
                        offset: true
                    }
                },
                plugins: { legend: { display: false }, tooltip: { enabled: true } },
                layout: { padding: { top: 0, bottom: bottomPadding, left: 0, right: 0 } }
            },
            plugins: [valueLabelPlugin, baselinePlugin]
        });

        // VIP flags from server
        const vipFlags = <?php echo json_encode($chartVip ?? [], 15, 512) ?>;

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
                if (!vipFlags[i]) return;

                const wrapper = document.createElement('div');
                wrapper.className = 'zs-vip-wrapper';
                wrapper.style.position = 'absolute';
                wrapper.style.left = `${Math.round(bar.x)}px`;
                // position badge relative to chart bottom; increase offset to move it down by ~15px
                const badgeOffset = 43;
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

            // After rendering badges, position controls under the first badge
            positionControlsBelowBadge();
        }

        function positionControlsBelowBadge() {
                try {
                    const controls = document.getElementById(controlsId);
                    const badgeEl = document.querySelector('#' + badgesId + ' .zs-vip-wrapper');
                    const chart = window[chartGlobalName];
                    if (!controls) return;
                    if (badgeEl) {
                        const badgeRect = badgeEl.getBoundingClientRect();
                        // use the chart container (parent of the canvas parent) as reference
                        const parentRect = canvas.parentElement.parentElement.getBoundingClientRect();
                        // Compute top relative to chart container
                        const top = Math.round(badgeRect.bottom - parentRect.top + 8);
                        controls.style.top = top + 'px';
                        return;
                    }

                    // Fallback: position controls just below the chart area
                    if (chart && chart.chartArea) {
                        const top = Math.round(chart.chartArea.bottom + 12);
                        controls.style.top = top + 'px';
                    }
                } catch (e) { console.warn('positionControlsBelowBadge error', e); }
        }

        // initial render
        renderVipBadges(window[chartGlobalName]);

        // update badges on resize to keep them aligned
        window.addEventListener('resize', function () {
            if (window[chartGlobalName]) renderVipBadges(window[chartGlobalName]);
        });
    }

    if (window.Livewire) {
        document.addEventListener('livewire:load', initChart);
        Livewire.hook('message.processed', (message, component) => {
            // Re-init after Livewire updates
            initChart();
        });
    } else {
        document.addEventListener('DOMContentLoaded', initChart);
    }

    // Try to initialize immediately in case events already fired
    initChart();
})();
</script>
</div>
<?php /**PATH C:\Users\roman\Desktop\ZasukejSi\resources\views/livewire/profile-statistics.blade.php ENDPATH**/ ?>