<?php

namespace Modules\Invoices\Events\Peppol;

use Modules\Clients\Models\Relation;

class PeppolIdValidationCompleted extends PeppolEvent
{
    public Relation $customer;
    public string $validationStatus;

    public function __construct(Relation $customer, string $validationStatus, array $details = [])
    {
        $this->customer = $customer;
        $this->validationStatus = $validationStatus;
        
        parent::__construct(array_merge([
            'customer_id' => $customer->id,
            'peppol_id' => $customer->peppol_id,
            'peppol_scheme' => $customer->peppol_scheme,
            'validation_status' => $validationStatus,
        ], $details));
    }

    public function getEventName(): string
    {
        return 'peppol.id_validation.completed';
    }
}
