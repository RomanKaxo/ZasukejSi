<?php

namespace App\Filament\Resources\Blogs\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BlogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('header_image')
                    ->label(__('blogs.table.header_image'))
                    ->collection('header-image')
                    ->size(80)
                    ->circular(false),
                TextColumn::make('title')
                    ->label(__('blogs.table.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('description')
                    ->label(__('blogs.table.description'))
                    ->limit(60)
                    ->toggleable(),
                TextColumn::make('slug')
                    ->label(__('blogs.table.slug'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_published')
                    ->label(__('blogs.table.is_published'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('blogs.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('blogs.table.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                TernaryFilter::make('is_published')
                    ->label(__('blogs.table.filter_published')),
            ])
            ->recordActions([
                // Publishing meant opening the post, finding a toggle and
                // saving — three steps for a yes/no decision.
                Action::make('togglePublished')
                    ->label(fn ($record) => $record->is_published
                        ? __('blogs.table.unpublish')
                        : __('blogs.table.publish'))
                    ->icon(fn ($record) => $record->is_published ? 'heroicon-o-eye-slash' : 'heroicon-o-globe-alt')
                    ->color(fn ($record) => $record->is_published ? 'gray' : 'success')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['is_published' => ! $record->is_published])),

                Action::make('open')
                    ->label(__('blogs.table.open'))
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => $record->slug ? url('/' . ltrim($record->slug, '/')) : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->is_published && filled($record->slug)),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
