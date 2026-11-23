<?php defined('BASEPATH') or exit('No direct script access allowed');

class Purchases extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Customer) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
         $this->load->admin_model('pos_model');
        $this->lang->admin_load('purchases', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('purchases_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
         $this->pos_settings = $this->pos_model->getSetting();

    }

    /* ------------------------------------------------------------------------- */

    public function index($warehouse_id = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('purchases')));
        $meta = array('page_title' => lang('purchases'), 'bc' => $bc);
        $this->page_construct('purchases/index', $meta, $this->data);

    }

    public function getPurchases($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
		 $list_link = anchor('admin/purchases/plist/$1', '<i class="fa fa-money"></i> ' . lang('List'), 'data-toggle="modal" data-target="#myModal"');
        $detail_link = anchor('admin/purchases/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('purchase_details'));
        $payments_link = anchor('admin/purchases/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('admin/purchases/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('admin/purchases/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('admin/purchases/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase'));
        $pdf_link = anchor('admin/purchases/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode = anchor('admin/products/print_barcodes/?purchase=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
				$print_signages = anchor('admin/products/print_signages/?pur_id=$1', '<i class="fa fa-print"></i> ' . lang('print_signages'),"target=_blank");
        $return_link = anchor('admin/purchases/return_purchase/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_purchase'));
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_purchase") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('purchases/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_purchase') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $list_link . '</li>
            <li>' . $detail_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
      
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $print_signages . '</li>
            <li>' . $print_barcode . '</li>
            <li>' . $return_link . '</li>
           
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';
if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no,ext_pur_ref_no,supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status")
                ->from('purchases')
                ->where('status !=', 'returned')
                ->where('status !=', 'ordered')
                ->where('status !=', 'Canceled')
                ->where('warehouse_id', $warehouse_id);
                if ($start_date && $end_date) {
                $this->datatables->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
        } else {
            $this->datatables
                ->select("id,DATE_FORMAT(date, '%Y-%m-%d %T') as date,reference_no,ext_pur_ref_no,supplier,status,grand_total,paid,(grand_total-paid) as balance,payment_status")
                ->from('purchases')
                ->where('status !=', 'returned')
                ->where('status !=', 'Canceled')
                ->where('status !=', 'ordered'); 
                if ($start_date && $end_date) {
                $this->datatables->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }               
        }
        // $this->datatables->where('status !=', 'returned');
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    /* ----------------------------------------------------------------------------- */

    public function modal_view($purchase_id = null)
    {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllReturnedItems($purchase_id) : NULL;
        $this->data['biller'] = $this->site->getCompanyByID($this->Settings->default_biller);
        $this->data['return_ids'] = $this->purchases_model->getAllReteurnPurchaseList($purchase_id);
        
        $this->load->view($this->theme . 'purchases/modal_view', $this->data);

    }
	
	

    public function view($purchase_id = null)
    {
        $this->sma->checkPermissions('index');

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }

        $this->data['dn_id']=NULL;
        $this->data['dn_paid']=false;

        if($inv->status=='returned')
        {
            $dn=$this->purchases_model->getDebitNoteByRef($inv->return_purchase_ref);
            $dn_paid=false;
            $dn_id=0;
            if($dn)
            {
                $dn_paid=$this->purchases_model->checkDebitPaymentByRef($inv->return_purchase_ref,$dn->id);
                $dn_id=$dn->id;
            }
            $this->data['dn_id']=$dn_id;
            $this->data['dn_paid']=$dn_paid;

        }
        $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' => lang('view_purchase_details'), 'bc' => $bc);
        $this->page_construct('purchases/view', $meta, $this->data);

    }

    /* ----------------------------------------------------------------------------- */

//generate pdf and force to download

    public function pdf($purchase_id = null, $view = null, $save_bufffer = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['inv'] = $inv;
	    $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
	    $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllReturnedItems($purchase_id) : NULL;
        $this->data['biller'] = $this->site->getCompanyByID($this->Settings->default_biller);
        $name = $this->lang->line("purchase") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'purchases/pdf', $this->data, true);
        if (! $this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'purchases/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer, $this->data['supplier']->invoice_footer);
        }

        else {
            //echo $html;
            $this->sma->generate_pdf($html, $name, false, $this->data['supplier']->invoice_footer,$margin_bottom = null, $header = null, $margin_top = null, $orientation = 'P');
        }

    }

    public function combine_pdf($purchases_id)
    {
        $this->sma->checkPermissions('pdf');

        foreach ($purchases_id as $purchase_id) {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->purchases_model->getPurchaseByID($purchase_id);
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
            $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['created_by'] = $this->site->getUser($inv->created_by);
            $this->data['inv'] = $inv;
            $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
            $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;
            $inv_html = $this->load->view($this->theme . 'purchases/pdf', $this->data, true);
            if (! $this->Settings->barcode_img) {
                $inv_html = preg_replace("'\<\?xml(.*)\?\>'", '', $inv_html);
            }
            $html[] = array(
                'content' => $inv_html,
                'footer' => '',
            );
        }

        $name = lang("purchases") . ".pdf";
        $this->sma->generate_pdf($html, $name);

    }

    public function email($purchase_id = null)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        $this->form_validation->set_rules('to', $this->lang->line("to") . " " . $this->lang->line("email"), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', $this->lang->line("subject"), 'trim|required');
        $this->form_validation->set_rules('cc', $this->lang->line("cc"), 'trim|valid_emails');
        $this->form_validation->set_rules('bcc', $this->lang->line("bcc"), 'trim|valid_emails');
        $this->form_validation->set_rules('note', $this->lang->line("message"), 'trim');

        if ($this->form_validation->run() == true) {
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $to = $this->input->post('to');
            $subject = $this->input->post('subject');
            if ($this->input->post('cc')) {
                $cc = $this->input->post('cc');
            } else {
                $cc = null;
            }
            if ($this->input->post('bcc')) {
                $bcc = $this->input->post('bcc');
            } else {
                $bcc = null;
            }
            $supplier = $this->site->getCompanyByID($inv->supplier_id);
            $this->load->library('parser');
            $parse_data = array(
                'reference_number' => $inv->reference_no,
                'contact_person' => $supplier->name,
                'company' => $supplier->company,
                'site_link' => base_url(),
                'site_name' => $this->Settings->site_name,
                'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>',
            );
            $msg = $this->input->post('note');
            $message = $this->parser->parse_string($msg, $parse_data);
            $attachment = $this->pdf($purchase_id, null, 'S');
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sma->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
            delete_files($attachment);
            $this->db->update('purchases', array('status' => 'ordered'), array('id' => $purchase_id));
            $this->session->set_flashdata('message', $this->lang->line("email_sent"));
            admin_redirect("purchases");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            if (file_exists('./themes/' . $this->Settings->theme . '/admin/views/email_templates/purchase.html')) {
                $purchase_temp = file_get_contents('themes/' . $this->Settings->theme . '/admin/views/email_templates/purchase.html');
            } else {
                $purchase_temp = file_get_contents('./themes/default/admin/views/email_templates/purchase.html');
            }
            $this->data['subject'] = array('name' => 'subject',
                'id' => 'subject',
                'type' => 'text',
                'value' => $this->form_validation->set_value('subject', lang('purchase_order').' (' . $inv->reference_no . ') '.lang('from').' ' . $this->Settings->site_name),
            );
            $this->data['note'] = array('name' => 'note',
                'id' => 'note',
                'type' => 'text',
                'value' => $this->form_validation->set_value('note', $purchase_temp),
            );
            $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);

            $this->data['id'] = $purchase_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'purchases/email', $this->data);

        }
    }

    /* -------------------------------------------------------------------------------------------------------------------------------- */

    public function add($quote_id = null)
    {
        $this->sma->checkPermissions();

        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        //$this->form_validation->set_rules('reference_no', $this->lang->line("ref_no"), 'required');
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');

        $this->session->unset_userdata('csrf_token');

       // $this->sma->print_arrays($_POST);
        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            $supplier_state = $this->input->post('supplier_state');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $payment_term = $this->input->post('payment_term');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            if(!empty($this->input->post('add_prod')))
                $add_prod = explode(',', $this->input->post('add_prod'));
            else
                $add_prod = NULL;

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $cgstamt = 0; $sgstamt = 0;  $igstamt = 0;
            $cgstrate = 0; $sgstrate = 0; $igstrate = 0;
            $cessrate = 0; $cessamt = 0;

            $i = sizeof($_POST['product']);
            for ($r = 0; $r < $i; $r++) {
                $item_code = $_POST['product'][$r];
                $item_net_cost = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                /*$item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;*/
                $item_tax_rate = $_POST['gst_rate'][$r];
                $gst_code = $_POST['gst_code'][$r];
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry <= $today) {
                            $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }
                    // $unit_cost = $real_unit_cost;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount);
                        }
                    }

                    $unit_cost = $this->sma->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost = $unit_cost;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = 0;
                    $cessrate = $_POST['cess_rate'][$r];
                    if($cessrate!='0.00') {
                        //$pr_item_tax += $cessrate;
                        $item_tax_rate += $cessrate;
                    }

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {


                        $ctax = $this->site->calculateTax($product_details, $item_tax_rate, $unit_cost);
                        $item_tax = $ctax['amount'];
                        $tax = $ctax['tax'];

                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

                    }
                    if($cessrate!='0.00') {

                        $tax -= $cessrate;
                    }
                    if($tax != 0){

                        if($this->Settings->state == $supplier_state) {
                            $cgstrate = $tax / 2;
                            $sgstrate = $cgstrate;
                            $igstrate = 0;

                        }else{

                           $igstrate =  $tax;

                            $sgstrate = $cgstrate = 0;
                        }
                        $cgstamt =  $_POST['cgst_amount'][$r];
                        $sgstamt = $_POST['sgst_amount'][$r];
                        $igstamt = $_POST['igst_amount'][$r];
                        $cessamt = $_POST['cess_amount'][$r];

                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    $products[] = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $item_quantity,
                        'quantity_received' => $item_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        'date' => date('Y-m-d', strtotime($date)),
                        'status' => $status,
                        'supplier_part_no' => $supplier_part_no,
                        'gst_code' => $gst_code,
                        'cgst_rate'=> $cgstrate,
                        'sgst_rate'=> $sgstrate,
                        'igst_rate'=> $igstrate,
                        'cgst_amt' => $cgstamt,
                        'sgst_amt'=> $sgstamt,
                        'igst_amt'=> $igstamt,
                        'cess_rate'=> $cessrate,
                        'cess_amt'=> $cessamt

                    );


                    $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }


            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);

                } else {
                    $order_discount = $this->sma->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);

            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            $data = array('reference_no' => $reference,
                'date' => $date,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'status' => $status,
                'created_by' => $this->session->userdata('user_id'),
                'payment_term' => $payment_term,
                'due_date' => $due_date,

            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);
        }
        //print_r($add_prod);die;
        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products,$add_prod)) {         
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line("purchase_added"));
            admin_redirect('purchases');
        } else {

            if ($quote_id) {
                $this->data['quote'] = $this->purchases_model->getQuoteByID($quote_id);
                $supplier_id = $this->data['quote']->supplier_id;
                $items = $this->purchases_model->getAllQuoteItems($quote_id);
                krsort($items);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    if ($row->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($row->id, $item->warehouse_id);
                        foreach ($combo_items as $citem) {
                            $crow = $this->site->getProductByID($citem->id);
                            if (!$crow) {
                                $crow = json_decode('{}');
                                $crow->qty = $item->quantity;
                            } else {
                                unset($crow->details, $crow->product_details, $crow->price);
                                $crow->qty = $citem->qty*$item->quantity;
                            }
                            $crow->base_quantity = $item->quantity;
                            $crow->base_unit = $crow->unit ? $crow->unit : $item->product_unit_id;
                            $crow->base_unit_cost = $crow->cost ? $crow->cost : $item->unit_cost;
                            $crow->unit = $item->product_unit_id;
                            $crow->discount = $item->discount ? $item->discount : '0';
                            $supplier_cost = $supplier_id ? $this->getSupplierCost($supplier_id, $crow) : $crow->cost;
                            $crow->cost = $supplier_cost ? $supplier_cost : 0;
                            $crow->tax_rate = $item->tax_rate_id;
                            $crow->real_unit_cost = $crow->cost ? $crow->cost : 0;
                            $crow->expiry = '';
                            $options = $this->purchases_model->getProductOptions($crow->id);
                            $units = $this->site->getUnitsByBUID($row->base_unit);
                            $tax_rate = $this->site->getTaxRateByID($crow->tax_rate);
                            $ri = $this->Settings->item_addition ? $crow->id : $c;

                            $pr[$ri] = array('id' => $c, 'item_id' => $crow->id, 'label' => $crow->name . " (" . $crow->code . ")", 'row' => $crow, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                            $c++;
                        }
                    } elseif ($row->type == 'standard') {
                        if (!$row) {
                            $row = json_decode('{}');
                            $row->quantity = 0;
                        } else {
                            unset($row->details, $row->product_details);
                        }

                        $row->id = $item->product_id;
                        $row->code = $item->product_code;
                        $row->name = $item->product_name;
                        $row->base_quantity = $item->quantity;
                        $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                        $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                        $row->unit = $item->product_unit_id;
                        $row->qty = $item->unit_quantity;
                        $row->option = $item->option_id;
                        $row->discount = $item->discount ? $item->discount : '0';
                        $supplier_cost = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                        $row->cost = $supplier_cost ? $supplier_cost : 0;
                        $row->tax_rate = $item->tax_rate_id;
                        $row->expiry = '';
                        $row->real_unit_cost = $row->cost ? $row->cost : 0;
                        $options = $this->purchases_model->getProductOptions($row->id);

                        $units = $this->site->getUnitsByBUID($row->base_unit);
                        $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                        $ri = $this->Settings->item_addition ? $row->id : $c;

                        $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                            'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                        $c++;
                    }
                }
                $this->data['quote_items'] = json_encode($pr);
            }

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id'] = $quote_id;
            $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['ponumber'] = ''; //$this->site->getReference('po');
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $this->pos_settings = $this->pos_model->getSetting();
            $this->data['pos_settings'] = $this->pos_settings;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('add_purchase')));
            $meta = array('page_title' => lang('add_purchase'), 'bc' => $bc);

            $this->page_construct('purchases/add', $meta, $this->data);

        }
    }

    /* ------------------------------------------------------------------------------------- */

    public function edit($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->purchases_model->getPurchaseByID($id);
        if ($inv->status == 'returned' || $inv->return_id || $inv->return_purchase_ref) {
            $this->session->set_flashdata('error', lang('purchase_x_action'));
            admin_redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
        }
        if (!$this->session->userdata('edit_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('reference_no', $this->lang->line("ref_no"), 'required');
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');

        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no');
            $ext_pur_ref_no = $this->input->post('ext_pur_ref_no');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $payment_term = $this->input->post('payment_term');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $supplier_state = $this->input->post('supplier_state');

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $cgstamt = 0; $sgstamt = 0;  $igstamt = 0;
            $cgstrate = 0; $sgstrate = 0; $igstrate = 0;
            $cessrate = 0; $cessamt = 0;

            $partial = false;
            $i = sizeof($_POST['product']);
            for ($r = 0; $r < $i; $r++) {
                $item_code = $_POST['product'][$r];
                $item_net_cost = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $quantity_received = $_POST['received_base_quantity'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $quantity_balance = $_POST['quantity_balance'][$r];
                $ordered_quantity = $_POST['ordered_quantity'][$r];
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
                $item_tax_rate = $_POST['gst_rate'][$r];
                $gst_code = $_POST['gst_code'][$r];

                if ($status == 'received' || $status == 'partial') {
                    if ($quantity_received < $item_quantity) {
                        $partial = 'partial';
                    } elseif ($quantity_received > $item_quantity) {
                        $this->session->set_flashdata('error', lang("received_more_than_ordered"));
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $balance_qty =  $quantity_received - ($ordered_quantity - $quantity_balance);
                } else {
                    $balance_qty = $item_quantity;
                    $quantity_received = $item_quantity;
                }
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity) && isset($quantity_balance)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    // $unit_cost = $real_unit_cost;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount);
                        }
                    }

                    $unit_cost = $this->sma->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost = $unit_cost;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = "";
                    $cessrate = $_POST['cess_rate'][$r];
                    if($cessrate!='0.00') {
                        //$pr_item_tax += $cessrate;
                        $item_tax_rate += $cessrate;
                    }
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {

                        $ctax = $this->site->calculateTax($product_details, $item_tax_rate, $unit_cost);
                        $item_tax = $ctax['amount'];
                        $tax = $ctax['tax'];

                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

                    }
                    if($cessrate!='0.00') {

                        $tax -= $cessrate;
                    }
                    if($tax!=0){
                        $tax = (int) $tax;

                        if($this->Settings->state == $supplier_state) {
                            $cgstrate = $tax / 2;
                            $sgstrate = $cgstrate;
                            $igstrate = 0;

                        }else{

                            $igstrate =  $tax;
                            $sgstrate = $cgstrate = 0;

                        }
                        $cgstamt =  $_POST['cgst_amount'][$r];
                        $sgstamt = $_POST['sgst_amount'][$r];
                        $igstamt = $_POST['igst_amount'][$r];

                        $cessamt = $_POST['cess_amount'][$r];
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    $items[] = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $balance_qty,
                        'quantity_received' => $quantity_received,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        'supplier_part_no' => $supplier_part_no,
                        'date' => date('Y-m-d', strtotime($date)),
                        'gst_code' => $gst_code,
                        'cgst_rate'=> $cgstrate,
                        'sgst_rate'=> $sgstrate,
                        'igst_rate'=> $igstrate,
                        'cgst_amt' => $cgstamt,
                        'sgst_amt'=> $sgstamt,
                        'igst_amt'=> $igstamt,
                        'cess_rate'=> $cessrate,
                        'cess_amt'=> $cessamt
                    );

                    $total += $item_net_cost * $item_unit_quantity;
                }
            }
            if ($status == 'received' || $status == 'partial') {
                $status = $partial ? $partial : 'received';
            }
            if (empty($items)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                foreach ($items as $item) {
                    $item["status"] = $status;
                    $products[] = $item;
                }
                krsort($products);
            }

            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);

                } else {
                    $order_discount = $this->sma->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            $data = array('reference_no' => $reference,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'status' => $status,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
                'payment_term' => $payment_term,
                'due_date' => $due_date,
                'ext_pur_ref_no' => $ext_pur_ref_no
            );
            if ($date) {
                $data['date'] = $date;
            }

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);
        }
        
        if ($this->form_validation->run() == true && $this->purchases_model->updatePurchase($id, $data, $products)) {
            if($inv->status == 'received')
            {
                $supplier=$this->site->getCompanyByID($data['supplier_id']);
                $this->db->update('companies',array('balance' =>($supplier->balance-$inv->grand_total)+$data['grand_total'] ),array('id'=>$data['supplier_id']));
            }
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line("purchase_updated"));
            admin_redirect('purchases');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $inv;
            if ($this->Settings->disable_editing) {
                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-'.$this->Settings->disable_editing.' days'))) {
                    $this->session->set_flashdata('error', sprintf(lang("purchase_x_edited_older_than_x_days"), $this->Settings->disable_editing));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }
            $inv_items = $this->purchases_model->getAllPurchaseItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->sma->hrsd($item->expiry) : '');
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->oqty = $item->quantity;
                $row->supplier_part_no = $item->supplier_part_no;
                $row->received = $item->quantity_received ? $item->quantity_received : $item->quantity;
                $row->quantity_balance = $item->quantity_balance + ($item->quantity-$row->received);
                $row->discount = $item->discount ? $item->discount : '0';
                $options = $this->purchases_model->getProductOptions($row->id);
                $row->option = $item->option_id;
                $row->real_unit_cost = $item->real_unit_cost;
                $row->cost = $this->sma->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                $row->tax_rate = $item->tax_rate_id;
                $row->price = $item->price;
                unset($row->details, $row->product_details, $row->file, $row->product_group_id);
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $c++;
            }

            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $this->data['purchase'] = $this->purchases_model->getPurchaseByID($id);
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['supplier_details'] = $this->site->getCompanyByID($this->data['purchase']->supplier_id);
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->session->set_userdata('remove_pols', 1);
			 $this->pos_settings = $this->pos_model->getSetting();
            $this->data['pos_settings'] = $this->pos_settings;
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('edit_purchase')));
            $meta = array('page_title' => lang('edit_purchase'), 'bc' => $bc);

            $this->page_construct('purchases/edit', $meta, $this->data);

        }
    }

    /* ----------------------------------------------------------------------------------------------------------- */

    public function purchase_by_csv()
    {
        $this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('userfile', $this->lang->line("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $quantity = "quantity";
            $product = "product";
            $unit_cost = "unit_cost";
            $tax_rate = "tax_rate";
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = null;
            }
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $supplier_state = $supplier_details->state;
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';

            if (isset($_FILES["userfile"])) {

                $this->load->library('upload');

                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("purchases/purchase_by_csv");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('code', 'net_unit_cost', 'quantity', 'variant', 'item_tax_rate', 'discount', 'expiry');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                     $cgstamt = 0; $sgstamt = 0;  $igstamt = 0;
                $cgstrate = 0; $sgstrate = 0; $igstrate = 0;
                $cessrate = 0; $cessamt = 0;
                    if (isset($csv_pr['code']) && isset($csv_pr['net_unit_cost']) && isset($csv_pr['quantity'])) {
                        $pcode=ltrim(trim($csv_pr['code']),"'");
                        if ($product_details = $this->purchases_model->getProductByCode($pcode)) {

                            if ($csv_pr['variant']) {
                                $item_option = $this->purchases_model->getProductVariantByName($csv_pr['variant'], $product_details->id);
                                if (!$item_option) {
                                    $this->session->set_flashdata('error', lang("pr_not_found") . " ( " . $product_details->name . " - " . $csv_pr['variant'] . " ). " . lang("line_no") . " " . $rw);
                                    redirect($_SERVER["HTTP_REFERER"]);
                                }
                            } else {
                                $item_option = json_decode('{}');
                                $item_option->id = null;
                            }
                            $item_tax=0;
                            $item_code = ltrim($csv_pr['code'],"'");
                            $item_net_cost = $this->sma->formatDecimal($csv_pr['net_unit_cost']);
                            $item_quantity = ((-1) * $csv_pr['quantity']);
                            $quantity_balance =((-1) * $csv_pr['quantity']);
                            $item_tax_rate = $csv_pr['item_tax_rate'];
                            $item_discount = $csv_pr['discount'];
                            $item_expiry = isset($csv_pr['expiry']) ? $this->sma->fsd($csv_pr['expiry']) : null;

                            if (isset($item_discount) && $this->Settings->product_discount) {
                                $discount = $item_discount;
                                $dpos = strpos($discount, $percentage);
                                if ($dpos !== false) {
                                    $pds = explode("%", $discount);
                                    $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($item_net_cost)) * (Float) ($pds[0])) / 100), 4);
                                } else {
                                    $pr_discount = $this->sma->formatDecimal($discount);
                                }
                            } else {
                                $pr_discount = 0;
                            }
                            $pr_item_discount = $this->sma->formatDecimal(($pr_discount * $item_quantity), 4);
                            $product_discount += $pr_item_discount;

                            if (1==2) {

                                if ($tax_details = $this->purchases_model->getTaxRateByName($item_tax_rate)) {
                                    $pr_tax = $tax_details->id;
                                    if ($tax_details->type == 1) {
                                        if (!$product_details->tax_method) {
                                            $item_tax = $this->sma->formatDecimal((($item_net_cost - $pr_discount) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                            $tax = $tax_details->rate . "%";
                                            $item_net_cost -= $item_tax;
                                        } else {
                                            $item_tax = $this->sma->formatDecimal((($item_net_cost - $pr_discount) * $tax_details->rate) / 100, 4);
                                            $tax = $tax_details->rate . "%";
                                        }
                                    } elseif ($tax_details->type == 2) {
                                        $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                        $tax = $tax_details->rate;
                                    }
                                    $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_quantity), 4);
                                } else {
                                    $this->session->set_flashdata('error', lang("tax_not_found") . " ( " . $item_tax_rate . " ). " . lang("line_no") . " " . $rw);
                                    redirect($_SERVER["HTTP_REFERER"]);
                                }

                            } elseif ($product_details->gst_rate) {

                                $pr_tax = $product_details->gst_rate;
                              //  $tax_details = $this->site->getTaxRateByID($pr_tax);
                                if (1 == 1) {//$tax_details->type == 1
                                    if (!$product_details->tax_method) {
                                        $item_tax = $this->sma->formatDecimal((($item_net_cost - $pr_discount) * $pr_tax) / (100 + $pr_tax), 4);
                                        $tax = $pr_tax . "%";
                                        $item_net_cost -= $item_tax;
                                    } else {
                                        $item_tax = $this->sma->formatDecimal((($item_net_cost - $pr_discount) * $pr_tax) / 100, 4);
                                        $tax = $pr_tax . "%";
                                    }
                                } /*elseif ($tax_details->type == 2) {

                                    $item_tax = $this->sma->formatDecimal($pr_tax);
                                    $tax = $tax_details->rate;

                                }*/
                                $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_quantity), 4);

                            } else {
                                $pr_tax = 0;
                                $pr_item_tax = 0;
                                $tax = "";
                            }
                            $product_tax += $pr_item_tax;
                            $subtotal = $this->sma->formatDecimal(((($item_net_cost * $item_quantity) + $pr_item_tax) - $pr_item_discount), 4);
                            $unit = $this->site->getUnitByID($product_details->unit);
                           if($product_details->gst_rate != 0){

                        if($this->Settings->state == $supplier_state) {
                            $cgstrate = $product_details->gst_rate / 2;
                            $sgstrate = $cgstrate;
                            $igstrate = 0;

                        }else{

                           $igstrate =  $product_details->gst_rate;

                            $sgstrate = $cgstrate = 0;
                        }
                        $cgstamt = ($cgstrate*0.01)*$item_net_cost;
                        $sgstamt = ($sgstrate*0.01)*$item_net_cost;
                        $igstamt = ($igstrate*0.01)*$item_net_cost;
                        $cessamt =0;//($cgstrate*0.01)*$pr_item_tax;

                    }

                            $products[] = array(
                                'product_id' => $product_details->id,
                                'product_code' => $item_code,
                                'product_name' => $product_details->name,
                                'option_id' => $item_option->id,
                                 'unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax),
                                'quantity' => $item_quantity,
                                'product_unit_id' => $product_details->unit,
                                'product_unit_code' => $unit->code,
                                'unit_quantity' => $item_quantity,
                                'quantity_balance' => $quantity_balance,
                                'warehouse_id' => $warehouse_id,
                                'item_tax' => $pr_item_tax,
                                'tax_rate_id' => $pr_tax,
                                'tax' => $tax,
                                'discount' => $item_discount,
                                'item_discount' => $pr_item_discount,
                                'expiry' => $item_expiry,
                                'subtotal' => $subtotal,
                                'date' => date('Y-m-d', strtotime($date)),
                                'status' => $status,
                                'unit_cost' => $this->sma->formatDecimal(($item_net_cost + $item_tax), 4),
                                'real_unit_cost' => $this->sma->formatDecimal(($item_net_cost + $item_tax + $pr_discount), 4),
                                'gst_code' => $product_details->gst_code,
                        'cgst_rate'=> $cgstrate,
                        'sgst_rate'=> $sgstrate,
                        'igst_rate'=> $igstrate,
                        'cgst_amt' => $cgstamt,
                        'sgst_amt'=> $sgstamt,
                        'igst_amt'=> $igstamt,
                        'cess_rate'=> $cessrate,
                        'cess_amt'=> $cessamt

                            );

                            $total += $this->sma->formatDecimal(($item_net_cost * $item_quantity), 4);

                        } else {
                            $this->session->set_flashdata('error', $this->lang->line("pr_not_found") . " ( " . $csv_pr['code'] . " ). " . $this->lang->line("line_no") . " " . $rw);
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                        $rw++;
                    }

                }
            }

            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);

                } else {
                    $order_discount = $this->sma->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);

            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal((($total + $product_tax - $total_discount) * $order_tax_details->rate) / 100);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $total_discount), 4);
            $data = array('reference_no' => $reference,
                'date' => $date,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'status' => $status,
                'created_by' => $this->session->userdata('username'),
            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            //$this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products)) {

            $this->session->set_flashdata('message', $this->lang->line("purchase_added"));
            admin_redirect("purchases");
        } else {

            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['ponumber'] = ''; // $this->site->getReference('po');

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('add_purchase_by_csv')));
            $meta = array('page_title' => lang('add_purchase_by_csv'), 'bc' => $bc);
            $this->page_construct('purchases/purchase_by_csv', $meta, $this->data);

        }
    }
    public function purchase_by_csv1()
    {
        $this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('userfile', $this->lang->line("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $quantity = "quantity";
            $product = "product";
            $unit_cost = "unit_cost";
            $tax_rate = "tax_rate";
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = null;
            }

            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
           
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
             $supplier_state =  $supplier_details->state;
            $note = $this->sma->clear_tags($this->input->post('note'));

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';

            if (isset($_FILES["userfile"])) {

                $this->load->library('upload');

                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("purchases/purchase_by_csv");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('code', 'net_unit_cost', 'quantity', 'variant', 'item_tax_rate', 'discount', 'expiry');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                $gst_code="";
                foreach ($final as $csv_pr) {
                     $cgstamt = 0; $sgstamt = 0;  $igstamt = 0;
                     $cgstrate = 0; $sgstrate = 0; $igstrate = 0;
                     $cessrate = 0; $cessamt = 0;
                    if (isset($csv_pr['code']) && isset($csv_pr['net_unit_cost']) && isset($csv_pr['quantity'])) {
                        $ccode=ltrim($csv_pr['code'],"'");
                        if ($product_details = $this->purchases_model->getProductByCode($ccode)) {
                            $gst_code=$product_details->gst_code;
                            if ($csv_pr['variant']) {
                                $item_option = $this->purchases_model->getProductVariantByName($csv_pr['variant'], $product_details->id);
                                if (!$item_option) {
                                    $this->session->set_flashdata('error', lang("pr_not_found") . " ( " . $product_details->name . " - " . $csv_pr['variant'] . " ). " . lang("line_no") . " " . $rw);
                                    redirect($_SERVER["HTTP_REFERER"]);
                                }
                            } else {
                                $item_option = json_decode('{}');
                                $item_option->id = null;
                            }

                            $item_code = $ccode;
                            $item_net_cost = $this->sma->formatDecimal($csv_pr['net_unit_cost']);
                            $item_quantity = $csv_pr['quantity'];
                            $quantity_balance = $csv_pr['quantity'];
                            $item_tax_rate = $csv_pr['item_tax_rate'];
                            $item_discount = $csv_pr['discount'];
                            $item_expiry = isset($csv_pr['expiry']) ? $this->sma->fsd($csv_pr['expiry']) : null;

                            if (isset($item_discount) && $this->Settings->product_discount) {
                                $discount = $item_discount;
                                $dpos = strpos($discount, $percentage);
                                if ($dpos !== false) {
                                    $pds = explode("%", $discount);
                                    $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($item_net_cost)) * (Float) ($pds[0])) / 100), 4);
                                } else {
                                    $pr_discount = $this->sma->formatDecimal($discount);
                                }
                            } else {
                                $pr_discount = 0;
                            }
                            $pr_item_discount = $this->sma->formatDecimal(($pr_discount * $item_quantity), 4);
                            $product_discount += $pr_item_discount;
                        

                            } elseif ($product_details->gst_rate) {
                                   $item_tax_rate=$product_details->gst_rate; 
                                 


                                //$pr_tax = $product_details->tax_rate;
                               // $tax_details = $this->site->getTaxRateByID($pr_tax);
                                $taxper=$product_details->gst_rate;
                                if (1 == 1) {
                                    if (!$product_details->tax_method) {
                                        $item_tax = $this->sma->formatDecimal((($item_net_cost - $pr_discount) * $taxper) / (100 + $taxper), 4);
                                        $tax = $taxper . "%";

                                                 if($taxper != 0){

                                if($this->Settings->state == $supplier_state) {
                                    $cgstrate = $taxper / 2;
                                    $sgstrate = $cgstrate;
                                    $igstrate = 0;

                                }else{

                                   $igstrate =  $taxper;

                                    $sgstrate = $cgstrate = 0;
                                }
                                $icost=$this->sma->formatDecimal($csv_pr['net_unit_cost']);
                                $cgstamt = $this->sma->formatDecimal(($cgstrate*0.01)*$icost);
                                $sgstamt = $this->sma->formatDecimal(($sgstrate*0.01)*$icost);
                                $igstamt = $this->sma->formatDecimal(($igstrate*0.01)*$icost);
                                $cessamt = 0;

                            }
                                        //$item_net_cost -= $item_tax;
                                    } else {
                                        $item_tax = $this->sma->formatDecimal((($item_net_cost - $pr_discount) * $taxper) / 100, 4);
                                        $tax = $taxper . "%";
                                    }
                                } elseif ($tax_details->type == 2) {

                                    $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                    $tax = $tax_details->rate;

                                }
                                $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_quantity), 4);

                            } else {
                                $pr_tax = 0;
                                $pr_item_tax = 0;
                                $tax = "";
                            }
                            $product_tax += $pr_item_tax;
                            $subtotal = $this->sma->formatDecimal(((($item_net_cost * $item_quantity) + $pr_item_tax) - $pr_item_discount), 4);
                            $unit = $this->site->getUnitByID($product_details->unit);
                            $icost=$this->sma->formatDecimal($csv_pr['net_unit_cost']);
                            $products[] = array(
                                'product_id' => $product_details->id,
                                'product_code' => $item_code,
                                'product_name' => $product_details->name,
                                'option_id' => $item_option->id,
                                'net_unit_cost' => $this->sma->formatDecimal($csv_pr['net_unit_cost']),
                                'quantity' => $item_quantity,
                                'product_unit_id' => $product_details->unit,
                                'product_unit_code' => $unit->code,
                                'unit_quantity' => $item_quantity,
                                'quantity_balance' => $quantity_balance,
                                'warehouse_id' => $warehouse_id,
                                'item_tax' => $pr_item_tax,
                                'tax_rate_id' => 0,
                                'tax' => $tax,
                                'discount' => $item_discount,
                                'item_discount' => $pr_item_discount,
                                'expiry' => $item_expiry,
                                'subtotal' => $subtotal,
                                'date' => date('Y-m-d', strtotime($date)),
                                'status' => $status,
                                'unit_cost' =>$this->sma->formatDecimal($csv_pr['net_unit_cost']),
                                'real_unit_cost' => $this->sma->formatDecimal(( $item_tax + $pr_discount+$icost), 4),
                                 'gst_code' => $gst_code,
                        'cgst_rate'=> $cgstrate,
                        'sgst_rate'=> $sgstrate,
                        'igst_rate'=> $igstrate,
                        'cgst_amt' => $cgstamt,
                        'sgst_amt'=> $sgstamt,
                        'igst_amt'=> $igstamt,
                        'cess_rate'=> $cessrate,
                        'cess_amt'=> $cessamt
                            );

                            $total += $this->sma->formatDecimal(($item_net_cost * $item_quantity), 4);

                        } else {
                            $this->session->set_flashdata('error', $this->lang->line("pr_not_found") . " ( " . $csv_pr['code'] . " ). " . $this->lang->line("line_no") . " " . $rw);
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                        $rw++;
                    }

                }
            }

            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);

                } else {
                    $order_discount = $this->sma->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);

            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal((($total + $product_tax - $total_discount) * $order_tax_details->rate) / 100);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $total_discount), 4);
            $data = array('reference_no' => $reference,
                'date' => $date,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'status' => $status,
                'created_by' => $this->session->userdata('username'),
            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            //$this->sma->print_arrays($data, $products);
        

        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products)) {

            $this->session->set_flashdata('message', $this->lang->line("purchase_added"));
            admin_redirect("purchases");
        } else {

            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['ponumber'] = ''; // $this->site->getReference('po');

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('add_purchase_by_csv')));
            $meta = array('page_title' => lang('add_purchase_by_csv'), 'bc' => $bc);
            $this->page_construct('purchases/purchase_by_csv', $meta, $this->data);

        }
    }

    /* --------------------------------------------------------------------------- */

    public function delete($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->purchases_model->deletePurchase($id)) {
            //For delete return purchase from debit
            $purchase = $this->purchases_model->getPurchaseByID($id);
            $ref = explode('/', $purchase->return_purchase_ref);
            if($ref[0] == 'PR')
            {   
                $this->db->delete('debit',array('ret_refrence_no' =>$purchase->return_purchase_ref ));
            }
            
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(array('error' => 0, 'msg' => lang("purchase_deleted")));
            }
            $this->session->set_flashdata('message', lang('purchase_deleted'));
            admin_redirect('welcome');
        }
    }

    /* --------------------------------------------------------------------------- */

    public function suggestions()
    {
        $term = $this->input->get('term', true);
        $supplier_id = $this->input->get('supplier_id', true);
        if($this->input->get('brand_id'))
            $brand_id = $this->input->get('brand_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->sma->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        if(!empty($brand_id))
            $rows = $this->purchases_model->getProductNames($sr,$brand_id);
        else
            $rows = $this->purchases_model->getProductNames($sr);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                $option = false;
                $row->item_tax_method = $row->tax_method;
                $options = $this->purchases_model->getProductOptions($row->id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->purchases_model->getProductOptionByID($option_id) : current($options);
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->cost = 0;
                    $option_id = FALSE;
                }
                $row->option = $option_id;
                $row->supplier_part_no = '';
                if ($opt->cost != 0) {
                    $row->cost = $opt->cost;
                }
                $row->cost = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                $row->real_unit_cost = $row->cost;
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_cost = $row->cost;
                $row->unit = $row->purchase_unit ? $row->purchase_unit : $row->unit;
                $row->new_entry = 1;
                $row->expiry = '';
                $row->qty = 1;
                $row->quantity_balance = '';
                $row->discount = '0';
                unset($row->details, $row->product_details,  $row->file, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);

                $units = $this->site->getUnitsByBUID($row->base_unit);
                //$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $tax_rate = $row->gst_rate;

                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $r++;
            }
            //console.log($pr);
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    /* -------------------------------------------------------------------------------- */

    public function purchase_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {

                    $this->sma->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->purchases_model->deletePurchase($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("purchases_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'combine') {

                    $html = $this->combine_pdf($_POST['val']);

                } elseif ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('purchases'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('supplier'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('status'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('grand_total'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $purchase = $this->purchases_model->getPurchaseByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($purchase->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $purchase->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $purchase->supplier);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $purchase->status);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->sma->formatMoney($purchase->grand_total));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'purchases_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    return create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_purchase_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    /* -------------------------------------------------------------------------------- */

    public function payments($id = null)
    {
        $this->sma->checkPermissions(false, true);

        $this->data['payments'] = $this->purchases_model->getPurchasePayments($id);
        $this->data['inv'] = $this->purchases_model->getPurchaseByID($id);
        $this->load->view($this->theme . 'purchases/payments', $this->data);
    }

    public function payment_note($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $payment = $this->purchases_model->getPaymentByID($id);
        $inv = $this->purchases_model->getPurchaseByID($payment->purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        $this->data['page_title'] = $this->lang->line("payment_note");

        $this->load->view($this->theme . 'purchases/payment_note', $this->data);
    }

    public function email_payment($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $payment = $this->purchases_model->getPaymentByID($id);
        $inv = $this->purchases_model->getPurchaseByID($payment->purchase_id);
        $supplier = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        if ( ! $supplier->email) {
            $this->sma->send_json(array('msg' => lang("update_supplier_email")));
        }
        $this->data['supplier'] =$supplier;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        $this->data['page_title'] = lang("payment_note");
        $html = $this->load->view($this->theme . 'purchases/payment_note', $this->data, TRUE);

        $html = str_replace(array('<i class="fa fa-2x">&times;</i>', 'modal-', '<p>&nbsp;</p>', '<p style="border-bottom: 1px solid #666;">&nbsp;</p>', '<p>'.lang("stamp_sign").'</p>'), '', $html);
        $html = preg_replace("/<img[^>]+\>/i", '', $html);
        // $html = '<div style="border:1px solid #DDD; padding:10px; margin:10px 0;">'.$html.'</div>';

        $this->load->library('parser');
        $parse_data = array(
            'stylesheet' => '<link href="'.$this->data['assets'].'styles/helpers/bootstrap.min.css" rel="stylesheet"/>',
            'name' => $supplier->company && $supplier->company != '-' ? $supplier->company :  $supplier->name,
            'email' => $supplier->email,
            'heading' => lang('payment_note').'<hr>',
            'msg' => $html,
            'site_link' => base_url(),
            'site_name' => $this->Settings->site_name,
            'logo' => '<img src="' . base_url('assets/uploads/logos/' . $this->Settings->logo) . '" alt="' . $this->Settings->site_name . '"/>'
        );
        $msg = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/email_con.html');
        $message = $this->parser->parse_string($msg, $parse_data);
        $subject = lang('payment_note') . ' - ' . $this->Settings->site_name;

        if ($this->sma->send_email($supplier->email, $subject, $message)) {
            $this->sma->send_json(array('msg' => lang("email_sent")));
        } else {
            $this->sma->send_json(array('msg' => lang("email_failed")));
        }
    }

    public function add_payment($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $purchase = $this->purchases_model->getPurchaseByID($id);
        if ($purchase->payment_status == 'paid' && $purchase->grand_total == $purchase->paid) {
            $this->session->set_flashdata('error', lang("purchase_already_paid"));
            $this->sma->md();
        }

        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            if($purchase->grand_total > abs($this->input->post('returned_balance')))
            {
                $paid=$this->input->post('returned_balance')? abs($this->input->post('returned_balance')):'0.00';
            }
            else
            {
                $paid=$purchase->grand_total;
            }
            $payment = array(
                'date' => $date,
                'purchase_id' => $this->input->post('purchase_id'),
                'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay'),
                'amount' => $this->input->post('amount-paid') > 0 ? $this->input->post('amount-paid') : '0',
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->sma->clear_tags($this->input->post('note')),
                'created_by' => $this->session->userdata('user_id'),
                'transaction_number' => $this->input->post('transaction_id'),
                'type' => 'sent',
            );

            //for return payment
            $return_payment = NULL;
            $debit = NULL;
            if(!empty($_POST['debit']))
            {
                $amount = $this->input->post('amount-paid');
                $total ='0';
                $actual_amount = $_POST['remaining_pay'];
                $i = sizeof($_POST['debit']);
                for ($r = 0; $r < $i; $r++) 
                {   
                    $debit_id = $_POST['debit'][$r];
                    $debit_data = $this->purchases_model->getDebitDataByID($debit_id);
                    if($actual_amount > 0)
                    {
                       $rem_amount = $debit_data->total + $actual_amount;
                       if($rem_amount < 0)
                       {
                            $total = $rem_amount;
                            $status = 'pending';
                            $paid_amount = abs($actual_amount);
                            $actual_amount =0;
                       }
                       else
                       {
                            $total = '0';
                            $status = 'paid';
                            $paid_amount = abs($debit_data->total);
                            $actual_amount = $rem_amount;
                       }

                       $debit[] = array(
                            'ret_refrence_no' => $debit_data->ret_refrence_no,
                            'total' => $total,
                            'payment_status' =>$status,
                        );    
                       if($amount > 0)
                       {
                           // $paid_amount = abs($debit_data->total);
                       }
                       $return_payment[]=array(
                        'date' => $date,
                        'purchase_id' => $this->input->post('purchase_id'),
                        'reference_no' => $debit_data->ret_refrence_no,
                        'amount' => $paid_amount,
                        'paid_by' => 'Debit note payment',
                        'note' => $this->sma->clear_tags($this->input->post('note')),
                        'created_by' => $this->session->userdata('user_id'),
                        'type' => 'sent',
                        );
                    }        
                }                
            }
            //print_r($return_payment);die;
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
         
        if ($this->form_validation->run() == true && $this->purchases_model->addPayment($payment,$return_payment,$debit)) {
            if($this->input->post('amount-paid') > 0)
            {
                $supplier = $this->site->getCompanyByID($purchase->supplier_id);
                $this->db->update('companies', array('balance' => $supplier->balance - $this->input->post('amount-paid')), array('id' => $purchase->supplier_id));
            }
            $this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $purchase;
            $this->data['payment_ref'] = ''; //$this->site->getReference('ppay');
            $supplier = $this->site->getCompanyByID($purchase->supplier_id);
            $this->data['debit'] = $this->purchases_model->getDebitDataBySupplier($purchase->supplier_id);
            /*$this->data['returned_balance'] =$supplier->returned_balance;*/
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'purchases/add_payment', $this->data);
        }
    }

    public function edit_payment($id = null)
    { //print_r($_POST);die;

       

        $this->sma->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        

        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $payment = array(
                'date' => $date,
                'purchase_id' => $this->input->post('purchase_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->sma->clear_tags($this->input->post('note')),
                'transaction_number' => $this->input->post('transaction_id'),
            );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }
           // print_r($this->input->post('reference_no'));die;
            
        $paid =$this->purchases_model->getPaymentByID($id);
            //$this->sma->print_arrays($payment);

        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

         //old
         $old_payment =$this->purchases_model->getPaymentByID($id);        
         //Debit note payment
        if ($this->form_validation->run() == true && $this->purchases_model->updatePayment($id, $payment)) {
            if($_POST['old_amount']){

                //new
                if($old_payment->paid_by=='Debit note payment')
                {
                    $status = 'pending';
                    $debit_note_amt=((-1)*($old_payment->amount));
                     $this->db->update('debit',array('total' =>$debit_note_amt,'payment_status'=>$status ),array('ret_refrence_no' => $old_payment->reference_no));
                }
                //new

                /*if($this->input->post('amount-paid')==abs($this->input->post('old_amount')))
                {
                    $amount = $this->input->post('old_amount') + $this->input->post('amount-paid');
                    $status = 'paid';
                }
                else
                {
                    $status = 'pending';
                    $amount = $this->input->post('old_amount') + $this->input->post('amount-paid');
                }
                $this->db->update('debit',array('total' =>$amount,'payment_status'=>$status ),array('ret_refrence_no' => $this->input->post('reference_no')));*/
            }else{
                $purchase = $this->purchases_model->getPurchaseByID($this->input->post('purchase_id'));
                $supplier = $this->site->getCompanyByID($purchase->supplier_id);
                $this->db->update('companies',array('balance' => ($supplier->balance-$paid->amount)+$this->input->post('amount-paid')),array('id' => $supplier->id));
            }
            $this->session->set_flashdata('message', lang("payment_updated"));
            admin_redirect("purchases");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['payment'] = $this->purchases_model->getPaymentByID($id);
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'purchases/edit_payment', $this->data);
        }
    }

    public function delete_payment($id = null)
    {
        $this->sma->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $payment = $this->purchases_model->getPaymentByID($id);
        $ref = explode('/', $payment->reference_no);
        if($ref[0] == 'PR')
        {   
            $debit = $this->purchases_model->getDebitDataByReference($payment->reference_no);
            $this->db->update('debit',array('total'=>$debit->total-$payment->amount,'payment_status'=>'pending'),array('ret_refrence_no'=>$payment->reference_no));
        }
        
        if ($this->purchases_model->deletePayment($id)) {
            //echo lang("payment_deleted");
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    /* -------------------------------------------------------------------------------- */

    public function expenses($id = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('expenses')));
        $meta = array('page_title' => lang('expenses'), 'bc' => $bc);
        $this->page_construct('purchases/expenses', $meta, $this->data);
    }

    public function getExpenses()
    {
        $this->sma->checkPermissions('expenses');

        $detail_link = anchor('admin/purchases/expense_note/$1', '<i class="fa fa-file-text-o"></i> ' . lang('expense_note'), 'data-toggle="modal" data-target="#myModal2"');
        $edit_link = anchor('admin/purchases/edit_expense/$1', '<i class="fa fa-edit"></i> ' . lang('edit_expense'), 'data-toggle="modal" data-target="#myModal"');
        //$attachment_link = '<a href="'.base_url('assets/uploads/$1').'" target="_blank"><i class="fa fa-chain"></i></a>';
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_expense") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('purchases/delete_expense/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_expense') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';

        $this->load->library('datatables');

        $this->datatables
            ->select($this->db->dbprefix('expenses') . ".id as id, date, reference, {$this->db->dbprefix('expense_categories')}.name as category, amount, note, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user", false)
            ->from('expenses')
            ->join('users', 'users.id=expenses.created_by', 'left')
            ->join('expense_categories', 'expense_categories.id=expenses.category_id', 'left')
            ->group_by('expenses.id');

        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
        //$this->datatables->edit_column("attachment", $attachment_link, "attachment");
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function expense_note($id = null)
    {
        $expense = $this->purchases_model->getExpenseByID($id);
        $this->data['user'] = $this->site->getUser($expense->created_by);
        $this->data['category'] = $expense->category_id ? $this->purchases_model->getExpenseCategoryByID($expense->category_id) : NULL;
        $this->data['warehouse'] = $expense->warehouse_id ? $this->site->getWarehouseByID($expense->warehouse_id) : NULL;
        $this->data['expense'] = $expense;
        $this->data['page_title'] = $this->lang->line("expense_note");
        $this->load->view($this->theme . 'purchases/expense_note', $this->data);
    }

    public function add_expense()
    {
        $this->sma->checkPermissions('expenses', true);
        $this->load->helper('security');

        //$this->form_validation->set_rules('reference', lang("reference"), 'required');
        $this->form_validation->set_rules('amount', lang("amount"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = array(
                'date' => $date,
                'reference' => $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('ex'),
                'amount' => $this->input->post('amount'),
                'created_by' => $this->session->userdata('user_id'),
                'note' => $this->input->post('note', true),
                'category_id' => $this->input->post('category', true),
                'warehouse_id' => $this->input->post('warehouse', true),
            );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);

                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            //$this->sma->print_arrays($data);

        } elseif ($this->input->post('add_expense')) {
            $this->session->set_flashdata('error', validation_errors());
            //redirect($_SERVER["HTTP_REFERER"]);
            admin_redirect("purchases/expenses");
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addExpense($data)) {
            $this->session->set_flashdata('message', lang("expense_added"));
            //redirect($_SERVER["HTTP_REFERER"]);
            admin_redirect("purchases/expenses");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['exnumber'] = ''; //$this->site->getReference('ex');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['categories'] = $this->purchases_model->getExpenseCategories();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'purchases/add_expense', $this->data);
        }
    }

    public function edit_expense($id = null)
    {
        $this->sma->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('reference', lang("reference"), 'required');
        $this->form_validation->set_rules('amount', lang("amount"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = array(
                'date' => $date,
                'reference' => $this->input->post('reference'),
                'amount' => $this->input->post('amount'),
                'note' => $this->input->post('note', true),
                'category_id' => $this->input->post('category', true),
                'warehouse_id' => $this->input->post('warehouse', true),
            );
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            //$this->sma->print_arrays($data);

        } elseif ($this->input->post('edit_expense')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->updateExpense($id, $data)) {
            $this->session->set_flashdata('message', lang("expense_updated"));
            admin_redirect("purchases/expenses");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['expense'] = $this->purchases_model->getExpenseByID($id);
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['categories'] = $this->purchases_model->getExpenseCategories();
            $this->load->view($this->theme . 'purchases/edit_expense', $this->data);
        }
    }

    public function delete_expense($id = null)
    {
        $this->sma->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $expense = $this->purchases_model->getExpenseByID($id);
        if ($this->purchases_model->deleteExpense($id)) {
            if ($expense->attachment) {
                unlink($this->upload_path . $expense->attachment);
            }
            $this->sma->send_json(array('error' => 0, 'msg' => lang("expense_deleted")));
        }
    }

    public function expense_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->purchases_model->deleteExpense($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("expenses_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('expenses'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('amount'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('created_by'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $expense = $this->purchases_model->getExpenseByID($id);
                        $user = $this->site->getUser($expense->created_by);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($expense->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $expense->reference);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->sma->formatMoney($expense->amount));
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $expense->note);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $user->first_name . ' ' . $user->last_name);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(35);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'expenses_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    return create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_expense_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function view_return($id = null)
    {
        $this->sma->checkPermissions('return_purchases');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getReturnByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->purchases_model->getAllReturnItems($id);
        $this->data['purchase'] = $this->purchases_model->getPurchaseByID($inv->purchase_id);
        $this->load->view($this->theme.'purchases/view_return', $this->data);
    }

    public function return_purchase($id = null)
    {
        $this->sma->checkPermissions('return_purchases');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $purchase = $this->purchases_model->getPurchaseByID($id);
        $supplier = $this->site->getCompanyByID($purchase->supplier_id);
        /*if ($purchase->return_id) {
            $this->session->set_flashdata('error', lang("purchase_already_returned"));
            redirect($_SERVER["HTTP_REFERER"]);
        }*/
        $this->form_validation->set_rules('return_surcharge', lang("return_surcharge"), 'required');

        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rep');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $return_surcharge = $this->input->post('return_surcharge') ? $this->input->post('return_surcharge') : 0;
            $note = $this->sma->clear_tags($this->input->post('note'));

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $item_tax = 0;
            $i = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_code = $_POST['product'][$r];
                $purchase_item_id = $_POST['purchase_item_id'][$r];
                $item_option = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_cost = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                $unit_cost = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                $item_unit_quantity = (0-$_POST['quantity'][$r]);
                $item_expiry = isset($_POST['expiry'][$r]) ? $_POST['expiry'][$r] : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = (0-$_POST['product_base_quantity'][$r]);
                $cgstrate = $_POST['cgst_rate'][$r];
                $sgstrate = $_POST['sgst_rate'][$r];
                $cessrate = $_POST['cess_rate'][$r];
                $igstrate = $_POST['igst_rate'][$r];
                $cgstamt =  0-$_POST['cgst_amount'][$r];
                $sgstamt = 0-$_POST['sgst_amount'][$r];
                $igstamt = 0-$_POST['igst_amount'][$r];
                $cessamt = 0-$_POST['cess_amount'][$r];
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity) && $item_quantity < 0) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);

                    $item_type = $product_details->type;
                    $item_name = $product_details->name;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount);
                        }
                    } else {
                        $pr_discount = 0;
                    }
                    // $unit_cost = $this->sma->formatDecimal($unit_cost - $pr_discount);
                    $pr_item_discount = $this->sma->formatDecimal(($pr_discount * $item_unit_quantity), 4);
                    $product_discount += $pr_item_discount;

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {

                            if (!$product_details->tax_method) {
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            }

                        } elseif ($tax_details->type == 2) {

                            $item_tax = $this->sma->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;

                        }
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

                    } else {
                        $pr_tax = 0;
                        $pr_item_tax = 0;
                        $tax = "";
                    }

                    $item_net_cost = $product_details->tax_method ? $this->sma->formatDecimal(($unit_cost - $pr_discount), 4) : $this->sma->formatDecimal(($unit_cost - $item_tax - $pr_discount), 4);
                    $product_tax += $pr_item_tax;
                    $subtotal = $this->sma->formatDecimal((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit = $this->site->getUnitByID($item_unit);

                    $products[] = array(
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $item_quantity,
                        'warehouse_id' => $purchase->warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'real_unit_cost' => $real_unit_cost,
                        'purchase_item_id' => $purchase_item_id,
                        'status' => 'received',
                        'cgst_rate'       => $cgstrate,
                        'igst_rate'       => $igstrate,
                        'sgst_rate'       => $sgstrate,
                        'cgst_amt'        => $cgstamt,
                        'sgst_amt'        => $sgstamt,
                        'igst_amt'        => $igstamt,
                        'cess_rate'       => $cessrate,
                        'cess_amt'        => $cessamt
                    );

                    $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }
            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('order_discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);
                } else {
                    $order_discount = $this->sma->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $order_discount + $product_discount;

            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($return_surcharge) - $order_discount), 4);
            $data = array('date' => $date,
                'purchase_id' => $id,
                'reference_no' => $purchase->reference_no,
                'supplier_id' => $purchase->supplier_id,
                'supplier' => $purchase->supplier,
                'warehouse_id' => $purchase->warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'surcharge' => $this->sma->formatDecimal($return_surcharge),
                'grand_total' => $grand_total,
                'created_by' => $this->session->userdata('user_id'),
                'return_purchase_ref' => $reference,
                'status' => 'returned',
                'payment_status' => $purchase->payment_status == 'paid' ? 'due' : 'pending',
            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);
        }
        
        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products)) {
            $debit_data = array(
                'supplier_id'     => $data['supplier_id'],
                'purchase_id'     => $data['purchase_id'],
                'ret_refrence_no' => $data['return_purchase_ref'],
                'total'           => $data['total'],
                'payment_status'  => 'pending',
            );
            $this->db->insert('debit',$debit_data);
            $this->session->set_flashdata('message', lang("debit_note_added"));
            admin_redirect("purchases/debit_note");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $purchase;
            if ($this->data['inv']->status != 'received' && $this->data['inv']->status != 'partial') {
                $this->session->set_flashdata('error', lang("purchase_status_x_received"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            
            $inv_items = $this->purchases_model->getAllPurchaseItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            $returnPurchaseId = $this->purchases_model->getAllReturnPurchaseIdByRefrence($purchase->reference_no);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                $row->received = $item->quantity_received ? $item->quantity_received : $item->quantity;
                if ($purchase->return_id) 
                {
                    $balance_quantity = $this->purchases_model->getBalanceQuantity($item->product_id,$item->warehouse_id,$returnPurchaseId);
                    //print_r($balance_quantity);die;
                    $rem_quantity = $this->purchases_model->getRemQuantityAfterRetuurn($item->product_id,$item->warehouse_id,$returnPurchaseId);
                    $row->base_quantity = 0;
                    $row->qty = 0;
                    $row->oqty = $row->received - abs($rem_quantity);
                }
                else{
                    $row->base_quantity = 0;
                    $row->qty = 0;
                    $row->oqty = $item->unit_quantity;
                }
                $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->sma->hrsd($item->expiry) : '');
                
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit = $item->product_unit_id;
                
                $row->purchase_item_id = $item->id;
                $row->supplier_part_no = $item->supplier_part_no;
                $row->quantity_balance = $item->quantity_balance + ($item->quantity-$row->received);
                $row->discount = $item->discount ? $item->discount : '0';
                $options = $this->purchases_model->getProductOptions($row->id);
                $row->option = !empty($item->option_id) ? $item->option_id : '';
                $row->real_unit_cost = $item->real_unit_cost;
                $row->cost = $this->sma->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                $row->tax_rate = $item->tax_rate_id;
                $row->cgst_rate = $item->cgst_rate;
                $row->cgst_amt = $item->cgst_amt;
                $row->sgst_rate = $item->sgst_rate;
                $row->sgst_amt = $item->sgst_amt;
                $row->cess_rate = $item->cess_rate;
                $row->cess_amt = $item->cess_amt;
                $row->igst_rate = $item->igst_rate;
                $row->igst_amt = $item->igst_amt;
                $row->supplier_state = $supplier->state;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'units' => $units, 'tax_rate' => $tax_rate, 'options' => $options);
                

                $c++;
            }
       
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['reference'] = '';
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('return_purchase')));
            $meta = array('page_title' => lang('return_purchase'), 'bc' => $bc);
            $this->page_construct('purchases/return_purchase', $meta, $this->data);
        }
    }

    public function getSupplierCost($supplier_id, $product)
    {
        switch ($supplier_id) {
            case $product->supplier1:
                $cost =  $product->supplier1price > 0 ? $product->supplier1price : $product->cost;
                break;
            case $product->supplier2:
                $cost =  $product->supplier2price > 0 ? $product->supplier2price : $product->cost;
                break;
            case $product->supplier3:
                $cost =  $product->supplier3price > 0 ? $product->supplier3price : $product->cost;
                break;
            case $product->supplier4:
                $cost =  $product->supplier4price > 0 ? $product->supplier4price : $product->cost;
                break;
            case $product->supplier5:
                $cost =  $product->supplier5price > 0 ? $product->supplier5price : $product->cost;
                break;
            default:
                $cost = $product->cost;
        }
        return $cost;
    }

    public function update_status($id)
    {

        $this->form_validation->set_rules('status', lang("status"), 'required');

        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $note = $this->sma->clear_tags($this->input->post('note'));
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        }

        if ($this->form_validation->run() == true && $this->purchases_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            admin_redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        } else {

            $this->data['inv'] = $this->purchases_model->getPurchaseByID($id);
            $this->data['returned'] = FALSE;
            if ($this->data['inv']->status == 'returned' || $this->data['inv']->return_id) {
                $this->data['returned'] = TRUE;
            }
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'purchases/update_status', $this->data);

        }
    }

    public function debit_note($warehouse_id = null)
    {
        $this->sma->checkPermissions('return_purchases');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('debit_note')));
        $meta = array('page_title' => lang('debit_note'), 'bc' => $bc);
        $this->page_construct('purchases/debit_note', $meta, $this->data);

    }

     public function getDebitNotes1($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('admin/purchases/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Debit_Note_details'));
        $payments_link = anchor('admin/purchases/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('admin/purchases/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('admin/purchases/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('admin/purchases/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase'));
        $pdf_link = anchor('admin/purchases/debit_note_pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode = anchor('admin/products/print_barcodes/?purchase=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $return_link = anchor('admin/purchases/return_purchase/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_purchase'));
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_Debit_Note") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('purchases/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('Delete_Debit_note') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $pdf_link . '</li>
           <li>' . $email_link . '</li>
                 
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("p.id, DATE_FORMAT(p.date, '%Y-%m-%d %T') as date, p.reference_no,{$this->db->dbprefix('debit')}.ret_refrence_no, p.supplier, p.status, p.grand_total, 
                    ((-1*p.grand_total) - (-1*{$this->db->dbprefix('debit')}.total) ) AS 
                    paid, {$this->db->dbprefix('debit')}.total, {$this->db->dbprefix('debit')}.payment_status")
               ->where('p.status', 'returned')
                ->from('debit')
                ->join('purchases p','debit.purchase_id=p.purchase_id','left')

                  
                ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("p.id, DATE_FORMAT(p.date, '%Y-%m-%d %T') as date, p.reference_no,{$this->db->dbprefix('debit')}.ret_refrence_no, p.supplier, p.status, p.grand_total, 
                    ((-1*p.grand_total) - (-1*{$this->db->dbprefix('debit')}.total) ) AS 
                    paid, {$this->db->dbprefix('debit')}.total, {$this->db->dbprefix('debit')}.payment_status")
               ->where('p.status', 'returned')
                ->from('debit')
                ->join('purchases p','debit.purchase_id=p.purchase_id','left')

                  ;
        }
        // $this->datatables->where('status !=', 'returned');
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
    public function debit_note_modal_view($debit_note_id = null)
    {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $debit_note_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($debit_note_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }


        $dn=$this->purchases_model->getDebitNoteByRef($inv->return_purchase_ref);
        $dn_paid=false;
        $dn_id=0;
        if($dn)
        {
            $dn_paid=$this->purchases_model->checkDebitPaymentByRef($inv->return_purchase_ref,$dn->id);
            $dn_id=$dn->id;
        }
        $this->data['dn_id']=$dn_id;
        $this->data['dn_paid']=$dn_paid;
        $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($debit_note_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['supplier_state'] = $this->purchases_model->getStateByID($this->data['supplier']->state);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($debit_note_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;
        $this->data['taxdata'] = $this->purchases_model->getTaxdata($debit_note_id);
        $this->data['biller'] = $this->site->getCompanyByID($this->Settings->default_biller);
        $this->data['purchase'] = $this->purchases_model->getPurchaseByID($inv->purchase_id);
        $this->load->view($this->theme . 'purchases/debit_note_modal_view', $this->data);

    }

    //generate pdf for debit note

    public function debit_note_pdf($purchase_id = null, $view = null, $save_bufffer = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['inv'] = $inv;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;
        $this->data['biller'] = $this->site->getCompanyByID($this->Settings->default_biller);
        $this->data['taxdata'] = $this->purchases_model->getTaxdata($purchase_id);
        $this->data['purchase'] = $this->purchases_model->getPurchaseByID($inv->purchase_id);
        $name = $this->lang->line("purchase") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";

        $html = $this->load->view($this->theme . 'purchases/debit_note_pdf', $this->data, true);
        if (! $this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'purchases/debit_note_pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer, $this->data['supplier']->invoice_footer);
        }

        else {
            //echo $html;
            $this->sma->generate_pdf($html, $name, false, $this->data['supplier']->invoice_footer,$margin_bottom = null, $header = null, $margin_top = null, $orientation = 'P');
        }

    }

    //Purchase order    
    public function order_list($warehouse_id = null)
    {
        $this->sma->checkPermissions('order');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('purchase_orders')));
        $meta = array('page_title' => lang('purchase_orders'), 'bc' => $bc);
        $this->page_construct('purchases/purchase_orders', $meta, $this->data);

    }

    public function getPurchaseOrders($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        
        $edit_link = anchor('admin/purchases/edit_order/$1', '<i class="fa fa-edit"></i> ' . lang('Edit_Purchase_order'));
        $convert_link = anchor('admin/purchases/edit_purchase_order/$1', '<i class="fa fa-edit"></i> ' . lang('Convert_GRN_Purchase'));
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $edit_link . '</li>
            <li>' . $convert_link . '</li>            
        </ul>
    </div></div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status")
                ->from('purchases')
                ->where('status !=', 'returned')
                ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status")
                ->from('purchases')
                ->where('status !=', 'returned');
        }
        // $this->datatables->where('status !=', 'returned');
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    // public function add_order($quote_id = null)
    // {
    //     $this->sma->checkPermissions('order', true);
    //    // $this->sma->print_arrays($_POST);
    //     $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
    //     //$this->form_validation->set_rules('reference_no', $this->lang->line("ref_no"), 'required');
    //     $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
    //     $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');

    //     $this->session->unset_userdata('csrf_token');
    //     if ($this->form_validation->run() == true) {

    //         $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');
    //         if ($this->Owner || $this->Admin) {
    //             $date = $this->sma->fld(trim($this->input->post('date')));
    //         } else {
    //             $date = date('Y-m-d H:i:s');
    //         }
    //         $warehouse_id = $this->input->post('warehouse');
    //         $supplier_id = $this->input->post('supplier');
    //         $supplier_state = $this->input->post('supplier_state');
    //         $status = $this->input->post('status');
    //         $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
    //         $supplier_details = $this->site->getCompanyByID($supplier_id);
    //         $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
    //         $note = $this->sma->clear_tags($this->input->post('note'));
    //         $payment_term = $this->input->post('payment_term');
    //         $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
    //         if(!empty($this->input->post('add_prod')))
    //             $add_prod = explode(',', $this->input->post('add_prod'));
    //         else
    //             $add_prod = NULL;

    //         $total = 0;
    //         $product_tax = 0;
    //         $order_tax = 0;
    //         $product_discount = 0;
    //         $order_discount = 0;
    //         $percentage = '%';
    //        /* $cgstamt = 0; $sgstamt = 0;  $igstamt = 0;
    //         $cgstrate = 0; $sgstrate = 0; $igstrate = 0;
    //         $cessrate = 0; $cessamt = 0;*/

    //         $i = sizeof($_POST['product']);
    //         for ($r = 0; $r < $i; $r++) {
    //              $cgstamt = 0; $sgstamt = 0;  $igstamt = 0;
    //             $cgstrate = 0; $sgstrate = 0; $igstrate = 0;
    //             $cessrate = 0; $cessamt = 0;
    //             $item_code = $_POST['product'][$r];
    //             $item_net_cost = $this->sma->formatDecimal($_POST['net_cost'][$r]);
    //             $unit_cost = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
    //             $real_unit_cost = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
    //             $item_unit_quantity = $_POST['quantity'][$r];
    //             $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
    //             /*$item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;*/
    //             $item_tax_rate = $_POST['gst_rate'][$r];
    //             $gst_code = $_POST['gst_code'][$r];
    //             $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
    //             $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
    //             $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
    //             $item_unit = $_POST['product_unit'][$r];
    //             $item_quantity = $_POST['product_base_quantity'][$r];

    //             if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity) && $item_quantity > 0) {
    //                 $product_details = $this->purchases_model->getProductByCode($item_code);
    //                 if ($item_expiry) {
    //                     $today = date('Y-m-d');
    //                     if ($item_expiry <= $today) {
    //                         $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
    //                         redirect($_SERVER["HTTP_REFERER"]);
    //                     }
    //                 }
    //                 // $unit_cost = $real_unit_cost;
    //                 $pr_discount = 0;

    //                 if (isset($item_discount)) {
    //                     $discount = $item_discount;
    //                     $dpos = strpos($discount, $percentage);
    //                     if ($dpos !== false) {
    //                         $pds = explode("%", $discount);
    //                         $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (Float) ($pds[0])) / 100), 4);
    //                     } else {
    //                         $pr_discount = $this->sma->formatDecimal($discount);
    //                     }
    //                 }

    //                 $unit_cost = $this->sma->formatDecimal($unit_cost - $pr_discount);
    //                 $item_net_cost = $unit_cost;
    //                 $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
    //                 $product_discount += $pr_item_discount;
    //                 $pr_item_tax = 0;
    //                 $item_tax = 0;
    //                 $tax = 0;
    //                 $cessrate = $_POST['cess_rate'][$r];
    //                 if($cessrate!='0.00') {
    //                     //$pr_item_tax += $cessrate;
    //                     $item_tax_rate += $cessrate;
    //                 }

    //                 if (isset($item_tax_rate) && $item_tax_rate != 0) {


    //                     $ctax = $this->site->calculateTax($product_details, $item_tax_rate, $unit_cost);
    //                     $item_tax = $ctax['amount'];
    //                     $tax = $ctax['tax'];

    //                     if ($product_details->tax_method != 1) {
    //                         $item_net_cost = $unit_cost - $item_tax;
    //                     }
    //                     $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

    //                 }
    //                 if($cessrate!='0.00') {

    //                     $tax -= $cessrate;
    //                 }
    //                 if($tax != 0){

    //                     if($this->Settings->state == $supplier_state) {
    //                         $cgstrate = $tax / 2;
    //                         $sgstrate = $cgstrate;
    //                         $igstrate = 0;

    //                     }else{

    //                        $igstrate =  $tax;

    //                         $sgstrate = $cgstrate = 0;
    //                     }
    //                     $cgstamt =  $_POST['cgst_amount'][$r];
    //                     $sgstamt = $_POST['sgst_amount'][$r];
    //                     $igstamt = $_POST['igst_amount'][$r];
    //                     $cessamt = $_POST['cess_amount'][$r];

    //                 }
                    

    //                 $product_tax += $pr_item_tax;
    //                 $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
    //                 $unit = $this->site->getUnitByID($item_unit);

    //                 $products[] = array(
    //                     'product_id' => $product_details->id,
    //                     'product_code' => $item_code,
    //                     'product_name' => $product_details->name,
    //                     'option_id' => $item_option,
    //                     'net_unit_cost' => $item_net_cost,
    //                     'unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax),
    //                     'quantity' => $item_quantity,
    //                     'product_unit_id' => $item_unit,
    //                     'product_unit_code' => $unit->code,
    //                     'unit_quantity' => $item_unit_quantity,
    //                     'quantity_balance' => $item_quantity,
    //                     'quantity_received' => $item_quantity,
    //                     'warehouse_id' => $warehouse_id,
    //                     'item_tax' => $pr_item_tax,
    //                     'tax_rate_id' => $item_tax_rate,
    //                     'tax' => $tax,
    //                     'discount' => $item_discount,
    //                     'item_discount' => $pr_item_discount,
    //                     'subtotal' => $this->sma->formatDecimal($subtotal),
    //                     'expiry' => $item_expiry,
    //                     'real_unit_cost' => $real_unit_cost,
    //                     'date' => date('Y-m-d', strtotime($date)),
    //                     'status' => $status,
    //                     'supplier_part_no' => $supplier_part_no,
    //                     'gst_code' => $gst_code,
    //                     'cgst_rate'=> $cgstrate,
    //                     'sgst_rate'=> $sgstrate,
    //                     'igst_rate'=> $igstrate,
    //                     'cgst_amt' => $cgstamt,
    //                     'sgst_amt'=> $sgstamt,
    //                     'igst_amt'=> $igstamt,
    //                     'cess_rate'=> $cessrate,
    //                     'cess_amt'=> $cessamt

    //                 );


    //                 $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
    //             }
    //         }


    //         if (empty($products)) {
    //             $this->form_validation->set_rules('product', lang("order_items"), 'required');
    //         } else {
    //             krsort($products);
    //         }

    //         if ($this->input->post('discount')) {
    //             $order_discount_id = $this->input->post('discount');
    //             $opos = strpos($order_discount_id, $percentage);
    //             if ($opos !== false) {
    //                 $ods = explode("%", $order_discount_id);
    //                 $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);

    //             } else {
    //                 $order_discount = $this->sma->formatDecimal($order_discount_id);
    //             }
    //         } else {
    //             $order_discount_id = null;
    //         }
    //         $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

    //         if ($this->Settings->tax2 != 0) {
    //             $order_tax_id = $this->input->post('order_tax');
    //             if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
    //                 if ($order_tax_details->type == 2) {
    //                     $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
    //                 }
    //                 if ($order_tax_details->type == 1) {
    //                     $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
    //                 }
    //             }
    //         } else {
    //             $order_tax_id = null;
    //         }

    //         $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);

    //         $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
    //         $data = array('reference_no' => $reference,
    //             'date' => $date,
    //             'supplier_id' => $supplier_id,
    //             'supplier' => $supplier,
    //             'warehouse_id' => $warehouse_id,
    //             'note' => $note,
    //             'total' => $total,
    //             'product_discount' => $product_discount,
    //             'order_discount_id' => $order_discount_id,
    //             'order_discount' => $order_discount,
    //             'total_discount' => $total_discount,
    //             'product_tax' => $product_tax,
    //             'order_tax_id' => $order_tax_id,
    //             'order_tax' => $order_tax,
    //             'total_tax' => $total_tax,
    //             'shipping' => $this->sma->formatDecimal($shipping),
    //             'grand_total' => $grand_total,
    //             'status' => $status,
    //             'created_by' => $this->session->userdata('user_id'),
    //             'payment_term' => $payment_term,
    //             'due_date' => $due_date,

    //         );

    //         if ($_FILES['document']['size'] > 0) {
    //             $this->load->library('upload');
    //             $config['upload_path'] = $this->digital_upload_path;
    //             $config['allowed_types'] = $this->digital_file_types;
    //             $config['max_size'] = $this->allowed_file_size;
    //             $config['overwrite'] = false;
    //             $config['encrypt_name'] = true;
    //             $this->upload->initialize($config);
    //             if (!$this->upload->do_upload('document')) {
    //                 $error = $this->upload->display_errors();
    //                 $this->session->set_flashdata('error', $error);
    //                 redirect($_SERVER["HTTP_REFERER"]);
    //             }
    //             $photo = $this->upload->file_name;
    //             $data['attachment'] = $photo;
    //         }

    //         // $this->sma->print_arrays($data, $products);
    //     }

    //     if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products,$add_prod)) {
    //         $this->session->set_userdata('remove_pols', 1);
    //         $this->session->set_flashdata('message', $this->lang->line("purchase_order_added"));
    //         admin_redirect('purchases/order_list');
    //     } else {

    //         if ($quote_id) {
    //             $this->data['quote'] = $this->purchases_model->getQuoteByID($quote_id);
    //             $supplier_id = $this->data['quote']->supplier_id;
    //             $items = $this->purchases_model->getAllQuoteItems($quote_id);
    //             krsort($items);
    //             $c = rand(100000, 9999999);
    //             foreach ($items as $item) {
    //                 $row = $this->site->getProductByID($item->product_id);
    //                 if ($row->type == 'combo') {
    //                     $combo_items = $this->site->getProductComboItems($row->id, $item->warehouse_id);
    //                     foreach ($combo_items as $citem) {
    //                         $crow = $this->site->getProductByID($citem->id);
    //                         if (!$crow) {
    //                             $crow = json_decode('{}');
    //                             $crow->qty = $item->quantity;
    //                         } else {
    //                             unset($crow->details, $crow->product_details, $crow->price);
    //                             $crow->qty = $citem->qty*$item->quantity;
    //                         }
    //                         $crow->base_quantity = $item->quantity;
    //                         $crow->base_unit = $crow->unit ? $crow->unit : $item->product_unit_id;
    //                         $crow->base_unit_cost = $crow->cost ? $crow->cost : $item->unit_cost;
    //                         $crow->unit = $item->product_unit_id;
    //                         $crow->discount = $item->discount ? $item->discount : '0';
    //                         $supplier_cost = $supplier_id ? $this->getSupplierCost($supplier_id, $crow) : $crow->cost;
    //                         $crow->cost = $supplier_cost ? $supplier_cost : 0;
    //                         $crow->tax_rate = $item->tax_rate_id;
    //                         $crow->real_unit_cost = $crow->cost ? $crow->cost : 0;
    //                         $crow->expiry = '';
    //                         $options = $this->purchases_model->getProductOptions($crow->id);
    //                         $units = $this->site->getUnitsByBUID($row->base_unit);
    //                         $tax_rate = $this->site->getTaxRateByID($crow->tax_rate);
    //                         $ri = $this->Settings->item_addition ? $crow->id : $c;

    //                         $pr[$ri] = array('id' => $c, 'item_id' => $crow->id, 'label' => $crow->name . " (" . $crow->code . ")", 'row' => $crow, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
    //                         $c++;
    //                     }
    //                 } elseif ($row->type == 'standard') {
    //                     if (!$row) {
    //                         $row = json_decode('{}');
    //                         $row->quantity = 0;
    //                     } else {
    //                         unset($row->details, $row->product_details);
    //                     }

    //                     $row->id = $item->product_id;
    //                     $row->code = $item->product_code;
    //                     $row->name = $item->product_name;
    //                     $row->base_quantity = $item->quantity;
    //                     $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
    //                     $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
    //                     $row->unit = $item->product_unit_id;
    //                     $row->qty = $item->unit_quantity;
    //                     $row->option = $item->option_id;
    //                     $row->discount = $item->discount ? $item->discount : '0';
    //                     $supplier_cost = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
    //                     $row->cost = $supplier_cost ? $supplier_cost : 0;
    //                     $row->tax_rate = $item->tax_rate_id;
    //                     $row->expiry = '';
    //                     $row->real_unit_cost = $row->cost ? $row->cost : 0;
    //                     $options = $this->purchases_model->getProductOptions($row->id);

    //                     $units = $this->site->getUnitsByBUID($row->base_unit);
    //                     $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
    //                     $ri = $this->Settings->item_addition ? $row->id : $c;

    //                     $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
    //                         'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
    //                     $c++;
    //                 }
    //             }
    //             $this->data['quote_items'] = json_encode($pr);
    //         }

    //         $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
    //         $this->data['quote_id'] = $quote_id;
    //         $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
    //         $this->data['categories'] = $this->site->getAllCategories();
    //         $this->data['tax_rates'] = $this->site->getAllTaxRates();
    //         $this->data['warehouses'] = $this->site->getAllWarehouses();
    //         $this->data['ponumber'] = ''; //$this->site->getReference('po');
    //         $this->load->helper('string');
    //         $value = random_string('alnum', 20);
    //         $this->session->set_userdata('user_csrf', $value);
    //         $this->data['csrf'] = $this->session->userdata('user_csrf');
    //         $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases/order_list'), 'page' => lang('purchase_orders')), array('link' => '#', 'page' => lang('add_purchase_order')));
    //         $meta = array('page_title' => lang('add_purchase_order'), 'bc' => $bc);

    //         $this->page_construct('purchases/add_order', $meta, $this->data);

    //     }
    // }
public function add_order($quote_id = null)
    {
        $this->sma->checkPermissions('order', true);
       // $this->sma->print_arrays($_POST);
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        //$this->form_validation->set_rules('reference_no', $this->lang->line("ref_no"), 'required');
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');

        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            $supplier_state = $this->input->post('supplier_state');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $payment_term = $this->input->post('payment_term');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            if(!empty($this->input->post('add_prod')))
                $add_prod = explode(',', $this->input->post('add_prod'));
            else
                $add_prod = NULL;

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
           /* $cgstamt = 0; $sgstamt = 0;  $igstamt = 0;
            $cgstrate = 0; $sgstrate = 0; $igstrate = 0;
            $cessrate = 0; $cessamt = 0;*/

            $i = sizeof($_POST['product']);
            for ($r = 0; $r < $i; $r++) {
                 $cgstamt = 0; $sgstamt = 0;  $igstamt = 0;
                $cgstrate = 0; $sgstrate = 0; $igstrate = 0;
                $cessrate = 0; $cessamt = 0;
                $item_code = $_POST['product'][$r];
                $item_net_cost = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                /*$item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;*/
                $item_tax_rate = $_POST['gst_rate'][$r];
                $gst_code = $_POST['gst_code'][$r];
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity) && $item_quantity > 0) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry <= $today) {
                            $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }
                    // $unit_cost = $real_unit_cost;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount);
                        }
                    }

                    $unit_cost = $this->sma->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost = $unit_cost;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = 0;
                    $cessrate = $_POST['cess_rate'][$r];
                    if($cessrate!='0.00') {
                        //$pr_item_tax += $cessrate;
                        $item_tax_rate += $cessrate;
                    }

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {


                        $ctax = $this->site->calculateTax($product_details, $item_tax_rate, $unit_cost);
                        $item_tax = $ctax['amount'];
                        $tax = $ctax['tax'];

                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

                    }
                    if($cessrate!='0.00') {

                        $tax -= $cessrate;
                    }
                    if($tax != 0){

                        if($this->Settings->state == $supplier_state) {
                            $cgstrate = $tax / 2;
                            $sgstrate = $cgstrate;
                            $igstrate = 0;

                        }else{

                           $igstrate =  $tax;

                            $sgstrate = $cgstrate = 0;
                        }
                        $cgstamt =  $_POST['cgst_amount'][$r];
                        $sgstamt = $_POST['sgst_amount'][$r];
                        $igstamt = $_POST['igst_amount'][$r];
                        $cessamt = $_POST['cess_amount'][$r];

                    }
                    

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    $products[] = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $item_quantity,
                        'quantity_received' => $item_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        'date' => date('Y-m-d', strtotime($date)),
                        'status' => $status,
                        'supplier_part_no' => $supplier_part_no,
                        'gst_code' => $gst_code,
                        'cgst_rate'=> $cgstrate,
                        'sgst_rate'=> $sgstrate,
                        'igst_rate'=> $igstrate,
                        'cgst_amt' => $cgstamt,
                        'sgst_amt'=> $sgstamt,
                        'igst_amt'=> $igstamt,
                        'cess_rate'=> $cessrate,
                        'cess_amt'=> $cessamt

                    );


                    $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }


            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);

                } else {
                    $order_discount = $this->sma->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);

            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
             //new code
            if ($this->pos_settings->rounding) {
              //  echo "Round";die();
                $grand_total = $this->sma->roundNumber($grand_total, $this->pos_settings->rounding);
              
            }
            $data = array('reference_no' => $reference,
                'date' => $date,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'status' => $status,
                'created_by' => $this->session->userdata('user_id'),
                'payment_term' => $payment_term,
                'due_date' => $due_date,

            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products,$add_prod)) {
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line("purchase_order_added"));
            admin_redirect('purchases/order_list');
        } else {

            if ($quote_id) {
                $this->data['quote'] = $this->purchases_model->getQuoteByID($quote_id);
                $supplier_id = $this->data['quote']->supplier_id;
                $items = $this->purchases_model->getAllQuoteItems($quote_id);
                krsort($items);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    if ($row->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($row->id, $item->warehouse_id);
                        foreach ($combo_items as $citem) {
                            $crow = $this->site->getProductByID($citem->id);
                            if (!$crow) {
                                $crow = json_decode('{}');
                                $crow->qty = $item->quantity;
                            } else {
                                unset($crow->details, $crow->product_details, $crow->price);
                                $crow->qty = $citem->qty*$item->quantity;
                            }
                            $crow->base_quantity = $item->quantity;
                            $crow->base_unit = $crow->unit ? $crow->unit : $item->product_unit_id;
                            $crow->base_unit_cost = $crow->cost ? $crow->cost : $item->unit_cost;
                            $crow->unit = $item->product_unit_id;
                            $crow->discount = $item->discount ? $item->discount : '0';
                            $supplier_cost = $supplier_id ? $this->getSupplierCost($supplier_id, $crow) : $crow->cost;
                            $crow->cost = $supplier_cost ? $supplier_cost : 0;
                            $crow->tax_rate = $item->tax_rate_id;
                            $crow->real_unit_cost = $crow->cost ? $crow->cost : 0;
                            $crow->expiry = '';
                            $options = $this->purchases_model->getProductOptions($crow->id);
                            $units = $this->site->getUnitsByBUID($row->base_unit);
                            $tax_rate = $this->site->getTaxRateByID($crow->tax_rate);
                            $ri = $this->Settings->item_addition ? $crow->id : $c;

                            $pr[$ri] = array('id' => $c, 'item_id' => $crow->id, 'label' => $crow->name . " (" . $crow->code . ")", 'row' => $crow, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                            $c++;
                        }
                    } elseif ($row->type == 'standard') {
                        if (!$row) {
                            $row = json_decode('{}');
                            $row->quantity = 0;
                        } else {
                            unset($row->details, $row->product_details);
                        }

                        $row->id = $item->product_id;
                        $row->code = $item->product_code;
                        $row->name = $item->product_name;
                        $row->base_quantity = $item->quantity;
                        $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                        $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                        $row->unit = $item->product_unit_id;
                        $row->qty = $item->unit_quantity;
                        $row->option = $item->option_id;
                        $row->discount = $item->discount ? $item->discount : '0';
                        $supplier_cost = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                        $row->cost = $supplier_cost ? $supplier_cost : 0;
                        $row->tax_rate = $item->tax_rate_id;
                        $row->expiry = '';
                        $row->real_unit_cost = $row->cost ? $row->cost : 0;
                        $options = $this->purchases_model->getProductOptions($row->id);

                        $units = $this->site->getUnitsByBUID($row->base_unit);
                        $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                        $ri = $this->Settings->item_addition ? $row->id : $c;

                        $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                            'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                        $c++;
                    }
                }
                $this->data['quote_items'] = json_encode($pr);
            }

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id'] = $quote_id;
            $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['ponumber'] = ''; //$this->site->getReference('po');
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases/order_list'), 'page' => lang('purchase_orders')), array('link' => '#', 'page' => lang('add_purchase_order')));
            $meta = array('page_title' => lang('add_purchase_order'), 'bc' => $bc);

            $this->page_construct('purchases/add_order', $meta, $this->data);

        }
    }
    public function getProductBySupplier($supplier_id = NULL)
    {
        $rows = $this->purchases_model->getLinkedProductNames($supplier_id);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                $option = false;
                $row->item_tax_method = $row->tax_method;
                $options = $this->purchases_model->getProductOptions($row->id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->purchases_model->getProductOptionByID($option_id) : current($options);
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->cost = 0;
                    $option_id = FALSE;
                }
                $row->option = $option_id;
                $row->supplier_part_no = '';
                if ($opt->cost != 0) {
                    $row->cost = $opt->cost;
                }
                $row->cost = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                $row->real_unit_cost = $row->cost;
                $row->base_quantity = 0;
                $row->base_unit = $row->unit;
                $row->base_unit_cost = $row->cost;
                $row->unit = $row->purchase_unit ? $row->purchase_unit : $row->unit;
                $row->new_entry = 1;
                $row->expiry = '';
                $row->qty = 0;
                $row->quantity_balance = '';
                $row->discount = '0';
                unset($row->details, $row->product_details,  $row->file, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);

                $units = $this->site->getUnitsByBUID($row->base_unit);
                //$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $tax_rate = $row->gst_rate;
                $ri = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $r++;
            }
            //console.log($pr);
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json("false");
        }
    }

    public function edit_purchase_order($id = null)
    {
        $this->sma->checkPermissions('order');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->purchases_model->getPurchaseByID($id);
        if ($inv->status == 'returned' || $inv->return_id || $inv->return_purchase_ref) {
            $this->session->set_flashdata('error', lang('purchase_x_action'));
            admin_redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
        }
        /*if (!$this->session->userdata('edit_right')) {
            $this->sma->view_rights($inv->created_by);
        }*/
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('reference_no', $this->lang->line("ref_no"), 'required');
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
        $this->form_validation->set_rules('ext_pur_ref_no', $this->lang->line("ext_pur_ref_no"), 'required');

        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no');
            $ext_pur_ref_no = $this->input->post('ext_pur_ref_no');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $payment_term = $this->input->post('payment_term');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $supplier_state = $this->input->post('supplier_state');

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
           /* $cgstamt = 0; $sgstamt = 0;  $igstamt = 0;
            $cgstrate = 0; $sgstrate = 0; $igstrate = 0;
            $cessrate = 0; $cessamt = 0;*/

            $partial = false;
            $i = sizeof($_POST['product']);
            for ($r = 0; $r < $i; $r++) {
                $cgstamt = 0; $sgstamt = 0;  $igstamt = 0;
                $cgstrate = 0; $sgstrate = 0; $igstrate = 0;
                $cessrate = 0; $cessamt = 0;
                $item_code = $_POST['product'][$r];
                $item_net_cost = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $quantity_received = $_POST['received_base_quantity'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $quantity_balance = $_POST['quantity_balance'][$r];
                $ordered_quantity = $_POST['ordered_quantity'][$r];
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
                $item_tax_rate = $_POST['gst_rate'][$r];
                $gst_code = $_POST['gst_code'][$r];

                if ($status == 'received' || $status == 'partial') {
                    if ($quantity_received < $item_quantity) {
                        $partial = 'partial';
                    } elseif ($quantity_received > $item_quantity) {
                        $this->session->set_flashdata('error', lang("received_more_than_ordered"));
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $balance_qty =  $quantity_received - ($ordered_quantity - $quantity_balance);
                } else {
                    $balance_qty = $item_quantity;
                    $quantity_received = $item_quantity;
                }
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity) && isset($quantity_balance)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    // $unit_cost = $real_unit_cost;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount);
                        }
                    }

                    $unit_cost = $this->sma->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost = $unit_cost;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = 0;
                    $cessrate = $_POST['cess_rate'][$r];
                    if($cessrate!='0.00') {
                        //$pr_item_tax += $cessrate;
                        $item_tax_rate += $cessrate;
                    }
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {

                        $ctax = $this->site->calculateTax($product_details, $item_tax_rate, $unit_cost);
                        $item_tax = $ctax['amount'];
                        $tax = $ctax['tax'];

                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

                    }
                    if($cessrate!='0.00') {

                        $tax -= $cessrate;
                    }
                    if($tax!=0){

                        if($this->Settings->state == $supplier_state) {
                            $cgstrate = $tax / 2;
                            $sgstrate = $cgstrate;
                            $igstrate = 0;

                        }else{

                            $igstrate =  $tax;
                            $sgstrate = $cgstrate = 0;

                        }
                        $cgstamt =  $_POST['cgst_amount'][$r];
                        $sgstamt = $_POST['sgst_amount'][$r];
                        $igstamt = $_POST['igst_amount'][$r];

                        $cessamt = $_POST['cess_amount'][$r];
                    }
                    

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    $items[] = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $balance_qty,
                        'quantity_received' => $quantity_received,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        'supplier_part_no' => $supplier_part_no,
                        'date' => date('Y-m-d', strtotime($date)),
                        'gst_code' => $gst_code,
                        'cgst_rate'=> $cgstrate,
                        'sgst_rate'=> $sgstrate,
                        'igst_rate'=> $igstrate,
                        'cgst_amt' => $cgstamt,
                        'sgst_amt'=> $sgstamt,
                        'igst_amt'=> $igstamt,
                        'cess_rate'=> $cessrate,
                        'cess_amt'=> $cessamt
                    );

                    $total += $item_net_cost * $item_unit_quantity;
                }
            }
            if ($status == 'received' || $status == 'partial') {
                $status = $partial ? $partial : 'received';
            }
            if (empty($items)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                foreach ($items as $item) {
                    $item["status"] = $status;
                    $products[] = $item;
                }
                krsort($products);
            }

            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);

                } else {
                    $order_discount = $this->sma->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            //new code
            if ($this->pos_settings->rounding) {
              //  echo "Round";die();
                $grand_total = $this->sma->roundNumber($grand_total, $this->pos_settings->rounding);
              
            }
            $data = array('reference_no' => $reference,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'status' => $status,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
                'payment_term' => $payment_term,
                'due_date' => $due_date,
                'ext_pur_ref_no' => $ext_pur_ref_no,
            );
            if ($date) {
                $data['date'] = $date;
            }

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);
        }
        
        if ($this->form_validation->run() == true && $this->purchases_model->updatePurchase($id, $data, $products)) {
            if($data['status']=='received' || $data['status']=='partial')
            {
                if(!empty($inv->ipo_reference_no))
                {
                    $reference_no = explode(",",$inv->ipo_reference_no);
                }
                foreach($reference_no as $ref_no)
                {
                    $this->db->update('internal_purchases',array('status' => 'Processed'),array('reference_no' => $ref_no));
                }
            }
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line("purchase_order_updated"));
            admin_redirect('purchases');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $inv->status = 'received';
            $this->data['inv'] = $inv;
            if ($this->Settings->disable_editing) {
                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-'.$this->Settings->disable_editing.' days'))) {
                    $this->session->set_flashdata('error', sprintf(lang("purchase_x_edited_older_than_x_days"), $this->Settings->disable_editing));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }
            $inv_items = $this->purchases_model->getAllPurchaseItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->sma->hrsd($item->expiry) : '');
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->oqty = $item->quantity;
                $row->supplier_part_no = $item->supplier_part_no;
                $row->received = $item->quantity_received ? $item->quantity_received : $item->quantity;
                $row->quantity_balance = $item->quantity_balance + ($item->quantity-$row->received);
                $row->discount = $item->discount ? $item->discount : '0';
                $options = $this->purchases_model->getProductOptions($row->id);
                $row->option = $item->option_id;
                $row->real_unit_cost = $item->real_unit_cost;
                $row->cost = $this->sma->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                $row->tax_rate = $item->tax_rate_id;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $c++;
            }

            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $this->data['purchase'] = $this->purchases_model->getPurchaseByID($id);
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->session->set_userdata('remove_pols', 1);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases/order_list'), 'page' => lang('purchase_orders')), array('link' => '#', 'page' => lang('Convert_GRN_Purchase')));
            $meta = array('page_title' => lang('Convert_GRN_Purchase'), 'bc' => $bc);

            $this->page_construct('purchases/edit_purchase_order', $meta, $this->data);

        }
    }

    public function purchase_order_pdf($purchase_id = null, $view = null, $save_bufffer = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['inv'] = $inv;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;
        $this->data['biller'] = $this->site->getCompanyByID($this->Settings->default_biller);
        $name = $this->lang->line("purchase") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'purchases/purchase_order_pdf', $this->data, true);
        if (! $this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'purchases/purchase_order_pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer, $this->data['supplier']->invoice_footer);
        }

        else {
            //echo $html;
            $this->sma->generate_pdf($html, $name, false, $this->data['supplier']->invoice_footer,$margin_bottom = null, $header = null, $margin_top = null, $orientation = 'P');
        }

    }

    //For internal purchase order
    public function add_po_internal($quote_id=NULL)
    {
        $this->sma->checkPermissions('requisition',true);

        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        //$this->form_validation->set_rules('reference_no', $this->lang->line("ref_no"), 'required');
       // $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
	    if(isset($_POST['add_pruchase'])){
	    	$this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
		    //$this->form_validation->set_rules('brand', $this->lang->line("brand"), 'required');
	    }
     

        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            $supplier_state = $this->input->post('supplier_state');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $payment_term = $this->input->post('payment_term');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $cgstamt = 0; $sgstamt = 0;  $igstamt = 0;
            $cgstrate = 0; $sgstrate = 0; $igstrate = 0;
            $cessrate = 0; $cessamt = 0;
            $add_prod = NULL;
            $i = sizeof($_POST['product']);
            for ($r = 0; $r < $i; $r++) {
                $item_code = $_POST['product'][$r];
                $item_net_cost = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                /*$item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;*/
                $item_tax_rate = $_POST['gst_rate'][$r];
                $gst_code = $_POST['gst_code'][$r];
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry <= $today) {
                            $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }
                    // $unit_cost = $real_unit_cost;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount);
                        }
                    }

                    $unit_cost = $this->sma->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost = $unit_cost;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = "";
                    $cessrate = $_POST['cess_rate'][$r];
                    if($cessrate!='0.00') {
                        //$pr_item_tax += $cessrate;
                        $item_tax_rate += $cessrate;
                    }

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {


                        $ctax = $this->site->calculateTax($product_details, $item_tax_rate, $unit_cost);
                        $item_tax = $ctax['amount'];
                        $tax = $ctax['tax'];

                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

                    }
                    if($cessrate!='0.00') {

                        $tax -= $cessrate;
                    }
                    if($tax != 0){

                        if($this->Settings->state == $supplier_state) {
                            $cgstrate = $tax / 2;
                            $sgstrate = $cgstrate;
                            $igstrate = 0;

                        }else{

                           $igstrate =  $tax;

                            $sgstrate = $cgstrate = 0;
                        }
                        $cgstamt =  $_POST['cgst_amount'][$r];
                        $sgstamt = $_POST['sgst_amount'][$r];
                        $igstamt = $_POST['igst_amount'][$r];
                        $cessamt = $_POST['cess_amount'][$r];

                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);
                    $add_prod[] = $product_details->id;
                    $products[] = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $item_quantity,
                        'quantity_received' => $item_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        'date' => date('Y-m-d', strtotime($date)),
                        'status' => $status,
                        'supplier_part_no' => $supplier_part_no,
                        'gst_code' => $gst_code,
                        'cgst_rate'=> $cgstrate,
                        'sgst_rate'=> $sgstrate,
                        'igst_rate'=> $igstrate,
                        'cgst_amt' => $cgstamt,
                        'sgst_amt'=> $sgstamt,
                        'igst_amt'=> $igstamt,
                        'cess_rate'=> $cessrate,
                        'cess_amt'=> $cessamt

                    );


                    $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }


            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);

                } else {
                    $order_discount = $this->sma->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);

            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            $data = array('reference_no' => $reference,
                'date' => $date,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'status' => $status,
                'created_by' => $this->session->userdata('user_id'),
                'payment_term' => $payment_term,
                'due_date' => $due_date,
            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            $brand_data = $this->purchases_model->getBrandDataByID($this->input->post('brand'));
            $ipo_reference_no ='';
            foreach($brand_data as $brand)
            {
                if(empty($ipo_reference_no))
                    $ipo_reference_no = $brand->reference_no;
                else
                $ipo_reference_no = $ipo_reference_no.','.$brand->reference_no;
            }
            $data['ipo_reference_no'] = $ipo_reference_no;
        }
        //print_r($add_prod);die;
        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products,$add_prod)) {            
            
            /*$brand_data = $this->purchases_model->getBrandDataByID($this->input->post('brand'));
            foreach($brand_data as $brand)
            {
                $purchase_id[]=$brand->id;
            }*/

            if($_POST['inv_id'])
            {
                $invid = explode(',',$_POST['inv_id']);
                foreach ($invid as $id) {
                    $purchase_id[] = $id;
                    
                }
            }

            $this->db->where_in('id', $purchase_id);
            $this->db->update('internal_purchases', array('status' => 'ordered'));
            
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line("purchase_added"));
            admin_redirect('purchases/list_po_internal');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            if (!empty($_POST['val'])) {  
            $brand_id ='';
                    foreach ($_POST['val'] as $id) {
                        $inv_id[] = $id;
                        $inv_data = $this->purchases_model->getInternalPurchaseByID($id);

                        if($brand_id =='')
                            $brand_id = $inv_data->brand_id;
                        else
                            if($brand_id != $inv_data->brand_id)
                            {
                                $this->session->set_flashdata('error', $this->lang->line("brands are not same"));
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                        if($inv_data->status!='Required')
                        {
                            $this->session->set_flashdata('error', $this->lang->line("This PR allready converted to PO"));
                                redirect($_SERVER["HTTP_REFERER"]);
                        }
                        $inv_items[] = $this->purchases_model->getAllInternalPurchaseItems($id);

                    }
                 //   echo '<pre>';  print_r($inv_items); echo '</pre>';

                }
                else if($_POST['inv_id'])
                {

                    //$brand_id = $_POST['brand'];
                    $invid = explode(',',$_POST['inv_id']);
                    foreach ($invid as $id) {
                        $inv_id[] = $id;
                        $inv_items[] = $this->purchases_model->getAllInternalPurchaseItems($id);
                    }
                }
                else
                {
                    $this->session->set_flashdata('error', $this->lang->line("no_purchase_selected"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                   
                    krsort($inv_items);
            $c = rand(100000, 9999999);
	        $product = array();
            foreach ($inv_items as $item) {
                for($i=0 ; $i < sizeof($item); $i++)
            {
	            if(array_key_exists($item[$i]->product_id,$product)){

		            $row = $this->site->getProductByID($item[$i]->product_id);
		            $row->qty = $item[$i]->quantity +  $product[$row->id];
		            $row->oqty = $item[$i]->quantity +  $product[$row->id];
                    $row->base_quantity = $item[$i]->quantity +  $product[$row->id];
	            }
	            else{
                    $row = $this->site->getProductByID($item[$i]->product_id);
					/*if(empty($row))
					{
						echo "#pid".$item[$i]->product_id;
						echo "#product".json_encode($item[$i]);
					}*/
		            $row->qty = $item[$i]->unit_quantity;
		            $row->oqty = $item[$i]->quantity;
                    $row->base_quantity = $item[$i]->quantity;

	            }
	           // $product[] = $row->id;
	            $product[$row->id] = $row->qty;
                $row->expiry = (($item[$i]->expiry && $item[$i]->expiry != '0000-00-00') ? $this->sma->hrsd($item[$i]->expiry) : '');
                
                $row->base_unit = $row->unit ? $row->unit : $item[$i]->product_unit_id;
                $row->base_unit_cost = $row->cost ? $row->cost : $item[$i]->unit_cost;
                $row->unit = $item[$i]->product_unit_id;

                $row->supplier_part_no = $item[$i]->supplier_part_no;
                $row->received = $item[$i]->quantity_received ? $item[$i]->quantity_received : $item[$i]->quantity;
                $row->quantity_balance = $item[$i]->quantity_balance + ($item[$i]->quantity-$row->received);
                $row->discount = $item[$i]->discount ? $item[$i]->discount : '0';
                $options = $this->purchases_model->getProductOptions($row->id);
                $row->option = $item[$i]->option_id;
                $row->real_unit_cost = $item[$i]->real_unit_cost;
                $row->cost = $this->sma->formatDecimal($item[$i]->net_unit_cost + ($item[$i]->item_discount / $item[$i]->quantity));
                $row->tax_rate = $item[$i]->tax_rate_id;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $c++;
            }
        }

            
            $this->data['quote_id'] = $quote_id;
            $this->data['inv_items'] = json_encode($pr);
            $this->data['inv_id'] = /*json_encode($inv_id);*/implode(',', $inv_id);
            $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['brands'] = $this->site->getAllBrands();
            $this->data['brand'] = $brand_id;
            $this->data['ponumber'] = ''; //$this->site->getReference('po');
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('add_po_internal')));
            $meta = array('page_title' => lang('add_po_internal'), 'bc' => $bc);

            $this->page_construct('purchases/add_po_internal', $meta, $this->data);

        }
    }

    public function getInternalPOproductsByBrand($brand_id)
    {
        $branddata =$this->purchases_model->getBrandDataByID($brand_id);
        if(!empty($branddata))
        {
            $purchase_items = $this->purchases_model->getInternalPOProducts($brand_id);
            if($purchase_items)
            {
            foreach ($purchase_items as $item) {
                $c = str_replace(".", "", microtime(true));
                $r = 0;
                    $row = $this->site->getProductByID($item->product_id);
                    $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->sma->hrsd($item->expiry) : '');
                    $row->base_quantity = $item->Qty;
                    $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                    $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                    $row->unit = $item->product_unit_id;
                    $row->qty = $item->Qty;
                    $row->oqty = $item->Qty;
                    $row->supplier_part_no = $item->supplier_part_no ? $item->supplier_part_no :'';
                    $row->received = $item->quantity_received ? $item->quantity_received : $item->quantity;
                    $row->quantity_balance = $item->quantity_balance + ($item->quantity-$row->received);
                    $row->discount = $item->discount ? $item->discount : '0';
                    $options = $this->purchases_model->getProductOptions($row->id);
                    $row->option = $item->option_id;
                    $row->real_unit_cost = $item->real_unit_cost;
                    $row->cost = $this->sma->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                    $row->tax_rate = $item->tax_rate_id;
                    unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                    $units = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $ri = $this->Settings->item_addition ? $row->id : $c;

                    $pr[$ri] = array( 'id' => ($c + $r),'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                    $r++;
                }
                $this->sma->send_json($pr);
            } else {
                $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
            }
        }
        else
        {
            $this->sma->send_json('false');
        }
    }

     public function list_po_internal($branch_code = null)
    {
        $this->sma->checkPermissions('requisition');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        //if ($this->Owner || $this->Admin) {
            $this->data['branches'] = $this->purchases_model->getAllBranches($this->Settings->branch_code);
            $this->data['branch_code'] = $branch_code;
            $this->data['branch'] = $branch_code ? $this->site->getBranchByCode($branch_code) : null;
            $this->data['brands'] = $this->site->getAllBrands();
        //} 


        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('purchases')));
        $meta = array('page_title' => lang('purchases'), 'bc' => $bc);
        $this->page_construct('purchases/list_po_internal', $meta, $this->data);

    }

    public function getPurchaseOrderInternal($branch_code= null)
    {
        $this->sma->checkPermissions('index');
        $brand = $this->input->get('brand') ? $this->input->get('brand') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }

        /*$convert_link = anchor('admin/storesales/add/0/$1' , '<i class="fa fa-heart"></i> ' . lang('create_sale'));*/

        $convert_link ="<a class=\"tip\" title='" . lang('create_sale') . "' href='" . admin_url('purchases/getCostMargin/$1') . "' data-toggle='modal' data-target='#myModal'><i class='fa fa-heart'></i>".lang('create_sale');
        
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $convert_link . '</li>
        </ul>
    </div></div>';
        

        $this->load->library('datatables');
        if ($branch_code) {
            $this->datatables
                ->select("internal_purchases.id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, internal_branches.name,internal_purchases.status")
                ->from('internal_purchases')
                ->join('internal_branches','internal_branches.code=internal_purchases.branch_code')
                //->where('status !=', 'completed')
                ->where('branch_code', $branch_code);
                if ($brand) 
                {
                    $this->datatables->where('internal_purchases.brand_id', $brand);
                }
                if ($start_date)
                {
                    $this->db->where($this->db->dbprefix('internal_purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
                }
        } else {
            $this->datatables
                ->select("internal_purchases.id,DATE_FORMAT(date, '%Y-%m-%d %T') as date,reference_no,internal_branches.name,internal_purchases.status")
                ->from('internal_purchases')
                ->join('internal_branches','internal_branches.code=internal_purchases.branch_code');
                //->where('status !=', 'completed'); 
                if ($brand) 
                {
                    $this->datatables->where('internal_purchases.brand_id', $brand);
                }
                if ($start_date)
                {
                    $this->db->where($this->db->dbprefix('internal_purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
                }

        }
        // $this->datatables->where('status !=', 'returned');
        
        $this->datatables->add_column("Actions", $action, "internal_purchases.id");
        echo $this->datatables->generate();
    }

    public function getTotal()
    {
        $id = $this->input->get('id');
        if($id)
        {
            $return =$this->purchases_model->getTotalReturn($id);
            $this->sma->send_json(array('total' => $return->total));
        }
        else
        {
            $this->sma->send_json(array('total' => '0'));
        }
    }

    function getReturnAmount()
    {   
        $reference_no = $this->input->get('reference_no');
        $row = $this->purchases_model->getReturnPurchaseByReference($reference_no);
        $this->sma->send_json(array('return_purchase_total' => $row->return_purchase_total));
    }

    function brand_suggestions($term = NULL, $limit = NULL)
    {   
        // $this->sma->checkPermissions('index');
        if ($this->input->get('term')) {
            $term = $this->input->get('term', TRUE);
        }
        $limit = $this->input->get('limit', TRUE);
        $rows['results'] = $this->purchases_model->getBrandSuggestions($term, $limit);
        $this->sma->send_json($rows);
    }

    function getBrand($id = NULL)
    {
        // $this->sma->checkPermissions('index');
        $row = $this->site->getBrandByID($id);
        $this->sma->send_json(array(array('id' => $row->id, 'text' => $row->name)));
    }

    public function internal_prchase_modal_view($purchase_id = null)
    {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getInternalPurchaseByID($purchase_id);
        $this->data['rows'] = $this->purchases_model->getAllInternalPurchaseItems($purchase_id);
        $this->data['branch'] = $this->purchases_model->getBranchByCode($inv->branch_code);
        $this->data['state'] = $this->purchases_model->getStateByID($this->data['branch']->state);
        $this->data['warehouse'] = $this->site->getWarehouseByID($this->Settings->default_warehouse);
        $this->data['inv'] = $inv;
        $this->data['biller'] = $this->site->getCompanyByID($this->Settings->default_biller);
        $this->load->view($this->theme . 'purchases/internal_purchase_modal_view', $this->data);

    }

    public function edit_order($id = null)
    {
        $this->sma->checkPermissions('order');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->purchases_model->getPurchaseByID($id);
       
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('reference_no', $this->lang->line("ref_no"), 'required');
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');

        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $payment_term = $this->input->post('payment_term');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $supplier_state = $this->input->post('supplier_state');

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            /*$cgstamt = 0; $sgstamt = 0;  $igstamt = 0;
            $cgstrate = 0; $sgstrate = 0; $igstrate = 0;
            $cessrate = 0; $cessamt = 0;*/

            $partial = false;
            $i = sizeof($_POST['product']);
           /* echo '<pre>';
            print_r($_POST);
            echo '</pre>';
            die;*/
            for ($r = 0; $r < $i; $r++) {

                $cgstamt = 0; $sgstamt = 0;  $igstamt = 0;
                $cgstrate = 0; $sgstrate = 0; $igstrate = 0;
                $cessrate = 0; $cessamt = 0;

                $item_code = $_POST['product'][$r];
                $item_net_cost = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $quantity_received = 0;
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $quantity_balance = $_POST['quantity_balance'][$r];
                $ordered_quantity = 0;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
                $item_tax_rate = $_POST['gst_rate'][$r];
                $gst_code = $_POST['gst_code'][$r];

                if ($status == 'received' || $status == 'partial') {
                    if ($quantity_received < $item_quantity) {
                        $partial = 'partial';
                    } elseif ($quantity_received > $item_quantity) {
                        $this->session->set_flashdata('error', lang("received_more_than_ordered"));
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $balance_qty =  $quantity_received - ($ordered_quantity - $quantity_balance);
                } else {
                    $balance_qty = $item_quantity;
                    $quantity_received = $item_quantity;
                }
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity) && isset($quantity_balance)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    // $unit_cost = $real_unit_cost;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount);
                        }
                    }

                    $unit_cost = $this->sma->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost = $unit_cost;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = 0;
                    $cessrate = $_POST['cess_rate'][$r];
                    if($cessrate!='0.00') {
                        //$pr_item_tax += $cessrate;
                        $item_tax_rate += $cessrate;
                    }
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {

                        $ctax = $this->site->calculateTax($product_details, $item_tax_rate, $unit_cost);
                        $item_tax = $ctax['amount'];
                        $tax = $ctax['tax'];

                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

                    }
                    if($cessrate!='0.00') {

                        $tax -= $cessrate;
                    }
                    if($tax!=0){

                        if($this->Settings->state == $supplier_state) {
                            $cgstrate = $tax / 2;
                            $sgstrate = $cgstrate;
                            $igstrate = 0;

                        }else{

                            $igstrate =  $tax;
                            $sgstrate = $cgstrate = 0;

                        }
                        $cgstamt =  $_POST['cgst_amount'][$r];
                        $sgstamt = $_POST['sgst_amount'][$r];
                        $igstamt = $_POST['igst_amount'][$r];

                        $cessamt = $_POST['cess_amount'][$r];
                    }
                    

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    $items[] = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $balance_qty,
                        'quantity_received' => $quantity_received,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        'supplier_part_no' => $supplier_part_no,
                        'date' => date('Y-m-d', strtotime($date)),
                        'gst_code' => $gst_code,
                        'cgst_rate'=> $cgstrate,
                        'sgst_rate'=> $sgstrate,
                        'igst_rate'=> $igstrate,
                        'cgst_amt' => $cgstamt,
                        'sgst_amt'=> $sgstamt,
                        'igst_amt'=> $igstamt,
                        'cess_rate'=> $cessrate,
                        'cess_amt'=> $cessamt
                    );

                    $total += $item_net_cost * $item_unit_quantity;
                }
            }
            //echo " ".json_encode($items);die();
            if ($status == 'received' || $status == 'partial') {
                $status = $partial ? $partial : 'received';
            }
            if (empty($items)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                foreach ($items as $item) {
                    $item["status"] = $status;
                    $products[] = $item;
                }
                krsort($products);
            }

            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);

                } else {
                    $order_discount = $this->sma->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
             //new code
             if ($this->pos_settings->rounding) {
              //  echo "Round";die();
                $grand_total = $this->sma->roundNumber($grand_total, $this->pos_settings->rounding);
              
            }
            $data = array('reference_no' => $reference,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'status' => $status,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
                'payment_term' => $payment_term,
                'due_date' => $due_date,
            );
            if ($date) {
                $data['date'] = $date;
            }

            /*if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }*/

            // $this->sma->print_arrays($data, $products);
        }
                
        if ($this->form_validation->run() == true && $this->purchases_model->updatePurchase($id, $data, $products)) {
            if($data['status']=='Canceled')
            {
                if(!empty($this->input->post('ipr_ref_no')))
                {
                    $this->db->update('internal_purchases',array('status'=>'Required'),array('reference_no' => $this->input->post('ipr_ref_no')));
                }
            }
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line("purchase_order_updated"));
            admin_redirect('purchases/order_list');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            //$inv->status = 'received';
            $this->data['inv'] = $inv;
            if ($this->Settings->disable_editing) {
                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-'.$this->Settings->disable_editing.' days'))) {
                    $this->session->set_flashdata('error', sprintf(lang("purchase_x_edited_older_than_x_days"), $this->Settings->disable_editing));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }
            $inv_items = $this->purchases_model->getAllPurchaseItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->sma->hrsd($item->expiry) : '');
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->oqty = $item->quantity;
                $row->price = $item->price;
                $row->supplier_part_no = $item->supplier_part_no;
                $row->received = $item->quantity_received ? $item->quantity_received : $item->quantity;
                $row->quantity_balance = $item->quantity_balance + ($item->quantity-$row->received);
                $row->discount = $item->discount ? $item->discount : '0';
                $options = $this->purchases_model->getProductOptions($row->id);
                $row->option = $item->option_id;
                $row->real_unit_cost = $item->real_unit_cost;
                $row->cost = $this->sma->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                $row->tax_rate = $item->tax_rate_id;
                unset($row->details, $row->product_details, $row->file, $row->product_group_id);
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $c++;
            }

            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $this->data['purchase'] = $this->purchases_model->getPurchaseByID($id);
	        $supplier_id = $this->data['purchase']->supplier_id;
	        $supplier_details = $this->site->getCompanyByID($supplier_id);
	        $this->data['supplier_id'] = $supplier_id;
	        $this->data['supplier_state'] = $supplier_details->state;
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->session->set_userdata('remove_pols', 1);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases/order_list'), 'page' => lang('purchase_orders')), array('link' => '#', 'page' => lang('edit_purchase_order')));
            $meta = array('page_title' => lang('edit_purchase_order'), 'bc' => $bc);

            $this->page_construct('purchases/edit_order', $meta, $this->data);

        }
    }

    function getCostMargin($id = NULL)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['purchase_id'] = $id;
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'purchases/getCostMargin', $this->data);
        
    }

    function updateAlertQuantity( )
    {
        $alert_quantity = $this->input->get('alert_quantity', TRUE);
        $product_id = $this->input->get('product_id', TRUE);
        
        if($this->db->update('products',array('alert_quantity' => $alert_quantity),array('id' => $product_id)))
        {
            $this->sma->send_json('true');
        }
        else
        {
            $this->sma->send_json('false');
        }
        
    }

    public function display_price($subtotal = null)
    {
        $this->sma->checkPermissions();
        $this->data['subtotal'] = $subtotal;
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'purchases/display_price', $this->data);
    }




      public function add_payment_multi($id = null)
    {
       

        $purchaseids=$this->input->post("pids");

      //  echo "1".json_encode($purchaseids);
        //echo "".;
       // echo "  ".json_encode($this->purchases_model->getMultiPurchaseByID($purchaseids));
        //die();
        $this->sma->checkPermissions('payments', true);
        $this->load->helper('security');
       
       /* $purchase = $this->purchases_model->getPurchaseByID($id);
        if ($purchase->payment_status == 'paid' && $purchase->grand_total == $purchase->paid) {
            $this->session->set_flashdata('error', lang("purchase_already_paid"));
            $this->sma->md();
        }*/

        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {

            
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            foreach ($purchaseids as $purid) {
                # code... 
                
                  $purchase = $this->purchases_model->getPurchaseByID($purid);
                  log_message("error","Purchase_ID ".$purid);
                if ($purchase->payment_status != 'paid' && $purchase->grand_total != $purchase->paid) {
//echo "PIDS ".json_encode($purid);die();
                     log_message("error","Not paid");
                           $paid=$purchase->grand_total-$purchase->paid;
            
                            $payment = array(
                                'date' => $date,
                                'purchase_id' => $purid,
                                'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay'),
                                'amount' => $paid,
                                'paid_by' => $this->input->post('paid_by'),
                                'cheque_no' => $this->input->post('cheque_no'),
                                'cc_no' => $this->input->post('pcc_no'),
                                'cc_holder' => $this->input->post('pcc_holder'),
                                'cc_month' => $this->input->post('pcc_month'),
                                'cc_year' => $this->input->post('pcc_year'),
                                'cc_type' => $this->input->post('pcc_type'),
                                'note' => $this->sma->clear_tags($this->input->post('note')),
                                'created_by' => $this->session->userdata('user_id'),
                                'transaction_number' => $this->input->post('transaction_id'),
                                'type' => 'sent',
                            );
                            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;


            }
             //for return payment
            $return_payment = NULL;
            $debit = NULL;
            $this->purchases_model->addPayment($payment,$return_payment,$debit);
            $supplier = $this->site->getCompanyByID($purchase->supplier_id);
                $this->db->update('companies', array('balance' => $supplier->balance - $this->input->post('amount-paid')), array('id' => $purchase->supplier_id));                   
                     }
                     else
                     {
                        log_message("error","PAid ");
                     }



            }
            
           
            
            

            $this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
          
            //print_r($return_payment);die;
            

        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
      else {

           
            $inv=$this->purchases_model->getMultiPurchaseByID($purchaseids); 

            if(!empty($inv->id))
            {
            $this->data['inv'] =$inv;
          //  echo "string ".json_encode($purchaseids);
         //   echo "string ".json_encode($this->data['inv']);die();
            $this->data['pur_ids'] =$this->input->post("pids");
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            //$this->data['inv'] = $purchase;
            $this->data['payment_ref'] = ''; //$this->site->getReference('ppay');
            $supplier = "";
          
            /*$this->data['returned_balance'] =$supplier->returned_balance;*/
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'purchases/add_payment_multi', $this->data);
           }
           else
           {
                 $this->session->set_flashdata('error', lang("purchase_already_paid"));
                 echo "0";
           }
        }
    }
	   public function getDebitNotes($warehouse_id = null) {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $edit_link = anchor('admin/purchases/edit_debitnote/$1', '<i class="fa fa-edit"></i> ' . lang('Debit_Note_details'));

        $detail_link = anchor('admin/purchases/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Debit_Note_details'));
        $payments_link = anchor('admin/purchases/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('admin/purchases/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('admin/purchases/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase'), 'data-toggle="modal" data-target="#myModal"');
        // $edit_link = anchor('admin/purchases/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase'));
        $pdf_link = anchor('admin/purchases/debit_note_pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode = anchor('admin/products/print_barcodes/?purchase=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $return_link = anchor('admin/purchases/return_purchase/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_purchase'));
        $delete_link = "<a
           
        href='#' class='po' title='<b>" . $this->lang->line("delete_Debit_Note") . "</b>' data-content=\"<p>"
                . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('purchases/delete_debit/$1/$2') . "'>"
                . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
                . lang('Delete_Debit_note') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
                . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            
            <li>' . $detail_link . '</li>
            <li>' . $pdf_link . '</li>
           <li>' . $email_link . '</li>'
        
                 .
            '<li id="dn_$1" >' . $delete_link . '</li>
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                    ->select("p.id, DATE_FORMAT(p.date, '%Y-%m-%d %T') as date, p.reference_no,p.return_purchase_ref, p.supplier, p.status, p.grand_total, 
                    ((-1*p.grand_total) - (-1*{$this->db->dbprefix('debit')}.total) ) AS 
                    paid, {$this->db->dbprefix('debit')}.total, {$this->db->dbprefix('debit')}.payment_status")
                    ->where('p.status', 'returned')
                    ->from('debit')
                    ->where('p.warehouse_id', $warehouse_id)
                    ->join('purchases p', 'debit.ret_refrence_no=p.return_purchase_ref', 'left');
        } else {
            $this->datatables
                    ->select("p.id, DATE_FORMAT(p.date, '%Y-%m-%d %T') as date, p.reference_no,{$this->db->dbprefix('debit')}.ret_refrence_no, p.supplier, p.status, p.grand_total, 
                    ((-1*p.grand_total) - (-1*{$this->db->dbprefix('debit')}.total) ) AS 
                    paid, {$this->db->dbprefix('debit')}.total, {$this->db->dbprefix('debit')}.payment_status,{$this->db->dbprefix('debit')}.id as did")
                    ->where('p.status', 'returned')
                    ->from('debit')
                    ->join('purchases p', 'debit.ret_refrence_no=p.return_purchase_ref', 'left')

            ;
        }
        // $this->datatables->where('status !=', 'returned');
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "p.id,did");
        $this->datatables->unset_column('id,did');
        echo $this->datatables->generate();
    }

  public function getDebitNotes2($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('admin/purchases/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Debit_Note_details'));
        $payments_link = anchor('admin/purchases/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('admin/purchases/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('admin/purchases/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('admin/purchases/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase'));
        $pdf_link = anchor('admin/purchases/debit_note_pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode = anchor('admin/products/print_barcodes/?purchase=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $return_link = anchor('admin/purchases/return_purchase/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_purchase'));
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_Debit_Note") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('purchases/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('Delete_Debit_note') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $pdf_link . '</li>
           <li>' . $email_link . '</li>
                 
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
           $this->datatables
                ->select("p.id, DATE_FORMAT(p.date, '%Y-%m-%d %T') as date, p.reference_no,p.return_purchase_ref, p.supplier, p.status, p.grand_total, 
                    ((-1*p.grand_total) - (-1*{$this->db->dbprefix('debit')}.total) ) AS 
                    paid, {$this->db->dbprefix('debit')}.total, {$this->db->dbprefix('debit')}.payment_status")
                ->where('p.status', 'returned')
                 ->from('debit')
                ->where('p.warehouse_id', $warehouse_id);
        } else {
           $this->datatables
                ->select("p.id, DATE_FORMAT(p.date, '%Y-%m-%d %T') as date, p.reference_no,{$this->db->dbprefix('debit')}.ret_refrence_no, p.supplier, p.status, p.grand_total, 
                    ((-1*p.grand_total) - (-1*{$this->db->dbprefix('debit')}.total) ) AS 
                    paid, {$this->db->dbprefix('debit')}.total, {$this->db->dbprefix('debit')}.payment_status")
               ->where('p.status', 'returned')
                ->from('debit')
                ->join('purchases p','debit.purchase_id=p.purchase_id','left')

                  ;
        }
        // $this->datatables->where('status !=', 'returned');
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "p.id");
        echo $this->datatables->generate();
    }


     public function delete_debit($id = null,$did=NULL)
    {
        $this->sma->checkPermissions(null, true);

        if($did)
        {
             $dn = $this->purchases_model->getDebitNoteByID($did);

        }
        //returned purchase
        $purchase = $this->site->getPurchaseByID($id);
        
        //returned purchase items
        $purchase_items = $this->site->getAllPurchaseItems($id);


        //purchase from which return taken
        $mainPurchaseID=$purchase->purchase_id; 

       // echo "mainPurchaseID".$mainPurchaseID ;
        $ref=$purchase->return_purchase_ref;

        if($this->purchases_model->checkDebitPaymentByRef($ref,$did))
        {
            
            $this->session->set_flashdata('error', lang('payemnt_found_for_this_debit_note') );
            redirect($_SERVER["HTTP_REFERER"]);
            //die();

        }
        //die();

       // echo "main "+$mainPurchaseID;die();

      if ($this->db->delete('purchase_items', array('purchase_id' => $id))
           && $this->db->delete('purchases', array('id' => $id))
           && $this->db->delete('debit', array('purchase_id' => $mainPurchaseID))
     ) {
         $data = array(
                'return_id' => '',
                'return_purchase_ref' => '',
                'return_purchase_total' => '0',

            );

           $this->db->update('purchases', $data, array('return_id' => $id));
             
            $this->site->reversePurchase($purchase_items); 
    }


         if ($this->input->is_ajax_request()) {
                $this->sma->send_json(array('error' => 0, 'msg' => lang("debit_note_deleted")));
            }


        $this->session->set_flashdata('message', lang('debit_note_deleted'));
        admin_redirect('welcome');
       
    }






    //debit notenew
    //new debit note changes    
    //add debit note
    public function add_debitnote($quote_id = null) {
        $this->sma->checkPermissions('order', true);
        // $this->sma->print_arrays($_POST);
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        //$this->form_validation->set_rules('reference_no', $this->lang->line("ref_no"), 'required');
        // $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');

        $this->session->unset_userdata('csrf_token');

        if ($this->form_validation->run() == true && $this->input->post("add_pruchase")) {

            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rep');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->Settings->default_warehouse;
            $supplier_id = $this->input->post('supplier');
            $supplier_state = $this->input->post('supplier_state');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $payment_term = $this->input->post('payment_term');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $products = array();

            $i = sizeof($_POST['product']);

            for ($r = 0; $r < $i; $r++) {
                $cgstamt = 0;
                $sgstamt = 0;
                $igstamt = 0;
                $cgstrate = 0;
                $sgstrate = 0;
                $igstrate = 0;
                $cessrate = 0;
                $cessamt = 0;
                $item_code = $_POST['product'][$r];
                $item_net_cost = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                //$item_unit_quantity = $_POST['quantity'][$r];
                $item_unit_quantity = (0 - $_POST['quantity'][$r]);
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                /* $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null; */
                $item_tax_rate = $_POST['gst_rate'][$r];
                $gst_code = $_POST['gst_code'][$r];
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                // $item_quantity = $_POST['product_base_quantity'][$r];
                $item_quantity = (0 - $_POST['product_base_quantity'][$r]);

                if($item_quantity > 0)
                {
                    $item_quantity=(-1)*$item_quantity;
                }

                if($item_unit_quantity > 0)
                {
                    $item_unit_quantity=(-1)*$item_unit_quantity;
                }
            
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity) && $item_quantity < 0) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry <= $today) {
                            $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }
                    // $unit_cost = $real_unit_cost;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount);
                        }
                    }

                    $unit_cost = $this->sma->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost = $unit_cost;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = 0;
                    $cessrate = $_POST['cess_rate'][$r];
                    if ($cessrate != '0.00') {
                        //$pr_item_tax += $cessrate;
                        $item_tax_rate += $cessrate;
                    }

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {


                        $ctax = $this->site->calculateTax($product_details, $item_tax_rate, $unit_cost);
                        $item_tax = $ctax['amount'];
                        $tax = $ctax['tax'];

                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);
                    }
                    if ($cessrate != '0.00') {

                        $tax -= $cessrate;
                    }
                    if ($tax != 0) {

                        if ($this->Settings->state == $supplier_state) {
                            $cgstrate = $tax / 2;
                            $sgstrate = $cgstrate;
                            $igstrate = 0;
                        } else {

                            $igstrate = $tax;

                            $sgstrate = $cgstrate = 0;
                        }


                        $cgstamt = 0 - $_POST['cgst_amount'][$r];
                        $sgstamt = 0 - $_POST['sgst_amount'][$r];
                        $igstamt = 0 - $_POST['igst_amount'][$r];
                        $cessamt = 0 - $_POST['cess_amount'][$r];
                    }




                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);
                    //  echo "2w2323 ".json_encode($product_details);
                    $products[] = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $item_quantity,
                        // 'quantity_received' => $item_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        'date' => date('Y-m-d', strtotime($date)),
                        'status' => 'received',
                        'supplier_part_no' => $supplier_part_no,
                        'gst_code' => $gst_code,
                        'cgst_rate' => $cgstrate,
                        'sgst_rate' => $sgstrate,
                        'igst_rate' => $igstrate,
                        'cgst_amt' => $cgstamt,
                        'sgst_amt' => $sgstamt,
                        'igst_amt' => $igstamt,
                        'cess_rate' => $cessrate,
                        'cess_amt' => $cessamt
                    );


                    $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }


            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }


//echo "products ".json_encode($products);die();
            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);
                } else {
                    $order_discount = $this->sma->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);

            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            //new code
            if ($this->pos_settings->rounding) {
                //  echo "Round";die();
                $grand_total = $this->sma->roundNumber($grand_total, $this->pos_settings->rounding);
            }
            $data = array('reference_no' => $reference,
                'date' => $date,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => ($grand_total),
                'status' => "returned",
                'created_by' => $this->session->userdata('user_id'),
                'payment_term' => $payment_term,
                'due_date' => $due_date,
                'return_purchase_ref' => $reference,
            );



            // $this->sma->print_arrays($data, $products);
        }


        if ($this->form_validation->run() == true &&
                $this->purchases_model->addDebitnote($products, $data)) {
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', lang("debit_note_added"));
            admin_redirect("purchases/debit_note_free");
        } else {



            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id'] = $quote_id;
            $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['ponumber'] = ''; //$this->site->getReference('po');
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases/order_list'), 'page' => lang('purchase_orders')), array('link' => '#', 'page' => lang('add_debitnote')));
            $meta = array('page_title' => lang('add_debitnote'), 'bc' => $bc);

            $this->page_construct('purchases/add_debitnote', $meta, $this->data);
        }
    }

    //edit debit note
    public function edit_debitnote($pur_id = null) {



        if($this->purchases_model->checkDebitPayment($pur_id))
        {
            $this->session->set_flashdata('error', lang('payemnt_found_for_this_debit_note') );
            redirect($_SERVER["HTTP_REFERER"]);
        }


        //check permission
        $this->sma->checkPermissions('order', true);
        //echo "d".$pur_id;die();
        //validations
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
        $this->session->unset_userdata('csrf_token');


        //validation starts
        if ($this->input->post("edit_pruchase") && ($this->form_validation->run() == true)) {
            //if no validation errors occurs
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rep');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->Settings->default_warehouse;
            $supplier_id = $this->input->post('supplier');
            $supplier_state = $this->input->post('supplier_state');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $payment_term = $this->input->post('payment_term');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $products = array();
            $i = sizeof($_POST['product']);
            $products = array();


            for ($r = 0; $r < $i; $r++) {
                $cgstamt = 0;
                $sgstamt = 0;
                $igstamt = 0;
                $cgstrate = 0;
                $sgstrate = 0;
                $igstrate = 0;
                $cessrate = 0;
                $cessamt = 0;
                $item_code = $_POST['product'][$r];
                $item_net_cost = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = (0 - $_POST['quantity'][$r]);
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $item_tax_rate = $_POST['gst_rate'][$r];
                $gst_code = $_POST['gst_code'][$r];
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = ( $_POST['product_base_quantity'][$r]);

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity) ) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);

                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry <= $today) {
                            $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }

                    // $unit_cost = $real_unit_cost;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount);
                        }
                    }

                    $unit_cost = $this->sma->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost = $unit_cost;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = 0;
                    $cessrate = $_POST['cess_rate'][$r];
                    if ($cessrate != '0.00') {
                        //$pr_item_tax += $cessrate;
                        $item_tax_rate += $cessrate;
                    }

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {


                        $ctax = $this->site->calculateTax($product_details, $item_tax_rate, $unit_cost);
                        $item_tax = $ctax['amount'];
                        $tax = $ctax['tax'];

                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);
                    }
                    if ($cessrate != '0.00') {

                        $tax -= $cessrate;
                    }
                    if ($tax != 0) {

                        if ($this->Settings->state == $supplier_state) {
                            $cgstrate = $tax / 2;
                            $sgstrate = $cgstrate;
                            $igstrate = 0;
                        } else {

                            $igstrate = $tax;

                            $sgstrate = $cgstrate = 0;
                        }


                        $cgstamt = 0 - $_POST['cgst_amount'][$r];
                        $sgstamt = 0 - $_POST['sgst_amount'][$r];
                        $igstamt = 0 - $_POST['igst_amount'][$r];
                        $cessamt = 0 - $_POST['cess_amount'][$r];
                    }




                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);
                    //  echo "2w2323 ".json_encode($product_details);
                    $products[] = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $item_quantity,
                        'quantity_received' => $item_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        'date' => date('Y-m-d', strtotime($date)),
                        'status' => 'received',
                        'supplier_part_no' => $supplier_part_no,
                        'gst_code' => $gst_code,
                        'cgst_rate' => $cgstrate,
                        'sgst_rate' => $sgstrate,
                        'igst_rate' => $igstrate,
                        'cgst_amt' => $cgstamt,
                        'sgst_amt' => $sgstamt,
                        'igst_amt' => $igstamt,
                        'cess_rate' => $cessrate,
                        'cess_amt' => $cessamt
                    );


                    $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }

            // echo json_encode($products);die;
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);
                } else {
                    $order_discount = $this->sma->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);

            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            //new code
            if ($this->pos_settings->rounding) {

                $grand_total = $this->sma->roundNumber($grand_total, $this->pos_settings->rounding);
            }
            $data = array('reference_no' => $reference,
                'date' => $date,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => ($grand_total),
                'status' => "returned",
                'created_by' => $this->session->userdata('user_id'),
                'payment_term' => $payment_term,
                'due_date' => $due_date,
                'return_purchase_ref' => $reference,
            );
        }


        if (
                (
                $this->form_validation->run() == true &&
                $this->purchases_model->addDebitnote($products, $data, $pur_id)
                )) {


            $this->session->set_flashdata('message', lang("debit_note_updated"));
            admin_redirect("purchases/debit_note_free");
        } else {
            $inv = $this->purchases_model->getDebitNoteByID($pur_id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            //$inv->status = 'received';
            $this->data['inv'] = $inv;
            // if ($this->Settings->disable_editing) {
            //     if ($this->data['inv']->date <= date('Y-m-d', strtotime('-' . $this->Settings->disable_editing . ' days'))) {
            //         $this->session->set_flashdata('error', sprintf(lang("purchase_x_edited_older_than_x_days"), $this->Settings->disable_editing));
            //         redirect($_SERVER["HTTP_REFERER"]);
            //     }
            // }
            $inv_items = $this->purchases_model->getAllDebitItems($pur_id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->sma->hrsd($item->expiry) : '');
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit = $item->product_unit_id;
                $row->qty = (-1) * $item->unit_quantity;
                $row->oqty = (-1) * $item->quantity;
                $row->price = $item->price;
                $row->supplier_part_no = $item->supplier_part_no;
                $row->received = $item->quantity_received ? $item->quantity_received : $item->quantity;
                $row->quantity_balance = $item->quantity_balance + ($item->quantity - $row->received);
                $row->discount = $item->discount ? $item->discount : '0';
                $options = $this->purchases_model->getProductOptions($row->id);
                $row->option = $item->option_id;
                $row->real_unit_cost = $item->real_unit_cost;
                $row->cost = $this->sma->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                $row->tax_rate = $item->tax_rate_id;
                unset($row->details, $row->product_details, $row->file, $row->product_group_id);
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $c++;
            }

            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $pur_id;
            $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $this->data['purchase'] = $this->purchases_model->getPurchaseBydeID($pur_id);
            $supplier_id = $this->data['purchase']->supplier_id;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $this->data['supplier_id'] = $supplier_id;
            $this->data['supplier_state'] = $supplier_details->state;
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->session->set_userdata('remove_pols', 1);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases/order_list'), 'page' => lang('purchase_orders')), array('link' => '#', 'page' => lang('edit_debitnote')));
            $meta = array('page_title' => lang('edit_debitnote'), 'bc' => $bc);

            $this->page_construct('purchases/edit_debitnote', $meta, $this->data);
        }
    }

    //view free debitnotes
    public function debit_note_free($warehouse_id = null) {
        $this->sma->checkPermissions('return_purchases');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('debit_note_free')));
        $meta = array('page_title' => lang('debit_note_free'), 'bc' => $bc);
        $this->page_construct('purchases/debit_note_free', $meta, $this->data);
    }

    //ajax        
    public function getFDebitNotes($warehouse_id = null) {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        

        $edit_link = anchor('admin/purchases/edit_debitnote/$1', '<i class="fa fa-edit"></i> ' . lang('Debit_Note_Edit'));
        $del_link = anchor('admin/purchases/delete_debit_note/$1', '<i class="fa fa-edit"></i> ' . lang('Debit_Note_delete'));

        $detail_link = anchor('admin/purchases/dnview/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Debit_Note_details'));
        $payments_link = anchor('admin/purchases/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('admin/purchases/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('admin/purchases/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase'), 'data-toggle="modal" data-target="#myModal"');
        // $edit_link = anchor('admin/purchases/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase'));
        $pdf_link = anchor('admin/purchases/fdebit_note_pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode = anchor('admin/products/print_barcodes/?purchase=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $return_link = anchor('admin/purchases/return_purchase/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_purchase'));
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_Debit_Note") . "</b>' data-content=\"<p>"
                . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('purchases/delete/$1') . "'>"
                . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
                . lang('Delete_Debit_note') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
                . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
           
            <li>' . $detail_link . '</li>
            <li>' . $pdf_link . '</li>
           <li>' . $email_link . '</li>
                 
            <li>' . $del_link . '</li>
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                    ->select("p.id, DATE_FORMAT(p.date, '%Y-%m-%d %T') as date, p.ret_refrence_no as ref,p.ret_refrence_no as ret_ref, p.supplier, p.status, p.grand_total, 
                     
                    (-1*p.grand_total)-(-1*{$this->db->dbprefix('debit')}.total)  as paid, {$this->db->dbprefix('debit')}.total, {$this->db->dbprefix('debit')}.payment_status")
                    ->where('p.status', 'returned')
                    ->from('debit')
                    ->where('p.warehouse_id', $warehouse_id);
        } else {
             $this->datatables
                     ->select("p.id, DATE_FORMAT(p.date, '%Y-%m-%d %T') as date, p.ret_refrence_no as ref,p.ret_refrence_no as ret_ref, c.name as supplier, p.status, p.grand_total, 
                    
                     (-1*p.grand_total)-(-1*p.total)  as paid, p.total, p.payment_status")
                    ->join('companies c', 'c.id=supplier_id', 'left')
                    ->where('p.purchase_id', 0)
                    ->from('debit p');

           
        }
        // $this->datatables->where('status !=', 'returned');
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "p.id");
        echo $this->datatables->generate();
    }



    //delete debit note
    public function delete_debit_note($id = null) {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if($this->purchases_model->checkDebitPayment($id))
        {
            $this->session->set_flashdata('error', lang('payemnt_found_for_this_debit_note') );
            redirect($_SERVER["HTTP_REFERER"]);
        }

       

        if ($this->purchases_model->deleteDebitNote($id)) {
            //For delete return purchase from debit
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(array('error' => 0, 'msg' => lang("debit_note_deleted")));
            }
            $this->session->set_flashdata('message', lang('debit_note_deleted'));
           redirect($_SERVER["HTTP_REFERER"]);
        }
    }

       public function fdebit_note_modal_view($debit_note_id = null) {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $debit_note_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getDebitNoteByID($debit_note_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['rows'] = $this->purchases_model->getAllDebitItems($debit_note_id);

        //echo "".json_encode($this->data['rows']);die();
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['supplier_state'] = $this->purchases_model->getStateByID($this->data['supplier']->state);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] =NULL;// $this->purchases_model->getPaymentsForPurchase($debit_note_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = NULL;//$inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = NULL;
        $this->data['return_rows'] = NULL;
        $this->data['dn_paid'] = $this->purchases_model->checkDebitPayment($inv->id);
        $this->data['taxdata'] = $this->purchases_model->getTaxdataDebitNote($debit_note_id);
        $this->data['biller'] = $this->site->getCompanyByID($this->Settings->default_biller);
        $this->data['purchase'] = $this->purchases_model->getDebitNoteByID($inv->purchase_id);

        $this->load->view($this->theme . 'purchases/fdebit_note_modal_view', $this->data);
    }



      public function fdebit_note_pdf($purchase_id = null, $view = null, $save_bufffer = null) {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getDebitNoteByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchases_model->getAllDebitItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['inv'] = $inv;
        $this->data['return_purchase'] = NULL;
        $this->data['return_rows'] =  NULL;
        $this->data['biller'] = $this->site->getCompanyByID($this->Settings->default_biller);
        $this->data['taxdata'] = $this->purchases_model->getTaxdataDebitNote($purchase_id);
        $this->data['purchase'] = $this->purchases_model->getDebitNoteByID($inv->purchase_id);
        $name = $this->lang->line("purchase") . "_" . str_replace('/', '_', $inv->ret_refrence_no) . ".pdf";

        $html = $this->load->view($this->theme . 'purchases/fdebit_note_pdf', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'purchases/fdebit_note_pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer, $this->data['supplier']->invoice_footer);
        } else {
            //echo $html;
            $this->sma->generate_pdf($html, $name, false, $this->data['supplier']->invoice_footer, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'P');
        }
    }

    

public function dnview($dn_id = null)
    {
        $this->sma->checkPermissions('index');

        if ($this->input->get('id')) {
            $dn_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $inv=$this->purchases_model->getDebitNoteByID($dn_id);
       
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }

        $this->data['dn_id']=$dn_id;

        $this->data['dn_paid']=false;

         $dn_paid=false;
          //$dn_id=0;

        if($inv->status=='returned')
        {
            
           
            if($inv)
            {
                $dn_paid=$this->purchases_model->checkDebitPaymentByRef($inv->ret_refrence_no,$inv->id);
                $dn_id=$inv->id;
            }
          

        }

         $this->data['dn_id']=$dn_id;
        $this->data['dn_paid']=$dn_paid;
        $this->data['rows'] = $this->purchases_model->getAllDebitItems($inv->id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($inv->id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = NULL;//$inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
        $this->data['return_rows'] = NULL;//$inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;

        //die();

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' => lang('view_purchase_details'), 'bc' => $bc);
        $this->page_construct('purchases/dnview', $meta, $this->data);

    }

    //NEW free debit notes
	public function plist($purchase_id = null)
    {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['rows'] = $this->purchases_model->getItems($purchase_id);
       // $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        //$this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
       // $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        //$this->data['return_purchase'] =  NULL;
       // $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllReturnedItems($purchase_id) : NULL;
        //$this->data['biller'] = $this->site->getCompanyByID($this->Settings->default_biller);
        //$this->data['return_ids'] = $this->purchases_model->getAllReteurnPurchaseList($purchase_id);
        
        $this->load->view($this->theme . 'purchases/list', $this->data);

    }


}
