-- InvoicePlane v1 Test Fixture Database Dump

-- 1. Tax Rates (2 records)
CREATE TABLE IF NOT EXISTS `ip_tax_rates` (
  `tax_rate_id` int(11) NOT NULL AUTO_INCREMENT,
  `tax_rate_name` varchar(50) NOT NULL,
  `tax_rate_percent` decimal(5,2) NOT NULL,
  `tax_rate_code` varchar(20) DEFAULT NULL,
  `tax_rate_is_compound` int(1) DEFAULT 0,
  `tax_rate_calculate_vat` int(1) DEFAULT 0,
  PRIMARY KEY (`tax_rate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ip_tax_rates` (`tax_rate_id`, `tax_rate_name`, `tax_rate_percent`, `tax_rate_code`, `tax_rate_is_compound`, `tax_rate_calculate_vat`) VALUES
(1, 'Standard VAT', 20.00, 'VAT20', 0, 0),
(2, 'Reduced VAT', 5.00, 'VAT5', 0, 0);

-- 2. Families (Product Categories) (2 records)
CREATE TABLE IF NOT EXISTS `ip_families` (
  `family_id` int(11) NOT NULL AUTO_INCREMENT,
  `family_name` varchar(50) NOT NULL,
  PRIMARY KEY (`family_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ip_families` (`family_id`, `family_name`) VALUES
(1, 'Hardware'),
(2, 'Services');

-- 3. Units (Product Units) (2 records)
CREATE TABLE IF NOT EXISTS `ip_units` (
  `unit_id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_name` varchar(50) NOT NULL,
  `unit_name_plrl` varchar(50) NOT NULL,
  PRIMARY KEY (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ip_units` (`unit_id`, `unit_name`, `unit_name_plrl`) VALUES
(1, 'Piece', 'Pieces'),
(2, 'Hour', 'Hours');

-- 4. Products (6 records)
CREATE TABLE IF NOT EXISTS `ip_products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `family_id` int(11) DEFAULT NULL,
  `product_sku` varchar(50) DEFAULT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_description` text,
  `product_price` decimal(20,2) NOT NULL,
  `purchase_price` decimal(20,2) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `tax_rate_id` int(11) DEFAULT NULL,
  `product_tariff` int(11) DEFAULT NULL,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ip_products` (`product_id`, `family_id`, `product_sku`, `product_name`, `product_description`, `product_price`, `purchase_price`, `unit_id`, `tax_rate_id`, `product_tariff`) VALUES
(1, 1, 'HW-001', 'Wireless Mouse', 'Ergonomic optical mouse', 25.00, 12.00, 1, 1, NULL),
(2, 1, 'HW-002', 'Mechanical Keyboard', 'RGB mechanical gaming keyboard', 85.00, 45.00, 1, 1, NULL),
(3, 1, 'HW-003', 'USB-C Hub', 'Multiport adapter with 4K HDMI', 40.00, 18.00, 1, 1, NULL),
(4, 2, 'SRV-001', 'Consulting Hour', 'Senior architecture consulting', 150.00, 0.00, 2, 1, NULL),
(5, 2, 'SRV-002', 'Website Maintenance', 'Monthly security updates & backups', 200.00, 50.00, 1, 2, NULL),
(6, 2, 'SRV-003', 'Security Audit', 'Comprehensive vulnerability scan', 500.00, 100.00, 1, 1, NULL);

-- 5. Clients (3 records)
CREATE TABLE IF NOT EXISTS `ip_clients` (
  `client_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_date_created` datetime NOT NULL,
  `client_date_modified` datetime NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `client_surname` varchar(100) DEFAULT NULL,
  `client_type` int(1) DEFAULT 1,
  `client_address_1` varchar(100) DEFAULT NULL,
  `client_address_2` varchar(100) DEFAULT NULL,
  `client_city` varchar(50) DEFAULT NULL,
  `client_state` varchar(50) DEFAULT NULL,
  `client_zip` varchar(20) DEFAULT NULL,
  `client_country` varchar(50) DEFAULT NULL,
  `client_phone` varchar(50) DEFAULT NULL,
  `client_fax` varchar(50) DEFAULT NULL,
  `client_mobile` varchar(50) DEFAULT NULL,
  `client_email` varchar(100) DEFAULT NULL,
  `client_web` varchar(100) DEFAULT NULL,
  `client_vat_id` varchar(50) DEFAULT NULL,
  `client_tax_code` varchar(50) DEFAULT NULL,
  `client_active` int(1) DEFAULT 1,
  `client_language` varchar(20) DEFAULT 'system',
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ip_clients` (`client_id`, `client_date_created`, `client_date_modified`, `client_name`, `client_surname`, `client_type`, `client_address_1`, `client_address_2`, `client_city`, `client_state`, `client_zip`, `client_country`, `client_phone`, `client_fax`, `client_mobile`, `client_email`, `client_web`, `client_vat_id`, `client_tax_code`, `client_active`, `client_language`) VALUES
(1, '2026-01-10 10:00:00', '2026-01-10 10:00:00', 'Acme Corp', 'Smith', 1, '123 Market St', 'Suite 400', 'San Francisco', 'CA', '94105', 'US', '+1-555-0199', NULL, '+1-555-0198', 'billing@acme.test', 'https://acme.test', 'US123456789', 'TX-9901', 1, 'en'),
(2, '2026-01-15 11:30:00', '2026-01-15 11:30:00', 'Globex International', 'Johnson', 1, '456 King St', NULL, 'Toronto', 'ON', 'M5V 1L7', 'CA', '+1-416-555-0144', NULL, '+1-416-555-0145', 'accounts@globex.test', 'https://globex.test', 'CA987654321', 'TX-9902', 1, 'en'),
(3, '2026-02-01 09:00:00', '2026-02-01 09:00:00', 'Wayne Enterprises', 'Wayne', 1, '1007 Mountain Drive', NULL, 'Gotham', 'NJ', '07001', 'US', '+1-201-555-0100', NULL, '+1-201-555-0101', 'bruce@wayne.test', 'https://wayne.test', 'US998877665', 'TX-9903', 1, 'en');

-- 6. Payment Methods (3 records)
CREATE TABLE IF NOT EXISTS `ip_payment_methods` (
  `payment_method_id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_method_name` varchar(50) NOT NULL,
  PRIMARY KEY (`payment_method_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ip_payment_methods` (`payment_method_id`, `payment_method_name`) VALUES
(1, 'Bank Transfer'),
(2, 'Credit Card'),
(3, 'Cash');

-- 7. Invoices (5 records with mixed statuses)
-- INV-1001: Paid (Status 4)
-- INV-1002: Sent (Status 2)
-- INV-1003: Overdue (Status 2, due past date)
-- INV-1004: Draft (Status 1)
-- INV-1005: Partially Paid (Status 2, partial payment)
CREATE TABLE IF NOT EXISTS `ip_invoices` (
  `invoice_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `invoice_group_id` int(11) DEFAULT 1,
  `invoice_status_id` int(1) NOT NULL,
  `invoice_date_created` date NOT NULL,
  `invoice_date_due` date NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `invoice_discount_amount` decimal(20,2) DEFAULT 0.00,
  `invoice_discount_percent` decimal(20,2) DEFAULT 0.00,
  `invoice_terms` text,
  `invoice_url_key` varchar(32) DEFAULT NULL,
  `payment_method` int(11) DEFAULT 0,
  `creditinvoice_parent_id` int(11) DEFAULT NULL,
  `is_read_only` int(1) DEFAULT 0,
  `invoice_password` varchar(100) DEFAULT NULL,
  `invoice_time_created` time DEFAULT '00:00:00',
  PRIMARY KEY (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ip_invoices` (`invoice_id`, `user_id`, `client_id`, `invoice_group_id`, `invoice_status_id`, `invoice_date_created`, `invoice_date_due`, `invoice_number`, `invoice_discount_amount`, `invoice_discount_percent`, `invoice_terms`, `invoice_url_key`, `payment_method`, `creditinvoice_parent_id`, `is_read_only`, `invoice_password`, `invoice_time_created`) VALUES
(1, 1, 1, 1, 4, '2026-01-15', '2026-02-15', 'INV-1001', 0.00, 0.00, 'Payment due within 30 days.', 'urlkey1001', 1, NULL, 0, NULL, '10:00:00'),
(2, 1, 1, 1, 2, '2026-06-01', '2026-07-01', 'INV-1002', 0.00, 0.00, 'Net 30', 'urlkey1002', 1, NULL, 0, NULL, '11:00:00'),
(3, 1, 2, 1, 2, '2026-01-01', '2026-01-31', 'INV-1003', 0.00, 0.00, 'Strict 30 days', 'urlkey1003', 2, NULL, 0, NULL, '12:00:00'),
(4, 1, 2, 1, 1, '2026-06-10', '2026-07-10', 'INV-1004', 0.00, 0.00, 'Draft terms', 'urlkey1004', 0, NULL, 0, NULL, '13:00:00'),
(5, 1, 3, 1, 2, '2026-05-01', '2026-06-01', 'INV-1005', 0.00, 0.00, 'Partial pay test', 'urlkey1005', 1, NULL, 0, NULL, '14:00:00');

-- 8. Invoice Items (8 records total)
CREATE TABLE IF NOT EXISTS `ip_invoice_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `item_tax_rate_id` int(11) DEFAULT NULL,
  `item_date_added` date DEFAULT NULL,
  `item_name` varchar(100) NOT NULL,
  `item_description` text,
  `item_quantity` decimal(20,2) NOT NULL,
  `item_price` decimal(20,2) NOT NULL,
  `item_discount_amount` decimal(20,2) DEFAULT 0.00,
  `item_order` int(11) DEFAULT 1,
  `item_product_id` int(11) DEFAULT NULL,
  `item_product_unit_id` int(11) DEFAULT NULL,
  `item_subtotal` decimal(20,2) DEFAULT 0.00,
  `item_tax_total` decimal(20,2) DEFAULT 0.00,
  `item_total` decimal(20,2) DEFAULT 0.00,
  PRIMARY KEY (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ip_invoice_items` (`item_id`, `invoice_id`, `item_tax_rate_id`, `item_date_added`, `item_name`, `item_description`, `item_quantity`, `item_price`, `item_discount_amount`, `item_order`, `item_product_id`, `item_product_unit_id`, `item_subtotal`, `item_tax_total`, `item_total`) VALUES
(1, 1, 1, '2026-01-15', 'Wireless Mouse', 'Ergonomic mouse', 2.00, 25.00, 0.00, 1, 1, 1, 50.00, 10.00, 60.00),
(2, 1, 1, '2026-01-15', 'Mechanical Keyboard', 'RGB keyboard', 1.00, 85.00, 0.00, 2, 2, 1, 85.00, 17.00, 102.00),
(3, 2, 1, '2026-06-01', 'USB-C Hub', 'Multiport hub', 3.00, 40.00, 0.00, 1, 3, 1, 120.00, 24.00, 144.00),
(4, 3, 1, '2026-01-01', 'Consulting Hour', 'Architecture consulting', 4.00, 150.00, 0.00, 1, 4, 2, 600.00, 120.00, 720.00),
(5, 3, 2, '2026-01-01', 'Website Maintenance', 'Monthly maintenance', 1.00, 200.00, 0.00, 2, 5, 1, 200.00, 10.00, 210.00),
(6, 4, 1, '2026-06-10', 'Wireless Mouse', 'Office mouse', 5.00, 25.00, 0.00, 1, 1, 1, 125.00, 25.00, 150.00),
(7, 5, 1, '2026-05-01', 'Security Audit', 'Vulnerability assessment', 1.00, 500.00, 0.00, 1, 6, 1, 500.00, 100.00, 600.00),
(8, 5, 1, '2026-05-01', 'Consulting Hour', 'Follow-up consulting', 2.00, 150.00, 0.00, 2, 4, 2, 300.00, 60.00, 360.00);

-- 9. Invoice Amounts (Financial Invariant Source)
CREATE TABLE IF NOT EXISTS `ip_invoice_amounts` (
  `invoice_amount_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `invoice_item_subtotal` decimal(20,2) NOT NULL,
  `invoice_item_tax_total` decimal(20,2) NOT NULL,
  `invoice_tax_total` decimal(20,2) NOT NULL,
  `invoice_total` decimal(20,2) NOT NULL,
  `invoice_paid` decimal(20,2) NOT NULL,
  `invoice_balance` decimal(20,2) NOT NULL,
  `invoice_sign` enum('1','-1') DEFAULT '1',
  PRIMARY KEY (`invoice_amount_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ip_invoice_amounts` (`invoice_amount_id`, `invoice_id`, `invoice_item_subtotal`, `invoice_item_tax_total`, `invoice_tax_total`, `invoice_total`, `invoice_paid`, `invoice_balance`, `invoice_sign`) VALUES
(1, 1, 135.00, 27.00, 27.00, 162.00, 162.00, 0.00, '1'),
(2, 2, 120.00, 24.00, 24.00, 144.00, 0.00, 144.00, '1'),
(3, 3, 800.00, 130.00, 130.00, 930.00, 0.00, 930.00, '1'),
(4, 4, 125.00, 25.00, 25.00, 150.00, 0.00, 150.00, '1'),
(5, 5, 800.00, 160.00, 160.00, 960.00, 400.00, 560.00, '1');

-- 10. Payments (4 records)
CREATE TABLE IF NOT EXISTS `ip_payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `payment_method_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_amount` decimal(20,2) NOT NULL,
  `payment_note` text,
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ip_payments` (`payment_id`, `invoice_id`, `payment_method_id`, `payment_date`, `payment_amount`, `payment_note`) VALUES
(1, 1, 1, '2026-02-01', 100.00, 'First installment via Bank Wire'),
(2, 1, 1, '2026-02-10', 62.00, 'Final settlement'),
(3, 5, 2, '2026-05-15', 200.00, 'Deposit payment Credit Card'),
(4, 5, 3, '2026-05-20', 200.00, 'Cash installment');

-- 11. Quotes (2 records)
CREATE TABLE IF NOT EXISTS `ip_quotes` (
  `quote_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `invoice_group_id` int(11) DEFAULT 1,
  `quote_status_id` int(1) NOT NULL,
  `quote_date_created` date NOT NULL,
  `quote_date_expires` date NOT NULL,
  `quote_number` varchar(50) NOT NULL,
  `quote_discount_amount` decimal(20,2) DEFAULT 0.00,
  `quote_discount_percent` decimal(20,2) DEFAULT 0.00,
  `quote_url_key` varchar(32) DEFAULT NULL,
  `quote_password` varchar(100) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`quote_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ip_quotes` (`quote_id`, `invoice_id`, `user_id`, `client_id`, `invoice_group_id`, `quote_status_id`, `quote_date_created`, `quote_date_expires`, `quote_number`, `quote_discount_amount`, `quote_discount_percent`, `quote_url_key`, `quote_password`, `notes`) VALUES
(1, 0, 1, 1, 1, 2, '2026-02-01', '2026-03-01', 'QUO-2001', 0.00, 0.00, 'quotekey2001', NULL, 'Quote for hardware upgrade'),
(2, 0, 1, 3, 1, 4, '2026-02-10', '2026-03-10', 'QUO-2002', 0.00, 0.00, 'quotekey2002', NULL, 'Approved security audit quote');

-- 12. Quote Items
CREATE TABLE IF NOT EXISTS `ip_quote_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `quote_id` int(11) NOT NULL,
  `item_tax_rate_id` int(11) DEFAULT NULL,
  `item_date_added` date DEFAULT NULL,
  `item_name` varchar(100) NOT NULL,
  `item_description` text,
  `item_quantity` decimal(20,2) NOT NULL,
  `item_price` decimal(20,2) NOT NULL,
  `item_discount_amount` decimal(20,2) DEFAULT 0.00,
  `item_order` int(11) DEFAULT 1,
  `item_product_id` int(11) DEFAULT NULL,
  `item_product_unit_id` int(11) DEFAULT NULL,
  `item_subtotal` decimal(20,2) DEFAULT 0.00,
  `item_tax_total` decimal(20,2) DEFAULT 0.00,
  `item_total` decimal(20,2) DEFAULT 0.00,
  PRIMARY KEY (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ip_quote_items` (`item_id`, `quote_id`, `item_tax_rate_id`, `item_date_added`, `item_name`, `item_description`, `item_quantity`, `item_price`, `item_discount_amount`, `item_order`, `item_product_id`, `item_product_unit_id`, `item_subtotal`, `item_tax_total`, `item_total`) VALUES
(1, 1, 1, '2026-02-01', 'Wireless Mouse', 'Quote item 1', 10.00, 25.00, 0.00, 1, 1, 1, 250.00, 50.00, 300.00),
(2, 2, 1, '2026-02-10', 'Security Audit', 'Quote audit', 1.00, 500.00, 0.00, 1, 6, 1, 500.00, 100.00, 600.00);

-- 13. Quote Amounts
CREATE TABLE IF NOT EXISTS `ip_quote_amounts` (
  `quote_amount_id` int(11) NOT NULL AUTO_INCREMENT,
  `quote_id` int(11) NOT NULL,
  `quote_item_subtotal` decimal(20,2) NOT NULL,
  `quote_item_tax_total` decimal(20,2) NOT NULL,
  `quote_tax_total` decimal(20,2) NOT NULL,
  `quote_total` decimal(20,2) NOT NULL,
  PRIMARY KEY (`quote_amount_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ip_quote_amounts` (`quote_amount_id`, `quote_id`, `quote_item_subtotal`, `quote_item_tax_total`, `quote_tax_total`, `quote_total`) VALUES
(1, 1, 250.00, 50.00, 50.00, 300.00),
(2, 2, 500.00, 100.00, 100.00, 600.00);

-- 14. Projects (1 record) & Tasks (2 records)
CREATE TABLE IF NOT EXISTS `ip_projects` (
  `project_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `project_name` varchar(100) NOT NULL,
  PRIMARY KEY (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ip_projects` (`project_id`, `client_id`, `project_name`) VALUES
(1, 1, 'Infrastructure Overhaul');

CREATE TABLE IF NOT EXISTS `ip_tasks` (
  `task_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT NULL,
  `task_name` varchar(100) NOT NULL,
  `task_description` text,
  `task_price` decimal(20,2) DEFAULT 0.00,
  `task_finish_date` date DEFAULT NULL,
  `task_status` int(1) DEFAULT 1,
  `tax_rate_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ip_tasks` (`task_id`, `project_id`, `task_name`, `task_description`, `task_price`, `task_finish_date`, `task_status`, `tax_rate_id`) VALUES
(1, 1, 'Network Topology Setup', 'Install new switches and configure VLANs', 450.00, '2026-03-15', 3, 1),
(2, 1, 'Firewall Policy Migration', 'Migrate rules to next-gen firewall', 350.00, '2026-03-20', 2, 1);

-- 15. Custom Fields (1 record)
CREATE TABLE IF NOT EXISTS `ip_custom_fields` (
  `custom_field_id` int(11) NOT NULL AUTO_INCREMENT,
  `custom_field_table` varchar(50) NOT NULL,
  `custom_field_label` varchar(50) NOT NULL,
  `custom_field_type` varchar(50) DEFAULT 'TEXT',
  `custom_field_order` int(11) DEFAULT 1,
  PRIMARY KEY (`custom_field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ip_custom_fields` (`custom_field_id`, `custom_field_table`, `custom_field_label`, `custom_field_type`, `custom_field_order`) VALUES
(1, 'ip_client_custom', 'Account Manager', 'TEXT', 1);
