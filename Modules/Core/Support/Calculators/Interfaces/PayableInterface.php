<?php

namespace Modules\Core\Support\Calculators\Interfaces;

use Modules\Core\Support\Calculators\Interfaces\PayableInterface;

interface PayableInterface
{
    /**
     * Set the total paid amount.
     *
     * @param float $totalPaid
     */
    public function setTotalPaid($totalPaid);

    /**
     * Calculate additional properties.
     *
     * @return void
     */
    public function calculatePayments(): void;
}
