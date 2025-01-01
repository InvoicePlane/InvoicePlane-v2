<?php

namespace Modules\Core\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Payments\Models\PaymentMethod;
use Modules\Quotes\Models\Quote;

class GuestTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    // endregion

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region CRUD Tests

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function route_to_guest_invoice_with_invalid_token(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        Invoice::factory()->sent()->create();
        $this->expectException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $response = $this->actingAs(user: $user, guard: 'web')->get(route('guest.invoice_view', ['invoice_url_key' => '123']));
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function test_route_to_guest_invoice_with_valid_token(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $user = User::factory()->create();
        $invoice = Invoice::factory()->sent()->create();

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('guest.invoice_view', ['invoice_url_key' => $invoice->invoice_url_key]));
        $response->assertSuccessful();
        $response->assertSee($invoice->invoice_number);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function test_route_to_guest_invoice_with_draft_status_and_valid_token(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $user = User::factory()->create();
        $invoice = Invoice::factory()->draft()->create();
        $this->expectException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $response = $this->actingAs(user: $user, guard: 'web')->get(route('guest.invoice_view', ['invoice_url_key' => $invoice->invoice_url_key]));
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function test_route_to_guest_quote_with_invalid_token(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $user = User::factory()->create();
        Quote::factory()->sent()->create();
        $this->expectException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $response = $this->actingAs(user: $user, guard: 'web')->get(route('guest.quote_view', ['quote_url_key' => '123']));
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function test_route_to_guest_quote_with_valid_token(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $user = User::factory()->create();
        $quote = Quote::factory()->sent()->create();

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('guest.quote_view', ['quote_url_key' => $quote->quote_url_key]));
        $response->assertSuccessful();
        $response->assertSee($quote->quote_number);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function test_route_to_guest_quote_with_draft_status_and_valid_token(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $user = User::factory()->create();
        $quote = Quote::factory()->draft()->create();
        $this->expectException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $response = $this->actingAs(user: $user, guard: 'web')->get(route('guest.quote_view', ['quote_url_key' => $quote->quote_url_key]));
    }

    public function test_guest_index_draft_invoice_and_quote(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $user = User::factory()->create();
        Quote::factory()->draft()->create([
            'notes'     => '::guest_draft_quote::',
            'client_id' => 1,
        ]);

        Quote::factory()->sent()->create([
            'notes'     => '::guest_sent_quote::',
            'client_id' => 1,
        ]);

        Invoice::factory()->draft()->create([
            'invoice_terms' => '::guest_draft_invoice::',
            'client_id'     => 1,
        ]);

        Invoice::factory()->sent()->create([
            'invoice_terms' => '::guest_sent_invoice::',
            'client_id'     => 1,
        ]);

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('guest.index'));
        $response->assertSuccessful();
        $response->assertSee('::guest_sent_quote::');
        $response->assertDontSee('::guest_draft_quote::');
        $response->assertSee('::guest_sent_invoice::');
        $response->assertDontSee('::guest_draft_invoice::');
    }

    public function test_guest_index_overdue_invoices(): void
    {
        $user = User::factory()->create();
        InvoiceGroup::factory()->count(1)->create();
        PaymentMethod::factory()->count(1)->create();
        Invoice::factory()->sent()->create([
            'invoice_date_due' => '1999-01-01',
            'client_id'        => 1,
            'invoice_terms'    => '::guest_sent_overdue_invoice::',
        ]);

        $response = $this->actingAs(
            user: $user,
            guard: 'web'
        )->get(
            route(
                'guest.index'
            )
        );
        $response->assertSuccessful();
        $response->assertSee('::guest_sent_overdue_invoice::');
    }
}
