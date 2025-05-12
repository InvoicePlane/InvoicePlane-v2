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
    #[Group('crud')]
    public function it_lists_document_groups(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $group = DocumentGroup::factory()->create(['name' => 'Policies']);

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(ListDocumentGroups::class);

        /* assert */
        $component->assertSuccessful()->assertSeeDatabaseRecords($group);
    }

    #[Test]
    #[Group('crud')]
    public function it_creates_a_document_group(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payload = ['name' => 'Forms'];

        /** act */
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

        /** act */
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

        /** act */
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

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(ListDocumentGroups::class)->callTableAction('delete', $group);

        $this->assertDatabaseMissing('document_groups', ['id' => $group->id]);
    }
}
