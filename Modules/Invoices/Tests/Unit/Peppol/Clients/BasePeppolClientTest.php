<?php

namespace Modules\Invoices\Tests\Unit\Peppol\Clients;

use Illuminate\Http\Client\Response;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Http\Contracts\HttpClientInterface;
use Modules\Invoices\Http\RequestMethod;
use Modules\Invoices\Peppol\Clients\BasePeppolClient;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * BasePeppolClientTest - Unit tests for BasePeppolClient.
 *
 * Tests the base class for all Peppol provider API clients, verifying
 * HTTP client dependency injection, authentication headers, URL building, and timeout handling.
 */
#[Group('peppol')]
class BasePeppolClientTest extends AbstractTestCase
{
    private TestPeppolClient $client;
    private MockHttpClient $mockClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockClient = new MockHttpClient();
        $this->client     = new TestPeppolClient(
            $this->mockClient,
            'test-api-key',
            'https://api.example.com'
        );
    }

    #[Test]
    public function it_constructs_with_http_client_interface(): void
    {
        $this->assertInstanceOf(BasePeppolClient::class, $this->client);
        $this->assertInstanceOf(HttpClientInterface::class, $this->mockClient);
    }

    #[Test]
    public function it_builds_urls_correctly(): void
    {
        $url = $this->client->testBuildUrl('/documents');
        $this->assertEquals('https://api.example.com/documents', $url);
    }

    #[Test]
    public function it_builds_urls_without_double_slashes(): void
    {
        $url = $this->client->testBuildUrl('documents');
        $this->assertEquals('https://api.example.com/documents', $url);
    }

    #[Test]
    public function it_builds_urls_with_nested_paths(): void
    {
        $url = $this->client->testBuildUrl('/api/v1/documents/123');
        $this->assertEquals('https://api.example.com/api/v1/documents/123', $url);
    }

    #[Test]
    public function it_strips_trailing_slash_from_base_url(): void
    {
        $client = new TestPeppolClient(
            $this->mockClient,
            'api-key',
            'https://api.example.com/'
        );

        $url = $client->testBuildUrl('/documents');
        $this->assertEquals('https://api.example.com/documents', $url);
    }

    #[Test]
    public function it_merges_authentication_headers_into_request_options(): void
    {
        $options = $this->client->testGetRequestOptions([
            'headers' => ['X-Custom' => 'value'],
        ]);

        $this->assertArrayHasKey('headers', $options);
        $this->assertArrayHasKey('Authorization', $options['headers']);
        $this->assertEquals('Bearer test-token', $options['headers']['Authorization']);
        $this->assertEquals('value', $options['headers']['X-Custom']);
    }

    #[Test]
    public function it_gives_authentication_headers_precedence_over_caller_headers(): void
    {
        $options = $this->client->testGetRequestOptions([
            'headers' => ['Authorization' => 'Bearer wrong-token'],
        ]);

        $this->assertEquals('Bearer test-token', $options['headers']['Authorization']);
    }

    #[Test]
    public function it_sets_default_timeout_when_not_provided(): void
    {
        $options = $this->client->testGetRequestOptions();

        $this->assertArrayHasKey('timeout', $options);
        $this->assertEquals(60, $options['timeout']);
    }

    #[Test]
    public function it_respects_custom_timeout(): void
    {
        $options = $this->client->testGetRequestOptions(['timeout' => 120]);

        $this->assertEquals(120, $options['timeout']);
    }

    #[Test]
    public function it_returns_http_client_interface(): void
    {
        $returnedClient = $this->client->getClient();

        $this->assertInstanceOf(HttpClientInterface::class, $returnedClient);
        $this->assertSame($this->mockClient, $returnedClient);
    }

    #[Test]
    public function it_has_logging_trait(): void
    {
        $this->assertTrue(method_exists($this->client, 'logPeppolInfo'));
        $this->assertTrue(method_exists($this->client, 'logPeppolError'));
        $this->assertTrue(method_exists($this->client, 'logPeppolWarning'));
        $this->assertTrue(method_exists($this->client, 'logPeppolDebug'));
    }
}

/**
 * MockHttpClient - Test implementation of HttpClientInterface.
 */
class MockHttpClient implements HttpClientInterface
{
    public function request(RequestMethod|string $method, string $uri, array $options = []): Response
    {
        return new Response(new \Illuminate\Http\Client\Request(
            $method instanceof RequestMethod ? $method->value : $method,
            $uri
        ), new \GuzzleHttp\Psr7\Response());
    }
}

/**
 * TestPeppolClient - Concrete test implementation of BasePeppolClient.
 */
class TestPeppolClient extends BasePeppolClient
{
    protected function getAuthenticationHeaders(): array
    {
        return ['Authorization' => 'Bearer test-token'];
    }

    public function testBuildUrl(string $path): string
    {
        return $this->buildUrl($path);
    }

    public function testGetRequestOptions(array $options = []): array
    {
        return $this->getRequestOptions($options);
    }
}
