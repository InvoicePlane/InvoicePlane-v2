<?php

namespace Modules\Clients\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Clients\Models\Address;
use Modules\Clients\Models\Communication;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Note;
use Modules\Core\Services\BaseService;
use Modules\Expenses\Models\Expense;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Models\Payment;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use Modules\Quotes\Models\Quote;

class RelationMergeService extends BaseService
{
    /**
     * Scalar columns copied from the duplicate onto the primary
     * only when the primary has no value of its own. Conflicting
     * values are never overwritten — the primary always wins.
     *
     * @var list<string>
     */
    protected const GAP_FILL_COLUMNS = [
        'trading_name',
        'unique_name',
        'id_number',
        'coc_number',
        'vat_number',
        'currency_code',
        'language',
        'primary_contact_id',
    ];

    public function model(): string
    {
        return Relation::class;
    }

    /**
     * Merge the duplicate relation into the primary one: re-parent every
     * dependent record, fill empty scalar fields on the primary, then
     * soft-delete the duplicate.
     */
    public function merge(Relation $primary, Relation $duplicate): Relation
    {
        if ($primary->is($duplicate)) {
            throw new InvalidArgumentException(trans('ip.merge_clients_same_record'));
        }

        if ($primary->company_id !== $duplicate->company_id) {
            throw new InvalidArgumentException(trans('ip.merge_clients_different_company'));
        }

        return DB::transaction(function () use ($primary, $duplicate): Relation {
            $this->reparentDependents($primary, $duplicate);
            $this->fillMissingAttributes($primary, $duplicate);

            $duplicate->delete();

            return $primary->refresh();
        });
    }

    /**
     * Point every record owned by the duplicate at the primary.
     *
     * Global scopes (company scope, soft-delete scope) are removed on
     * purpose: the same-company guard has already run, and trashed
     * invoices/quotes must move along with the rest.
     */
    protected function reparentDependents(Relation $primary, Relation $duplicate): void
    {
        $foreignKeys = [
            [Invoice::class, 'customer_id'],
            [Quote::class, 'prospect_id'],
            [Payment::class, 'customer_id'],
            [Expense::class, 'vendor_id'],
            [Expense::class, 'customer_id'],
            [Task::class, 'customer_id'],
            [Project::class, 'customer_id'],
            [Contact::class, 'relation_id'],
        ];

        foreach ($foreignKeys as [$modelClass, $column]) {
            $modelClass::query()
                ->withoutGlobalScopes()
                ->where($column, $duplicate->id)
                ->update([$column => $primary->id]);
        }

        $morphs = [
            [Address::class, 'addressable'],
            [Communication::class, 'communicationable'],
            [Note::class, 'notable'],
        ];

        foreach ($morphs as [$modelClass, $morphName]) {
            $modelClass::query()
                ->withoutGlobalScopes()
                ->where("{$morphName}_type", $duplicate->getMorphClass())
                ->where("{$morphName}_id", $duplicate->id)
                ->update(["{$morphName}_id" => $primary->id]);
        }
    }

    /**
     * Copy the duplicate's scalar values onto the primary, but only into
     * empty slots. Conflicts keep the primary's value untouched.
     */
    protected function fillMissingAttributes(Relation $primary, Relation $duplicate): void
    {
        $changed = false;

        foreach (self::GAP_FILL_COLUMNS as $column) {
            if (filled($primary->{$column}) || blank($duplicate->{$column})) {
                continue;
            }

            $primary->{$column} = $duplicate->{$column};
            $changed            = true;
        }

        if ($changed) {
            $primary->save();
        }
    }
}
