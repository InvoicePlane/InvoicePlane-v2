<?php

namespace Fable5\Audit;

use Illuminate\Support\Str;

class TestCaseAnalyzer
{
    public function analyze(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $fileName = basename($filePath);

        $isWeak = false;
        $isStrong = false;
        $reasons = [];
        $suspiciousPatterns = [];

        // Check for "meaningless" assertions
        if (preg_match('/\$this->assertEquals\(\$val, \$val\)/', $content) ||
            preg_match('/\$this->assertSame\(\$x, \$x\)/', $content)) {
            $isWeak = true;
            $reasons[] = "Asserting value equals itself";
        }

        // Check for DTO-only tests (very simple heuristic)
        if (preg_match('/it_sets_and_gets/', $content) || (preg_match_all('/(set|get)[A-Z]/', $content) > 5 && !str_contains($content, 'Service'))) {
             // This is a weak signal but often true for DTO tests
             // Let's refine: if it only calls getters and setters and asserts they match
             if (str_contains($content, '->set') && str_contains($content, '->get')) {
                 $suspiciousPatterns[] = "Likely DTO getter/setter test";
             }
        }

        // Check for heavy mocking
        $mockCount = substr_count($content, '->createMock(') + substr_count($content, 'Mockery::mock(');
        if ($mockCount > 3) {
            $suspiciousPatterns[] = "High mock count ($mockCount mocks)";
        }

        // Check for assertions that only validate logging
        if (str_contains($content, "->expects(\$this->") && str_contains($content, "->method('log')") && !str_contains($content, 'assertEquals')) {
             $isWeak = true;
             $reasons[] = "Only asserts logging";
        }

        // Strong test detection
        if (str_contains($content, 'Service') || str_contains($content, 'Runner') || str_contains($content, 'Planner') || str_contains($content, 'Cli') || str_contains($content, 'Client') || str_contains($content, 'Graph')) {
            if (str_contains($content, '->assert') || str_contains($content, '::assert')) {
                $isStrong = true;
            }
        }

        if (str_contains($content, 'Http::fake') && str_contains($content, 'Http::assertSent')) {
            $isStrong = true;
        }

        return [
            'file' => $fileName,
            'is_weak' => $isWeak,
            'is_strong' => $isStrong,
            'reasons' => $reasons,
            'suspicious_patterns' => $suspiciousPatterns,
        ];
    }
}
