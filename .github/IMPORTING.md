# Importing Data

InvoicePlane supports importing data from external systems using CSV files. This guide outlines the requirements and steps for successful data import.

---

## 📂 Accessing the Import Tool

1. Navigate to **Settings**.
2. Click on **Import Data**.

---

## 📄 Import Requirements

To ensure a successful import:

- **File Format**: Files must be in **comma-delimited CSV** format.
- **File Names**: Use the exact file names as listed below.
- **Headers**: The first row must contain headers matching the specified column names.
- **Columns**: All required columns must be present, even if some fields are empty.
- **File Location**: Place CSV files in the `uploads/import` directory of your InvoicePlane installation.
- **User Email**: The `user_email` in `invoices.csv` must correspond to an existing user in InvoicePlane.

*Note: Failure to meet these requirements may result in import errors.*

---

## 📁 Supported Files and Structures

### 1. `clients.csv`

| Column Name         | Description                         |
|---------------------|-------------------------------------|
| `client_name`       | Client's full name                  |
| `client_address_1`  | Primary address line                |
| `client_address_2`  | Secondary address line              |
| `client_city`       | City                                |
| `client_state`      | State or province                   |
| `client_zip`        | ZIP or postal code                  |
| `client_country`    | Country                             |
| `client_phone`      | Phone number                        |
| `client_fax`        | Fax number                          |
| `client_mobile`     | Mobile number                       |
| `client_email`      | Email address                       |
| `client_web`        | Website URL                         |
| `client_vat_id`     | VAT identification number           |
| `client_tax_code`   | Tax code                            |
| `client_active`     | Status (`1` for active, `0` for inactive) |

### 2. `invoices.csv`

| Column Name             | Description                               |
|-------------------------|-------------------------------------------|
| `user_email`            | Email of the InvoicePlane user            |
| `client_name`           | Name of the client                        |
| `invoice_date_created`  | Creation date (`YYYY-MM-DD`)              |
| `invoice_date_due`      | Due date (`YYYY-MM-DD`)                   |
| `invoice_number`        | Unique invoice number                     |
| `invoice_terms`         | Payment terms                             |

### 3. `invoice_items.csv`

| Column Name        | Description                               |
|--------------------|-------------------------------------------|
| `invoice_number`   | Associated invoice number                 |
| `item_tax_rate`    | Tax rate (e.g., `7.8` for 7.8%)           |
| `item_date_added`  | Date added (`YYYY-MM-DD`)                 |
| `item_name`        | Name of the item                          |
| `item_description` | Description of the item                   |
| `item_quantity`    | Quantity of the item                      |
| `item_price`       | Price per item (numeric, no currency symbols) |

### 4. `payments.csv`

| Column Name      | Description                               |
|------------------|-------------------------------------------|
| `invoice_number` | Associated invoice number                 |
| `payment_method` | Method of payment (e.g., Cash, Credit)    |
| `payment_date`   | Date of payment (`YYYY-MM-DD`)            |
| `payment_amount` | Amount paid (numeric, no currency symbols)|
| `payment_note`   | Additional notes                          |

---

## ⚠️ Important Notes

- **Custom Fields**: Importing custom fields is not supported in the current version.
- **Data Validation**: Ensure all data is accurate and conforms to the required formats to prevent import errors.
- **Testing**: It's recommended to test imports with a small dataset before full-scale importing.

---

## 🛠️ Troubleshooting

- **Import Errors**: If the import process fails, double-check file formats, headers, and data consistency.
- **Community Support**: For assistance, visit the [InvoicePlane Community Forums](https://community.invoiceplane.com/).

---

*For more information and updates, refer to the [InvoicePlane Wiki](https://wiki.invoiceplane.com/en/1.6/system/importing-data).*
