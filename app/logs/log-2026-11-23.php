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
ERROR - 2026-11-23 14:47:17 --> product_id 104
ERROR - 2026-11-23 14:47:17 --> req_qty 131
ERROR - 2026-11-23 14:52:11 --> in reverseSale 
ERROR - 2026-11-23 14:52:11 --> in oitems 104
ERROR - 2026-11-23 14:52:11 --> product_id 104
ERROR - 2026-11-23 14:52:11 --> req_qty 142
ERROR - 2026-11-23 14:52:11 --> product_id 104
ERROR - 2026-11-23 14:52:11 --> req_qty 132
ERROR - 2026-11-23 14:54:08 --> return sale items [{"product_id":"104","option_id":null,"quantity":5,"warehouse_id":"3","product_type":"standard"}]
ERROR - 2026-11-23 14:54:08 --> in reverseSale 
ERROR - 2026-11-23 14:54:08 --> in oitems 104
ERROR - 2026-11-23 14:54:08 --> product_id 104
ERROR - 2026-11-23 14:54:08 --> req_qty 137
ERROR - 2026-11-23 14:54:08 --> Sale ID 197414 return
ERROR - 2026-11-23 14:54:08 --> Sale Items [{"product_id":"104","product_code":"8901499007704","product_name":"KELLOGGS OATS 200G","product_type":"standard","option_id":null,"net_unit_price":"56.0000","unit_price":"56.0000","quantity":-5,"product_unit_id":"1","product_unit_code":"pcs","unit_quantity":-5,"warehouse_id":"3","item_tax":0,"tax_rate_id":"undefined","tax":"","discount":null,"item_discount":"0.0000","subtotal":"-280.0000","serial_no":"","real_unit_price":"56.0000","sale_item_id":"1539996","cgst_rate":"2.50","sgst_rate":"2.50","cgst_amt":"6.67","sgst_amt":"6.67","cess_rate":"0.00","cess_amt":"0","enable_sale_point":"1"}]
ERROR - 2026-11-23 14:55:43 --> in reverseSale 
ERROR - 2026-11-23 14:55:43 --> in oitems 104
ERROR - 2026-11-23 14:55:43 --> product_id 104
ERROR - 2026-11-23 14:55:43 --> req_qty 132
ERROR - 2026-11-23 14:55:47 --> in reverseSale 
ERROR - 2026-11-23 14:55:47 --> in oitems 104
ERROR - 2026-11-23 14:55:47 --> product_id 104
ERROR - 2026-11-23 14:55:47 --> req_qty 142
ERROR - 2026-11-23 14:57:20 --> product_id 104
ERROR - 2026-11-23 14:57:20 --> req_qty 132
ERROR - 2026-11-23 15:01:27 --> return sale items [{"product_id":"104","option_id":null,"quantity":2.555,"warehouse_id":"3","product_type":"standard"}]
ERROR - 2026-11-23 15:01:27 --> in reverseSale 
ERROR - 2026-11-23 15:01:27 --> in oitems 104
ERROR - 2026-11-23 15:01:27 --> product_id 104
ERROR - 2026-11-23 15:01:27 --> req_qty 134.555
ERROR - 2026-11-23 15:01:27 --> Sale ID 197416 return
ERROR - 2026-11-23 15:01:27 --> Sale Items [{"product_id":"104","product_code":"8901499007704","product_name":"KELLOGGS OATS 200G","product_type":"standard","option_id":null,"net_unit_price":"56.0000","unit_price":"56.0000","quantity":-2.555,"product_unit_id":"1","product_unit_code":"pcs","unit_quantity":-2.555,"warehouse_id":"3","item_tax":0,"tax_rate_id":"undefined","tax":"","discount":null,"item_discount":"0.0000","subtotal":"-143.0800","serial_no":"","real_unit_price":"56.0000","sale_item_id":"1539998","cgst_rate":"2.50","sgst_rate":"2.50","cgst_amt":"3.41","sgst_amt":"3.41","cess_rate":"0.00","cess_amt":"0","enable_sale_point":"1"}]
ERROR - 2026-11-23 15:04:12 --> Severity: Notice --> Undefined index: return_sale_ref I:\Rahul\Wamp\UwAmp\www\indu6\app\models\admin\Sales_model.php 271
ERROR - 2026-11-23 15:04:12 --> product_id 104
ERROR - 2026-11-23 15:04:12 --> req_qty 124.555
ERROR - 2026-11-23 15:09:20 --> Severity: Notice --> Undefined index: return_sale_ref I:\Rahul\Wamp\UwAmp\www\indu6\app\models\admin\Sales_model.php 271
ERROR - 2026-11-23 15:09:20 --> product_id 104
ERROR - 2026-11-23 15:09:20 --> req_qty 113.555
ERROR - 2026-11-23 15:24:45 --> in reverseSale 
ERROR - 2026-11-23 15:24:45 --> in oitems 104
ERROR - 2026-11-23 15:24:45 --> product_id 104
ERROR - 2026-11-23 15:24:45 --> req_qty 124.555
ERROR - 2026-11-23 15:24:45 --> product_id 104
ERROR - 2026-11-23 15:24:45 --> req_qty 112.555
ERROR - 2026-11-23 15:26:01 --> return sale items [{"product_id":"104","option_id":null,"quantity":1,"warehouse_id":"3","product_type":"standard"}]
ERROR - 2026-11-23 15:26:01 --> in reverseSale 
ERROR - 2026-11-23 15:26:01 --> in oitems 104
ERROR - 2026-11-23 15:26:01 --> product_id 104
ERROR - 2026-11-23 15:26:01 --> req_qty 113.555
ERROR - 2026-11-23 15:26:01 --> Sale ID 197419 return
ERROR - 2026-11-23 15:26:01 --> Sale Items [{"product_id":"104","product_code":"8901499007704","product_name":"KELLOGGS OATS 200G","product_type":"standard","option_id":null,"net_unit_price":"51.4988","unit_price":"51.4988","quantity":-1,"product_unit_id":"1","product_unit_code":"pcs","unit_quantity":-1,"warehouse_id":"3","item_tax":0,"tax_rate_id":"undefined","tax":"","discount":null,"item_discount":"0.0000","subtotal":"-51.4988","serial_no":"","real_unit_price":"51.4988","sale_item_id":"1540002","cgst_rate":"2.50","sgst_rate":"2.50","cgst_amt":"1.23","sgst_amt":"1.23","cess_rate":"0.00","cess_amt":"0","enable_sale_point":"1"}]
ERROR - 2026-11-23 15:27:47 --> in reverseSale 
ERROR - 2026-11-23 15:27:47 --> in oitems 104
ERROR - 2026-11-23 15:27:47 --> product_id 104
ERROR - 2026-11-23 15:27:47 --> req_qty 112.555
ERROR - 2026-11-23 15:27:50 --> in reverseSale 
ERROR - 2026-11-23 15:27:50 --> in oitems 104
ERROR - 2026-11-23 15:27:50 --> product_id 104
ERROR - 2026-11-23 15:27:50 --> req_qty 124.555
