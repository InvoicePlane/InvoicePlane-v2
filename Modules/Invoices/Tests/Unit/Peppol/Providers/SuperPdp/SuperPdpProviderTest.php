<?php

namespace Modules\Invoices\Tests\Unit\Peppol\Providers\SuperPdp;

use Illuminate\Support\Facades\Http;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Peppol\Providers\SuperPdp\SuperPdpProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;

/**
 * SuperPdpProviderTest - proves OAuth2 authentication is actually attempted on the real
 * send/status/test-connection paths (previously RefreshesOAuth2Token::ensureAuthenticated()
 * was never called from any of them — see InvoicePlane-v2#769).
 */
#[Group('peppol')]
class SuperPdpProviderTest extends AbstractTestCase
{
    #[Test]
    public function it_fails_test_connection_without_credentials(): void
    {
        /* Arrange */
        $provider = new SuperPdpProvider();

        /* Act */
        $result = $provider->testConnection([]);

        /* Assert */
        $this->assertFalse($result['ok']);
    }

    #[Test]
    public function it_succeeds_test_connection_when_the_oauth2_token_exchange_succeeds(): void
    {
        /* Arrange */
        Http::fake([
            'https://auth.superpdp.com/oauth/token' => Http::response([
                'access_token' => 'fresh-token',
                'expires_in'   => 3600,
            ], 200),
        ]);

        $provider = $this->providerWithConfig([
            'client_id'     => 'test-client-id',
            'client_secret' => 'test-client-secret',
        ]);

        /* Act */
        $result = $provider->testConnection([]);

        /* Assert */
        $this->assertTrue($result['ok']);
        Http::assertSent(fn ($request) => $request->url() === 'https://auth.superpdp.com/oauth/token'
            && $request['client_id'] === 'test-client-id'
            && $request['client_secret'] === 'test-client-secret');
    }

    #[Test]
    public function it_refuses_to_send_an_invoice_when_authentication_fails_and_never_reaches_the_send_endpoint(): void
    {
        /* Arrange */
        Http::fake(); // no credentials configured, so no request should ever go out
        $provider = new SuperPdpProvider();

        /* Act */
        $result = $provider->sendInvoice(['invoice' => (object) [], 'recipient_id' => '123', 'recipient_scheme' => '0088']);

        /* Assert */
        $this->assertFalse($result['accepted']);
        $this->assertSame(401, $result['status_code']);
        Http::assertNothingSent();
    }

    private function providerWithConfig(array $config): SuperPdpProvider
    {
        $provider = new SuperPdpProvider();

        $property = new ReflectionProperty($provider, 'config');
        $property->setAccessible(true);
        $property->setValue($provider, $config);

        return $provider;
    }
}
