/**
 * Shared E2E test configuration.
 *
 * Reads from environment variables so credentials and environment specifics
 * never live in source. Override via .env, shell exports, or CI secrets:
 *   E2E_EMAIL, E2E_PASSWORD, E2E_TENANT, APP_URL
 *
 * Defaults match database/seeders/DatabaseSeeder.php: the super_admin
 * account is attached to the `ivplv2` company by every fresh seed.
 */

export const E2E_EMAIL = process.env.E2E_EMAIL || 'admin@invoiceplane.com';
export const E2E_PASSWORD = process.env.E2E_PASSWORD || 'password';

// search_code of the company (tenant) to run tests against. Company panel
// routes are tenant-scoped: {tenant}/invoices, {tenant}/dashboard, etc.
// The login route itself is NOT tenant-scoped.
export const E2E_TENANT = process.env.E2E_TENANT || 'ivplv2';

export const E2E_BASE_URL = process.env.APP_URL || process.env.E2E_BASE_URL || 'http://localhost:8000';
