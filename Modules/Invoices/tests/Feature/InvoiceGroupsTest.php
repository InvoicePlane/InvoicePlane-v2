<?php

namespace Modules\Invoices\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Core\tests\AbstractTestCase;
use Modules\Invoices\Filament\Resources\InvoiceGroupResource\Pages\CreateInvoiceGroup;
use Modules\Invoices\Filament\Resources\InvoiceGroupResource\Pages\EditInvoiceGroup;
use Modules\Invoices\Filament\Resources\InvoiceGroupResource\Pages\ManageInvoiceGroups;
use Modules\Invoices\Models\InvoiceGroup;

class InvoiceGroupsTest extends AbstractTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_invoice_groups_index(): void
    {
        InvoiceGroup::factory()->create(['invoice_group_name' => 'Test Invoice Group']);

        Livewire::test(ManageInvoiceGroups::class)
            ->assertCanSeeTableRecords(InvoiceGroup::all());
    }

    /** @test */
    public function it_creates_an_invoice_group(): void
    {
        $this->withoutExceptionHandling();
        $payload = [
            'invoice_group_name'              => 'Test Group',
            'invoice_group_identifier_format' => 'TG-',
            'invoice_group_next_id'           => 1,
            'invoice_group_left_pad'          => 1,
        ];
        InvoiceGroup::factory()->create($payload);

        $component = Livewire::test(CreateInvoiceGroup::class)
            ->set('data.invoice_group_name', $payload['invoice_group_name'])
            ->set('data.invoice_group_identifier_format', $payload['invoice_group_identifier_format'])
            ->set('data.invoice_group_next_id', $payload['invoice_group_next_id'])
            ->set('data.invoice_group_left_pad', $payload['invoice_group_left_pad'])
            ->call('create');

        $component->assertHasNoErrors();

        $this->assertDatabaseHas(InvoiceGroup::class, array_merge($payload, [
            'invoice_group_id' => InvoiceGroup::latest('invoice_group_id')->first()->invoice_group_id,
        ]));
    }

    /** @test */
    public function it_updates_an_invoice_group(): void
    {
        $group = InvoiceGroup::factory()->create([
            'invoice_group_name'              => 'Original Group',
            'invoice_group_identifier_format' => 'OG-',
            'invoice_group_next_id'           => 1,
        ]);

        $updatedData = [
            'invoice_group_name'              => 'Updated Group',
            'invoice_group_identifier_format' => 'UG-',
            'invoice_group_next_id'           => 2,
        ];

        Livewire::test(EditInvoiceGroup::class, ['record' => $group->invoice_group_id])
            ->set('data.invoice_group_name', $updatedData['invoice_group_name'])
            ->set('data.invoice_group_identifier_format', $updatedData['invoice_group_identifier_format'])
            ->set('data.invoice_group_next_id', $updatedData['invoice_group_next_id'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas(InvoiceGroup::class, array_merge($updatedData, [
            'invoice_group_id' => $group->invoice_group_id,
            'updated_at'       => now()->toDateTimeString(),
        ]));
    }

    /** @test */
    public function it_deletes_an_invoice_group(): void
    {
        $this->markTestIncomplete('Needs delete action');
        $group = InvoiceGroup::factory()->create();

        Livewire::test(ManageInvoiceGroups::class)
            ->callTableAction('delete', $group);

        $this->assertDatabaseMissing(InvoiceGroup::class, [
            'invoice_group_id' => $group->invoice_group_id,
        ]);
    }
}
