import { chromium } from '@playwright/test';

const authFile = 'auth.json';

export default async function globalSetup() {
  const browser = await chromium.launch();
  const context = await browser.newContext();
  const page = await context.newPage();

  // Navigate to login page
  await page.goto('/login');

  // Perform login
  await page.fill('[name="email"]', 'testuser@invoiceplane.test');
  await page.fill('[name="password"]', 'password');
  await page.click('[type="submit"]');

  // Wait for navigation to dashboard
  await page.waitForNavigation();
  await page.goto('/dashboard');

  // Save authenticated state (cookies, storage, etc.)
  await context.storageState({ path: authFile });

  await browser.close();
}
