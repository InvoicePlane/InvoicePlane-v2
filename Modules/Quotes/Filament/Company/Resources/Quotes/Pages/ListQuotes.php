<?php

namespace Modules\Quotes\Filament\Company\Resources\Quotes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Quotes\Filament\Company\Resources\Quotes\QuoteResource;

class ListQuotes extends ListRecords
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->url(fn (): string => QuoteResource::getUrl('create')),
        ];
    }
}
