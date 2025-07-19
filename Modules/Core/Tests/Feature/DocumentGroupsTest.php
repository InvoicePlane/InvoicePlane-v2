<?php

namespace Modules\Core\Tests\Feature;

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
        $group = DocumentGroup::factory()->create(['name' => 'Policies']);

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
    public function it_creates_a_document_group_trough_a_modal(): void
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
    public function it_fails_to_create_a_document_group_trough_a_modal_when_group_identifier_format_missing(): void
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
    public function it_creates_a_document_group(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payload = ['name' => 'Forms'];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(CreateDocumentGroup::class)->fillForm($payload)->call('create');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('document_groups', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_document_group_when_name_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payload = [];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(CreateDocumentGroup::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['name']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_document_group(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $group = DocumentGroup::factory()->create(['name' => 'Old Group']);

        $payload = ['name' => 'Updated Group'];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(EditDocumentGroup::class, ['record' => $group->id])->fillForm($payload)->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('document_groups', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_document_group(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $group = DocumentGroup::factory()->create();

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(ListDocumentGroups::class)->callTableAction('delete', $group);

        $this->assertDatabaseMissing('document_groups', ['id' => $group->id]);
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_cannot_access_document_groups_of_another_tenant(): void
    {
        $this->markTestIncomplete('Should assert forbidden/404 when accessing another tenant\'s document group.');
    }
    # endregion

    #region spicy
    # endregion
}
