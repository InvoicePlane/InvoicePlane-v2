<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices\Pages;

use Exception;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Invoices\Filament\Company\Resources\Invoices\InvoiceResource;
use Modules\Invoices\Models\Invoice;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    public function mount($record): void
    {
        parent::mount($record);
    }

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $invoice = $this->record;

        if ( ! $invoice instanceof Invoice) {
            throw new Exception('No valid Invoice record.');
        }

        $data['invoiceItems'] = $invoice->invoiceItems()
            ->get(['product_id', 'quantity', 'price', 'discount', 'subtotal'])
            ->toArray();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
