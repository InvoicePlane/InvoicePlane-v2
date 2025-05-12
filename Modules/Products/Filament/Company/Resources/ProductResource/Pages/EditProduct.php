<?php

namespace Modules\Products\Filament\Company\Resources\ProductResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Products\Filament\Company\Resources\ProductResource;
use Modules\Products\Services\ProductService;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(ProductService::class)->updateProduct($record, $data);
    }
}
