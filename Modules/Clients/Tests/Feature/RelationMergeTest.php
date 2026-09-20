<?php

namespace Modules\Clients\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use InvalidArgumentException;
use Livewire\Livewire;
use Modules\Clients\Filament\Company\Resources\Relations\Pages\ListRelations;
use Modules\Clients\Models\Relation;
use Modules\Clients\Services\RelationMergeService;
use Modules\Core\Models\Company;
use Modules\Core\Models\Note;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Expenses\Models\Expense;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Models\Payment;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RelationMergeService::class)]
class RelationMergeTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    #[Group('merge')]
    public function it_reparents_all_dependent_records_onto_the_primary(): void
    {
        /* Arrange */
        $primary   = Relation::factory()->for($this->company)->customer()->create();
        $duplicate = Relation::factory()->for($this->company)->customer()->create();

        $invoice = Invoice::factory()->for($this->company)->create(['customer_id' => $duplicate->id]);
        $quote   = Quote::factory()->for($this->company)->create(['prospect_id' => $duplicate->id]);
        $payment = Payment::factory()->for($this->company)->create([
            'customer_id' => $duplicate->id,
            'invoice_id'  => $invoice->id,
        ]);
        $expense = Expense::factory()->for($this->company)->create(['vendor_id' => $duplicate->id]);
        $task    = Task::factory()->for($this->company)->create(['customer_id' => $duplicate->id]);
        $project = Project::factory()->for($this->company)->create(['customer_id' => $duplicate->id]);

        $note = Note::query()->create([
            'company_id'   => $this->company->id,
            'noted_at'     => now()->toDateString(),
            'notable_type' => $duplicate->getMorphClass(),
            'notable_id'   => $duplicate->id,
            'is_private'   => false,
            'title'        => 'Call notes',
            'content'      => 'Discussed contract renewal.',
        ]);

        $duplicateContactIds = $duplicate->contacts()->pluck('id');
        $duplicateAddressIds = $duplicate->addresses()->pluck('id');

        /* Act */
        app(RelationMergeService::class)->merge($primary, $duplicate);

        /* Assert */
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'customer_id' => $primary->id]);
        $this->assertDatabaseHas('quotes', ['id' => $quote->id, 'prospect_id' => $primary->id]);
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'customer_id' => $primary->id]);
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'vendor_id' => $primary->id]);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'customer_id' => $primary->id]);
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'customer_id' => $primary->id]);
        $this->assertDatabaseHas('notes', ['id' => $note->id, 'notable_id' => $primary->id]);

        $this->assertNotEmpty($duplicateContactIds);
        foreach ($duplicateContactIds as $contactId) {
            $this->assertDatabaseHas('contacts', ['id' => $contactId, 'relation_id' => $primary->id]);
        }

        $this->assertNotEmpty($duplicateAddressIds);
        foreach ($duplicateAddressIds as $addressId) {
            $this->assertDatabaseHas('addresses', ['id' => $addressId, 'addressable_id' => $primary->id]);
        }
    }

    #[Test]
    #[Group('merge')]
    public function it_exposes_the_merged_records_through_the_primary_relations(): void
    {
        /* Arrange */
        $primary   = Relation::factory()->for($this->company)->customer()->create();
        $duplicate = Relation::factory()->for($this->company)->customer()->create();

        $invoice = Invoice::factory()->for($this->company)->create(['customer_id' => $duplicate->id]);
        $quote   = Quote::factory()->for($this->company)->create(['prospect_id' => $duplicate->id]);

        /* Act */
        $merged = app(RelationMergeService::class)->merge($primary, $duplicate);

        /* Assert */
        $this->assertTrue($merged->invoices()->pluck('id')->contains($invoice->id));
        $this->assertTrue($merged->quotes()->pluck('id')->contains($quote->id));
    }

    #[Test]
    #[Group('merge')]
    public function it_fills_empty_scalar_fields_without_overwriting_existing_values(): void
    {
        /* Arrange */
        $primary = Relation::factory()->for($this->company)->customer()->create([
            'vat_number'    => null,
            'coc_number'    => 'PRIMARY-COC',
            'language'      => null,
            'trading_name'  => null,
            'currency_code' => 'EUR',
        ]);

        $duplicate = Relation::factory()->for($this->company)->customer()->create([
            'vat_number'    => 'NL123456789',
            'coc_number'    => 'DUPLICATE-COC',
            'language'      => 'nl',
            'trading_name'  => 'Dup Trading BV',
            'currency_code' => 'USD',
        ]);

        /* Act */
        $merged = app(RelationMergeService::class)->merge($primary, $duplicate);

        /* Assert — gaps filled from the duplicate */
        $this->assertSame('NL123456789', $merged->vat_number);
        $this->assertSame('nl', $merged->language);
        $this->assertSame('Dup Trading BV', $merged->trading_name);

        /* Assert — existing values never overwritten */
        $this->assertSame('PRIMARY-COC', $merged->coc_number);
        $this->assertSame('EUR', $merged->currency_code);
    }

    #[Test]
    #[Group('merge')]
    public function it_soft_deletes_the_duplicate_and_keeps_the_primary(): void
    {
        /* Arrange */
        $primary   = Relation::factory()->for($this->company)->customer()->create();
        $duplicate = Relation::factory()->for($this->company)->customer()->create();

        /* Act */
        app(RelationMergeService::class)->merge($primary, $duplicate);

        /* Assert */
        $this->assertSoftDeleted('relations', ['id' => $duplicate->id]);
        $this->assertDatabaseHas('relations', ['id' => $primary->id, 'deleted_at' => null]);
        $this->assertNull(Relation::query()->find($duplicate->id));
        $this->assertNotNull(Relation::withTrashed()->find($duplicate->id));
    }

    #[Test]
    #[Group('merge')]
    public function it_rejects_merging_a_relation_into_itself(): void
    {
        /* Arrange */
        $relation = Relation::factory()->for($this->company)->customer()->create();

        /* Assert */
        $this->expectException(InvalidArgumentException::class);

        /* Act */
        app(RelationMergeService::class)->merge($relation, $relation);
    }

    #[Test]
    #[Group('merge')]
    public function it_rejects_merging_relations_from_different_companies(): void
    {
        /* Arrange */
        $primary = Relation::factory()->for($this->company)->customer()->create();

        $otherCompany = Company::factory()->create();
        $foreign      = Relation::factory()->for($otherCompany)->customer()->create();

        /* Assert */
        $this->expectException(InvalidArgumentException::class);

        /* Act */
        app(RelationMergeService::class)->merge($primary, $foreign);
    }

    #[Test]
    #[Group('merge')]
    public function it_merges_two_selected_clients_through_the_table_bulk_action(): void
    {
        /* Arrange */
        $primary   = Relation::factory()->for($this->company)->customer()->create();
        $duplicate = Relation::factory()->for($this->company)->customer()->create();

        $invoice = Invoice::factory()->for($this->company)->create(['customer_id' => $duplicate->id]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->selectTableRecords([$primary->id, $duplicate->id])
            ->callAction(
                TestAction::make('merge')->table()->bulk(),
                ['primary_id' => $primary->id],
            );

        /* Assert */
        $component->assertSuccessful()->assertNotified();
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'customer_id' => $primary->id]);
        $this->assertSoftDeleted('relations', ['id' => $duplicate->id]);
    }

    #[Test]
    #[Group('merge')]
    public function it_refuses_the_bulk_action_when_more_than_two_clients_are_selected(): void
    {
        /* Arrange */
        $relations = Relation::factory()->for($this->company)->customer()->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->selectTableRecords($relations->pluck('id')->all())
            ->callAction(
                TestAction::make('merge')->table()->bulk(),
                ['primary_id' => $relations->first()->id],
            );

        /* Assert — nothing merged, nothing deleted */
        foreach ($relations as $relation) {
            $this->assertDatabaseHas('relations', ['id' => $relation->id, 'deleted_at' => null]);
        }
    }
}
