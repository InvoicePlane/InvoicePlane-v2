<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Yaml\Yaml;

/**
 * mind-the-gap: CI workflow "required precondition" audit.
 *
 * Two real incidents this guards against, both on this branch at once:
 * 1. `phpunit.yml` ran `php artisan test` with no Node/yarn/build step at
 *    all, while its sibling `quickstart.yml` built frontend assets first.
 *    Feature tests that do a real HTTP request into a Blade view calling
 *    `@vite(...)` (e.g. `GuestQuoteViewTest`, via resources/css/guest.css)
 *    fail with "Unable to locate file in Vite manifest" as a result.
 * 2. `e2e-tests.yml` installed JS deps but never ran `yarn build` before
 *    starting `php artisan serve` and pointing Playwright at it. Every page
 *    Playwright navigates to is a real browser hitting a real running app —
 *    including Filament's own per-panel Vite themes
 *    (resources/css/filament/**, see CLAUDE.md) — so a missing manifest
 *    there doesn't fail narrowly, it 500s essentially every page in the
 *    whole E2E suite.
 *
 * Both were found reactively. This turns the fix into a permanent,
 * whole-codebase check: for every job that either runs PHP tests directly
 * or serves the app for something else (Playwright) to hit, assert an
 * earlier step in the same job builds frontend assets — unless the job is
 * listed in KNOWN_EXEMPT_JOBS with a reason a stranger could evaluate, same
 * discipline as FormDbConstraintAuditTest / FormDbGapKnownExceptions.
 */
class CiWorkflowAssetBuildAuditTest extends AbstractTestCase
{
    /**
     * Substrings of a step's `run:` content that mean "this job needs the
     * app's rendered output to be correct" — either PHPUnit rendering a
     * Blade view directly, or a real server Playwright (or anything else)
     * will point a browser at.
     */
    private const array APP_RENDERING_TRIGGERS = [
        'artisan test',
        'vendor/bin/phpunit',
        'artisan serve',
        'npm run e2e',
        'playwright test',
    ];

    /** "<workflow-file>:<job-key>" => reason a stranger could evaluate. */
    private const array KNOWN_EXEMPT_JOBS = [
        'smoke.yml:smoke'                                  => "Only runs #[Group('smoke')] tests — Filament resource CRUD driven through Livewire::test(), which mounts the component directly and never renders the outer @vite-using layout via a real HTTP request.",
        'composer-update.yml:update-composer-dependencies' => 'Its smoke-test step runs phpunit.smoke.xml, which filters to the same smoke group as smoke.yml above, for the same reason.',
    ];

    #[Test]
    public function every_app_rendering_job_builds_frontend_assets_first_or_is_a_known_exception(): void
    {
        $violations = [];

        foreach (glob(base_path('.github/workflows/*.yml')) as $file) {
            $workflow = Yaml::parseFile($file);
            $filename = basename($file);

            foreach ($workflow['jobs'] ?? [] as $jobKey => $job) {
                $needsAssets  = false;
                $buildsAssets = false;
                $triggerSeen  = false;

                foreach ($job['steps'] ?? [] as $step) {
                    $run = $step['run'] ?? '';

                    if ( ! $triggerSeen && $this->containsAny($run, self::APP_RENDERING_TRIGGERS)) {
                        $needsAssets = true;
                        $triggerSeen = true;
                    }

                    if ( ! $triggerSeen && (str_contains($run, 'yarn build') || str_contains($run, 'npm run build'))) {
                        $buildsAssets = true;
                    }
                }

                if ( ! $needsAssets || $buildsAssets) {
                    continue;
                }

                $key = "{$filename}:{$jobKey}";

                if ( ! array_key_exists($key, self::KNOWN_EXEMPT_JOBS)) {
                    $violations[] = $key;
                }
            }
        }

        self::assertSame(
            [],
            $violations,
            'These CI jobs render the app (PHPUnit or a real server for something like Playwright to hit) '
                . "without building frontend assets first — add a 'yarn build' step before the triggering step, "
                . 'or add a reasoned entry to KNOWN_EXEMPT_JOBS: ' . implode(', ', $violations),
        );
    }

    #[Test]
    public function every_browser_facing_job_publishes_filament_assets_when_composer_scripts_are_skipped(): void
    {
        // Filament ships its interactive JS (Alpine plugins for every modal,
        // combobox and repeater) as precompiled assets published by
        // `filament:assets` — NOT through Vite (yarn build here only emits the
        // per-panel CSS themes). That publish normally rides composer's
        // post-autoload-dump hook (`@php artisan filament:upgrade`). A job
        // that installs with `--no-scripts` skips it, so unless it also runs
        // `filament:assets`/`filament:upgrade` explicitly, public/js/filament/**
        // is absent: the app renders and navigates (list pages, nav smoke
        // pass) but nothing interactive works. Real incident: run 34348125213,
        // ~40 modal/combobox/repeater specs hung for exactly this reason.
        $violations = [];

        foreach (glob(base_path('.github/workflows/*.yml')) as $file) {
            $workflow = Yaml::parseFile($file);
            $filename = basename($file);

            foreach ($workflow['jobs'] ?? [] as $jobKey => $job) {
                $runsBrowser   = false;
                $skipsScripts  = false;
                $publishesFila = false;

                foreach ($job['steps'] ?? [] as $step) {
                    $run = $step['run'] ?? '';

                    if ($this->containsAny($run, ['npm run e2e', 'playwright test', 'artisan serve'])) {
                        $runsBrowser = true;
                    }

                    if (str_contains($run, 'composer install') && str_contains($run, '--no-scripts')) {
                        $skipsScripts = true;
                    }

                    if (str_contains($run, 'filament:assets') || str_contains($run, 'filament:upgrade')) {
                        $publishesFila = true;
                    }
                }

                if ($runsBrowser && $skipsScripts && ! $publishesFila) {
                    $violations[] = "{$filename}:{$jobKey}";
                }
            }
        }

        self::assertSame(
            [],
            $violations,
            'These CI jobs point a browser at the app and install composer deps with --no-scripts, but never '
                . 'run `php artisan filament:assets` — so public/js/filament/** is missing and every Filament '
                . 'modal, combobox and repeater is dead in the browser. Add a `filament:assets` step after env '
                . 'setup, or drop --no-scripts: ' . implode(', ', $violations),
        );
    }

    #[Test]
    public function every_artisan_serve_step_disables_the_reloader_so_worker_processes_take_effect(): void
    {
        // .env.example (copied to .env in CI) ships PHP_CLI_SERVER_WORKERS=4.
        // `php artisan serve` silently ignores it unless --no-reload is passed
        // ("Unable to respect the PHP_CLI_SERVER_WORKERS environment variable
        // without the --no-reload flag. Only creating a single server.") and
        // runs the PHP built-in server single-threaded. A single-threaded
        // server deadlocks every Filament modal in the E2E suite: "New X"
        // fires a Livewire mountAction round-trip while the list page still
        // has connections in flight, the second request queues behind the
        // first, and the modal never opens. Real incident: run 34348125213 —
        // ~40 modal/repeater/"Add Team Member" specs all timed out at 30s
        // while every dedicated /create page (one request) passed.
        $violations = [];

        foreach (glob(base_path('.github/workflows/*.yml')) as $file) {
            $workflow = Yaml::parseFile($file);
            $filename = basename($file);

            foreach ($workflow['jobs'] ?? [] as $jobKey => $job) {
                foreach ($job['steps'] ?? [] as $step) {
                    $run = $step['run'] ?? '';

                    if (str_contains($run, 'artisan serve') && ! str_contains($run, '--no-reload')) {
                        $violations[] = "{$filename}:{$jobKey}";
                    }
                }
            }
        }

        self::assertSame(
            [],
            $violations,
            'These CI jobs start `php artisan serve` without `--no-reload`, so PHP_CLI_SERVER_WORKERS '
                . '(shipped as 4 in .env.example) is ignored and the server runs single-threaded — which '
                . 'deadlocks concurrent Livewire round-trips and hangs every Filament modal in the E2E suite. '
                . 'Add --no-reload to the serve command: ' . implode(', ', $violations),
        );
    }

    #[Test]
    public function every_workflow_runs_the_same_db_driver_as_local_dev(): void
    {
        // Laravel 11+ has a dedicated `mariadb` driver that is NOT a synonym
        // for `mysql` — different grammar, JSON handling, no RETURNING, etc.
        // CLAUDE.md's canonical local test command runs `DB_CONNECTION=mariadb`
        // and .env.example ships `mariadb`, but the CI workflows historically
        // set `DB_CONNECTION: mysql` in their job env and .env.testing.example.
        // That split is exactly the "green on my machine, red in CI" trap: a
        // bug that only bites one driver hides on the other. Pin every
        // workflow to the same driver local dev uses.
        $expected   = 'mariadb';
        $violations = [];

        foreach (glob(base_path('.github/workflows/*.yml')) as $file) {
            $contents = file_get_contents($file);

            if (preg_match_all('/DB_CONNECTION:\s*(\S+)/', $contents, $matches)) {
                foreach ($matches[1] as $value) {
                    if (mb_trim($value, "'\"") !== $expected) {
                        $violations[] = basename($file) . " (DB_CONNECTION: {$value})";
                    }
                }
            }
        }

        $envTemplate = file_get_contents(base_path('.env.testing.example'));
        if ( ! preg_match('/^DB_CONNECTION=' . preg_quote($expected, '/') . '$/m', $envTemplate)) {
            $violations[] = '.env.testing.example (DB_CONNECTION is not ' . $expected . ')';
        }

        self::assertSame(
            [],
            $violations,
            "These CI configs run a different DB driver than local dev (CLAUDE.md uses `{$expected}`), so a "
                . "driver-specific bug would pass locally and fail in CI (or vice versa). Set them to `{$expected}`: "
                . implode(', ', $violations),
        );
    }

    #[Test]
    public function every_known_exempt_job_still_exists(): void
    {
        $stale = [];

        foreach (array_keys(self::KNOWN_EXEMPT_JOBS) as $key) {
            [$filename, $jobKey] = explode(':', $key, 2);
            $path                = base_path(".github/workflows/{$filename}");

            if ( ! is_file($path)) {
                $stale[] = $key;

                continue;
            }

            $workflow = Yaml::parseFile($path);

            if ( ! array_key_exists($jobKey, $workflow['jobs'] ?? [])) {
                $stale[] = $key;
            }
        }

        self::assertSame(
            [],
            $stale,
            'KNOWN_EXEMPT_JOBS references a workflow file or job that no longer exists — remove the stale entry: '
                . implode(', ', $stale),
        );
    }

    /** @param string[] $needles */
    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}
