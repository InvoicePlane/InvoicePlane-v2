<?php

namespace Modules\Invoices\Peppol\Providers\Concerns;

use Carbon\Carbon;

/**
 * RefreshesOAuth2Token - Trait for providers that use OAuth2 client-credentials flow.
 *
 * Handles token persistence, expiry tracking, and automatic refresh. Providers using this
 * trait should call ensureAuthenticated() before making API requests that require a valid token.
 *
 * Persists tokens to the integration's merchant_clients table with:
 * - access_token: the bearer token itself
 * - token_expires_at: computed expiry timestamp (now + expires_in)
 *
 * On refresh, propagates the new token to all resource clients so mid-request token
 * refreshes actually reach the clients that use them.
 */
trait RefreshesOAuth2Token
{
    /**
     * Ensure OAuth2 authentication is valid, refreshing if necessary.
     *
     * Checks if a valid access token exists in config. If missing or expired:
     * - Builds a temporary client of the same type as the provider
     * - Exchanges client_id/client_secret for a new access token
     * - Persists token and expiry back to merchant_clients
     * - Propagates token to all resource clients
     *
     * @return bool true if authentication succeeded or token is valid, false otherwise
     */
    public function ensureAuthenticated(): bool
    {
        $accessToken = $this->config['access_token'] ?? null;
        $expiresAt = $this->config['token_expires_at'] ?? null;

        // Token exists and is not expired
        if ($accessToken && $expiresAt) {
            $expiry = Carbon::parse($expiresAt);
            if ($expiry->isFuture()) {
                return true;
            }
        }

        // Token missing or expired — refresh
        $clientId = $this->config['client_id'] ?? null;
        $clientSecret = $this->config['client_secret'] ?? null;

        if (!$clientId || !$clientSecret) {
            return false;
        }

        // Build temporary client to perform OAuth2 exchange
        $clientClass = $this->getOAuth2ClientClass();
        if (!$clientClass || !class_exists($clientClass)) {
            return false;
        }

        $tempClient = new $clientClass(
            app(\Modules\Invoices\Http\Contracts\HttpClientInterface::class),
            'placeholder',
            $this->getDefaultBaseUrl()
        );

        // Perform OAuth2 authentication
        if (!$tempClient->authenticate(['client_id' => $clientId, 'client_secret' => $clientSecret])) {
            return false;
        }

        // Extract token and expiry from response
        $response = $tempClient->getLastAuthResponse();
        if (!$response || !isset($response['access_token'])) {
            return false;
        }

        $newToken = $response['access_token'];
        $expiresIn = $response['expires_in'] ?? 3600;
        $expiresAt = Carbon::now()->addSeconds($expiresIn)->toDateTimeString();

        // Persist token and expiry back to merchant_clients
        if ($this->integration) {
            $this->integration->setConfig([
                'access_token'     => $newToken,
                'token_expires_at' => $expiresAt,
            ]);

            // Refresh config in memory
            $this->config = $this->integration->config;
        }

        // Propagate token to all resource clients
        $this->propagateAccessToken($newToken);

        return true;
    }

    /**
     * Get the OAuth2 client class name for this provider.
     *
     * Subclasses should override this to return the full class name of their OAuth2 client.
     * Example: return LetsPeppolClient::class;
     *
     * @return string|null the full class name of the OAuth2 client, or null if not applicable
     */
    protected function getOAuth2ClientClass(): ?string
    {
        return null;
    }

    /**
     * Propagate the access token to all resource clients.
     *
     * Subclasses should override this to call setAccessToken() on all clients
     * that were constructed in __construct().
     *
     * @param string $token the new access token
     */
    protected function propagateAccessToken(string $token): void
    {
        // Override in subclass to call setAccessToken() on each resource client
    }
}
