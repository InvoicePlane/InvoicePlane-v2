<?php

namespace Modules\Invoices\Tests\Feature;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\MerchantClient;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Pages\CreatePeppolIntegration;
use Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Pages\EditPeppolIntegration;
use Modules\Invoices\Models\PeppolIntegration;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * PeppolIntegrationCredentialFormTest - proves the credential-entry form actually collects
 * and persists real values (InvoicePlane-v2#768 — it was previously a static Placeholder
 * with no input fields at all).
 */
#[Group('peppol')]
class PeppolIntegrationCredentialFormTest extends AbstractAdminPanelTestCase
{
    use WithoutMiddleware;

    #[Test]
    public function it_renders_the_selected_providers_declared_credential_fields(): void
    {
        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreatePeppolIntegration::class)
            ->fillForm(['provider_name' => 'storecove']);

        /* Assert */
        $component->assertFormFieldExists('api_key');
        $component->assertFormFieldExists('legal_entity_id');
    }

    #[Test]
    public function it_persists_only_the_providers_declared_credential_fields_when_creating_an_integration(): void
    {
        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreatePeppolIntegration::class)
            ->fillForm([
                'company_id'      => $this->company->id,
                'provider_name'   => 'storecove',
                'enabled'         => false,
                'api_key'         => 'sk_test_123',
                'legal_entity_id' => '999',
            ])
            ->call('create');

        /* Assert */
        $component->assertHasNoFormErrors();

        $integration = PeppolIntegration::withoutGlobalScopes()
            ->where('company_id', $this->company->id)
            ->where('provider_name', 'storecove')
            ->firstOrFail();

        // merchant_value is encrypted at rest — compare the decrypted attribute, not the raw column.
        $legalEntityRow = MerchantClient::withoutGlobalScopes()
            ->where('company_id', $this->company->id)
            ->where('driver', 'storecove')
            ->where('merchant_key', 'legal_entity_id')
            ->firstOrFail();
        $this->assertSame('999', $legalEntityRow->merchant_value);

        $rows = MerchantClient::withoutGlobalScopes()
            ->where('company_id', $this->company->id)
            ->where('driver', 'storecove')
            ->pluck('merchant_key');

        // Only the provider's declared settings keys were persisted — not the model's own
        // company_id/provider_name/enabled fields, which handleRecordCreation() passes through
        // in the same $data array.
        $this->assertEqualsCanonicalizing(['api_key', 'legal_entity_id'], $rows->all());
        $this->assertSame($integration->company_id, $this->company->id);
    }

    #[Test]
    public function it_never_persists_the_system_managed_access_token_field_for_oauth2_providers(): void
    {
        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreatePeppolIntegration::class)
            ->fillForm([
                'company_id'    => $this->company->id,
                'provider_name' => 'lets_peppol',
                'enabled'       => false,
                'client_id'     => 'test-client-id',
                'client_secret' => 'test-client-secret',
                'access_token'  => 'should-never-be-persisted-this-way',
            ])
            ->call('create');

        /* Assert */
        $component->assertHasNoFormErrors();

        $rows = MerchantClient::withoutGlobalScopes()
            ->where('company_id', $this->company->id)
            ->where('driver', 'lets_peppol')
            ->pluck('merchant_value', 'merchant_key');

        $this->assertSame('test-client-id', $rows['client_id']);
        $this->assertSame('test-client-secret', $rows['client_secret']);
        $this->assertArrayNotHasKey('access_token', $rows);
    }

    #[Test]
    public function it_prefills_existing_credential_values_when_editing_an_integration(): void
    {
        /* Arrange */
        $integration = PeppolIntegration::withoutGlobalScopes()->create([
            'company_id'    => $this->company->id,
            'provider_name' => 'storecove',
            'enabled'       => true,
        ]);

        MerchantClient::withoutGlobalScopes()->create([
            'company_id'     => $this->company->id,
            'driver'         => 'storecove',
            'merchant_key'   => 'api_key',
            'merchant_value' => 'existing-api-key',
        ]);

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(EditPeppolIntegration::class, ['record' => $integration->getKey()]);

        /* Assert */
        $component->assertFormSet(['api_key' => 'existing-api-key']);
    }
}
