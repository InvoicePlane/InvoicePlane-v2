import { test as base } from '@playwright/test';

const TEST_EMAIL = 'testuser@invoiceplane.test';
const TEST_PASSWORD = 'password';

export const test = base.extend({
  authenticatedPage: async ({ page }, use) => {
    // Login before test
    await page.goto('/login');
    await page.fill('[name="email"]', TEST_EMAIL);
    await page.fill('[name="password"]', TEST_PASSWORD);
    await page.click('[type="submit"]');
    await page.waitForNavigation();

    // Provide authenticated page to test
    await use(page);

    // Logout after test (optional cleanup)
    await page.goto('/logout');
  },
});

export const { expect } = test;
