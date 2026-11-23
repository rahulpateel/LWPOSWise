<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Internal_purchases_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getProductNames($term, $limit = 5) //1
    {
        $this->db->where("type = 'standard' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
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

    public function getProductByCode($code) //1
    {
        $q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllPurchaseItems($purchase_id)  //1
    {
        $this->db->select('internal_purchase_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=internal_purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=internal_purchase_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=internal_purchase_items.tax_rate_id', 'left')
            ->group_by('internal_purchase_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('internal_purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPurchaseByID($id)  //1
    {
        $q = $this->db->get_where('internal_purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductOptionByID($id) //1
    {
        $q = $this->db->get_where('product_variants', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }


    public function addPurchase($data, $items)
    {

        if ($this->db->insert('internal_purchases', $data)) {
            $purchase_id = $this->db->insert_id();
            if ($this->site->getReference('ipr') == $data['reference_no']) {
                $this->site->updateReference('ipr');
            }
            foreach ($items as $item) {
                $item['purchase_id'] = $purchase_id;
                $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : NULL;
                $this->db->insert('internal_purchase_items', $item);
                if ($this->Settings->update_cost) {
                    $this->db->update('products', array('cost' => $item['real_unit_cost']), array('id' => $item['product_id']));
                }
                if($item['option_id']) {
                    $this->db->update('product_variants', array('cost' => $item['real_unit_cost']), array('id' => $item['option_id'], 'product_id' => $item['product_id']));
                }
            }
            return true;
        }
        return false;
    }

    public function updatePurchase($id, $data, $items = array())
    {
        $opurchase = $this->getPurchaseByID($id);
        $oitems = $this->getAllPurchaseItems($id);
        if ($this->db->update('internal_purchases', $data, array('id' => $id)) && $this->db->delete('internal_purchase_items', array('purchase_id' => $id))) {
            $purchase_id = $id;
            foreach ($items as $item) {
                $item['purchase_id'] = $id;
                $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : NULL;
                $this->db->insert('internal_purchase_items', $item);
            }
            return true;
        }

        return false;
    }

    public function deletePurchase($id) //1
    {
        $purchase = $this->getPurchaseByID($id);
        $purchase_items = $this->getAllinternalPurchaseItems($id);
        if ($this->db->delete('internal_purchase_items', array('purchase_id' => $id)) && $this->db->delete('internal_purchases', array('id' => $id))) {
            $data = array(
                'return_id' => '',
                'return_purchase_ref' => '',
                'return_purchase_total' => '0',

            );

           $this->db->update('internal_purchases', $data, array('return_id' => $id));
            return true;
        }
        return FALSE;
    }


    public function getPaymentsForPurchase($purchase_id) //1
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount, payments.reference_no, users.first_name, users.last_name, type')
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


    public function getProductOptions($product_id) //1
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


    public function getQuoteByID($id) //1
    {
        $q = $this->db->get_where('quotes', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    //New funcations
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

    public function getProductNamesByBrand($brand)
    {
        $this->db->where(array('type' =>'standard','brand'=>$brand));
        //$this->db->where('alert_quantity > quantity', NULL);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getBranchByCode($branch_code)
    {
        $q = $this->db->get_where("companies", array('membership_id' => $branch_code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllinternalPurchaseItems($purchase_id) {
        $q = $this->db->get_where('internal_purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

}