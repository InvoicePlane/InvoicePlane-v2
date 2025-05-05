<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\EmailTemplateResource;
use Modules\Core\Filament\Admin\Resources\EmailTemplateResource\Pages\CreateEmailTemplate;
use Modules\Core\Filament\Admin\Resources\EmailTemplateResource\Pages\EditEmailTemplate;
use Modules\Core\Filament\Admin\Resources\EmailTemplateResource\Pages\ListEmailTemplates;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(EmailTemplateResource::class)]

class EmailTemplatesTest extends AbstractTestCase
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
     * \Modules\Core\Filament\Admin\Resources\EmailTemplateResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "title": "Example",
     * "type": "Value",
     * "subject": "Example",
     * "body": "Example",
     * "from_name": "Example",
     * "from_email": "Example",
     * "cc": "Example",
     * "bcc": "Example"
     * }
     */
    public function it_creates_a_emailtemplate(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
            'company_id' => 'Value',
            'title'      => 'Example',
            'type'       => 'Value',
            'subject'    => 'Example',
            'body'       => 'Example',
            'from_name'  => 'Example',
            'from_email' => 'Example',
            'cc'         => 'Example',
            'bcc'        => 'Example',
        ];

        Livewire::test(CreateEmailTemplate::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Core\Filament\Admin\Resources\EmailTemplateResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "title": "Example",
     * "type": "Value",
     * "subject": "Example",
     * "body": "Example",
     * "from_name": "Example",
     * "from_email": "Example",
     * "cc": "Example",
     * "bcc": "Example"
     * }
     */
    public function it_updates_a_emailtemplate(): void
    {
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = EmailTemplate::factory()->create();

        $payload = [
            'company_id' => 'Value',
            'title'      => 'Example',
            'type'       => 'Value',
            'subject'    => 'Example',
            'body'       => 'Example',
            'from_name'  => 'Example',
            'from_email' => 'Example',
            'cc'         => 'Example',
            'bcc'        => 'Example',
        ];

        Livewire::test(EditEmailTemplate::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Core\Filament\Admin\Resources\EmailTemplateResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "title": "Example",
     * "type": "Value",
     * "subject": "Example",
     * "body": "Example",
     * "from_name": "Example",
     * "from_email": "Example",
     * "cc": "Example",
     * "bcc": "Example"
     * }
     */
    public function it_deletes_a_emailtemplate(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = EmailTemplate::factory()->create();

        Livewire::test(ListEmailTemplates::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('emailtemplates', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
