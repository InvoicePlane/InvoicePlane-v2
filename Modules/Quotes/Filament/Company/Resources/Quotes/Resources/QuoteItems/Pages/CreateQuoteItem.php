<?php

namespace Modules\Quotes\Filament\Company\Resources\Quotes\Resources\QuoteItems\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Quotes\Filament\Company\Resources\Quotes\Resources\QuoteItems\QuoteItemResource;

class CreateQuoteItem extends CreateRecord
{
    protected static string $resource = QuoteItemResource::class;
}
