<?php

namespace App\Filament\Resources\Segments;

use App\Filament\Resources\Segments\Pages\CreateSegment;
use App\Filament\Resources\Segments\Pages\EditSegment;
use App\Filament\Resources\Segments\Pages\ListSegments;
use App\Filament\Resources\Segments\Schemas\SegmentForm;
use App\Filament\Resources\Segments\Tables\SegmentsTable;
use App\Models\Segment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SegmentResource extends Resource
{
    protected static ?string $model = Segment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 21;

    public static function getNavigationLabel(): string
    {
        return __('filament.navigation.segments');
    }

    public static function getModelLabel(): string
    {
        return __('common.Segment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('common.Segments');
    }

    public static function form(Schema $schema): Schema
    {
        return SegmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SegmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSegments::route('/'),
            'create' => CreateSegment::route('/create'),
            'edit' => EditSegment::route('/{record}/edit'),
        ];
    }
}
