<?php

namespace Modules\Core\Tests;

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
            if (in_array($key, ['client_date_created', 'client_date_modified'])) {
                continue;
            }
            $this->assertEquals($actualData[$key], $expectedData[$key]);
        }
    }
}
