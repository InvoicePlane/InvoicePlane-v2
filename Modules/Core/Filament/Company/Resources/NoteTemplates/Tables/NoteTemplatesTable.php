<?php

namespace Modules\Core\Filament\Company\Resources\NoteTemplates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Filament\Company\Resources\NoteTemplates\NoteTemplateResource;
use Modules\Core\Models\NoteTemplate;
use Modules\Core\Services\NoteTemplateService;

class NoteTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('template_title')
                    ->label(trans('ip.title'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('template_body')
                    ->label(trans('ip.body'))
                    ->limit(50)
                    ->searchable(),
            ])
            ->defaultSort('template_title', 'asc')
            ->recordActions([
                EditAction::make()
                    ->url(fn (NoteTemplate $record): string => NoteTemplateResource::getUrl('edit', [
                        'record' => $record,
                    ])),
                DeleteAction::make()
                    ->action(function (NoteTemplate $record, array $data) {
                        app(NoteTemplateService::class)->deleteNoteTemplate($record, $data);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
