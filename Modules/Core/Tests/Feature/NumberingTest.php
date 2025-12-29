<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Filament\Admin\Resources\Numberings\Pages\ListNumberings;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class NumberingTest extends AbstractAdminPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('smoke')]
    #[Group('crud')]
    public function it_lists_numberings(): void
    {
        /* Arrange */
        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::PROJECT->value,
            'name'     => 'Test Numbering',
            'next_id'  => 1,
            'left_pad' => 4,
            'format'   => null,
            'prefix'   => NumberingType::PROJECT->prefix(),
        ]);

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListNumberings::class);

        /* Assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('numbering', [
            'id'       => $numbering->id,
            'type'     => $numbering->type->value,
            'name'     => $numbering->name,
            'next_id'  => $numbering->next_id,
            'left_pad' => $numbering->left_pad,
            'format'   => $numbering->format,
            'prefix'   => $numbering->prefix,
            'last_id'  => null,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_filters_numberings_by_current_company_id(): void
    {
        /* Arrange */
        $otherCompany = \Modules\Core\Models\Company::factory()->create();
        
        $ownNumbering = Numbering::factory()->for($this->company)->create([
            'type' => NumberingType::INVOICE->value,
            'name' => 'Own Numbering',
        ]);
        
        $otherNumbering = Numbering::factory()->for($otherCompany)->create([
            'type' => NumberingType::INVOICE->value,
            'name' => 'Other Numbering',
        ]);

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListNumberings::class);

        /* Assert */
        $component->assertSuccessful();
        $component->assertCanSeeTableRecords([$ownNumbering]);
        $component->assertCanNotSeeTableRecords([$otherNumbering]);
    }

    #[Test]
    #[Group('crud')]
    public function it_creates_a_numbering_scheme(): void
    {
        /* Arrange */
        $payload = [
            'type'                    => NumberingType::PROJECT->value,
            'name'                    => 'Project Numbering',
            'group_identifier_format' => 'PRJ-{YEAR}-{ID}',
            'left_pad'                => 5,
            'format'                  => 'PRJ-{YEAR}-{ID}',
            'next_id'                 => 1,
            'reset_number'            => 0,
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListNumberings::class)
            ->callTableAction('create', data: $payload);

        /* Assert */
        $component->assertSuccessful();
        $component->assertHasNoTableActionErrors();
        $this->assertDatabaseHas('numbering', [
            'name'   => 'Project Numbering',
            'type'   => NumberingType::PROJECT->value,
            'format' => 'PRJ-{YEAR}-{ID}',
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_numbering_scheme(): void
    {
        /* Arrange */
        $numbering = Numbering::factory()->for($this->company)->create([
            'type'                    => NumberingType::QUOTE->value,
            'name'                    => 'Old Name',
            'group_identifier_format' => 'QUO-{ID}',
        ]);

        $payload = [
            'name'                    => 'Updated Quote Numbering',
            'group_identifier_format' => 'QUO-{YEAR}-{ID}',
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListNumberings::class)
            ->callTableAction('edit', $numbering, data: $payload);

        /* Assert */
        $component->assertSuccessful();
        $component->assertHasNoTableActionErrors();
        $this->assertDatabaseHas('numbering', [
            'id'   => $numbering->id,
            'name' => 'Updated Quote Numbering',
            'group_identifier_format' => 'QUO-{YEAR}-{ID}',
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_numbering_scheme(): void
    {
        /* Arrange */
        $numbering = Numbering::factory()->for($this->company)->create([
            'type'                    => NumberingType::EXPENSE->value,
            'name'                    => 'Numbering to Delete',
            'group_identifier_format' => 'EXP-{ID}',
        ]);

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListNumberings::class)
            ->callTableAction('delete', $numbering);

        /* Assert */
        $component->assertSuccessful();
        $this->assertDatabaseMissing('numbering', ['id' => $numbering->id]);
    }

    #[Test]
    #[Group('validation')]
    public function it_requires_name_when_creating_numbering(): void
    {
        /* Arrange */
        $payload = [
            'type'                    => NumberingType::TASK->value,
            'group_identifier_format' => 'TSK-{ID}',
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListNumberings::class)
            ->callTableAction('create', data: $payload);

        /* Assert */
        $component->assertHasTableActionErrors(['name']);
    }

    #[Test]
    #[Group('validation')]
    public function it_requires_type_when_creating_numbering(): void
    {
        /* Arrange */
        $payload = [
            'name'                    => 'Test Numbering',
            'group_identifier_format' => 'XXX-{ID}',
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListNumberings::class)
            ->callTableAction('create', data: $payload);

        /* Assert */
        $component->assertHasTableActionErrors(['type']);
    }
}
