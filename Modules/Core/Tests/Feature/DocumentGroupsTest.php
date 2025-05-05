<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
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
    use WithFaker;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    // region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "type": "Value",
     * "document_group_name": "Example",
     * "left_pad": "Example",
     * "format": "Example",
     * "next_id": "Value"
     * }
     */
    public function it_creates_a_documentgroup(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
            'company_id'          => 'Value',
            'type'                => 'Value',
            'document_group_name' => 'Example',
            'left_pad'            => 'Example',
            'format'              => 'Example',
            'next_id'             => 'Value',
        ];

        Livewire::test(CreateDocumentGroup::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "type": "Value",
     * "document_group_name": "Example",
     * "left_pad": "Example",
     * "format": "Example",
     * "next_id": "Value"
     * }
     */
    public function it_updates_a_documentgroup(): void
    {
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = DocumentGroup::factory()->create();

        $payload = [
            'company_id'          => 'Value',
            'type'                => 'Value',
            'document_group_name' => 'Example',
            'left_pad'            => 'Example',
            'format'              => 'Example',
            'next_id'             => 'Value',
        ];

        Livewire::test(EditDocumentGroup::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "type": "Value",
     * "document_group_name": "Example",
     * "left_pad": "Example",
     * "format": "Example",
     * "next_id": "Value"
     * }
     */
    public function it_deletes_a_documentgroup(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = DocumentGroup::factory()->create();

        Livewire::test(ListDocumentGroups::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('document_groups', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
