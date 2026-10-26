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
 *    dies in setup before a single test runs. Real incident: the
 *    playwright/test dependency and the tailwind/rolldown platform binaries
 *    were missing from yarn.lock, so phpunit.yml and quickstart.yml both
 *    failed at "Install JS dependencies".
 *  - composer.lock drifting from composer.json is the same story vs. CI's
 *    `composer install`.
 *  - a never-built Vite manifest: feature tests that render a Blade view
 *    through a Vite directive (e.g. GuestQuoteViewTest) — and the whole
 *    Playwright E2E suite — 500 the moment it's missing; every CI job that
 *    renders the app runs `yarn build` first (see CiWorkflowAssetBuildAuditTest).
 *
 * Each check shells out to the same tool CI uses and reads its output, so the
 * drift surfaces where you already look. In CI a missing tool or a disabled
 * exec() fails loudly (requireToolOrSkip); locally it skips.
 */
final class ToolchainMatchesCiTest extends AbstractTestCase
{
    #[Test]
    public function yarn_lock_is_in_sync_with_package_json(): void
    {
        $this->requireToolOrSkip('yarn');

        // No --dry-run: it swallows the non-zero exit, leaving only a string
        // to match. `yarn install --frozen-lockfile` is a fast no-op on a
        // clean lock (exit 0, writes nothing) and exits 1 without touching
        // yarn.lock on a stale one — so the exit code IS the signal. Also
        // guard the string in case a different yarn major changes the code.
        [$out, $exit] = $this->shell('yarn install --frozen-lockfile --non-interactive');

        self::assertTrue(
            $exit === 0 && ! str_contains($out, 'lockfile needs to be updated'),
            "yarn.lock is stale (or `yarn install --frozen-lockfile` failed) — this is what every CI JS job runs.\n"
            . "Fix: run `yarn install`, then commit the updated yarn.lock.\n\n--- yarn (exit {$exit}) ---\n" . $out,
        );
    }

    #[Test]
    public function composer_lock_is_in_sync_with_composer_json(): void
    {
        $this->requireToolOrSkip('composer');

        // Not --strict: that also errors on unbound-version-constraint warnings,
        // which have nothing to do with lock sync. `composer validate` with
        // --no-check-all reports lock drift in its output but still exits 0 on
        // it, so match the message; a non-zero exit here means validate itself
        // failed and is also worth failing on.
        [$out, $exit] = $this->shell('composer validate --no-check-all --no-check-publish --no-interaction');

        self::assertTrue(
            $exit === 0 && ! str_contains($out, 'lock file is not up to date'),
            "composer.lock is out of sync with composer.json (or `composer validate` failed) — CI's "
            . "`composer install` would resolve stale deps.\n"
            . "Fix: run `composer update --lock` (or `composer require` / `composer update <pkg>`), then commit.\n\n"
            . "--- composer (exit {$exit}) ---\n" . $out,
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

    /**
     * Skip locally when the tool genuinely isn't runnable, but in CI —
     * where this guard is the whole point — a missing tool or a disabled
     * exec() must FAIL loudly, not pass by silent skip.
     */
    private function requireToolOrSkip(string $bin): void
    {
        if ($this->canShellOut() && $this->onPath($bin)) {
            return;
        }

        $reason = "`{$bin}` is not runnable here (missing binary or exec() disabled).";

        if (getenv('CI')) {
            self::fail($reason . ' In CI this parity guard must run — fix the runner, do not skip.');
        }

        self::markTestSkipped($reason . ' Skipping the CI-parity lockfile check locally.');
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
