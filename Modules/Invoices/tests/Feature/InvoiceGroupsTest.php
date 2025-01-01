<?php

namespace Modules\Invoices\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Invoices\Filament\Resources\InvoiceGroupResource\Pages\ManageInvoiceGroups;
use Modules\Invoices\Models\InvoiceGroup;

class InvoiceGroupsTest extends AbstractTestCase
{
    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_displays_invoice_groups_index(): void
    {
        $user = User::factory()->create();
        InvoiceGroup::factory()->create(['invoice_group_name' => 'Test Invoice Group']);

        Livewire::test(ManageInvoiceGroups::class)
            ->assertCanSeeTableRecords(InvoiceGroup::all());
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_creates_an_invoice_group(): void
    {
        $data = [
            'invoice_group_name' => 'Test Group',
            'group_prefix'       => 'TG-',
            'group_next_id'      => 1,
        ];

        Livewire::test(CreateInvoiceGroup::class)
            ->set('data.invoice_group_name', $data['invoice_group_name'])
            ->set('data.group_prefix', $data['group_prefix'])
            ->set('data.group_next_id', $data['group_next_id'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas(InvoiceGroup::class, array_merge($data, [
            'invoice_group_id' => InvoiceGroup::latest('invoice_group_id')->first()->invoice_group_id,
            'created_at'       => now()->toDateTimeString(),
            'updated_at'       => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_updates_an_invoice_group(): void
    {
        $group = InvoiceGroup::factory()->create([
            'group_name'    => 'Original Group',
            'group_prefix'  => 'OG-',
            'group_next_id' => 1,
        ]);

        $updatedData = [
            'group_name'    => 'Updated Group',
            'group_prefix'  => 'UG-',
            'group_next_id' => 2,
        ];

        Livewire::test(EditInvoiceGroup::class, ['record' => $group->invoice_group_id])
            ->set('data.group_name', $updatedData['group_name'])
            ->set('data.group_prefix', $updatedData['group_prefix'])
            ->set('data.group_next_id', $updatedData['group_next_id'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas(InvoiceGroup::class, array_merge($updatedData, [
            'invoice_group_id' => $group->invoice_group_id,
            'updated_at'       => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_deletes_an_invoice_group(): void
    {
        $group = InvoiceGroup::factory()->create();

        Livewire::test(ManageInvoiceGroups::class)
            ->callTableAction('delete', $group);

        $this->assertDatabaseMissing(InvoiceGroup::class, [
            'invoice_group_id' => $group->invoice_group_id,
        ]);
    }
}
