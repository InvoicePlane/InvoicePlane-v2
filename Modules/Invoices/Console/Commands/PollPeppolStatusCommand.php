<?php

namespace Modules\Invoices\Console\Commands;

use Illuminate\Console\Command;
use Modules\Invoices\Jobs\Peppol\PeppolStatusPoller;

/**
 * Console command to poll Peppol transmission statuses
 * 
 * Should be scheduled to run periodically (e.g., every 15 minutes)
 * Add to schedule: $schedule->command('peppol:poll-status')->everyFifteenMinutes();
 */
class PollPeppolStatusCommand extends Command
{
    protected $signature = 'peppol:poll-status';
    protected $description = 'Poll Peppol provider for transmission status updates';

    public function handle(): int
    {
        $this->info('Starting Peppol status polling...');

        try {
            PeppolStatusPoller::dispatch();
            
            $this->info('Peppol status polling job dispatched successfully.');
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to dispatch status polling job: ' . $e->getMessage());
            
            return self::FAILURE;
        }
    }
}
