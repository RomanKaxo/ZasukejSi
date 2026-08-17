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
        return 'Tržby za posledních 12 měsíců';
    }

    public function getDescription(): ?string
    {
        return 'Počítá se, co bylo skutečně zaplaceno. Předplatné přidělené bez platby se sem nezapočítává.';
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $monthly = app(SubscriptionRevenue::class)->monthly(12);

        return [
            'datasets' => [
                [
                    'label' => 'Tržby (' . SubscriptionRevenue::CURRENCY . ')',
                    'data' => array_values($monthly),
                    'backgroundColor' => '#DD3888',
                    'borderColor' => '#DD3888',
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
                ],
            ],
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ];
    }
}
