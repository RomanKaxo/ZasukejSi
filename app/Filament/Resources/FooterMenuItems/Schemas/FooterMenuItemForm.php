<?php

namespace App\Filament\Resources\FooterMenuItems\Schemas;

use App\Models\FooterMenuItem;
use App\Models\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FooterMenuItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Odkaz')
                    ->columns(2)
                    ->columnSpanFull()
                    ->components([
                        TextInput::make('label')
                            ->label('Popisek (' . strtoupper(app()->getLocale()) . ')')
                            ->helperText('Text, který se zobrazí v patičce. Nemusí se shodovat s názvem stránky.')
                            ->required()
                            ->maxLength(60)
                            ->columnSpanFull()
                            // Translatable column: edit the language you are
                            // currently in, the rest stays as it was.
                            ->afterStateHydrated(function (TextInput $component, $state, ?FooterMenuItem $record) {
                                if ($record?->exists) {
                                    $component->state($record->getTranslation('label', app()->getLocale()));
                                }
                            }),

                        Select::make('page_id')
                            ->label('Stránka')
                            ->helperText('Odkaz zůstane správný i po změně adresy stránky.')
                            ->options(fn () => Page::linkOptions())
                            ->searchable()
                            ->placeholder('Vlastní adresa níže')
                            ->live(),

                        TextInput::make('url')
                            ->label('Vlastní adresa')
                            ->helperText('Použije se, jen když není vybraná stránka. Hodí se pro odkaz mimo web.')
                            ->maxLength(255)
                            ->disabled(fn ($get) => filled($get('page_id')))
                            ->requiredWithout('page_id'),

                        Select::make('audience')
                            ->label('Komu se zobrazí')
                            ->helperText('Návrh nabízí VIP pro dívky i Premium pro pány vedle sebe. Přihlášenému má smysl ukázat jen ten jeho; nepřihlášený role nemá, takže mu patří obecná stránka VIP & Premium.')
                            ->options(fn () => FooterMenuItem::audienceOptions())
                            ->default(FooterMenuItem::AUDIENCE_ALL)
                            ->required(),

                        Toggle::make('opens_in_new_tab')
                            ->label('Otevřít v novém panelu')
                            ->default(false),
                    ]),

                Section::make('Umístění')
                    ->columns(3)
                    ->columnSpanFull()
                    ->components([
                        Select::make('column')
                            ->label('Sloupec')
                            ->helperText('Návrh počítá se třemi sloupci.')
                            ->options(array_combine(FooterMenuItem::COLUMNS, FooterMenuItem::COLUMNS))
                            ->default(1)
                            ->required(),

                        TextInput::make('sort_order')
                            ->label('Pořadí ve sloupci')
                            ->helperText('Nižší číslo je výš.')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Toggle::make('is_visible')
                            ->label('Zobrazit')
                            ->default(true),
                    ]),
            ]);
    }
}
