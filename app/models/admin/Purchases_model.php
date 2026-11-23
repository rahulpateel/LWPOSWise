<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Purchases_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getProductNames($term, $brand_id = NULL, $limit = 5)
    {
        $this->db->where("type = 'standard' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        if($brand_id)
        {
            $this->db->where('brand',$brand_id);
        }
        $this->db->where('is_active',1);
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllProducts()
    {
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductByID($id)
    {
        $q = $this->db->get_where('products', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductsByCode($code)
    {
        $this->db->select('*')->from('products')->like('code', $code, 'both');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductByCode($code)
    {
        $q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductByName($name)
    {
        $q = $this->db->get_where('products', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllPurchases()
    {
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getAllPurchaseItems($purchase_id)
    {
        $this->db->select('purchase_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant,mrp,products.price')
            ->join('products', 'products.id=purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_items.tax_rate_id', 'left')
            ->group_by('purchase_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	public function getAllReturnedItems($purchase_id)
	{
		$this->db->select('purchase_items.*, products.unit, products.details as details,product_variants.name as variant')

		         ->join('purchase_items', 'purchases.id=purchase_items.purchase_id', 'left')
				->join('products', 'products.id=purchase_items.product_id', 'left')
			    ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
		        // ->group_by('purchase_items.id')
		         ->order_by('purchases.date', 'asc');
		$q = $this->db->get_where('purchases', array('purchases.purchase_id' => $purchase_id,'purchases.status'=>'returned'));
		if ($q->num_rows() > 0) {
			foreach (($q->result()) as $row) {
				$data[] = $row;
			}
			return $data;
		}
		return FALSE;
	}

    public function getItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTaxRateByName($name)
    {
        $q = $this->db->get_where('tax_rates', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPurchaseByID($id)
    {
        $q = $this->db->get_where('purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductOptionByID($id)
    {
        $q = $this->db->get_where('product_variants', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductWarehouseOptionQty($option_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity + $quantity;
            if ($this->db->update('warehouses_products_variants', array('quantity' => $nq), array('option_id' => $option_id, 'warehouse_id' => $warehouse_id))) {
                return TRUE;
            }
        } else {
            if ($this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity))) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function resetProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('warehouses_products_variants', array('quantity' => $nq), array('option_id' => $option_id, 'warehouse_id' => $warehouse_id))) {
                return TRUE;
            }
        } else {
            $nq = 0 - $quantity;
            if ($this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $nq))) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function getOverSoldCosting($product_id)
    {
        $q = $this->db->get_where('costing', array('overselling' => 1));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function addPurchase($data, $items,$add_product=NULL)
    { 

                    
            if ($this->db->insert('purchases', $data)) {
                $purchase_id = $this->db->insert_id();
                if ($this->site->getReference('po') == $data['reference_no']) {
                    $this->site->updateReference('po');
                }else
                {
                    $this->site->updateReference('rep');
                }
                foreach ($items as $item) {
                    $item['purchase_id'] = $purchase_id;
                    $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : NULL;
                    $this->db->insert('purchase_items', $item);
                    if ($this->Settings->update_cost) {
                        $this->db->update('products', array('cost' => $item['real_unit_cost']), array('id' => $item['product_id']));
                    }
                    if($item['option_id']) {
                        $this->db->update('product_variants', array('cost' => $item['real_unit_cost']), array('id' => $item['option_id'], 'product_id' => $item['product_id']));
                    }
                    if ($data['status'] == 'received' || $data['status'] == 'returned') {
                        $this->updateAVCO(array('product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['real_unit_cost']));

                        $supplier = $this->site->getCompanyByID($data['supplier_id']);
                        if($data['status'] == 'received')
                            $this->db->update('companies',array('balance' =>$supplier->balance+$data['grand_total'] ),array('id'=>$data['supplier_id']));
                        else if($data['status'] == 'returned')
                            $this->db->update('companies',array('balance' =>$supplier->balance-$data['grand_total'] ),array('id'=>$data['supplier_id']));
                    }
                }   
                if ($data['status'] == 'returned') {
                    $this->db->update('purchases', array('return_purchase_ref' => $data['return_purchase_ref'], 'surcharge' => $data['surcharge'],'return_purchase_total' => $data['grand_total'], 'return_id' => $purchase_id), array('id' => $data['purchase_id']));
                    /*$supplier =$this->site->getCompanyByID($data['supplier_id']);
                    $balance = ($data['grand_total'] + $supplier->returned_balance);
                    $this->db->update('companies', array('returned_balance' => $balance), array('id' => $data['supplier_id']));*/
                }

                if ($data['status'] == 'received' || $data['status'] == 'returned') {
                    $this->site->syncQuantity(NULL, $purchase_id);
                }
                /*if(!empty($add_product))
                {
                     $i = sizeof($add_product);
                    for ($r = 0; $r < $i; $r++) {
                        $product_id = $add_product[$r];
                        $product_data[] = array(
                            'supplier_id' => $data['supplier_id'],
                            'product_id' => $product_id,
                        );
                    }
                    krsort($product_data);
                    foreach ($product_data as $item) {
                        $this->db->insert('supplier_products', $item); 
                    }
                }*/
                if(!empty($add_product))
                {
                    $i = sizeof($add_product);
                    for ($r = 0; $r < $i; $r++) {
                        $product_id = $add_product[$r];
                        $q=$this->db->get_where('supplier_products',array('supplier_id' => $data['supplier_id'],'product_id' => $product_id));
                        
                        if ($q->num_rows() <= 0) {
                            $product_data[] = array(
                                'supplier_id' => $data['supplier_id'],
                                'product_id' => $product_id,
                            );
                        }
                        }
                    }
                    
                   if(!empty($product_data))
                   {
                        krsort($product_data);
                        foreach ($product_data as $item) {
                            $this->db->insert('supplier_products', $item); 
                        }
                    }
                return true;
            }
            return false;
        }


    public function updatePurchase($id, $data, $items = array())
    {
        //$opurchase = $this->getPurchaseByID($id);
        $oitems = $this->getAllPurchaseItems($id);
        if ($this->db->update('purchases', $data, array('id' => $id)) && $this->db->delete('purchase_items', array('purchase_id' => $id))) {
            $purchase_id = $id;
            foreach ($items as $item) {
                $item['purchase_id'] = $id;
                $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : NULL;
                $this->db->insert('purchase_items', $item);
                if ($data['status'] == 'received' || $data['status'] == 'partial') {
                    $this->updateAVCO(array('product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['real_unit_cost']));
                }
            }
           $this->site->syncQuantity(NULL, NULL, $oitems);
            if ($data['status'] == 'received' || $data['status'] == 'partial') {
                $this->site->syncQuantity(NULL, $id);
                foreach ($oitems as $oitem) {
                    $this->updateAVCO(array('product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'quantity' => (0-$oitem->quantity), 'cost' => $oitem->real_unit_cost));
                }
            }
            $this->site->syncPurchasePayments($id);
            return true;
        }

        return false;
    }

    public function updateStatus($id, $status, $note)
    {
        // $purchase = $this->getPurchaseByID($id);
        $items = $this->site->getAllPurchaseItems($id);

        if ($this->db->update('purchases', array('status' => $status, 'note' => $note), array('id' => $id))) {
            foreach ($items as $item) {
                $qb = $status == 'completed' ? ($item->quantity_balance + ($item->quantity - $item->quantity_received)) : $item->quantity_balance;
                $qr = $status == 'completed' ? $item->quantity : $item->quantity_received;
                $this->db->update('purchase_items', array('status' => $status, 'quantity_balance' => $qb, 'quantity_received' => $qr), array('id' => $item->id));
                $this->updateAVCO(array('product_id' => $item->product_id, 'warehouse_id' => $item->warehouse_id, 'quantity' => $item->quantity, 'cost' => $item->real_unit_cost));
            }
            $this->site->syncQuantity(NULL, NULL, $items);
            return true;
        }
        return false;
    }

    public function deletePurchase($id)
    {
        $purchase = $this->getPurchaseByID($id);
        $purchase_items = $this->site->getAllPurchaseItems($id);
        if ($this->db->delete('purchase_items', array('purchase_id' => $id)) && $this->db->delete('purchases', array('id' => $id))) {
            $data = array(
                'return_id' => '',
                'return_purchase_ref' => '',
                'return_purchase_total' => '0',

            );

           $this->db->update('purchases', $data, array('return_id' => $id));
            $this->db->delete('payments', array('purchase_id' => $id));
            if ($purchase->status == 'received' || $purchase->status == 'partial') {
                foreach ($purchase_items as $oitem) {
                    $this->updateAVCO(array('product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'quantity' => (0-$oitem->quantity), 'cost' => $oitem->real_unit_cost));
                    $received = $oitem->quantity_received ? $oitem->quantity_received : $oitem->quantity;
                    if ($oitem->quantity_balance < $received) {
                        $clause = array('purchase_id' => NULL, 'transfer_id' => NULL, 'product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'option_id' => $oitem->option_id);
                        $this->site->setPurchaseItem($clause, ($oitem->quantity_balance - $received));
                    }
                }
            }
            $flag=NULL;

            if ($purchase->status == 'returned' )
            {
                $flag=TRUE;
            }

            $this->site->syncQuantity(NULL, NULL, $purchase_items,NULL,NULL,$flag);
            return true;
        }
        return FALSE;
    }

    public function getWarehouseProductQuantity($warehouse_id, $product_id)
    {
        $q = $this->db->get_where('warehouses_products', array('warehouse_id' => $warehouse_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPurchasePayments($purchase_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payments', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getPaymentByID($id)
    {
        $q = $this->db->get_where('payments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function getPaymentsForPurchase($purchase_id)
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount, payments.reference_no, users.first_name, users.last_name, type,payments.transaction_number')
            ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function addPayment($data = array(),$return_payment=NULL,$debit=NULL)
    { 
        if ($this->db->insert('payments', $data)) {
            if ($this->site->getReference('ppay') == $data['reference_no']) {
                $this->site->updateReference('ppay');
            }
            if(!empty($return_payment) && !empty($debit))
            {
                foreach($return_payment as $payment)
                {
                    $this->db->insert('payments', $payment);
                }

                foreach($debit as $value)
                {
                    $this->db->update('debit', array('total'=>$value['total'],'payment_status'=>$value['payment_status']),array('ret_refrence_no' => $value['ret_refrence_no']));
                }
            }
            $this->site->syncPurchasePayments($data['purchase_id']);
            return true;
        }
        return false;
    }

    public function updatePayment($id, $data = array())
    {
        if ($this->db->update('payments', $data, array('id' => $id))) {
            $this->site->syncPurchasePayments($data['purchase_id']);
            return true;
        }
        return false;
    }

    public function deletePayment($id)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->delete('payments', array('id' => $id))) {
            $this->site->syncPurchasePayments($opay->purchase_id);
            return true;
        }
        return FALSE;
    }

    public function getProductOptions($product_id)
    {
        $q = $this->db->get_where('product_variants', array('product_id' => $product_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductVariantByName($name, $product_id)
    {
        $q = $this->db->get_where('product_variants', array('name' => $name, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getExpenseByID($id)
    {
        $q = $this->db->get_where('expenses', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addExpense($data = array())
    {
        if ($this->db->insert('expenses', $data)) {
            if ($this->site->getReference('ex') == $data['reference']) {
                $this->site->updateReference('ex');
            }
            return true;
        }
        return false;
    }

    public function updateExpense($id, $data = array())
    {
        if ($this->db->update('expenses', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteExpense($id)
    {
        if ($this->db->delete('expenses', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getQuoteByID($id)
    {
        $q = $this->db->get_where('quotes', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllQuoteItems($quote_id)
    {
        $q = $this->db->get_where('quote_items', array('quote_id' => $quote_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getReturnByID($id)
    {
        $q = $this->db->get_where('return_purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllReturnItems($return_id)
    {
        $this->db->select('return_purchase_items.*, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=return_purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=return_purchase_items.option_id', 'left')
            ->group_by('return_purchase_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('return_purchase_items', array('return_id' => $return_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getPurcahseItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function returnPurchase($data = array(), $items = array())
    {

        $purchase_items = $this->site->getAllPurchaseItems($data['purchase_id']);

        if ($this->db->insert('return_purchases', $data)) {
            $return_id = $this->db->insert_id();
            if ($this->site->getReference('rep') == $data['reference_no']) {
                $this->site->updateReference('rep');
            }
            foreach ($items as $item) {
                $item['return_id'] = $return_id;
                $this->db->insert('return_purchase_items', $item);

                if ($purchase_item = $this->getPurcahseItemByID($item['purchase_item_id'])) {
                    if ($purchase_item->quantity == $item['quantity']) {
                        $this->db->delete('purchase_items', array('id' => $item['purchase_item_id']));
                    } else {
                        $nqty = $purchase_item->quantity - $item['quantity'];
                        $bqty = $purchase_item->quantity_balance - $item['quantity'];
                        $rqty = $purchase_item->quantity_received - $item['quantity'];
                        $tax = $purchase_item->unit_cost - $purchase_item->net_unit_cost;
                        $discount = $purchase_item->item_discount / $purchase_item->quantity;
                        $item_tax = $tax * $nqty;
                        $item_discount = $discount * $nqty;
                        $subtotal = $purchase_item->unit_cost * $nqty;
                        $this->db->update('purchase_items', array('quantity' => $nqty, 'quantity_balance' => $bqty, 'quantity_received' => $rqty, 'item_tax' => $item_tax, 'item_discount' => $item_discount, 'subtotal' => $subtotal), array('id' => $item['purchase_item_id']));
                    }

                }
            }
            $this->calculatePurchaseTotals($data['purchase_id'], $return_id, $data['surcharge']);
            $this->site->syncQuantity(NULL, NULL, $purchase_items);
            $this->site->syncQuantity(NULL, $data['purchase_id']);
            return true;
        }
        return false;
    }

    public function calculatePurchaseTotals($id, $return_id, $surcharge)
    {
        $purchase = $this->getPurchaseByID($id);
        $items = $this->getAllPurchaseItems($id);
        if (!empty($items)) {
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            foreach ($items as $item) {
                $product_tax += $item->item_tax;
                $product_discount += $item->item_discount;
                $total += $item->net_unit_cost * $item->quantity;
            }
            if ($purchase->order_discount_id) {
                $percentage = '%';
                $order_discount_id = $purchase->order_discount_id;
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = (($total + $product_tax) * (Float)($ods[0])) / 100;
                } else {
                    $order_discount = $order_discount_id;
                }
            }
            if ($purchase->order_tax_id) {
                $order_tax_id = $purchase->order_tax_id;
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = (($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            }
            $total_discount = $order_discount + $product_discount;
            $total_tax = $product_tax + $order_tax;
            $grand_total = $total + $total_tax + $purchase->shipping - $order_discount + $surcharge;
            $data = array(
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'grand_total' => $grand_total,
                'return_id' => $return_id,
                'surcharge' => $surcharge
            );

            if ($this->db->update('purchases', $data, array('id' => $id))) {
                return true;
            }
        } else {
            $this->db->delete('purchases', array('id' => $id));
        }
        return FALSE;
    }

    public function getExpenseCategories()
    {
        $q = $this->db->get('expense_categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getExpenseCategoryByID($id)
    {
        $q = $this->db->get_where("expense_categories", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateAVCO($data)
    {
        if ($wp_details = $this->getWarehouseProductQuantity($data['warehouse_id'], $data['product_id'])) {
            $total_cost = (($wp_details->quantity * $wp_details->avg_cost) + ($data['quantity'] * $data['cost']));
            $total_quantity = $wp_details->quantity + $data['quantity'];
            if (!empty($total_quantity)) {
                $avg_cost = ($total_cost / $total_quantity);
                $this->db->update('warehouses_products', array('avg_cost' => $avg_cost), array('product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']));
            }
        } else {
            $this->db->insert('warehouses_products', array('product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id'], 'avg_cost' => $data['cost'], 'quantity' => 0));
        }
    }
    public function getStateByID($id)
    {
        $q = $this->db->get_where('states', array('id' => $id));
        if ($q->num_rows() > 0) {

            return $q->row();
        }
        return FALSE;
    }

    public function getBalanceQuantity($product_id, $warehouse_id = NULL,$returnPurchaseId) {
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as stock', False);
        $this->db->where('product_id', $product_id)->where('quantity_balance !=', 0);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if ($returnPurchaseId) {
            $this->db->where_in('purchase_id', $returnPurchaseId);
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }

    public function getAllReturnPurchaseIdByRefrence($reference_no) { 
        $this->db->select('id', False);
        $this->db->where('reference_no', $reference_no);
        $this->db->where('status', 'returned');
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row->id;
            }
            return $data;
        }
        return FALSE;
    }

    public function getTaxdata($id){
        $this->db->select('SUM(subtotal) as item_tax,cgst_rate,sgst_rate,igst_rate,cess_rate,SUM(cgst_amt) as cgst_total,SUM(sgst_amt) as sgst_total,SUM(igst_amt) as igst_total,SUM(cess_amt) as cess_total,')
            ->from('purchase_items')
            ->group_by('cgst_rate')
            ->where('purchase_id',$id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getLinkedProductNames($supplier_id)
    {
        $this->db->select("supplier_products.supplier_id,supplier_products.product_id,products.*");
        $this->db->from("supplier_products");
        $this->db->join("products","supplier_products.product_id=products.id");
        $this->db->where("products.type = 'standard'");
        $this->db->where("supplier_products.supplier_id",$supplier_id);        
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getInternalPOProducts($brand_id)
    {
        $this->db->select('internal_purchase_items.*,(SUM(sma_internal_purchase_items.quantity)-SUM(sma_internal_purchase_items.quantity_received)) as Qty');
        $this->db->from('internal_purchase_items');
        $this->db->join('internal_purchases','internal_purchases.id = internal_purchase_items.purchase_id');
        $this->db->where('internal_purchases.brand_id',$brand_id);
        $this->db->where('internal_purchases.status !=','completed');
        $this->db->group_by('internal_purchase_items.product_id');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getBrandDataByID($brand_id)
    {
       $q= $this->db->get_where('internal_purchases',array('brand_id'=>$brand_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllBranches($code) {
        $this->db->where('code !=',$code);
        $q = $this->db->get('internal_branches');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getDebitDataBySupplier($supplier_id)
    {
        $this->db->select('*');
        $this->db->from('debit');
        $this->db->where('supplier_id',$supplier_id);
        $this->db->where('payment_status !=','paid');
        $q= $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getTotalReturn($id)
    {
        $this->db->select('sum(total) as total');
        $this->db->from('debit');
        $this->db->where_in('id',$id);
        $q=$this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getDebitDataByID($debit_id) {
        $q = $this->db->get_where('debit',array('id'=>$debit_id),1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getReturnPurchaseByReference($reference_no)
    {
        $q = $this->db->get_where('purchases',array('return_purchase_ref'=>$reference_no),1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getDebitDataByReference($reference_no)
    {
        $q = $this->db->get_where('debit',array('ret_refrence_no'=>$reference_no),1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getBrandSuggestions($term, $limit = 10)
    {
        $this->db->select("id, name as text", FALSE);
        $this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' ) ");
        $q = $this->db->get('brands', $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    } 

    public function getRemQuantityAfterRetuurn($product_id, $warehouse_id = NULL,$returnPurchaseId) {
        $this->db->select('SUM(COALESCE(quantity, 0)) as stock', False);
        $this->db->where('product_id', $product_id)->where('quantity_balance !=', 0);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if ($returnPurchaseId) {
            $this->db->where_in('purchase_id', $returnPurchaseId);
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }

    public function getInternalPurchaseByID($id)
    {
        $q = $this->db->get_where('internal_purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllInternalPurchaseItems($purchase_id)
    {
        $this->db->select('internal_purchase_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant,(unit_quantity-quantity_received) as remaining_quantity')
            ->join('products', 'products.id=internal_purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=internal_purchase_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=internal_purchase_items.tax_rate_id', 'left')
            ->group_by('internal_purchase_items.id')
            ->order_by('remaining_quantity', 'desc');
        $q = $this->db->get_where('internal_purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getBranchByCode($code)
    {
        $q = $this->db->get_where('internal_branches', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllReteurnPurchaseList($purchase_id)
    {
        $q = $this->db->get_where('purchases', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }



    public function getPurchasesByID($id)
    {
        $this->db->select('id,sum(balance)');
        $q = $this->db->where_in('purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    
    public function getMultiPurchaseByID($id)
    {
        $this->db->select('id,group_concat( reference_no SEPARATOR ",") as refno ,sum(grand_total-paid) as balance'); 
        $this->db->where('payment_status!=', 'paid');
        $this->db->where_in('id', $id);
        $q=$this->db->get('purchases');
        if ($q->num_rows() > 0) {
          return $q->row();
        }
        else
        {

        return FALSE;
        }
    }

     public function getMulti1PurchaseByID($id)
    {
        $this->db->where_in('id', $id);
        $this->db->where('payment_status!=', 'paid');
       

        $q=$this->db->get('purchases');
        if ($q->num_rows() > 0) {
          foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }



     public function checkDebitPayment1($id) {

        $q = $this->db->get_where('debit', array('purchase_id' => $id), 1);
        if ($q->num_rows() > 0) {
            $ref_no = $q->row()->ret_refrence_no;
            $id = $q->row()->id;
            $this->db->select('id');
            $this->db->group_start()->where('reference_no', $ref_no)->or_where('debit_note_id', $id)->group_end();
            $pay_query = $this->db->get('payments');
            if ($pay_query->num_rows() > 0) {

                return TRUE;
            }

        } else {
            return FALSE;
        }
    }




    //new DEBIT NOte
    //add debit note
    public function addDebitnote1($data, $items, $add_product = NULL) {


        if (1 == 1) {
            $purchase_id = $data['purchase_id'];
            $data['purchase_id'] = $purchase_id;
            $this->db->update('purchases', array('purchase_id' => $purchase_id), array('id' => $purchase_id));

            if ($this->site->getReference('pr') == $data['reference_no']) {
                $this->site->updateReference('pr');
            } else {
                $this->site->updateReference('rep');
            }
            foreach ($items as $item) {
                $item['purchase_id'] = $purchase_id;
                $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : NULL;
                $this->db->insert('purchase_items', $item);
                if ($this->Settings->update_cost) {
                    $this->db->update('products', array('cost' => $item['real_unit_cost']), array('id' => $item['product_id']));
                }
                if ($item['option_id']) {
                    $this->db->update('product_variants', array('cost' => $item['real_unit_cost']), array('id' => $item['option_id'], 'product_id' => $item['product_id']));
                }
                if ($data['status'] == 'received' || $data['status'] == 'returned') {
                    $this->updateAVCO(array('product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => (-1 * $item['quantity']), 'cost' => $item['real_unit_cost']));

                    $supplier = $this->site->getCompanyByID($data['supplier_id']);
                    if ($data['status'] == 'received')
                        $this->db->update('companies', array('balance' => $supplier->balance + $data['grand_total']), array('id' => $data['supplier_id']));
                    else if ($data['status'] == 'returned')
                        $this->db->update('companies', array('balance' => $supplier->balance - $data['grand_total']), array('id' => $data['supplier_id']));
                }
            }
            if ($data['status'] == 'returned') {
                $this->db->update('purchases', array('return_purchase_ref' => $data['return_purchase_ref'], 'surcharge' => $data['surcharge'], 'return_purchase_total' => $data['grand_total'], 'return_id' => $purchase_id), array('id' => $data['purchase_id']));
            }

            if ($data['status'] == 'received' || $data['status'] == 'returned') {
                $this->site->syncQuantity(NULL, $purchase_id);
            }



            return true;
        }
        return false;
    }

    public function addDebitNote($items, $data, $did = NULL) {

        $flag = true;
        log_message('error', "in add debit note");
        //    echo "items ".json_encode($items);
        //  echo "data ".json_encode($data);

        try {
            //add debit note entry
            /* `warehouse_id`, 
              `product_discount`,
              `order_discount_id`,
              `order_discount`,
              `total_discount`,
              `product_tax`,
              `order_tax_id`,
              `order_tax`,
              `total_tax`,
              `shipping`,
              `grand_total`,
              `status`,
              `created_by`,
              `payment_term`,
              `due_date` */

            //debit note data  
            $debit_data = array(
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'product_discount' => $data['product_discount'],
                'order_discount_id' => $data['order_discount_id'],
                'order_discount' => $data['order_discount'],
                'total_discount' => $data['total_discount'],
                'order_tax_id' => $data['order_tax_id'],
                'order_tax' => $data['order_tax'],
                'total_tax' => $data['total_tax'],
                'shipping' => $data['shipping'],
                'grand_total' => $data['grand_total'],
                'status' => $data['status'],
                'created_by' => $data['created_by'],
                'payment_term' => $data['payment_term'],
                'due_date' => $data['due_date'],
                'date' => $data['date'],
                'purchase_id' => 0,
                'ret_refrence_no' => $data['return_purchase_ref'],
                'total' => $data['grand_total'],
                'payment_status' => 'pending',
                'created_by' => $this->session->userdata('user_id')
            );

            $opr_flag = true;
            $debit_note_id = 0;

            if (!empty($did)) {// if update
                log_message("error", "In update with dnid " . $did);
                $this->db->where("id", $did);
                if ($this->db->update('debit', $debit_data) == FALSE) {
                    $opr_flag = false;
                    log_message("error", "DN not updated ");
                } else {
                    log_message("error", "DN updated ");
                    // old debit noteid
                    $debit_note_id = $did;

                    $pis = $this->purchases_model->getDebitItems($did);

                    foreach ($pis as $pi) {

                        $qtyPlus = (-1) * $pi->quantity;

                        //products
                        $this->db->where('id', $pi->product_id);
                        $this->db->set('quantity', 'quantity+' . "'" . $qtyPlus . "'", FALSE);
                        $this->db->update('products');
                        log_message("error", "product qty update success");

                        //warehouses_products
                        $this->db->where('product_id', $pi->product_id);
                        $this->db->set('quantity', 'quantity+' . "'" . $qtyPlus . "'", FALSE);
                        $this->db->update('warehouses_products');
                        log_message("error", "warehouses products qty update success");

                        # code...
                    }

                    //delete old items
                    $this->db->delete('purchase_items', array('debit_note_id' => $did));
                    log_message("error", "pis deleted ");
                }
            } else {

                log_message("error", "in insert ");
                if (!$this->db->insert('debit', $debit_data)) {

                    $opr_flag = false;
                    log_message("error", "insert fail ");
                } else {
                    $debit_note_id = $this->db->insert_id();
                    $this->site->updateReference('rep');
                    log_message("error", "insert success ");
                }
            }

            if ($opr_flag) {

                log_message('error', "debit_note_id " . $debit_note_id);

                foreach ($items as $item) {
                    log_message('error', "//start//");
                    log_message('error', "item " . json_encode($item));
                    $curr_prod_id = $item['product_id'];

                    //echo "".$item['product_id'];die();
                    //get quantity balance
                    $bal_q = $this->db->query("select sum(COALESCE(quantity_balance,0)) as bal from sma_purchase_items where product_id=" . $curr_prod_id . " limit 0,1 ");
                    $bal_qty = 0;
                    $org_qty = 0;

                    $prod_details = $this->getProductByID($curr_prod_id);

                    if ($prod_details) {
                        $org_qty = $prod_details->quantity;
                    }


                    if ($bal_q->num_rows() > 0) {
                        $bal_qty = $bal_q->row()->bal;
                        //echo "BAL" .$balq;
                    }
                    log_message('error', "product " . $curr_prod_id);
                    log_message('error', "ORG QTY " . $org_qty);
                    log_message('error', "BAL QTY " . $bal_qty);
                    //quantity difference
                    $qtyDiff = $org_qty - $bal_qty;
                    log_message('error', "DIFF QTY " . $qtyDiff);

                    //set to current quantity if any difference
                    if ($qtyDiff != 0) {
                        log_message('error', "if qty diff");
                        //purchase item
                        $clause['product_id'] = $curr_prod_id;
                        $clause['quantity'] = 0;
                        $clause['item_tax'] = 0;
                        $clause['quantity_balance'] = $qtyDiff;
                        $clause['quantity_received'] = $qtyDiff;
                        $clause['status'] = 'received';
                        $clause['option_id'] = NULL;
                        $clause['warehouse_id'] = $this->Settings->default_warehouse;
                        $clause['product_code'] = $prod_details->code;
                        $clause['product_name'] = $prod_details->name;
                        $clause['debit_note_id'] = 0;
                        $this->db->insert('purchase_items', $clause);
                        log_message("error", "PI insert success " . json_encode($clause));

                        //products
                        $this->db->where('id', $curr_prod_id);
                        $this->db->set('quantity', $org_qty, FALSE);
                        $this->db->update('products');
                        log_message("error", "product qty update success");

                        //warehouses_products
                        $this->db->where('product_id', $curr_prod_id);
                        $this->db->set('quantity', $org_qty, FALSE);
                        $this->db->update('warehouses_products');
                        log_message("error", "warehouses products qty update success");
                    }

                    //set to return qty purchase_items
                    // $clause['product_id'] = $item->id;
                    // $clause['quantity'] = $item->quantity;
                    // $clause['item_tax'] = 0;
                    // $clause['quantity_balance'] = $item->quantity;
                    // $clause['quantity_received'] = $item->quantity;
                    // $clause['status'] = 'received';
                    // $clause['option_id'] = NULL;
                    // $clause['warehouse_id'] = $this->Settings->default_warehouse;
                    // $clause['product_code'] = $prod_details->code;
                    // $clause['product_name'] = $prod_details->name;
                    // $clause['debit_note_id'] = $debit_note_id;

                    $clauseDN = array(
                        'product_id' => $curr_prod_id,
                        'product_code' => $item['product_code'],
                        'product_name' => $item['product_name'],
                        'option_id' => $item['option_id'],
                        'net_unit_cost' => $item['net_unit_cost'],
                        'unit_cost' => $item['unit_cost'],
                        'quantity' => $item['quantity'],
                        'product_unit_id' => $item['product_unit_id'],
                        'product_unit_code' => $item['product_unit_code'],
                        'unit_quantity' => $item['unit_quantity'],
                        'quantity_balance' => $item['quantity'],
                        'quantity_received' => $item['quantity'],
                        'warehouse_id' => $item['warehouse_id'],
                        'item_tax' => $item['item_tax'],
                        'tax_rate_id' => $item['tax_rate_id'],
                        'tax' => $item['tax'],
                        'discount' => $item['discount'],
                        'item_discount' => $item['item_discount'],
                        'subtotal' => $item['subtotal'],
                        'expiry' => $item['expiry'],
                        'real_unit_cost' => $item['real_unit_cost'],
                        'date' => $item['date'],
                        'status' => 'received',
                        'supplier_part_no' => $item['supplier_part_no'],
                        'gst_code' => $item['gst_code'],
                        'cgst_rate' => $item['cgst_rate'],
                        'sgst_rate' => $item['sgst_rate'],
                        'igst_rate' => $item['igst_rate'],
                        'cgst_amt' => $item['cgst_amt'],
                        'sgst_amt' => $item['sgst_amt'],
                        'igst_amt' => $item['igst_amt'],
                        'cess_rate' => $item['cess_rate'],
                        'cess_amt' => $item['cess_amt'],
                        'debit_note_id' => $debit_note_id
                    );
                    $this->db->insert('purchase_items', $clauseDN);
                    log_message("error", "pis insert success");

                    $finalQty = 0;
                    $bal_q = $this->db->query("select sum(COALESCE(quantity_balance,0)) as bal from sma_purchase_items where product_id=" . $curr_prod_id . " limit 0,1 ");
                    if ($bal_q->num_rows() > 0) {
                        $finalQty = $bal_q->row()->bal;
                    }
                    log_message('error', "Final qty " . $finalQty);

                    //products
                    $this->db->where('id', $curr_prod_id);
                    $this->db->set('quantity', $finalQty, FALSE);
                    $this->db->update('products');
                    log_message("error", "product qty update success");

                    //warehouses_products
                    $this->db->where('product_id', $curr_prod_id);
                    $this->db->set('quantity', $finalQty, FALSE);
                    $this->db->update('warehouses_products');
                    log_message("error", "warehouses products qty update success");

                    log_message('error', "//end//");
                }
            } else {
                $flag = false;
                log_message("error", "error in creating/updating debit note");
            }
        } catch (Exception $e) {

            $flag = false;
        }

        return $flag;

        # code...
    }

    public function getDebitNoteByID($id) {
        $q = $this->db->get_where('debit', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function checkDebitPayment($id) {
        $q = $this->db->get_where('debit', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            $ref_no = $q->row()->ret_refrence_no;
            $id = $q->row()->id;
            $this->db->select('id');
            $this->db->group_start()->where('reference_no', $ref_no)->or_where('debit_note_id', $id)->group_end();
            $pay_query = $this->db->get('payments');
            if ($pay_query->num_rows() > 0) {

                return TRUE;
            }

        } else {
            return FALSE;
        }
    }

    public function checkDebitPaymentByRef($ref_no,$did) {
        $q = $this->db->get_where('debit', array('id' => $did), 1);
        if ($q->num_rows() > 0) {
            $ref_no = $q->row()->ret_refrence_no;
            $id = $q->row()->id;
            $this->db->select('id');
            $this->db->group_start()->where('reference_no', $ref_no)->or_where('debit_note_id', $id)->group_end();
            $pay_query = $this->db->get('payments');
            if ($pay_query->num_rows() > 0) {

                return TRUE;
            }

        } else {
            return FALSE;
        }
    }

    public function getAllDebitItems($purchase_id) {
        $this->db->select('purchase_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant,mrp,products.price')
                ->join('products', 'products.id=purchase_items.product_id', 'left')
                ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
                ->join('tax_rates', 'tax_rates.id=purchase_items.tax_rate_id', 'left')
                ->group_by('purchase_items.id')
                ->order_by('id', 'asc');
        $q = $this->db->get_where('purchase_items', array('debit_note_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getReturnQty($purchase_item_id) {
        $qty = 0;
        $this->db->select('purchase_items.quantity');
        $q = $this->db->get_where('purchase_items', array('id' => $purchase_item_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $qty = $row->quantity;
            }
        }

        return (-1) * $qty;
        # code...
    }

    public function getDebitItems($purchase_id) {
        $this->db->select('purchase_items.*')
                ->order_by('id', 'asc');
        $q = $this->db->get_where('purchase_items', array('debit_note_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function deleteDebitNote($did) {

        $flag = true;
        log_message('error', "in del debit note");

        $dn = $this->getDebitNoteByID($did);

        
        //    echo "items ".json_encode($items);
        //  echo "data ".json_encode($data);
        try {

            //reverse quantity
            $pis = $this->purchases_model->getDebitItems($did);
            foreach ($pis as $pi) {

                $qtyPlus = (0- $pi->quantity);



                // echo "".$qtyPlus;

                // die();

                //products
                $this->db->where('id', $pi->product_id);
                $this->db->set('quantity', 'quantity+' . "'" . $qtyPlus . "'", FALSE);
                $this->db->update('products');
                log_message("error", "product qty update success");

                //warehouses_products
                $this->db->where('product_id', $pi->product_id);
                $this->db->set('quantity', 'quantity+' . "'" . $qtyPlus . "'", FALSE);
                $this->db->update('warehouses_products');
                log_message("error", "warehouses products qty update success");
                # code...
            }

            //delete old items
            $this->db->delete('purchase_items', array('debit_note_id' => $did));
            log_message("error", "pis deleted ");
            $this->db->delete('debit', array('id' => $did));
            log_message("error", "dn deleted ");
        } catch (Exception $e) {

            $flag = false;
        }
        return $flag;
        # code...
    }

     public function getTaxdataDebitNote($id) {
        $this->db->select('SUM(subtotal) as item_tax,cgst_rate,sgst_rate,igst_rate,cess_rate,SUM(cgst_amt) as cgst_total,SUM(sgst_amt) as sgst_total,SUM(igst_amt) as igst_total,SUM(cess_amt) as cess_total,')
                ->from('purchase_items')
                ->group_by('cgst_rate')
                ->where('debit_note_id', $id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }




        public function getPurchaseItems($purchase_id) {
    
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

     public function getDelPurchaseByID($id) {
        $q = $this->db->get_where('delpurchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }


    public function getAllDelPurchaseItems($purchase_id) {
        $this->db->select('delpurchase_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant,mrp,products.price')
                ->join('products', 'products.id=delpurchase_items.product_id', 'left')
                ->join('product_variants', 'product_variants.id=delpurchase_items.option_id', 'left')
                ->join('tax_rates', 'tax_rates.id=delpurchase_items.tax_rate_id', 'left')
                ->group_by('delpurchase_items.id')
                ->order_by('id', 'asc');
        $q = $this->db->get_where('delpurchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }


    public function getDebitNoteByRef($ref) {
        $q = $this->db->get_where('debit', array('ret_refrence_no' => $ref), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

        public function getPurchaseByRef($return_purchase_ref) {
        $q = $this->db->get_where('purchases', array('return_purchase_ref' => $return_purchase_ref), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	     public function getItems($purchase_id)
    {
        $this->db->select('purchase_items.unit_quantity,purchase_items.product_code,purchase_items.product_name,product_variants.name as variant,products.mrp')
            ->join('products', 'products.id=purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_items.tax_rate_id', 'left')
            ->order_by('purchase_items.id', 'asc');
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }


     public function getBCItems($purchase_id)
    {
        $this->db->select('products.id,products.code,products.name,1 as qty')
            ->join('products', 'products.id=purchase_items.product_id', 'left')
            ->order_by('purchase_items.id', 'asc');
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
          
          return $data;
        }
        return FALSE;
    }
	
	///new
	
	 public function updateOldPurchase($id, $data, $items = array())
    {
        //$opurchase = $this->getPurchaseByID($id);
       // $oitems = $this->getAllPurchaseItems($id);
     //   $this->db->update_batch('purchase_items', $items, 'id');
        if ($this->db->update('purchases', $data, array('id' => $id)) &&

         //$this->db->delete('purchase_items', array('purchase_id' => $id)
          $this->db->update_batch('purchase_items', $items, 'id')

     ) {
         
         //old code
          /*  $purchase_id = $id;
            foreach ($items as $item) {
                $item['purchase_id'] = $id;
                $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : NULL;
                $this->db->insert('purchase_items', $item);
                
            } */
          
            $this->site->syncPurchasePayments($id);
            return true;
        }

        return false;
    }


     public function search($term)
    {
        $this->db->select($this->db->dbprefix('purchases') . '.id, supplier,reference_no,grand_total,status,payment_status,paid,(grand_total-paid) as balance,DATE_FORMAT(date

            ,"%Y-%m-%d %T") as date ')

         ->where("status",'received')
        //    ->where(" reference_no like '%".$term."%'"
            ->where(" reference_no like '%".$term."%'"
                 
                 );
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }



    public function getAllOldPurchaseItems($purchase_id)
    {
        $this->db->select('purchase_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant,mrp,products.price')
            ->join('products', 'products.id=purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_items.tax_rate_id', 'left')
            ->group_by('purchase_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

}
