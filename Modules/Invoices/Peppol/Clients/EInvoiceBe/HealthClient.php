<?php

namespace Modules\Invoices\Peppol\Clients\EInvoiceBe;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\RequestMethod;

/**
 * HealthClient - Client for API health checks and status monitoring.
 *
 * Provides methods to check if the e-invoice.be API is operational and
 * retrieve system status information. Useful for monitoring and alerting.
 *
 * API Documentation: https://api.e-invoice.be/docs#tag/health
 */
class HealthClient extends EInvoiceBeClient
{
    /**
     * Ping the API to check if it's responsive.
     *
     * Simple endpoint that returns immediately to verify API connectivity.
     *
     * Example response:
     * ```json
     * {
     *   "status": "ok",
     *   "timestamp": "2025-01-15T10:00:00Z",
     *   "version": "2.0.1"
     * }
     * ```
     *
     * @return Response
     */
    public function ping(): Response
    {
        $url     = $this->buildUrl('/health/ping');
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET->value, $url, $options);
    }

    /**
     * Get comprehensive health status of the API.
     *
     * Example response:
     * ```json
     * {
     *   "status": "healthy",
     *   "timestamp": "2025-01-15T10:00:00Z",
     *   "version": "2.0.1",
     *   "components": {
     *     "database": {
     *       "status": "up",
     *       "response_time_ms": 15
     *     },
     *     "peppol_network": {
     *       "status": "up",
     *       "sml_accessible": true,
     *       "smp_queries": "operational"
     *     },
     *     "document_processing": {
     *       "status": "up",
     *       "queue_length": 42,
     *       "average_processing_time_ms": 350
     *     }
     *   },
     *   "uptime_seconds": 2592000,
     *   "last_restart": "2025-01-01T00:00:00Z"
     * }
     * ```
     *
     * @return Response
     */
    public function getStatus(): Response
    {
        $url     = $this->buildUrl('/health/status');
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET->value, $url, $options);
    }

    /**
     * Get detailed system metrics.
     *
     * Example response:
     * ```json
     * {
     *   "metrics": {
     *     "requests_per_minute": 125,
     *     "active_connections": 42,
     *     "documents_processed_today": 1543,
     *     "documents_in_queue": 12,
     *     "average_response_time_ms": 245,
     *     "error_rate_percent": 0.02
     *   },
     *   "resource_usage": {
     *     "cpu_percent": 35,
     *     "memory_used_mb": 2048,
     *     "memory_total_mb": 8192,
     *     "disk_used_percent": 45
     *   },
     *   "timestamp": "2025-01-15T10:00:00Z"
     * }
     * ```
     *
     * @return Response
     */
    public function getMetrics(): Response
    {
        $url     = $this->buildUrl('/health/metrics');
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET->value, $url, $options);
    }

    /**
     * Check connectivity to Peppol network components.
     *
     * Example response:
     * ```json
     * {
     *   "peppol_connectivity": {
     *     "sml_status": "reachable",
     *     "sml_response_time_ms": 125,
     *     "smp_queries_operational": true,
     *     "access_points_reachable": 245,
     *     "network_issues": []
     *   },
     *   "last_check": "2025-01-15T09:59:30Z",
     *   "next_check": "2025-01-15T10:04:30Z"
     * }
     * ```
     *
     * @return Response
     */
    public function checkPeppolConnectivity(): Response
    {
        $url     = $this->buildUrl('/health/peppol');
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET->value, $url, $options);
    }

    /**
     * Get API version information.
     *
     * Example response:
     * ```json
     * {
     *   "version": "2.0.1",
     *   "build_date": "2025-01-10",
     *   "environment": "production",
     *   "api_endpoints": {
     *     "documents": "/api/documents",
     *     "participants": "/api/participants",
     *     "tracking": "/api/tracking",
     *     "webhooks": "/api/webhooks"
     *   },
     *   "supported_formats": [
     *     "PEPPOL_BIS_3.0",
     *     "UBL_2.1",
     *     "UBL_2.4",
     *     "CII"
     *   ]
     * }
     * ```
     *
     * @return Response
     */
    public function getVersion(): Response
    {
        $url     = $this->buildUrl('/health/version');
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET->value, $url, $options);
    }

    /**
     * Perform a readiness check (for load balancers).
     *
     * Returns 200 OK only if the service is ready to accept requests.
     *
     * Example response:
     * ```json
     * {
     *   "ready": true,
     *   "checks": {
     *     "database": "ready",
     *     "peppol_network": "ready",
     *     "queue_processor": "ready"
     *   }
     * }
     * ```
     *
     * @return Response
     */
    public function checkReadiness(): Response
    {
        $url     = $this->buildUrl('/health/ready');
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET->value, $url, $options);
    }

    /**
     * Perform a liveness check (for orchestrators like Kubernetes).
     *
     * Returns 200 OK if the service is alive (even if not ready).
     *
     * Example response:
     * ```json
     * {
     *   "alive": true
     * }
     * ```
     *
     * @return Response
     */
    public function checkLiveness(): Response
    {
        $url     = $this->buildUrl('/health/live');
        $options = $this->getRequestOptions();

        return $this->client->request(RequestMethod::GET->value, $url, $options);
    }
}
