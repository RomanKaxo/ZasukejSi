@php
    /**
     * Malý graf v řádku tabulky.
     *
     * Sloupečky, ne křivka: u návštěvnosti je čitelnější, kde byl den nebo
     * měsíc silný, než jak plynule to mezi tím přecházelo.
     */
    $series = $getState() ?? [];
    $max = max(1, ...(count($series) ? $series : [0]));
    $count = max(1, count($series));

    $width = 132;
    $height = 28;
    $gap = $count > 40 ? 0 : 1;
    $barWidth = max(1, ($width - ($gap * ($count - 1))) / $count);
    $total = array_sum($series);
@endphp

<div class="flex items-center gap-2" title="{{ $total }} zobrazení za sledované období">
    @if($total === 0)
        <span class="text-xs text-gray-400 dark:text-gray-500">bez zobrazení</span>
    @else
        <svg width="{{ $width }}" height="{{ $height }}" viewBox="0 0 {{ $width }} {{ $height }}"
             role="img" aria-label="Vývoj zobrazení: celkem {{ $total }}"
             style="display:block;overflow:visible;">
            @foreach($series as $i => $value)
                @php
                    $barHeight = $value > 0 ? max(2, ($value / $max) * $height) : 1;
                    $x = $i * ($barWidth + $gap);
                    $y = $height - $barHeight;
                @endphp
                <rect x="{{ round($x, 2) }}" y="{{ round($y, 2) }}"
                      width="{{ round($barWidth, 2) }}" height="{{ round($barHeight, 2) }}"
                      rx="{{ $barWidth > 3 ? 1 : 0 }}"
                      fill="{{ $value > 0 ? '#DD3888' : '#E5E0E7' }}"
                      opacity="{{ $value > 0 ? 1 : 0.6 }}"></rect>
            @endforeach
        </svg>
    @endif
</div>
