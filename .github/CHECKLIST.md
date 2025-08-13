# InvoicePlane V2 – Feature Test Checklist (Detailed)

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

## Test Coverage

| Module    | Submodule        | Index (happy) | Statuses (happy) | Specials (happy) | Create (happy) | Update (happy) | Delete (happy) | Translations |
|-----------|------------------|:-------------:|:----------------:|:----------------:|:--------------:|:--------------:|:--------------:|:------------:|
| clients   |                  |       X       |        X         |                  |       X        |       X        |                |              |
|           | user_clients     |       -       |        -         |                  |       X        |       X        |                |              |
| core      |                  |       -       |        -         |                  |       X        |       X        |                |              |
|           | custom_fields    |               |                  |                  |       X        |       X        |                |              |
|           | custom_values    |               |                  |                  |       X        |       X        |                |              |
|           | dashboard        |       X       |        -         |                  |       X        |       X        |                |              |
|           | email_templates  |       X       |        -         |                  |       X        |       X        |                |              |
|           | filter           |       -       |        -         |                  |       X        |       X        |                |              |
|           | guest            | (view missing)|        -         |                  |       X        |       X        |                |              |
|           | import           |       X       |        -         |                  |       X        |       X        |                |              |
|           | layout           |       -       |        -         |                  |       X        |       X        |                |              |
|           | mailer           |       -       |        -         |                  |       X        |       X        |                |              |
|           | sessions         |       -       |        -         |                  |       X        |       X        |                |              |
|           | settings         |       X       |        -         |                  |       X        |       X        |                |              |
|           | upload           |       -       |        -         |                  |       X        |       X        |                |              |
|           | welcome          |       X       |        -         |                  |       X        |       X        |                |              |
| invoices  |                  |       X       |        X         |                  |       X        |       X        |                |              |
|           | invoice_groups   |       X       |        -         |                  |       X        |       X        |                |              |
|           | tax_rates        |       X       |        -         |                  |       X        |       X        |                |              |
| payments  |                  |       X       |                  |                  |       X        |       X        |                |              |
|           | payment_methods  |       X       |        -         |                  |       X        |       X        |                |              |
| products  |                  |       X       |        -         |                  |       X        |       X        |                |              |
|           | families         |       X       |        -         |                  |       X        |       X        |                |              |
|           | units            |       X       |        -
