<?php

namespace App\Filament\Resources\ScrapeItems\Schemas;

use App\Models\ScrapeItem;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

/**
 * What was scraped, laid out so it can be judged before it becomes a profile.
 *
 * The queue used to offer Approve and Import without ever showing what the
 * item contained — the decision was made on a name and a city. Everything the
 * run captured is here, and the fields the importer actually reads can be
 * corrected first, because a wrong value is easier to fix here than on a
 * profile that already exists.
 */
class ScrapeItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Fotografie')
                    ->columnSpanFull()
                    ->description(fn (?ScrapeItem $record) => $record && $record->images
                        ? 'Nalezeno ' . count($record->images) . ' fotografií. Stahují se až při vytvoření profilu.'
                        : 'Zdroj u této položky žádné fotografie nenabídl.')
                    ->components([
                        Placeholder::make('image_previews')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->content(fn (?ScrapeItem $record) => new HtmlString(
                                view('filament.scraping.image-strip', [
                                    'urls' => array_values(array_filter((array) ($record?->images ?? []))),
                                ])->render()
                            )),
                    ]),

                Section::make('Údaje profilu')
                    ->description('Tato pole importér zapisuje do profilu. Můžete je opravit dřív, než profil vznikne.')
                    ->columns(3)
                    ->columnSpanFull()
                    ->components([
                        TextInput::make('normalized.display_name')
                            ->label('Jméno')
                            ->required(),

                        TextInput::make('normalized.age')
                            ->label('Věk')
                            ->numeric(),

                        TextInput::make('normalized.city')
                            ->label('Město'),

                        TextInput::make('normalized.country_code')
                            ->label('Kód země')
                            ->maxLength(2)
                            ->helperText('Dvoupísmenný, např. CZ.'),

                        TextInput::make('normalized.card_height_cm')
                            ->label('Výška (cm)')
                            ->numeric(),

                        TextInput::make('normalized.weight_kg')
                            ->label('Váha (kg)')
                            ->numeric(),

                        TextInput::make('normalized.bust_size')
                            ->label('Prsa'),

                        TextInput::make('normalized.nationality')
                            ->label('Národnost'),

                        TextInput::make('normalized.address')
                            ->label('Adresa'),

                        Textarea::make('normalized.about')
                            ->label('O mně')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),

                Section::make('Ostatní stažené hodnoty')
                    ->description('Vše, co extrakce našla nad rámec polí výše. Importér z toho bere jen služby a jazyky.')
                    ->collapsed()
                    ->columnSpanFull()
                    ->components([
                        Placeholder::make('extra_values')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->content(fn (?ScrapeItem $record) => new HtmlString(
                                view('filament.scraping.value-table', [
                                    'values' => self::extraValues($record),
                                ])->render()
                            )),
                    ]),

                Section::make('Původ')
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsed()
                    ->components([
                        Placeholder::make('source_name')
                            ->label('Zdroj')
                            ->content(fn (?ScrapeItem $record) => $record?->source?->name ?? '—'),

                        Placeholder::make('run_link')
                            ->label('Běh')
                            ->content(fn (?ScrapeItem $record) => $record?->scrape_run_id
                                ? '#' . $record->scrape_run_id
                                : '—'),

                        Placeholder::make('source_url_link')
                            ->label('Zdrojová adresa')
                            ->columnSpanFull()
                            ->content(fn (?ScrapeItem $record) => $record?->source_url
                                ? new HtmlString(
                                    '<a href="' . e($record->source_url) . '" target="_blank" rel="noopener" class="text-primary-600 underline">'
                                    . e($record->source_url) . '</a>'
                                )
                                : '—'),

                        Placeholder::make('external_id_value')
                            ->label('ID u zdroje')
                            ->content(fn (?ScrapeItem $record) => $record?->external_id ?? '—'),

                        Placeholder::make('imported_profile_link')
                            ->label('Vytvořený profil')
                            ->content(fn (?ScrapeItem $record) => $record?->imported_profile_id
                                ? new HtmlString(
                                    '<a href="' . route('filament.admin.resources.profiles.view', $record->imported_profile_id)
                                    . '" class="text-primary-600 underline">#' . $record->imported_profile_id . '</a>'
                                )
                                : '—'),

                        Placeholder::make('error_value')
                            ->label('Poslední chyba')
                            ->columnSpanFull()
                            ->content(fn (?ScrapeItem $record) => $record?->error ?? '—'),
                    ]),
            ]);
    }

    /**
     * Scraped values the fields above do not already show.
     *
     * @return array<string, string>
     */
    private static function extraValues(?ScrapeItem $record): array
    {
        $known = [
            'display_name', 'age', 'city', 'country_code', 'address', 'about',
            'card_height_cm', 'weight_kg', 'bust_size', 'nationality',
        ];

        $values = [];

        foreach ((array) ($record?->normalized ?? []) as $key => $value) {
            if (in_array($key, $known, true)) {
                continue;
            }

            $values[$key] = is_array($value)
                ? implode(', ', array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v), $value))
                : (string) $value;
        }

        return $values;
    }
}
