<?php

namespace Modules\Core\Tests;

use Modules\Core\Tests\ApiTestTrait;

trait ApiTestTrait
{
    private $response;

    public function assertApiResponse(array $actualData): void
    {
        $this->assertApiSuccess();

        $response     = json_decode($this->response->getContent(), true);
        $responseData = $response['data'];

        $this->assertModelData($actualData, $responseData);
    }

    public function assertApiSuccess(): void
    {
        $this->response->assertSuccessful();
        $this->response->assertJson(['success' => true]);
    }

    public function assertModelData(array $actualData, array $expectedData): void
    {
        foreach ($actualData as $key => $value) {
            if ($key === 'client_date_created') {
                dd($key);
            }
            if (in_array($key, ['created_at', 'updated_at', 'client_date_created', 'client_date_modified'])) {
                continue;
            }
            $this->assertEquals($actualData[$key], $expectedData[$key]);
        }
    }
}
