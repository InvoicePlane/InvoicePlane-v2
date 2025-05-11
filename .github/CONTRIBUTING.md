---

### `CHECKLIST.md`

```markdown
# Module Implementation Checklist

This document tracks feature implementation and test coverage for each module in InvoicePlane V2.

Legend:
- ✅ = Done
- 🚧 = In progress
- ⬜ = Not started
- N/A = Not applicable

| Module   | Submodule        | Index ✅ | Statuses ✅ | Specials ✅ | Create ✅ | Update ✅ | Delete ✅ | Translations |
|----------|------------------|:--------:|:-----------:|:-----------:|:--------:|:--------:|:--------:|:------------:|
| clients  |                  |   ✅     |     ✅      |             |          |          |          |              |
|          | user_clients     |   N/A    |     N/A     |             |          |          |          |              |
| core     | custom_fields    |          |             |             |          |          |          |              |
|          | custom_values    |          |             |             |          |          |          |              |
|          | dashboard        |   ✅     |     N/A     |             |          |          |          |              |
|          | email_templates  |   ✅     |     N/A     |             |          |          |          |              |
|          | guest            | Missing  |     N/A     |             |          |          |          |              |
|          | import           |   ✅     |     N/A     |             |          |          |          |              |
| invoices |                  |   ✅     |     ✅      |             |          |          |          |              |
|          | invoice_groups   |   ✅     |     N/A     |             |          |          |          |              |
|          | tax_rates        |   ✅     |     N/A     |             |          |          |          |              |
| payments |                  |   ✅     |             |             |          |          |          |              |
|          | payment_methods  |   ✅     |     N/A     |             |          |          |          |              |
| products |                  |   ✅     |     N/A     |             |          |          |          |              |
|          | families         |   ✅     |     N/A     |             |          |          |          |              |
|          | units            |   ✅     |     N/A     |             |          |          |          |              |
| projects |                  |   ✅     |     N/A     |             |          |          |          |              |
|          | tasks            |   ✅     |             |             |          |          |          |              |
| quotes   |                  |   ✅     |     ✅      |             |          |          |          |              |
| reports  |                  |   N/A    |     N/A     |             |          |          |          |              |
| users    |                  |   ✅     |             |             |          |          |          |              |

---

### Special Notes

#### Invoices
- [ ] Add test for overdue scope: `DATEDIFF(NOW, invoice_date_due) > 0 AND status NOT IN (1, 4)`
- [ ] Create special `overdue()` scope
- [ ] Translate invoice group labels

#### Quotes
- [ ] Notes column skipped on index (not ported from CodeIgniter)

---

This file is regularly updated. Contributions should reference specific rows in this table.
