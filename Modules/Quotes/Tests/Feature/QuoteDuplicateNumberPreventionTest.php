<?php

namespace Modules\Quotes\Tests\Feature;

use Modules\Core\Models\Company;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Support\QuoteNumberGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

#[CoversClass(QuoteNumberGenerator::class)]
class QuoteDuplicateNumberPreventionTest extends AbstractAdminPanelTestCase
{
    #[Test]
    #[Group('failing')]
    public function it_prevents_duplicate_quote_numbers_within_same_company(): void
    {
        /* Arrange */
        $company   = Company::factory()->create();
        $numbering = Numbering::factory()->for($company)->create();

        Quote::factory()->for($company)->create([
            'numbering_id' => $numbering->id,
            'quote_number' => 'QUO-2025-0001',
        ]);

        /* Act & Assert */
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Duplicate quote number 'QUO-2025-0001'");

        Quote::factory()->for($company)->create([
            'numbering_id' => $numbering->id,
            'quote_number' => 'QUO-2025-0001',
        ]);
    }

    #[Test]
    public function it_allows_same_quote_number_in_different_companies(): void
    {
        /* Arrange */
        $company1   = Company::factory()->create();
        $company2   = Company::factory()->create();
        $numbering1 = Numbering::factory()->for($company1)->create();
        $numbering2 = Numbering::factory()->for($company2)->create();

        Quote::factory()->for($company1)->create([
            'numbering_id' => $numbering1->id,
            'quote_number' => 'QUO-2025-0001',
        ]);

        /* Act */
        $quote2 = Quote::factory()->for($company2)->create([
            'numbering_id' => $numbering2->id,
            'quote_number' => 'QUO-2025-0001',
        ]);

        /* Assert */
        $this->assertNotNull($quote2);
        $this->assertEquals('QUO-2025-0001', $quote2->quote_number);
        $this->assertEquals($company2->id, $quote2->company_id);
    }

    #[Test]
    #[Group('failing')]
    public function it_allows_multiple_null_quote_numbers_for_drafts(): void
    {
        /* Arrange */
        $company   = Company::factory()->create();
        $numbering = Numbering::factory()->for($company)->create();

        /* Act */
        $draft1 = Quote::factory()->for($company)->create([
            'numbering_id' => $numbering->id,
            'quote_number' => null,
        ]);

        $draft2 = Quote::factory()->for($company)->create([
            'numbering_id' => $numbering->id,
            'quote_number' => null,
        ]);

        $draft3 = Quote::factory()->for($company)->create([
            'numbering_id' => $numbering->id,
            'quote_number' => null,
        ]);

        /* Assert */
        $this->assertNull($draft1->quote_number);
        $this->assertNull($draft2->quote_number);
        $this->assertNull($draft3->quote_number);

        // All three drafts should exist
        $drafts = Quote::query()->where('company_id', $company->id)
            ->whereNull('quote_number')
            ->count();
        $this->assertEquals(3, $drafts);
    }

    #[Test]
    #[Group('failing')]
    public function it_allows_updating_quote_without_changing_number(): void
    {
        /* Arrange */
        $company   = Company::factory()->create();
        $numbering = Numbering::factory()->for($company)->create();

        $quote = Quote::factory()->for($company)->create([
            'numbering_id' => $numbering->id,
            'quote_number' => 'QUO-2025-0001',
        ]);

        /* Act */
        $quote->update([
            'quote_status' => 'approved',
        ]);
        $quote->refresh();

        /* Assert */
        $this->assertEquals('QUO-2025-0001', $quote->quote_number);
        $this->assertEquals('approved', $quote->quote_status->value);
    }
}
