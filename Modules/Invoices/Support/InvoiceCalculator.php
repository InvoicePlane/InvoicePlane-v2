<?php

namespace Modules\Invoices\Support;

use App\Support\Calculators\Calculator;
use App\Support\Calculators\Interfaces\PayableInterface;

class InvoiceCalculator extends Calculator implements PayableInterface
{
    /**
     * Call the calculation methods.
     */
    public function calculate()
    {
        $this->calculateItems();
        $this->calculatePayments();
    }

    /**
     * Calculate additional properties.
     *
     * @return void
     */
    public function calculatePayments()
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
    public function setTotalPaid($totalPaid)
    {
        if ($totalPaid) {
            $this->calculatedAmount['paid'] = $totalPaid;
        } else {
            $this->calculatedAmount['paid'] = 0;
        }
    }
}
