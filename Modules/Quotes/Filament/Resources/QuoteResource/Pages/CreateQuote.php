<?php

namespace Modules\Quotes\Filament\Resources\QuoteResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Quotes\Transformers\QuoteResource;

class CreateQuote extends CreateRecord
{
    protected static string $resource = QuoteResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
