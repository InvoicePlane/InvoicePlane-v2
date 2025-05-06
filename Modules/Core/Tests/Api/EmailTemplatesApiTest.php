<?php

namespace Modules\Core\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Sanctum\Sanctum;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Core\Tests\ApiTestTrait;

class EmailTemplatesApiTest extends AbstractTestCase
{
    use ApiTestTrait;
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

    public function it_returns_email_templates_index(): void
    {
        EmailTemplate::factory(5)->create([
            'email_template_title' => '::email_template_title::',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->get(route('api.email_templates.index'));
        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                ],
            ],
        ]);
        $response->assertJsonFragment(['title' => '::email_template_title::']);
    }

    public function it_creates_an_email_template(): void
    {
        $initialEmailTemplate = EmailTemplate::factory()->create([
            'email_template_title' => '::email_template_title::',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->post(route('api.email_templates.store'), [
            'email_template_title' => '::email_template_title::',
        ]);

        $response->assertSuccessful();

        $initialEmailTemplate->refresh();

        $response->assertJsonFragment(['title' => '::email_template_title::']);
    }

    public function it_updates_an_email_template(): void
    {
        $initialEmailTemplate = EmailTemplate::factory()->create([
            'email_template_title' => '::email_template_title::',
        ]);

        $updatedData = [
            'email_template_title' => '::updated_email_template_title::',
        ];

        Sanctum::actingAs(User::factory()->create());

        $response = $this->put(route('api.email_templates.update', ['emailTemplate' => $initialEmailTemplate->email_template_id]), $updatedData);

        $response->assertSuccessful();

        $initialEmailTemplate->refresh();

        $response->assertJsonFragment(['title' => $updatedData['email_template_title']]);
    }

    public function it_deletes_an_email_template(): void
    {
        $initialEmailTemplate = EmailTemplate::factory()->create([
            'email_template_title' => '::email_template_title::',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->deleteJson(
            route('api.email_templates.destroy', ['emailTemplate' => $initialEmailTemplate->email_template_id])
        );

        $response->assertSuccessful();

        $getEmailTemplateResponse = $this->getJson(
            route(
                'api.email_templates.show',
                ['emailTemplate' => $initialEmailTemplate->email_template_id]
            )
        );
        $getEmailTemplateResponse->assertNotFound();
    }
}
