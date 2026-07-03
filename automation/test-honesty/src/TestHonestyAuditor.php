<?php

namespace Fable5\Audit;

require_once __DIR__.'/TestCaseAnalyzer.php';
require_once __DIR__.'/ApplicationFlowRegistry.php';

class TestHonestyAuditor
{
    private array $testFiles = [];

    private array $sourceFiles = [];

    public function __construct(private string $testPath, private string $srcPath) {}

    public function run(): array
    {
        $this->scanDirectories($this->testPath, $this->testFiles);
        $this->scanDirectories($this->srcPath, $this->sourceFiles);

        $analyzer = new TestCaseAnalyzer;
        $results = [];

        foreach ($this->testFiles as $file) {
            $results[] = $analyzer->analyze($file);
        }

        $report = $this->generateReport($results);

        return $report;
    }

    private function scanDirectories(string $dir, &$fileList): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $dir.DIRECTORY_SEPARATOR.$file;
            if (is_dir($path)) {
                $this->scanDirectories($path, $fileList);
            } elseif (str_ends_with($file, '.php')) {
                $fileList[] = $path;
            }
        }
    }

    private function generateReport(array $analysisResults): array
    {
        $weakTests = [];
        $strongTests = [];
        $suspiciousTests = [];
        $missingCoverage = [];
        $moduleScores = [];

        foreach ($analysisResults as $res) {
            if ($res['is_weak']) {
                $weakTests[] = $res;
            } elseif ($res['is_strong']) {
                $strongTests[] = $res;
            }

            if (! empty($res['suspicious_patterns'])) {
                $suspiciousTests[] = $res;
            }
        }

        // Simple missing coverage detection: check if source classes have corresponding tests
        $criticalFlows = ApplicationFlowRegistry::getCriticalFlows();
        foreach ($criticalFlows as $flowName => $flow) {
            foreach ($flow['classes'] as $class) {
                $parts = explode('\\', $class);
                $className = end($parts);
                $covered = false;
                foreach ($analysisResults as $res) {
                    if (str_contains($res['file'], $className.'Test')) {
                        $covered = true;
                        break;
                    }
                }
                if (! $covered) {
                    $missingCoverage[] = "Missing test for $class in flow '$flowName'";
                }
            }
        }

        // Calculate scores
        $total = count($analysisResults);
        $weakCount = count($weakTests);
        $overallScore = $total > 0 ? (($total - $weakCount) / $total) * 100 : 0;

        return [
            'weak_tests' => $weakTests,
            'strong_tests' => $strongTests,
            'suspicious_tests' => $suspiciousTests,
            'missing_coverage' => $missingCoverage,
            'module_scores' => $moduleScores, // TBD: more granular scoring
            'overall_score' => round($overallScore, 2),
        ];
    }
}
