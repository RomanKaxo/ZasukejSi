<?php

namespace App\Filament\Resources\ScrapeSources\Schemas;

use App\Models\ScrapeSource;
use App\Services\Scraping\AdapterRegistry;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ScrapeSourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Zdroj')
                ->schema([
                    TextInput::make('name')
                        ->label('Název')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),

                    TextInput::make('slug')
                        ->label('Identifikátor')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText('Používá se v příkazu: php artisan scrape:run <identifikátor>'),

                    TextInput::make('base_url')
                        ->label('Základní URL')
                        ->url()
                        ->required()
                        ->helperText('Bez koncového lomítka, např. https://www.priklad.cz'),

                    Select::make('adapter')
                        ->label('Adaptér')
                        ->options(fn () => app(AdapterRegistry::class)->options())
                        ->default('generic')
                        ->required()
                        ->helperText('Generický adaptér zvládne běžný výpis s odkazy a stránkováním.'),

                    Toggle::make('is_enabled')
                        ->label('Zapnuto')
                        ->helperText('Vypnutý zdroj lze spustit jen s --dry-run.'),
                ])
                ->columns(2),

            Section::make('Automatické spouštění')
                ->description('Bez intervalu se zdroj spouští jen ručně, jak tomu bylo dosud. Vypnutý zdroj se nespustí ani s intervalem.')
                ->schema([
                    TextInput::make('schedule_hours')
                        ->label('Interval (hodiny)')
                        ->helperText('Prázdné = žádné automatické spouštění. 24 znamená jednou denně.')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(24 * 30),

                    DateTimePicker::make('next_run_at')
                        ->label('Další běh')
                        ->helperText('Prázdné a s vyplněným intervalem znamená „hned při nejbližší kontrole".')
                        ->seconds(false),

                    TextInput::make('schedule_pages')
                        ->label('Stránek výpisu na běh')
                        ->helperText('Prázdné = použije se max_pages z nastavení zdroje.')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(200),

                    TextInput::make('schedule_limit')
                        ->label('Maximálně profilů na běh')
                        ->helperText('Prázdné = bez omezení. Stahování čeká mezi požadavky, takže velký běh trvá.')
                        ->numeric()
                        ->minValue(1),
                ])
                ->columns(2),

            Section::make('Chování při stahování')
                ->description('Prodleva se nikdy nesníží pod hodnotu, kterou požaduje robots.txt daného webu.')
                ->schema([
                    KeyValue::make('settings')
                        ->label('Nastavení')
                        ->keyLabel('Klíč')
                        ->valueLabel('Hodnota')
                        ->default(ScrapeSource::DEFAULT_SETTINGS)
                        ->addActionLabel('Přidat položku')
                        ->helperText(
                            'crawl_delay (s), timeout (s), max_pages, listing_path, pagination_param, '
                            . 'detail_link_selector, detail_url_pattern, external_id_pattern, '
                            . 'image_selector, image_attribute, image_prefer_pattern, image_limit, respect_robots, '
                            . 'user_agent, headers. — Když web začne vracet 403, mění se user_agent nebo se přidá '
                            . 'headers jako JSON, třeba {"Referer":"https://www.example.com/"}.'
                        )
                        ->columnSpanFull(),
                ]),

            Section::make('Poznámky a robots.txt')
                ->schema([
                    Textarea::make('notes')
                        ->label('Poznámky')
                        ->rows(3)
                        ->columnSpanFull(),

                    KeyValue::make('robots_rules')
                        ->label('Poslední načtený robots.txt')
                        ->disabled()
                        ->columnSpanFull(),
                ])
                ->collapsed(),
        ]);
    }
}
