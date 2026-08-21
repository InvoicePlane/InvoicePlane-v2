import { chromium } from '@playwright/test';
import { E2E_EMAIL, E2E_PASSWORD, E2E_BASE_URL } from './config.js';

const authFile = 'auth.json';

export default async function globalSetup() {
  const browser = await chromium.launch();
  const context = await browser.newContext({ baseURL: E2E_BASE_URL });
  const page = await context.newPage();

  // Navigate to login page
  await page.goto('/login');

  // Perform login
  await page.fill('[name="email"]', E2E_EMAIL);
  await page.fill('[name="password"]', E2E_PASSWORD);
  await page.click('[type="submit"]');

  // Wait for navigation to dashboard
  await page.waitForNavigation();
  await page.goto('/dashboard');

  // Save authenticated state (cookies, storage, etc.)
  await context.storageState({ path: authFile });

  await browser.close();
}
