/**
 * Global authentication helpers for E2E tests
 *
 * All tests start with pre-authenticated session from global-setup.js
 * Use these helpers if a test needs to reset auth or perform logout
 */

export async function login(page, email = 'testuser@invoiceplane.test', password = 'password') {
  await page.goto('/login');
  await page.fill('[name="email"]', email);
  await page.fill('[name="password"]', password);
  await page.click('[type="submit"]');
  await page.waitForNavigation();
}

export async function logout(page) {
  await page.click('[aria-label="User menu"]');
  await page.click('text=Log out');
  await page.waitForNavigation();
}

export async function isAuthenticated(page) {
  try {
    await page.goto('/dashboard');
    return !page.url().includes('/login');
  } catch {
    return false;
  }
}
