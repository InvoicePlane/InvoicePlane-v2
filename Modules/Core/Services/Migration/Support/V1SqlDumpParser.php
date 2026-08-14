<?php

namespace Modules\Core\Services\Migration\Support;

class V1SqlDumpParser
{
    /**
     * Parse SQL file or raw SQL string into structured table rows.
     *
     * @param string $sqlOrPath Path to .sql file or raw SQL content
     *
     * @return array<string, array<int, array<string, mixed>>> Keyed by table name
     */
    public function parse(string $sqlOrPath): array
    {
        $content = file_exists($sqlOrPath) ? file_get_contents($sqlOrPath) : $sqlOrPath;
        if ($content === false) {
            return [];
        }

        $tables       = [];
        $tableColumns = [];

        // 1. First pass: find CREATE TABLE statements to get column orders if INSERT doesn't specify columns
        $this->extractCreateTables($content, $tableColumns);

        // 2. Parse INSERT statements
        $this->extractInserts($content, $tableColumns, $tables);

        return $tables;
    }

    /**
     * Robust tokenizer for SQL value tuples like: (1, 'hello', NULL, 12.34), (2, 'world, with \'quote\'', '2026-01-01').
     *
     * @return array<int, array<int, mixed>>
     */
    public function parseValueTuples(string $valuesString): array
    {
        $tuples       = [];
        $length       = mb_strlen($valuesString);
        $inTuple      = false;
        $inString     = false;
        $quoteChar    = '';
        $currentValue = '';
        $currentTuple = [];
        $isEscaped    = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $valuesString[$i];

            if ($inString) {
                if ($isEscaped) {
                    $currentValue .= $this->unescapeChar($char);
                    $isEscaped = false;
                } elseif ($char === '\\') {
                    $isEscaped = true;
                } elseif ($char === $quoteChar) {
                    // Check for SQL double quote escape e.g. ''
                    if ($i + 1 < $length && $valuesString[$i + 1] === $quoteChar) {
                        $currentValue .= $quoteChar;
                        $i++; // skip next quote
                    } else {
                        $inString = false;
                    }
                } else {
                    $currentValue .= $char;
                }
                continue;
            }

            if ($char === '\'' || $char === '"') {
                $inString  = true;
                $quoteChar = $char;
                continue;
            }

            if ($char === '(' && ! $inTuple) {
                $inTuple      = true;
                $currentTuple = [];
                $currentValue = '';
                continue;
            }

            if ($char === ')' && $inTuple) {
                $currentTuple[] = $this->castSqlValue(mb_trim($currentValue));
                $tuples[]       = $currentTuple;
                $currentTuple   = [];
                $currentValue   = '';
                $inTuple        = false;
                continue;
            }

            if ($char === ',' && $inTuple) {
                $currentTuple[] = $this->castSqlValue(mb_trim($currentValue));
                $currentValue   = '';
                continue;
            }

            if ($inTuple) {
                $currentValue .= $char;
            }
        }

        return $tuples;
    }

    /**
     * Extract table schemas from CREATE TABLE definitions.
     *
     * @param array<string, array<int, string>> $tableColumns
     */
    protected function extractCreateTables(string $content, array &$tableColumns): void
    {
        $pattern = '/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?(?:[`\'"]?([a-zA-Z0-9_]+)[`\'"]?\.)?[`\'"]?([a-zA-Z0-9_]+)[`\'"]?\s*\((.*?)\)\s*(?:ENGINE|DEFAULT|AUTO_INCREMENT|CHARSET|COLLATE|;)/is';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $tableName = ! empty($match[2]) ? $match[2] : $match[1];
                $body      = $match[3];

                $cols  = [];
                $lines = explode("\n", $body);
                foreach ($lines as $line) {
                    $trimmed = mb_trim($line);
                    if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                        continue;
                    }

                    // Check if line defines a column (not KEY, PRIMARY KEY, CONSTRAINT, etc.)
                    if (preg_match('/^[`\'"]?([a-zA-Z0-9_]+)[`\'"]?\s+(?:int|varchar|char|text|datetime|date|decimal|tinyint|smallint|bigint|mediumtext|longtext|enum|float|double|timestamp)/i', $trimmed, $colMatch)) {
                        $colName = $colMatch[1];
                        if ( ! in_array(mb_strtoupper($colName), ['PRIMARY', 'KEY', 'UNIQUE', 'CONSTRAINT', 'FOREIGN', 'CHECK', 'INDEX'])) {
                            $cols[] = $colName;
                        }
                    }
                }

                if ( ! empty($cols)) {
                    $tableColumns[$tableName] = $cols;
                }
            }
        }
    }

    /**
     * Extract and parse INSERT INTO statements.
     *
     * @param array<string, array<int, string>>               $tableColumns
     * @param array<string, array<int, array<string, mixed>>> $tables
     */
    protected function extractInserts(string $content, array $tableColumns, array &$tables): void
    {
        // Match INSERT INTO `tableName` (`col1`, `col2`) VALUES (...) or INSERT INTO `tableName` VALUES (...)
        $insertPattern = '/INSERT\s+INTO\s+[`\'"]?([a-zA-Z0-9_]+)[`\'"]?\s*(?:\((.*?)\))?\s*VALUES\s*(.*?)(\s*;\s*(?:\r?\n|$))/is';

        if (preg_match_all($insertPattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $tableName  = $match[1];
                $columnsRaw = mb_trim($match[2] ?? '');
                $valuesRaw  = mb_trim($match[3] ?? '');

                $columns = [];
                if ($columnsRaw !== '') {
                    // Parse column list e.g. `col1`, `col2`
                    preg_match_all('/[`\'"]?([a-zA-Z0-9_]+)[`\'"]?/', $columnsRaw, $colMatches);
                    $columns = array_filter($colMatches[1] ?? [], fn ($c) => $c !== '');
                } elseif (isset($tableColumns[$tableName])) {
                    $columns = $tableColumns[$tableName];
                }

                if ( ! isset($tables[$tableName])) {
                    $tables[$tableName] = [];
                }

                // Parse value tuples (val1, val2), (val3, val4)
                $tuples = $this->parseValueTuples($valuesRaw);
                foreach ($tuples as $tuple) {
                    $row = [];
                    if ( ! empty($columns)) {
                        $colKeys = array_values($columns);
                        foreach ($tuple as $i => $val) {
                            $colName       = $colKeys[$i] ?? ('col_' . $i);
                            $row[$colName] = $val;
                        }
                    } else {
                        foreach ($tuple as $i => $val) {
                            $row['col_' . $i] = $val;
                        }
                    }
                    $tables[$tableName][] = $row;
                }
            }
        }
    }

    protected function unescapeChar(string $char): string
    {
        return match ($char) {
            'n'     => "\n",
            'r'     => "\r",
            't'     => "\t",
            '\\'    => '\\',
            '\''    => '\'',
            '"'     => '"',
            '0'     => "\0",
            default => $char,
        };
    }

    protected function castSqlValue(string $val): mixed
    {
        $upper = mb_strtoupper($val);
        if ($upper === 'NULL') {
            return null;
        }
        if ($upper === 'TRUE') {
            return true;
        }
        if ($upper === 'FALSE') {
            return false;
        }
        if (is_numeric($val)) {
            return str_contains($val, '.') ? (float) $val : (int) $val;
        }

        return $val;
    }
}
