<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends MY_Controller
{
public function savestocktake()
    {
        
        
        if(1==1)
        {
            //die;
            $id=$this->input->get('product_id'); //product id
            $diff=$this->input->get('diff'); // difference
            $stock_qty=$this->input->get('stock_qty'); // stock qty
            $rc=$this->input->get('rc'); // stock qty
            $prod=$this->site->getProductByID($id);
            if($diff==0)
            {
                echo "3";//equal
                die();
            }

            if($diff<0)
            {
                $operation="Minus";
            }
            elseif ($diff>0) {
                # code...
                $operation="Plus";
            }

            $hdata = array(
                'product_id' =>$id , 
                'stock_qty' =>$stock_qty, 
                'diff_qty' =>$diff, 
                'status' =>"Completed", 
                'user_d' =>$this->session->userdata('user_id'),
                'created_date' => date('Y-m-d H:s:i'),
                'operation' => $operation,
                'cost' => $prod->cost,
                'value' => ($prod->cost*$diff)

            );

           
            
        if($rc!='1')
           {
             
                    if($operation=='Plus')
                    {
                        $isPlus=1;
                        $qtyPlus=$diff;
                        $updated_product_quantity=$stock_qty;
                    }
                    else
                    {
                        $isPlus=0;
                        $qtyMinus=$diff;
                        $updated_product_quantity=$stock_qty;
                    }

     
          
            $product_id=$id;
            


            //update stock take item
            if($isPlus)
            {
                $value=$stock_qty;     
            }
            else
            {

                 $value=$stock_qty; 

                 if($value<0)
                 {
                    echo "2";
                    die();

                 }
            }

       

            //update product qty
            if($isPlus)
            {
                    $this->db->where('id',$product_id);
                    $this->db->set('quantity', 'quantity+'."'".$qtyPlus."'", FALSE);
                    $this->db->update('products');   
            }
            else
            {
                    $this->db->where('id',$product_id);
                    $this->db->set('quantity', 'quantity+'."'".$qtyMinus."'", FALSE);
                    $this->db->update('products');   

            }

             //update wearhouse qty
             if($isPlus)
            {
                    $this->db->where('product_id',$product_id);
                    $this->db->set('quantity', 'quantity+'."'".$qtyPlus."'", FALSE);
                    $this->db->update('warehouses_products');   
            }
            else
            {
                    $this->db->where('product_id',$product_id);
                    $this->db->set('quantity', 'quantity+'."'".$qtyMinus."'", FALSE);
                    $this->db->update('warehouses_products');   

            }
            
          
            
      //get total balance quantity
               $bal_q=$this->db->query("select sum(quantity_balance) as bal from sma_purchase_items where product_id=".$product_id." limit 0,1 ");
               $balq=0;

               if($bal_q->num_rows()>0)
               {
                  $balq=$bal_q->row()->bal;
                  //echo "BAL" .$balq;
               }

                  $finalqty=0;


                    $finalqty=$stock_qty - $balq;
           
                
                    $clause['product_id'] = $product_id;
                    $clause['quantity'] = 0;
                    $clause['item_tax'] = 0;
                    $clause['quantity_balance'] =  $finalqty;
					$clause['quantity_received'] = $finalqty;
                    $clause['status'] = 'received';
                    $clause['option_id'] =  NULL;
                    $clause['warehouse_id'] =  $this->Settings->default_warehouse;
                    $clause['product_code'] =  $prod->code;
                    $clause['product_name'] =  $prod->name;

                    $this->db->insert('purchase_items', $clause);
               


        
           

           echo "1";
           //
            $qr=$this->db->query("select id from sma_st where product_id=".$product_id." and DATE(created_date)='".date("Y-m-d")."' and status='Recheck' limit 0,1");
         
           
            if($qr->num_rows()>0)
            {
               // echo "".$qr->row()->id;die();
                //update
                $this->db->where("id",$qr->row()->id);
                $hdata['status']="Done";
                $this->db->update('st', $hdata);

            }
            else
            {
             $this->db->insert('st', $hdata);
            
            }
             $this->session->set_userdata('remove_qals', 1);
             //
        }
        else{
            echo "1";
            $hdata['status']='Recheck';
            $this->db->insert('st', $hdata);
             $this->session->set_userdata('remove_qals', 1);
        }
       }

       
    }
	 function stocktake($count_id = NULL)
    {
        $this->sma->checkPermissions('adjustments', true);
        $this->form_validation->set_rules('per', lang("percentage"), 'required');
        
        if ($this->input->post('add_adjustment') && $this->form_validation->run() == true) {

            $per = $this->input->post('per');
            //echo "PER ".$per;
            $products = array( );
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            //echo "string".$i;die();
            for ($r = 0; $r < $i; $r++) {

                $product_id = $_POST['product_id'][$r];
                $pr=$this->products_model->getProductByID($product_id);
                $price=round( $pr->mrp - ( $pr->mrp*($per/100)  )  )   ;  
             
               

              $products[] = array(
                    'id' => $product_id,
                    'price' => $price,
                  );
           }

           //echo "setmrp ".json_encode($products);die;        
       if ($this->products_model->setgst($products)) {
            $this->session->set_userdata('remove_qals', 1);
            $this->session->set_flashdata('message', lang("prices_updated"));
            admin_redirect('products/setmrp');
        } else {

           
            $this->data['adjustment_items'] = FALSE;
            $this->data['warehouse_id'] = FALSE;
            $this->data['count_id'] = 0;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
           
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('set_gst')));
            $meta = array('page_title' => lang('setmrp'), 'bc' => $bc);
            $this->page_construct('products/stocktake', $meta, $this->data);

        }
    }
    else
    {
            $this->data['adjustment_items'] = FALSE;
            $this->data['warehouse_id'] = FALSE;
            $this->data['count_id'] = 0;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
           
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('stocktake')));
            $meta = array('page_title' => lang('stocktake'), 'bc' => $bc);
            $this->page_construct('products/stocktake', $meta, $this->data);

    }



}


function stks($rcheck = NULL,$XLS=NULL)
    {

        $this->sma->checkPermissions('adjustments', true);
        $this->form_validation->set_rules('per', lang("percentage"), 'required');
         $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

                $queryString="SELECT p.name as name,p.code ,s.stock_qty,s.diff_qty,s.status,DATE_FORMAT(s.created_date , '%d-%M-%Y %T') as date,u.username as user,s.cost,s.value as value FROM 
             sma_st s
           LEFT JOIN sma_products p ON p.id=s.product_id  
            LEFT JOIN sma_users u ON u.id=s.user_d";
            
            if($rcheck)
            {
                 $queryString.=" WHERE s.status!='Completed'";
            }
            else
            {

            $queryString.=" WHERE s.status='Completed'";
            }

             if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                $queryString .= " and  s.created_date >= '{$start_date}' AND s.created_date < '{$end_date}' ";
               
               
            }
             $query=$this->db->query($queryString);
             $res= array();
             if($query->num_rows()>0) //if rows present
                {
                    //for loop start
                    foreach (($query->result()) as $row) {

                        $res[]=$row
                        ;
                    
                    }
                }
                
            $this->data['adjustment_items'] = FALSE;
            $this->data['warehouse_id'] = FALSE;
            $this->data['count_id'] = 0;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $ttl='completed_stock_takes';

            if($rcheck)
            {
                 $ttl='recheck_stock_takes';
                }

            if(!$XLS)
            {    
           
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang($ttl)));
            $meta = array('page_title' => lang($ttl), 'bc' => $bc);

            $this->data["ttl"]=$ttl;
            $this->data["rc"]=$rcheck;
            $this->data["pending"]=$res;
            $this->page_construct('products/stks', $meta, $this->data);
        }
        else
        {
              if (!empty($res)) {
 $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('products_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('stock_qty'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('Diff'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('status'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('cost'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('value'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('user'));

                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $sAmt = 0;
                $pAmt = 0;
                $bQty = 0;
                $bAmt = 0;
                $pl = 0;
                foreach ($res as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->stock_qty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->diff_qty);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->status);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->cost);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->value);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->date);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->user);
                   
                    $row++;
                }
               
                

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                
                $filename = 'stock_take_report';
                 if ($start_date) {
                    $filename.=" ".$start_date." _ ".$end_date;
                 }
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        }




}
function stks1($rcheck = NULL)
    {
        $this->sma->checkPermissions('adjustments', true);
        $this->form_validation->set_rules('per', lang("percentage"), 'required');
        
                $queryString="SELECT p.name as name,p.code as code ,s.stock_qty,s.diff_qty,s.status,DATE_FORMAT(s.created_date , '%d-%M-%Y %T') as date,u.username as user,
s.cost,s.value				FROM 
             sma_st s
           LEFT JOIN sma_products p ON p.id=s.product_id  
            LEFT JOIN sma_users u ON u.id=s.user_d";
            
            if($rcheck)
            {
                 $queryString.=" WHERE s.status!='Completed'";
            }
            else
            {

            $queryString.=" WHERE s.status='Completed'";
            }
             $query=$this->db->query($queryString);
             $res= array();
             if($query->num_rows()>0) //if rows present
                {
                    //for loop start
                    foreach (($query->result()) as $row) {

                        $res[]=$row
                        ;
                    
                    }
                }
                
            $this->data['adjustment_items'] = FALSE;
            $this->data['warehouse_id'] = FALSE;
            $this->data['count_id'] = 0;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $ttl='completed_stock_takes';

            if($rcheck)
            {
                 $ttl='recheck_stock_takes';
                }
           
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang($ttl)));
            $meta = array('page_title' => lang($ttl), 'bc' => $bc);

            $this->data["ttl"]=$ttl;
            $this->data["pending"]=$res;
            $this->page_construct('products/stks', $meta, $this->data);




}
    function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->lang->admin_load('products', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('products_model');
		$this->load->admin_model('purchases_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '4096';
        $this->popup_attributes = array('width' => '900', 'height' => '600', 'window_name' => 'sma_popup', 'menubar' => 'yes', 'scrollbars' => 'yes', 'status' => 'no', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0');
    }

    function index($warehouse_id = NULL)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }

        $this->data['supplier'] = $this->input->get('supplier') ? $this->site->getCompanyByID($this->input->get('supplier')) : NULL;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('products')));
        $meta = array('page_title' => lang('products'), 'bc' => $bc);
        $this->page_construct('products/index', $meta, $this->data);
    }

    function getProducts($warehouse_id = NULL)
    {
        $this->sma->checkPermissions('index', TRUE);
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('admin/products/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('product_details'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_product") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete1' id='a__$1' href='" . admin_url('products/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_product') . "</a>";
        $single_barcode = anchor('admin/products/print_barcodes/$1', '<i class="fa fa-print"></i> ' . lang('print_barcode_label'));
        // $single_label = anchor_popup('products/single_label/$1/' . ($warehouse_id ? $warehouse_id : ''), '<i class="fa fa-print"></i> ' . lang('print_label'), $this->popup_attributes);
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
		<ul class="dropdown-menu pull-right" role="menu">
			<li>' . $detail_link . '</li>
			
			<li><a href="' . admin_url('products/edit/$1') . '"><i class="fa fa-edit"></i> ' . lang('edit_product') . '</a></li>';
        if ($warehouse_id) {
            $action .= '<li><a href="' . admin_url('products/set_rack/$1/' . $warehouse_id) . '" data-toggle="modal" data-target="#myModal"><i class="fa fa-bars"></i> '
                . lang('set_rack') . '</a></li>';
        }
        $action .= '<li><a href="' . admin_url() . 'assets/uploads/$2" data-type="image" data-toggle="lightbox"><i class="fa fa-file-photo-o"></i> '
            . lang('view_image') . '</a></li>
			<li>' . $single_barcode . '</li>
			<li class="divider"></li>
			<li>' . $delete_link . '</li>
			</ul>
		</div></div>';
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid,  {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price,gst_rate, COALESCE(wp.quantity, 0) as quantity, {$this->db->dbprefix('products')}.is_active as unit, wp.rack as rack, alert_quantity,mrp", "mrp" , "weight", FALSE)
                ->from('products');
            if ($this->Settings->display_all_products) {
                $this->datatables->join("( SELECT product_id, quantity, rack from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) wp", 'products.id=wp.product_id', 'left');
            } else {
                $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
                    ->where('wp.warehouse_id', $warehouse_id)
                    ->where('wp.quantity !=', 0);
            }
            $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left');
            // ->group_by("products.id");
        } else {
            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid,  {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price,gst_rate, COALESCE(quantity, 0) as quantity, {$this->db->dbprefix('products')}.is_active as unit, '' as rack, alert_quantity,mrp", "mrp", "weight", FALSE)
                ->from('products')
                ->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left');
              //  ->group_by("products.id");
        }
        if (!$this->Owner && !$this->Admin) {
            if (!$this->GP['products-cost']) {
              //  $this->datatables->unset_column("cost");
            }
            if (!$this->GP['products-price']) {
               // $this->datatables->unset_column("price");
            }
        }
        if ($supplier) {
            $this->datatables->where('supplier1', $supplier)
                ->or_where('supplier2', $supplier)
                ->or_where('supplier3', $supplier)
                ->or_where('supplier4', $supplier)
                ->or_where('supplier5', $supplier);
        }
    //    $this->datatables->where("{$this->db->dbprefix('products')}.is_active",
    //   1);
        $this->datatables->add_column("Actions", $action, "productid, code, name");
        echo $this->datatables->generate();
    }


    function inactive($warehouse_id = NULL)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }

        $this->data['supplier'] = $this->input->get('supplier') ? $this->site->getCompanyByID($this->input->get('supplier')) : NULL;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('products')));
        $meta = array('page_title' => lang('products'), 'bc' => $bc);
        $this->page_construct('products/inactive', $meta, $this->data);
    }

    function getInActiveProducts($warehouse_id = NULL)
    {
      

        $this->sma->checkPermissions('index', TRUE);
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('admin/products/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('product_details'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_product") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete1' id='a__$1' href='" . admin_url('products/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_product') . "</a>";
        $single_barcode = anchor('admin/products/print_barcodes/$1', '<i class="fa fa-print"></i> ' . lang('print_barcode_label'));
        // $single_label = anchor_popup('products/single_label/$1/' . ($warehouse_id ? $warehouse_id : ''), '<i class="fa fa-print"></i> ' . lang('print_label'), $this->popup_attributes);
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            
            <li><a href="' . admin_url('products/edit/$1') . '"><i class="fa fa-edit"></i> ' . lang('edit_product') . '</a></li>';
        if ($warehouse_id) {
            $action .= '<li><a href="' . admin_url('products/set_rack/$1/' . $warehouse_id) . '" data-toggle="modal" data-target="#myModal"><i class="fa fa-bars"></i> '
                . lang('set_rack') . '</a></li>';
        }
        $action .= '<li><a href="' . admin_url() . 'assets/uploads/$2" data-type="image" data-toggle="lightbox"><i class="fa fa-file-photo-o"></i> '
            . lang('view_image') . '</a></li>
            <li>' . $single_barcode . '</li>
            <li class="divider"></li>
            <li>' . $delete_link . '</li>
            </ul>
        </div></div>';
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid,  {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price,gst_rate, COALESCE(wp.quantity, 0) as quantity1, {$this->db->dbprefix('units')}.code as unit, wp.rack as rack, alert_quantity,mrp", "mrp" , "weight", FALSE)
                ->from('products');
            if ($this->Settings->display_all_products) {
                $this->datatables->join("( SELECT product_id, quantity, rack from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) wp", 'products.id=wp.product_id', 'left');
            } else {
                $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
                    ->where('wp.warehouse_id', $warehouse_id)
                    ->where('wp.quantity !=', 0);
            }
            $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
               
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left');/* ->join('sale_items', 'products.id=sale_items.product_id')
                ->join('purchase_items', 'products.id=purchase_items.product_id');*/
            // ->group_by("products.id");
        } else {
            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid,  {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price,gst_rate, COALESCE( {$this->db->dbprefix('products')}.quantity, 0) as quantity1, {$this->db->dbprefix('units')}.code as unit, '' as rack, alert_quantity,mrp", "mrp", "weight", FALSE)
                ->from('products')
                ->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left') /*->join('sale_items', 'products.id=sale_items.product_id')
                ->join('purchase_items', 'products.id=purchase_items.product_id')*/
                ->group_by("products.id");
        }
        if (!$this->Owner && !$this->Admin) {
            if (!$this->session->userdata('show_cost')) {
                $this->datatables->unset_column("cost");
            }
            if (!$this->session->userdata('show_price')) {
                $this->datatables->unset_column("price");
            }
        }
        if ($supplier) {
            $this->datatables->where('supplier1', $supplier)
                ->or_where('supplier2', $supplier)
                ->or_where('supplier3', $supplier)
                ->or_where('supplier4', $supplier)
                ->or_where('supplier5', $supplier);
        }

  
$this->datatables->where("{$this->db->dbprefix('products')}.is_active",
       0);
        $this->datatables->add_column("Actions", $action, "productid, code, name");
        echo $this->datatables->generate();
    }
    function set_rack($product_id = NULL, $warehouse_id = NULL)
    {
        $this->sma->checkPermissions('edit', true);

        $this->form_validation->set_rules('rack', lang("rack_location"), 'trim|required');

        if ($this->form_validation->run() == true) {
            $data = array('rack' => $this->input->post('rack'),
                'product_id' => $product_id,
                'warehouse_id' => $warehouse_id,
            );
        } elseif ($this->input->post('set_rack')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("products");
        }

        if ($this->form_validation->run() == true && $this->products_model->setRack($data)) {
            $this->session->set_flashdata('message', lang("rack_set"));
            admin_redirect("products/" . $warehouse_id);
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['product'] = $this->site->getProductByID($product_id);
            $wh_pr = $this->products_model->getProductQuantity($product_id, $warehouse_id);
            $this->data['rack'] = $wh_pr['rack'];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'products/set_rack', $this->data);

        }
    }

    function product_barcode($product_code = NULL, $bcs = 'code128', $height = 60)
    {
        // if ($this->Settings->barcode_img) {
        return "<img src='" . admin_url('products/gen_barcode/' . $product_code . '/' . $bcs . '/' . $height) . "' alt='{$product_code}' class='bcimg' />";
        // } else {
        //     return $this->gen_barcode($product_code, $bcs, $height);
        // }
    }

    function barcode($product_code = NULL, $bcs = 'code128', $height = 60)
    {
        return admin_url('products/gen_barcode/' . $product_code . '/' . $bcs . '/' . $height);
    }

    function gen_barcode($product_code = NULL, $bcs = 'code128', $height = 60, $text = 1)
    {
        $drawText = ($text != 1) ? FALSE : TRUE;
        $this->load->library('zend');
        $this->zend->load('Zend/Barcode');
        $barcodeOptions = array('text' => $product_code, 'barHeight' => $height, 'drawText' => $drawText, 'factor' => 1.0);
        if ($this->Settings->barcode_img) {
            $rendererOptions = array('imageType' => 'jpg', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle');
            $imageResource = Zend_Barcode::render($bcs, 'image', $barcodeOptions, $rendererOptions);
            return $imageResource;
        } else {
            $rendererOptions = array('renderer' => 'svg', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle');
            $imageResource = Zend_Barcode::render($bcs, 'svg', $barcodeOptions, $rendererOptions);
            header("Content-Type: image/svg+xml");
            echo $imageResource;
        }
    }

    function print_barcodes($product_id = NULL)
    {
        $this->sma->checkPermissions('barcode', true);

        $this->form_validation->set_rules('style', lang("style"), 'required');

        if ($this->form_validation->run() == true) {

            $style = $this->input->post('style');
            $bci_size = ($style == 10 || $style == 12 ? 50 : ($style == 14 || $style == 18 ? 30 : 20));
            $currencies = $this->site->getAllCurrencies();
            $s = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            if ($s < 1) {
                $this->session->set_flashdata('error', lang('no_product_selected'));
                admin_redirect("products/print_barcodes");
            }
            for ($m = 0; $m < $s; $m++) {
                $pid = $_POST['product'][$m];
                $quantity = $_POST['quantity'][$m];
                $product = $this->products_model->getProductWithCategory($pid);
                $product->price = $this->input->post('check_promo') ? ($product->promotion ? $product->promo_price : $product->price) : $product->price;
                if ($variants = $this->products_model->getProductOptions($pid)) {
                    foreach ($variants as $option) {
                        if ($this->input->post('vt_' . $product->id . '_' . $option->id)) {
                            $barcodes[] = array(
                                'site' => $this->input->post('site_name') ? $this->Settings->site_name : FALSE,
                                'name' => $this->input->post('product_name') ? $product->name . ' - ' . $option->name : FALSE,
                                'image' => $this->input->post('product_image') ? $product->image : FALSE,
                                'barcode' => $this->product_barcode($product->code . $this->Settings->barcode_separator . $option->id, 'code128', $bci_size),
                                'price' => $this->input->post('price') ? $this->sma->formatMoney($option->price != 0 ? ($product->price + $option->price) : $product->price) : FALSE,
                                'unit' => $this->input->post('unit') ? $product->unit : FALSE,
                                'category' => $this->input->post('category') ? $product->category : FALSE,
                                'currencies' => $this->input->post('currencies'),
                                'variants' => $this->input->post('variants') ? $variants : FALSE,
                                'quantity' => $quantity
                            );
                        }
                    }
                } else {
                    $barcodes[] = array(
                        'site' => $this->input->post('site_name') ? $this->Settings->site_name : FALSE,
                        'name' => $this->input->post('product_name') ? $product->name : FALSE,
                        'image' => $this->input->post('product_image') ? $product->image : FALSE,
                        'barcode' => $this->product_barcode($product->code, $product->barcode_symbology, $bci_size),
                        'price' => $this->input->post('price') ? $this->sma->formatMoney($product->price) : FALSE,
                        'unit' => $this->input->post('unit') ? $product->unit : FALSE,
                        'category' => $this->input->post('category') ? $product->category : FALSE,
                        'currencies' => $this->input->post('currencies'),
                        'variants' => FALSE,
                        'quantity' => $quantity
                    );
                }

            }
            $this->data['barcodes'] = $barcodes;
            $this->data['currencies'] = $currencies;
            $this->data['style'] = $style;
            $this->data['items'] = false;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('print_barcodes')));
            $meta = array('page_title' => lang('print_barcodes'), 'bc' => $bc);
            $this->page_construct('products/print_barcodes', $meta, $this->data);

        } else {

            if ($this->input->get('purchase') || $this->input->get('transfer')) {
                if ($this->input->get('purchase')) {
                    $purchase_id = $this->input->get('purchase', TRUE);
                    $items = $this->products_model->getPurchaseItems($purchase_id);
                } elseif ($this->input->get('transfer')) {
                    $transfer_id = $this->input->get('transfer', TRUE);
                    $items = $this->products_model->getTransferItems($transfer_id);
                }
                if ($items) {
                    foreach ($items as $item) {
                        if ($row = $this->products_model->getProductByID($item->product_id)) {
                            $selected_variants = false;
                            if ($variants = $this->products_model->getProductOptions($row->id)) {
                                foreach ($variants as $variant) {
                                    $selected_variants[$variant->id] = isset($pr[$row->id]['selected_variants'][$variant->id]) && !empty($pr[$row->id]['selected_variants'][$variant->id]) ? 1 : ($variant->id == $item->option_id ? 1 : 0);
                                }
                            }
                            $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $item->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                        }
                    }
                    $this->data['message'] = lang('products_added_to_list');
                }
            }

            if ($product_id) {
                if ($row = $this->site->getProductByID($product_id)) {

                    $selected_variants = false;
                    if ($variants = $this->products_model->getProductOptions($row->id)) {
                        foreach ($variants as $variant) {
                            $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                        }
                    }
                    $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);

                    $this->data['message'] = lang('product_added_to_list');
                }
            }

            if ($this->input->get('category')) {
                if ($products = $this->products_model->getCategoryProducts($this->input->get('category'))) {
                    foreach ($products as $row) {
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                    }
                    $this->data['message'] = lang('products_added_to_list');
                } else {
                    $pr = array();
                    $this->session->set_flashdata('error', lang('no_product_found'));
                }
            }

            if ($this->input->get('subcategory')) {
                if ($products = $this->products_model->getSubCategoryProducts($this->input->get('subcategory'))) {
                    foreach ($products as $row) {
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                    }
                    $this->data['message'] = lang('products_added_to_list');
                } else {
                    $pr = array();
                    $this->session->set_flashdata('error', lang('no_product_found'));
                }
            }

            $this->data['items'] = isset($pr) ? json_encode($pr) : false;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('print_barcodes')));
            $meta = array('page_title' => lang('print_barcodes'), 'bc' => $bc);
            $this->page_construct('products/print_barcodes', $meta, $this->data);

        }
    }


    /* ------------------------------------------------------- */
 function add()
    {
       

        $this->sma->checkPermissions();
        $this->load->helper('security');
        $warehouses = $this->site->getAllWarehouses();
        $this->form_validation->set_rules('category', lang("category"), 'required|is_natural_no_zero');
        if ($this->input->post('type') == 'standard') {
            $this->form_validation->set_rules('cost', lang("product_cost"), 'required');
            $this->form_validation->set_rules('unit', lang("product_unit"), 'required');
        }
        if ($this->input->post('barcode_symbology') == 'ean13') {
            $this->form_validation->set_rules('code', lang("product_code"), 'min_length[13]|max_length[13]');
        }
        $this->form_validation->set_rules('code', lang("product_code"), 'is_unique[products.code]|alpha_dash');
        $this->form_validation->set_rules('slug', lang("slug"), 'required|is_unique[products.slug]|alpha_dash');
        $this->form_validation->set_rules('weight', lang("weight"), 'numeric');
        $this->form_validation->set_rules('product_image', lang("product_image"), 'xss_clean');
        $this->form_validation->set_rules('digital_file', lang("digital_file"), 'xss_clean');
        $this->form_validation->set_rules('userfile', lang("product_gallery_images"), 'xss_clean');
        $this->form_validation->set_rules('gstcode', lang("HSN Code"), 'required');


        //add bulk quantity price
            if($this->input->post('bulk_quantity_price')!==null){

                $catid = $this->input->post('category');
               
                
                if($catid!=11)
                {
                    $this->form_validation->set_rules('special_quantity[]',"Bulk Quantity", "trim|xss_clean|is_natural_no_zero");
                    $this->form_validation->set_rules('special_price[]',"Bulk Price", 'trim|xss_clean|numeric|greater_than_equal_to['.$this->input->post('cost').']');

                }  
                else
                {
                    $this->form_validation->set_rules('special_quantity[]',"Bulk Quantity", "trim|xss_clean|numeric");
                    $this->form_validation->set_rules('special_price[]',"Bulk Price", 'trim|xss_clean|numeric|greater_than_equal_to['.$this->input->post('cost').']');
                }
              
            }

        if ($this->form_validation->run() == true) {
            $tax_rate = $this->input->post('tax_rate') ? $this->site->getTaxRateByID($this->input->post('tax_rate')) : NULL;
            $data = array(
                'code' => $this->input->post('code'),
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'name' => $this->input->post('name'),
                'type' => $this->input->post('type'),
                'brand' => $this->input->post('brand'),
                'category_id' => $this->input->post('category'),
                'subcategory_id' => $this->input->post('subcategory') ? $this->input->post('subcategory') : NULL,
                'cost' => $this->sma->formatDecimal($this->input->post('cost')),
                'price' => $this->sma->formatDecimal($this->input->post('price')),
                'unit' => $this->input->post('unit'),
                'sale_unit' => $this->input->post('default_sale_unit'),
                'purchase_unit' => $this->input->post('default_purchase_unit'),
                'tax_rate' => $this->input->post('tax_rate'),
                'tax_method' => $this->input->post('tax_method'),
                'alert_quantity' => $this->input->post('alert_quantity'),
                'track_quantity' => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
                'details' => $this->input->post('details'),
                'product_details' => $this->input->post('product_details'),
                'supplier1' => $this->input->post('supplier'),
                'supplier1price' => $this->sma->formatDecimal($this->input->post('supplier_price')),
                'supplier2' => $this->input->post('supplier_2'),
                'supplier2price' => $this->sma->formatDecimal($this->input->post('supplier_2_price')),
                'supplier3' => $this->input->post('supplier_3'),
                'supplier3price' => $this->sma->formatDecimal($this->input->post('supplier_3_price')),
                'supplier4' => $this->input->post('supplier_4'),
                'supplier4price' => $this->sma->formatDecimal($this->input->post('supplier_4_price')),
                'supplier5' => $this->input->post('supplier_5'),
                'supplier5price' => $this->sma->formatDecimal($this->input->post('supplier_5_price')),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'promotion' => $this->input->post('promotion'),
                'promo_price' => $this->sma->formatDecimal($this->input->post('promo_price')),
                'start_date' => $this->input->post('start_date') ? $this->sma->fsd($this->input->post('start_date')) : NULL,
                'end_date' => $this->input->post('end_date') ? $this->sma->fsd($this->input->post('end_date')) : NULL,
                'supplier1_part_no' => $this->input->post('supplier_part_no'),
                'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
                'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
                'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
                'supplier5_part_no' => $this->input->post('supplier_5_part_no'),
                'file' => $this->input->post('file_link'),
                'slug' => $this->input->post('slug'),
                'weight' => $this->input->post('weight'),
                'mrp' => $this->input->post('mrp'),
                'featured' => $this->input->post('featured'),
                'gst_rate' => $this->input->post('gstrate'),
                'gst_code' => $this->input->post('gstcode'),
                'gst_name' => $this->input->post('gstname'),
                'cess' => $this->input->post('cess'),
                'reordered_quantity' => $this->input->post('reordered_quantity'),
                'marathi_name' => $this->input->post('marathi_name'),
                'is_active' => (($this->input->post('is_active'))?'1':'0'),
                'enable_sale_point' => ($this->input->post('enable_sale_point'))?'1':'0',
                'bulk_quantity_price' => ($this->input->post('bulk_quantity_price'))?'1':'0',
                'exp_days' => ($this->input->post('exp_days'))?$this->input->post('exp_days'):'',
                'show_on_pos' => ($this->input->post('show_on_pos'))?'1':'0',
            );
            $warehouse_qty = NULL;
            $product_attributes = NULL;
            $this->load->library('upload');
            if ($this->input->post('type') == 'standard') {
                $wh_total_quantity = 0;
                $pv_total_quantity = 0;
                for ($s = 2; $s > 5; $s++) {
                    $data['suppliers' . $s] = $this->input->post('supplier_' . $s);
                    $data['suppliers' . $s . 'price'] = $this->input->post('supplier_' . $s . '_price');
                }
                foreach ($warehouses as $warehouse) {
                    if ($this->input->post('wh_qty_' . $warehouse->id)) {
                        $warehouse_qty[] = array(
                            'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                            'quantity' => $this->input->post('wh_qty_' . $warehouse->id),
                            'rack' => $this->input->post('rack_' . $warehouse->id) ? $this->input->post('rack_' . $warehouse->id) : NULL
                        );
                        $wh_total_quantity += $this->input->post('wh_qty_' . $warehouse->id);
                    }
                }

                if ($this->input->post('attributes')) {
                    $a = sizeof($_POST['attr_name']);
                    for ($r = 0; $r <= $a; $r++) {
                        if (isset($_POST['attr_name'][$r])) {
                            $product_attributes[] = array(
                                'name' => $_POST['attr_name'][$r],
                                'warehouse_id' => $_POST['attr_warehouse'][$r],
                                'quantity' => $_POST['attr_quantity'][$r],
                                'price' => $_POST['attr_price'][$r],
                            );
                            $pv_total_quantity += $_POST['attr_quantity'][$r];
                        }
                    }

                } else {
                    $product_attributes = NULL;
                }

                if ($wh_total_quantity != $pv_total_quantity && $pv_total_quantity != 0) {
                    $this->form_validation->set_rules('wh_pr_qty_issue', 'wh_pr_qty_issue', 'required');
                    $this->form_validation->set_message('required', lang('wh_pr_qty_issue'));
                }
            }

            if ($this->input->post('type') == 'service') {
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'combo') {
                $total_price = 0;
                $c = sizeof($_POST['combo_item_code']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
                        $items[] = array(
                            'item_code' => $_POST['combo_item_code'][$r],
                            'quantity' => $_POST['combo_item_quantity'][$r],
                            'unit_price' => $_POST['combo_item_price'][$r],
                        );
                    }
                    $total_price += $_POST['combo_item_price'][$r] * $_POST['combo_item_quantity'][$r];
                }
                if ($this->sma->formatDecimal($total_price) != $this->sma->formatDecimal($this->input->post('price'))) {
                    $this->form_validation->set_rules('combo_price', 'combo_price', 'required');
                    $this->form_validation->set_message('required', lang('pprice_not_match_ciprice'));
                }
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'digital') {
                if ($this->input->post('file_link')) {
                    $data['file'] = $this->input->post('file_link');
                }
                if ($_FILES['digital_file']['size'] > 0) {
                    $config['upload_path'] = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['overwrite'] = FALSE;
                    $config['encrypt_name'] = TRUE;
                    $config['max_filename'] = 25;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('digital_file')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect("products/add");
                    }
                    $file = $this->upload->file_name;
                    $data['file'] = $file;
                } else {
                    if (!$this->input->post('file_link')) {
                        $this->form_validation->set_rules('digital_file', lang("digital_file"), 'required');
                    }
                }
                $config = NULL;
                $data['track_quantity'] = 0;
            }
            if (!isset($items)) {
                $items = NULL;
            }
            if ($_FILES['product_image']['size'] > 0) {

                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['max_filename'] = 25;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("products/add");
                }
                $photo = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image'] = $this->upload_path . $photo;
                    $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type'] = 'text';
                    $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                    $wm['quality'] = '100';
                    $wm['wm_font_size'] = '16';
                    $wm['wm_font_color'] = '999999';
                    $wm['wm_shadow_color'] = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding'] = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = NULL;
            }

            if ($_FILES['userfile']['name'][0] != "") {

                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $files = $_FILES;
                $cpt = count($_FILES['userfile']['name']);
                for ($i = 0; $i < $cpt; $i++) {

                    $_FILES['userfile']['name'] = $files['userfile']['name'][$i];
                    $_FILES['userfile']['type'] = $files['userfile']['type'][$i];
                    $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                    $_FILES['userfile']['error'] = $files['userfile']['error'][$i];
                    $_FILES['userfile']['size'] = $files['userfile']['size'][$i];

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect("products/add");
                    } else {

                        $pho = $this->upload->file_name;

                        $photos[] = $pho;

                        $this->load->library('image_lib');
                        $config['image_library'] = 'gd2';
                        $config['source_image'] = $this->upload_path . $pho;
                        $config['new_image'] = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = TRUE;
                        $config['width'] = $this->Settings->twidth;
                        $config['height'] = $this->Settings->theight;

                        $this->image_lib->initialize($config);

                        if (!$this->image_lib->resize()) {
                            echo $this->image_lib->display_errors();
                        }

                        if ($this->Settings->watermark) {
                            $this->image_lib->clear();
                            $wm['source_image'] = $this->upload_path . $pho;
                            $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                            $wm['wm_type'] = 'text';
                            $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                            $wm['quality'] = '100';
                            $wm['wm_font_size'] = '16';
                            $wm['wm_font_color'] = '999999';
                            $wm['wm_shadow_color'] = 'CCCCCC';
                            $wm['wm_vrt_alignment'] = 'top';
                            $wm['wm_hor_alignment'] = 'left';
                            $wm['wm_padding'] = '10';
                            $this->image_lib->initialize($wm);
                            $this->image_lib->watermark();
                        }

                        $this->image_lib->clear();
                    }
                }
                $config = NULL;
            } else {
                $photos = NULL;
            }
            $data['quantity'] = isset($wh_total_quantity) ? $wh_total_quantity : 0;
            // $this->sma->print_arrays($data, $warehouse_qty, $product_attributes);
        }

        if ($this->form_validation->run() == true && $productid = $this->products_model->addProduct($data, $items, $warehouse_qty, $product_attributes, $photos)) {

            //add bulk quantity price
            if($this->input->post('bulk_quantity_price')!==null):
            $row ['product_id'] = $productid;
            $row ['price_group_id'] = 0;
            
            foreach ($_POST['special_quantity'] as $key => $value) {
           
            $row ['quantity'] = $value;
            $row ['price'] = $_POST['special_price'][$key]; 
            $catID=$this->input->post('category');
            if($catID!=11)
            {
                $row ['quantity'] = ((int)$value);
            }
            if($value>0 && $_POST['special_price'][$key]>0)
            $this->db->insert('product_prices', $row);
          }
            endif;
            $this->session->set_flashdata('message', lang("product_added"));
            admin_redirect('products');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['brands'] = $this->site->getAllBrands();
            $this->data['base_units'] = $this->site->getAllBaseUnits();
            $this->data['warehouses'] = $warehouses;
            $this->data['warehouses_products'] = array();
            $this->data['product'] =  NULL;
            $this->data['variants'] = $this->products_model->getAllVariants();
            $this->data['combo_items'] = array();
            $this->data['product_options'] =array();
             $this->data['product_prices'] =array();
             $this->data['subunits'] =array();
              $this->data['last_cost']=array();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('add_product')));
            $meta = array('page_title' => lang('add_product'), 'bc' => $bc);
            $this->page_construct('products/add', $meta, $this->data);
        }
    }
    function add111($id = NULL)
    {
        //substore copy disable duplicate
        if(!empty($id))
        {
            admin_redirect("/products");
        }

        $this->sma->checkPermissions();
        $this->load->helper('security');
        $warehouses = $this->site->getAllWarehouses();
        $this->form_validation->set_rules('category', lang("category"), 'required|is_natural_no_zero');
        if ($this->input->post('type') == 'standard') {
            $this->form_validation->set_rules('cost', lang("product_cost"), 'required');
            $this->form_validation->set_rules('unit', lang("product_unit"), 'required');
        }
        if ($this->input->post('barcode_symbology') == 'ean13') {
            $this->form_validation->set_rules('code', lang("product_code"), 'min_length[13]|max_length[13]');
        }
        $this->form_validation->set_rules('code', lang("product_code"), 'is_unique[products.code]|alpha_dash');
        $this->form_validation->set_rules('slug', lang("slug"), 'required|is_unique[products.slug]|alpha_dash');
        $this->form_validation->set_rules('weight', lang("weight"), 'numeric');
        $this->form_validation->set_rules('product_image', lang("product_image"), 'xss_clean');
        $this->form_validation->set_rules('digital_file', lang("digital_file"), 'xss_clean');
        $this->form_validation->set_rules('userfile', lang("product_gallery_images"), 'xss_clean');
        $this->form_validation->set_rules('gstcode', lang("HSN Code"), 'required');


        //add bulk quantity price
            if($this->input->post('bulk_quantity_price')!==null){

                $catid = $this->input->post('category');
               
                
                if($catid!=11)
                {
                    $this->form_validation->set_rules('special_quantity[]',"Bulk Quantity", "trim|xss_clean|is_natural_no_zero");
                    $this->form_validation->set_rules('special_price[]',"Bulk Price", 'trim|xss_clean|numeric|greater_than_equal_to['.$this->input->post('cost').']');

                }  
                else
                {
                    $this->form_validation->set_rules('special_quantity[]',"Bulk Quantity", "trim|xss_clean|numeric");
                    $this->form_validation->set_rules('special_price[]',"Bulk Price", 'trim|xss_clean|numeric|greater_than_equal_to['.$this->input->post('cost').']');
                }
              
            }

        if ($this->form_validation->run() == true) {
            $tax_rate = $this->input->post('tax_rate') ? $this->site->getTaxRateByID($this->input->post('tax_rate')) : NULL;
            $data = array(
                'code' => $this->input->post('code'),
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'name' => $this->input->post('name'),
                'type' => $this->input->post('type'),
                'brand' => $this->input->post('brand'),
                'category_id' => $this->input->post('category'),
                'subcategory_id' => $this->input->post('subcategory') ? $this->input->post('subcategory') : NULL,
                'cost' => $this->sma->formatDecimal($this->input->post('cost')),
                'price' => $this->sma->formatDecimal($this->input->post('price')),
                'unit' => $this->input->post('unit'),
                'sale_unit' => $this->input->post('default_sale_unit'),
                'purchase_unit' => $this->input->post('default_purchase_unit'),
                'tax_rate' => $this->input->post('tax_rate'),
                'tax_method' => $this->input->post('tax_method'),
                'alert_quantity' => $this->input->post('alert_quantity'),
                'track_quantity' => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
                'details' => $this->input->post('details'),
                'product_details' => $this->input->post('product_details'),
                'supplier1' => $this->input->post('supplier'),
                'supplier1price' => $this->sma->formatDecimal($this->input->post('supplier_price')),
                'supplier2' => $this->input->post('supplier_2'),
                'supplier2price' => $this->sma->formatDecimal($this->input->post('supplier_2_price')),
                'supplier3' => $this->input->post('supplier_3'),
                'supplier3price' => $this->sma->formatDecimal($this->input->post('supplier_3_price')),
                'supplier4' => $this->input->post('supplier_4'),
                'supplier4price' => $this->sma->formatDecimal($this->input->post('supplier_4_price')),
                'supplier5' => $this->input->post('supplier_5'),
                'supplier5price' => $this->sma->formatDecimal($this->input->post('supplier_5_price')),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'promotion' => $this->input->post('promotion'),
                'promo_price' => $this->sma->formatDecimal($this->input->post('promo_price')),
                'start_date' => $this->input->post('start_date') ? $this->sma->fsd($this->input->post('start_date')) : NULL,
                'end_date' => $this->input->post('end_date') ? $this->sma->fsd($this->input->post('end_date')) : NULL,
                'supplier1_part_no' => $this->input->post('supplier_part_no'),
                'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
                'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
                'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
                'supplier5_part_no' => $this->input->post('supplier_5_part_no'),
                'file' => $this->input->post('file_link'),
                'slug' => $this->input->post('slug'),
                'weight' => $this->input->post('weight'),
                'mrp' => $this->input->post('mrp'),
                'featured' => $this->input->post('featured'),
                'gst_rate' => $this->input->post('gstrate'),
                'gst_code' => $this->input->post('gstcode'),
                'gst_name' => $this->input->post('gstname'),
                'cess' => $this->input->post('cess'),
                'reordered_quantity' => $this->input->post('reordered_quantity'),
                'marathi_name' => $this->input->post('marathi_name'),
                'enable_sale_point' => ($this->input->post('enable_sale_point'))?'1':'0',
                'bulk_quantity_price' => ($this->input->post('bulk_quantity_price'))?'1':'0',
                
            );
            $warehouse_qty = NULL;
            $product_attributes = NULL;
            $this->load->library('upload');
            if ($this->input->post('type') == 'standard') {
                $wh_total_quantity = 0;
                $pv_total_quantity = 0;
                for ($s = 2; $s > 5; $s++) {
                    $data['suppliers' . $s] = $this->input->post('supplier_' . $s);
                    $data['suppliers' . $s . 'price'] = $this->input->post('supplier_' . $s . '_price');
                }
                foreach ($warehouses as $warehouse) {
                    if ($this->input->post('wh_qty_' . $warehouse->id)) {
                        $warehouse_qty[] = array(
                            'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                            'quantity' => $this->input->post('wh_qty_' . $warehouse->id),
                            'rack' => $this->input->post('rack_' . $warehouse->id) ? $this->input->post('rack_' . $warehouse->id) : NULL
                        );
                        $wh_total_quantity += $this->input->post('wh_qty_' . $warehouse->id);
                    }
                }

                if ($this->input->post('attributes')) {
                    $a = sizeof($_POST['attr_name']);
                    for ($r = 0; $r <= $a; $r++) {
                        if (isset($_POST['attr_name'][$r])) {
                            $product_attributes[] = array(
                                'name' => $_POST['attr_name'][$r],
                                'warehouse_id' => $_POST['attr_warehouse'][$r],
                                'quantity' => $_POST['attr_quantity'][$r],
                                'price' => $_POST['attr_price'][$r],
                            );
                            $pv_total_quantity += $_POST['attr_quantity'][$r];
                        }
                    }

                } else {
                    $product_attributes = NULL;
                }

                if ($wh_total_quantity != $pv_total_quantity && $pv_total_quantity != 0) {
                    $this->form_validation->set_rules('wh_pr_qty_issue', 'wh_pr_qty_issue', 'required');
                    $this->form_validation->set_message('required', lang('wh_pr_qty_issue'));
                }
            }

            if ($this->input->post('type') == 'service') {
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'combo') {
                $total_price = 0;
                $c = sizeof($_POST['combo_item_code']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
                        $items[] = array(
                            'item_code' => $_POST['combo_item_code'][$r],
                            'quantity' => $_POST['combo_item_quantity'][$r],
                            'unit_price' => $_POST['combo_item_price'][$r],
                        );
                    }
                    $total_price += $_POST['combo_item_price'][$r] * $_POST['combo_item_quantity'][$r];
                }
                if ($this->sma->formatDecimal($total_price) != $this->sma->formatDecimal($this->input->post('price'))) {
                    $this->form_validation->set_rules('combo_price', 'combo_price', 'required');
                    $this->form_validation->set_message('required', lang('pprice_not_match_ciprice'));
                }
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'digital') {
                if ($_FILES['digital_file']['size'] > 0) {
                    $config['upload_path'] = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['overwrite'] = FALSE;
                    $config['encrypt_name'] = TRUE;
                    $config['max_filename'] = 25;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('digital_file')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect("products/add");
                    }
                    $file = $this->upload->file_name;
                    $data['file'] = $file;
                } else {
                    if (!$this->input->post('file_link')) {
                        $this->form_validation->set_rules('digital_file', lang("digital_file"), 'required');
                    }
                }
                $config = NULL;
                $data['track_quantity'] = 0;
            }
            if (!isset($items)) {
                $items = NULL;
            }
            if ($_FILES['product_image']['size'] > 0) {

                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['max_filename'] = 25;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("products/add");
                }
                $photo = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image'] = $this->upload_path . $photo;
                    $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type'] = 'text';
                    $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                    $wm['quality'] = '100';
                    $wm['wm_font_size'] = '16';
                    $wm['wm_font_color'] = '999999';
                    $wm['wm_shadow_color'] = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding'] = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = NULL;
            }

            if ($_FILES['userfile']['name'][0] != "") {

                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $files = $_FILES;
                $cpt = count($_FILES['userfile']['name']);
                for ($i = 0; $i < $cpt; $i++) {

                    $_FILES['userfile']['name'] = $files['userfile']['name'][$i];
                    $_FILES['userfile']['type'] = $files['userfile']['type'][$i];
                    $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                    $_FILES['userfile']['error'] = $files['userfile']['error'][$i];
                    $_FILES['userfile']['size'] = $files['userfile']['size'][$i];

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect("products/add");
                    } else {

                        $pho = $this->upload->file_name;

                        $photos[] = $pho;

                        $this->load->library('image_lib');
                        $config['image_library'] = 'gd2';
                        $config['source_image'] = $this->upload_path . $pho;
                        $config['new_image'] = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = TRUE;
                        $config['width'] = $this->Settings->twidth;
                        $config['height'] = $this->Settings->theight;

                        $this->image_lib->initialize($config);

                        if (!$this->image_lib->resize()) {
                            echo $this->image_lib->display_errors();
                        }

                        if ($this->Settings->watermark) {
                            $this->image_lib->clear();
                            $wm['source_image'] = $this->upload_path . $pho;
                            $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                            $wm['wm_type'] = 'text';
                            $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                            $wm['quality'] = '100';
                            $wm['wm_font_size'] = '16';
                            $wm['wm_font_color'] = '999999';
                            $wm['wm_shadow_color'] = 'CCCCCC';
                            $wm['wm_vrt_alignment'] = 'top';
                            $wm['wm_hor_alignment'] = 'left';
                            $wm['wm_padding'] = '10';
                            $this->image_lib->initialize($wm);
                            $this->image_lib->watermark();
                        }

                        $this->image_lib->clear();
                    }
                }
                $config = NULL;
            } else {
                $photos = NULL;
            }
            $data['quantity'] = isset($wh_total_quantity) ? $wh_total_quantity : 0;
            // $this->sma->print_arrays($data, $warehouse_qty, $product_attributes);
        }

        if ($this->form_validation->run() == true && $productid = $this->products_model->addProduct($data, $items, $warehouse_qty, $product_attributes, $photos)) {

            //add bulk quantity price
            if($this->input->post('bulk_quantity_price')!==null):
            $row ['product_id'] = $productid;
            $row ['price_group_id'] = 0;
            
            foreach ($_POST['special_quantity'] as $key => $value) {
           
            $row ['quantity'] = $value;
            $row ['price'] = $_POST['special_price'][$key]; 
            $catID=$this->input->post('category');
            if($catID!=11)
            {
                $row ['quantity'] = ((int)$value);
            }
            if($value>0 && $_POST['special_price'][$key]>0)
            $this->db->insert('product_prices', $row);
          }
            endif;
            $this->session->set_flashdata('message', lang("product_added"));
            admin_redirect('products');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['brands'] = $this->site->getAllBrands();
            $this->data['base_units'] = $this->site->getAllBaseUnits();
            $this->data['warehouses'] = $warehouses;
            $this->data['warehouses_products'] = $id ? $this->products_model->getAllWarehousesWithPQ($id) : NULL;
            $this->data['product'] = $id ? $this->products_model->getProductByID($id) : NULL;
            $this->data['variants'] = $this->products_model->getAllVariants();
            $this->data['combo_items'] = ($id && $this->data['product']->type == 'combo') ? $this->products_model->getProductComboItems($id) : NULL;
            $this->data['product_options'] = $id ? $this->products_model->getProductOptionsWithWH($id) : NULL;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('add_product')));
            $meta = array('page_title' => lang('add_product'), 'bc' => $bc);
            $this->page_construct('products/add', $meta, $this->data);
        }
    }

    function suggestions()
    {
        $term = $this->input->get('term', TRUE);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $rows = $this->products_model->getProductNames($term);
        if ($rows) {
            foreach ($rows as $row) {
                $pr[] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => 1);
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    function get_suggestions()
    {
        $term = $this->input->get('term', TRUE);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $rows = $this->products_model->getProductsForPrinting($term);
        if ($rows) {
            foreach ($rows as $row) {
                $variants = $this->products_model->getProductOptions($row->id);
                $pr[] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => 1, 'variants' => $variants);
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    function addByAjax()
    {
        if (!$this->mPermissions('add')) {
            exit(json_encode(array('msg' => lang('access_denied'))));
        }
        if ($this->input->get('token') && $this->input->get('token') == $this->session->userdata('user_csrf') && $this->input->is_ajax_request()) {
            $product = $this->input->get('product');
            if (!isset($product['code']) || empty($product['code'])) {
                exit(json_encode(array('msg' => lang('product_code_is_required'))));
            }
            if (!isset($product['name']) || empty($product['name'])) {
                exit(json_encode(array('msg' => lang('product_name_is_required'))));
            }
            if (!isset($product['category_id']) || empty($product['category_id'])) {
                exit(json_encode(array('msg' => lang('product_category_is_required'))));
            }
            if (!isset($product['unit']) || empty($product['unit'])) {
                exit(json_encode(array('msg' => lang('product_unit_is_required'))));
            }
            if (!isset($product['price']) || empty($product['price'])) {
                exit(json_encode(array('msg' => lang('product_price_is_required'))));
            }
            if (!isset($product['cost']) || empty($product['cost'])) {
                exit(json_encode(array('msg' => lang('product_cost_is_required'))));
            }
            if ($this->products_model->getProductByCode($product['code'])) {
                exit(json_encode(array('msg' => lang('product_code_already_exist'))));
            }
            if ($row = $this->products_model->addAjaxProduct($product)) {
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $pr = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'qty' => 1, 'cost' => $row->cost, 'name' => $row->name, 'tax_method' => $row->tax_method, 'tax_rate' => $tax_rate, 'discount' => '0');
                $this->sma->send_json(array('msg' => 'success', 'result' => $pr));
            } else {
                exit(json_encode(array('msg' => lang('failed_to_add_product'))));
            }
        } else {
            json_encode(array('msg' => 'Invalid token'));
        }

    }


    /* -------------------------------------------------------- */

    function edit($id = NULL)
    {
        $this->sma->checkPermissions();
        $this->load->helper('security');
	    $this->load->helper('date');
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }
        $warehouses = $this->site->getAllWarehouses();
        $warehouses_products = $this->products_model->getAllWarehousesWithPQ($id);
        $product = $this->site->getProductByID($id);
        if (!$id || !$product) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('category', lang("category"), 'required|is_natural_no_zero');
        if ($this->input->post('type') == 'standard') {
            $this->form_validation->set_rules('cost', lang("product_cost"), 'required');
            $this->form_validation->set_rules('unit', lang("product_unit"), 'required');
        }
        $this->form_validation->set_rules('code', lang("product_code"), 'alpha_dash');
        if ($this->input->post('code') !== $product->code) {
            $this->form_validation->set_rules('code', lang("product_code"), 'is_unique[products.code]');
        }
        if ($this->input->post('barcode_symbology') == 'ean13') {
            $this->form_validation->set_rules('code', lang("product_code"), 'min_length[13]|max_length[13]');
        }
        $this->form_validation->set_rules('slug', lang("slug"), 'required|alpha_dash');
        if ($this->input->post('slug') !== $product->slug) {
            $this->form_validation->set_rules('slug', lang("slug"), 'required|is_unique[products.slug]|alpha_dash');
        }
        $this->form_validation->set_rules('weight', lang("weight"), 'numeric');
        /*mrp edit*/
        $this->form_validation->set_rules('mrp', lang("mrp"), 'numeric');
        $this->form_validation->set_rules('product_image', lang("product_image"), 'xss_clean');
        $this->form_validation->set_rules('digital_file', lang("digital_file"), 'xss_clean');
        $this->form_validation->set_rules('userfile', lang("product_gallery_images"), 'xss_clean');
        $this->form_validation->set_rules('gstrate', lang("gst_rate"), 'required');
        $this->form_validation->set_rules('cess', lang("cess"), 'required');
        $this->form_validation->set_rules('gstcode', lang("HSN Code"), 'required');

           //add bulk quantity price
            if($this->input->post('bulk_quantity_price')!==null){

                $catid = $this->input->post('category');
               
                
                if($catid!=11)
                {
                    $this->form_validation->set_rules('special_quantity[]',"Bulk Quantity", "trim|xss_clean|is_natural_no_zero");
                    $this->form_validation->set_rules('special_price[]',"Bulk Price", 'trim|xss_clean|numeric|greater_than_equal_to['.$this->input->post('cost').']');

                }  
                else
                {
                    $this->form_validation->set_rules('special_quantity[]',"Bulk Quantity", "trim|xss_clean|numeric");
                    $this->form_validation->set_rules('special_price[]',"Bulk Price", 'trim|xss_clean|numeric|greater_than_equal_to['.$this->input->post('cost').']');
                }
              
            }
        if ($this->form_validation->run('products/add') == true) {

            $data = array('code' => $this->input->post('code'),
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'name' => $this->input->post('name'),
                'type' => $this->input->post('type'),
                'brand' => $this->input->post('brand'),
                'category_id' => $this->input->post('category'),
                'subcategory_id' => $this->input->post('subcategory') ? $this->input->post('subcategory') : NULL,
                'cost' => $this->sma->formatDecimal($this->input->post('cost')),
                'price' => $this->sma->formatDecimal($this->input->post('price')),
                'unit' => $this->input->post('unit'),
                'sale_unit' => $this->input->post('default_sale_unit'),
                'purchase_unit' => $this->input->post('default_purchase_unit'),
                'tax_rate' => $this->input->post('tax_rate'),
                'tax_method' => $this->input->post('tax_method'),
                'alert_quantity' => $this->input->post('alert_quantity'),
                'track_quantity' => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
                'details' => $this->input->post('details'),
                'product_details' => $this->input->post('product_details'),
                'supplier1' => $this->input->post('supplier'),
                'supplier1price' => $this->sma->formatDecimal($this->input->post('supplier_price')),
                'supplier2' => $this->input->post('supplier_2'),
                'supplier2price' => $this->sma->formatDecimal($this->input->post('supplier_2_price')),
                'supplier3' => $this->input->post('supplier_3'),
                'supplier3price' => $this->sma->formatDecimal($this->input->post('supplier_3_price')),
                'supplier4' => $this->input->post('supplier_4'),
                'supplier4price' => $this->sma->formatDecimal($this->input->post('supplier_4_price')),
                'supplier5' => $this->input->post('supplier_5'),
                'supplier5price' => $this->sma->formatDecimal($this->input->post('supplier_5_price')),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'promotion' => $this->input->post('promotion'),
                'promo_price' => $this->sma->formatDecimal($this->input->post('promo_price')),
                'start_date' => $this->input->post('start_date') ? $this->sma->fsd($this->input->post('start_date')) : NULL,
                'end_date' => $this->input->post('end_date') ? $this->sma->fsd($this->input->post('end_date')) : NULL,
                'supplier1_part_no' => $this->input->post('supplier_part_no'),
                'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
                'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
                'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
                'supplier5_part_no' => $this->input->post('supplier_5_part_no'),
                'slug' => $this->input->post('slug'),
                'weight' => $this->input->post('weight'),
                'mrp' => $this->input->post('mrp'),
                'featured' => $this->input->post('featured'),
                'gst_rate' => $this->input->post('gstrate'),
                'gst_code' => $this->input->post('gstcode'),
                'gst_name' => $this->input->post('gstname'),
                'cess' => $this->input->post('cess'),
                'reordered_quantity' => $this->input->post('reordered_quantity'),
                'marathi_name' => $this->input->post('marathi_name'),
                'is_active' => (($this->input->post('is_active'))?'1':'0'),
                'enable_sale_point' => ($this->input->post('enable_sale_point'))?'1':'0',
                'bulk_quantity_price' => ($this->input->post('bulk_quantity_price'))?'1':'0',
                'exp_days' => ($this->input->post('exp_days'))?$this->input->post('exp_days'):'',
	            'show_on_pos' => ($this->input->post('show_on_pos'))?'1':'0',
            );
            $warehouse_qty = NULL;
            $product_attributes = NULL;
            $update_variants = array();
            $this->load->library('upload');
            if ($this->input->post('type') == 'standard') {
                if ($product_variants = $this->products_model->getProductOptions($id)) {
                    foreach ($product_variants as $pv) {
                        $update_variants[] = array(
                            'id' => $this->input->post('variant_id_' . $pv->id),
                            'name' => $this->input->post('variant_name_' . $pv->id),
                            'cost' => $this->input->post('variant_cost_' . $pv->id),
                            'price' => $this->input->post('variant_price_' . $pv->id),
                        );
                    }
                }
                for ($s = 2; $s > 5; $s++) {
                    $data['suppliers' . $s] = $this->input->post('supplier_' . $s);
                    $data['suppliers' . $s . 'price'] = $this->input->post('supplier_' . $s . '_price');
                }
                foreach ($warehouses as $warehouse) {
                    $warehouse_qty[] = array(
                        'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                        'rack' => $this->input->post('rack_' . $warehouse->id) ? $this->input->post('rack_' . $warehouse->id) : NULL
                    );
                }

                if ($this->input->post('attributes')) {
                    $a = sizeof($_POST['attr_name']);
                    for ($r = 0; $r <= $a; $r++) {
                        if (isset($_POST['attr_name'][$r])) {
                            if ($product_variatnt = $this->products_model->getPrductVariantByPIDandName($id, trim($_POST['attr_name'][$r]))) {
                                $this->form_validation->set_message('required', lang("product_already_has_variant") . ' (' . $_POST['attr_name'][$r] . ')');
                                $this->form_validation->set_rules('new_product_variant', lang("new_product_variant"), 'required');
                            } else {
                                $product_attributes[] = array(
                                    'name' => $_POST['attr_name'][$r],
                                    'warehouse_id' => $_POST['attr_warehouse'][$r],
                                    'quantity' => $_POST['attr_quantity'][$r],
                                    'price' => $_POST['attr_price'][$r],
                                );
                            }
                        }
                    }

                } else {
                    $product_attributes = NULL;
                }

            }

            if ($this->input->post('type') == 'service') {
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'combo') {
                $total_price = 0;
                $c = sizeof($_POST['combo_item_code']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
                        $items[] = array(
                            'item_code' => $_POST['combo_item_code'][$r],
                            'quantity' => $_POST['combo_item_quantity'][$r],
                            'unit_price' => $_POST['combo_item_price'][$r],
                        );
                    }
                    $total_price += $_POST['combo_item_price'][$r] * $_POST['combo_item_quantity'][$r];
                }
                if ($this->sma->formatDecimal($total_price) != $this->sma->formatDecimal($this->input->post('price'))) {
                    $this->form_validation->set_rules('combo_price', 'combo_price', 'required');
                    $this->form_validation->set_message('required', lang('pprice_not_match_ciprice'));
                }
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'digital') {
                if ($this->input->post('file_link')) {
                    $data['file'] = $this->input->post('file_link');
                }
                if ($_FILES['digital_file']['size'] > 0) {
                    $config['upload_path'] = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['overwrite'] = FALSE;
                    $config['encrypt_name'] = TRUE;
                    $config['max_filename'] = 25;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('digital_file')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect("products/add");
                    }
                    $file = $this->upload->file_name;
                    $data['file'] = $file;
                }
                $config = NULL;
                $data['track_quantity'] = 0;
            }
            if (!isset($items)) {
                $items = NULL;
            }
            if ($_FILES['product_image']['size'] > 0) {

                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("products/edit/" . $id);
                }
                $photo = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image'] = $this->upload_path . $photo;
                    $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type'] = 'text';
                    $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                    $wm['quality'] = '100';
                    $wm['wm_font_size'] = '16';
                    $wm['wm_font_color'] = '999999';
                    $wm['wm_shadow_color'] = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding'] = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = NULL;
            }

            if ($_FILES['userfile']['name'][0] != "") {

                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $files = $_FILES;
                $cpt = count($_FILES['userfile']['name']);
                for ($i = 0; $i < $cpt; $i++) {

                    $_FILES['userfile']['name'] = $files['userfile']['name'][$i];
                    $_FILES['userfile']['type'] = $files['userfile']['type'][$i];
                    $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                    $_FILES['userfile']['error'] = $files['userfile']['error'][$i];
                    $_FILES['userfile']['size'] = $files['userfile']['size'][$i];

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect("products/edit/" . $id);
                    } else {

                        $pho = $this->upload->file_name;

                        $photos[] = $pho;

                        $this->load->library('image_lib');
                        $config['image_library'] = 'gd2';
                        $config['source_image'] = $this->upload_path . $pho;
                        $config['new_image'] = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = TRUE;
                        $config['width'] = $this->Settings->twidth;
                        $config['height'] = $this->Settings->theight;

                        $this->image_lib->initialize($config);

                        if (!$this->image_lib->resize()) {
                            echo $this->image_lib->display_errors();
                        }

                        if ($this->Settings->watermark) {
                            $this->image_lib->clear();
                            $wm['source_image'] = $this->upload_path . $pho;
                            $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                            $wm['wm_type'] = 'text';
                            $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                            $wm['quality'] = '100';
                            $wm['wm_font_size'] = '16';
                            $wm['wm_font_color'] = '999999';
                            $wm['wm_shadow_color'] = 'CCCCCC';
                            $wm['wm_vrt_alignment'] = 'top';
                            $wm['wm_hor_alignment'] = 'left';
                            $wm['wm_padding'] = '10';
                            $this->image_lib->initialize($wm);
                            $this->image_lib->watermark();
                        }

                        $this->image_lib->clear();
                    }
                }
                $config = NULL;
            } else {
                $photos = NULL;
            }
           /// $data['quantity'] = isset($wh_total_quantity) ? $wh_total_quantity : 0;
            // $this->sma->print_arrays($data, $warehouse_qty, $update_variants, $product_attributes, $photos, $items);
        }

        if ($this->form_validation->run() == true && $this->products_model->updateProduct($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants)) {

            //update bulk quantity price
            if($this->input->post('bulk_quantity_price')!==null):
            //remove prvious prices
              // $this->products_model->deleteBulkProductPrices($id);
            foreach ($_POST['special_quantity'] as $key => $value) {
                $price = $_POST['special_price'][$key];
                $spe_id = $_POST['special_id'][$key];
                if($price == '-1')
                    {
                if(!empty($spe_id))
                {
                    $this->db->query('delete from '.$this->db->dbprefix('product_prices').' where id='.$spe_id);
                }
                else
                {
                     $this->db->query('delete from '.$this->db->dbprefix('product_prices').' where quantity='.$value .' and product_id='.$id.' and price_group_id = 0');

                }        
               
                    }
              elseif($value>0 && $price > 0){
                $catID=$this->input->post('category');
                if($catID!=11)
                {
                    $value = ((int)$value);
                }
                if(empty($spe_id))
                {
                    $this->products_model->setBulkProductPrice($id,$price,$value);    
                }
                else
                {
                    $this->products_model->setUpdateBulkProductPrice($id,$price,$value,$spe_id); 
                }
                
              }
              else{

                    if(!empty($spe_id))
                    {
                         $this->db->query('delete from '.$this->db->dbprefix('product_prices').' where id='.$spe_id);

                    }
              }

                
                }
            endif;


            $this->session->set_flashdata('message', lang("product_updated"));
            admin_redirect('products');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['brands'] = $this->site->getAllBrands();
            $this->data['base_units'] = $this->site->getAllBaseUnits();
            $this->data['warehouses'] = $warehouses;
            $this->data['warehouses_products'] = $warehouses_products;
            $this->data['product'] = $product;
            $this->data['variants'] = $this->products_model->getAllVariants();
            $this->data['subunits'] = $this->site->getUnitsByBUID($product->unit);
            $this->data['product_variants'] = $this->products_model->getProductOptions($id);
            $this->data['combo_items'] = $product->type == 'combo' ? $this->products_model->getProductComboItems($product->id) : NULL;
            $this->data['product_options'] = $id ? $this->products_model->getProductOptionsWithWH($id) : NULL;
            $this->data['product_prices'] = $id ? $this->products_model->bulk_product_prices($id) : NULL;
			 $this->data['last_cost']=$this->products_model->getLatestPurchasePrice($id);
            //echo "sp ".json_encode( $this->data['product_prices'] );die();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('edit_product')));
            $meta = array('page_title' => lang('edit_product'), 'bc' => $bc);
            $this->page_construct('products/edit', $meta, $this->data);
        }
    }

    /* ---------------------------------------------------------------- */

    function import_csv1()
    {
        $this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (isset($_FILES["userfile"])) {

                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = TRUE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("products/import_csv");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('name', 'code','brand', 'category_code', 'unit', 'sale_unit', 'purchase_unit', 'cost', 'price', 'alert_quantity','variants','subcategory_code', 'weight','mrp','gst_rate','gst_code','cess','qty');

                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
              //  $this->sma->print_arrays($final);
                $rw = 2;
                $items = array();
                foreach ($final as $csv_pr) {
                    if (!$this->products_model->getProductByCode(trim($csv_pr['code']))) {

                        $catd = $this->products_model->getCategoryByCode(trim($csv_pr['category_code']));
                        if(empty($catd))
                        {
                            $catData = array('code' =>trim($csv_pr['category_code']) ,'name' =>trim($csv_pr['category_code']) );
                             $this->site->addCategory($catData);
                             $catd = $this->products_model->getCategoryByCode(trim($csv_pr['category_code']));
                        }
                         $brand = $this->products_model->getBrandByName(trim($csv_pr['brand']));
                         if(empty($brand))
                        {
                            $brandData = array('code' =>trim($csv_pr['brand']) ,'name' =>trim($csv_pr['brand']) );
                            $this->site->addBrand($brandData);
                            $brand = $this->products_model->getBrandByName(trim($csv_pr['brand']));
                        }
                         

                         $unit = $this->products_model->getUnitByCode(trim($csv_pr['unit']));


                          if(empty($unit))
                        {
                            $unitData = array('code' =>trim($csv_pr['unit']) ,'name' =>trim($csv_pr['unit']) );
                            $this->site->addUnit($unitData);
                            $unit = $this->products_model->getUnitByCode(trim($csv_pr['unit']));
                        }
                        if (1==1) {
                           
                            $base_unit = $unit ? $unit->id : NULL;
                            $sale_unit = $base_unit;
                            $purcahse_unit = $base_unit;
                            if ($base_unit) {
                                $units = $this->site->getUnitsByBUID($base_unit);
                                foreach ($units as $u) {
                                    if ($u->code == trim($csv_pr['sale_unit'])) {
                                        $sale_unit = $u->id;
                                    }
                                    if ($u->code == trim($csv_pr['purchase_unit'])) {
                                        $purcahse_unit = $u->id;
                                    }
                                }
                            } else {
                                $this->session->set_flashdata('error', lang("check_unit") . " (" . $csv_pr['unit'] . "). " . lang("unit_code_x_exist") . " " . lang("line_no") . " " . $rw);
                                admin_redirect("products/import_csv");
                            }

                            //$tax_details = $this->products_model->getTaxRateByName(trim($csv_pr['tax_rate']));
                            $prsubcat = $this->products_model->getCategoryByCode(trim($csv_pr['subcategory_code']));
                            $slug = $this->sma->slug(trim($csv_pr['name']), 'products');
                            $items[] = array(
                                'code' => trim($csv_pr['code']),
                                'name' => trim($csv_pr['name']),
                                'slug' => $slug,
                                'category_id' => $catd->id,
                              //  'barcode_symbology' => mb_strtolower(trim($csv_pr['barcode_symbology']), 'UTF-8'),
                                'barcode_symbology' => mb_strtolower('code128', 'UTF-8'),
                                'brand' => ($brand ? $brand->id : NULL),
                                'unit' => $base_unit,
                                'sale_unit' => $sale_unit,
                                'purchase_unit' => $purcahse_unit,
                                'cost' => trim($csv_pr['cost']),
                                'price' => trim($csv_pr['price']),
                                'alert_quantity' => trim($csv_pr['alert_quantity']),
                               // 'tax_rate' => ($tax_details ? $tax_details->id : NULL),
                                'tax_method' => 'inclusive',
                                'subcategory_id' => ($prsubcat ? $prsubcat->id : NULL),
                                'variants' => trim($csv_pr['variants']),
                              //  'image' => trim($csv_pr['image']),
                                'weight' => trim($csv_pr['weight']),
                                'mrp' => trim($csv_pr['mrp']),
                                'gst_rate' => trim($csv_pr['gst_rate']),
                                'gst_code' => trim($csv_pr['gst_code']),
                                'cess' => trim($csv_pr['cess']),
                                'quantity' => trim($csv_pr['Qty'])
                            );
                            //$this->sma->print_arrays($items);
                        } else {
                            $this->session->set_flashdata('error', lang("check_category_code") . " (" . $csv_pr['category_code'] . "). " . lang("category_code_x_exist") . " " . lang("line_no") . " " . $rw);
                            admin_redirect("products/import_csv");
                        }
                    }
                    else {
                        $this->session->set_flashdata('error', lang("check_product_code") . " (" . $csv_pr['name'] . "). " . lang("code_already_exist") . " " . lang("line_no") . " " . $rw);
                        admin_redirect("products/import_csv");
                    }
                    $rw++;
                }
            }

            // $this->sma->print_arrays($items);die;
        }

        //();
        if ($this->form_validation->run() == true && $prs = $this->products_model->add_products($items)) {
            $this->session->set_flashdata('message', sprintf(lang("products_added"), $prs));
            admin_redirect('products');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('import_products_by_csv')));
            $meta = array('page_title' => lang('import_products_by_csv'), 'bc' => $bc);
            $this->page_construct('products/import_csv', $meta, $this->data);

        }
    }

    /* ------------------------------------------------------------------ */

    function update_price()
    {
        $this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (DEMO) {
                $this->session->set_flashdata('message', lang("disabled_in_demo"));
                admin_redirect('welcome');
            }

            if (isset($_FILES["userfile"])) {

                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = TRUE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("products");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('code', 'price');

                $final = array();

                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if (!$this->products_model->getProductByCode(trim($csv_pr['code']))) {
                        $this->session->set_flashdata('message', lang("check_product_code") . " (" . $csv_pr['code'] . "). " . lang("code_x_exist") . " " . lang("line_no") . " " . $rw);
                        admin_redirect("products");
                    }
                    $rw++;
                }
            }

        } elseif ($this->input->post('update_price')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("system_settings/group_product_prices/" . $group_id);
        }

        if ($this->form_validation->run() == true && !empty($final)) {
            $this->products_model->updatePrice($final);
            $this->session->set_flashdata('message', lang("price_updated"));
            admin_redirect('products');
        } else {

            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'products/update_price', $this->data);

        }
    }

    /* ------------------------------------------------------------------------------- */

    function delete($id = NULL)
    {
        $this->sma->checkPermissions(NULL, TRUE);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->products_model->deleteProduct($id)) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(array('error' => 0, 'msg' => lang("product_deleted")));
            }
            $this->session->set_flashdata('message', lang('product_deleted'));
            admin_redirect('welcome');
        }

    }

    /* ----------------------------------------------------------------------------- */

    function quantity_adjustments($warehouse_id = NULL)
    {
        $this->sma->checkPermissions('adjustments');

        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('quantity_adjustments')));
        $meta = array('page_title' => lang('quantity_adjustments'), 'bc' => $bc);
        $this->page_construct('products/quantity_adjustments', $meta, $this->data);
    }

    function getadjustments($warehouse_id = NULL)
    {
        $this->sma->checkPermissions('adjustments');

        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_adjustment") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('products/delete_adjustment/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('adjustments')}.id as id, date, reference_no, warehouses.name as wh_name, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by, note")
            ->from('adjustments')
            ->join('warehouses', 'warehouses.id=adjustments.warehouse_id', 'left')
            ->join('users', 'users.id=adjustments.created_by', 'left')
            ->group_by("adjustments.id");
        if ($warehouse_id) {
            $this->datatables->where('adjustments.warehouse_id', $warehouse_id);
        }
        $this->datatables->add_column("Actions", "<div class='text-center'><a href='" . admin_url('products/edit_adjustment/$1') . "' class='tip' title='" . lang("edit_adjustment") . "'><i class='fa fa-edit'></i></a> " . $delete_link . "</div>", "id");

        echo $this->datatables->generate();

    }

    public function view_adjustment($id)
    {
        $this->sma->checkPermissions('adjustments', TRUE);

        $adjustment = $this->products_model->getAdjustmentByID($id);
        if (!$id || !$adjustment) {
            $this->session->set_flashdata('error', lang('adjustment_not_found'));
            $this->sma->md();
        }

        $this->data['inv'] = $adjustment;
        $this->data['rows'] = $this->products_model->getAdjustmentItems($id);
        $this->data['created_by'] = $this->site->getUser($adjustment->created_by);
        $this->data['updated_by'] = $this->site->getUser($adjustment->updated_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($adjustment->warehouse_id);
        $this->load->view($this->theme . 'products/view_adjustment', $this->data);
    }

    function add_adjustment($count_id = NULL)
    {
        $this->sma->checkPermissions('adjustments', true);
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');

        if ($this->form_validation->run() == true) {

            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:s:i');
            }

            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qa');
            $warehouse_id = $this->input->post('warehouse');
            $note = $this->sma->clear_tags($this->input->post('note'));

            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {

                $product_id = $_POST['product_id'][$r];
                $type = $_POST['type'][$r];
                $quantity = $_POST['quantity'][$r];
                $serial = isset($_POST['serial']) ? $_POST['serial'][$r] : "";
                $variant = isset($_POST['variant'][$r]) && !empty($_POST['variant'][$r]) ? $_POST['variant'][$r] : NULL;

                if (!$this->Settings->overselling && $type == 'subtraction') {
                    if ($variant) {
                        if ($op_wh_qty = $this->products_model->getProductWarehouseOptionQty($variant, $warehouse_id)) {
                            if ($op_wh_qty->quantity < $quantity) {
                                $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'));
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                        } else {
                            $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'));
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }
                    if ($wh_qty = $this->products_model->getProductQuantity($product_id, $warehouse_id)) {
                        if ($wh_qty['quantity'] < $quantity) {
                            $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'));
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    } else {
                        $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'));
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                }

                $products[] = array(
                    'product_id' => $product_id,
                    'type' => $type,
                    'quantity' => $quantity,
                    'warehouse_id' => $warehouse_id,
                    'option_id' => $variant,
                    'serial_no' => $serial,
                );

            }

            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("products"), 'required');
            } else {
                krsort($products);
            }

            $data = array(
                'date' => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
                'count_id' => $this->input->post('count_id') ? $this->input->post('count_id') : NULL,
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

        if ($this->form_validation->run() == true && $this->products_model->addAdjustment($data, $products)) {
            $this->session->set_userdata('remove_qals', 1);
            $this->session->set_flashdata('message', lang("quantity_adjusted"));
            admin_redirect('products/quantity_adjustments');
        } else {

            if ($count_id) {
                $stock_count = $this->products_model->getStouckCountByID($count_id);
                $items = $this->products_model->getStockCountItems($count_id);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    if ($item->counted != $item->expected) {
                        $product = $this->site->getProductByID($item->product_id);
                        $row = json_decode('{}');
                        $row->id = $item->product_id;
                        $row->code = $product->code;
                        $row->name = $product->name;
                        $row->qty = $item->counted - $item->expected;
                        $row->type = $row->qty > 0 ? 'addition' : 'subtraction';
                        $row->qty = $row->qty > 0 ? $row->qty : (0 - $row->qty);
                        $options = $this->products_model->getProductOptions($product->id);
                        $row->option = $item->product_variant_id ? $item->product_variant_id : 0;
                        $row->serial = '';
                        $ri = $this->Settings->item_addition ? $product->id : $c;

                        $pr[$ri] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                            'row' => $row, 'options' => $options);
                        $c++;
                    }
                }
            }
            $this->data['adjustment_items'] = $count_id ? json_encode($pr) : FALSE;
            $this->data['warehouse_id'] = $count_id ? $stock_count->warehouse_id : FALSE;
            $this->data['count_id'] = $count_id;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('add_adjustment')));
            $meta = array('page_title' => lang('add_adjustment'), 'bc' => $bc);
            $this->page_construct('products/add_adjustment', $meta, $this->data);

        }
    }

    function edit_adjustment($id)
    {
        $this->sma->checkPermissions('adjustments', true);
        $adjustment = $this->products_model->getAdjustmentByID($id);
        if (!$id || !$adjustment) {
            $this->session->set_flashdata('error', lang('adjustment_not_found'));
            $this->sma->md();
        }
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');

        if ($this->form_validation->run() == true) {

            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld($this->input->post('date'));
            } else {
                $date = $adjustment->date;
            }

            $reference_no = $this->input->post('reference_no');
            $warehouse_id = $this->input->post('warehouse');
            $note = $this->sma->clear_tags($this->input->post('note'));

            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {

                $product_id = $_POST['product_id'][$r];
                $type = $_POST['type'][$r];
                $quantity = $_POST['quantity'][$r];
                $serial = $_POST['serial'][$r];
                $variant = isset($_POST['variant'][$r]) && !empty($_POST['variant'][$r]) ? $_POST['variant'][$r] : null;

                if (!$this->Settings->overselling && $type == 'subtraction') {
                    if ($variant) {
                        if ($op_wh_qty = $this->products_model->getProductWarehouseOptionQty($variant, $warehouse_id)) {
                            if ($op_wh_qty->quantity < $quantity) {
                                $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'));
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                        } else {
                            $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'));
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }
                    if ($wh_qty = $this->products_model->getProductQuantity($product_id, $warehouse_id)) {
                        if ($wh_qty['quantity'] < $quantity) {
                            $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'));
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    } else {
                        $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'));
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                }

                $products[] = array(
                    'product_id' => $product_id,
                    'type' => $type,
                    'quantity' => $quantity,
                    'warehouse_id' => $warehouse_id,
                    'option_id' => $variant,
                    'serial_no' => $serial,
                );

            }

            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("products"), 'required');
            } else {
                krsort($products);
            }

            $data = array(
                'date' => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id')
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

        if ($this->form_validation->run() == true && $this->products_model->updateAdjustment($id, $data, $products)) {
            $this->session->set_userdata('remove_qals', 1);
            $this->session->set_flashdata('message', lang("quantity_adjusted"));
            admin_redirect('products/quantity_adjustments');
        } else {

            $inv_items = $this->products_model->getAdjustmentItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $product = $this->site->getProductByID($item->product_id);
                $row = json_decode('{}');
                $row->id = $item->product_id;
                $row->code = $product->code;
                $row->name = $product->name;
                $row->qty = $item->quantity;
                $row->type = $item->type;
                $options = $this->products_model->getProductOptions($product->id);
                $row->option = $item->option_id ? $item->option_id : 0;
                $row->serial = $item->serial_no ? $item->serial_no : '';
                $ri = $this->Settings->item_addition ? $product->id : $c;

                $pr[$ri] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'options' => $options);
                $c++;
            }

            $this->data['adjustment'] = $adjustment;
            $this->data['adjustment_items'] = json_encode($pr);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('edit_adjustment')));
            $meta = array('page_title' => lang('edit_adjustment'), 'bc' => $bc);
            $this->page_construct('products/edit_adjustment', $meta, $this->data);

        }
    }

    function add_adjustment_by_csv()
    {
        $this->sma->checkPermissions('adjustments', true);
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');

        if ($this->form_validation->run() == true) {

            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:s:i');
            }

            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qa');
            $warehouse_id = $this->input->post('warehouse');
            $note = $this->sma->clear_tags($this->input->post('note'));
            $data = array(
                'date' => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
                'count_id' => NULL,
            );

            if ($_FILES['csv_file']['size'] > 0) {

                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('csv_file')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                $csv = $this->upload->file_name;
                $data['attachment'] = $csv;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys = array('code', 'quantity', 'variant');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                // $this->sma->print_arrays($final);
                $rw = 2;
                foreach ($final as $pr) {
                    $ccode=trim($pr['code']);
                    $ccode=ltrim($ccode,"'");
                    if ($product = $this->products_model->getProductByCode($ccode)) {
                        $csv_variant = trim($pr['variant']);
                        $variant = !empty($csv_variant) ? $this->products_model->getProductVariantID($product->id, $csv_variant) : FALSE;

                        $csv_quantity = trim($pr['quantity']);
                        $type = $csv_quantity > 0 ? 'addition' : 'subtraction';
                        $quantity = $csv_quantity > 0 ? $csv_quantity : (0 - $csv_quantity);

                        if (!$this->Settings->overselling && $type == 'subtraction') {
                            if ($variant) {
                                if ($op_wh_qty = $this->products_model->getProductWarehouseOptionQty($variant, $warehouse_id)) {
                                    if ($op_wh_qty->quantity < $quantity) {
                                        $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage') . ' - ' . lang('line_no') . ' ' . $rw);
                                        redirect($_SERVER["HTTP_REFERER"]);
                                    }
                                } else {
                                    $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage') . ' - ' . lang('line_no') . ' ' . $rw);
                                    redirect($_SERVER["HTTP_REFERER"]);
                                }
                            }
                            if ($wh_qty = $this->products_model->getProductQuantity($product->id, $warehouse_id)) {
                                if ($wh_qty['quantity'] < $quantity) {
                                    $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage') . ' - ' . lang('line_no') . ' ' . $rw);
                                    redirect($_SERVER["HTTP_REFERER"]);
                                }
                            } else {
                                $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage') . ' - ' . lang('line_no') . ' ' . $rw);
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                        }

                        $products[] = array(
                            'product_id' => $product->id,
                            'type' => $type,
                            'quantity' => $quantity,
                            'warehouse_id' => $warehouse_id,
                            'option_id' => $variant,
                        );

                    } else {
                        $this->session->set_flashdata('error', lang('check_product_code') . ' (' . $pr['code'] . '). ' . lang('product_code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $rw++;
                }

            } else {
                $this->form_validation->set_rules('csv_file', lang("upload_file"), 'required');
            }

            // $this->sma->print_arrays($data, $products);

        }

        if ($this->form_validation->run() == true && $this->products_model->addAdjustment($data, $products)) {
            $this->session->set_flashdata('message', lang("quantity_adjusted"));
            admin_redirect('products/quantity_adjustments');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('add_adjustment')));
            $meta = array('page_title' => lang('add_adjustment_by_csv'), 'bc' => $bc);
            $this->page_construct('products/add_adjustment_by_csv', $meta, $this->data);

        }
    }

    /* ------------------------------------------------------------------
     *  STOCK UPLOAD (Excel) : adjustments from StockProducts.xls
     *  Columns : id, name, barcode, WhQty, mrp, cost, price, Floor Qty
     *  Total Qty = WhQty + Floor Qty ; Difference = Total Qty - System Qty
     * ------------------------------------------------------------------ */

    /**
     * Read + validate the stock excel. Returns array('rows' => [], 'errors' => []).
     * row = [id, name, barcode, whqty, mrp, cost, price, floorqty, total, system, diff]
     */
    private function _read_stock_excel($file)
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);
        $this->load->library('excel');

        $errors = array();
        $rows = array();

        try {
            $reader = PHPExcel_IOFactory::createReaderForFile($file);
            $reader->setReadDataOnly(true);
            $book = $reader->load($file);
            $data = $book->getActiveSheet()->toArray(null, false, false, false);
            $book->disconnectWorksheets();
            unset($book, $reader);
        } catch (Exception $e) {
            return array('rows' => array(), 'errors' => array('Unable to read the Excel file: ' . $e->getMessage()));
        }

        if (empty($data)) {
            return array('rows' => array(), 'errors' => array('The Excel file is empty.'));
        }

        // map header names -> column index
        $map = array();
        foreach ((array)array_shift($data) as $i => $h) {
            $map[preg_replace('/[^a-z0-9]/', '', strtolower((string)$h))] = $i;
        }
        foreach (array('id' => 'id', 'whqty' => 'WhQty', 'floorqty' => 'Floor Qty') as $k => $label) {
            if (!isset($map[$k])) {
                $errors[] = 'Required column "' . $label . '" not found in the first row of the Excel file.';
            }
        }
        if ($errors) {
            return array('rows' => array(), 'errors' => $errors);
        }

        $cell = function ($r, $key) use ($map) {
            return isset($map[$key]) && isset($r[$map[$key]]) ? $r[$map[$key]] : null;
        };
        $blank = function ($v) {
            return $v === null || trim((string)$v) === '';
        };
        $text_num = function ($v) {   // barcode as plain digits (no 4.98E+12)
            if (is_numeric($v) && floor($v) == $v && abs($v) < 1e15) {
                return sprintf('%.0f', $v);
            }
            return trim((string)$v);
        };

        $seen = array();
        foreach ($data as $idx => $r) {
            $xl_row = $idx + 2;                       // excel row number (header = 1)
            if ($blank($cell($r, 'id')) && $blank($cell($r, 'whqty')) && $blank($cell($r, 'floorqty')) && $blank($cell($r, 'name'))) {
                continue;                             // completely empty line
            }
            $id = $cell($r, 'id');
            if ($blank($id) || !is_numeric($id) || $id <= 0) {
                $errors[] = 'Row ' . $xl_row . ': invalid or blank product id.';
                continue;
            }
            $id = (int)$id;
            if (isset($seen[$id])) {
                $errors[] = 'Row ' . $xl_row . ': product id ' . $id . ' is duplicated (first seen on row ' . $seen[$id] . ').';
                continue;
            }
            $seen[$id] = $xl_row;

            $ok = true;
            $q = array();
            foreach (array('whqty' => 'WhQty', 'floorqty' => 'Floor Qty') as $k => $label) {
                $v = $cell($r, $k);
                if ($blank($v)) {
                    $errors[] = 'Row ' . $xl_row . ' (id ' . $id . '): ' . $label . ' is blank.';
                    $ok = false;
                } elseif (!is_numeric($v)) {
                    $errors[] = 'Row ' . $xl_row . ' (id ' . $id . '): ' . $label . ' "' . $v . '" is not a number.';
                    $ok = false;
                } elseif ($v < 0) {
                    $errors[] = 'Row ' . $xl_row . ' (id ' . $id . '): ' . $label . ' cannot be negative.';
                    $ok = false;
                } else {
                    $q[$k] = round((float)$v, 4);
                }
            }
            if (!$ok) {
                continue;
            }
            $rows[$id] = array(
                $id,
                trim((string)$cell($r, 'name')),
                $text_num($cell($r, 'barcode')),
                $q['whqty'],
                is_numeric($cell($r, 'mrp')) ? (float)$cell($r, 'mrp') : '',
                is_numeric($cell($r, 'cost')) ? round((float)$cell($r, 'cost'), 4) : '',
                is_numeric($cell($r, 'price')) ? (float)$cell($r, 'price') : '',
                $q['floorqty'],
                round($q['whqty'] + $q['floorqty'], 4),
                0, 0,
            );
        }
        unset($data);

        if (empty($rows) && empty($errors)) {
            $errors[] = 'No product rows found in the Excel file.';
        }

        // system quantity
        if ($rows) {
            $sys = $this->products_model->getSystemQuantities(array_keys($rows));
            foreach ($rows as $id => &$row) {
                if (!isset($sys[$id])) {
                    $errors[] = 'Row ' . $seen[$id] . ': product id ' . $id . ' (' . $row[1] . ') does not exist in the system.';
                    unset($rows[$id]);
                    continue;
                }
                $row[9] = round((float)$sys[$id], 4);
                $row[10] = round($row[8] - $row[9], 4);
            }
            unset($row);
        }

        return array('rows' => array_values($rows), 'errors' => $errors);
    }

    /** Handle the excel upload of the stock screens. Returns array('rows','errors') or NULL when no file posted */
    private function _handle_stock_upload($field = 'stock_file')
    {
        if (empty($_FILES[$field]['name'])) {
            return array('rows' => array(), 'errors' => array('Please select the Excel file (.xls / .xlsx) to upload.'));
        }
        $this->load->library('upload');
        $config['upload_path'] = APPPATH . 'cache/';
        $config['allowed_types'] = 'xls|xlsx';
        $config['max_size'] = 20480;
        $config['overwrite'] = false;
        $config['encrypt_name'] = true;
        $this->upload->initialize($config);
        if (!$this->upload->do_upload($field)) {
            return array('rows' => array(), 'errors' => array(strip_tags($this->upload->display_errors('', ''))));
        }
        $path = $this->upload->data('full_path');
        $res = $this->_read_stock_excel($path);
        @unlink($path);                      // the uploaded file is not needed any more
        return $res;
    }

    private function _stock_cache_file($token)
    {
        return APPPATH . 'cache/stockupload_' . preg_replace('/[^a-f0-9]/', '', $token) . '.json';
    }

    /** Screen 1 : upload excel -> preview (WhQty + Floor Qty vs system qty) -> save as adjustment */
    function stock_upload()
    {
        $this->sma->checkPermissions('adjustments', true);
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');

        $this->data['rows'] = null;
        $this->data['upload_errors'] = array();
        $this->data['token'] = null;

        if ($this->input->post('upload_stock')) {
            if ($this->form_validation->run() == true) {
                $res = $this->_handle_stock_upload();
                if ($res['errors']) {
                    $this->data['upload_errors'] = $res['errors'];
                } else {
                    // purge old previews, keep this one server side (never trust posted quantities)
                    foreach ((array)glob(APPPATH . 'cache/stockupload_*.json') as $old) {
                        if (filemtime($old) < time() - 86400) {
                            @unlink($old);
                        }
                    }
                    $token = md5(uniqid(mt_rand(), true));
                    $meta = array(
                        'user_id' => $this->session->userdata('user_id'),
                        'warehouse_id' => $this->input->post('warehouse'),
                        'date' => ($this->Owner || $this->Admin) ? $this->input->post('date') : '',
                        'reference_no' => $this->input->post('reference_no'),
                        'note' => $this->sma->clear_tags($this->input->post('note')),
                        'rows' => $res['rows'],
                    );
                    file_put_contents($this->_stock_cache_file($token), json_encode($meta));
                    $this->data['rows'] = $res['rows'];
                    $this->data['token'] = $token;
                }
            }
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('stock_upload')));
        $meta = array('page_title' => lang('stock_upload'), 'bc' => $bc);
        $this->page_construct('products/stock_upload', $meta, $this->data);
    }

    /** Save the previewed excel into stock_diff / stock_diff_items and sync the difference into products / warehouses_products. */
    function stock_upload_save()
    {
        $this->sma->checkPermissions('adjustments', true);
        $token = $this->input->post('upload_token');
        $file = $token ? $this->_stock_cache_file($token) : '';
        $cache = ($file && is_file($file)) ? json_decode(file_get_contents($file), true) : null;
        if (!$cache || $cache['user_id'] != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error', 'Upload session expired. Please upload the Excel file again.');
            admin_redirect('products/stock_upload');
        }

        $warehouse_id = $cache['warehouse_id'];
        $date = ($this->Owner || $this->Admin) && $cache['date'] ? $this->sma->fld($cache['date']) : date('Y-m-d H:i:s');
        $reference_no = $cache['reference_no'] ? $cache['reference_no'] : 'SD' . date('YmdHis');

        $items = array();
        foreach ($cache['rows'] as $r) {
            if ($r[10] == 0) {
                continue;               // only products with a difference are stored
            }
            $items[] = array(
                'product_id' => $r[0],
                'warehouse_id' => $warehouse_id,
                'wh_qty' => $r[3],
                'floor_qty' => $r[7],
                'total_qty' => $r[8],
                'system_qty' => $r[9],
                'difference' => $r[10],
                'type' => $r[10] > 0 ? 'addition' : 'subtraction',
                'mrp' => $r[4] === '' ? NULL : $r[4],
                'cost' => $r[5] === '' ? NULL : $r[5],
                'price' => $r[6] === '' ? NULL : $r[6],
            );
        }
        if (empty($items)) {
            $this->session->set_flashdata('error', 'All quantities already match the system. Nothing to save.');
            admin_redirect('products/stock_upload');
        }

        $data = array(
            'date' => $date,
            'reference_no' => $reference_no,
            'warehouse_id' => $warehouse_id,
            'note' => $cache['note'],
            'total_products' => count($cache['rows']),
            'diff_products' => count($items),
            'created_by' => $this->session->userdata('user_id'),
        );

        if ($this->products_model->addStockDiff($data, $items)) {
            @unlink($file);
            $this->session->set_flashdata('message', 'Stock upload saved (' . $reference_no . '): ' . count($items) . ' products with difference. Product / warehouse quantities were updated.');
            admin_redirect('products/stock_upload');
        }
        $this->session->set_flashdata('error', 'Unable to save. Please check that the stock_diff tables exist and try again.');
        admin_redirect('products/stock_upload');
    }

    /** Listing of the records created by products/stock_upload (stock_diff) */
    function stock_upload_list($warehouse_id = NULL)
    {
        $this->sma->checkPermissions('adjustments');

        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('stock_upload_list')));
        $meta = array('page_title' => lang('stock_upload_list'), 'bc' => $bc);
        $this->page_construct('products/stock_upload_list', $meta, $this->data);
    }

    function get_stock_uploads($warehouse_id = NULL)
    {
        $this->sma->checkPermissions('adjustments');

        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }

        $delete_link = "<a href='#' class='tip po' title='<b>" . lang("delete_stock_upload") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('products/delete_stock_upload/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('stock_diff')}.id as id, {$this->db->dbprefix('stock_diff')}.date, {$this->db->dbprefix('stock_diff')}.reference_no, warehouses.name as wh_name, {$this->db->dbprefix('stock_diff')}.total_products, {$this->db->dbprefix('stock_diff')}.diff_products, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by, {$this->db->dbprefix('stock_diff')}.note")
            ->from('stock_diff')
            ->join('warehouses', 'warehouses.id=stock_diff.warehouse_id', 'left')
            ->join('users', 'users.id=stock_diff.created_by', 'left')
            ->group_by("stock_diff.id");
        if ($warehouse_id) {
            $this->datatables->where('stock_diff.warehouse_id', $warehouse_id);
        }
        // only the delete action
        $this->datatables->add_column("Actions", "<div class='text-center'>" . $delete_link . "</div>", "id");

        echo $this->datatables->generate();
    }

    /** popup (same as view_adjustment) */
    public function view_stock_upload($id = NULL)
    {
        $this->sma->checkPermissions('adjustments', TRUE);

        $inv = $id ? $this->products_model->getStockDiffByID($id) : false;
        if (!$inv) {
            $this->session->set_flashdata('error', lang('stock_upload_not_found'));
            $this->sma->md();
        }

        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->products_model->getStockDiffItems($id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->load->view($this->theme . 'products/view_stock_upload', $this->data);
    }

    function delete_stock_upload($id = NULL)
    {
        $this->sma->checkPermissions('delete', TRUE);

        if ($id && $this->products_model->deleteStockDiff($id)) {
            $this->sma->send_json(array('error' => 0, 'msg' => lang("stock_upload_deleted")));
        }
        $this->sma->send_json(array('error' => 1, 'msg' => lang("stock_upload_not_found")));
    }

    /** Screen 2 : upload the same excel and verify Total Qty (WhQty + Floor Qty) against the system quantity */
    function stock_check()
    {
        $this->sma->checkPermissions('adjustments', true);
        $this->data['rows'] = null;
        $this->data['upload_errors'] = array();
        $this->data['mismatch'] = 0;
       // echo "string".$this->input->post('check_stock');die();
        if ($this->input->post('check_stock')) {
            $res = $this->_handle_stock_upload();
            if ($res['errors']) {
                $this->data['upload_errors'] = $res['errors'];
            } else {
                $this->data['rows'] = $res['rows'];
                foreach ($res['rows'] as $r) {
                    if ($r[10] != 0) {
                        $this->data['mismatch']++;
                    }
                }
            }
        }
        else
        {
            //echo "string";die();
        }

        $this->data['error'] = $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('stock_check')));
        $meta = array('page_title' => lang('stock_check'), 'bc' => $bc);
        $this->page_construct('products/stock_check', $meta, $this->data);
    }

    function delete_adjustment($id = NULL)
    {
        $this->sma->checkPermissions('delete', TRUE);

        if ($this->products_model->deleteAdjustment($id)) {
            $this->sma->send_json(array('error' => 0, 'msg' => lang("adjustment_deleted")));
        }

    }

    /* --------------------------------------------------------------------------------------------- */

    function modal_view($id = NULL)
    {
        $this->sma->checkPermissions('index', TRUE);

        $pr_details = $this->site->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            $this->sma->md();
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product'] = $pr_details;
        $this->data['unit'] = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand'] = $this->site->getBrandByID($pr_details->brand);
        $this->data['images'] = $this->products_model->getProductPhotos($id);
        $this->data['category'] = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : NULL;
        $this->data['tax_rate'] = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : NULL;
        $this->data['warehouses'] = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants'] = $this->products_model->getProductOptions($id);

        $this->load->view($this->theme . 'products/modal_view', $this->data);
    }

    function view($id = NULL)
    {
        $this->sma->checkPermissions('index');

        $pr_details = $this->products_model->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product'] = $pr_details;
        $this->data['unit'] = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand'] = $this->site->getBrandByID($pr_details->brand);
        $this->data['images'] = $this->products_model->getProductPhotos($id);
        $this->data['category'] = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : NULL;
        $this->data['tax_rate'] = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : NULL;
        $this->data['popup_attributes'] = $this->popup_attributes;
        $this->data['warehouses'] = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants'] = $this->products_model->getProductOptions($id);
        $this->data['sold'] = $this->products_model->getSoldQty($id);
        $this->data['purchased'] = $this->products_model->getPurchasedQty($id);

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => $pr_details->name));
        $meta = array('page_title' => $pr_details->name, 'bc' => $bc);
        $this->page_construct('products/view', $meta, $this->data);
    }

    function pdf($id = NULL, $view = NULL)
    {
        $this->sma->checkPermissions('index');

        $pr_details = $this->products_model->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product'] = $pr_details;
        $this->data['unit'] = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand'] = $this->site->getBrandByID($pr_details->brand);
        $this->data['images'] = $this->products_model->getProductPhotos($id);
        $this->data['category'] = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : NULL;
        $this->data['tax_rate'] = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : NULL;
        $this->data['popup_attributes'] = $this->popup_attributes;
        $this->data['warehouses'] = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants'] = $this->products_model->getProductOptions($id);

        $name = $pr_details->code . '_' . str_replace('/', '_', $pr_details->name) . ".pdf";
        if ($view) {
            $this->load->view($this->theme . 'products/pdf', $this->data);
        } else {
            $html = $this->load->view($this->theme . 'products/pdf', $this->data, TRUE);
            if (!$this->Settings->barcode_img) {
                $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
            }
            $this->sma->generate_pdf($html, $name);
        }
    }

    function getSubCategories($category_id = NULL)
    {
        if ($rows = $this->products_model->getSubCategories($category_id)) {
            $data = json_encode($rows);
        } else {
            $data = false;
        }
        echo $data;
    }

    function product_actions($wh = NULL)
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'sync_quantity') {

                    foreach ($_POST['val'] as $id) {
                        $this->site->syncQuantity(NULL, NULL, NULL, $id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("products_quantity_sync"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'delete') {

                    $this->sma->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->products_model->deleteProduct($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("products_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'labels') {

                    foreach ($_POST['val'] as $id) {
                        $row = $this->products_model->getProductByID($id);
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                    }

                    $this->data['items'] = isset($pr) ? json_encode($pr) : false;
                    $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                    $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('print_barcodes')));
                    $meta = array('page_title' => lang('print_barcodes'), 'bc' => $bc);
                    $this->page_construct('products/print_barcodes', $meta, $this->data);

                } elseif ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('Products');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('barcode_symbology'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('brand'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('category_code'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('unit_code'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('sale') . ' ' . lang('unit_code'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('purchase') . ' ' . lang('unit_code'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('cost'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('price'));
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('alert_quantity'));
                    $this->excel->getActiveSheet()->SetCellValue('L1', lang('tax_rate'));
                    $this->excel->getActiveSheet()->SetCellValue('M1', lang('tax_method'));
                    $this->excel->getActiveSheet()->SetCellValue('N1', lang('image'));
                    $this->excel->getActiveSheet()->SetCellValue('O1', lang('subcategory_code'));
                    $this->excel->getActiveSheet()->SetCellValue('P1', lang('product_variants'));
                    $this->excel->getActiveSheet()->SetCellValue('Q1', lang('pcf1'));
                    $this->excel->getActiveSheet()->SetCellValue('R1', lang('pcf2'));
                    $this->excel->getActiveSheet()->SetCellValue('S1', lang('pcf3'));
                    $this->excel->getActiveSheet()->SetCellValue('T1', lang('pcf4'));
                    $this->excel->getActiveSheet()->SetCellValue('U1', lang('pcf5'));
                    $this->excel->getActiveSheet()->SetCellValue('V1', lang('pcf6'));
                    $this->excel->getActiveSheet()->SetCellValue('W1', lang('quantity'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $product = $this->products_model->getProductDetail($id);
                        $brand = $this->site->getBrandByID($product->brand);
                        $base_unit = $sale_unit = $purchase_unit = '';
                        if ($units = $this->site->getUnitsByBUID($product->unit)) {
                            foreach ($units as $u) {
                                if ($u->id == $product->unit) {
                                    $base_unit = $u->code;
                                }
                                if ($u->id == $product->sale_unit) {
                                    $sale_unit = $u->code;
                                }
                                if ($u->id == $product->purchase_unit) {
                                    $purchase_unit = $u->code;
                                }
                            }
                        }
                        $variants = $this->products_model->getProductOptions($id);
                        $product_variants = '';
                        if ($variants) {
                            foreach ($variants as $variant) {
                                $product_variants .= trim($variant->name) . '|';
                            }
                        }
                        $quantity = $product->quantity;
                        if ($wh) {
                            if ($wh_qty = $this->products_model->getProductQuantity($id, $wh)) {
                                $quantity = $wh_qty['quantity'];
                            } else {
                                $quantity = 0;
                            }
                        }
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $product->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $product->code);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $product->barcode_symbology);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, ($brand ? $brand->name : ''));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $product->category_code);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $base_unit);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sale_unit);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $purchase_unit);
                        if ($this->Owner || $this->Admin || $this->session->userdata('show_cost')) {
                            $this->excel->getActiveSheet()->SetCellValue('I' . $row, $product->cost);
                        }
                        if ($this->Owner || $this->Admin || $this->session->userdata('show_price')) {
                            $this->excel->getActiveSheet()->SetCellValue('J' . $row, $product->price);
                        }
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, $product->alert_quantity);
                        $this->excel->getActiveSheet()->SetCellValue('L' . $row, $product->tax_rate_name);
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, $product->tax_method ? lang('exclusive') : lang('inclusive'));
                        $this->excel->getActiveSheet()->SetCellValue('N' . $row, $product->image);
                        $this->excel->getActiveSheet()->SetCellValue('O' . $row, $product->subcategory_code);
                        $this->excel->getActiveSheet()->SetCellValue('P' . $row, $product_variants);
                        $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $product->cf1);
                        $this->excel->getActiveSheet()->SetCellValue('R' . $row, $product->cf2);
                        $this->excel->getActiveSheet()->SetCellValue('S' . $row, $product->cf3);
                        $this->excel->getActiveSheet()->SetCellValue('T' . $row, $product->cf4);
                        $this->excel->getActiveSheet()->SetCellValue('U' . $row, $product->cf5);
                        $this->excel->getActiveSheet()->SetCellValue('V' . $row, $product->cf6);
                        $this->excel->getActiveSheet()->SetCellValue('W' . $row, $quantity);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(40);
                    $this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(30);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'products_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    return create_excel($this->excel, $filename);

                }
                elseif ($this->input->post('form_action') == 'export_qty_csv') {
                     $filename = 'productsqty_'.date('Y_m_d_H_i_s').'.csv';
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=$filename");
                header("Content-Type: application/csv; ");

                $allproducts = array();
                $file = fopen('php://output', 'w');
                $header = array(lang('code'),lang('quantity'));
                fputcsv($file, $header);
                   

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $value = $this->products_model->getProductDetail($id);
                        
                        
                        $quantity = $value->quantity;
                       
                       
                          $allproducts=array(
                        'code'              => "'".$value->code,
                       'quantity'          => $quantity
                    );

                    fputcsv($file,$allproducts);
                    }

                     fclose($file);
                exit;

                }
            } elseif ($this->input->post('form_action') == 'all_export_excel') {
                // file name
                $filename = 'all_products_'.date('Y_m_d_H_i_s').'.csv';
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=$filename");
                header("Content-Type: application/csv; ");

                // get data
                $products = $this->products_model->getAllProductDetail();
                $allproducts = array();
                $file = fopen('php://output', 'w');
                $header = array(lang('code'),lang('name'),lang('brand'),lang('category_code'),lang('unit_code'),lang('sale') . ' ' . lang('unit_code'),lang('purchase') . ' ' . lang('unit_code'),lang('cost'),lang('price'),lang('mrp'),lang('gst_rate'),lang('alert_quantity'),lang('subcategory_code'),lang('product_variants'),lang('quantity'));
                fputcsv($file, $header);
                foreach ($products as $value) {
                    $brand = $this->site->getBrandByID($value->brand);
                    $base_unit = $sale_unit = $purchase_unit = '';
                    if ($units = $this->site->getUnitsByBUID($value->unit)) {
                        foreach ($units as $u) {
                            if ($u->id == $value->unit) {
                                $base_unit = $u->code;
                            }
                            if ($u->id == $value->sale_unit) {
                                $sale_unit = $u->code;
                            }
                            if ($u->id == $value->purchase_unit) {
                                $purchase_unit = $u->code;
                            }
                        }
                    }
                    $variants = $this->products_model->getProductOptions($value->id);


                    $product_variants = '';
                    if ($variants) {
                        foreach ($variants as $variant) {
                            $product_variants .= trim($variant->name) . '|';
                        }
                    }
                    $quantity = $value->quantity;
                    if ($wh) {
                        if ($wh_qty = $this->products_model->getProductQuantity($value->id, $wh)) {
                            $quantity = $wh_qty['quantity'];
                        } else {
                            $quantity = 0;
                        }
                    }
                    if ($this->Owner || $this->Admin || $this->session->userdata('show_cost'))
                    {
                        $cost = $value->cost;
                    }
                    if ($this->Owner || $this->Admin || $this->session->userdata('show_price'))
                    {
                        $price = $value->price;
                    }
                    $mrp = $value->mrp;
                    $allproducts=array(
                        'code'              => "'".$value->code,
                        'name'              => $value->name,

                        'brand'             => $brand ? $brand->name : '',
                        'category_code'     => $value->category_code,
                        'unit_code'         => $base_unit,
                        'sale_unit'         => $sale_unit,
                        'purchase_unit'     => $purchase_unit,
                        'cost'              => $cost,
                        'price'             => $price,
						 'mrp'              => $mrp,
						  'gst_rate'    => $value->gst_rate,
                        'alert_quantity'    => $value->alert_quantity,

                        'subcategory_code'  => $value->subcategory_code,
                        'product_variants'  => $product_variants,
                        /*'cf1'               => $value->cf1,
                        'cf2'               => $value->cf2,
                        'cf3'               => $value->cf3,
                        'cf4'               => $value->cf4,
                        'cf5'               => $value->cf5,
                        'cf6'               => $value->cf6,*/
                        'quantity'          => $quantity,
                    );

                    fputcsv($file,$allproducts);
                }
                fclose($file);
                exit;

            }
              if ( ($this->input->post('form_action') == 'export_points_excel')
                                                          
                                                          
                                                              
                                                    
                                                     
                                                      
                                                                      

                ||   ($this->input->get('form_action') == 'export_points_excel')
            ) {
                   // echo "aaaa";die();
                    $products = $this->products_model->getAllProductDetail();
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('Products');
                  
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('id'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('category'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('points'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('desc'));
                    

                    $row = 2;

                        foreach ($products as $value) {


                                $this->excel->getActiveSheet()->SetCellValue('A'.$row, $value->id);
                                $this->excel->getActiveSheet()->SetCellValue('B'.$row,  $value->name);
                                $this->excel->getActiveSheet()->SetCellValue('C'.$row,  $value->category);
                                $this->excel->getActiveSheet()->SetCellValue('D'.$row,  $value->enable_sale_point?'1':'0');
                                //$this->excel->getActiveSheet()->SetCellValue('D'.$row,  $value->code);
                                $this->excel->getActiveSheet()->setCellValueExplicit("E". $row, $value->code.'', PHPExcel_Cell_DataType::TYPE_STRING);
                                $this->excel->getActiveSheet()->SetCellValue('F'.$row,  $value->details);

                                //fputcsv($file,$allproducts);
                                $row++;
                        }
                
                    

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
                  
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'products_enable_points_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    return create_excel($this->excel, $filename);
                    

                }
           else {
                $this->session->set_flashdata('error', $this->lang->line("no_product_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
           
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'admin/products');
        }
    }

    public function delete_image($id = NULL)
    {
        $this->sma->checkPermissions('edit', true);
        if ($id && $this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            $this->db->delete('product_photos', array('id' => $id));
            $this->sma->send_json(array('error' => 0, 'msg' => lang("image_deleted")));
        }
        $this->sma->send_json(array('error' => 1, 'msg' => lang("ajax_error")));
    }

    public function getSubUnits($unit_id)
    {
        // $unit = $this->site->getUnitByID($unit_id);
        // if ($units = $this->site->getUnitsByBUID($unit_id)) {
        //     array_push($units, $unit);
        // } else {
        //     $units = array($unit);
        // } 
        $units = $this->site->getUnitsByBUID($unit_id);
        $this->sma->send_json($units);
    }

    public function qa_suggestions()
    {
        $term = $this->input->get('term', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->sma->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $rows = $this->products_model->getQASuggestions($sr);
        if ($rows) {
            foreach ($rows as $row) {
                $row->qty = 1;
                $options = $this->products_model->getProductOptions($row->id);
                $row->option = $option_id;
                $row->serial = '';

                $pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'options' => $options);

            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    function adjustment_actions()
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
                        $this->products_model->deleteAdjustment($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("adjustment_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('quantity_adjustments');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('items'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $adjustment = $this->products_model->getAdjustmentByID($id);
                        $created_by = $this->site->getUser($adjustment->created_by);
                        $warehouse = $this->site->getWarehouseByID($adjustment->warehouse_id);
                        $items = $this->products_model->getAdjustmentItems($id);
                        $products = '';
                        if ($items) {
                            foreach ($items as $item) {
                                $products .= $item->product_name . '(' . $this->sma->formatQuantity($item->type == 'subtraction' ? -$item->quantity : $item->quantity) . ')' . "\n";
                            }
                        }

                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($adjustment->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $adjustment->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $warehouse->name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $created_by->first_name . ' ' . $created_by->last_name);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->sma->decode_html($adjustment->note));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $products);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'quantity_adjustments_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    return create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    function stock_counts($warehouse_id = NULL)
    {
        $this->sma->checkPermissions('stock_count');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('stock_counts')));
        $meta = array('page_title' => lang('stock_counts'), 'bc' => $bc);
        $this->page_construct('products/stock_counts', $meta, $this->data);
    }

    function getCounts($warehouse_id = NULL)
    {
        $this->sma->checkPermissions('stock_count', TRUE);

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('admin/products/view_count/$1', '<label class="label label-primary pointer">' . lang('details') . '</label>', 'class="tip" title="' . lang('details') . '" data-toggle="modal" data-target="#myModal"');

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('stock_counts')}.id as id, date, reference_no, {$this->db->dbprefix('warehouses')}.name as wh_name, type, brand_names, category_names, initial_file")
            ->from('stock_counts')
            ->join('warehouses', 'warehouses.id=stock_counts.warehouse_id', 'left');
        if ($warehouse_id) {
            $this->datatables->where('warehouse_id', $warehouse_id);
        }

        $this->datatables->add_column('Actions', '<div class="text-center">' . $detail_link . '</div>', "id");
        echo $this->datatables->generate();
    }

    function view_count($id)
    {
        $this->sma->checkPermissions('stock_count', TRUE);
        $stock_count = $this->products_model->getStouckCountByID($id);
        if (!$stock_count->finalized) {
            $this->sma->md('admin/products/finalize_count/' . $id);
        }

        $this->data['stock_count'] = $stock_count;
        $this->data['stock_count_items'] = $this->products_model->getStockCountItems($id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($stock_count->warehouse_id);
        $this->data['adjustment'] = $this->products_model->getAdjustmentByCountID($id);
        $this->load->view($this->theme . 'products/view_count', $this->data);
    }

    function count_stock($page = NULL)
    {
        $this->sma->checkPermissions('stock_count');
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
        $this->form_validation->set_rules('type', lang("type"), 'required');

        if ($this->form_validation->run() == true) {

            $warehouse_id = $this->input->post('warehouse');
            $type = $this->input->post('type');
            $categories = $this->input->post('category') ? $this->input->post('category') : NULL;
            $brands = $this->input->post('brand') ? $this->input->post('brand') : NULL;
            $this->load->helper('string');
            $name = random_string('md5') . '.csv';
            $products = $this->products_model->getStockCountProducts($warehouse_id, $type, $categories, $brands);
            $pr = 0;
            $rw = 0;
            foreach ($products as $product) {
                if ($variants = $this->products_model->getStockCountProductVariants($warehouse_id, $product->id)) {
                    foreach ($variants as $variant) {
                        $items[] = array(
                            'product_code' => $product->code,
                            'product_name' => $product->name,
                            'variant' => $variant->name,
                            'expected' => $variant->quantity,
                            'counted' => ''
                        );
                        $rw++;
                    }
                } else {
                    $items[] = array(
                        'product_code' => $product->code,
                        'product_name' => $product->name,
                        'variant' => '',
                        'expected' => $product->quantity,
                        'counted' => ''
                    );
                    $rw++;
                }
                $pr++;
            }
            if (!empty($items)) {
                $csv_file = fopen('./files/' . $name, 'w');
                fputcsv($csv_file, array(lang('product_code'), lang('product_name'), lang('variant'), lang('expected'), lang('counted')));
                foreach ($items as $item) {
                    fputcsv($csv_file, $item);
                }
                // file_put_contents('./files/'.$name, $csv_file);
                // fwrite($csv_file, $txt);
                fclose($csv_file);
            } else {
                $this->session->set_flashdata('error', lang('no_product_found'));
                redirect($_SERVER["HTTP_REFERER"]);
            }

            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:s:i');
            }
            $category_ids = '';
            $brand_ids = '';
            $category_names = '';
            $brand_names = '';
            if ($categories) {
                $r = 1;
                $s = sizeof($categories);
                foreach ($categories as $category_id) {
                    $category = $this->site->getCategoryByID($category_id);
                    if ($r == $s) {
                        $category_names .= $category->name;
                        $category_ids .= $category->id;
                    } else {
                        $category_names .= $category->name . ', ';
                        $category_ids .= $category->id . ', ';
                    }
                    $r++;
                }
            }
            if ($brands) {
                $r = 1;
                $s = sizeof($brands);
                foreach ($brands as $brand_id) {
                    $brand = $this->site->getBrandByID($brand_id);
                    if ($r == $s) {
                        $brand_names .= $brand->name;
                        $brand_ids .= $brand->id;
                    } else {
                        $brand_names .= $brand->name . ', ';
                        $brand_ids .= $brand->id . ', ';
                    }
                    $r++;
                }
            }
            $data = array(
                'date' => $date,
                'warehouse_id' => $warehouse_id,
                'reference_no' => $this->input->post('reference_no'),
                'type' => $type,
                'categories' => $category_ids,
                'category_names' => $category_names,
                'brands' => $brand_ids,
                'brand_names' => $brand_names,
                'initial_file' => $name,
                'products' => $pr,
                'rows' => $rw,
                'created_by' => $this->session->userdata('user_id')
            );

        }

        if ($this->form_validation->run() == true && $this->products_model->addStockCount($data)) {
            $this->session->set_flashdata('message', lang("stock_count_intiated"));
            admin_redirect('products/stock_counts');

        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['brands'] = $this->site->getAllBrands();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('count_stock')));
            $meta = array('page_title' => lang('count_stock'), 'bc' => $bc);
            $this->page_construct('products/count_stock', $meta, $this->data);

        }

    }

    function finalize_count($id)
    {
        $this->sma->checkPermissions('stock_count');
        $stock_count = $this->products_model->getStouckCountByID($id);
        if (!$stock_count || $stock_count->finalized) {
            $this->session->set_flashdata('error', lang("stock_count_finalized"));
            admin_redirect('products/stock_counts');
        }

        $this->form_validation->set_rules('count_id', lang("count_stock"), 'required');

        if ($this->form_validation->run() == true) {

            if ($_FILES['csv_file']['size'] > 0) {
                $note = $this->sma->clear_tags($this->input->post('note'));
                $data = array(
                    'updated_by' => $this->session->userdata('user_id'),
                    'updated_at' => date('Y-m-d H:s:i'),
                    'note' => $note
                );

                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('csv_file')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys = array('product_code', 'product_name', 'product_variant', 'expected', 'counted');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                // $this->sma->print_arrays($final);
                $rw = 2;
                $differences = 0;
                $matches = 0;
                foreach ($final as $pr) {
                    if ($product = $this->products_model->getProductByCode(trim($pr['product_code']))) {
                        $pr['counted'] = !empty($pr['counted']) ? $pr['counted'] : 0;
                        if ($pr['expected'] == $pr['counted']) {
                            $matches++;
                        } else {
                            $pr['stock_count_id'] = $id;
                            $pr['product_id'] = $product->id;
                            $pr['cost'] = $product->cost;
                            $pr['product_variant_id'] = empty($pr['product_variant']) ? NULL : $this->products_model->getProductVariantID($pr['product_id'], $pr['product_variant']);
                            $products[] = $pr;
                            $differences++;
                        }
                    } else {
                        $this->session->set_flashdata('error', lang('check_product_code') . ' (' . $pr['product_code'] . '). ' . lang('product_code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                        admin_redirect('products/finalize_count/' . $id);
                    }
                    $rw++;
                }

                $data['final_file'] = $csv;
                $data['differences'] = $differences;
                $data['matches'] = $matches;
                $data['missing'] = $stock_count->rows - ($rw - 2);
                $data['finalized'] = 1;
            }

            // $this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->products_model->finalizeStockCount($id, $data, $products)) {
            $this->session->set_flashdata('message', lang("stock_count_finalized"));
            admin_redirect('products/stock_counts');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['stock_count'] = $stock_count;
            $this->data['warehouse'] = $this->site->getWarehouseByID($stock_count->warehouse_id);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => admin_url('products/stock_counts'), 'page' => lang('stock_counts')), array('link' => '#', 'page' => lang('finalize_count')));
            $meta = array('page_title' => lang('finalize_count'), 'bc' => $bc);
            $this->page_construct('products/finalize_count', $meta, $this->data);

        }

    }

    // For GST Lookup

    function gst_lookup()
    {
        $this->sma->checkPermissions('index', TRUE);


        $this->load->view($this->theme . 'products/gst_lookup', $this->data);

    }

    function GST_results($search='',$type='',$srcby='')
    {


        if ( $search!='' ) {

            echo $this->products_model->get_gst_autopopulate($search, $type, $srcby);

        }


    }

    function GST_rates()
    {
        $search = $this->input->post('srcstr');
        $srcby = $this->input->post('searchby');

        if ( $search!='' ) {

            echo $this->products_model->get_gst_Rate($search,  $srcby);

        }


    }

    function import_alert_quantity(){

        $this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (isset($_FILES["userfile"])) {

                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = TRUE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("products/import_alert_quantity");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('code', 'name','alert_quantity');

                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                //  $this->sma->print_arrays($final);
                $rw = 2;
                $items = array();
                foreach ($final as $csv_pr) {
                    $pr_code = str_replace("'",'',$csv_pr['code']);
                    // $this->sma->print_arrays($pr_code);
                    if ($this->products_model->getProductByCode(trim($pr_code))) {

                        $items = array(
                            // 'code' => trim($pr_code),
                            //'name' => trim($csv_pr['name']),
                            'alert_quantity' => $csv_pr['alert_quantity'],

                        );

                        $this->db->update('products', $items, array('code' => $pr_code));
                    }
                    else {
                        $this->session->set_flashdata('error', lang("check_product_code") . " (" . $csv_pr['name'] . "). " . lang("line_no") . " " . $rw);
                        admin_redirect("products/import_alert_quantity");
                    }
                    $rw++;
                }
            }

            // $this->sma->print_arrays($items);die;
        }

        if ($this->form_validation->run() == true) {
            $this->session->set_flashdata('message', sprintf(lang("alert_quantity_updated")));
            admin_redirect('products/import_alert_quantity');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('set_alert_quantity')));
            $meta = array('page_title' => lang('set_alert_quantity'), 'bc' => $bc);
            $this->page_construct('products/import_alert_quantity', $meta, $this->data);

        }
    }

    function download_products(){

        $filename = 'all_products_alert'.date('Y_m_d_H_i_s').'.csv';
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv; ");

        // get data
        $products = $this->products_model->getAllProductDetail();
        $allproducts = array();
        $file = fopen('php://output', 'w');
        $header = array(lang('code'),lang('name'),lang('alert_quantity'));
        fputcsv($file, $header);
        foreach ($products as $value) {

            $allproducts=array(
                'code'              => "'".$value->code,
                'name'              => $value->name,
                'alert_quantity'    => $value->alert_quantity,
            );

            fputcsv($file,$allproducts);
        }
        fclose($file);
        exit;

    }
	 function print_signages($product_id = NULL)
    {

        $pur_items=array();
        $pur_id=NULL;

        if($this->input->get("pur_id"))
        {
            $pur_id=$this->input->get("pur_id");
        }
        if($pur_id)
        {

           $pur_items=$this->purchases_model->getBCItems($pur_id);
          // echo "".json_encode($pur_items);

        }   //die();
        //$this->sma->print_arrays($_POST);
        $this->sma->checkPermissions('barcode', true);

        $this->form_validation->set_rules('style', lang("style"), 'required');

        if ($this->form_validation->run() == true) {

            $style = $this->input->post('style');
            $bci_size = ($style == 10 || $style == 12 ? 50 : ($style == 14 || $style == 18 ? 30 : 20));
            $currencies = $this->site->getAllCurrencies();
            $s = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            if ($s < 1) {
                $this->session->set_flashdata('error', lang('no_product_selected'));
                admin_redirect("products/print_signages");
            }


            for ($m = 0; $m < $s; $m++) {
                $pid = $_POST['product'][$m];
                $quantity = $_POST['quantity'][$m];
                $product = $this->products_model->getProductWithCategory($pid);
                $product->price = $this->input->post('check_promo') ? ($product->promotion ? $product->promo_price : $product->price) : $product->price;
                if ($variants = $this->products_model->getProductOptions($pid)) {
                    foreach ($variants as $option) {
                        if ($this->input->post('vt_' . $product->id . '_' . $option->id)) {
                            $barcodes[] = array(
                                'site' => $this->input->post('site_name') ? $this->Settings->site_name : FALSE,
                                'name' => $this->input->post('product_name') ? $product->name . ' - ' . $option->name : FALSE,
                                'image' => $this->input->post('product_image') ? $product->image : FALSE,
                                'barcode' => $this->product_barcode($product->code . $this->Settings->barcode_separator . $option->id, 'code128', $bci_size),
                                'price' => $this->input->post('price') ? $this->sma->formatMoney($option->price != 0 ? ($product->price + $option->price) : $product->price) : FALSE,
                                'unit' => $this->input->post('unit') ? $product->unit : FALSE,
                                'category' => $this->input->post('category') ? $product->category : FALSE,
                                'currencies' => $this->input->post('currencies'),
                                'variants' => $this->input->post('variants') ? $variants : FALSE,
                                'quantity' => $quantity
                            );
                        }
                    }
                } else {
                    $barcodes[] = array(
                        'site' => $this->input->post('site_name') ? $this->Settings->site_name : FALSE,
                        'name' => $product->name ,
                        'marathi_name' => $product->marathi_name ? $product->marathi_name : FALSE,
                        'mrp' => $product->mrp,
                        'barcode' => $this->product_barcode($product->code, $product->barcode_symbology, $bci_size),
                        'price' => $product->price ,
                        'unit' => $product->unit ,
                        // 'category' => $this->input->post('category') ? $product->category : FALSE,
                        'langauge' => $this->input->post('langauge'),
                        // 'variants' => FALSE,
                        'quantity' => $quantity
                    );
                }
                $values [] =array('name' => $this->input->post('product_name') ? $product->name : FALSE,
                    'barcode' => "'".$product->code,
                    'price' => $this->input->post('price') ? $product->price : FALSE,
                    'quantity' => $quantity,
                    'mrp' => $product->mrp,
                );
            }



            // create_csv($columns,$values, $filename);
            $this->data['barcodes'] = $barcodes;
            // $this->data['currencies'] = $currencies;
            $this->data['style'] = $style;
            $this->data['items'] = false;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('print_signages')));
            $meta = array('page_title' => lang('print_signages'), 'bc' => $bc);
            $this->page_construct('products/print_signages', $meta, $this->data);

        } else {




            if(empty($pur_items))
            {

            $this->data['items'] = isset($pr) ? json_encode($pr) : false;
            }
            else
            {
                //$this->data['items'] = isset($pur_items) ? json_encode($pur_items) : false;

                foreach ($pur_items as $p) {
                    # code...
                    $pr[$p->id]=array('id'=>$p->id,'name'=>$p->name,'qty'=>1,'selected_variants'=>array(),'variants'=>false,'label'=>$p->name." (".$p->code.")",
                        'value'=>$p->name." (".$p->code.")");
                }
                 $this->data['items'] = isset($pr) ? json_encode($pr) : false;

                // echo "".json_encode($pr);die();

            }
            $this->data['pitems'] = json_encode($pur_items) ;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('print_signages')));
            $meta = array('page_title' => lang('print_signages'), 'bc' => $bc);
            $this->page_construct('products/print_signages', $meta, $this->data);

        }
    }
 function print_signages1($product_id = NULL)
    {
        //$this->sma->print_arrays($_POST);
        $this->sma->checkPermissions('barcode', true);

        $this->form_validation->set_rules('style', lang("style"), 'required');

        if ($this->form_validation->run() == true) {

            $style = $this->input->post('style');
            $bci_size = ($style == 10 || $style == 12 ? 50 : ($style == 14 || $style == 18 ? 30 : 20));
            $currencies = $this->site->getAllCurrencies();
            $s = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            if ($s < 1) {
                $this->session->set_flashdata('error', lang('no_product_selected'));
                admin_redirect("products/print_signages");
            }
            for ($m = 0; $m < $s; $m++) {
                $pid = $_POST['product'][$m];
                $quantity = $_POST['quantity'][$m];
                $product = $this->products_model->getProductWithCategory($pid);
                $product->price = $this->input->post('check_promo') ? ($product->promotion ? $product->promo_price : $product->price) : $product->price;
                if ($variants = $this->products_model->getProductOptions($pid)) {
                    foreach ($variants as $option) {
                        if ($this->input->post('vt_' . $product->id . '_' . $option->id)) {
                            $barcodes[] = array(
                                'site' => $this->input->post('site_name') ? $this->Settings->site_name : FALSE,
                                'name' => $this->input->post('product_name') ? $product->name . ' - ' . $option->name : FALSE,
                                'image' => $this->input->post('product_image') ? $product->image : FALSE,
                                'barcode' => $this->product_barcode($product->code . $this->Settings->barcode_separator . $option->id, 'code128', $bci_size),
                                'price' => $this->input->post('price') ? $this->sma->formatMoney($option->price != 0 ? ($product->price + $option->price) : $product->price) : FALSE,
                                'unit' => $this->input->post('unit') ? $product->unit : FALSE,
                                'category' => $this->input->post('category') ? $product->category : FALSE,
                                'currencies' => $this->input->post('currencies'),
                                'variants' => $this->input->post('variants') ? $variants : FALSE,
                                'quantity' => $quantity
                            );
                        }
                    }
                } else {
                    $barcodes[] = array(
                        'site' => $this->input->post('site_name') ? $this->Settings->site_name : FALSE,
                        'name' => $product->name ,
                        'marathi_name' => $product->marathi_name ? $product->marathi_name : FALSE,
                        'mrp' => $product->mrp,
                        'barcode' => $this->product_barcode($product->code, $product->barcode_symbology, $bci_size),
                        'price' => $product->price ,
                        'unit' => $product->unit ,
                        // 'category' => $this->input->post('category') ? $product->category : FALSE,
                        'langauge' => $this->input->post('langauge'),
                        // 'variants' => FALSE,
                        'quantity' => $quantity
                    );
                }
                $values [] =array('name' => $this->input->post('product_name') ? $product->name : FALSE,
                    'barcode' => "'".$product->code,
                    'price' => $this->input->post('price') ? $product->price : FALSE,
                    'quantity' => $quantity,
                    'mrp' => $product->mrp,
                );
            }



            // create_csv($columns,$values, $filename);
            $this->data['barcodes'] = $barcodes;
            // $this->data['currencies'] = $currencies;
            $this->data['style'] = $style;
            $this->data['items'] = false;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('print_signages')));
            $meta = array('page_title' => lang('print_signages'), 'bc' => $bc);
            $this->page_construct('products/print_signages', $meta, $this->data);

        } else {



            $this->data['items'] = isset($pr) ? json_encode($pr) : false;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('print_signages')));
            $meta = array('page_title' => lang('print_signages'), 'bc' => $bc);
            $this->page_construct('products/print_signages', $meta, $this->data);

        }
    }

      function import_points_excel()
    {
        //$this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (isset($_FILES["userfile"])) {
                 $this->load->library('excel');
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'xlsx';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = TRUE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                   // echo " ".$this->digital_upload_path;die();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("products/import_points_excel");
                }

                $csv = $this->digital_upload_path.$this->upload->file_name;
               
                $objReader = PHPExcel_IOFactory::createReader('Excel2007');
                $objReader->setReadDataOnly(true);
                $objPHPExcel = $objReader->load($csv);
                $objWorksheet = $objPHPExcel->getActiveSheet();

                $highestRow = $objWorksheet->getHighestRow();
                $highestColumn = $objWorksheet->getHighestColumn();
                $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
                $rows = array();
                $errors = "";
                $prodData = array();
                
                for ($row = 1; $row <= $highestRow; ++$row) {
                  for ($col = 0; $col <= $highestColumnIndex; ++$col) {
                    $rows[$col] = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();

                      if($row==1)
                      {
                        $name[]=$rows[$col];
                      }
                      else
                      {
                        break;
                      }
                  }

                 
                }


              //  echo " ".json_encode($name);
               

                $keys = array("ID","Name","Category","points","Code","desc");
                //["ID","Name","Category","Points","Code","Desc",null]
                if( $name[0]!='ID'  || $name[1]!='Name' || $name[2]!='Category' || $name[3]!='points'
                    || $name[4]!='Code' || $name[5]!='desc'

                )
                {
                   // echo "string ".json_encode($name);die();
                    $this->session->set_flashdata('error', "Invalid File");
                    admin_redirect("products/import_points_excel");
                }
                else
                {
                    for ($row = 2; $row <= $highestRow; ++$row) {

                       $id= trim($objWorksheet->getCellByColumnAndRow(0, $row)->getValue()); 
                       $name=trim($objWorksheet->getCellByColumnAndRow(1, $row)->getValue()); 
                       $points = trim($objWorksheet->getCellByColumnAndRow(3, $row)->getValue()); 
                       $code=trim($objWorksheet->getCellByColumnAndRow(4, $row)->getValue()); 
                       $desc= trim($objWorksheet->getCellByColumnAndRow(5, $row)->getValue()); 
                     
                      $prod=$this->products_model->getProductByID(trim($id));   
                      if(!$prod)
                      {
                        $errors.="<p>Product ".$name."(".$code.") not found please check ID ".$id."</p>";
                       
                      }
                      elseif($points!='0' && $points!='1' )
                      {

                        $errors.="<p>For Product ".$name."(".$code.") ,please enter 0 or 1 to enable/disable points </p>";
                      }
                      else
                      {
                          
                            $prodData[]= array(
                            'id' =>$id,
                           'enable_sale_point' =>$points
                           );
                          
                          
                      }
                     
                    }

                }



        

                
               if(!empty($errors))
                {
                    // $this->data['error']=$errors;
                     $this->session->set_flashdata('error', $errors);
                    admin_redirect("products/import_points_excel");
                }
                
                
              
              //  $this->sma->print_arrays($final);
                
               
            }

            // $this->sma->print_arrays($items);die;
        }

        if ($this->form_validation->run() == true) {
            
            $this->db->update_batch('products', $prodData, 'id');
            $this->session->set_flashdata('message', 'Updated Successfuly');
            admin_redirect('products/import_points_excel');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('import_products_points_by_excel')));
            $meta = array('page_title' => lang('import_products_points_by_excel'), 'bc' => $bc);
            $this->page_construct('products/import_points_excel', $meta, $this->data);

        }
    }

     public function checkCSV()
   
    {
        //from report all 3430
        $a="D:\Karad.csv";
        //from report only gc 4  3423
        //$a="D:\Rahul\aplabazar\olddbs\CSV\DataFromQueryOnlyGC4.csv";

        //$a="D:\Rahul\aplabazar\olddbs\CSV\DataFromQueryOnlyGC4.csv";
        
        $file = fopen($a, 'r');
        $cnt=0;
             $columns = [
        
        'code',
        'name'
    ];
    $dupli = array();
     $data = array();
while (($line = fgetcsv($file)) !== FALSE) {
   //$line[0] = '1004000018' in first iteratio


    if(1==1)
    {
        //echo json_encode($cnt);
    $name=(empty(trim($line[0])) || trim($line[0])=='NULL' ) ? '':trim($line[0]);
    $code=(empty(trim($line[1])) || trim($line[1])=='NULL' ) ? '':trim($line[1]);
    $qty=(empty(trim($line[2])) || trim($line[2])=='NULL' ) ? '':trim($line[2]);

    
    

    //echo "code ".$code."<br/>";
    //echo "name ".$name."<br/>";

//  $this->db->where('id',$id);
//  $this->db->update('products', array('code' =>$code));

   //echo(json_encode($data));

   
       $this->db->select('name,code')
            ->where('code', trim($code));
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            $res = $q->row();

          //  $data[]=array('code' =>$res->code ,'name'=>trim($res->name) ,'found'=>1);
            echo ("name ".$name."<br/>");
            echo (" code ".$code."<br/>");
           

            
        }
        else
        {
           // $data[]=array('code' =>$code ,'name'=>trim($name));
            //echo json_encode($data);NF"
            echo "#@#";
             echo ("name ".$name."<br/>");
            echo (" code ".$code."<br/>");
        }

         $this->db->select('name,code')
            ->where('code', trim($code));
        $q = $this->db->get('testcode');
        if ($q->num_rows() > 0) {

            echo "Duplicate ".$name." ".$code."<br/>";

            $dupli[] = array('name' =>$name ,'code'=>trim($code) );
        }
        else
        {


        $this->db->insert('testcode', array('name' =>$name ,'code' =>trim($code) ));
        }

       // return FALSE;


  }
  $cnt++;
 
}
echo "##########".json_encode($dupli);
}



 public function taaaaa()
   
    {
         $this->load->admin_model('settings_model');
        //from report all 3430
        $a="D:\Karad1.csv";
        //from report only gc 4  3423
        //$a="D:\Rahul\aplabazar\olddbs\CSV\DataFromQueryOnlyGC4.csv";

        //$a="D:\Rahul\aplabazar\olddbs\CSV\DataFromQueryOnlyGC4.csv";
        
        $file = fopen($a, 'r');
        $cnt=0;
           $cnt1=0; 
  
     $data = array();
while (($line = fgetcsv($file)) !== FALSE) {
  

    if(1==1)
    {
        //echo json_encode($cnt);
    $name=(empty(trim($line[0])) || trim($line[0])=='NULL' ) ? '':trim($line[0]);
    $code=(empty(trim($line[1])) || trim($line[1])=='NULL' ) ? '':trim($line[1]);
    $qty=(empty(trim($line[2])) || trim($line[2])=='NULL' ) ? '':trim($line[2]);

    
    


      // echo ltrim($code, "'")."<br/>";
       $this->db->select('id,name,code')
            ->where('code', ltrim($code, "'"));
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            $res = $q->row();
          //  echo json_encode($res);
               //set qty
                    $qtydata = array(
                        'product_id' => $res->id,
                        'option_id' => NULL,
                        'warehouse_id' =>$this->settings_model->getSettings()->default_warehouse,
                        'quantity' => $qty,
                        'type' => 'addition'
                     );

         // echo json_encode($qtydata);
       /* $this->db->where('id',$res->id);            
        $this->db->update('products', array('quantity' => $qty));            
        $this->products_model->syncAdjustmentService($qtydata);*/
           //$cnt1++;

            
        }
       

       

       

  }
  $cnt++;
 
}
//echo "##########".json_encode($dupli);
//echo $cnt1;
}


 public function check()
   
    {
         $this->load->admin_model('settings_model');
        //from report all 3430
        $a="D:\HERLE.csv";
        //from report only gc 4  3423
        //$a="D:\Rahul\aplabazar\olddbs\CSV\DataFromQueryOnlyGC4.csv";

        //$a="D:\Rahul\aplabazar\olddbs\CSV\DataFromQueryOnlyGC4.csv";
        
        $file = fopen($a, 'r');
        $cnt=0;
            
  
     $data = array();
while (($line = fgetcsv($file)) !== FALSE) {
  

    if(1==1)
    {
        //echo json_encode($cnt);
    $name=(empty(trim($line[0])) || trim($line[0])=='NULL' ) ? '':trim($line[0]);
    $code=(empty(trim($line[1])) || trim($line[1])=='NULL' ) ? '':trim($line[1]);
   // $qty=(empty(trim($line[2])) || trim($line[2])=='NULL' ) ? '':trim($line[2]);

    
    


   
       $this->db->select('id,name,code')
            ->where('code', trim($code));
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            
            
        }
        else
        {
            $cnt++;
            echo trim($code)."<br/>";
        }
       

       

       

  }
  
 
}
echo " count ".$cnt;
}


   /*new*/
     function import_csv()
    {
        $this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (isset($_FILES["userfile"])) {


                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = TRUE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("products/import_csv");
                }
                ini_set('memory_limit', '2048M');
                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('name', 'code','brand', 'category_code', 'unit', 'sale_unit', 'purchase_unit', 'cost', 'price', 'alert_quantity','variants','subcategory_code', 'weight','mrp','gst_rate','gst_code','cess');

                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }

              //  $this->sma->print_arrays($final);
                $rw = 2;
                $items = array();
                foreach ($final as $csv_pr) {
                    $pcode=trim($csv_pr['code']);
                    $pcode=ltrim($csv_pr['code'], "'");
                    if (!$this->products_model->getProductByCode( $pcode )) {
                          $catd = $this->products_model->getCategoryByCode(trim($csv_pr['category_code']));
                        if(empty($catd))
                        {
                            $catData = array('code' =>trim($csv_pr['category_code']) ,'name' =>trim($csv_pr['category_code']) );
                             $this->site->addCategory($catData);
                             $catd = $this->products_model->getCategoryByCode(trim($csv_pr['category_code']));
                        }
                         $brand = $this->products_model->getBrandByName(trim($csv_pr['brand']));
                         if(empty($brand))
                        {
                            $brandData = array('code' =>trim($csv_pr['brand']) ,'name' =>trim($csv_pr['brand']) );
                            $this->site->addBrand($brandData);
                            $brand = $this->products_model->getBrandByName(trim($csv_pr['brand']));
                        }
                         

                         $unit = $this->products_model->getUnitByCode(trim($csv_pr['unit']));


                          if(empty($unit))
                        {
                            $unitData = array('code' =>trim($csv_pr['unit']) ,'name' =>trim($csv_pr['unit']) );
                            $this->site->addUnit($unitData);
                            $unit = $this->products_model->getUnitByCode(trim($csv_pr['unit']));
                        }
                        if (1==1) {
                            $brand = $this->products_model->getBrandByName(trim($csv_pr['brand']));
                            $unit = $this->products_model->getUnitByCode(trim($csv_pr['unit']));
                            $base_unit = $unit ? $unit->id : NULL;
                            $sale_unit = $base_unit;
                            $purcahse_unit = $base_unit;
                            if ($base_unit) {
                                $units = $this->site->getUnitsByBUID($base_unit);
                                foreach ($units as $u) {
                                    if ($u->code == trim($csv_pr['sale_unit'])) {
                                        $sale_unit = $u->id;
                                    }
                                    if ($u->code == trim($csv_pr['purchase_unit'])) {
                                        $purcahse_unit = $u->id;
                                    }
                                }
                            } else {
                                $this->session->set_flashdata('error', lang("check_unit") . " (" . $csv_pr['unit'] . "). " . lang("unit_code_x_exist") . " " . lang("line_no") . " " . $rw);
                                admin_redirect("products/import_csv");
                            }

                            //$tax_details = $this->products_model->getTaxRateByName(trim($csv_pr['tax_rate']));
                            $prsubcat = $this->products_model->getCategoryByCode(trim($csv_pr['subcategory_code']));
                            $slug = $this->sma->slug(trim($csv_pr['name']), 'products');
                            $items[] = array(
                                'code' => trim($csv_pr['code']),
                                'name' => trim($csv_pr['name']),
                                'slug' => $slug,
                                'category_id' => $catd->id,
                              //  'barcode_symbology' => mb_strtolower(trim($csv_pr['barcode_symbology']), 'UTF-8'),
                                'barcode_symbology' => mb_strtolower('code128', 'UTF-8'),
                                'brand' => ($brand ? $brand->id : NULL),
                                'unit' => $base_unit,
                                'sale_unit' => $sale_unit,
                                'purchase_unit' => $purcahse_unit,
                                'cost' => trim($csv_pr['cost']),
                                'price' => trim($csv_pr['price']),
                                'alert_quantity' => trim($csv_pr['alert_quantity']),
                               // 'tax_rate' => ($tax_details ? $tax_details->id : NULL),
                                'tax_method' => 'inclusive',
                                'subcategory_id' => ($prsubcat ? $prsubcat->id : NULL),
                                'variants' => trim($csv_pr['variants']),
                              //  'image' => trim($csv_pr['image']),
                                'weight' => trim($csv_pr['weight']),
                                'mrp' => trim($csv_pr['mrp']),
                                'gst_rate' => trim($csv_pr['gst_rate']),
                                'gst_code' => trim($csv_pr['gst_code']),
                                'cess' => trim($csv_pr['cess']),
                            );
                        } else {
                            $this->session->set_flashdata('error', lang("check_category_code") . " (" . $csv_pr['category_code'] . "). " . lang("category_code_x_exist") . " " . lang("line_no") . " " . $rw);
                            admin_redirect("products/import_csv");
                        }
                    }
                    else {
                        $this->session->set_flashdata('error', lang("check_product_code") . " (" . $csv_pr['name'] . "). " . lang("code_already_exist") . " " . lang("line_no") . " " . $rw);
                        admin_redirect("products/import_csv");
                    }
                    $rw++;
                }
            }

            // $this->sma->print_arrays($items);die;
        }

        if ($this->form_validation->run() == true && $prs = $this->products_model->add_products($items)) {
            $this->session->set_flashdata('message', sprintf(lang("products_added"), $prs));
            admin_redirect('products');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('import_products_by_csv')));
            $meta = array('page_title' => lang('import_products_by_csv'), 'bc' => $bc);
            $this->page_construct('products/import_csv', $meta, $this->data);

        }
    }
	
	//gst rate by category
    function set_gst_by_cat()
    {


       

        if($this->input->post("submit"))
        {
            if (!empty($_POST['val'])) {
//                  echo "".json_encode($this->input->post("val"));
// echo " len ".sizeof($this->input->post("val"));

// echo "gstrate ".$this->input->post("gstrate");
// echo " gst_code ".$this->input->post("gst_code");
                 
               $gc=$this->input->post("gst_code");
			   
			   echo "GC".$gc;
			 
			 
                $products = array();
                $arr=$this->input->post("val");
                foreach ($arr as $pid) {
                    # code...
					
					$ap=array(
                                'id' => $pid,
                                'gst_rate' => $this->input->post("gstrate")
                                
                    );
					 if(!empty(trim($gc)))
                    {
                        $ap['gst_code']=$gc;
                    }
					
                    $products[]=$ap;
                   

                }
				
				//  echo "id".json_encode($products);die;

             if($this->products_model->setgst($products))
             {

                   $this->session->set_userdata('remove_qals', 1);
                   $this->session->set_flashdata('message', lang("gst_rates_updated"));
                   admin_redirect('products/set_gst_by_cat');
             }
             else
             {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                admin_redirect('products/set_gst_by_cat');
             }

              
            }
            else
            {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);

            }
             
         

       }
       else
       {

         $this->data['categories'] = $this->site->getAllCategories();
        $this->data['brands'] = $this->site->getAllBrands();
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('system_settings')),  array('link' => admin_url('products/gstcat'), 'page' => lang('price_groups')), array('link' => '#', 'page' => lang('gstcat')));
        $meta = array('page_title' => lang('gstcat'), 'bc' => $bc);
        $this->page_construct('products/gstcat', $meta, $this->data);
       }
       
            }
    

    function getProductsForGST($group_id = NULL)
    {
        

        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $subcategory = $this->input->get('subcategory') ? $this->input->get('subcategory') : NULL;
        $percentage = $this->input->get('percentage') ? $this->input->get('percentage') : NULL;
        $brand = $this->input->get('brand') ? $this->input->get('brand') : NULL;
       

        $this->load->library('datatables');
        
       
            $this->datatables
            ->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as product_code, {$this->db->dbprefix('products')}.name as product_name,products.mrp as prodmrp, products.price as prodprice, products.cost as prodcost, products.gst_code,products.gst_rate");
     
            
            $this->datatables->from("products");
          
            if($category)
            {
                $this->datatables->where("{$this->db->dbprefix('products')}.category_id",$category);
            }
            if($subcategory)
            {
                $this->datatables->where("{$this->db->dbprefix('products')}.subcategory_id",$subcategory);
            }
            if($brand)
            {
                $this->datatables->where("{$this->db->dbprefix('products')}.brand",$brand);
            }

            //->add_column('Product Price',"$1",'prodprice')
            //->add_column('Product MRP',"$1",'prodmrp')


        echo $this->datatables->generate();
    }
	
	//set GSt
function set_gst($count_id = NULL)
    {
        $this->sma->checkPermissions('adjustments', true);
        $this->form_validation->set_rules('gstrate', lang("gstrate"), 'required');
        
        if ($this->input->post('add_adjustment') && $this->form_validation->run() == true) {

            $gstrate = $this->input->post('gstrate');
            $products = array( );
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {

                $product_id = $_POST['product_id'][$r];
              
                $gstcode = $_POST['gstcode'][$r];
               

              $products[] = array(
                    'id' => $product_id,
                    'gst_code' => $gstcode,
                    'gst_rate' => $gstrate
                );      
           }        
       if ($this->products_model->setgst($products)) {
            $this->session->set_userdata('remove_qals', 1);
            $this->session->set_flashdata('message', lang("gst_rates_updated"));
            admin_redirect('products/set_gst');
        } else {

           
            $this->data['adjustment_items'] = FALSE;
            $this->data['warehouse_id'] = FALSE;
            $this->data['count_id'] = 0;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
           
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('set_gst')));
            $meta = array('page_title' => lang('set_gst'), 'bc' => $bc);
            $this->page_construct('products/setgst', $meta, $this->data);

        }
    }
    else
    {
            $this->data['adjustment_items'] = FALSE;
            $this->data['warehouse_id'] = FALSE;
            $this->data['count_id'] = 0;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
           
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('set_gst')));
            $meta = array('page_title' => lang('set_gst'), 'bc' => $bc);
            $this->page_construct('products/setgst', $meta, $this->data);

    }



}
function setmrp($count_id = NULL)
    {
        $this->sma->checkPermissions('adjustments', true);
        $this->form_validation->set_rules('per', lang("percentage"), 'required');
        
        if ($this->input->post('add_adjustment') && $this->form_validation->run() == true) {

            $per = $this->input->post('per');
            //echo "PER ".$per;
            $products = array( );
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            //echo "string".$i;die();
            for ($r = 0; $r < $i; $r++) {

                $product_id = $_POST['product_id'][$r];
                $pr=$this->products_model->getProductByID($product_id);
                $price=round( $pr->mrp - ( $pr->mrp*($per/100)  )  )   ;  
             
               

              $products[] = array(
                    'id' => $product_id,
                    'price' => $price,
                  );
           }

           //echo "setmrp ".json_encode($products);die;        
       if ($this->products_model->setgst($products)) {
            $this->session->set_userdata('remove_qals', 1);
            $this->session->set_flashdata('message', lang("prices_updated"));
            admin_redirect('products/setmrp');
        } else {

           
            $this->data['adjustment_items'] = FALSE;
            $this->data['warehouse_id'] = FALSE;
            $this->data['count_id'] = 0;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
           
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('setprice')));
            $meta = array('page_title' => lang('setmrp'), 'bc' => $bc);
            $this->page_construct('products/setmrp', $meta, $this->data);

        }
    }
    else
    {
            $this->data['adjustment_items'] = FALSE;
            $this->data['warehouse_id'] = FALSE;
            $this->data['count_id'] = 0;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
           
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('setprice')));
            $meta = array('page_title' => lang('setmrp'), 'bc' => $bc);
            $this->page_construct('products/setmrp', $meta, $this->data);

    }



}
 function details_view($id = NULL)
    {
        $this->sma->checkPermissions('index');

        $pr_details = $this->products_model->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product'] = $pr_details;
        $this->data['unit'] = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand'] = $this->site->getBrandByID($pr_details->brand);
        $this->data['images'] = $this->products_model->getProductPhotos($id);
        $this->data['category'] = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : NULL;
        $this->data['tax_rate'] = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : NULL;
        $this->data['popup_attributes'] = $this->popup_attributes;
        $this->data['warehouses'] = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants'] = $this->products_model->getProductOptions($id);
        $this->data['sold'] = $this->products_model->getSoldQty($id);
        $this->data['purchased'] = $this->products_model->getPurchasedQty($id);

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => $pr_details->name));
        $meta = array('page_title' => $pr_details->name, 'bc' => $bc);
      //  $this->page_construct('products/viewl', $meta, $this->data);

        $this->load->view($this->theme . 'products/viewl', $this->data);
    }

	        //gst rate by category
    function setcategory()
    {

          $this->form_validation->set_rules('category', lang("category"), 'required|is_natural_no_zero');
       

        if($this->input->post("add_adjustment") && ($this->form_validation->run('products/setcategory') == true) )
        {
           
            if (!empty($_POST['product_id'])) {

             
             
                $products = array();
                $arr=$this->input->post("product_id");
                foreach ($arr as $pid) {
                                     
                    $ap=array(
                                'id' => $pid,
                                'category_id' => $this->input->post("category")
                                
                    );
                    if($this->input->post("subcategory"))
                    {
                        $ap['subcategory_id']=$this->input->post("subcategory");
                    }
                    else
                    {
                        $ap['subcategory_id']=NULL;
                    }
                    
                    $products[]=$ap;
                   

                }
                
                //  echo "id".json_encode($products);die;

             if($this->db->update_batch('products', $products, 'id')!=FALSE)
             {

                   $this->session->set_userdata('remove_qals', 1);
                   $this->session->set_flashdata('message', lang("category_updated"));
                   admin_redirect('products/setcategory');
             }
             else
             {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                admin_redirect('products/setcategory');
             }

              
            }
            else
            {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);

            }
             
         

       }
       else
       {
 $this->data['count_id'] = 0;
         $this->data['categories'] = $this->site->getAllCategories();
        $this->data['brands'] = $this->site->getAllBrands();
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('system_settings')),  array('link' => admin_url('products/gstcat'), 'page' => lang('price_groups')), array('link' => '#', 'page' => lang('set_category')));
        $meta = array('page_title' => lang('set_category'), 'bc' => $bc);
        $this->page_construct('products/setcat', $meta, $this->data);
       }
       
            }








            //set cat new
                //gst rate by category
    function set_cat_new()
    {


       

        if($this->input->post("submit"))
        {
            //echo "".json_encode($_POST['subcategory']);die();
            //echo "".json_encode($_POST['products']);die();
            if (!empty($_POST['subcategory'])) {

                $prods=$_POST['products'];
                $subcats=$_POST['subcategory'];
                $subdata=array();
                if(is_array($subcats) &&  (sizeof($subcats) >0 ) )
                {
                    for ($i=0; $i < sizeof($subcats) ; $i++) {

                        if(!empty($subcats[$i]))
                        {
                            $subdata[]=array(
                                'id' => $prods[$i],
                                'subcategory_id' => $subcats[$i]
                            );
                        } 
                        # code...
                    }


                }
    
                 if($this->products_model->setgst($subdata))
             {

                   $this->session->set_userdata('remove_qals', 1);
                   $this->session->set_flashdata('message', lang("sub_categories_updated"));
                   admin_redirect('products/set_cat_new');
             }
             else
             {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                admin_redirect('products/set_cat_new');
             }

              
            }
            else
            {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);

            }
             
         

       }
       else
       {

         $this->data['categories'] = $this->site->getAllCategories();
        $this->data['brands'] = $this->site->getAllBrands();
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products/set_cat_new'), 'page' => lang('Set Subcaegory')));
        $meta = array('page_title' => lang('Set Subcaegory'), 'bc' => $bc);
        $this->page_construct('products/setcatnew', $meta, $this->data);
       }
       
            }
    

      function getProductsForCAT($group_id = NULL)
    {
        

        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $subcategory = $this->input->get('subcategory') ? $this->input->get('subcategory') : NULL;
        $percentage = $this->input->get('percentage') ? $this->input->get('percentage') : NULL;
        $brand = $this->input->get('brand') ? $this->input->get('brand') : NULL;
       
        $st=NULL;
        if($category)
        {
            $st="<select  class='select form-control' name='subcategory[]' >";
          $rows = $this->products_model->getSubCategories($category);
                $st.="<option value=''>Select...</option>";
                foreach ($rows as $r) {
                    # code...
                    $st.="<option data-prodid='$1'  value='".$r->id."' >".$r->text."</option>";
                }

                $st.="</select>";

            }
          //  echo "".$st;die();

        $this->load->library('datatables');
        
       
            $this->datatables
            ->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as product_code, {$this->db->dbprefix('products')}.name as product_name");
           
     
            
            $this->datatables->from("products");
          
            if($category)
            {
                $this->datatables->where("{$this->db->dbprefix('products')}.category_id",$category);
                $this->datatables->where("{$this->db->dbprefix('products')}.subcategory_id",NULL);


            }
            if($subcategory)
            {
                $this->datatables->where("{$this->db->dbprefix('products')}.subcategory_id",$subcategory);
            }
            if($brand)
            {
                $this->datatables->where("{$this->db->dbprefix('products')}.brand",$brand);
            }

            //->add_column('Product Price',"$1",'prodprice')
            //->add_column('Product MRP',"$1",'prodmrp')

            if($st)
            {
                $this->datatables->add_column("Actions", $st, "id");
            }


        echo $this->datatables->generate();
    }



    //import subcategory excel
function subcategoryexcel()
    {
        //$this->sma->checkPermissions('csv');
              $this->load->helper('security');
              $this->load->library('excel');
               $csv = "D:/final.xls";
               
                //$objReader = PHPExcel_IOFactory::createReader('Excel2007');
                $objReader = PHPExcel_IOFactory::createReader('Excel5');
                $objReader->setReadDataOnly(true);
                $objPHPExcel = $objReader->load($csv);
                $objWorksheet = $objPHPExcel->getActiveSheet();

                $highestRow = $objWorksheet->getHighestRow();
                $highestColumn = $objWorksheet->getHighestColumn();
                $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
                $rows = array();
                $errors = "";
                $prodData = array();
                $prodDataUp = array();
                 $cnt=0;
                 $pcnt=0;
                 $rfcnt=0;
                 $ncnt=0;

                
                 //  echo " ".json_encode($name);
               
//              //productid  NAME    CODE    brand   category    subcategory cost    price   gst_rate    quantity    unit    rack    alert_quantity  mrp

           for ($row = 2; $row <= $highestRow; ++$row) {

                       $id= trim($objWorksheet->getCellByColumnAndRow(0, $row)->getValue()); 
                      

                       $s=trim($objWorksheet->getCellByColumnAndRow(4, $row)->getValue());
                       $sc=trim($objWorksheet->getCellByColumnAndRow(5, $row)->getValue());

                       $pcnt++;

                      // echo "ID ".$id;
                       //echo " s ".$s;
                       //echo " sc ".$sc;

                       if($sc=='NULL' || $sc=='null')
                       {
                        $ncnt++;
                            continue;
                       }

                       $cobj= $this->site->getCategoryByName(trim($s)); 
                       $scobj= $this->site->getCategoryByName(trim($sc)); 
                                        
                       $prod=$this->products_model->getProductByID(trim($id));  

                       if(empty($cobj))
                       {
                          echo "Category ".$s." NOtFound<br/>";
                       }


                       if(empty($scobj))
                       {
                          echo "SubCategory ".$sc." NOtFound<br/>";
                       }

                       if(!empty($cobj) && !empty($scobj))
                       {
                      
                       if($scobj->parent_id!=$cobj->id)
                       {
                           // echo "RNF c ".$s;
                           // echo "RNF sc ".$sc;
                           // echo "RNF scparent ".$scobj->parent_id;

                           $cnt++;


                       }
                       else
                       {
                        $prodData[]=array(
                                'product' => $prod->name,
                                'c' => $cobj->name,
                                's' => $scobj->name
                        );

                       $a=array(
                                'id'=>$prod->id,
                                'category_id'=>$cobj->id,
                                'subcategory_id'=>$scobj->id

                        );
                        $prodDataUp[]=$a;
                          $rfcnt++;
                       }
                    } 
                     
                      // if(!empty($prod) && !empty($scobj))
                      // {
                          
                      //       $prodData[]= array(
                      //       'id' =>$id,
                      //       'subcategory_id' =>$cobj->id,
                      //       'subcategory_id' =>$scobj->id
                      //      );
                          
                          
                      // }
                     
                    }

               
                    echo "Product count ".$pcnt;      
                    echo "<br/>";
                    echo "Proper Category Subcaegory ".$rfcnt;    
                    echo "<br/>";  
                    echo "Not Proper Category Subcaegory ".$cnt;   
                    echo "<br/>";   
                    echo "Null subcategory ".$ncnt;  
                    echo "<br/>";
                    echo "<br/>";
                    echo "<br/>";
                   $this->db->update_batch('products', $prodDataUp, 'id');
                     echo "After update<br/>";
                    echo "Product,Category,Subcaegory<br/>";
                    foreach ($prodData as $key) {

                        $key=(object)$key;

                        echo " ".$key->product.",".$key->c.",".$key->s."<br/>";

                        # code...
                    }
        }


//inact

public function inac21233e1()
   
    {
       
        $a="D:\Inactive.csv";
       
        
        $file = fopen($a, 'r');
         $prodDataUp = array();
            
while (($line = fgetcsv($file)) !== FALSE) {
  

    if(1==1)
    {
        
    $code=(empty(trim($line[0])) || trim($line[1])=='NULL' ) ? '':trim($line[0]);
     $code=ltrim($code,"'");
   

echo "CODE ".$code."<br/>";
   
       $this->db->select('id,name,code,gst_rate')
            ->where('code', trim($code));
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            $res = $q->row();



    $qs = $this->db->get_where('sale_items', array('product_id' => $res->id));
        if( $qs->num_rows() > 0 ) {

            //echo "#s#";

        }


         $qp = $this->db->get_where('purchase_items', array('product_id' => $res->id));
        if( $qp->num_rows() > 0 ) {

            //echo "#P#";

        }



            $a=array(
                'id' => $res->id , 
                'is_active' =>0

            );

           $prodDataUp[]=$a;
        
            
        }
        else
        {
           // $data[]=array('code' =>$code ,'name'=>trim($name));
            //echo json_encode($data);NF"
            echo "#NF#";
            
            echo (" code ".$code."<br/>");
        }

         
     
  }
 
 
}

//die();
$this->db->update_batch('products', $prodDataUp, 'id');
echo "SUCCESS";
die();

}


//inact

}
