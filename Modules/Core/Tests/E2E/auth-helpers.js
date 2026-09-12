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
  // Filament v5 form inputs bind via wire:model, not a native `name`
  // attribute — `[name="email"]` never matches anything real.
  await page.fill('input[type="email"]', email);
  await page.fill('input[type="password"]', password);
  await page.click('[type="submit"]');
  await page.waitForURL(new RegExp(tenantPath('/dashboard')));
}

export async function logout(page) {
  await page.click('[aria-label="User menu"]');
  // The dropdown item is a plain <button>, not an ARIA menu/menuitem —
  // Filament's dropdown doesn't use menu semantics here.
  await page.getByRole('button', { name: /log ?out/i }).click();
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
