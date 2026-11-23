-- Run once on the idb42 database (table prefix sma_)
CREATE TABLE IF NOT EXISTS `sma_stock_diff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `reference_no` varchar(55) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `note` text,
  `total_products` int(11) NOT NULL DEFAULT '0',
  `diff_products` int(11) NOT NULL DEFAULT '0',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `warehouse_id` (`warehouse_id`),
  KEY `reference_no` (`reference_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sma_stock_diff_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stock_diff_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `wh_qty` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `floor_qty` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total_qty` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `system_qty` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `difference` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `type` varchar(20) NOT NULL,
  `mrp` decimal(15,2) DEFAULT NULL,
  `cost` decimal(25,4) DEFAULT NULL,
  `price` decimal(25,4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_diff_id` (`stock_diff_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
