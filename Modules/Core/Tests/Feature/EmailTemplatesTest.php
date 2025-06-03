<?php

namespace Modules\Core\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\EmailTemplates\EmailTemplateResource;
use Modules\Core\Filament\Admin\Resources\EmailTemplates\Pages\CreateEmailTemplate;
use Modules\Core\Filament\Admin\Resources\EmailTemplates\Pages\EditEmailTemplate;
use Modules\Core\Filament\Admin\Resources\EmailTemplates\Pages\ListEmailTemplates;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(EmailTemplateResource::class)]
class EmailTemplatesTest extends AbstractAdminPanelTestCase
{
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['subject' => 'Test Email']
     */
    #[Group('crud')]
    public function it_lists_email_templates(): void
    {
        /* arrange */
        $template = EmailTemplate::factory()->create(['subject' => 'Test Email']);

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListEmailTemplates::class);

        /* assert */
        $component->assertSuccessful();

        $this->assertDatabaseHas('email_templates', $template->toArray());
    }

    #[Test]
    #[Group('crud')]
    public function it_creates_an_email_template(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $payload = ['subject' => 'Welcome', 'body' => 'Hello world'];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(CreateEmailTemplate::class)->fillForm($payload)->call('create');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_email_template_without_subject(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payload = ['body' => 'Missing subject'];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(CreateEmailTemplate::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['subject']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_email_template(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $template = EmailTemplate::factory()->create(['subject' => 'Old Subject']);

        $payload = ['subject' => 'Updated Subject'];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(EditEmailTemplate::class, ['record' => $template->id])->fillForm($payload)->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_an_email_template(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $template = EmailTemplate::factory()->create();

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(ListEmailTemplates::class)->callTableAction('delete', $template);

        $this->assertDatabaseMissing('email_templates', ['id' => $template->id]);
    }
}
