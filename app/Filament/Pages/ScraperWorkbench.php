<?php

namespace App\Filament\Pages;

use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use App\Services\Scraping\FieldExtractor;
use App\Services\Scraping\HttpFetcher;
use App\Services\Scraping\PageSnapshots;
use App\Services\Scraping\SiteProbe;
use App\Services\Scraping\StructuredData;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Throwable;

/**
 * Try a site, and try the selectors, without harvesting anything.
 *
 * Setting a source up used to be archaeology: open the page, read the markup,
 * guess a selector, start a trial run, read the log, guess again. Every one of
 * those loops cost a full run against somebody else's server, and the answer
 * came back as counts — „nalezeno 0" — which does not say whether the selector
 * is wrong, the pagination is wrong, or the site refused us.
 *
 * Here one page is fetched once and every question is answered against it: how
 * the site is built, what it publishes about itself, and what each field map
 * actually returns. Nothing is written, nothing is queued.
 */
class ScraperWorkbench extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?int $navigationSort = 42;

    protected string $view = 'filament.pages.scraper-workbench';

    /** @var array<string, mixed> */
    public array $data = [];

    /** @var array<string, mixed>|null */
    public ?array $probe = null;

    /** @var array<int, array<string, mixed>>|null */
    public ?array $selectors = null;

    public ?string $error = null;

    /** Odkud se vzalo HTML posledního pokusu — kvůli popisku ve výsledku. */
    public ?string $lastSourceLabel = null;

    public static function getNavigationGroup(): ?string
    {
        return 'Scraper';
    }

    public static function getNavigationLabel(): string
    {
        return 'Dílna';
    }

    public function getTitle(): string
    {
        return 'Dílna scraperu';
    }

    public function mount(): void
    {
        $this->form->fill([
            'scrape_source_id' => ScrapeSource::query()->orderBy('name')->value('id'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Co zkoumáme')
                    ->description('Stáhne se jedna stránka a nic víc. Nic se neukládá do fronty ani se nepublikuje.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('scrape_source_id')
                            ->label('Zdroj')
                            ->options(fn () => ScrapeSource::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->required()
                            ->searchable()
                            ->helperText('Použijí se jeho nastavení: hlavičky, prodleva, robots.txt.'),

                        TextInput::make('url')
                            ->label('Adresa stránky')
                            ->url()
                            ->helperText('Prázdné = výpis podle nastavení zdroje. Pro zkoušení selektorů zadejte adresu jednoho profilu.'),

                        Toggle::make('use_snapshot')
                            ->label('Použít uloženou stránku')
                            ->default(true)
                            ->helperText('Zkouší selektory na stránce, kterou scraper naposledy stáhl. Žádný dotaz na cizí web — a funguje to i na webu, který nás zrovna odmítá.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /** Co web nabízí: robots, sitemapa, tvary odkazů, strukturovaná data. */
    public function probe(): void
    {
        $this->reset(['probe', 'selectors', 'error']);

        $source = $this->source();

        if ($source === null) {
            return;
        }

        try {
            $this->probe = app(SiteProbe::class)->run($source, $this->url());
        } catch (Throwable $e) {
            $this->fail($e);
        }
    }

    /** Co jednotlivá mapování polí na téhle stránce doopravdy vrátí. */
    public function testSelectors(): void
    {
        $this->reset(['probe', 'selectors', 'error']);

        $source = $this->source();

        if ($source === null) {
            return;
        }

        $url = $this->url() ?: $source->base_url;
        $useSnapshot = (bool) ($this->form->getState()['use_snapshot'] ?? true);

        $html = $useSnapshot ? $this->snapshotFor($source, $url) : null;

        if ($html === null) {
            try {
                $html = app(HttpFetcher::class)->get($source, $url);
                $this->lastSourceLabel = 'staženo právě teď z ' . $url;
            } catch (Throwable $e) {
                $this->fail($e);

                return;
            }
        }

        $extractor = app(FieldExtractor::class);
        $xpath = $extractor->xpathFor($html);
        $rows = [];

        foreach ($source->fieldMaps as $map) {
            try {
                $raw = StructuredData::handles($map->selector)
                    ? $extractor->selectStructured($html, $map)
                    : $extractor->select($xpath, $map);
            } catch (Throwable $e) {
                $rows[] = [
                    'field' => $map->target_field,
                    'selector' => $map->selector,
                    'raw' => null,
                    'value' => null,
                    'error' => $e->getMessage(),
                    'required' => (bool) $map->is_required,
                ];

                continue;
            }

            // Raw and transformed side by side: a selector that found the
            // right node and a transform that then threw the value away look
            // identical when only the result is shown.
            $value = app(\App\Services\Scraping\Transformers::class)
                ->apply($raw, $map->transforms ?? [], ['base_url' => $source->base_url]);

            $rows[] = [
                'field' => $map->target_field,
                'selector' => $map->selector,
                'raw' => $this->preview($raw),
                'value' => $this->preview($value),
                'error' => null,
                'required' => (bool) $map->is_required,
            ];
        }

        $this->selectors = $rows;

        if ($rows === []) {
            Notification::make()
                ->title('Zdroj nemá žádná mapování polí')
                ->body('Přidejte je u zdroje v záložce Mapování polí.')
                ->warning()
                ->send();
        }
    }

    /**
     * Stránka, kterou scraper u téhle adresy naposledy uložil.
     *
     * Je to přesně to, co viděl extraktor — už převedené do UTF-8 — takže
     * zkouška nad ní je tentýž pokus jako naživo, jen bez požadavku.
     */
    private function snapshotFor(ScrapeSource $source, string $url): ?string
    {
        $item = ScrapeItem::query()
            ->where('scrape_source_id', $source->id)
            ->where('source_url', $url)
            ->latest('updated_at')
            ->first();

        if ($item === null) {
            return null;
        }

        $html = app(PageSnapshots::class)->get($item);

        if ($html !== null) {
            $this->lastSourceLabel = 'z uložené stránky (položka #' . $item->id
                . ', staženo ' . $item->updated_at->format('j. n. Y H:i') . ')';
        }

        return $html;
    }

    private function source(): ?ScrapeSource
    {
        $id = $this->form->getState()['scrape_source_id'] ?? null;

        $source = $id ? ScrapeSource::with('fieldMaps')->find($id) : null;

        if ($source === null) {
            Notification::make()->title('Vyberte zdroj')->warning()->send();
        }

        return $source;
    }

    private function url(): ?string
    {
        $url = trim((string) ($this->form->getState()['url'] ?? ''));

        return $url === '' ? null : $url;
    }

    private function fail(Throwable $e): void
    {
        $this->error = $e->getMessage();

        Notification::make()
            ->title('Stránku se nepodařilo načíst')
            ->body($e->getMessage())
            ->danger()
            ->persistent()
            ->send();
    }

    /** A value shortened for a table cell, without losing what shape it is. */
    private function preview(mixed $value): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (is_array($value)) {
            $shown = array_slice(array_map(fn ($item) => is_scalar($item) ? (string) $item : '…', $value), 0, 5);
            $suffix = count($value) > 5 ? ' … (celkem ' . count($value) . ')' : '';

            return implode(' · ', $shown) . $suffix;
        }

        if (is_bool($value)) {
            return $value ? 'ano' : 'ne';
        }

        $text = (string) $value;

        return mb_strlen($text) > 300 ? mb_substr($text, 0, 300) . '…' : $text;
    }
}
