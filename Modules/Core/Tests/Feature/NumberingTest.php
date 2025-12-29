<?php

namespace Modules\Core\Tests\Feature\Numbering;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(NumberingResource::class)]
class NumberingTest extends AbstractAdminPanelTestCase
{
    use RefreshDatabase;

    # region smoke
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
            'name'         => $numbering->name,
            'next_id'      => $numbering->next_id,
            'left_pad'     => $numbering->left_pad,
            'format'       => $numbering->format,
            'prefix'       => $numbering->prefix,
            'last_id'      => null,
        ]);
    }

    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['name' => 'Policies']
     */
    #[Group('crud')]
    public function it_lists_document_groups(): void
    {
        /* arrange */
        $group = DocumentGroup::factory()->for($this->company)->create(['name' => 'Policies']);

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListDocumentGroups::class);

        /* assert */
        $component->assertSuccessful();

        $this->assertDatabaseHas('document_groups', $group->toArray());
    }
    # endregion

    #[Test]
    public function it_displays_numbering_form_fields_correctly(): void
    {
        $this->markTestIncomplete();
        $response = $this->get(route('filament.resources.numberings.create'));
        $response->assertStatus(200);
        $response->assertSee('type');
        $response->assertSee('name');
        $response->assertSee('next_id');
        $response->assertSee('left_pad');
        $response->assertSee('format');
        $response->assertDontSee('last_id');
    }

    #[Test]
    public function it_prefills_prefix_and_format_on_type_change(): void
    {
        $this->markTestIncomplete();
        // Simulate selecting each type and check prefix/format
        foreach (\Modules\Core\Enums\NumberingType::cases() as $type) {
            $response = $this->post(route('filament.resources.numberings.store'), [
                'type'     => $type->value,
                'name'     => ucfirst($type->label()),
                'format'   => '{{prefix}}-{{number}}',
                'prefix'   => $type->prefix(),
                'next_id'  => 2,
                'left_pad' => 3,
            ]);
            $response->assertSessionHasNoErrors();
            $this->assertDatabaseHas('numbering', [
                'type'   => $type->value,
                'format' => '{{prefix}}-{{number}}',
                'prefix' => $type->prefix(),
            ]);
        }
    }

    #[Test]
    public function it_validates_required_fields(): void
    {
        $this->markTestIncomplete();
        $response = $this->post(route('filament.resources.numberings.store'), []);
        $response->assertSessionHasErrors(['type', 'name', 'next_id', 'left_pad']);
    }

    #[Test]
    public function it_creates_numbering_with_valid_data(): void
    {
        $this->markTestIncomplete();
        $data = [
            'type'     => \Modules\Core\Enums\NumberingType::PROJECT->value,
            'name'     => 'Test Project',
            'format'   => '{{prefix}}-{{number}}',
            'prefix'   => \Modules\Core\Enums\NumberingType::PROJECT->prefix(),
            'next_id'  => 1,
            'left_pad' => 4,
        ];
        $response = $this->post(route('filament.resources.numberings.store'), $data);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('numbering', [
            'name'   => 'Test Project',
            'type'   => \Modules\Core\Enums\NumberingType::PROJECT->value,
            'format' => '{{prefix}}-{{number}}',
            'prefix' => \Modules\Core\Enums\NumberingType::PROJECT->prefix(),
        ]);
    }

    # region modals
    #[Test]
    #[Group('crud')]
    public function it_creates_a_document_group_through_a_modal(): void
    {
        /* arrange */
        $groupType = DocumentGroupType::CUSTOMERS;

        $payload = [
            'type'                    => $groupType,
            'group_identifier_format' => $groupType->prefix() . '-656',
            'name'                    => $groupType->label(),
            'left_pad'                => 1,
            'format'                  => $groupType->prefix() . '-4376656',
            'next_id'                 => 1,
            'reset_number'            => 34343,
            'last_id'                 => 437843,
            'last_year'               => 2025,
            'last_month'              => 6,
            'last_week'               => 23,
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListDocumentGroups::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertSuccessful();
        $component->assertHasNoFormErrors();
        $this->assertDatabaseHas('document_groups', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_a_document_group_through_a_modal_when_group_identifier_format_missing(): void
    {
        $groupType = DocumentGroupType::CUSTOMERS;

        /* arrange */
        $payload = [
            'type'         => $groupType,
            'name'         => $groupType->label(),
            'left_pad'     => 1,
            'format'       => $groupType->prefix() . '-4376656',
            'next_id'      => 1,
            'reset_number' => 34343,
            'last_id'      => 437843,
            'last_year'    => 2025,
            'last_month'   => 6,
            'last_week'    => 23,
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListDocumentGroups::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors();

        $this->assertDatabaseMissing('document_groups', $payload);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "name": "Forms"
     * }
     */
    public function it_creates_a_document_group(): void
    {
        $groupType = DocumentGroupType::CUSTOMERS;

        /* arrange */
        $payload = [
            'type'                    => $groupType,
            'group_identifier_format' => $groupType->prefix() . '-656',
            'name'                    => $groupType->label(),
            'left_pad'                => 1,
            'format'                  => $groupType->prefix() . '-4376656',
            'next_id'                 => 1,
            'reset_number'            => 34343,
            'last_id'                 => 437843,
            'last_year'               => 2025,
            'last_month'              => 6,
            'last_week'               => 23,
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateDocumentGroup::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('document_groups', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {}
     */
    public function it_fails_to_create_a_document_group_without_required_name(): void
    {
        /* arrange */
        $groupType = DocumentGroupType::CUSTOMERS;

        $payload = [
            'type'                    => $groupType,
            'group_identifier_format' => $groupType->prefix() . '-{ID}',
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateDocumentGroup::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['name']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "name": "Updated Group"
     * }
     */
    public function it_updates_a_document_group(): void
    {
        /* arrange */
        $groupType = DocumentGroupType::CUSTOMERS;
        $group     = DocumentGroup::factory()->for($this->company)->create([
            'type'                    => $groupType,
            'name'                    => 'Old Group',
            'group_identifier_format' => $groupType->prefix() . '-{ID}',
        ]);

        $payload = [
            'name'                    => 'Updated Group',
            'group_identifier_format' => $groupType->prefix() . '-{Y}-{ID}',
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(EditDocumentGroup::class, ['record' => $group->id])
            ->fillForm($payload)
            ->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('document_groups', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "id": "<id>"
     * }
     */
    public function it_deletes_a_document_group(): void
    {
        /* arrange */
        $groupType = DocumentGroupType::CUSTOMERS;
        $group     = DocumentGroup::factory()->for($this->company)->create([
            'type'                    => $groupType,
            'name'                    => 'Group to Delete',
            'group_identifier_format' => $groupType->prefix() . '-{ID}',
        ]);

        /* act */
        $component = Livewire::actingAs($this->superAdmin)
            ->test(ListDocumentGroups::class)
            ->mountAction(TestAction::make('delete')->table($group))
            ->callMountedAction();

        /* assert */
        $this->assertDatabaseMissing('document_groups', ['id' => $group->id]);
    }
    # endregion

    # region multi-tenancy
    # endregion

    #region spicy
    # endregion
}

    
