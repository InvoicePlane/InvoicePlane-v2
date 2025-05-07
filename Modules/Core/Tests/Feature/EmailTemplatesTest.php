<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Core\Filament\Admin\Resources\EmailTemplateResource\Pages\CreateEmailTemplate;

use Modules\Core\Tests\Feature\EmailTemplatesTest;

use Modules\Core\Filament\Admin\Resources\EmailTemplateResource\Pages\ListEmailTemplates;

use Modules\Core\Models\EmailTemplate;

use Modules\Core\Filament\Admin\Resources\EmailTemplateResource\Pages\EditEmailTemplate;

use Modules\Core\Filament\Admin\Resources\EmailTemplateResource;

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
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['subject' => 'Test Email']
     */
    public function it_lists_email_templates(): void
    {
        $template = EmailTemplate::factory()->create(['subject' => 'Test Email']);

        Livewire::test(ListEmailTemplates::class)
            ->actingAs($this->superAdmin())
            ->assertSuccessful()
            ->assertSeeDatabaseRecords($template);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['subject' => 'Welcome', 'body' => 'Hello world']
     */
    public function it_creates_an_email_template(): void
    {
        $payload = ['subject' => 'Welcome', 'body' => 'Hello world'];

        Livewire::test(CreateEmailTemplate::class)
            ->actingAs($this->superAdmin())
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_fails_to_create_email_template_without_subject(): void
    {
        $payload = ['body' => 'Missing subject'];

        Livewire::test(CreateEmailTemplate::class)
            ->actingAs($this->superAdmin())
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['subject']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['subject' => 'Updated Subject']
     */
    public function it_updates_an_email_template(): void
    {
        $template = EmailTemplate::factory()->create(['subject' => 'Old Subject']);

        $payload = ['subject' => 'Updated Subject'];

        Livewire::test(EditEmailTemplate::class, ['record' => $template->id])
            ->actingAs($this->superAdmin())
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_deletes_an_email_template(): void
    {
        $template = EmailTemplate::factory()->create();

        Livewire::test(ListEmailTemplates::class)
            ->actingAs($this->superAdmin())
            ->callTableAction('delete', $template);

        $this->assertDatabaseMissing('email_templates', ['id' => $template->id]);
    }
}
