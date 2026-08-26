<?php

namespace Modules\Quotes\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Services\QuoteService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(QuoteService::class)]
class QuotePdfSignatureTest extends AbstractCompanyPanelTestCase
{
    private const VALID_PNG_DATA_URL = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

    #[Test]
    #[Group('crud')]
    public function it_does_not_render_a_signature_block_when_the_quote_is_unsigned(): void
    {
        /* Arrange */
        $quote = Quote::factory()->for($this->company)->create();

        /* Act */
        $html = app(QuoteService::class)->renderHtml($quote);

        /* Assert */
        $this->assertStringNotContainsString(trans('ip.quote_signatures'), $html);
    }

    #[Test]
    #[Group('crud')]
    public function it_renders_the_captured_signature_image_and_signer_name_on_the_pdf(): void
    {
        /* Arrange */
        Storage::fake(config('filament.default_filesystem_disk'));
        $quote   = Quote::factory()->for($this->company)->create();
        $service = app(QuoteService::class);
        $service->captureSignature($quote, self::VALID_PNG_DATA_URL, 'Jane Client');

        /* Act */
        $html = $service->renderHtml($quote->fresh());

        /* Assert */
        $this->assertStringContainsString(trans('ip.quote_signatures'), $html);
        $this->assertStringContainsString('Jane Client', $html);
        $this->assertStringContainsString('<img src=', $html);
    }

    #[Test]
    #[Group('crud')]
    public function it_renders_a_block_per_signature_when_a_quote_has_multiple_signers(): void
    {
        /* Arrange */
        Storage::fake(config('filament.default_filesystem_disk'));
        $quote   = Quote::factory()->for($this->company)->create();
        $service = app(QuoteService::class);
        $service->captureSignature($quote, self::VALID_PNG_DATA_URL, 'First Signer');
        $service->captureSignature($quote, self::VALID_PNG_DATA_URL, 'Second Signer');

        /* Act */
        $html = $service->renderHtml($quote->fresh());

        /* Assert */
        $this->assertStringContainsString('First Signer', $html);
        $this->assertStringContainsString('Second Signer', $html);
    }

    #[Test]
    #[Group('crud')]
    public function it_skips_a_signature_whose_stored_file_is_missing(): void
    {
        /* Arrange */
        Storage::fake(config('filament.default_filesystem_disk'));
        $quote   = Quote::factory()->for($this->company)->create();
        $service = app(QuoteService::class);
        $signature = $service->captureSignature($quote, self::VALID_PNG_DATA_URL, 'Jane Client');
        Storage::disk($signature->signature_disk)->delete($signature->signature_path);

        /* Act */
        $html = $service->renderHtml($quote->fresh());

        /* Assert */
        $this->assertStringNotContainsString(trans('ip.quote_signatures'), $html);
    }
}
