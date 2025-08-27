<?php

namespace Modules\Core\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Modules\Core\Enums\DocumentGroupType;
use Modules\Core\Filament\Admin\Resources\DocumentGroups\DocumentGroupResource;
use Modules\Core\Filament\Admin\Resources\DocumentGroups\Pages\CreateDocumentGroup;
use Modules\Core\Filament\Admin\Resources\DocumentGroups\Pages\EditDocumentGroup;
use Modules\Core\Filament\Admin\Resources\DocumentGroups\Pages\ListDocumentGroups;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(DocumentGroupResource::class)]
class DocumentGroupsTest extends AbstractAdminPanelTestCase
{
    # region smoke
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
