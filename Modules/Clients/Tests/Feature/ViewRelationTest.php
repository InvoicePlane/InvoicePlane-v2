<?php

namespace Modules\Clients\Tests\Feature;

use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Exceptions\RelationHasLinkedRecordsException;
use Modules\Clients\Filament\Company\Resources\Relations\Pages\ViewRelation;
use Modules\Clients\Models\Relation;
use Modules\Clients\Services\RelationService;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ViewRelation::class)]
#[CoversClass(RelationService::class)]
class ViewRelationTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    public function it_renders_the_client_view_page(): void
    {
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->customer()->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ViewRelation::class, [
                'record' => $customer->getKey(),
                'tenant' => Str::lower($this->company->search_code),
            ]);

        /* Assert */
        $component->assertSuccessful();
    }

    #[Test]
    public function it_prevents_deletion_of_client_with_linked_invoices(): void
    {
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->customer()->create();
        Invoice::factory()->for($this->company)->create(['customer_id' => $customer->id]);

        /* Act & Assert */
        $this->expectException(RelationHasLinkedRecordsException::class);

        app(RelationService::class)->deleteRelation($customer);
    }

    #[Test]
    public function it_prevents_deletion_of_client_with_linked_quotes(): void
    {
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->customer()->create();
        Quote::factory()->for($this->company)->create(['prospect_id' => $customer->id]);

        /* Act & Assert */
        $this->expectException(RelationHasLinkedRecordsException::class);

        app(RelationService::class)->deleteRelation($customer);
    }

    #[Test]
    public function it_allows_deletion_of_client_without_linked_records(): void
    {
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->customer()->create();

        /* Act */
        app(RelationService::class)->deleteRelation($customer);

        /* Assert */
        $this->assertDatabaseMissing('relations', ['id' => $customer->id]);
    }
}
