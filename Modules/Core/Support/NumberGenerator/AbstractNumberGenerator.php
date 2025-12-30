<?php

namespace Modules\Core\Support\NumberGenerator;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\Models\Numbering;

/**
 * AbstractNumberGenerator.
 *
 * Base class for generating sequential numbers with customizable formatting.
 * This is the foundation for all number generators in the system, including
 * invoices, quotes, expenses, projects, tasks, payments, and customers.
 *
 * Child classes must define:
 * - `$type` (required): The entity type identifier (e.g., 'Invoice', 'Quote', 'Project')
 * - `$groupName` (optional): Default numbering scheme name (e.g., 'Default Invoice Numbering')
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
        if (config('app.extreme_logging')) {
            Log::debug('NumberGenerator: Setting group name', [
                'previous_group' => $this->groupName,
                'new_group'      => $groupName,
                'type'           => $this->type,
                'company_id'     => $this->companyId,
                'trace'          => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
            ]);
        }

        $this->groupName = $groupName;

        return $this;
    }

    public function forNumberingId(int $groupId): self
    {
        if (config('app.extreme_logging')) {
            Log::debug('NumberGenerator: Setting group ID', [
                'previous_group_id' => $this->groupId,
                'new_group_id'      => $groupId,
                'type'              => $this->type,
                'company_id'        => $this->companyId,
                'trace'             => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
            ]);
        }

        $this->groupId = $groupId;

        return $this;
    }

    public function generate(?int $numberingId = null): ?string
    {
        if ($numberingId !== null) {
            $this->groupId   = $numberingId;
            $this->groupName = null;
        }

        if (config('app.extreme_logging')) {
            Log::debug('NumberGenerator: Starting number generation', [
                'type'       => $this->type,
                'company_id' => $this->companyId,
                'group_id'   => $this->groupId,
                'group_name' => $this->groupName,
                'trace'      => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
            ]);
        }

        return DB::transaction(function () {
            $numbering = $this->getNumbering(forUpdate: true);
            if ( ! $numbering) {
                if (config('app.extreme_logging')) {
                    Log::error('NumberGenerator: Numbering scheme not found', [
                        'type'       => $this->type,
                        'company_id' => $this->companyId,
                        'group_id'   => $this->groupId,
                        'group_name' => $this->groupName,
                    ]);
                }

                Log::error('No numbering scheme found for type: ' . $this->type . ', company: ' . $this->companyId);

                return;
            }

            if (config('app.extreme_logging')) {
                Log::debug('NumberGenerator: Found numbering scheme', [
                    'numbering_id' => $numbering->id,
                    'name'         => $numbering->name,
                    'last_id'      => $numbering->last_id,
                    'next_id'      => $numbering->next_id,
                    'format'       => $numbering->format,
                    'prefix'       => $numbering->prefix,
                ]);
            }

            // Apply reset logic if configured
            // Note: For new numbering schemes (last_id = 0), reset logic is applied
            // to ensure consistent behavior across all uses
            $this->checkAndResetCounter($numbering);

            $number = $this->formatNumber($numbering);

            if (config('app.extreme_logging')) {
                Log::debug('NumberGenerator: Generated number', [
                    'number'       => $number,
                    'numbering_id' => $numbering->id,
                    'next_id'      => $numbering->next_id,
                ]);
            }

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
            $query->where('id', $this->groupId);
        } elseif ($this->groupName) {
            $query->where('name', $this->groupName);
        } else {
            // Get the first numbering for this type if no specific group is set
            $query->orderBy('id');
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
        $now = now();

        $numbering->update([
            'last_id'    => $numbering->next_id,
            'next_id'    => $numbering->next_id + 1,
            'last_week'  => (int) $now->weekOfYear,
            'last_month' => (int) $now->month,
            'last_year'  => (int) $now->year,
        ]);
    }

    /**
     * Check and reset counter based on reset_number configuration.
     *
     * Reset rules:
     * - 0: Never reset
     * - 1: Reset yearly (at start of new year)
     * - 2: Reset monthly (at start of new month)
     * - 3: Reset weekly (at start of new week)
     */
    protected function checkAndResetCounter(Numbering $numbering): void
    {
        $now = now();

        if (config('app.extreme_logging')) {
            Log::debug('NumberGenerator: Checking for counter reset', [
                'last_id'       => $numbering->last_id,
                'next_id'       => $numbering->next_id,
                'reset_number'  => $numbering->reset_number,
                'last_year'     => $numbering->last_year,
                'last_month'    => $numbering->last_month,
                'last_week'     => $numbering->last_week,
                'current_year'  => $now->year,
                'current_month' => $now->month,
                'current_week'  => $now->weekOfYear,
            ]);
        }

        // No reset needed if reset_number is 0 (never reset)
        if ($numbering->reset_number === 0) {
            return;
        }

        // Yearly reset (1) - Reset at the start of each year
        if ($numbering->reset_number === 1 && $numbering->last_year < $now->year) {
            if (config('app.extreme_logging')) {
                Log::info('NumberGenerator: Applying yearly reset', [
                    'numbering_id'     => $numbering->id,
                    'name'             => $numbering->name,
                    'previous_next_id' => $numbering->next_id,
                    'new_next_id'      => 1,
                ]);
            }

            $numbering->next_id = 1;
            $numbering->save();

            return;
        }

        // Monthly reset (2) - Reset at the start of each month
        if (
            $numbering->reset_number === 2 &&
            ($numbering->last_year < $now->year ||
                ($numbering->last_year === $now->year && $numbering->last_month < $now->month))
        ) {
            if (config('app.extreme_logging')) {
                Log::info('NumberGenerator: Applying monthly reset', [
                    'numbering_id'     => $numbering->id,
                    'name'             => $numbering->name,
                    'previous_next_id' => $numbering->next_id,
                    'new_next_id'      => 1,
                ]);
            }

            $numbering->next_id = 1;
            $numbering->save();

            return;
        }

        // Weekly reset (3) - Reset at the start of each week
        if (
            $numbering->reset_number === 3 &&
            ($numbering->last_year < $now->year ||
                ($numbering->last_year === $now->year &&
                    ($numbering->last_month < $now->month ||
                        ($numbering->last_month === $now->month && $numbering->last_week < $now->weekOfYear))))
        ) {
            if (config('app.extreme_logging')) {
                Log::info('NumberGenerator: Applying weekly reset', [
                    'numbering_id'     => $numbering->id,
                    'name'             => $numbering->name,
                    'previous_next_id' => $numbering->next_id,
                    'new_next_id'      => 1,
                ]);
            }

            $numbering->next_id = 1;
            $numbering->save();
        }
    }
}
