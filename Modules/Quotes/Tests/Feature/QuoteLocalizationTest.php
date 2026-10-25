<?php

namespace Modules\Quotes\Tests\Feature;

use Modules\Clients\Models\Relation;
use Modules\Core\Models\Setting;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Quotes\Mail\QuoteMailable;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteItem;
use Modules\Quotes\Services\QuoteService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('crud')]
class QuoteLocalizationTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    public function it_renders_customer_language_and_formats_and_restores_the_operator_locale(): void
    {
        app('translator')->addLines(['ip.quote' => 'Angebot Test'], 'de');
        $customer = Relation::factory()->for($this->company)->create(['language' => 'de']);
        $quote    = Quote::factory()->for($this->company)->create(['prospect_id' => $customer->id, 'quoted_at' => '2026-10-25']);
        QuoteItem::factory()->create(['company_id' => $this->company->id, 'quote_id' => $quote->id, 'quantity' => 1.25, 'price' => 1234.5]);
        app()->setLocale('en');
        $attributes = $quote->getAttributes();
        $html       = app(QuoteService::class)->renderHtml($quote);
        $this->assertStringContainsString('Angebot Test', $html);
        $this->assertStringContainsString('25.10.2026', $html);
        $this->assertStringContainsString('1.234,50', $html);
        $this->assertStringContainsString('1,25', $html);
        $this->assertSame('en', app()->getLocale());
        $this->assertSame($attributes, $quote->getAttributes());

        Setting::saveByKey(Setting::KEY_DATE_FORMAT, 'd/m/Y');
        Setting::saveForCompany($this->company->id, Setting::KEY_DATE_FORMAT, '');
        $this->assertStringContainsString('25/10/2026', app(QuoteService::class)->renderHtml($quote));

        Setting::saveForCompany($this->company->id, Setting::KEY_DATE_FORMAT, 'd/m/Y');
        $this->assertStringContainsString('25/10/2026', app(QuoteService::class)->renderHtml($quote));
    }

    #[Test]
    public function it_keeps_the_customer_locale_when_mail_is_rendered_by_a_worker(): void
    {
        app('translator')->addLines(['ip.view_and_sign_quote' => 'Angebot prüfen Test'], 'de');
        $customer = Relation::factory()->for($this->company)->create(['language' => 'de']);
        $quote    = Quote::factory()->for($this->company)->create(['prospect_id' => $customer->id]);
        $mail     = new QuoteMailable($quote, 'Custom subject', 'Custom body');
        app()->setLocale('en');
        $html = $mail->render();
        $this->assertStringContainsString('Angebot prüfen Test', $html);
        $this->assertStringContainsString('Custom body', $html);
        $this->assertSame('en', app()->getLocale());
    }

    #[Test]
    public function it_uses_the_recipient_language_on_public_pages_and_password_errors(): void
    {
        $this->withoutVite();
        app('translator')->addLines(['ip.quote_password' => 'Angebotskennwort Test', 'ip.quote_password_incorrect' => 'Kennwort falsch Test'], 'de');
        $customer = Relation::factory()->for($this->company)->create(['language' => 'de']);
        $quote    = Quote::factory()->for($this->company)->create(['prospect_id' => $customer->id, 'quote_password' => 'secret']);
        app()->setLocale('en');
        $this->get(route('quotes.guest.show', $quote))->assertOk()->assertSee('lang="de"', false)->assertSee('Angebotskennwort Test');
        $this->assertSame('en', app()->getLocale());
        $this->post(route('quotes.guest.password', $quote), ['password' => 'wrong'])
            ->assertSessionHasErrors(['password' => 'Kennwort falsch Test']);
        $this->assertSame('en', app()->getLocale());
    }

    #[Test]
    public function public_form_validation_translates_attribute_names(): void
    {
        $this->withExceptionHandling();
        app('translator')->addLines(['ip.password' => 'Passwort Test', 'ip.signer_name' => 'Name Test', 'ip.signature' => 'Unterschrift Test', 'validation.required' => ':attribute fehlt', 'validation.max.string' => ':attribute zu lang'], 'de');
        $customer = Relation::factory()->for($this->company)->create(['language' => 'de']);
        $quote    = Quote::factory()->for($this->company)->create(['prospect_id' => $customer->id, 'quote_password' => null]);
        app()->setLocale('en');
        $this->post(route('quotes.guest.password', $quote), [])->assertSessionHasErrors(['password' => 'Passwort Test fehlt']);
        $this->post(route('quotes.guest.sign', $quote), ['signer_name' => str_repeat('a', 256)])
            ->assertSessionHasErrors(['signer_name' => 'Name Test zu lang', 'signature_data' => 'Unterschrift Test fehlt']);
        $this->assertSame('en', app()->getLocale());
    }
}
