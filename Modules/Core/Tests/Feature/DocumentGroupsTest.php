<?php

namespace Modules\Core\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\DocumentGroupResource;
use Modules\Core\Filament\Admin\Resources\DocumentGroupResource\Pages\CreateDocumentGroup;
use Modules\Core\Filament\Admin\Resources\DocumentGroupResource\Pages\EditDocumentGroup;
use Modules\Core\Filament\Admin\Resources\DocumentGroupResource\Pages\ListDocumentGroups;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(DocumentGroupResource::class)]
class DocumentGroupsTest extends AbstractTestCase
{
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['name' => 'Policies']
     */
    public function it_lists_document_groups(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $group = DocumentGroup::factory()->create(['name' => 'Policies']);

        Livewire::test(ListDocumentGroups::class)
            ->actingAs($this->superAdmin())
            ->assertSuccessful()
            ->assertSeeDatabaseRecords($group);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['name' => 'Forms']
     */
    public function it_creates_a_document_group(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payload = ['name' => 'Forms'];

        Livewire::test(CreateDocumentGroup::class)
            ->actingAs($this->superAdmin())
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('document_groups', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_fails_to_create_document_group_when_name_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payload = [];

        Livewire::test(CreateDocumentGroup::class)
            ->actingAs($this->superAdmin())
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['name']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['name' => 'Updated Group']
     */
    public function it_updates_a_document_group(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $group = DocumentGroup::factory()->create(['name' => 'Old Group']);

        $payload = ['name' => 'Updated Group'];

        Livewire::test(EditDocumentGroup::class, ['record' => $group->id])
            ->actingAs($this->superAdmin())
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('document_groups', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_deletes_a_document_group(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $group = DocumentGroup::factory()->create();

        Livewire::test(ListDocumentGroups::class)
            ->actingAs($this->superAdmin())
            ->callTableAction('delete', $group);

        $this->assertDatabaseMissing('document_groups', ['id' => $group->id]);
    }
}
