<?php

namespace Modules\Invoices\Peppol\Clients\Qonto;

use Modules\Invoices\Http\Contracts\HttpClientInterface;
use Modules\Invoices\Peppol\Clients\BasePeppolClient;

class QontoClient extends BasePeppolClient
{
    protected string $stagingToken;

    public function __construct(
        HttpClientInterface $httpClient,
        string $apiKey,
        string $baseUrl,
        string $stagingToken = ''
    ) {
        parent::__construct($httpClient, $apiKey, $baseUrl);
        $this->stagingToken = $stagingToken;
    }

    protected function getAuthenticationHeaders(): array
    {
        $headers = ['Authorization' => 'Bearer ' . $this->apiKey];

        if ($this->stagingToken) {
            $headers['X-Qonto-Staging-Token'] = $this->stagingToken;
        }

        return $headers;
    }

    protected function getTimeout(): int
    {
        return 30;
    }
}
