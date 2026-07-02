<?php

namespace App\Filament\Blocks;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use SkyRaptor\FilamentBlocksBuilder\Blocks\Contracts\HTMLBlock;

/**
 * This block defines a collapsible FAQ repeater component.
 */
class Faq extends HTMLBlock
{
    /**
     * @inheritDoc
     */
    public static function block(Schema $schema): Builder\Block
    {
        return parent::block($schema)->schema([
            TextInput::make('heading')
                ->label('Section Heading')
                ->placeholder('Frequently Asked Questions')
                ->maxLength(255),
            
            Repeater::make('items')
                ->label('FAQ Items')
                ->schema([
                    TextInput::make('question')
                        ->label('Question')
                        ->required()
                        ->maxLength(500)
                        ->columnSpanFull(),
                    
                    Textarea::make('answer')
                        ->label('Answer')
                        ->required()
                        ->rows(4)
                        ->columnSpanFull(),
                ])
                ->defaultItems(1)
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                ->addActionLabel('Add FAQ Item')
                ->reorderable()
                ->columnSpanFull(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function view(): ?string
    {
        return 'blocks.faq';
    }
}
