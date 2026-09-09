<?php

namespace Modules\Quotes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\Company;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Http\Controllers\GuestQuoteController;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Services\QuoteService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(GuestQuoteController::class)]
class GuestQuoteViewTest extends AbstractTestCase
{
    use RefreshDatabase;

    private const VALID_PNG_DATA_URL = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::factory()->create();
        Storage::fake(config('filament.default_filesystem_disk'));
    }

    #[Test]
    #[Group('crud')]
    public function it_renders_the_guest_view_for_a_valid_url_key(): void
    {
        /* Arrange */
        $quote = Quote::factory()->for($this->company)->create(['quote_password' => null]);

        /* Act */
        $response = $this->get(route('quotes.guest.show', $quote));

        /* Assert */
        $response->assertSuccessful();
        $response->assertSee($quote->quote_number);
    }

    #[Test]
    #[Group('crud')]
    public function it_returns_404_for_an_unknown_url_key(): void
    {
        /* Act */
        $response = $this->get('/quotes/not-a-real-url-key');

        /* Assert */
        $response->assertNotFound();
    }

    #[Test]
    #[Group('crud')]
    public function it_shows_a_password_form_when_the_quote_is_password_protected(): void
    {
        /* Arrange */
        $quote = Quote::factory()->for($this->company)->create(['quote_password' => 'secret']);

        /* Act */
        $response = $this->get(route('quotes.guest.show', $quote));

        /* Assert */
        $response->assertSuccessful();
        $response->assertViewIs('quotes::guest.password');
    }

    #[Test]
    #[Group('crud')]
    public function it_rejects_an_incorrect_password(): void
    {
        /* Arrange */
        $quote = Quote::factory()->for($this->company)->create(['quote_password' => 'secret']);

        /* Act */
        $response = $this->from(route('quotes.guest.show', $quote))
            ->post(route('quotes.guest.password', $quote), ['password' => 'wrong']);

        /* Assert */
        $response->assertRedirect(route('quotes.guest.show', $quote));
        $response->assertSessionHasErrors('password');
    }

    #[Test]
    #[Group('crud')]
    public function it_unlocks_the_guest_view_after_a_correct_password(): void
    {
        /* Arrange */
        $quote = Quote::factory()->for($this->company)->create(['quote_password' => 'secret']);

        /* Act */
        $this->post(route('quotes.guest.password', $quote), ['password' => 'secret']);
        $response = $this->get(route('quotes.guest.show', $quote));

        /* Assert */
        $response->assertViewIs('quotes::guest.show');
    }

    #[Test]
    #[Group('crud')]
    public function it_blocks_the_pdf_download_when_password_protected_and_unverified(): void
    {
        /* Arrange */
        $quote = Quote::factory()->for($this->company)->create(['quote_password' => 'secret']);

        /* Act */
        $response = $this->get(route('quotes.guest.pdf', $quote));

        /* Assert */
        $response->assertForbidden();
    }

    #[Test]
    #[Group('crud')]
    public function it_streams_the_pdf_when_unlocked(): void
    {
        /* Arrange */
        $quote = Quote::factory()->for($this->company)->create(['quote_password' => null]);

        /* Act */
        $response = $this->get(route('quotes.guest.pdf', $quote));

        /* Assert */
        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    #[Test]
    #[Group('crud')]
    public function it_captures_a_signature_and_does_not_change_the_quote_status(): void
    {
        /* Arrange */
        $quote = Quote::factory()->for($this->company)->sent()->create(['quote_password' => null]);

        /* Act */
        $response = $this->post(route('quotes.guest.sign', $quote), [
            'signer_name'    => 'Jane Client',
            'signature_data' => self::VALID_PNG_DATA_URL,
        ]);

        /* Assert */
        $response->assertRedirect(route('quotes.guest.show', $quote));
        $this->assertDatabaseHas('quote_signatures', [
            'quote_id'    => $quote->id,
            'signer_name' => 'Jane Client',
        ]);
        $this->assertSame(QuoteStatus::SENT, $quote->fresh()->quote_status);
    }

    #[Test]
    #[Group('crud')]
    public function it_rejects_signing_an_already_signed_quote(): void
    {
        /* Arrange */
        $quote = Quote::factory()->for($this->company)->create(['quote_password' => null]);
        $this->post(route('quotes.guest.sign', $quote), [
            'signer_name'    => 'Jane Client',
            'signature_data' => self::VALID_PNG_DATA_URL,
        ]);

        /* Act */
        $response = $this->post(route('quotes.guest.sign', $quote), [
            'signer_name'    => 'Second Signer',
            'signature_data' => self::VALID_PNG_DATA_URL,
        ]);

        /* Assert */
        $response->assertForbidden();
        $this->assertSame(1, $quote->signatures()->count());
    }

    #[Test]
    #[Group('crud')]
    public function it_rejects_signing_when_password_protected_and_unverified(): void
    {
        /* Arrange */
        $quote = Quote::factory()->for($this->company)->create(['quote_password' => 'secret']);

        /* Act */
        $response = $this->post(route('quotes.guest.sign', $quote), [
            'signer_name'    => 'Jane Client',
            'signature_data' => self::VALID_PNG_DATA_URL,
        ]);

        /* Assert */
        $response->assertForbidden();
        $this->assertSame(0, $quote->signatures()->count());
    }

    #[Test]
    #[Group('crud')]
    public function it_streams_a_captured_signature_image_via_its_guest_route(): void
    {
        /* Arrange */
        $quote     = Quote::factory()->for($this->company)->create(['quote_password' => null]);
        $signature = app(QuoteService::class)->captureSignature($quote, self::VALID_PNG_DATA_URL, 'Jane Client');

        /* Act */
        $response = $this->get(route('quotes.guest.signature', [$quote, $signature]));

        /* Assert */
        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'image/png');
    }

    #[Test]
    #[Group('crud')]
    public function it_blocks_the_signature_image_when_password_protected_and_unverified(): void
    {
        /* Arrange */
        $quote     = Quote::factory()->for($this->company)->create(['quote_password' => 'secret']);
        $signature = app(QuoteService::class)->captureSignature($quote, self::VALID_PNG_DATA_URL, 'Jane Client');

        /* Act */
        $response = $this->get(route('quotes.guest.signature', [$quote, $signature]));

        /* Assert */
        $response->assertForbidden();
    }

    #[Test]
    #[Group('crud')]
    public function it_returns_404_for_a_signature_belonging_to_a_different_quote(): void
    {
        /* Arrange */
        $quote      = Quote::factory()->for($this->company)->create(['quote_password' => null]);
        $otherQuote = Quote::factory()->for($this->company)->create(['quote_password' => null]);
        $signature  = app(QuoteService::class)->captureSignature($otherQuote, self::VALID_PNG_DATA_URL, 'Jane Client');

        /* Act */
        $response = $this->get(route('quotes.guest.signature', [$quote, $signature]));

        /* Assert */
        $response->assertNotFound();
    }

    #[Test]
    #[Group('crud')]
    public function it_embeds_signature_image_guest_routes_not_local_paths_in_the_browser_preview(): void
    {
        /* Arrange */
        $quote     = Quote::factory()->for($this->company)->create(['quote_password' => null]);
        $signature = app(QuoteService::class)->captureSignature($quote, self::VALID_PNG_DATA_URL, 'Jane Client');

        /* Act */
        $response = $this->get(route('quotes.guest.show', $quote));

        /* Assert */
        $response->assertSee(route('quotes.guest.signature', [$quote, $signature]), false);
        $response->assertDontSee($signature->fresh()->signature_path);
    }
}
