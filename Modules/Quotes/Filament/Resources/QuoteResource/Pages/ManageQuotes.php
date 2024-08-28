<?php

namespace Modules\Quotes\Filament\Resources\QuoteResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Quotes\Filament\Resources\QuoteResource;

class ManageQuotes extends ManageRecords
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
