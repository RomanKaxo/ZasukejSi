<?php

namespace App\Filament\Widgets;

use App\Support\SubscriptionRevenue;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * Tržby po měsících za poslední rok.
 *
 * Jedno číslo za měsíc nic neřekne o tom, jestli to roste nebo padá. Křivka
 * ano — a je to jediná otázka, kterou si u tržeb člověk klade jako první.
 */
class RevenueChart extends ChartWidget
{
    protected static ?int $sort = -8;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Provoz za posledních 12 měsíců';
    }

    public function getDescription(): ?string
    {
        return 'Tržby počítají, co bylo skutečně zaplaceno — předplatné přidělené bez platby se do nich nezapočítává, ale mezi zakoupená ano. Registrace a počty mají vlastní osu vpravo, jinak by se u tržeb ztratily.';
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $revenue = app(SubscriptionRevenue::class);

        $monthly = $revenue->monthly(12);
        $purchases = $revenue->purchasesByMonth(12);
        $registrations = $revenue->registrationsByMonth(12);

        return [
            'datasets' => [
                [
                    'type' => 'bar',
                    'label' => 'Tržby (' . SubscriptionRevenue::CURRENCY . ')',
                    'data' => array_values($monthly),
                    'backgroundColor' => '#DD3888',
                    'borderColor' => '#DD3888',
                    'yAxisID' => 'y',
                    'order' => 3,
                ],
                [
                    // Čárou, ne sloupcem: jednotky jsou počty, ne koruny, a
                    // vedle tržeb by ze sloupečku zbyl proužek.
                    'type' => 'line',
                    'label' => 'Zakoupená předplatná',
                    'data' => array_values($purchases),
                    'backgroundColor' => '#5C2D62',
                    'borderColor' => '#5C2D62',
                    'yAxisID' => 'count',
                    'tension' => 0.3,
                    'order' => 1,
                ],
                [
                    'type' => 'line',
                    'label' => 'Registrace',
                    'data' => array_values($registrations),
                    'backgroundColor' => '#00B80F',
                    'borderColor' => '#00B80F',
                    'yAxisID' => 'count',
                    'tension' => 0.3,
                    'order' => 2,
                ],
            ],
            'labels' => array_map(
                fn (string $month) => Carbon::createFromFormat('Y-m', $month)->translatedFormat('M Y'),
                array_keys($monthly),
            ),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'position' => 'left',
                    'title' => ['display' => true, 'text' => 'Tržby'],
                ],
                'count' => [
                    'beginAtZero' => true,
                    'position' => 'right',
                    'title' => ['display' => true, 'text' => 'Počet'],
                    // Počty jsou celá čísla; půl registrace neexistuje.
                    'ticks' => ['precision' => 0],
                    'grid' => ['drawOnChartArea' => false],
                ],
            ],
            'plugins' => [
                'legend' => ['display' => true, 'position' => 'bottom'],
            ],
        ];
    }
}
