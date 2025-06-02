<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices\Pages;

use Exception;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Invoices\Filament\Company\Resources\Invoices\InvoiceResource;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    public function mount($record): void
    {
        parent::mount($record);
        // Debug
        dd($this->record);
    }

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ( ! $this->record) {
            throw new Exception('No record found when loading invoice.');
        }

        $data['invoiceItems'] = $this->record->invoiceItems()->get()->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity'   => $item->quantity,
                'price'      => $item->price,
                'discount'   => $item->discount,
                'subtotal'   => $item->subtotal,
            ];
        })->toArray();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
