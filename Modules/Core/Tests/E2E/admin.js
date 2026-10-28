import { execFileSync } from 'node:child_process';
import { expect } from '@playwright/test';
import { E2E_BASE_URL } from './config.js';

/**
 * A fresh server-side session for the seeded administrator, instead of the one shared by every test.
 *
 * Filament throttles logins, so the session is written directly (the e2e vhost keeps sessions in files).
 * Sharing the global-setup session made parallel admin tests consume each other's flash notifications
 * and session state, and let connector credentials/tokens be wiped mid-OAuth-flow.
 */
export function adminSession() {
    return php(`
        $user = \\Modules\\Core\\Models\\User::where('email', '${ADMIN_EMAIL}')->firstOrFail();
        $guard = \\Illuminate\\Support\\Facades\\Auth::guard('web');
        $store = app('session')->driver('file');
        $store->setId(\\Illuminate\\Support\\Str::random(40));
        $store->put((new \\ReflectionMethod($guard, 'getName'))->invoke($guard), $user->id);
        $store->put('password_hash_web', $user->getAuthPassword());
        $store->regenerateToken();
        $store->save();
        $id = $store->getId();
        $cookie = config('session.cookie');
        $encrypter = app('encrypter');

        return ['name' => $cookie, 'value' => $encrypter->encrypt(\\Illuminate\\Cookie\\CookieValuePrefix::create($cookie, $encrypter->getKey()) . $id, false)];
    `);
}

/** Open the admin panel as the seeded administrator, in a session of this test only. */
export async function loginAdmin(page) {
    const session = adminSession();
    await page.context().addCookies([{ name: session.name, value: session.value, domain: new URL(E2E_BASE_URL).hostname, path: '/', httpOnly: true }]);
    await page.goto('/admin/tracks');
    await expect(page.getByTestId('connect-wrapper')).toBeVisible({ timeout: SLOW_NAVIGATION });
}
