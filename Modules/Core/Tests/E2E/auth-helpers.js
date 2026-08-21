/**
 * Global authentication helpers for E2E tests.
 *
 * Every test starts with a pre-authenticated session restored from
 * global-setup.js's storageState — no per-test login is needed. Reach for
 * these helpers only when a test specifically exercises the auth flow
 * itself (e.g. login/logout behavior).
 */

import { E2E_EMAIL, E2E_PASSWORD } from './config.js';
import { tenantPath } from './tenant-path.js';

export async function login(page, email = E2E_EMAIL, password = E2E_PASSWORD) {
  await page.goto('/login');
  await page.fill('[name="email"]', email);
  await page.fill('[name="password"]', password);
  await page.click('[type="submit"]');
  await page.waitForURL(new RegExp(tenantPath('/dashboard')));
}

export async function logout(page) {
  await page.click('[aria-label="User menu"]');
  await page.getByRole('menuitem', { name: /log ?out/i }).click();
  await page.waitForURL(/\/login/);
}

export async function isAuthenticated(page) {
  try {
    await page.goto(tenantPath('/dashboard'));

    return !page.url().includes('/login');
  } catch {
    return false;
  }
}
