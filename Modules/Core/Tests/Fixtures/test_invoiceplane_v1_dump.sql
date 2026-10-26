-- InvoicePlane v1 Test Database Dump

-- Tax Rates
CREATE TABLE IF NOT EXISTS `ip_tax_rates` (
  `tax_rate_id` int(11) NOT NULL AUTO_INCREMENT,
  `tax_rate_name` varchar(50) NOT NULL,
  `tax_rate_percent` decimal(8,3) NOT NULL,
  PRIMARY KEY (`tax_rate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ip_tax_rates` (`tax_rate_id`, `tax_rate_name`, `tax_rate_percent`) VALUES
(1, 'VAT 21%', 21.000),
(2, 'VAT 9%', 9.000);

-- Product Families
CREATE TABLE IF NOT EXISTS `ip_families` (
  `family_id` int(11) NOT NULL AUTO_INCREMENT,
  `family_name` varchar(50) NOT NULL,
  PRIMARY KEY (`family_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ip_families` (`family_id`, `family_name`) VALUES
(1, 'Services'),
(2, 'Products');

-- Product Units
CREATE TABLE IF NOT EXISTS `ip_units` (
  `unit_id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_name` varchar(50) NOT NULL,
  `unit_name_plrl` varchar(50) NOT NULL,
  PRIMARY KEY (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ip_units` (`unit_id`, `unit_name`, `unit_name_plrl`) VALUES
(1, 'Hour', 'Hours'),
(2, 'Piece', 'Pieces');

-- Products
CREATE TABLE IF NOT EXISTS `ip_products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `family_id` int(11) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `tax_rate_id` int(11) DEFAULT NULL,
  `product_sku` varchar(50) DEFAULT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_description` text,
  `product_price` decimal(20,4) DEFAULT 0.0000,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ip_products` (`product_id`, `family_id`, `unit_id`, `tax_rate_id`, `product_sku`, `product_name`, `product_description`, `product_price`) VALUES
(1, 1, 1, 1, 'SRV001', 'Consulting', 'Hourly consulting service', 100.0000),
(2, 2, 2, 2, 'PRD001', 'Widget', 'Standard widget product', 50.0000);

-- Clients
CREATE TABLE IF NOT EXISTS `ip_clients` (
  `client_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_name` varchar(100) NOT NULL,
  `client_vat_id` varchar(50) DEFAULT NULL,
  `client_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ip_clients` (`client_id`, `client_name`, `client_vat_id`, `client_active`) VALUES
(1, 'Test Client 1', 'VAT123456', 1),
(2, 'Test Client 2', 'VAT789012', 1);

-- Invoice Groups
CREATE TABLE IF NOT EXISTS `ip_invoice_groups` (
  `invoice_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_group_name` varchar(50) NOT NULL,
  `invoice_group_prefix` varchar(20) DEFAULT NULL,
  `invoice_group_next_id` int(11) DEFAULT 1,
  PRIMARY KEY (`invoice_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ip_invoice_groups` (`invoice_group_id`, `invoice_group_name`, `invoice_group_prefix`, `invoice_group_next_id`) VALUES
(1, 'Default', 'INV', 1001);

-- Invoices
CREATE TABLE IF NOT EXISTS `ip_invoices` (
  `invoice_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `invoice_group_id` int(11) DEFAULT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `invoice_status_id` int(11) DEFAULT 1,
  `invoice_date_created` date DEFAULT NULL,
  `invoice_date_due` date DEFAULT NULL,
  `invoice_discount_percent` decimal(8,2) DEFAULT 0.00,
  `invoice_discount_amount` decimal(20,4) DEFAULT 0.0000,
  `invoice_item_tax_total` decimal(20,4) DEFAULT 0.0000,
  `invoice_item_subtotal` decimal(20,4) DEFAULT 0.0000,
  `invoice_tax_total` decimal(20,4) DEFAULT 0.0000,
  `invoice_total` decimal(20,4) DEFAULT 0.0000,
  `invoice_url_key` varchar(50) DEFAULT NULL,
  `invoice_terms` text,
  PRIMARY KEY (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ip_invoices` (`invoice_id`, `client_id`, `invoice_group_id`, `invoice_number`, `invoice_status_id`, `invoice_date_created`, `invoice_date_due`, `invoice_item_subtotal`, `invoice_tax_total`, `invoice_total`) VALUES
(1, 1, 1, 'INV-001', 2, '2024-01-01', '2024-01-31', 100.0000, 21.0000, 121.0000),
(2, 2, 1, 'INV-002', 4, '2024-01-15', '2024-02-14', 50.0000, 4.5000, 54.5000);

-- Invoice Items
CREATE TABLE IF NOT EXISTS `ip_invoice_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `item_product_id` int(11) DEFAULT NULL,
  `item_tax_rate_id` int(11) DEFAULT NULL,
  `item_name` varchar(100) NOT NULL,
  `item_description` text,
  `item_quantity` decimal(10,2) DEFAULT 1.00,
  `item_price` decimal(20,4) DEFAULT 0.0000,
  `item_discount_amount` decimal(20,4) DEFAULT 0.0000,
  `item_subtotal` decimal(20,4) DEFAULT 0.0000,
  `item_tax_total` decimal(20,4) DEFAULT 0.0000,
  `item_total` decimal(20,4) DEFAULT 0.0000,
  `item_order` int(11) DEFAULT 0,
  PRIMARY KEY (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ip_invoice_items` (`item_id`, `invoice_id`, `item_product_id`, `item_tax_rate_id`, `item_name`, `item_description`, `item_quantity`, `item_price`, `item_subtotal`, `item_tax_total`, `item_total`, `item_order`) VALUES
(1, 1, 1, 1, 'Consulting', 'Hourly consulting', 1.00, 100.0000, 100.0000, 21.0000, 121.0000, 1),
(2, 2, 2, 2, 'Widget', 'Standard widget', 1.00, 50.0000, 50.0000, 4.5000, 54.5000, 1);

-- Quotes
CREATE TABLE IF NOT EXISTS `ip_quotes` (
  `quote_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `quote_group_id` int(11) DEFAULT NULL,
  `quote_number` varchar(50) NOT NULL,
  `quote_status_id` int(11) DEFAULT 1,
  `quote_date_created` date DEFAULT NULL,
  `quote_date_expires` date DEFAULT NULL,
  `quote_discount_percent` decimal(8,2) DEFAULT 0.00,
  `quote_discount_amount` decimal(20,4) DEFAULT 0.0000,
  `quote_item_tax_total` decimal(20,4) DEFAULT 0.0000,
  `quote_item_subtotal` decimal(20,4) DEFAULT 0.0000,
  `quote_tax_total` decimal(20,4) DEFAULT 0.0000,
  `quote_total` decimal(20,4) DEFAULT 0.0000,
  `quote_url_key` varchar(50) DEFAULT NULL,
  `quote_terms` text,
  PRIMARY KEY (`quote_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ip_quotes` (`quote_id`, `client_id`, `quote_group_id`, `quote_number`, `quote_status_id`, `quote_date_created`, `quote_date_expires`, `quote_item_subtotal`, `quote_tax_total`, `quote_total`) VALUES
(1, 1, 1, 'QUO-001', 2, '2024-01-01', '2024-01-31', 100.0000, 21.0000, 121.0000);

-- Quote Items
CREATE TABLE IF NOT EXISTS `ip_quote_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `quote_id` int(11) NOT NULL,
  `item_product_id` int(11) DEFAULT NULL,
  `item_tax_rate_id` int(11) DEFAULT NULL,
  `item_name` varchar(100) NOT NULL,
  `item_description` text,
  `item_quantity` decimal(10,2) DEFAULT 1.00,
  `item_price` decimal(20,4) DEFAULT 0.0000,
  `item_discount_amount` decimal(20,4) DEFAULT 0.0000,
  `item_subtotal` decimal(20,4) DEFAULT 0.0000,
  `item_tax_total` decimal(20,4) DEFAULT 0.0000,
  `item_total` decimal(20,4) DEFAULT 0.0000,
  `item_order` int(11) DEFAULT 0,
  PRIMARY KEY (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ip_quote_items` (`item_id`, `quote_id`, `item_product_id`, `item_tax_rate_id`, `item_name`, `item_description`, `item_quantity`, `item_price`, `item_subtotal`, `item_tax_total`, `item_total`, `item_order`) VALUES
(1, 1, 1, 1, 'Consulting', 'Hourly consulting', 1.00, 100.0000, 100.0000, 21.0000, 121.0000, 1);

-- Payments
CREATE TABLE IF NOT EXISTS `ip_payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `payment_method_id` int(11) DEFAULT 1,
  `payment_amount` decimal(20,4) DEFAULT 0.0000,
  `payment_date` date DEFAULT NULL,
  `payment_note` text,
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ip_payments` (`payment_id`, `invoice_id`, `client_id`, `payment_method_id`, `payment_amount`, `payment_date`, `payment_note`) VALUES
(1, 2, 2, 2, 54.5000, '2024-02-01', 'Payment received via bank transfer');
