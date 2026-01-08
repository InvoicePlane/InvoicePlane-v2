<?php

namespace Modules\Products\Filament\Company\Resources\Products\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Products\Filament\Company\Resources\Products\ProductResource;
use Modules\Products\Services\ProductService;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(ProductService::class)->updateProduct($record, $data);
    }
}
