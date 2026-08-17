<?php

namespace App\Filament\Widgets;

use App\Support\Currencies;
use App\Support\SubscriptionRevenue;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Peníze a běžící předplatná.
 *
 * Nástěnka uměla říct, kolik profilů čeká na schválení, ale o té druhé
 * polovině provozu — kolik se vydělalo a kolik předplatných běží — neřekla
 * vůbec nic.
 */
class SubscriptionOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -9;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $revenue = app(SubscriptionRevenue::class);

        $thisMonth = $revenue->between(now()->startOfMonth(), now()->addSecond());
        $lastMonth = $revenue->between(now()->subMonth()->startOfMonth(), now()->startOfMonth());

        $thisYear = $revenue->between(now()->subYear(), now()->addSecond());
        $lastYear = $revenue->between(now()->subYears(2), now()->subYear());

        return [
            $this->money('Tržby tento měsíc', $thisMonth, $this->comparison($thisMonth, $lastMonth, 'měsíc')),
            $this->money('Tržby za rok', $thisYear, $this->comparison($thisYear, $lastYear, 'rok')),
            $this->activeSubscriptions($revenue),
            $this->activeMemberships($revenue),
            $this->expiring($revenue),
            $this->withoutPayment($revenue),
        ];
    }

    private function money(string $label, float $amount, ?string $note = null): Stat
    {
        $stat = Stat::make($label, Currencies::format($amount, SubscriptionRevenue::CURRENCY))
            ->color('success')
            ->icon('heroicon-o-banknotes');

        return $note ? $stat->description($note) : $stat;
    }

    /**
     * Slovní srovnání s předchozím obdobím, ať číslo něco znamená.
     *
     * @param  string  $period  „měsíc" nebo „rok" — vejde se do věty.
     */
    private function comparison(float $now, float $before, string $period): string
    {
        $previous = $period === 'rok' ? 'předchozí rok' : 'minulý měsíc';

        if ($before <= 0.0) {
            return $now > 0
                ? "první tržby, {$previous} byl bez nich"
                : "{$previous} byl také bez tržeb";
        }

        $change = (int) round((($now - $before) / $before) * 100);

        return match (true) {
            $change === 0 => "stejně jako {$previous}",
            $change > 0 => "o {$change} % víc než {$previous}",
            default => 'o ' . abs($change) . " % míň než {$previous}",
        };
    }

    private function activeSubscriptions(SubscriptionRevenue $revenue): Stat
    {
        return Stat::make('Aktivní předplatná dívek', (string) $revenue->activeProfileSubscriptions())
            ->description('VIP a další tarify profilů')
            ->icon('heroicon-o-star')
            ->color('warning')
            ->url(route('filament.admin.resources.subscriptions.index'));
    }

    private function activeMemberships(SubscriptionRevenue $revenue): Stat
    {
        return Stat::make('Aktivní členství pánů', (string) $revenue->activeMemberships())
            ->description('Premium, které odemyká hodnocení')
            ->icon('heroicon-o-user-group')
            ->color('info')
            ->url(route('filament.admin.resources.member-subscriptions.index'));
    }

    private function expiring(SubscriptionRevenue $revenue): Stat
    {
        $count = $revenue->expiringWithin(7);

        return Stat::make('Končí do 7 dnů', (string) $count)
            ->description($count > 0 ? 'stojí za připomenutí' : 'nic nekončí')
            ->icon('heroicon-o-clock')
            ->color($count > 0 ? 'danger' : 'gray');
    }

    /**
     * Bez téhle položky by tržby a počet předplatných nešly srovnat: ručně
     * přidělené předplatné se počítá mezi aktivní, ale ne do peněz.
     */
    private function withoutPayment(SubscriptionRevenue $revenue): Stat
    {
        $count = $revenue->grantedWithoutPayment();

        return Stat::make('Přiděleno bez platby', (string) $count)
            ->description($count > 0 ? 'nezapočítává se do tržeb' : 'vše je zaplacené')
            ->icon('heroicon-o-exclamation-triangle')
            ->color($count > 0 ? 'warning' : 'gray');
    }
}
