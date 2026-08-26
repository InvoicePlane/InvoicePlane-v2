<?php

namespace Modules\Core\Filament\Company\Resources\NoteTemplates\Schemas;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NoteTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Template')
                    ->schema([
                        TextInput::make('template_title')
                            ->label(trans('ip.title'))
                            ->required()
                            ->maxLength(255),

                        MarkdownEditor::make('template_body')
                            ->label(trans('ip.body'))
                            ->required()
                            ->toolbarButtons([
                                'bold', 'italic', 'strike', 'link',
                                'heading',
                                'blockquote', 'codeBlock', 'bulletList', 'orderedList',
                                'undo', 'redo',
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
