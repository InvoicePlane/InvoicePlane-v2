<?php

namespace Modules\Quotes\Tests\Feature;

use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Quotes\Filament\Company\Resources\Quotes\Pages\EditQuote;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(EditQuote::class)]
class ReferenceFieldsTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    public function it_stores_client_reference_and_work_order_on_quote(): void
    {
        /* Arrange */
        $prospect  = Relation::factory()->for($this->company)->customer()->create();
        $numbering = Numbering::factory()->for($this->company)->state(['type' => NumberingType::QUOTE->value])->create();
        $quote     = Quote::factory()->for($this->company)->create([
            'prospect_id'  => $prospect->id,
            'numbering_id' => $numbering->id,
        ]);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(EditQuote::class, ['record' => $quote->id])
            ->fillForm([
                'client_reference' => 'PO-2025-99999',
                'work_order'       => 'WO-002',
            ])
            ->call('save')
            ->assertHasNoErrors();

        /* Assert */
        $this->assertDatabaseHas('quotes', [
            'id'               => $quote->id,
            'client_reference' => 'PO-2025-99999',
            'work_order'       => 'WO-002',
        ]);
    }

    #[Test]
    public function it_allows_client_reference_and_work_order_to_be_null_on_quote(): void
    {
        /* Arrange */
        $prospect  = Relation::factory()->for($this->company)->customer()->create();
        $numbering = Numbering::factory()->for($this->company)->state(['type' => NumberingType::QUOTE->value])->create();

        /* Act */
        $quote = Quote::factory()->for($this->company)->create([
            'prospect_id'      => $prospect->id,
            'numbering_id'     => $numbering->id,
            'client_reference' => null,
            'work_order'       => null,
        ]);

        /* Assert */
        $this->assertDatabaseHas('quotes', [
            'id'               => $quote->id,
            'client_reference' => null,
            'work_order'       => null,
        ]);
    }
}
