<?php

namespace Modules\Core\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Filament\Resources\EmailTemplateResource\Pages\CreateEmailTemplate;
use Modules\Core\Filament\Resources\EmailTemplateResource\Pages\EditEmailTemplate;
use Modules\Core\Filament\Resources\EmailTemplateResource\Pages\ManageEmailTemplates;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\tests\AbstractTestCase;

class EmailTemplatesTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    // endregion

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region CRUD Tests

    /** @test */
    public function it_shows_email_templates_index(): void
    {
        // $this->authenticate();
        EmailTemplate::factory(5)->create([
            'email_template_title' => '::email_template_title::',
        ]);

        Livewire::test(ManageEmailTemplates::class)
            ->assertStatus(200)
            ->assertSee('::email_template_title::');
    }

    /** @test */
    public function it_creates_an_email_template(): void
    {
        $this->markTestSkipped();
        // $this->authenticate();
        $payload = [
            'email_template_title'        => '::email_template_title::',
            'email_template_type'         => 'User Welcome',
            'email_template_body'         => '::email_template_body::',
            'email_template_subject'      => '::email_template_subject::',
            'email_template_from_name'    => '::email_template_from::',
            'email_template_from_email'   => '::email_template_from_mail::',
            'email_template_cc'           => '::email_template_cc::',
            'email_template_bcc'          => 'email_template_cc',
            'email_template_pdf_template' => 'default_template',
        ];

        Livewire::test(CreateEmailTemplate::class)
            ->assertStatus(200)
            ->set('data.email_template_title', $payload['email_template_title'])
            ->set('data.email_template_type', $payload['email_template_type'])
            ->set('data.email_template_body', $payload['email_template_body'])
            ->set('data.email_template_subject', $payload['email_template_subject'])
            ->set('data.email_template_from_name', $payload['email_template_from_name'])
            ->set('data.email_template_from_email', $payload['email_template_from_email'])
            ->set('data.email_template_cc', $payload['email_template_cc'])
            ->set('data.email_template_bcc', $payload['email_template_bcc'])
            ->set('data.email_template_pdf_template', $payload['email_template_pdf_template'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('email_templates', $payload);
    }

    /** @test */
    public function it_fails_to_create_an_email_template_with_missing_fields(): void
    {
        $this->markTestSkipped();
        // $this->authenticate();
        $payload = [
            'email_template_title'        => null,
            'email_template_type'         => '::email_template_title::',
            'email_template_body'         => '::email_template_body::',
            'email_template_subject'      => '::email_template_subject::',
            'email_template_from_name'    => '::email_template_from::',
            'email_template_from_email'   => '::email_template_from_mail::',
            'email_template_cc'           => '::email_template_cc::',
            'email_template_bcc'          => '::email_template_bcc::',
            'email_template_pdf_template' => '::email_template_pdf::',
        ];

        Livewire::test(CreateEmailTemplate::class)
            ->assertStatus(422)
            ->set('data.email_template_title', $payload['email_template_title'])
            ->set('data.email_template_type', $payload['email_template_type'])
            ->set('data.email_template_body', $payload['email_template_body'])
            ->set('data.email_template_subject', $payload['email_template_subject'])
            ->set('data.email_template_from_name', $payload['email_template_from_name'])
            ->set('data.email_template_from_email', $payload['email_template_from_email'])
            ->set('data.email_template_cc', $payload['email_template_cc'])
            ->set('data.email_template_bcc', $payload['email_template_bcc'])
            ->set('data.email_template_pdf_template', $payload['email_template_pdf_template'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('email_templates', $payload);
    }

    /** @test */
    public function it_updates_an_email_template(): void
    {
        // $this->authenticate();
        $this->markTestSkipped();
        $this->withoutExceptionHandling();
        $emailTemplate = EmailTemplate::factory()->create();

        $updatedData = [
            'email_template_title' => '::updated_email_template_title::',
            'email_template_type'  => '::updated_email_template_type::',
        ];

        Livewire::test(EditEmailTemplate::class, ['record' => $emailTemplate->email_template_id])
            ->set('data.email_template_title', $updatedData['email_template_title'])
            ->set('data.email_template_type', $updatedData['email_template_type'])
            ->call('save')
            ->assertStatus(200)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('email_templates', [
            'email_template_title' => $updatedData['email_template_title'],
            'email_template_type'  => $updatedData['email_template_type'],
        ]);
    }

    /** @test */
    public function it_bulk_deletes_clients(): void
    {
        // $this->authenticate();
        $emailTemplates = EmailTemplate::factory()->count(3)->create();

        Livewire::test(ManageEmailTemplates::class)
            ->callTableBulkAction('delete', $emailTemplates)
            ->assertHasNoErrors();

        foreach ($emailTemplates as $emailTemplate) {
            $this->assertDatabaseMissing('email_templates', [
                'email_template_id' => $emailTemplate->client_id,
            ]);
        }
    }

    /** @test */
    public function it_can_delete_an_email_template(): void
    {
        $this->markTestSkipped();
        $emailTemplate = EmailTemplate::factory()->create();

        Livewire::test(ManageEmailTemplates::class)
            ->callTableAction('delete', $emailTemplate->email_template_id)
            ->assertStatus(200)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('email_templates', ['email_template_id' => $emailTemplate->email_template_id]);
    }
}
