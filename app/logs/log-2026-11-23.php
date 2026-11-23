ERROR - 2026-11-23 12:27:39 --> product_id 3
ERROR - 2026-11-23 12:27:39 --> req_qty 5
ERROR - 2026-11-23 12:47:35 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'GROUP BY `sma_products`.`id`
 LIMIT 5' at line 7 - Invalid query: SELECT sma_products.*, FWP.quantity as quantity, sma_categories.id as category_id, sma_categories.name as category_name
FROM `sma_products`
LEFT JOIN ( SELECT product_id, warehouse_id, quantity as quantity from sma_warehouses_products ) FWP ON `FWP`.`product_id`=`sma_products`.`id`
LEFT JOIN `sma_categories` ON `sma_categories`.`id`=`sma_products`.`category_id`
WHERE (`sma_products`.`track_quantity` =0 OR `FWP`.`quantity` >0) AND `FWP`.`warehouse_id` = '3' AND (`sma_products`.`name` LIKE '%$('#payment').attr('disabled', true);%' OR `sma_products`.`code` LIKE '%$('#payment').attr('disabled', true);%' OR  concat(sma_products.name, ' (', sma_products.code, ')') LIKE '%$('#payment').attr('disabled', true);%')
AND `sma_products`.`is_active` = 1
GROUP BY `sma_products`.`id`
 LIMIT 5
ERROR - 2026-11-23 12:47:35 --> Severity: Error --> Call to a member function num_rows() on boolean I:\Rahul\Wamp\UwAmp\www\indu6\app\models\admin\Sales_model.php 26
