<?php

namespace Modules\Core\Database\Factories;

use Modules\Core\Models\Company;
use Modules\Core\Models\MailQueue;
use Modules\Invoices\Models\Invoice;

class MailQueueFactory extends AbstractFactory
{
    protected $model = MailQueue::class;

    public function definition(): array
    {
        $company  = $this->resolveCompany() ?? Company::factory()->create();
        $mailable = Invoice::factory()->for($company)->create();

        return [
            'mailable_id'   => $mailable->id,
            'mailable_type' => $mailable->getMorphClass(),
            'from'          => fake()->safeEmail(),
            'to'            => fake()->safeEmail(),
            'cc'            => '',
            'bcc'           => '',
            'subject'       => fake()->sentence(),
            'body'          => fake()->paragraph(),
            'attach_pdf'    => fake()->boolean(),
            'is_sent'       => false,
            'error'         => null,
        ];
    }
}
