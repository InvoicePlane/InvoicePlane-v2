<?php

namespace Modules\Products\Filament\Company\Resources\ProductCategoryResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Products\Filament\Company\Resources\ProductCategoryResource;
use Modules\Products\Services\ProductCategoryService;

class EditProductCategory extends EditRecord
{
    protected static string $resource = ProductCategoryResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(ProductCategoryService::class)->updateProductCategory($record, $data);
    }
}
