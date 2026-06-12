<?php

namespace Modules\Quotes\Filament\Company\Resources\Quotes\Resources\QuoteItems\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Quotes\Filament\Company\Resources\Quotes\Resources\QuoteItems\QuoteItemResource;

class EditQuoteItem extends EditRecord
{
    protected static string $resource = QuoteItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
