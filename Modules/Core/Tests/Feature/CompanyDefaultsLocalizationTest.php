<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Company;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Services\CompanyDefaultsBootstrapService;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class CompanyDefaultsLocalizationTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_email_defaults_in_the_current_locale(): void
    {
        app()->setLocale('de');
        app('translator')->addLines([
            'mail_defaults.invoice_sent_subject'     => 'Rechnung #{{ invoice.number }}',
            'mail_defaults.invoice_sent_body'        => 'Ihre Rechnung #{{ invoice.number }} ist beigefügt.',
            'mail_defaults.invoice_reminder_subject' => 'Zahlungserinnerung #{{ invoice.number }}',
            'mail_defaults.invoice_reminder_body'    => 'Guten Tag {{ customer.name }}, Rechnung {{ invoice.number }} über {{ invoice.total_formatted }} war am {{ invoice.due_date_formatted }} fällig. {{ company.name }}',
        ], 'de');

        $company   = Company::factory()->create();
        $templates = $company->email_templates()->get()->keyBy('title');

        $this->assertSame('Rechnung #{{ invoice.number }}', $templates['invoice_sent']->subject);
        $this->assertSame('Ihre Rechnung #{{ invoice.number }} ist beigefügt.', $templates['invoice_sent']->body);
        $this->assertSame('Zahlungserinnerung #{{ invoice.number }}', $templates['invoice_reminder']->subject);
        $this->assertSame('Guten Tag {{ customer.name }}, Rechnung {{ invoice.number }} über {{ invoice.total_formatted }} war am {{ invoice.due_date_formatted }} fällig. {{ company.name }}', $templates['invoice_reminder']->body);
    }

    #[Test]
    public function it_preserves_the_english_defaults_for_english_company_creation(): void
    {
        app()->setLocale('en');

        $company   = Company::factory()->create();
        $templates = $company->email_templates()->get()->keyBy('title');

        $this->assertSame('Invoice #{{ invoice.number }}', $templates['invoice_sent']->subject);
        $this->assertSame('Please find your invoice #{{ invoice.number }} attached.', $templates['invoice_sent']->body);
        $this->assertSame('Payment reminder — Invoice #{{ invoice.number }}', $templates['invoice_reminder']->subject);
        $this->assertSame("Dear {{ customer.name }},\n\nThis is a friendly reminder that invoice #{{ invoice.number }} for {{ invoice.total_formatted }} was due on {{ invoice.due_date_formatted }} and remains unpaid.\n\nPlease arrange payment at your earliest convenience. The invoice is attached for your reference.\n\nThank you,\n{{ company.name }}", $templates['invoice_reminder']->body);
    }

    #[Test]
    public function it_keeps_existing_custom_templates_when_bootstrapping_in_another_locale(): void
    {
        app()->setLocale('en');
        $company = Company::factory()->create();
        foreach (['invoice_sent', 'invoice_reminder', 'quote_sent'] as $title) {
            $company->email_templates()->where('title', $title)->update([
                'subject'    => 'Custom subject for ' . $title,
                'body'       => 'Custom body {{ company.name }}',
                'from_name'  => 'Custom sender',
                'from_email' => 'custom@example.test',
                'cc'         => 'copy@example.test',
                'bcc'        => 'hidden@example.test',
            ]);
        }
        $before = $company->email_templates()->orderBy('id')->get()->toArray();

        app()->setLocale('de');
        CompanyDefaultsBootstrapService::bootstrap($company->id);

        $this->assertSame($before, $company->email_templates()->orderBy('id')->get()->toArray());
        $this->assertSame(3, EmailTemplate::query()->where('company_id', $company->id)->count());
    }

    #[Test]
    public function it_keeps_existing_unmodified_defaults_when_the_locale_changes(): void
    {
        app()->setLocale('en');
        $company = Company::factory()->create();
        $before  = $company->email_templates()->orderBy('id')->get()->toArray();

        app()->setLocale('de');
        CompanyDefaultsBootstrapService::bootstrap($company->id);

        $this->assertSame($before, $company->email_templates()->orderBy('id')->get()->toArray());
    }
}
