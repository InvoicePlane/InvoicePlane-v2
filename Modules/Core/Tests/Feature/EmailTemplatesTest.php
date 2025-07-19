<?php

namespace Modules\Core\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Enums\EmailTemplateType;
use Modules\Core\Filament\Admin\Resources\EmailTemplates\EmailTemplateResource;
use Modules\Core\Filament\Admin\Resources\EmailTemplates\Pages\CreateEmailTemplate;
use Modules\Core\Filament\Admin\Resources\EmailTemplates\Pages\EditEmailTemplate;
use Modules\Core\Filament\Admin\Resources\EmailTemplates\Pages\ListEmailTemplates;
use Modules\Core\Models\Company;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(EmailTemplateResource::class)]
class EmailTemplatesTest extends AbstractAdminPanelTestCase
{
    # region smoke
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
    # endregion

    # region modals
    #[Test]
    #[Group('crud')]
    public function it_creates_an_email_template_trough_a_modal(): void
    {
        /* arrange */
        $company = Company::factory()->create();
        $payload = [
            'title'      => 'Test Email',
            'subject'    => 'Welcome',
            'body'       => '',
            'type'       => EmailTemplateType::TEXT->value,
            'from_name'  => 'Acme Support',
            'from_email' => 'support@acme.com',
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListEmailTemplates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        if (app()->runningUnitTests()) {
            dump($payload);
        }

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_an_email_template_trough_a_modal_without_required_title(): void
    {
        /* arrange */
        $payload = [
            'subject'    => 'Welcome',
            'body'       => '',
            'type'       => EmailTemplateType::TEXT->value,
            'from_name'  => 'Acme Support',
            'from_email' => 'support@acme.com',
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListEmailTemplates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        if (app()->runningUnitTests()) {
            dump($payload);
        }

        /* assert */
        $component
            ->assertHasFormErrors(['title']);

        $this->assertDatabaseMissing('email_templates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_an_email_template_trough_a_modal_without_required_type(): void
    {
        /* arrange */
        $payload = [
            'title'      => 'Welcome',
            'subject'    => 'Test Email',
            'body'       => '',
            'from_name'  => 'Acme Support',
            'from_email' => 'support@acme.com',
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListEmailTemplates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        if (app()->runningUnitTests()) {
            dump($payload);
        }

        /* assert */
        $component
            ->assertHasFormErrors(['type']);

        $this->assertDatabaseMissing('email_templates', $payload);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_creates_an_email_template(): void
    {
        $this->markTestIncomplete();
        /* arrange */
        $company = Company::factory()->create();
        $payload = [
            'company_id' => $company->id,
            'subject'    => 'Welcome',
            'body'       => 'Hello world',
            'type'       => EmailTemplateType::BOOLEAN->value,
            'from_name'  => 'Acme Support',
            'from_email' => 'support@acme.com',
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateEmailTemplate::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertSuccessful()->assertHasNoFormErrors();
        $this->assertDatabaseHas('email_templates', [
            'subject' => 'Welcome',
            'body'    => 'Hello world',
        ]);
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
    # endregion

    #region spicy
    # endregion
}
