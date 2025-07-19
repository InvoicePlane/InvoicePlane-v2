<?php

namespace Modules\Core\Support\NumberGenerator;

use Illuminate\Support\Facades\Log;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Repositories\DocumentGroupRepository;

abstract class AbstractNumberGenerator
{
    protected string $type;

    protected ?int $companyId;

    protected ?string $groupName = null;

    protected ?int $groupId = null;

    public function __construct(?int $companyId = null)
    {
        $this->companyId = $companyId ?? auth()->user()?->company_id;
    }

    public function forGroup(string $groupName): self
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

    public function forGroupId(int $groupId): self
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

    public function generate(): ?string
    {
        if (config('app.extreme_logging')) {
            Log::debug('NumberGenerator: Starting number generation', [
                'type'       => $this->type,
                'company_id' => $this->companyId,
                'group_id'   => $this->groupId,
                'group_name' => $this->groupName,
                'trace'      => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
            ]);
        }

        $group = $this->getDocumentGroup();

        if ( ! $group) {
            if (config('app.extreme_logging')) {
                Log::error('NumberGenerator: Document group not found', [
                    'type'       => $this->type,
                    'company_id' => $this->companyId,
                    'group_id'   => $this->groupId,
                    'group_name' => $this->groupName,
                ]);
            }

            return null;
        }

        if (config('app.extreme_logging')) {
            Log::debug('NumberGenerator: Found document group', [
                'group'   => $group->toArray(),
                'last_id' => $group->last_id,
                'next_id' => $group->next_id,
            ]);
        }

        // Skip reset check for new groups that haven't been used yet
        if ($group->last_id === 0) {
            return null;
        }

        if (config('app.extreme_logging')) {
            Log::debug('NumberGenerator: Checking for counter reset', [
                'last_id'      => $group->last_id,
                'reset_number' => $group->reset_number,
                'last_used'    => [
                    'year'  => $group->last_year,
                    'month' => $group->last_month,
                    'week'  => $group->last_week,
                ],
                'current' => [
                    'year'  => now()->year,
                    'month' => now()->month,
                    'week'  => now()->weekOfYear,
                ],
            ]);
        }

        $this->checkAndResetCounter($group);

        $number = $this->formatNumber($group);

        if (config('app.extreme_logging')) {
            Log::debug('NumberGenerator: Formatted number', [
                'format'           => $group->format,
                'formatted_number' => $number,
            ]);
        }

        $this->incrementCounter($group);

        if (config('app.extreme_logging')) {
            Log::debug('NumberGenerator: Number generated successfully', [
                'number'      => $number,
                'new_next_id' => $group->next_id + 1,
            ]);
        }

        return $number;
    }

    protected function getDocumentGroup(): ?DocumentGroup
    {
        if (config('app.extreme_logging')) {
            Log::debug('NumberGenerator: Getting document group', [
                'type'       => $this->type,
                'company_id' => $this->companyId,
                'group_id'   => $this->groupId,
                'group_name' => $this->groupName,
                'trace'      => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
            ]);
        }

        $documentGroupRepository = app(DocumentGroupRepository::class);

        if ($this->groupId) {
            $group = DocumentGroup::query()
                ->where('company_id', $this->companyId)
                ->where('type', $this->type)
                ->find($this->groupId);

            if (config('app.extreme_logging')) {
                Log::debug('NumberGenerator: Found group by ID', [
                    'group_id' => $this->groupId,
                    'group'    => $group ? $group->toArray() : null,
                ]);
            }

            return $group;
        }

        // Find by company ID, type, and optional group name
        $group = $documentGroupRepository->findByCompanyAndType(
            $this->companyId,
            $this->type,
            $this->groupName
        );

        if (config('app.extreme_logging')) {
            Log::debug('NumberGenerator: Found group by company, type, and name', [
                'company_id' => $this->companyId,
                'type'       => $this->type,
                'group_name' => $this->groupName,
                'group'      => $group ? $group->toArray() : null,
            ]);
        }

        return $group;

        if (empty($this->groupName)) {
            $this->groupName = 'default';
            Log::warning('NumberGenerator: groupName was not set, using default', [
                'type'       => $this->type,
                'company_id' => $this->companyId,
                'trace'      => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
            ]);
        }

        $group = DocumentGroup::query()
            ->where('company_id', $this->companyId)
            ->where('type', $this->type)
            ->where('name', $this->groupName)
            ->first();

        if (config('app.extreme_logging')) {
            Log::debug('NumberGenerator: Found group by name', [
                'group_name' => $this->groupName,
                'group'      => $group ? $group->toArray() : null,
            ]);
        }
        dd($group);

        return $group;
    }

    protected function checkAndResetCounter(DocumentGroup $group): void
    {
        $now            = now();
        $resetPerformed = false;
        $resetReason    = null;

        // No reset needed if reset_number is 0 (never reset)
        if ($group->reset_number === 0) {
            return;
        }

        // Yearly reset (1) - Reset at the start of each year
        if ($group->reset_number === 1 && $group->last_year < $now->year) {
            $group->next_id = 1;
            $resetPerformed = true;
            $resetReason    = 'yearly_reset';
        }
        // Monthly reset (2) - Reset at the start of each month
        elseif (
            $group->reset_number === 2 &&
            ($group->last_year < $now->year ||
                ($group->last_year === $now->year && $group->last_month < $now->month))
        ) {
            $group->next_id = 1;
            $resetPerformed = true;
            $resetReason    = 'monthly_reset';
        }
        // Weekly reset (3) - Reset at the start of each week
        elseif (
            $group->reset_number === 3 &&
            ($group->last_year < $now->year ||
                ($group->last_year === $now->year &&
                    ($group->last_month < $now->month ||
                        ($group->last_month === $now->month && $group->last_week < $now->weekOfYear))))
        ) {
            $group->next_id = 1;
            $resetPerformed = true;
            $resetReason    = 'weekly_reset';
        }
        $group->save();
    }

    protected function formatNumber(DocumentGroup $group): string
    {
        $groupFormat = $this->group->format ?? '{{{YEAR}}}-{{{ID}}}';

        // First try the new format with triple curly braces
        $formatted = $this->parseIdentifierFormat($groupFormat, $group->next_id, $group->left_pad);

        // Fall back to the old format if no replacements were made
        if ($formatted === $group->format) {
            $replacements = [
                '{NUMBER}'         => mb_str_pad((string) $group->next_id, $group->left_pad, '0', STR_PAD_LEFT),
                '{YEAR}'           => date('Y'),
                '{MONTH}'          => date('m'),
                '{WEEK}'           => date('W'),
                '{MONTHSHORTNAME}' => date('M'),
            ];

            $formatted = strtr($group->format, $replacements);
        }

        return $formatted;
    }

    protected function incrementCounter(DocumentGroup $group): void
    {
        $now = now();

        $group->update([
            'last_id'    => $group->next_id,
            'next_id'    => $group->next_id + 1,
            'last_week'  => (int) $now->weekOfYear,
            'last_month' => (int) $now->month,
            'last_year'  => (int) $now->year,
        ]);
    }

    /**
     * Parse identifier format with triple curly braces syntax
     * Example: {{{year}}}-{{{id}}} becomes 2023-0001.
     */
    private function parseIdentifierFormat(string $identifier_format, int $next_id, int $left_pad): string
    {
        if (preg_match_all('/{{{([^{|}]*)}}}/', $identifier_format, $template_vars)) {
            foreach ($template_vars[1] as $var) {
                switch ($var) {
                    case 'year':
                        $replace = date('Y');
                        break;
                    case 'yy':
                        $replace = date('y');
                        break;
                    case 'month':
                        $replace = date('m');
                        break;
                    case 'day':
                        $replace = date('d');
                        break;
                    case 'id':
                        $replace = mb_str_pad($next_id, $left_pad, '0', STR_PAD_LEFT);
                        break;
                    default:
                        $replace = '';
                }

                $identifier_format = str_replace('{{{' . $var . '}}}', $replace, $identifier_format);
            }
        }

        return $identifier_format;
    }
}
