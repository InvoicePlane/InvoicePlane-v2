<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Storage;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Models\Quote;
use Throwable;

/**
 * Builds the data arrays consumed by the brick index views
 * (Modules/Core/resources/views/report-builder/bricks/*). Keys follow the view contract:
 * company, client, invoice/quote, items, totals, terms, summary, footer — plus, for
 * invoice-only bricks, invoice_items, expense_items, aging_items/aging_totals; and for
 * quote-only bricks, quote_items.
 */
class ReportDataMapper
{
    /**
     * Statuses that still carry an outstanding balance and belong in an
     * aging report. Draft invoices were never sent, and paid invoices have
     * nothing left to age.
     */
    private const OPEN_INVOICE_STATUSES = [
        InvoiceStatus::SENT->value,
        InvoiceStatus::VIEWED->value,
        InvoiceStatus::PARTIALLY_PAID->value,
        InvoiceStatus::OVERDUE->value,
    ];

    /**
     * Bricks that read the customer's project/task tree. If none are in the
     * template, the whole project/task eager-load and data build is skipped.
     */
    private const PROJECT_BRICKS = [
        'detail_invoice_project',
        'detail_quote_project',
        'detail_tasks',
        'header_project',
    ];

    /**
     * @param list<string> $brickIds bricks in the resolved template; an empty
     *                               list means "unknown — build everything"
     */
    public function forInvoice(Invoice $invoice, array $brickIds = []): array
    {
        $wantsProject  = $this->wantsProject($brickIds);
        $wantsExpenses = $this->wantsExpenses($brickIds);

        $relations = [
            'company.addresses',
            'company.communications',
            'customer.addresses',
            'customer.communications',
            'invoiceItems.product.productCategory',
            'invoiceItems.taxRate',
            'payments',
        ];

        if ($wantsProject) {
            array_push($relations, 'customer.projects.tasks', 'customer.tasks', 'invoiceItems.task.project');
        }

        if ($wantsExpenses) {
            array_push($relations, 'expenses.expenseCategory', 'expenses.vendor');
        }

        $invoice->loadMissing($relations);

        $paid = (float) $invoice->payments->sum('payment_amount');

        $maxRows   = $this->maxRows();
        $truncated = $invoice->invoiceItems->count() > $maxRows
            || ($wantsExpenses && $invoice->expenses->count() > $maxRows);

        return [
            'company' => $this->companyData($invoice->company),
            'client'  => $this->clientData($invoice->customer),
            'invoice' => [
                'number'    => (string) $invoice->invoice_number,
                'date'      => $invoice->invoiced_at?->format('Y-m-d') ?? '',
                'due_date'  => $invoice->invoice_due_at?->format('Y-m-d') ?? '',
                'po_number' => '',
                'status'    => $invoice->invoice_status?->value ?? '',
            ],
            'items'         => $this->cap($invoice->invoiceItems->map(fn ($item): array => $this->itemData($item))->all()),
            'invoice_items' => $this->cap($invoice->invoiceItems->map(fn ($item): array => $this->productItemData($item))->all()),
            'expense_items' => $wantsExpenses
                ? $this->cap($invoice->expenses->map(fn ($expense): array => $this->expenseItemData($expense))->all())
                : [],
            'items_truncated' => $truncated,
            'project'         => $wantsProject ? $this->projectData($invoice->invoiceItems, $invoice->customer) : $this->emptyProject(),
            'tasks'           => $wantsProject ? $this->cap($this->tasksData($invoice->invoiceItems, $invoice->customer)) : [],
            'project_items'   => $wantsProject ? $this->cap($this->projectItemsData($invoice->invoiceItems, $invoice->customer)) : [],
            'totals'          => [
                'subtotal' => $this->money($invoice->invoice_item_subtotal),
                'tax'      => $this->money($invoice->invoice_tax_total),
                'total'    => $this->money($invoice->invoice_total),
                'paid'     => $this->money($paid),
                'balance'  => $this->money((float) $invoice->invoice_total - $paid),
            ],
            'summary' => (string) $invoice->summary,
            'terms'   => (string) $invoice->terms,
            'footer'  => (string) $invoice->footer,
            // The aging report runs its own query per invoice; skip it unless
            // the template actually has the aging brick.
            ...$this->agingData($this->wantsAging($brickIds) ? $invoice->customer : null),
        ];
    }

    /**
     * @param list<string> $brickIds bricks in the resolved template; an empty
     *                               list means "unknown — build everything"
     */
    public function forQuote(Quote $quote, array $brickIds = []): array
    {
        $wantsProject = $this->wantsProject($brickIds);

        $relations = [
            'company.addresses',
            'company.communications',
            'prospect.addresses',
            'prospect.communications',
            'quoteItems.product.productCategory',
            'quoteItems.taxRate',
        ];

        if ($wantsProject) {
            array_push($relations, 'prospect.projects.tasks', 'prospect.tasks', 'quoteItems.task.project');
        }

        $quote->loadMissing($relations);

        return [
            'company' => $this->companyData($quote->company),
            'client'  => $this->clientData($quote->prospect),
            'quote'   => [
                'quote_number'     => (string) $quote->quote_number,
                'quoted_at'        => $quote->quoted_at?->format('Y-m-d') ?? '',
                'quote_expires_at' => $quote->quote_expires_at?->format('Y-m-d') ?? '',
                'quote_status'     => $quote->quote_status?->value ?? '',
            ],
            'items'           => $this->cap($quote->quoteItems->map(fn ($item): array => $this->itemData($item))->all()),
            'quote_items'     => $this->cap($quote->quoteItems->map(fn ($item): array => $this->productItemData($item))->all()),
            'items_truncated' => $quote->quoteItems->count() > $this->maxRows(),
            'project'         => $wantsProject ? $this->projectData($quote->quoteItems, $quote->prospect) : $this->emptyProject(),
            'tasks'           => $wantsProject ? $this->cap($this->tasksData($quote->quoteItems, $quote->prospect)) : [],
            'project_items'   => $wantsProject ? $this->cap($this->projectItemsData($quote->quoteItems, $quote->prospect)) : [],
            'totals'          => [
                'subtotal' => $this->money($quote->quote_item_subtotal),
                'tax'      => $this->money($quote->quote_tax_total),
                'total'    => $this->money($quote->quote_total),
                'paid'     => $this->money(0),
                'balance'  => $this->money($quote->quote_total),
            ],
            'summary' => (string) $quote->summary,
            'terms'   => (string) $quote->terms,
            'footer'  => (string) $quote->footer,
        ];
    }

    /**
     * @param list<string> $brickIds
     */
    protected function wantsAging(array $brickIds): bool
    {
        return $brickIds === [] || in_array('detail_customer_aging', $brickIds, true);
    }

    /**
     * @param list<string> $brickIds
     */
    protected function wantsProject(array $brickIds): bool
    {
        return $brickIds === [] || array_intersect($brickIds, self::PROJECT_BRICKS) !== [];
    }

    /**
     * @param list<string> $brickIds
     */
    protected function wantsExpenses(array $brickIds): bool
    {
        return $brickIds === [] || in_array('detail_expense', $brickIds, true);
    }

    /**
     * @return array<string, string>
     */
    protected function emptyProject(): array
    {
        return [
            'project_number' => '',
            'project_name'   => '',
            'start_at'       => '',
            'end_at'         => '',
            'project_status' => '',
        ];
    }

    protected function companyData(?Company $company): array
    {
        if ($company === null) {
            return [];
        }

        $address = $company->addresses->first();

        return [
            'name'        => (string) $company->name,
            'vat_id'      => (string) $company->vat_number,
            'address'     => (string) ($address?->address_1 ?? ''),
            'city'        => (string) ($address?->city ?? ''),
            'postal_code' => (string) ($address?->postal_code ?? ''),
            'phone'       => $this->communication($company, 'phone'),
            'email'       => $this->communication($company, 'email'),
            'logo_path'   => $this->logoPath($company),
        ];
    }

    protected function clientData(?Relation $client): array
    {
        if ($client === null) {
            return [];
        }

        $address = $client->addresses->first();

        return [
            'name'        => (string) $client->company_name,
            'address'     => (string) ($address?->address_1 ?? ''),
            'city'        => (string) ($address?->city ?? ''),
            'postal_code' => (string) ($address?->postal_code ?? ''),
            'phone'       => $this->communication($client, 'phone'),
            'email'       => $this->communication($client, 'email'),
        ];
    }

    protected function itemData($item): array
    {
        return [
            'description' => (string) ($item->item_name ?: $item->description),
            'quantity'    => (float) $item->quantity,
            'price'       => $this->money($item->price),
            'tax'         => $this->money($item->tax_total),
            'total'       => $this->money($item->total),
            'category'    => (string) ($item->product?->productCategory?->category_name ?? ''),
            'tax_rate'    => (string) ($item->taxRate?->name ?? ''),
            'product'     => (string) ($item->product?->product_name ?? ($item->item_name ?: '')),
            'sku'         => (string) ($item->product?->code ?? ''),
        ];
    }

    /**
     * Row shape for the per-document-type product tables (detail-invoice-product,
     * detail-quote-product) — a superset of itemData() with the sku/unit_price/
     * discount columns those tables render.
     */
    protected function productItemData($item): array
    {
        return [
            'sku'         => (string) ($item->product?->code ?? ''),
            'description' => (string) ($item->item_name ?: $item->description),
            'quantity'    => (float) $item->quantity,
            'unit_price'  => $this->money($item->price),
            'tax'         => $this->money($item->tax_total),
            'discount'    => $this->money($item->discount ?? 0),
            'total'       => $this->money($item->total),
            'category'    => (string) ($item->product?->productCategory?->category_name ?? ''),
            'tax_rate'    => (string) ($item->taxRate?->name ?? ''),
            'product'     => (string) ($item->product?->product_name ?? ($item->item_name ?: '')),
        ];
    }

    protected function expenseItemData($expense): array
    {
        return [
            'expense_number' => (string) $expense->expense_number,
            'expense_date'   => $expense->expensed_at?->format('Y-m-d') ?? '',
            'category'       => (string) ($expense->expenseCategory?->category_name ?? ''),
            'vendor'         => (string) ($expense->vendor?->company_name ?? ''),
            'description'    => (string) $expense->description,
            'amount'         => $this->money($expense->expense_amount),
            'status'         => $expense->expense_status?->label() ?? '',
        ];
    }

    /**
     * Aging report for the client's still-open invoices, bucketed by how
     * many days past due each one is. One row per invoice; each row's
     * balance lands in exactly one bucket column, the rest '-'.
     *
     * @return array{aging_items: array, aging_totals: array}
     */
    protected function agingData(?Relation $client): array
    {
        $totals = ['current' => 0.0, 'days_30' => 0.0, 'days_60' => 0.0, 'days_90' => 0.0, 'over_90' => 0.0, 'total_due' => 0.0];

        if ($client === null) {
            return ['aging_items' => [], 'aging_totals' => $this->formatAgingTotals($totals)];
        }

        $now   = now();
        $items = [];

        $openInvoices = Invoice::query()
            ->where('customer_id', $client->id)
            ->whereIn('invoice_status', self::OPEN_INVOICE_STATUSES)
            ->with('payments')
            ->get();

        foreach ($openInvoices as $openInvoice) {
            $due = (float) $openInvoice->invoice_total - (float) $openInvoice->payments->sum('payment_amount');

            if ($due <= 0.0) {
                continue;
            }

            $dueDate     = $openInvoice->invoice_due_at;
            $daysOverdue = $dueDate ? (int) $dueDate->copy()->startOfDay()->diffInDays($now->copy()->startOfDay(), false) : 0;

            $bucket = match (true) {
                $daysOverdue <= 0  => 'current',
                $daysOverdue <= 30 => 'days_30',
                $daysOverdue <= 60 => 'days_60',
                $daysOverdue <= 90 => 'days_90',
                default            => 'over_90',
            };

            $row = [
                'invoice_number' => (string) $openInvoice->invoice_number,
                'invoice_date'   => $openInvoice->invoiced_at?->format('Y-m-d') ?? '',
                'due_date'       => $dueDate?->format('Y-m-d') ?? '',
                'current'        => '-',
                'days_30'        => '-',
                'days_60'        => '-',
                'days_90'        => '-',
                'over_90'        => '-',
                'total_due'      => $this->money($due),
                'days_overdue'   => max(0, $daysOverdue),
            ];
            $row[$bucket] = $this->money($due);

            $items[] = $row;

            $totals[$bucket] += $due;
            $totals['total_due'] += $due;
        }

        return ['aging_items' => $this->cap($items), 'aging_totals' => $this->formatAgingTotals($totals)];
    }

    protected function formatAgingTotals(array $totals): array
    {
        return array_map(fn (float $amount): string => $this->money($amount), $totals);
    }

    /**
     * Logo is embedded as a base64 data URI so both dompdf and Browsershot
     * can render it without requiring local file access in the browser.
     */
    protected function logoPath(Company $company): string
    {
        if (blank($company->logo)) {
            return '';
        }

        try {
            $disk = Storage::disk('public');

            if ( ! $disk->exists((string) $company->logo)) {
                return '';
            }

            $path = $disk->path((string) $company->logo);

            if ( ! is_file($path)) {
                return '';
            }

            $mimeType = mime_content_type($path) ?: 'image/png';
            $contents = file_get_contents($path);

            if ($contents === false) {
                return '';
            }

            return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
        } catch (Throwable) {
            return '';
        }
    }

    protected function communication($model, string $type): string
    {
        $matching = $model->communications
            ->filter(function ($entry) use ($type): bool {
                $commType = (string) $entry->communication_type;

                if ($type === 'phone') {
                    return str_contains($commType, 'phone') || str_contains($commType, 'mobile');
                }

                return str_contains($commType, $type);
            });

        $primary = $matching->firstWhere('is_primary', true) ?? $matching->first();

        return (string) ($primary?->communication_value ?? '');
    }

    protected function money(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    /**
     * Cap a rows array so one document cannot force an unbounded render.
     *
     * @param array<int, mixed> $rows
     *
     * @return array<int, mixed>
     */
    protected function cap(array $rows): array
    {
        return array_slice($rows, 0, $this->maxRows());
    }

    protected function maxRows(): int
    {
        return max(1, (int) config('ip.report.max_rows', 2000));
    }

    /**
     * Data array for header_project brick.
     *
     * @param \Illuminate\Support\Collection $items
     */
    protected function projectData($items, ?Relation $client): array
    {
        $project = $items->first(fn ($item): bool => $item->task?->project !== null)?->task?->project
            ?? collect($client?->projects)->first();

        if ($project === null) {
            return $this->emptyProject();
        }

        return [
            'project_number' => (string) ($project->project_number ?? ''),
            'project_name'   => (string) ($project->project_name ?? ''),
            'start_at'       => $project->start_at?->format('Y-m-d') ?? '',
            'end_at'         => $project->end_at?->format('Y-m-d') ?? '',
            'project_status' => (string) ($project->project_status?->label() ?? ($project->project_status?->value ?? '')),
        ];
    }

    /**
     * Data array for detail_tasks brick.
     *
     * @param \Illuminate\Support\Collection $items
     */
    protected function tasksData($items, ?Relation $client): array
    {
        $billedTasks = $items->map(fn ($item) => $item->task)->filter()->unique('id');

        if ($billedTasks->isNotEmpty()) {
            $tasks = $billedTasks;
        } elseif ($client !== null) {
            $clientTasks    = collect($client->tasks);
            $clientProjects = collect($client->projects);
            $tasks          = $clientTasks->isNotEmpty()
                ? $clientTasks
                : $clientProjects->flatMap(fn ($project) => collect($project->tasks))->unique('id');
        } else {
            $tasks = collect();
        }

        return $tasks->map(fn ($task): array => [
            'task_number' => (string) ($task->task_number ?? ''),
            'task_name'   => (string) ($task->task_name ?? ''),
            'description' => (string) ($task->description ?? ''),
            'due_at'      => $task->due_at?->format('Y-m-d') ?? '',
            'task_price'  => $this->money($task->task_price ?? 0),
            'task_status' => (string) ($task->task_status?->label() ?? ($task->task_status?->value ?? '')),
        ])->values()->all();
    }

    /**
     * Data array for detail_invoice_project and detail_quote_project bricks.
     *
     * @param \Illuminate\Support\Collection $items
     */
    protected function projectItemsData($items, ?Relation $client): array
    {
        $itemsWithTask = $items->filter(fn ($item): bool => $item->task !== null);

        if ($itemsWithTask->isNotEmpty()) {
            return $itemsWithTask->map(fn ($item): array => [
                'project_name' => (string) ($item->task?->project?->project_name ?? ''),
                'task_name'    => (string) ($item->task?->task_name ?? ($item->item_name ?: '')),
                'description'  => (string) ($item->description ?: ($item->task?->description ?? '')),
                'hours'        => (float) $item->quantity,
                'rate'         => $this->money($item->price),
                'total'        => $this->money($item->total),
            ])->values()->all();
        }

        $clientProjects = collect($client?->projects);

        if ($clientProjects->isNotEmpty()) {
            $rows = [];
            foreach ($clientProjects as $project) {
                foreach (collect($project->tasks) as $task) {
                    $price  = (float) ($task->task_price ?? 0);
                    $rows[] = [
                        'project_name' => (string) ($project->project_name ?? ''),
                        'task_name'    => (string) ($task->task_name ?? ''),
                        'description'  => (string) ($task->description ?? ''),
                        'hours'        => 1.0,
                        'rate'         => $this->money($price),
                        'total'        => $this->money($price),
                    ];
                }
            }

            return $rows;
        }

        return [];
    }
}
