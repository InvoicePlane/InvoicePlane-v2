import { defineConfig, devices } from '@playwright/test';
import path from 'path';
import { E2E_BASE_URL } from './Modules/Core/Tests/E2E/config.js';

export default defineConfig({
  // testDir does not support glob patterns (only testMatch does) — the
  // literal directory './Modules/*/Tests/E2E' never existed, so this
  // config discovered zero tests no matter what ran `npx playwright test`.
  // Scoped to `Modules/` (not repo root) so the directory walk never touches
  // storage/ (root-owned files from Docker runs make it unreadable) or
  // vendor/node_modules.
  testDir: './Modules',
  testMatch: '*/Tests/E2E/**/*.spec.js',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: [
    ['html'],
    ['github'],
    ['list'],
    ['./Modules/Core/Tests/E2E/error-summary-reporter.js'],
  ],
  globalSetup: path.resolve('./Modules/Core/Tests/E2E/global-setup.js'),
  use: {
    baseURL: E2E_BASE_URL,
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    storageState: 'auth.json',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],

  webServer: process.env.CI ? undefined : {
    command: 'php artisan serve',
    url: 'http://localhost:8000',
    reuseExistingServer: !process.env.CI,
    timeout: 120 * 1000,
  },
});
