<?php

namespace Modules\Core\Filament\Company\Resources\NoteTemplates\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Company\Resources\NoteTemplates\NoteTemplateResource;

class ListNoteTemplates extends ListRecords
{
    protected static string $resource = NoteTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->url(fn (): string => NoteTemplateResource::getUrl('create')),
        ];
    }
}
