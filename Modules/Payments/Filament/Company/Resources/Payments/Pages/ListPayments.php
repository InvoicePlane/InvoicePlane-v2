<?php

namespace Modules\Payments\Filament\Company\Resources\Payments\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Payments\Filament\Company\Resources\Payments\PaymentResource;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(\Modules\Payments\Services\PaymentService::class)->createPayment($data);
                })
                ->modalWidth('full'),
        ];
    }
}
