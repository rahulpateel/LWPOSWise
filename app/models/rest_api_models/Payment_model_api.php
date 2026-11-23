<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_model_api extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    //get all payments against booking id
    public function getSalePayments($sale_id) {
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

}