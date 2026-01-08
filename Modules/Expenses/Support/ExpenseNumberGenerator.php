<?php

namespace Modules\Expenses\Support;

use Illuminate\Support\Facades\Log;
use Modules\Core\Support\NumberGenerator\AbstractNumberGenerator;
use Modules\Expenses\Enums\ExpenseStatus;
use Modules\Expenses\Models\Expense;

class ExpenseNumberGenerator extends AbstractNumberGenerator
{
    protected string $type = 'expenses';

    protected ?string $groupName = 'Expenses';

    public function __construct(?int $companyId = null)
    {
        if ($companyId === null) {
            $user      = auth()->user();
            $companyId = $user?->getCurrentCompanyId();

            if (config('app.extreme_logging')) {
                Log::debug('ExpenseNumberGenerator: Resolved company context', [
                    'resolved_company_id' => $companyId,
                    'user_id'             => $user?->id,
                    'session_company_id'  => session('current_company_id'),
                    'trace'               => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
                ]);
            }
        }

        parent::__construct($companyId);

        if (config('app.extreme_logging')) {
            Log::debug('ExpenseNumberGenerator: Initialized', [
                'company_id'    => $this->companyId,
                'type'          => $this->type,
                'default_group' => $this->groupName,
                'trace'         => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
            ]);
        }
    }

    public function forExpense(): self
    {
        if (config('app.extreme_logging')) {
            Log::debug('ExpenseNumberGenerator: Setting to expense (non-draft) mode', [
                'previous_group' => $this->groupName,
                'new_group'      => 'default',
                'company_id'     => $this->companyId,
                'trace'          => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
            ]);
        }

        $this->groupName = 'Expenses';

        return $this;
    }

    public function getNextNumber(?Expense $expense = null): ?string
    {
        if (config('app.extreme_logging')) {
            Log::debug('ExpenseNumberGenerator: Getting next number', [
                'expense_id'     => $expense?->id,
                'current_number' => $expense?->expense_number,
                'status'         => $expense?->status?->value,
                'group'          => $this->groupName,
                'company_id'     => $this->companyId,
            ]);
        }

        if ($expense?->expense_number) {
            if (config('app.extreme_logging')) {
                Log::debug('ExpenseNumberGenerator: Using existing number', [
                    'expense_id' => $expense->id,
                    'number'     => $expense->expense_number,
                ]);
            }

            return $expense->expense_number;
        }

        if ($expense?->status === ExpenseStatus::DRAFT && ! $this->shouldGenerateForDraft()) {
            if (config('app.extreme_logging')) {
                Log::debug('ExpenseNumberGenerator: Skipping number generation for draft', [
                    'expense_id'      => $expense->id,
                    'status'          => $expense->status->value,
                    'should_generate' => $this->shouldGenerateForDraft(),
                ]);
            }

            return null;
        }

        $number = $this->generate();

        if (config('app.extreme_logging')) {
            Log::debug('ExpenseNumberGenerator: Generated new number', [
                'expense_id' => $expense?->id,
                'number'     => $number,
                'group'      => $this->groupName,
            ]);
        }

        return $number;
    }

    protected function shouldGenerateForDraft(): bool
    {
        // Configure this based on your business logic
        // For example, you might want to generate numbers for drafts only in certain cases
        return false;
    }
}
