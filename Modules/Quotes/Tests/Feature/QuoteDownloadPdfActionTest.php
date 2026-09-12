<?php

namespace Modules\Quotes\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Mockery;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Seeders\PermissionsSeeder;
use Modules\Core\Database\Seeders\RolesSeeder;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Enums\Permission;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Numbering;
use Modules\Core\Services\PdfGenerationService;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Filament\Company\Resources\Quotes\Pages\ListQuotes;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class QuoteDownloadPdfActionTest extends AbstractCompanyPanelTestCase
{
    protected Relation $prospect;

    protected Numbering $numbering;

    protected function setUp(): void
    {
        parent::setUp();

        (new PermissionsSeeder())->run();
        (new RolesSeeder())->run();
        $this->user->assignRole(UserRole::CUSTOMER_ADMIN->value);

        /** @var Relation $prospect */
        $prospect       = Relation::factory()->for($this->company)->prospect()->create();
        $this->prospect = $prospect;

        /** @var Numbering $numbering */
        $numbering = Numbering::factory()
            ->for($this->company)
            ->state(['type' => NumberingType::QUOTE->value])
            ->create();
        $this->numbering = $numbering;
    }

    #[Test]
    public function it_downloads_quote_pdf_for_permitted_user(): void
    {
        /* Arrange */
        $quote = $this->createQuote(['quote_number' => 'Q-2026-001']);

        /* Act */
        $response = app(PdfGenerationService::class)->downloadQuote($quote);

        /* Assert */
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="quote-Q-2026-001.pdf"', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
        // The quote number is verified in the HTML layer
        // (PdfGenerationServiceTest); the dompdf byte stream is zlib-compressed,
        // so it is not asserted here.

        // The action returns a raw binary Response, which Livewire's callAction
        // test harness cannot serialise — assert the permitted user sees it.
        Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->assertActionVisible(TestAction::make('download pdf')->table($quote));
    }

    #[Test]
    public function it_sanitizes_slashes_in_quote_number_for_download_filename(): void
    {
        /* Arrange */
        $quote = $this->createQuote(['quote_number' => 'Q/2026/001']);

        /* Act */
        $response = app(PdfGenerationService::class)->downloadQuote($quote);

        /* Assert */
        $disposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringNotContainsString('/', $disposition);
        $this->assertStringNotContainsString('\\', $disposition);
        $this->assertStringNotContainsString('%', $disposition);
        $this->assertSame('attachment; filename="quote-Q-2026-001.pdf"', $disposition);
    }

    #[Test]
    public function it_hides_download_action_for_user_without_permission(): void
    {
        /* Arrange */
        $this->user->syncRoles([]);
        $this->user->givePermissionTo([
            Permission::VIEW_QUOTES->value,
        ]);
        $quote = $this->createQuote();

        /* Act & Assert */
        Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->assertActionHidden(TestAction::make('download pdf')->table($quote));
    }

    #[Test]
    public function it_propagates_render_failure_when_template_resolution_fails(): void
    {
        /* Arrange */
        $quote = $this->createQuote(['template' => 'nonexistent-template-slug']);

        $mockService = Mockery::mock(PdfGenerationService::class);
        $mockService->shouldReceive('handleQuoteDownload')
            ->andThrow(new RuntimeException('Template resolution error'));
        $this->app->instance(PdfGenerationService::class, $mockService);

        /* Assert */
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Template resolution error');

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->callAction(TestAction::make('download pdf')->table($quote));
    }

    private function createQuote(array $attributes = []): Quote
    {
        /** @var Quote $quote */
        $quote = Quote::factory()->for($this->company)->create(array_merge([
            'quote_number'     => 'Q-987654',
            'prospect_id'      => $this->prospect->getKey(),
            'numbering_id'     => $this->numbering->getKey(),
            'user_id'          => $this->user->id,
            'quote_status'     => QuoteStatus::DRAFT->value,
            'quoted_at'        => '2025-05-10',
            'quote_expires_at' => '2025-06-09',
        ], $attributes));

        return $quote;
    }
}
