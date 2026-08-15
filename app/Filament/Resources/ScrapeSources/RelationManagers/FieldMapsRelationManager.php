<?php

namespace App\Filament\Resources\ScrapeSources\RelationManagers;

use App\Models\ScrapeFieldMap;
use App\Services\Scraping\ScrapeItemImporter;
use App\Services\Scraping\Transformers;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * The mapping editor: one row says "this selector on the page is that field of
 * ours", plus the transforms to run on the value.
 */
class FieldMapsRelationManager extends RelationManager
{
    protected static string $relationship = 'fieldMaps';

    protected static ?string $title = 'Mapování polí';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('target_field')
                ->label('Naše pole')
                ->required()
                ->searchable()
                ->allowHtml(false)
                ->options(self::targetFieldOptions())
                ->helperText('Pole mimo tento seznam se uloží, ale import je do profilu nepřenese.'),

            TextInput::make('selector')
                ->label('CSS selektor')
                ->required()
                ->helperText('Přijímá i XPath, pokud selektor není platné CSS.'),

            Select::make('extract')
                ->label('Co z prvku vzít')
                ->options(ScrapeFieldMap::extractOptions())
                ->default(ScrapeFieldMap::EXTRACT_TEXT)
                ->required(),

            Toggle::make('multiple')
                ->label('Více hodnot')
                ->helperText('Zapněte, když selektor vrací seznam (např. řádky tabulky).'),

            Select::make('transforms')
                ->label('Transformace')
                ->multiple()
                ->options(Transformers::options())
                ->helperText('Spouštějí se v uvedeném pořadí. Regulární výrazy se zadávají v JSON přes API nebo seeder.'),

            Toggle::make('is_required')
                ->label('Povinné')
                ->helperText('Když chybí, položka se označí jako chybná a nepůjde importovat.'),

            TextInput::make('sort_order')
                ->label('Pořadí')
                ->numeric()
                ->default(0),

            TextInput::make('note')
                ->label('Poznámka')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('target_field')
            ->columns([
                TextColumn::make('target_field')->label('Naše pole')->sortable(),
                TextColumn::make('selector')->label('Selektor')->limit(50)->tooltip(fn ($state) => $state),
                TextColumn::make('extract')->label('Zdroj hodnoty')->badge(),
                IconColumn::make('multiple')->label('Více')->boolean(),
                IconColumn::make('is_required')->label('Povinné')->boolean(),
                TextColumn::make('transforms')
                    ->label('Transformace')
                    ->formatStateUsing(fn ($state) => is_array($state)
                        ? implode(', ', array_map(fn ($t) => is_array($t) ? ($t[0] ?? '?') : $t, $state))
                        : '—'),
            ])
            ->defaultSort('sort_order')
            ->headerActions([CreateAction::make()->label('Přidat mapování')])
            ->recordActions([EditAction::make()->label('Upravit'), DeleteAction::make()->label('Smazat')])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    /**
     * The fields the importer knows how to place, plus a few that are useful
     * to capture for review even though they do not reach a Profile.
     */
    public static function targetFieldOptions(): array
    {
        $options = [];

        foreach (ScrapeItemImporter::DIRECT_FIELDS as $field) {
            $options[$field] = $field . ' (profil)';
        }

        foreach (ScrapeItemImporter::CONTENT_FIELDS as $field) {
            $options[$field] = $field . ' (parametry)';
        }

        foreach (['photo_count', 'phone', 'services', 'prices', 'source_name'] as $field) {
            $options[$field] = $field . ' (jen ke kontrole)';
        }

        return $options;
    }
}
