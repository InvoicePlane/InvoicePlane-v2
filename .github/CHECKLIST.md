# InvoicePlane V2

I've written some tests.
Indexes first.
After indexes, the statuses, etc
After that the special cases, exceptions, etc
then the Create, Update, Delete actions within the different "_modules_"

Some "_modules_" had a "_sub page_".
For example : Invoices have invoice_groups as a "_sub page_"

Maybe I'll do the _settings_ per module as a separate row in this checklist

## Notes (Invoices):
- Tests for overdue invoices are missing:
If status NOT IN (1,4) and DATEDIFF((NOW), invoice_date_due) > 0 then `is_overdue` is true
- Make special scope for overdue invoices
- Test for that scope

## Notes (Quotes)
- The notes in de index, I've not ported them over from CodeIgniter.
- For now, it's silly to put notes in an index. It's easily added though

This is the checklist:

|          |                 | Index (happy)  | Statuses (happy) | Specials (happy) | Create (happy) | Update (happy) | Delete (happy) | Translations |
|----------|-----------------|:--------------:|:----------------:|:----------------:|:--------------:|:--------------:|:--------------:|:------------:|
| clients	 |                 |       X        |        X         |                  |                |                |                |              |
|          | user_clients	   |       -        |        -         |                  |                |                |                |              |
| core     |                 |       -        |        -         |                  |                |                |                |              |
|          | custom_fields   |                |                  |                  |                |                |                |              |
|          | custom_values   |                |                  |                  |                |                |                |              |
|          | dashboard       |       X        |        -         |                  |                |                |                |              |
|          | email_templates |       X        |        -         |                  |                |                |                |              |
|          | filter          |       -        |        -         |                  |                |                |                |              |
|          | guest           | (missing view) |        -         |                  |                |                |                |              |
|          | import          |       X        |        -         |                  |                |                |                |              |
|          | layout          |       -        |        -         |                  |                |                |                |              |
|          | mailer          |       -        |        -         |                  |                |                |                |              |
|          | sessions        |       -        |        -         |                  |                |                |                |              |
|          | settings        |       X        |        -         |                  |                |                |                |              |
|          | upload          |       -        |        -         |                  |                |                |                |              |
|          | welcome         |       X        |        -         |                  |                |                |                |              |
| invoices |                 |       X        |        X         |                  |                |                |                |              |
|          | invoice_groups	 |       X        |        -         |                  |                |                |                |              |
|          | tax_rates       |       X        |        -         |                  |                |                |                |              |
| payments |                 |       X        |                  |                  |                |                |                |              |
|          | payment_methods |       X        |        -         |                  |                |                |                |              |
| products |                 |       X        |        -         |                  |                |                |                |              |
|          | families        |       X        |        -         |                  |                |                |                |              |
|          | units           |       X        |        -         |                  |                |                |                |              |
| projects |                 |       X        |        -         |                  |                |                |                |              |
|          | tasks           |       X        |                  |                  |                |                |                |              |
| quotes   |                 |       X        |        X         |                  |                |                |                |              |
| reports	 |                 |       -        |        -         |                  |                |                |                |              |
| users	   |                 |       X        |                  |                  |                |                |                |              |
| setup    |                 |                |                  |                  |                |                |                |
