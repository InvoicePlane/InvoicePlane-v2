<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * mind-the-gap: a local `php artisan test` run must fail on the same
 * "toolchain out of sync" conditions CI fails on — the class of bug where a
 * green local run still reds the pipeline in a step that never even reached
 * the tests.
 *
 * The trap, three ways:
 *  - `yarn install` with no flags SILENTLY rewrites yarn.lock to match
 *    package.json, so a stale lockfile passes locally forever — until a CI
 *    job runs `yarn install --frozen-lockfile`, refuses to touch it, and
 *    dies in setup before a single test runs. Real incident: @playwright/test
 *    and the tailwind/rolldown platform binaries were missing from yarn.lock,
 *    so phpunit.yml and quickstart.yml both failed at "Install JS dependencies".
 *  - composer.lock drifting from composer.json is the same story vs. CI's
 *    `composer install`.
 *  - a never-built Vite manifest: feature tests that render a Blade view with
 *    @vite(...) (e.g. GuestQuoteViewTest) — and the whole Playwright E2E
 *    suite — 500 the moment it's missing; every CI job that renders the app
 *    runs `yarn build` first (see CiWorkflowAssetBuildAuditTest).
 *
 * Each check shells out to the same tool CI uses and reads its output, so the
 * drift surfaces where you already look. Skips cleanly when the tool isn't on
 * PATH (a bare PHP-only box) — the same "no environment, no assertion" stance
 * the mind-the-gap-again E2E helper takes when Docker isn't reachable.
 */
final class ToolchainMatchesCiTest extends AbstractTestCase
{
    #[Test]
    public function yarn_lock_is_in_sync_with_package_json(): void
    {
        if ( ! $this->canShellOut() || ! $this->onPath('yarn')) {
            self::markTestSkipped('`yarn` not runnable here — skipping the CI-parity lockfile check.');
        }

        // --dry-run writes nothing; the "needs updating" line still prints.
        [$out] = $this->shell('yarn install --frozen-lockfile --dry-run --non-interactive');

        self::assertStringNotContainsString(
            'lockfile needs to be updated',
            $out,
            "yarn.lock is stale — `yarn install --frozen-lockfile` (what every CI JS job runs) would fail here.\n"
            . "Fix: run `yarn install`, then commit the updated yarn.lock.\n\n--- yarn output ---\n" . $out,
        );
    }

    #[Test]
    public function composer_lock_is_in_sync_with_composer_json(): void
    {
        if ( ! $this->canShellOut() || ! $this->onPath('composer')) {
            self::markTestSkipped('`composer` not runnable here — skipping the CI-parity lockfile check.');
        }

        // Not --strict: that also errors on unbound-version-constraint warnings,
        // which have nothing to do with lock sync. Read the message instead —
        // `composer validate` reports lock drift but (with --no-check-all)
        // exits 0 on it, so the exit code can't be trusted here.
        [$out] = $this->shell('composer validate --no-check-all --no-check-publish --no-interaction');

        self::assertStringNotContainsString(
            'lock file is not up to date',
            $out,
            "composer.lock is out of sync with composer.json — CI's `composer install` would resolve stale deps.\n"
            . "Fix: run `composer update --lock` (or `composer require` / `composer update <pkg>`), then commit.\n\n"
            . "--- composer output ---\n" . $out,
        );
    }

    #[Test]
    public function the_vite_manifest_has_been_built(): void
    {
        self::assertFileExists(
            public_path('build/manifest.json'),
            'public/build/manifest.json is missing — run `yarn build`. Feature tests that render an '
            . '@vite(...) Blade view (e.g. GuestQuoteViewTest) and the whole Playwright E2E suite 500 without it; '
            . 'every CI job that renders the app builds it first (see CiWorkflowAssetBuildAuditTest).',
        );
    }

    private function canShellOut(): bool
    {
        return function_exists('exec') && ! in_array('exec', array_map('trim', explode(',', (string) ini_get('disable_functions'))), true);
    }

    private function onPath(string $bin): bool
    {
        [, $exit] = $this->shell('command -v ' . escapeshellarg($bin));

        return $exit === 0;
    }

    /** @return array{0: string, 1: int} */
    private function shell(string $command): array
    {
        $output = [];
        $exit   = 0;
        exec('cd ' . escapeshellarg(base_path()) . ' && ' . $command . ' 2>&1', $output, $exit);

        return [implode("\n", $output), $exit];
    }
}
