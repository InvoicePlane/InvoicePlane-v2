<?php

namespace Modules\Invoices\Support;

use Modules\Core\Support\Calculators\Calculator;
use Modules\Core\Support\Calculators\Interfaces\PayableInterface;

class InvoiceCalculator extends Calculator implements PayableInterface
{
    /**
     * Call the calculation methods.
     */
    public function calculate(): void
    {
        $this->calculateItems();
        $this->calculatePayments();
    }

    /**
     * Calculate additional properties.
     *
     * @return void
     */
    public function calculatePayments(): void
    {
        if ( ! $this->isCanceled) {
            $this->calculatedAmount['balance'] = round($this->calculatedAmount['total'], 2) - $this->calculatedAmount['paid'];
        } else {
            $this->calculatedAmount['balance'] = 0;
        }
    }

    /**
     * Set the total paid amount.
     *
     * @param float $totalPaid
     */
    public function setTotalPaid($totalPaid): void
    {
        if ($totalPaid) {
            $this->calculatedAmount['paid'] = $totalPaid;
        } else {
            $this->calculatedAmount['paid'] = 0;
        }
    }
}
