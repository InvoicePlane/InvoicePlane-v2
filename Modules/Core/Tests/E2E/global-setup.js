import { chromium } from '@playwright/test';
import { E2E_EMAIL, E2E_PASSWORD, E2E_BASE_URL } from './config.js';
import { tenantPath } from './tenant-path.js';

const authFile = 'auth.json';

export default async function globalSetup() {
  const browser = await chromium.launch();
  const context = await browser.newContext({ baseURL: E2E_BASE_URL });
  const page = await context.newPage();

  // Login route is NOT tenant-scoped.
  await page.goto('/login');

  // Filament v5 form inputs bind via wire:model, not a native `name`
  // attribute — `[name="email"]` never matches anything real, and this
  // step silently timed out on every run.
  await page.fill('input[type="email"]', E2E_EMAIL);
  await page.fill('input[type="password"]', E2E_PASSWORD);
  await page.click('[type="submit"]');

  // LoginResponse redirects into the tenant-scoped dashboard. Every request
  // in this dev environment pays Xdebug step-debug connection overhead, so
  // under sustained load (e.g. several full-suite runs back to back) the
  // default 30s action timeout has been observed to trip here even though
  // login itself is working correctly — this runs once for the whole
  // suite, so a generous timeout is cheap insurance against that, not a
  // mask for a real bug.
  await page.waitForURL(new RegExp(tenantPath('/dashboard')), { timeout: 60000 });

  await context.storageState({ path: authFile });

  await browser.close();
}
