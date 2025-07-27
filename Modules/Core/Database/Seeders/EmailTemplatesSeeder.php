<?php

namespace Modules\Core\Database\Seeders;

use Modules\Core\Models\EmailTemplate;

class EmailTemplatesSeeder extends \Modules\Core\Database\Seeders\AbstractSeeder
{
    public function run(?int $companyId = null): void
    {
        $templates = [
            [
                'title'   => 'invoice_sent',
                'subject' => 'New Invoice: {{ invoice.number }}',
                'body'    => "Dear {{ customer.name }},\n\nA new invoice #{{ invoice.number }} has been created for you.\n\nAmount Due: {{ invoice.total_formatted }}\nDue Date: {{ invoice.due_date_formatted }}\n\nYou can view and pay your invoice by clicking the link below:\n{{ invoice.public_url }}\n\nThank you for your business!\n\n{{ company.name }}",
            ],
            [
                'title'   => 'payment_received',
                'subject' => 'Payment Received - Invoice #{{ invoice.number }}',
                'body'    => "Dear {{ customer.name }},\n\nWe have received your payment of {{ payment.amount_formatted }}\nFor Invoice: {{ invoice.number }}\nPayment Date: {{ payment.paid_at_formatted }}\n\nThank you for your payment!\n\n{{ company.name }}",
            ],
            [
                'title'   => 'quote_sent',
                'subject' => 'New Quote: {{ quote.number }}',
                'body'    => "Dear {{ customer.name }},\n\nA new quote #{{ quote.number }} has been prepared for you.\n\nAmount: {{ quote.total_formatted }}\nValid Until: {{ quote.valid_until_formatted }}\n\nYou can view the quote by clicking the link below:\n{{ quote.public_url }}\n\nPlease let us know if you have any questions.\n\nBest regards,\n{{ company.name }}",
            ],
            [
                'title'   => 'user_invitation',
                'subject' => 'You have been invited to {{ company.name }}',
                'body'    => "Hello,\n\nYou have been invited to join {{ company.name }}.\n\nPlease click the link below to set up your account:\n{{ invitation_link }}\n\nThis invitation will expire in 7 days.\n\nIf you did not expect this invitation, you can safely ignore this email.\n\nBest regards,\n{{ company.name }}",
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::query()->firstOrCreate(
                ['title' => $template['title'], 'company_id' => $companyId],
                $template
            );
        }

        $this->command->info('Email templates seeded successfully.');
    }
}
