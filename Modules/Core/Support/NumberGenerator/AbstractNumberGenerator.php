<?php

namespace Modules\Core\Support\NumberGenerator;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\Models\Numbering;
use RuntimeException;

/**
 * AbstractNumberGenerator.
 *
 * Base class for generating sequential numbers with customizable formatting.
 *
 * Features:
 * - Automatic number generation with configurable padding (e.g., PRJ-0001, PRJ-0023)
 * - Custom format templates supporting tags like {{year}}, {{month}}, {{prefix}}, {{number}}
 * - Thread-safe number generation using database row locking
 * - Consistent formatting through Numbering model's applyFormat method
 *
 * Usage:
 * ```php
 * $generator = new ProjectNumberGenerator();
 * $number = $generator->generate(); // Returns formatted number like "PRJ-0023"
 * ```
 *
 * The generator ensures padding is preserved across generations (e.g., PRJ-0023 → PRJ-0024)
 * and supports format patterns like "{{prefix}}/{{year}}/{{number}}" for PRJ/2025/0001.
 */
abstract class AbstractNumberGenerator
{
    protected string $type;

    protected ?int $companyId;

    protected ?string $groupName = null;

    protected ?int $groupId = null;

    public function __construct(?int $companyId = null)
    {
        $this->companyId = $companyId ?? session('current_company_id') ?? auth()->user()?->company_id;
    }

    public function forNumbering(string $groupName): self
    {
        $this->groupName = $groupName;

        return $this;
    }

    public function forNumberingId(int $groupId): self
    {
        $this->groupId = $groupId;

        return $this;
    }

    public function generate(?int $numberingId = null): ?string
    {
        if ($numberingId !== null) {
            $this->groupId   = $numberingId;
            $this->groupName = null;
        }

        return DB::transaction(function () {
            $numbering = $this->getNumbering(forUpdate: true);
            if ( ! $numbering) {
                Log::error('No numbering scheme found for type: ' . $this->type . ', company: ' . $this->companyId);

                return null;
            }

            $number = $this->formatNumber($numbering);

            $this->incrementCounter($numbering);

            return $number;
        });
    }

    protected function getNumbering(bool $forUpdate = false): ?Numbering
    {
        $query = Numbering::query()
            ->where('company_id', $this->companyId)
            ->where('type', $this->type);

        if ($this->groupId) {
            $query->where('numbering_id', $this->groupId);
        } elseif ($this->groupName) {
            $query->where('name', $this->groupName);
        } else {
            // Get the first numbering for this type if no specific group is set
            $query->orderBy('numbering_id');
        }

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    protected function formatNumber(Numbering $numbering): string
    {
        $prefix = $numbering->resolvedPrefix();
        $nextId = $numbering->next_id;

        // If format is provided, use the Numbering model's applyFormat method
        if ($numbering->format) {
            $formatted = $numbering->applyFormat($nextId, $prefix);

            // Replace date placeholders if present
            $formatted = $this->replaceDatePlaceholders($formatted);

            return $formatted;
        }

        // Default format: prefix + padded number
        $pad      = max((int) ($numbering->left_pad ?? 0), 0);
        $idPadded = mb_str_pad((string) $nextId, $pad, '0', STR_PAD_LEFT);

        return ($prefix ? $prefix . '-' : '') . $idPadded;
    }

    protected function replaceDatePlaceholders(string $formatted): string
    {
        $now = now();

        $replacements = [
            '{{year}}'  => $now->format('Y'),
            '{{yy}}'    => $now->format('y'),
            '{{month}}' => $now->format('m'),
            '{{day}}'   => $now->format('d'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $formatted);
    }

    protected function incrementCounter(Numbering $numbering): void
    {
        $numbering->update([
            'last_id' => $numbering->next_id,
            'next_id' => $numbering->next_id + 1,
        ]);
    }
}
