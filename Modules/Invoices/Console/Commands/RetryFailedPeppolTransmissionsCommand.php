<?php

namespace Modules\Invoices\Console\Commands;

use Illuminate\Console\Command;
use Modules\Invoices\Jobs\Peppol\RetryFailedTransmissions;

/**
 * Console command to retry failed Peppol transmissions
 * 
 * Should be scheduled to run frequently (e.g., every minute)
 * Add to schedule: $schedule->command('peppol:retry-failed')->everyMinute();
 */
class RetryFailedPeppolTransmissionsCommand extends Command
{
    protected $signature = 'peppol:retry-failed';
    protected $description = 'Retry failed Peppol transmissions that are ready for retry';

    public function handle(): int
    {
        $this->info('Starting retry of failed Peppol transmissions...');

        try {
            RetryFailedTransmissions::dispatch();
            
            $this->info('Retry job dispatched successfully.');
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to dispatch retry job: ' . $e->getMessage());
            
            return self::FAILURE;
        }
    }
}
