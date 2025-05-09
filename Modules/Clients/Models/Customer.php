<?php

namespace Modules\Clients\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Core\Models\User;
use Modules\Expenses\Models\Expense;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\RecurringInvoice;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use Modules\Quotes\Models\Quote;

/**
 * @property int                           $id
 * @property int                           $primary_contact_id
 * @property string                        $relation_type
 * @property string                        $relation_status
 * @property string                        $relation_number
 * @property string                        $company_name
 * @property string                        $trading_name
 * @property string                        $id_number
 * @property string                        $coc_number
 * @property string                        $vat_number
 * @property Carbon                        $registered_at
 * @property mixed                         $created_at
 * @property mixed                         $updated_at
 * @property Invoice[]                     $invoices
 * @property Quote[]                       $quotes
 * @property Project[]                     $projects
 * @property Contact                       $contact
 * @property string|null                   $currency_code
 * @property string|null                   $language
 * @property Collection|Contact[]          $contacts
 * @property Collection|Expense[]          $expenses
 * @property Collection|RecurringInvoice[] $recurring_invoices
 * @property Collection|User[]             $users
 * @property Task[]                        $tasks
 */
class Customer extends Relation {}
