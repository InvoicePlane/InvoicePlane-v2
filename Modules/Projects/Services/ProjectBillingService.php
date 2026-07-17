<?php

namespace Modules\Projects\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;

class ProjectBillingService
{
    /**
     * Create (or extend) a draft invoice for the project's client with one
     * invoice item per billable task.
     *
     * Only completed tasks that are not yet linked to an invoice item are
     * billed. When the client already has a draft invoice, the items are
     * appended to it instead of creating a second draft.
     */
    public function billTasks(Project $project, array $taskIds): Invoice
    {
        $tasks = $this->billableTasks($project, $taskIds);

        if ($tasks->isEmpty()) {
            throw new InvalidArgumentException(trans('ip.no_billable_tasks_selected'));
        }

        return DB::transaction(function () use ($project, $tasks) {
            $invoice = $this->findOrCreateDraftInvoice($project);

            foreach ($tasks as $task) {
                $price = (float) ($task->task_price ?? 0);

                $invoice->invoiceItems()->create([
                    'task_id'     => $task->id,
                    'item_name'   => $task->task_name,
                    'description' => $task->description,
                    'quantity'    => 1,
                    'price'       => $price,
                    'discount'    => 0,
                    'subtotal'    => $price,
                    'tax_rate_id' => $task->tax_rate_id,
                ]);
            }

            $this->refreshTotals($invoice);

            return $invoice->refresh();
        });
    }

    /**
     * The project's completed tasks from the given selection that are not
     * already on an invoice.
     */
    public function billableTasks(Project $project, array $taskIds)
    {
        $alreadyBilled = InvoiceItem::query()
            ->whereIn('task_id', $taskIds)
            ->pluck('task_id')
            ->all();

        return $project->tasks()
            ->whereIn('id', $taskIds)
            ->where('task_status', TaskStatus::COMPLETED->value)
            ->whereNotIn('id', $alreadyBilled)
            ->get();
    }

    /**
     * Completed, unbilled tasks of the project — the options offered in the
     * Bill Tasks selection modal.
     */
    public function billableTaskOptions(Project $project): array
    {
        $billedTaskIds = InvoiceItem::query()
            ->whereNotNull('task_id')
            ->pluck('task_id')
            ->all();

        return $project->tasks()
            ->where('task_status', TaskStatus::COMPLETED->value)
            ->whereNotIn('id', $billedTaskIds)
            ->pluck('task_name', 'id')
            ->toArray();
    }

    protected function findOrCreateDraftInvoice(Project $project): Invoice
    {
        $draft = Invoice::query()
            ->where('customer_id', $project->customer_id)
            ->where('invoice_status', InvoiceStatus::DRAFT->value)
            ->latest('id')
            ->first();

        if ($draft) {
            return $draft;
        }

        /*
         * Drafts may carry a null invoice number — numbering is assigned
         * when the invoice leaves draft.
         */
        return Invoice::query()->create([
            'company_id'               => $project->company_id,
            'customer_id'              => $project->customer_id,
            'user_id'                  => auth()->id(),
            'invoice_number'           => null,
            'invoice_status'           => InvoiceStatus::DRAFT->value,
            'invoice_sign'             => '1',
            'invoiced_at'              => now(),
            'invoice_due_at'           => now()->addDays(30),
            'invoice_discount_amount'  => 0,
            'invoice_discount_percent' => 0,
            'item_tax_total'           => 0,
            'invoice_item_subtotal'    => 0,
            'invoice_tax_total'        => 0,
            'invoice_total'            => 0,
        ]);
    }

    protected function refreshTotals(Invoice $invoice): void
    {
        $subtotal = (float) $invoice->invoiceItems()->sum('subtotal');

        $invoice->update([
            'invoice_item_subtotal' => $subtotal,
            'invoice_total'         => $subtotal + (float) ($invoice->invoice_tax_total ?? 0),
        ]);
    }
}
