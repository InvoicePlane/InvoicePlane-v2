import { chromium } from '@playwright/test';
import { E2E_EMAIL, E2E_PASSWORD, E2E_BASE_URL } from './config.js';
import { tenantPath } from './tenant-path.js';

const authFile = 'auth.json';

export default async function globalSetup() {
  const SKIP_LOGIN = typeof process !== 'undefined' && process.env && process.env.SKIP_LOGIN === '1';

  const browser = await chromium.launch();

  if (SKIP_LOGIN) {
    console.log('SKIP_LOGIN enabled: skipping tenant login for admin projects.');
  } else {
    const context = await browser.newContext({ baseURL: E2E_BASE_URL });
    const page = await context.newPage();

    await page.goto('/login');

    await page.getByRole('textbox').fill(E2E_EMAIL);
    await page.fill('input[type="password"]', E2E_PASSWORD);
    await page.click('[type="submit"]');

    await page.waitForURL(new RegExp(tenantPath('/dashboard')), { timeout: 60000 });

    await context.storageState({ path: authFile });
  }

  await browser.close();
}
