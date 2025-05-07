<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Tests\Feature\CustomFieldValuesTest;

use Modules\Core\Filament\Admin\Resources\CustomFieldValueResource\Pages\EditCustomFieldValue;

use Modules\Core\Filament\Admin\Resources\CustomFieldValueResource\Pages\CreateCustomFieldValue;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Core\Filament\Admin\Resources\CustomFieldValueResource\Pages\ListCustomFieldValues;

use Modules\Core\Filament\Admin\Resources\CustomFieldValueResource;

use Modules\Core\Models\User;

use Modules\Core\Models\CustomFieldValue;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\CustomFieldValueResource;
use Modules\Core\Filament\Admin\Resources\CustomFieldValueResource\Pages\CreateCustomFieldValue;
use Modules\Core\Filament\Admin\Resources\CustomFieldValueResource\Pages\EditCustomFieldValue;
use Modules\Core\Filament\Admin\Resources\CustomFieldValueResource\Pages\ListCustomFieldValues;
use Modules\Core\Models\CustomFieldValue;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(CustomFieldValueResource::class)]

class CustomFieldValuesTest extends AbstractTestCase
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
     * \Modules\Core\Filament\Admin\Resources\CustomFieldValueResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "custom_field_id": "Value",
     * "fieldable_type": "Example",
     * "fieldable_id": "Value",
     * "custom_field_value": "Example"
     * }
     */
    public function it_creates_a_customfieldvalue(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
            'company_id'         => 'Value',
            'custom_field_id'    => 'Value',
            'fieldable_type'     => 'Example',
            'fieldable_id'       => 'Value',
            'custom_field_value' => 'Example',
        ];

        Livewire::test(CreateCustomFieldValue::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Core\Filament\Admin\Resources\CustomFieldValueResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "custom_field_id": "Value",
     * "fieldable_type": "Example",
     * "fieldable_id": "Value",
     * "custom_field_value": "Example"
     * }
     */
    public function it_updates_a_customfieldvalue(): void
    {
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = CustomFieldValue::factory()->create();

        $payload = [
            'company_id'         => 'Value',
            'custom_field_id'    => 'Value',
            'fieldable_type'     => 'Example',
            'fieldable_id'       => 'Value',
            'custom_field_value' => 'Example',
        ];

        Livewire::test(EditCustomFieldValue::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Core\Filament\Admin\Resources\CustomFieldValueResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "custom_field_id": "Value",
     * "fieldable_type": "Example",
     * "fieldable_id": "Value",
     * "custom_field_value": "Example"
     * }
     */
    public function it_deletes_a_customfieldvalue(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = CustomFieldValue::factory()->create();

        Livewire::test(ListCustomFieldValues::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('customfieldvalues', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
