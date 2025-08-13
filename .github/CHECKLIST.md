# InvoicePlane V2 – Feature Test Checklist

I've written some tests. Indexes first. After indexes, the statuses, etc. After that the special cases, exceptions, etc., then the Create, Update, Delete actions within the different *modules*.  
Some *modules* had a *sub page*. For example: Invoices have invoice_groups as a *sub page*.  
Maybe I'll do the *settings* per module as a separate row in this checklist.

## Notes (Invoices)
- Tests for overdue invoices are missing:
  - If status NOT IN (1,4) and DATEDIFF((NOW), invoice_date_due) > 0 then `is_overdue` is true
- Make special scope for overdue invoices
- Test for that scope

## Notes (Quotes)
- The notes in the index, I've not ported them over from CodeIgniter.
- For now, it's silly to put notes in an index. It's easily added though.

---

## Test Coverage

| Module    | Submodule        | Index (happy) | Specials (happy) | Create (happy) | Update (happy) | Delete (happy) | Translations |
|-----------|------------------|:-------------:|:----------------:|:--------------:|:--------------:|:--------------:|:------------:|
| clients   |                  | ✔️            |                  | ✔️             | ✔️             |                |              |
|           | user_clients     |               |                  | ✔️             | ✔️             |                |              |
| core      |                  |               |                  | ✔️             | ✔️             |                |              |
|           | custom_fields    |               |                  | ✔️             | ✔️             |                |              |
|           | custom_values    |               |                  | ✔️             | ✔️             |                |              |
|           | dashboard        | ✔️            |                  | ✔️             | ✔️             |                |              |
|           | email_templates  | ✔️            |                  | ✔️             | ✔️             |                |              |
|           | filter           |               |                  | ✔️             | ✔️             |                |              |
|           | guest            | (view missing)|                  | ✔️             | ✔️             |                |              |
|           | import           | ✔️            |                  | ✔️             | ✔️             |                |              |
|           | layout           |               |                  | ✔️             | ✔️             |                |              |
|           | mailer           |               |                  | ✔️             | ✔️             |                |              |
|           | sessions         |               |                  | ✔️             | ✔️             |                |              |
|           | settings         | ✔️            |                  | ✔️             | ✔️             |                |              |
|           | upload           |               |                  | ✔️             | ✔️             |                |              |
|           | welcome          | ✔️            |                  | ✔️             | ✔️             |                |              |
| invoices  |                  | ✔️            |                  | ✔️             | ✔️             |                |              |
|           | invoice_groups   | ✔️            |                  | ✔️             | ✔️             |                |              |
|           | tax_rates        | ✔️            |                  | ✔️             | ✔️             |                |              |
| payments  |                  | ✔️            |                  | ✔️             | ✔️             |                |              |
|           | payment_methods  | ✔️            |                  | ✔️             | ✔️             |                |              |
| products  |                  | ✔️            |                  | ✔️             | ✔️             |                |              |
|           | families         | ✔️            |                  | ✔️             | ✔️             |                |              |
|           | units            | ✔️            |                  | ✔️             | ✔️             |                |              |
| projects  |                  | ✔️            |                  | ✔️             | ✔️             |                |              |
|           | tasks            | ✔️            |                  | ✔️             | ✔️             |                |              |
| quotes    |                  | ✔️            |                  | ✔️             | ✔️             |                |              |
| reports   |                  |               |                  | ✔️             | ✔️             |                |              |
| users     |                  | ✔️            |                  | ✔️             | ✔️             |                |              |
| setup     |                  |               |                  | ✔️             | ✔️             |                |              |
