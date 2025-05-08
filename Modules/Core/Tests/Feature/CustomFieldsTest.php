<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\CustomFieldResource;
use Modules\Core\Filament\Admin\Resources\CustomFieldResource\Pages\CreateCustomField;
use Modules\Core\Filament\Admin\Resources\CustomFieldResource\Pages\EditCustomField;
use Modules\Core\Filament\Admin\Resources\CustomFieldResource\Pages\ListCustomFields;
use Modules\Core\Models\CustomField;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(CustomFieldResource::class)]
class CustomFieldsTest extends AbstractTestCase
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
     * \Modules\Core\Filament\Admin\Resources\CustomFieldResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "fieldable_type": "Example",
     * "field_type": "Example",
     * "field_label": "Example",
     * "field_order": "Example"
     * }
     */
    #[Group('crud')]
    public function it_creates_a_customfield(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        //$this->actingAs(User::factory()->create());

        $payload = [
            'company_id'     => 'Value',
            'fieldable_type' => 'Example',
            'field_type'     => 'Example',
            'field_label'    => 'Example',
            'field_order'    => 'Example',
        ];

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(CreateCustomField::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Core\Filament\Admin\Resources\CustomFieldResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "fieldable_type": "Example",
     * "field_type": "Example",
     * "field_label": "Example",
     * "field_order": "Example"
     * }
     */
    #[Group('crud')]
    public function it_updates_a_customfield(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = CustomField::factory()->create();

        $payload = [
            'company_id'     => 'Value',
            'fieldable_type' => 'Example',
            'field_type'     => 'Example',
            'field_label'    => 'Example',
            'field_order'    => 'Example',
        ];

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(EditCustomField::class, ['record' => $record->getKey()])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Core\Filament\Admin\Resources\CustomFieldResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "fieldable_type": "Example",
     * "field_type": "Example",
     * "field_label": "Example",
     * "field_order": "Example"
     * }
     */
    #[Group('crud')]
    public function it_deletes_a_customfield(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = CustomField::factory()->create();

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(ListCustomFields::class)->callTableAction('delete', $record);

        $this->assertDatabaseMissing('customfields', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
