#!/usr/bin/env php
<?php

/**
 * PHPStan Results Parser
 *
 * This script parses PHPStan JSON output and generates a formatted, actionable report.
 * It groups errors by class, strips noise, and generates a markdown checklist suitable
 * for GitHub PR comments or Copilot context.
 *
 * Usage: php parse-phpstan-results.php phpstan.json
 */

if ($argc < 2) {
    echo "Usage: php parse-phpstan-results.php <phpstan.json>\n";
    exit(1);
}

$jsonFile = $argv[1];

if (!file_exists($jsonFile)) {
    echo "Error: File '$jsonFile' not found.\n";
    exit(1);
}

$content = file_get_contents($jsonFile);
$data = json_decode($content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Error: Invalid JSON in '$jsonFile': " . json_last_error_msg() . "\n";
    exit(1);
}

// Extract errors from PHPStan JSON format
$files = $data['files'] ?? [];
$totalErrors = $data['totals']['file_errors'] ?? 0;

if ($totalErrors === 0) {
    echo "## ✅ PHPStan Analysis - No Errors Found\n\n";
    echo "All files passed static analysis!\n";
    exit(0);
}

// Group errors by class/file
$errorsByFile = [];
$errorsByCategory = [
    'type_errors' => [],
    'method_errors' => [],
    'property_errors' => [],
    'return_type_errors' => [],
    'other_errors' => [],
];

foreach ($files as $filePath => $fileData) {
    $messages = $fileData['messages'] ?? [];
    
    foreach ($messages as $message) {
        $errorText = $message['message'] ?? '';
        $line = $message['line'] ?? 0;
        
        // Categorize errors
        $category = categorizeError($errorText);
        
        $errorsByFile[$filePath][] = [
            'line' => $line,
            'message' => $errorText,
            'category' => $category,
        ];
        
        $errorsByCategory[$category][] = [
            'file' => $filePath,
            'line' => $line,
            'message' => $errorText,
        ];
    }
}

// Generate markdown report
echo "## 🔍 PHPStan Analysis Report\n\n";
echo "**Total Errors:** $totalErrors\n\n";

// Summary by category
echo "### 📊 Error Summary by Category\n\n";
foreach ($errorsByCategory as $category => $errors) {
    $count = count($errors);
    if ($count > 0) {
        $emoji = getCategoryEmoji($category);
        $label = getCategoryLabel($category);
        echo "- $emoji **$label**: $count error(s)\n";
    }
}
echo "\n---\n\n";

// Detailed errors grouped by file
echo "### 📝 Detailed Errors by File\n\n";

$fileCount = 0;
foreach ($errorsByFile as $filePath => $errors) {
    $fileCount++;
    $shortPath = getShortPath($filePath);
    $errorCount = count($errors);
    
    echo "#### $fileCount. `$shortPath` ($errorCount error(s))\n\n";
    
    foreach ($errors as $error) {
        $line = $error['line'];
        $message = trimMessage($error['message']);
        $category = getCategoryLabel($error['category']);
        
        echo "- **Line $line** [$category]: $message\n";
    }
    
    echo "\n";
}

echo "---\n\n";

// Generate actionable checklist
echo "### ✅ Actionable Checklist\n\n";
echo "Use this checklist to track fixes:\n\n";

$checklistNumber = 0;
foreach ($errorsByFile as $filePath => $errors) {
    $shortPath = getShortPath($filePath);
    
    foreach ($errors as $error) {
        $checklistNumber++;
        $line = $error['line'];
        $message = trimMessage($error['message'], 80);
        
        echo "- [ ] Fix error in `$shortPath:$line` - $message\n";
    }
}

echo "\n---\n\n";
echo "**Generated:** " . date('Y-m-d H:i:s') . " UTC\n";

/**
 * Categorize error based on message content
 */
function categorizeError(string $message): string
{
    if (stripos($message, 'return') !== false && stripos($message, 'should return') !== false) {
        return 'return_type_errors';
    }
    
    if (stripos($message, 'method') !== false || stripos($message, 'call to') !== false) {
        return 'method_errors';
    }
    
    if (stripos($message, 'property') !== false) {
        return 'property_errors';
    }
    
    if (stripos($message, 'type') !== false || stripos($message, 'expects') !== false) {
        return 'type_errors';
    }
    
    return 'other_errors';
}

/**
 * Get emoji for error category
 */
function getCategoryEmoji(string $category): string
{
    $emojis = [
        'type_errors' => '🔢',
        'method_errors' => '🔧',
        'property_errors' => '📦',
        'return_type_errors' => '↩️',
        'other_errors' => '⚠️',
    ];
    
    return $emojis[$category] ?? '❓';
}

/**
 * Get human-readable label for category
 */
function getCategoryLabel(string $category): string
{
    $labels = [
        'type_errors' => 'Type Errors',
        'method_errors' => 'Method Errors',
        'property_errors' => 'Property Errors',
        'return_type_errors' => 'Return Type Errors',
        'other_errors' => 'Other Errors',
    ];
    
    return $labels[$category] ?? 'Unknown';
}

/**
 * Shorten file path for readability
 */
function getShortPath(string $path): string
{
    // Remove common prefixes
    $path = str_replace('/home/runner/work/InvoicePlane-v2/InvoicePlane-v2/', '', $path);
    $path = str_replace(getcwd() . '/', '', $path);
    
    return $path;
}

/**
 * Trim message to reasonable length
 */
function trimMessage(string $message, int $maxLength = 150): string
{
    // Remove excessive whitespace
    $message = preg_replace('/\s+/', ' ', $message);
    $message = trim($message);
    
    // Truncate if too long
    if (strlen($message) > $maxLength) {
        $message = substr($message, 0, $maxLength - 3) . '...';
    }
    
    return $message;
}
