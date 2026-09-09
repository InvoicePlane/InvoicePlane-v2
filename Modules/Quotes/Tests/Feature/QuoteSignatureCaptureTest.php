<?php

namespace Modules\Quotes\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteSignature;
use Modules\Quotes\Services\QuoteService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(QuoteService::class)]
#[CoversClass(QuoteSignature::class)]
class QuoteSignatureCaptureTest extends AbstractCompanyPanelTestCase
{
    private const VALID_PNG_DATA_URL = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

    #[Test]
    #[Group('crud')]
    public function it_stores_a_captured_signature_on_a_quote(): void
    {
        /* Arrange */
        Storage::fake(config('filament.default_filesystem_disk'));
        $quote = Quote::factory()->for($this->company)->create();

        /* Act */
        $signature = app(QuoteService::class)->captureSignature($quote, self::VALID_PNG_DATA_URL, 'Jane Client');

        /* Assert */
        Storage::disk(config('filament.default_filesystem_disk'))->assertExists($signature->signature_path);
        $this->assertDatabaseHas('quote_signatures', [
            'id'          => $signature->id,
            'quote_id'    => $quote->id,
            'company_id'  => $quote->company_id,
            'signer_name' => 'Jane Client',
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_links_a_signature_to_a_user_when_a_user_id_is_provided(): void
    {
        /* Arrange */
        Storage::fake(config('filament.default_filesystem_disk'));
        $quote = Quote::factory()->for($this->company)->create();

        /* Act */
        $signature = app(QuoteService::class)->captureSignature(
            $quote,
            self::VALID_PNG_DATA_URL,
            $this->user->name,
            $this->user->id,
            '127.0.0.1',
            'PHPUnit',
        );

        /* Assert */
        $this->assertSame($this->user->id, $signature->fresh()->user_id);
        $this->assertSame('127.0.0.1', $signature->fresh()->ip_address);
        $this->assertSame('PHPUnit', $signature->fresh()->user_agent);
    }

    #[Test]
    #[Group('crud')]
    public function it_reports_a_quote_as_unsigned_when_no_signature_has_been_captured(): void
    {
        /* Arrange */
        $quote = Quote::factory()->for($this->company)->create();

        /* Act */
        $isSigned = $quote->isSigned();

        /* Assert */
        $this->assertFalse($isSigned);
    }

    #[Test]
    #[Group('crud')]
    public function it_reports_a_quote_as_signed_after_a_signature_is_captured(): void
    {
        /* Arrange */
        Storage::fake(config('filament.default_filesystem_disk'));
        $quote = Quote::factory()->for($this->company)->create();
        app(QuoteService::class)->captureSignature($quote, self::VALID_PNG_DATA_URL, 'Jane Client');

        /* Act */
        $isSigned = $quote->isSigned();

        /* Assert */
        $this->assertTrue($isSigned);
    }

    #[Test]
    #[Group('crud')]
    public function it_does_not_change_the_quote_status_when_a_signature_is_captured(): void
    {
        /* Arrange */
        Storage::fake(config('filament.default_filesystem_disk'));
        $quote = Quote::factory()->for($this->company)->sent()->create();

        /* Act */
        app(QuoteService::class)->captureSignature($quote, self::VALID_PNG_DATA_URL, 'Jane Client');

        /* Assert */
        $this->assertSame(QuoteStatus::SENT, $quote->fresh()->quote_status);
    }

    #[Test]
    #[Group('crud')]
    public function it_throws_when_the_signature_data_is_not_a_valid_base64_image(): void
    {
        /* Arrange */
        Storage::fake(config('filament.default_filesystem_disk'));
        $quote = Quote::factory()->for($this->company)->create();

        /* Act & Assert */
        $this->expectException(InvalidArgumentException::class);
        app(QuoteService::class)->captureSignature($quote, 'not-a-data-url', 'Jane Client');
    }

    #[Test]
    #[Group('crud')]
    public function it_throws_when_the_payload_is_valid_base64_but_not_actually_an_image(): void
    {
        /* Arrange */
        Storage::fake(config('filament.default_filesystem_disk'));
        $quote      = Quote::factory()->for($this->company)->create();
        $notAnImage = 'data:image/png;base64,' . base64_encode('this is not image bytes');

        /* Act & Assert */
        $this->expectException(InvalidArgumentException::class);
        app(QuoteService::class)->captureSignature($quote, $notAnImage, 'Jane Client');
    }

    #[Test]
    #[Group('crud')]
    public function it_supports_capturing_more_than_one_signature_on_the_same_quote(): void
    {
        /* Arrange */
        Storage::fake(config('filament.default_filesystem_disk'));
        $quote   = Quote::factory()->for($this->company)->create();
        $service = app(QuoteService::class);

        /* Act */
        $service->captureSignature($quote, self::VALID_PNG_DATA_URL, 'First Signer');
        $service->captureSignature($quote, self::VALID_PNG_DATA_URL, 'Second Signer');

        /* Assert */
        $this->assertSame(2, $quote->signatures()->count());
    }

    #[Test]
    #[Group('crud')]
    public function it_truncates_an_overlong_user_agent_to_fit_the_column(): void
    {
        /* Arrange */
        Storage::fake(config('filament.default_filesystem_disk'));
        $quote     = Quote::factory()->for($this->company)->create();
        $userAgent = str_repeat('a', 500);

        /* Act */
        $signature = app(QuoteService::class)->captureSignature(
            $quote,
            self::VALID_PNG_DATA_URL,
            'Jane Client',
            userAgent: $userAgent,
        );

        /* Assert */
        $this->assertSame(255, mb_strlen($signature->fresh()->user_agent));
    }

    #[Test]
    #[Group('crud')]
    public function it_creates_a_valid_signature_via_its_factory(): void
    {
        /* Act */
        $signature = QuoteSignature::factory()->create();

        /* Assert */
        $this->assertNotNull($signature->company_id);
    }
}
