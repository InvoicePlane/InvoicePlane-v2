<?php

namespace Modules\Core\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
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
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region CRUD Tests

    /** @test */
    public function it_can_create_an_email_template(): void
    {
        // Arrange
        $payload = [
            'email_template_title'        => 'Welcome Email',
            'email_template_type'         => 'User Welcome',
            'email_template_body'         => '<p>Hello {{ name }},</p><p>Welcome!</p>',
            'email_template_subject'      => 'Welcome to Our Service',
            'email_template_from_name'    => 'Support Team',
            'email_template_from_email'   => 'support@example.com',
            'email_template_cc'           => 'cc@example.com',
            'email_template_bcc'          => 'bcc@example.com',
            'email_template_pdf_template' => 'default_template',
        ];

        // Act
        $response = $this->post(route('filament.resources.email-templates.store'), $payload);

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('email_templates', [
            'email_template_title' => $payload['email_template_title'],
            'email_template_type'  => $payload['email_template_type'],
        ]);
    }

    /** @test */
    public function it_fails_to_create_an_email_template_with_missing_fields(): void
    {
        // Arrange
        $payload = [
            'email_template_title'        => null, // Missing required field
            'email_template_type'         => 'User Welcome',
            'email_template_body'         => '<p>Hello {{ name }},</p><p>Welcome!</p>',
            'email_template_subject'      => 'Welcome to Our Service',
            'email_template_from_name'    => 'Support Team',
            'email_template_from_email'   => 'support@example.com',
            'email_template_cc'           => 'cc@example.com',
            'email_template_bcc'          => 'bcc@example.com',
            'email_template_pdf_template' => 'default_template',
        ];

        // Act
        $response = $this->post(route('filament.resources.email-templates.store'), $payload);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email_template_title']);
    }

    /** @test */
    public function it_can_update_an_email_template(): void
    {
        // Arrange
        $template = EmailTemplate::factory()->create();

        $payload = [
            'email_template_title'        => 'Updated Title',
            'email_template_type'         => 'Updated Type',
            'email_template_body'         => '<p>Updated body content</p>',
            'email_template_subject'      => 'Updated Subject',
            'email_template_from_name'    => 'Updated Name',
            'email_template_from_email'   => 'updated@example.com',
            'email_template_cc'           => 'updated_cc@example.com',
            'email_template_bcc'          => 'updated_bcc@example.com',
            'email_template_pdf_template' => 'updated_template',
        ];

        // Act
        $response = $this->put(route('filament.resources.email-templates.update', $template->email_template_id), $payload);

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseHas('email_templates', [
            'email_template_title' => $payload['email_template_title'],
            'email_template_type'  => $payload['email_template_type'],
        ]);
    }

    /** @test */
    public function it_can_delete_an_email_template(): void
    {
        // Arrange
        $template = EmailTemplate::factory()->create();

        // Act
        $response = $this->delete(route('filament.resources.email-templates.destroy', $template->email_template_id));

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseMissing('email_templates', ['email_template_id' => $template->email_template_id]);
    }
}
