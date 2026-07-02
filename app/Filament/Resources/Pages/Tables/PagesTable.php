<?php

namespace App\Filament\Resources\Pages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('pages.table.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('pages.table.slug'))
                    ->searchable(),
                IconColumn::make('display_in_menu')
                    ->label(__('pages.table.display_in_menu'))
                    ->boolean(),
                IconColumn::make('display_in_footer')
                    ->label(__('pages.table.display_in_footer'))
                    ->boolean(),
                IconColumn::make('is_published')
                    ->label(__('pages.table.is_published'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('pages.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('pages.table.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('display_in_menu')
                    ->label(__('pages.table.filter_menu')),
                TernaryFilter::make('display_in_footer')
                    ->label(__('pages.table.filter_footer')),
                TernaryFilter::make('is_published')
                    ->label(__('pages.table.filter_published')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
