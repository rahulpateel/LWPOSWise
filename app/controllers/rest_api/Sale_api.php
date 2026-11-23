<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Sale_api extends MY_Controller
{
    function __construct()
    {
        parent:: __construct();
        $this->load->model('rest_api_models/authenticateUser_model');
        $this->load->model('rest_api_models/user_api_model');
        $this->load->model('rest_api_models/functions_model');

    }

    //add sale
    public function add()
    {

        $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getWarehouseSo($this->input->post('warehouse'));

        print_r($reference);die();



        $data = array('date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'staff_note' => $staff_note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $this->input->post('order_discount'),
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $this->input->post('order_tax'),
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'total_items' => $total_items,
                'sale_status' => $sale_status,
                'payment_status' => $payment_status,
                'payment_term' => $payment_term,
                'due_date' => $due_date,
                'paid' => 0,
                'created_by' => $this->session->userdata('user_id'),
                'hash' => hash('sha256', microtime() . mt_rand()),
                'vehicle_no' => $vehicleno,
            );


        $payment = array(
            'date' => $date,
            'reference_no' => $this->input->post('payment_reference_no'),
            'amount' => $this->sma->formatDecimal($amount_paying),
            'paid_by' => $this->input->post('paid_by'),
            'cheque_no' => $this->input->post('cheque_no'),
            'cc_no' => $this->input->post('gift_card_no'),
            'cc_holder' => $this->input->post('pcc_holder'),
            'cc_month' => $this->input->post('pcc_month'),
            'cc_year' => $this->input->post('pcc_year'),
            'cc_type' => $this->input->post('pcc_type'),
            'created_by' => $this->session->userdata('user_id'),
            'note' => $this->input->post('payment_note'),
            'type' => 'received',
            'gc_balance' => $gc_balance,
        );


        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment)) {
            print_r("data is ". $data);die;
        } else {

        }
    }

}
