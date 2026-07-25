-- Add supplier_id and order_type to orders table for purchase order support
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `supplier_id` INT UNSIGNED DEFAULT NULL AFTER `user_id`;
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `order_type` ENUM('sale', 'purchase') NOT NULL DEFAULT 'sale' AFTER `order_number`;
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `expected_date` DATE DEFAULT NULL AFTER `notes`;

-- Add foreign key for supplier_id
ALTER TABLE `orders` ADD CONSTRAINT `fk_orders_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE SET NULL;

-- Add index for order_type
ALTER TABLE `orders` ADD INDEX IF NOT EXISTS `idx_order_type` (`order_type`);
ALTER TABLE `orders` ADD INDEX IF NOT EXISTS `idx_supplier` (`supplier_id`);
