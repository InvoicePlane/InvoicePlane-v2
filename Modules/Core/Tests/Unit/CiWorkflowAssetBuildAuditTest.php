<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Yaml\Yaml;

/**
 * mind-the-gap: CI workflow "required precondition" audit.
 *
 * Real incident this guards against: `phpunit.yml` ran `php artisan test`
 * with no Node/yarn/build step at all, while its sibling `quickstart.yml`
 * built frontend assets first. Feature tests that do a real HTTP request
 * into a Blade view calling `@vite(...)` (e.g. `GuestQuoteViewTest`, via
 * `resources/css/guest.css`) fail with "Unable to locate file in Vite
 * manifest" on a clean checkout as a result — not a real test failure, a
 * missing precondition that only one of two near-identical workflows had.
 *
 * Asserts every workflow job that runs PHP tests also builds frontend
 * assets first, unless explicitly listed in KNOWN_EXEMPT_JOBS with a reason
 * — same "record it or it's a bug" discipline as
 * FormDbConstraintAuditTest / FormDbGapKnownExceptions.
 */
class CiWorkflowAssetBuildAuditTest extends AbstractTestCase
{
    /** "<workflow-file>:<job-key>" => reason a stranger could evaluate. */
    private const array KNOWN_EXEMPT_JOBS = [
        'smoke.yml:smoke'                                  => "Only runs #[Group('smoke')] tests — Filament resource CRUD driven through Livewire::test(), which mounts the component directly and never renders the outer @vite-using layout via a real HTTP request.",
        'composer-update.yml:update-composer-dependencies' => 'Its smoke-test step runs phpunit.smoke.xml, which filters to the same smoke group as smoke.yml above, for the same reason.',
    ];

    #[Test]
    public function every_php_test_job_builds_frontend_assets_first_or_is_a_known_exception(): void
    {
        $violations = [];

        foreach (glob(base_path('.github/workflows/*.yml')) as $file) {
            $workflow = Yaml::parseFile($file);
            $filename = basename($file);

            foreach ($workflow['jobs'] ?? [] as $jobKey => $job) {
                $runsPhpTests    = false;
                $buildsAssets    = false;
                $testStepReached = false;

                foreach ($job['steps'] ?? [] as $step) {
                    $run = $step['run'] ?? '';

                    if ( ! $testStepReached && (str_contains($run, 'artisan test') || str_contains($run, 'vendor/bin/phpunit'))) {
                        $runsPhpTests    = true;
                        $testStepReached = true;
                    }

                    if ( ! $testStepReached && (str_contains($run, 'yarn build') || str_contains($run, 'npm run build'))) {
                        $buildsAssets = true;
                    }
                }

                if ( ! $runsPhpTests || $buildsAssets) {
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
            'These CI jobs run PHP tests without building frontend assets first — add a '
                . "'yarn build' step before the test step, or add a reasoned entry to "
                . 'KNOWN_EXEMPT_JOBS: ' . implode(', ', $violations),
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
}
