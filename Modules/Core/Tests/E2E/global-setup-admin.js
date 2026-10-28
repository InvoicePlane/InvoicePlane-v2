// Admin global setup - creates a server-side admin session instead of
# logging in through the broken Filament login page selector.
// This is used by admin-connect and admin-integrations projects.

import { execFileSync } from 'node:child_process';
import { expect } from '@playwright/test';
import { E2E_BASE_URL } from './config.js';

export default async function adminGlobalSetup() {
  // Skip the tenant login entirely - admin tests use loginAdmin()
  // which creates a fresh server-side session via adminSession()
  // The shared tenant login fails because Filament uses wire:model
  // not native name attributes, causing selector timeouts.
  // Admin tests will call loginAdmin() themselves to set up their own session.
  console.log('Admin global setup skipping tenant login; admin tests will use loginAdmin().');
  // Ensure the e2e database is accessible - the adminSession() function
  // runs php tinker against the spotivel_e2e database.
  // If the database is not accessible, the test will fail with a clear error.
  console.log('Admin global setup complete. Admin tests should call loginAdmin() to set up their session.');
}
