<?php

namespace Modules\Quotes\Filament\Resources\QuoteResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\Quotes\Transformers\QuoteResource;

class EditQuote extends Page
{
    protected static string $resource = QuoteResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
