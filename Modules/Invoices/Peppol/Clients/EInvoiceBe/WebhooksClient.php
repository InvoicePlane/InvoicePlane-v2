<?php

namespace Modules\Invoices\Peppol\Clients\EInvoiceBe;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\RequestMethod;

/**
 * WebhooksClient - Client for managing webhook subscriptions.
 *
 * Enables receiving real-time notifications about document delivery status,
 * errors, and other events via HTTP callbacks to your application.
 *
 * API Documentation: https://api.e-invoice.be/docs#tag/webhooks
 */
class WebhooksClient extends EInvoiceBeClient
{
    /**
     * Create a new webhook subscription.
     *
     * Example request:
     * ```json
     * {
     *   "url": "https://your-app.com/webhooks/peppol",
     *   "events": ["document.delivered", "document.failed", "document.accepted"],
     *   "secret": "your-webhook-secret",
     *   "description": "Production webhook for invoice notifications",
     *   "active": true
     * }
     * ```
     *
     * Example response:
     * ```json
     * {
     *   "webhook_id": "wh_abc123def456",
     *   "url": "https://your-app.com/webhooks/peppol",
     *   "events": ["document.delivered", "document.failed", "document.accepted"],
     *   "active": true,
     *   "created_at": "2025-01-15T10:00:00Z",
     *   "signing_secret": "whsec_xyz789..."
     * }
     * ```
     *
     * @param string               $url     The webhook callback URL
     * @param array<string>        $events  Array of event types to subscribe to
     * @param array<string, mixed> $options Additional options (secret, description, etc.)
     *
     * @return Response
     */
    public function createWebhook(string $url, array $events, array $options = []): Response
    {
        $apiUrl         = $this->buildUrl('/webhooks');
        $requestOptions = $this->getRequestOptions([
            'payload' => array_merge([
                'url'    => $url,
                'events' => $events,
            ], $options),
        ]);

        return $this->client->request(RequestMethod::POST->value, $apiUrl, $requestOptions);
    }

    /**
     * List all webhook subscriptions.
     *
     * Example response:
     * ```json
     * {
     *   "webhooks": [
     *     {
     *       "webhook_id": "wh_abc123def456",
     *       "url": "https://your-app.com/webhooks/peppol",
     *       "events": ["document.delivered", "document.failed"],
     *       "active": true,
     *       "created_at": "2025-01-15T10:00:00Z",
     *       "last_delivery": {
     *         "timestamp": "2025-01-15T11:30:00Z",
     *         "success": true,
     *         "response_code": 200
     *       }
     *     }
     *   ]
     * }
     * ```
     *
     * @return Response
     */
    public function listWebhooks(): Response
    {
        $url     = $this->buildUrl('/webhooks');
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET->value, $url, $options);
    }

    /**
     * Get details of a specific webhook.
     *
     * Example response:
     * ```json
     * {
     *   "webhook_id": "wh_abc123def456",
     *   "url": "https://your-app.com/webhooks/peppol",
     *   "events": ["document.delivered", "document.failed", "document.accepted"],
     *   "active": true,
     *   "created_at": "2025-01-15T10:00:00Z",
     *   "statistics": {
     *     "total_deliveries": 1543,
     *     "successful_deliveries": 1540,
     *     "failed_deliveries": 3,
     *     "last_success": "2025-01-15T11:30:00Z",
     *     "last_failure": "2025-01-14T09:15:00Z"
     *   }
     * }
     * ```
     *
     * @param string $webhookId The webhook ID
     *
     * @return Response
     */
    public function getWebhook(string $webhookId): Response
    {
        $url     = $this->buildUrl("/webhooks/{$webhookId}");
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET->value, $url, $options);
    }

    /**
     * Update a webhook subscription.
     *
     * Example request:
     * ```json
     * {
     *   "url": "https://your-app.com/webhooks/peppol-v2",
     *   "events": ["document.delivered", "document.failed"],
     *   "active": false
     * }
     * ```
     *
     * Example response:
     * ```json
     * {
     *   "webhook_id": "wh_abc123def456",
     *   "url": "https://your-app.com/webhooks/peppol-v2",
     *   "events": ["document.delivered", "document.failed"],
     *   "active": false,
     *   "updated_at": "2025-01-15T12:00:00Z"
     * }
     * ```
     *
     * @param string               $webhookId The webhook ID
     * @param array<string, mixed> $data      Update data
     *
     * @return Response
     */
    public function updateWebhook(string $webhookId, array $data): Response
    {
        $url     = $this->buildUrl("/webhooks/{$webhookId}");
        $options = $this->getRequestOptions([
            'payload' => $data,
        ]);

        return $this->client->request(RequestMethod::PATCH->value, $url, $options);
    }

    /**
     * Delete a webhook subscription.
     *
     * Example response:
     * ```json
     * {
     *   "webhook_id": "wh_abc123def456",
     *   "deleted": true,
     *   "deleted_at": "2025-01-15T12:00:00Z"
     * }
     * ```
     *
     * @param string $webhookId The webhook ID
     *
     * @return Response
     */
    public function deleteWebhook(string $webhookId): Response
    {
        $url     = $this->buildUrl("/webhooks/{$webhookId}");
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::DELETE->value, $url, $options);
    }

    /**
     * Get delivery history for a webhook.
     *
     * Example response:
     * ```json
     * {
     *   "webhook_id": "wh_abc123def456",
     *   "deliveries": [
     *     {
     *       "delivery_id": "del_123",
     *       "event_type": "document.delivered",
     *       "timestamp": "2025-01-15T11:30:00Z",
     *       "success": true,
     *       "response_code": 200,
     *       "response_time_ms": 145,
     *       "payload": {
     *         "document_id": "DOC-123",
     *         "status": "delivered"
     *       }
     *     }
     *   ],
     *   "total": 1543,
     *   "page": 1,
     *   "per_page": 50
     * }
     * ```
     *
     * @param string $webhookId The webhook ID
     * @param int    $page      Page number
     * @param int    $perPage   Results per page
     *
     * @return Response
     */
    public function getDeliveryHistory(string $webhookId, int $page = 1, int $perPage = 50): Response
    {
        $url     = $this->buildUrl("/webhooks/{$webhookId}/deliveries");
        $options = $this->getRequestOptions([
            'payload' => [
                'page'     => $page,
                'per_page' => $perPage,
            ],
        ]);

        return $this->client->request(RequestMethod::GET->value, $url, $options);
    }

    /**
     * Test a webhook by sending a test event.
     *
     * Example request:
     * ```json
     * {
     *   "event_type": "document.delivered"
     * }
     * ```
     *
     * Example response:
     * ```json
     * {
     *   "test_delivery_id": "test_123",
     *   "sent_at": "2025-01-15T12:00:00Z",
     *   "response_code": 200,
     *   "response_time_ms": 125,
     *   "success": true,
     *   "response_body": "OK"
     * }
     * ```
     *
     * @param string $webhookId The webhook ID
     * @param string $eventType The event type to test
     *
     * @return Response
     */
    public function testWebhook(string $webhookId, string $eventType = 'document.delivered'): Response
    {
        $url     = $this->buildUrl("/webhooks/{$webhookId}/test");
        $options = $this->getRequestOptions([
            'payload' => [
                'event_type' => $eventType,
            ],
        ]);

        return $this->client->request(RequestMethod::POST->value, $url, $options);
    }

    /**
     * Regenerate webhook signing secret.
     *
     * Example response:
     * ```json
     * {
     *   "webhook_id": "wh_abc123def456",
     *   "signing_secret": "whsec_new789...",
     *   "regenerated_at": "2025-01-15T12:00:00Z"
     * }
     * ```
     *
     * @param string $webhookId The webhook ID
     *
     * @return Response
     */
    public function regenerateSecret(string $webhookId): Response
    {
        $url     = $this->buildUrl("/webhooks/{$webhookId}/regenerate-secret");
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::POST->value, $url, $options);
    }
}
