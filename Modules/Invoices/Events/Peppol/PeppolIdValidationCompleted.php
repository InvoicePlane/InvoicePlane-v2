<?php

namespace Modules\Invoices\Events\Peppol;

use Modules\Clients\Models\Relation;

class PeppolIdValidationCompleted extends PeppolEvent
{
    public Relation $customer;
    public string $validationStatus;

    /**
     * Create a PeppolIdValidationCompleted event for a customer's PEPPOL ID validation.
     *
     * The event is initialized with default detail fields (`customer_id`, `peppol_id`, `peppol_scheme`, `validation_status`)
     * which are merged with any provided additional details.
     *
     * @param Relation $customer The customer Relation associated with the validation.
     * @param string $validationStatus The resulting validation status.
     * @param array $details Additional event detail key-value pairs to merge into the default details.
     */
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

    /**
     * Get the event's canonical name.
     *
     * @return string The event name 'peppol.id_validation.completed'.
     */
    public function getEventName(): string
    {
        return 'peppol.id_validation.completed';
    }
}