<?php

namespace App\Filament\Resources\ScrapeItems\Pages;

use App\Filament\Resources\ScrapeItems\ScrapeItemResource;
use App\Models\ScrapeItem;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListScrapeItems extends ListRecords
{
    protected static string $resource = ScrapeItemResource::class;

    /** Tabs rather than a filter, so the queue is the default view. */
    public function getTabs(): array
    {
        $tabs = ['all' => Tab::make('Vše')];

        foreach (ScrapeItem::statusOptions() as $status => $label) {
            $tabs[$status] = Tab::make($label)
                ->modifyQueryUsing(fn ($query) => $query->where('status', $status))
                ->badge(ScrapeItem::where('status', $status)->count());
        }

        return $tabs;
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return ScrapeItem::STATUS_PENDING;
    }
}
