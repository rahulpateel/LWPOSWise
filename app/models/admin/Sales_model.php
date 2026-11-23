<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Sales_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getProductNames($term, $warehouse_id, $limit = 5)
    {
        $wp = "( SELECT product_id, warehouse_id from {$this->db->dbprefix('warehouses_products')} ) FWP";

        $this->db->select('products.*, categories.id as category_id, categories.name as category_name', FALSE)
            ->join($wp, 'FWP.product_id=products.id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->group_by('products.id');
        if ($this->Settings->overselling) {
            $this->db->where("({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
        } else {
            $this->db->where("(products.track_quantity = 0 OR quantity > 0) AND FWP.warehouse_id = '" . $warehouse_id . "' AND "
                . "({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
        }
          $this->db->where('products.is_active',1);
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductComboItems($pid, $warehouse_id = NULL)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name,products.type as type, warehouses_products.quantity as quantity')
            ->join('products', 'products.code=combo_items.item_code', 'left')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('combo_items.id');
        if($warehouse_id) {
            $this->db->where('warehouses_products.warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('combo_items', array('combo_items.product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return FALSE;
    }

    public function getProductByCode($code)
    {
        $q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function syncQuantity($sale_id)
    {
        if ($sale_items = $this->getAllInvoiceItems($sale_id)) {
            foreach ($sale_items as $item) {
                $this->site->syncProductQty($item->product_id, $item->warehouse_id);
                if (isset($item->option_id) && !empty($item->option_id)) {
                    $this->site->syncVariantQty($item->option_id, $item->warehouse_id);
                }
            }
        }
    }

    public function getProductQuantity($product_id, $warehouse)
    {
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse), 1);
        if ($q->num_rows() > 0) {
            return $q->row_array(); //$q->row();
        }
        return FALSE;
    }

    public function getProductOptions($product_id, $warehouse_id, $all = NULL)
    {
        $wpv = "( SELECT option_id, warehouse_id, quantity from {$this->db->dbprefix('warehouses_products_variants')} WHERE product_id = {$product_id}) FWPV";
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.price as price, product_variants.quantity as total_quantity, FWPV.quantity as quantity', FALSE)
            ->join($wpv, 'FWPV.option_id=product_variants.id', 'left')
            //->join('warehouses', 'warehouses.id=product_variants.warehouse_id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->group_by('product_variants.id');

        if (! $this->Settings->overselling && ! $all) {
            $this->db->where('FWPV.warehouse_id', $warehouse_id);
            $this->db->where('FWPV.quantity >', 0);
        }
        $q = $this->db->get('product_variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductVariants($product_id)
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

    public function getItemByID($id)
    {

        $q = $this->db->get_where('sale_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function getAllInvoiceItems($sale_id, $return_id = NULL)
    {
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant,products.mrp')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->group_by('sale_items.id')
            ->order_by('id', 'asc');
        if ($sale_id && !$return_id) {
            $this->db->where('sale_id', $sale_id);
        } elseif ($return_id) {
            $this->db->where('sale_id', $return_id);
        }
        $q = $this->db->get('sale_items');
        //print_r( $this->db->last_query());die;
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllInvoiceItemsWithDetails($sale_id)
    {
        $this->db->select('sale_items.*, products.details, product_variants.name as variant');
        $this->db->join('products', 'products.id=sale_items.product_id', 'left')
        ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
        ->group_by('sale_items.id');
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('sale_items', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getInvoiceByID($id)
    {
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getReturnByID($id)
    {
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getReturnBySID($sale_id)
    {
        $q = $this->db->get_where('sales', array('sale_id' => $sale_id), 1);
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

    public function updateOptionQuantity($option_id, $quantity)
    {
        if ($option = $this->getProductOptionByID($option_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('product_variants', array('quantity' => $nq), array('id' => $option_id))) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function addOptionQuantity($option_id, $quantity)
    {
        if ($option = $this->getProductOptionByID($option_id)) {
            $nq = $option->quantity + $quantity;
            if ($this->db->update('product_variants', array('quantity' => $nq), array('id' => $option_id))) {
                return TRUE;
            }
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

    public function updateProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('warehouses_products_variants', array('quantity' => $nq), array('option_id' => $option_id, 'warehouse_id' => $warehouse_id))) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return TRUE;
            }
        } else {
            $nq = 0 - $quantity;
            if ($this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $nq))) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return TRUE;
            }
        }
        return FALSE;
    }

    public function addSale($data = array(), $items = array(), $payment = array(), $si_return = array())
    {
        //old
        // if (empty($si_return)) {
        //     //old $cost = $this->site->costing($items);
        //     // $this->sma->print_arrays($cost);
        // }

        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();
            if ($this->site->getReference('so') == $data['reference_no']) {
                $this->site->updateReference('so');
            }
	        if ($this->site->getReference('iso') == $data['reference_no']) {
		        $this->site->updateReference('iso');
	        }
            if ($this->site->getReference('re') == $data['return_sale_ref'])
            {
                $this->site->updateReference('re');
            }
            foreach ($items as $item) {
                    $enable_sale_point=0;
                if(isset($item['enable_sale_point']))
                {
                    $enable_sale_point=$item['enable_sale_point'];
                }
                $item['sale_id'] = $sale_id;
                unset($item['enable_sale_point']); 
                $this->db->insert('sale_items', $item);
                $item['enable_sale_point']=$enable_sale_point;
                $sale_item_id = $this->db->insert_id();

               /* if ($data['sale_status'] == 'completed' && empty($si_return)) {

                    $item_costs = $this->site->item_costing($item);
                    //print_r($item_costs);die;
                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date']) || isset($item_cost['pi_overselling'])) {
                            $item_cost['sale_item_id'] = $sale_item_id;
                            $item_cost['sale_id'] = $sale_id;
                            $item_cost['date'] = date('Y-m-d', strtotime($data['date']));
                            if(! isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                $ic['sale_item_id'] = $sale_item_id;
                                $ic['sale_id'] = $sale_id;
                                $ic['date'] = date('Y-m-d', strtotime($data['date']));
                                if(! isset($ic['pi_overselling'])) {
                                    $this->db->insert('costing', $ic);
                                }
                            }
                        }
                    }

                } */



            }
            //old
            // if ($data['sale_status'] == 'completed') {
            //   //old  $this->site->syncPurchaseItems($cost);
            // }

            if (!empty($si_return)) {
                //new
                $oitems=array();
                

                foreach ($si_return as $return_item) {
                    //old      
                    $product = $this->site->getProductByID($return_item['product_id']);
                    /*if ($product->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($return_item['product_id'], $return_item['warehouse_id']);
                        foreach ($combo_items as $combo_item) {
                            $this->UpdateCostingAndPurchaseItem($return_item['id'], $combo_item->id, ($return_item['quantity']*$combo_item->qty));
                        }
                    } else {
                        $this->UpdateCostingAndPurchaseItem($return_item['id'], $return_item['product_id'], $return_item['quantity']);
                    }*/
                     //old

                    $object =(object) (array(
                        'product_id' => $return_item['product_id'],
                        'option_id' => $return_item['option_id'],
                        'quantity' =>  (($return_item['quantity'])),
                        'warehouse_id' => $return_item['warehouse_id'],
                        'product_type' => $product->type
                     ));    

                    $oitems[]=$object;

                }
                //new 
                log_message('error',"return sale items ".json_encode($oitems));
              
                 $this->site->reverseSale($oitems);

                $this->db->update('sales', array('return_sale_ref' => $data['return_sale_ref'], 'surcharge' => $data['surcharge'],'return_sale_total' => $data['grand_total'], 'return_id' => $sale_id), array('id' => $data['sale_id']));
            }

            if ($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
                if (empty($payment['reference_no'])) {
                    $payment['reference_no'] = $this->site->getReference('pay');
                }
                $payment['sale_id'] = $sale_id;
                if ($payment['paid_by'] == 'gift_card') {
                    $this->db->update('gift_cards', array('balance' => $payment['gc_balance']), array('card_no' => $payment['cc_no']));
                    unset($payment['gc_balance']);
                    $this->db->insert('payments', $payment);
                } else {
                    if ($payment['paid_by'] == 'deposit') {
                        $customer = $this->site->getCompanyByID($data['customer_id']);
                        $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$payment['amount'])), array('id' => $customer->id));
                    }
                    $this->db->insert('payments', $payment);
                }
                if ($this->site->getReference('pay') == $payment['reference_no']) {
                    $this->site->updateReference('pay');
                }
                $this->site->syncSalePayments($sale_id);

            }
            if (empty($si_return)) {
               $this->site->syncQuantity($sale_id);
             }
            if (!empty($si_return)) {

                log_message('error',"Sale ID ".$sale_id." return");
                log_message('error',"Sale Items ".json_encode($items));
               // log_message('error',"Points Total ".$this->getPointsEligibleTotal($data['sale_id']));

           /*$this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by'],'return');*/
           $saledata=$this->getInvoiceByID($sale_id);
           if(!empty($saledata) && !empty($saledata->pos) )
            {
               $this->sma->update_award_points_by_products($items,$data['grand_total'],  $data['customer_id'],$data['created_by'], 'return',$sale_id);
            }
           

        }
            /*$this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
            return true;*/

            return $data;

        }

        return false;
    }

    public function updateSale($id, $data, $items = array())
    {
        // old $this->resetSaleActions($id, FALSE, TRUE);

        if ($data['sale_status'] == 'completed') {
            //$cost = $this->site->costing($items);
        }
        $oitems = $this->site->getAllSaleItems($id);    
        // $this->sma->print_arrays($cost);

        if ($this->db->update('sales', $data, array('id' => $id)) && 
            $this->db->delete('sale_items', array('sale_id' => $id)) && 
            $this->db->delete('costing', array('sale_id' => $id))) {

            foreach ($items as $item) {

                $item['sale_id'] = $id;
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();
               
               /* if ($data['sale_status'] == 'completed' && $this->site->getProductByID($item['product_id'])) {
                    $item_costs = $this->site->item_costing($item);
                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date']) || isset($item_cost['pi_overselling'])) {
                            $item_cost['sale_item_id'] = $sale_item_id;
                            $item_cost['sale_id'] = $id;
                            $item_cost['date'] = date('Y-m-d', strtotime($data['date']));
                            if(! isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                $ic['sale_item_id'] = $sale_item_id;
                                $ic['sale_id'] = $id;
                                $item_cost['date'] = date('Y-m-d', strtotime($data['date']));
                                if(! isset($ic['pi_overselling'])) {
                                    $this->db->insert('costing', $ic);
                                }
                            }
                        }
                    }
                } */

            }

            if ($data['sale_status'] == 'completed') {
                //old $this->site->syncPurchaseItems($cost);
            }

            $this->site->syncSalePayments($id);

            $this->site->reverseSale($oitems);
            $this->site->syncQuantity($id);
            $sale = $this->getInvoiceByID($id);
           

            $this->sma->update_award_points_by_products($items,$data['grand_total'],  $data['customer_id'],$sale->created_by, NULL,$id);
            return true;

        }
        return false;
    }

    public function updateStatus($id, $status, $note)
    {

        $sale = $this->getInvoiceByID($id);
        $items = $this->getAllInvoiceItems($id);
        $cost = array();
        if ($status == 'completed' && $status != $sale->sale_status) {
            foreach ($items as $item) {
                $items_array[] = (array) $item;
            }
            $cost = $this->site->costing($items_array);
        }

        if ($this->db->update('sales', array('sale_status' => $status, 'note' => $note), array('id' => $id))) {

            if ($status == 'completed' && $status != $sale->sale_status) {

                foreach ($items as $item) {
                    $item = (array) $item;
                    if ($this->site->getProductByID($item['product_id'])) {
                        $item_costs = $this->site->item_costing($item);
                        foreach ($item_costs as $item_cost) {
                            $item_cost['sale_item_id'] = $item['id'];
                            $item_cost['sale_id'] = $id;
                            $item_cost['date'] = date('Y-m-d', strtotime($sale->date));
                            if(! isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        }
                    }
                }

            } elseif ($status != 'completed' && $sale->sale_status == 'completed') {
                $this->resetSaleActions($id);
            }

            if (!empty($cost)) { $this->site->syncPurchaseItems($cost); }
            return true;
        }
        return false;
    }

    public function deleteSale($id)
    {
        //old
       // $sale_items = $this->resetSaleActions($id);
        
        
        //new
        $sale_items = $this->site->getAllSaleItems($id);
        //new check return items 
        $return_sale_items = $this->site->getAllReturnSaleItems($id);
        if(!empty($return_sale_items))
        {
            $this->site->reverseSale($return_sale_items);
        }    
        //new

        $sale = $this->getInvoiceByID($id);
        if ($this->db->delete('sale_items', array('sale_id' => $id)) &&
        $this->db->delete('sales', array('id' => $id)) &&
        $this->db->delete('costing', array('sale_id' => $id))) {
            $this->db->delete('sales', array('sale_id' => $id));
            $this->db->delete('payments', array('sale_id' => $id));
         //   $credit = $this->getCreditDataByReference($sale->reference_no);
           // if(!empty($credit))
               // $this->db->delete('credit',array('ret_refrence_no' =>$sale->reference_no ));

            //yncQuantity($sale_id = NULL, $purchase_id = NULL, $oitems = NULL, $product_id = NULL

        //,$is_sale_r=NULL,$is_pur_r=NULL) 
           // $this->site->syncQuantity(NULL, NULL, $sale_items,NULL,TRUE,NULL);
            $this->site->reverseSale($sale_items);
            return true;
        }
        return FALSE;
    }

    public function resetSaleActions($id, $return_id = NULL, $check_return = NULL)
    {
        if ($sale = $this->getInvoiceByID($id)) {
            if ($check_return && $sale->sale_status == 'returned') {
                $this->session->set_flashdata('warning', lang('sale_x_action'));
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
            }

            if ($sale->sale_status == 'completed') {
                if ($costings = $this->getSaleCosting($id)) {
                    foreach ($costings as $costing) {
                        $pi = $this->getPurchaseItemByID($costing->purchase_item_id);
                        $this->site->setPurchaseItem(['id' => $pi->id, 'product_id' => $pi->product_id, 'option_id' => $pi->option_id], $costing->quantity);
                    }
                }
                $items = $this->getAllInvoiceItems($id);
                $this->site->syncQuantity(NULL, NULL, $items);
                //$this->sma->update_award_points($sale->grand_total, $sale->customer_id, $sale->created_by, TRUE);
                return $items;
            }
        }
    }

    public function getPurchaseItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
public function getLatestPurchaseItemByProductId($product_id)
{
	$this->db->order_by('id', 'desc');
        $q = $this->db->get_where('purchase_items', array('product_id' => $product_id),1);
        if ($q->num_rows() > 0) {
           return $q->row();
           
        }
        return FALSE;
}
    public function getCostingLines($sale_item_id, $product_id, $sale_id = NULL)
    {
        if ($sale_id) { $this->db->where('sale_id', $sale_id); }
        $orderby = ($this->Settings->accounting_method == 1) ? 'asc' : 'desc';
        $this->db->order_by('id', $orderby);
        $q = $this->db->get_where('costing', array('sale_item_id' => $sale_item_id, 'product_id' => $product_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getSaleItemByID($id)
    {
        $q = $this->db->get_where('sale_items', array('id' => $id), 1);
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

    public function addDelivery($data = array())
    {
        if ($this->db->insert('deliveries', $data)) {
            if ($this->site->getReference('do') == $data['do_reference_no']) {
                $this->site->updateReference('do');
            }
            return true;
        }
        return false;
    }

    public function updateDelivery($id, $data = array())
    {
        if ($this->db->update('deliveries', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function getDeliveryByID($id)
    {
        $q = $this->db->get_where('deliveries', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getDeliveryBySaleID($sale_id)
    {
        $q = $this->db->get_where('deliveries', array('sale_id' => $sale_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteDelivery($id)
    {
        if ($this->db->delete('deliveries', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getInvoicePayments($sale_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
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

    public function getPaymentsForSale($sale_id)
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount, payments.cc_no, payments.cheque_no, payments.reference_no, users.first_name, users.last_name, type')
            ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function addPayment($data = array(), $customer_id = null, $return_payment=NULL,$credit=NULL)
    {
        if ($this->db->insert('payments', $data)) {
            if ($this->site->getReference('pay') == $data['reference_no']) {
                $this->site->updateReference('pay');
            }
            if(!empty($return_payment) && !empty($credit))
            {
                foreach($return_payment as $payment)
                {
                    $this->db->insert('payments', $payment);
                }

                foreach($credit as $value)
                {
                    $this->db->update('credit', array('ret_amount'=>$value['ret_amount'],'payment_status'=>$value['payment_status']),array('ret_refrence_no' => $value['ret_refrence_no']));
                }
            }
            $this->site->syncSalePayments($data['sale_id']);
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$data['amount'])), array('id' => $customer_id));
            }
            return true;
        }
        return false;
    }

    public function updatePayment($id, $data = array(), $customer_id = null)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->update('payments', $data, array('id' => $id))) {
            $this->site->syncSalePayments($data['sale_id']);
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance+$opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit') {
                if (!$customer_id) {
                    $sale = $this->getInvoiceByID($opay->sale_id);
                    $customer_id = $sale->customer_id;
                }
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount+$opay->amount)), array('id' => $customer->id));
            }
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$data['amount'])), array('id' => $customer_id));
            }
            return true;
        }
        return false;
    }

    public function deletePayment($id)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->delete('payments', array('id' => $id))) {
            $this->site->syncSalePayments($opay->sale_id);
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance+$opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit') {
                $sale = $this->getInvoiceByID($opay->sale_id);
                $customer = $this->site->getCompanyByID($sale->customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount+$opay->amount)), array('id' => $customer->id));
            }
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

    /* ----------------- Gift Cards --------------------- */

    public function addGiftCard($data = array(), $ca_data = array(), $sa_data = array())
    {
        if ($this->db->insert('gift_cards', $data)) {
            if (!empty($ca_data)) {
                $this->db->update('companies', array('award_points' => $ca_data['points']), array('id' => $ca_data['customer']));
            } elseif (!empty($sa_data)) {
                $this->db->update('users', array('award_points' => $sa_data['points']), array('id' => $sa_data['user']));
            }
            return true;
        }
        return false;
    }

    public function updateGiftCard($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('gift_cards', $data)) {
            return true;
        }
        return false;
    }

    public function deleteGiftCard($id)
    {
        if ($this->db->delete('gift_cards', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getPaypalSettings()
    {
        $q = $this->db->get_where('paypal', array('id' => 1));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getSkrillSettings()
    {
        $q = $this->db->get_where('skrill', array('id' => 1));
        if ($q->num_rows() > 0) {
            return $q->row();
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

    public function getStaff()
    {
        if (!$this->Owner) {
            $this->db->where('group_id !=', 1);
        }
        $this->db->where('group_id !=', 3)->where('group_id !=', 4);
        $q = $this->db->get('users');
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

    public function getTaxRateByName($name)
    {
        $q = $this->db->get_where('tax_rates', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function topupGiftCard($data = array(), $card_data = NULL)
    {
        if ($this->db->insert('gift_card_topups', $data)) {
            $this->db->update('gift_cards', $card_data, array('id' => $data['card_id']));
            return true;
        }
        return false;
    }

    public function getAllGCTopups($card_id)
    {
        $this->db->select("{$this->db->dbprefix('gift_card_topups')}.*, {$this->db->dbprefix('users')}.first_name, {$this->db->dbprefix('users')}.last_name, {$this->db->dbprefix('users')}.email")
        ->join('users', 'users.id=gift_card_topups.created_by', 'left')
        ->order_by('id', 'desc')->limit(10);
        $q = $this->db->get_where('gift_card_topups', array('card_id' => $card_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getItemRack($product_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            $wh = $q->row();
            return $wh->rack;
        }
        return FALSE;
    }

    public function getSaleCosting($sale_id)
    {
        $q = $this->db->get_where('costing', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function UpdateCostingAndPurchaseItem($item_id, $product_id, $quantity)
    {
        $bln_quantity = $quantity;
        if ($costings = $this->getCostingLines($item_id, $product_id)) {
            foreach ($costings as $costing) {

                if ($quantity != 0) {
                    $pi = $this->getPurchaseItemByID($costing->purchase_item_id);
                     if(!$pi){
                     	$pi = $this->getLatestPurchaseItemByProductId($product_id);
                     }
                    if ($pi->quantity_received >= $quantity) {
                        $this->site->setPurchaseItem(['id' => $pi->id, 'product_id' => $pi->product_id, 'option_id' => $pi->option_id], $quantity);
                        $quantity = 0;
                    } else {
                        $this->site->setPurchaseItem(['id' => $pi->id, 'product_id' => $pi->product_id, 'option_id' => $pi->option_id], $pi->quantity_received);
                        $quantity = ($quantity-$pi->quantity_received);
                    }
					
                }

                if ($costing->quantity > $bln_quantity && $bln_quantity != 0) {
                    $qty = $costing->quantity - $bln_quantity;
                    $bln = $costing->quantity_balance && $costing->quantity_balance >= $bln_quantity ? $costing->quantity_balance - $bln_quantity : 0;
                    $this->db->update('costing', array('quantity' => $qty, 'quantity_balance' => $bln), array('id' => $costing->id));
                    $bln_quantity = 0;
                } elseif ($costing->quantity <= $bln_quantity && $bln_quantity != 0) {
                    $this->db->delete('costing', array('id' => $costing->id));
                    $bln_quantity = ($bln_quantity - $costing->quantity);
                }

                if ($quantity == 0 && $bln_quantity == 0) { break; }
            }
        }

    }
     public function getTaxdata($id,$is_chg_state=NULL){
        
        if(empty($is_chg_state))
        {
            $this->db->select(' (SUM(subtotal)-SUM(sgst_amt)-SUM(cgst_amt))  as item_tax,cgst_rate,sgst_rate,igst_rate,cess_rate,SUM(cgst_amt) as cgst_total,SUM(sgst_amt) as sgst_total,SUM(igst_amt) as igst_total,SUM(cess_amt) as cess_total,')
            ->from('sale_items')
            ->group_by('cgst_rate')
            ->where('sale_id',$id);

        }
        else
        {

            $this->db->select('(SUM(subtotal)-SUM(igst_amt)) as item_tax,cgst_rate,sgst_rate,igst_rate,cess_rate,SUM(cgst_amt) as cgst_total,SUM(sgst_amt) as sgst_total,SUM(igst_amt) as igst_total,SUM(cess_amt) as cess_total,')
            ->from('sale_items')
            ->group_by('igst_rate')
            ->where('sale_id',$id);
        }
        
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }


    public function getCessTaxdata($id){
        $this->db->select('cess_rate,SUM(cess_amt) as cess_total')
            ->from('sale_items')
            ->group_by('cess_rate')
            ->where('cess_rate >',0)
            ->where('sale_id',$id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    //For internal purchase order sale
    public function getInternalPOByID($id)
    {
        $q = $this->db->get_where('internal_purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllInternalPOItems($purchase_id)
    {
        $q = $this->db->get_where('internal_purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    //Get product data of internal purchase order
     public function getProductData($purchase_id, $warehouse_id)
    {
        $wp = "( SELECT product_id, (quantity - quantity_received) as quantity from {$this->db->dbprefix('internal_purchase_items')} WHERE (quantity - quantity_received) > 0 AND purchase_id=".$purchase_id." ) FWP";

        $this->db->select('products.*,products.quantity as stock, FWP.quantity as quantity, categories.id as category_id, categories.name as category_name', FALSE)
            ->join($wp, 'FWP.product_id=products.id')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->group_by('products.id');
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getCustomerByBranchCode($code)
    {
         $q = $this->db->get_where('companies', array('membership_id' => $code,'customer_group_name' => 'SMSubstores'),1);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPurchaseQuantity($purchase_id,$product_id)
    {
        $q = $this->db->get_where('internal_purchase_items', array('purchase_id' => $purchase_id,'product_id' => $product_id),1);
        if ($q->num_rows() > 0) {
           return $q->row();
        }
        return FALSE;
    }

    public function getCreditDataByCustomer($customer_id)
    {
        $this->db->select('*');
        $this->db->from('credit');
        $this->db->where('customer_id',$customer_id);
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
        $this->db->select('sum(ret_amount) as ret_amount');
        $this->db->from('credit');
        $this->db->where_in('id',$id);
        $q=$this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getCreditDataByID($credit_id) {
        $q = $this->db->get_where('credit',array('id'=>$credit_id),1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getReturnSaleByReference($reference_no)
    {
        $q = $this->db->get_where('sales',array('return_sale_ref'=>$reference_no),1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getCreditDataByReference($reference_no)
    {
        $q = $this->db->get_where('credit',array('ret_refrence_no'=>$reference_no),1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

     public function getAllReturnSaleIdByRefrence($reference_no) { 
        $this->db->select('id', False);
        $this->db->where('reference_no', $reference_no);
        $this->db->where('sale_status', 'returned');
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row->id;
            }
            return $data;
        }
        return FALSE;
    }

    public function getRemQuantityAfterReturn($product_id, $warehouse_id = NULL,$returnSaleId) {
        $this->db->select('SUM(COALESCE(quantity, 0)) as stock', False);
        $this->db->where('product_id', $product_id)->where('quantity !=', 0);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if ($returnSaleId) {
            $this->db->where_in('sale_id', $returnSaleId);
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }

     public function getAllReturnItems($sale_id)
    {
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant,products.mrp')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->join('sales', 'sales.id=sale_items.sale_id', 'left')
            ->group_by('sale_items.id')
            ->order_by('id', 'asc');
        if ($sale_id ) {
            $this->db->where('sales.sale_id', $sale_id);
        } 
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllReteurnSaleList($sale_id)
    {
        $q = $this->db->get_where('sales', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPointsEligibleTotal($sale_id)
    {
        $this->db->select('SUM(sale_items.subtotal)');
        $this->db->join('products', 'products.id=sale_items.product_id', 'left')
        ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
        ->group_by('sale_items.id');
        $this->db->where('products', array('enable_sale_point' => 1));
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('sale_items', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }



         //cancel sale
     public function getCancelSaleByID($id)
    {
        $q = $this->db->get_where('cancel_sale', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

     public function getAllCancelSaleItems($sale_id)
    {
       
            $this->db->select('cancel_sale_items.*');
            
     
        
        $q = $this->db->get_where('cancel_sale_items', array('cancel_sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }


  public function search($term)
    {
        $this->db->select($this->db->dbprefix('sales') . '.id, customer,reference_no,biller,grand_total,sale_status,payment_status,paid,(grand_total-paid) as balance,DATE_FORMAT(date

            ,"%Y-%m-%d %T") as date ')


        //    ->where(" reference_no like '%".$term."%'"
            ->where(" reference_no like '%".$term."%'"
                 
                 );
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

}
