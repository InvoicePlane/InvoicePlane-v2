/**
 * Shared E2E test configuration
 *
 * Reads from environment variables so credentials never live in source.
 * Override via .env, shell exports, or CI secrets:
 *   E2E_EMAIL, E2E_PASSWORD, E2E_BASE_URL
 */

export const E2E_EMAIL = process.env.E2E_EMAIL || 'testuser@invoiceplane.test';
export const E2E_PASSWORD = process.env.E2E_PASSWORD || 'password';
export const E2E_BASE_URL = process.env.APP_URL || process.env.E2E_BASE_URL || 'http://localhost:8000';
