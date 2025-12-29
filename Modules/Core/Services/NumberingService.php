<?php

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Modules\Clients\Models\Customer;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Models\Numbering;
use Modules\Expenses\Models\Expense;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Models\Payment;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use Modules\Quotes\Models\Quote;
use Throwable;

class NumberingService
{
    public function createNumbering(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            try {
                $payload = $this->prepareCreateData($data);
                $payload = $this->scrubNullValues($payload, ['last_id']);

                return Numbering::query()->create($payload);
            } catch (Throwable $e) {
                throw $e;
            }
        });
    }

    public function updateNumbering(Numbering $numbering, array $data): Numbering
    {
        if ($this->isNumberingApplied($numbering)) {
            throw new InvalidArgumentException('Cannot update numbering scheme that has already been applied.');
        }

        return DB::transaction(function () use ($numbering, $data) {
            try {
                $payload = $this->prepareUpdateData($numbering, $data);

                // Troubleshooting mode: if next_id is set lower than existing numbers,
                // automatically recalculate to find the highest number
                if (isset($payload['next_id']) && $payload['next_id'] < $numbering->next_id) {
                    Log::warning('Numbering troubleshooting mode triggered', [
                        'numbering_id'   => $numbering->id,
                        'numbering_name' => $numbering->name,
                        'old_next_id'    => $numbering->next_id,
                        'requested_next_id' => $payload['next_id'],
                    ]);

                    // Trigger resolveNextIdUpdate to find the highest existing number
                    $payload['next_id'] = $this->resolveNextIdUpdate($numbering, $payload['next_id']);
                }

                unset($payload['__desired_next_id'], $payload['__resolved_next_id']);

                $numbering->update($payload);
                $numbering->refresh();

                return $numbering;
            } catch (Throwable $e) {
                throw $e;
            }
        });
    }

    public function previewNextFormattedNumber(Numbering $numbering): string
    {
        $currentNextId = (int) ($numbering->next_id ?? 1);
        $safeNextId    = $this->resolveNextIdUpdate($numbering, $currentNextId);
        $prefix        = $numbering->resolvedPrefix();

        return $this->generateFormattedNumber($numbering, $safeNextId, $prefix);
    }

    public function deleteNumbering(Numbering $numbering): ?Numbering
    {
        return DB::transaction(function () use ($numbering) {
            try {
                $this->validateNumberingNotInUse($numbering);

                $numbering->delete();

                return $numbering;
            } catch (Throwable $e) {
                throw $e;
            }
        });
    }

    public function hasNumberingsInUse(): bool
    {
        return Customer::query()->whereNotNull('numbering_id')->exists()
            || Expense::query()->whereNotNull('numbering_id')->exists()
            || Invoice::query()->whereNotNull('numbering_id')->exists()
            || Payment::query()->whereNotNull('numbering_id')->exists()
            || Project::query()->whereNotNull(Project::NUMBERING_ID)->exists()
            || Quote::query()->whereNotNull('numbering_id')->exists()
            || Task::query()->whereNotNull('numbering_id')->exists();
    }

    /**
     * Check whether any numberings are in use for a specific numbering type.
     *
     * Accepts either a NumberingType enum or the underlying string value.
     */
    public function hasNumberingsInUseForType(NumberingType|string $type): bool
    {
        $modelClass = $this->getModelClassForType($type);
        $foreignKey = $this->getNumberingForeignKeyForType($type);

        if ($modelClass === null || $foreignKey === null) {
            return false;
        }

        /** @var class-string<Model> $modelClass */
        /** @var Model $modelInstance */
        $modelInstance = new $modelClass();

        return $modelInstance->newQuery()->whereNotNull($foreignKey)->exists();
    }

    public function isNumberingApplied(Numbering $numbering): bool
    {
        return $this->countAppliedRecords($numbering) > 0;
    }

    /**
     * Prepare sanitized payload for creating a numbering record.
     */
    protected function prepareCreateData(array $data): array
    {
        if ( ! isset($data['company_id'])) {
            $companyId = session('current_company_id')
                ?? Auth::user()?->companies()?->first()?->id;
            
            if ($companyId === null) {
                throw new InvalidArgumentException(
                    'Unable to determine company_id. Please provide a valid company context via session or authenticated user.'
                );
            }
            
            $data['company_id'] = $companyId;
        }

        if (isset($data['starting_id'])) {
            $data['next_id'] = (int) $data['starting_id'];
            unset($data['starting_id']);
        }

        if ( ! isset($data['last_id'])) {
            $data['last_id'] = null;
        }

        if (isset($data['next_id'])) {
            $data['next_id'] = (int) $data['next_id'];
        }

        if (array_key_exists('left_pad', $data) && $data['left_pad'] !== null) {
            $data['left_pad'] = (int) $data['left_pad'];
        }

        if (isset($data['format'])) {
            $data['format'] = Numbering::sanitizeFormat($data['format']);
        }

        if (array_key_exists('prefix', $data)) {
            $data['prefix'] = $this->sanitizePrefix($data['prefix']);
        }

        if (( ! isset($data['prefix']) || $data['prefix'] === null) && isset($data['type'])) {
            $type = $data['type'];
            if ($type instanceof NumberingType) {
                $data['prefix'] = $type->prefix();
            } elseif (is_string($type)) {
                $enum           = NumberingType::tryFrom($type);
                $data['prefix'] = $enum?->prefix();
            }
        }

        if (isset($data['format'], $data['prefix']) && $data['prefix'] !== null) {
            $data['format'] = Numbering::replacePrefixInFormat($data['format'], $data['prefix']);
        }

        return $data;
    }

    /**
     * Prepare sanitized payload for updating a numbering record.
     */
    protected function prepareUpdateData(Numbering $numbering, array $data): array
    {
        $payload = $data;

        if (array_key_exists('left_pad', $payload) && $payload['left_pad'] !== null) {
            $payload['left_pad'] = (int) $payload['left_pad'];
        }

        if (isset($payload['format'])) {
            $payload['format'] = Numbering::sanitizeFormat($payload['format']);
        }

        $existingPrefix = $numbering->resolvedPrefix();

        if (array_key_exists('prefix', $payload)) {
            $payload['prefix'] = $this->sanitizePrefix($payload['prefix']);

            if ($payload['prefix'] === null) {
                $payload['prefix'] = $existingPrefix;
            }

            $formatToUpdate = array_key_exists('format', $payload)
                ? $payload['format']
                : Numbering::sanitizeFormat($numbering->format);

            if ($formatToUpdate !== null && $formatToUpdate !== '') {
                $updatedFormat = Numbering::replacePrefixInFormat($formatToUpdate, $payload['prefix'], $existingPrefix);

                if ($updatedFormat !== $formatToUpdate || array_key_exists('format', $payload)) {
                    $payload['format'] = $updatedFormat;
                }
            }
        }

        $desiredNextId  = null;
        $resolvedNextId = null;

        if (array_key_exists('next_id', $payload) && $payload['next_id'] !== null) {
            $desiredNextId      = (int) $payload['next_id'];
            $resolvedNextId     = $this->resolveNextIdUpdate($numbering, $desiredNextId);
            $payload['next_id'] = $resolvedNextId;
        }

        $payload = $this->scrubNullValues($payload);

        if ($desiredNextId !== null) {
            $payload['__desired_next_id'] = $desiredNextId;
        }

        if ($resolvedNextId !== null) {
            $payload['__resolved_next_id'] = $resolvedNextId;
        }

        return $payload;
    }

    /**
     * Determine a safe next_id value that will not collide with existing numbers.
     */
    protected function resolveNextIdUpdate(Numbering $numbering, int $desiredNextId): int
    {
        $type       = $numbering->type;
        $modelClass = $this->getModelClassForType($type);

        if ( ! $modelClass) {
            return $desiredNextId;
        }

        $numberField = $this->getNumberFieldForType($type);

        if ( ! $numberField) {
            return $desiredNextId;
        }

        $query = $modelClass::whereNotNull($numberField);

        $foreignKey = $this->getNumberingForeignKeyForType($type);
        if ($foreignKey) {
            $query->where($foreignKey, $numbering->getKey());
        }
        $existingNumbers = $query
            ->pluck($numberField)
            ->filter(static fn ($value) => $value !== '')
            ->all();

        if (empty($existingNumbers)) {
            return $desiredNextId;
        }

        $existingLookup = array_flip($existingNumbers);
        $prefix         = $numbering->resolvedPrefix();
        $candidateId    = max(1, $desiredNextId);
        $maxAttempts    = 10000; // Safeguard against infinite loops
        $attempts       = 0;

        while ($attempts < $maxAttempts) {
            $testNumber = $this->generateFormattedNumber($numbering, $candidateId, $prefix);

            if ( ! isset($existingLookup[$testNumber])) {
                return $candidateId;
            }

            $candidateId++;
            $attempts++;
        }

        // If we reach here, we've exceeded max attempts
        Log::warning('Numbering collision resolution exceeded max attempts', [
            'numbering_id' => $numbering->id,
            'numbering_name' => $numbering->name,
            'prefix' => $prefix,
            'desired_next_id' => $desiredNextId,
            'attempts' => $attempts,
        ]);

        throw new RuntimeException(
            "Unable to resolve numbering collision after {$maxAttempts} attempts for numbering '{$numbering->name}' (ID: {$numbering->id}). " .
            'Please review existing numbers or adjust the numbering format.'
        );
    }

    /**
     * Build the formatted number for a given sequential id using the numbering settings.
     */
    protected function generateFormattedNumber(Numbering $numbering, int $sequentialId, string $prefix): string
    {
        $format = $numbering->format;

        if ($format) {
            return $numbering->applyFormat($sequentialId, $prefix);
        }

        $pad      = max((int) ($numbering->left_pad ?? 0), 0);
        $idPadded = mb_str_pad((string) $sequentialId, $pad, '0', STR_PAD_LEFT);

        return ($prefix ? $prefix . '-' : '') . $idPadded;
    }

    /**
     * Validate that a numbering scheme is not currently in use.
     *
     * @param Numbering $numbering
     *
     * @throws InvalidArgumentException
     */
    protected function validateNumberingNotInUse(Numbering $numbering): void
    {
        $usageCount = $this->countAppliedRecords($numbering);

        if ($usageCount > 0) {
            $label = $numbering->type instanceof NumberingType
                ? $numbering->type->label()
                : (string) ($numbering->type ?? 'record');

            throw new InvalidArgumentException(
                "Cannot delete numbering scheme '{$numbering->name}' because it is in use by {$usageCount} " . mb_strtolower($label) . '(s).'
            );
        }
    }

    protected function countAppliedRecords(Numbering $numbering): int
    {
        $modelClass = $this->getModelClassForType($numbering->type);
        $foreignKey = $this->getNumberingForeignKeyForType($numbering->type);

        if ($modelClass === null || $foreignKey === null) {
            return 0;
        }

        return (int) $modelClass::where($foreignKey, $numbering->getKey())
            ->count();
    }

    /**
     * Get the model class for a numbering type.
     *
     * @param mixed $type
     *
     * @return string|null
     */
    protected function getModelClassForType($type): ?string
    {
        return match ($type->value ?? $type) {
            'Customer' => Customer::class,
            'Expense'  => Expense::class,
            'Invoice'  => Invoice::class,
            'Payment'  => Payment::class,
            'Project'  => Project::class,
            'Quote'    => Quote::class,
            'Task'     => Task::class,
            default    => null,
        };
    }

    /**
     * Get the number field name for a numbering type.
     *
     * @param mixed $type
     *
     * @return string|null
     */
    protected function getNumberFieldForType($type): ?string
    {
        return match ($type->value ?? $type) {
            'Customer' => 'customer_number',
            'Expense'  => 'expense_number',
            'Invoice'  => 'invoice_number',
            'Payment'  => 'payment_number',
            'Project'  => 'project_number',
            'Quote'    => 'quote_number',
            'Task'     => 'task_number',
            default    => null,
        };
    }

    protected function getNumberingForeignKeyForType($type): ?string
    {
        return match ($type->value ?? $type) {
            'Invoice', 'Quote' => 'numbering_id',
            default => null,
        };
    }

    /**
     * Normalize prefix input by trimming whitespace and collapsing empty strings to null.
     */
    protected function sanitizePrefix(?string $prefix): ?string
    {
        if ($prefix === null) {
            return null;
        }

        $trimmed = trim($prefix);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * Remove null values from the payload while ensuring required context is preserved.
     */
    protected function scrubNullValues(array $data, array $preserveKeys = []): array
    {
        foreach ($data as $key => $value) {
            if ($value === null) {
                if ($key === 'user_id') {
                    $user       = Auth::user();
                    $resolvedId = $user?->user_id ?? $user?->getAuthIdentifier();

                    if ($resolvedId !== null) {
                        $data[$key] = $resolvedId;
                        continue;
                    }
                }

                if (in_array($key, $preserveKeys, true)) {
                    continue;
                }

                unset($data[$key]);
            }
        }

        return $data;
    }
}
