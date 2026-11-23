<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Stocktake extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->load->admin_model('sync_model');
        $this->load->admin_model('stocktake_model');
        $this->load->admin_model('products_model');
          $this->load->library('form_validation');
    }

    public function index()
    {
		$this->sma->checkPermissions($warehouse_id = NULL);
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
        
        $bc = array(array('link' => '#', 'page' => 'Stock Takes List'));
		$meta = array('page_title' => 'Stock Takes List', 'bc' => $bc);
		$this->page_construct('stocktake/index', $meta, $this->data);

    }

public function getStockTakes($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('admin/quotes/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('quote_details'));
        
        $edit_link = anchor('admin/stocktake/edit_stocktake/$1', '<i class="fa fa-edit"></i> ' . lang('edit_stocktake'));
         $delete_link = anchor('admin/stocktake/delete/$1', '<i class="fa fa-edit"></i> ' . lang('delete_stocktake'));
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                      
                        <li>' . $edit_link . '</li>
                      
                    </ul>
                </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
                    $this->datatables
               ->select($this->db->dbprefix('stock_takes').'.id as stocktakeid,concat(u.first_name," ",u.last_name) as user,for_month,c.name as category_name,c1.name as subcategory_name,concat(u1.first_name," ",u1.last_name) as assignedby', FALSE)
		->from('stock_takes')
		->join('categories c','c.id = '.$this->db->dbprefix('stock_takes').'.category_id','left')
		->join('categories c1','c1.id = '.$this->db->dbprefix('stock_takes').'.subcategory_id','left')
		->join('users u','u.id ='.$this->db->dbprefix('stock_takes').'.user_id','left')
		->join('users u1','u1.id ='.$this->db->dbprefix('stock_takes').'.created_by','left');
//		$this->db->get();
//       print_r($this->db->last_query());
        
        $this->datatables->add_column("Actions", $action, "stocktakeid");
        echo $this->datatables->generate();
    }

    public function qrcode()
    {

		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$bc = array(array('link' => '#', 'page' => 'QR Code '));
		$meta = array('page_title' => 'QR Code', 'bc' => $bc);
		$this->page_construct('stocktake/qrcode', $meta, $this->data);
    }

public function stocktake_app()
    {

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $bc = array(array('link' => '#', 'page' => 'APK Download'));
        $meta = array('page_title' => 'APK Download', 'bc' => $bc);
        $this->page_construct('stocktake/app_download', $meta, $this->data);
    }
    function add_stocktake()
    {

       if($this->input->method()=="get")
       {
           //echo "GET";
           $this->data['categories'] = $this->site->getAllCategories();
            $this->data['users'] = $this->stocktake_model->getAllStockTakeUsers();
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('add_stocktake');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('stocktake'), 'page' => lang('stocktake')), array('link' => admin_url('stocktake/add_stocktake'), 'page' => lang('add_stocktake')));
            $meta = array('page_title' => lang('add_stocktake'), 'bc' => $bc);
            $this->page_construct('stocktake/add_stocktake', $meta, $this->data);

       }
       else
       {
         $this->form_validation->set_rules('category', lang("category"), 'required|is_natural_no_zero');
         $this->form_validation->set_rules('user', lang("user"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('for_month', $this->lang->line("for_month"), 'required');
       

                    $months = array(
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July ',
                'August',
                'September',
                'October',
                'November',
                'December',
            );

        $for_month=$this->input->post('for_month');
        $user=$this->input->post('user');
        $category=$this->input->post('category');
        $subcategory=$this->input->post('subcategory');
        $month_year_arr=explode("-", $for_month);
        if(sizeof($month_year_arr)>1)
        {
            $for_month=trim($month_year_arr[0]);
        }

        if($for_month=='January')
        {
            $for_month='01';   
        }
         if($for_month=='February')
        {
            $for_month='02';   
        }
         if($for_month=='March')
        {
            $for_month='03';   
        }
         if($for_month=='April')
        {
            $for_month='04';   
        }
         if($for_month=='May')
        {
            $for_month='05';   
        }
        if($for_month=='June')
        {
            $for_month='06';   
        }
         if($for_month=='July')
        {
            $for_month='07';   
        }
         if($for_month=='August')
        {
            $for_month='08';   
        }
         if($for_month=='September')
        {
            $for_month='09';   
        }
         if($for_month=='October')
        {
            $for_month='10';   
        }
         if($for_month=='November')
        {
            $for_month='11';   
        }
         if($for_month=='December')
        {
            $for_month='12';   
        }
        if(sizeof($month_year_arr)>1)
        {
            //$for_month=trim($month_year_arr[0]);
            $for_month.="-".trim($month_year_arr[1]);
        }
       // die;
        if ($this->form_validation->run() == true) {

            $queryString="select * from sma_stock_takes where for_month='".$for_month."' and category_id=".$category;
            if(!empty($subcategory))
            {
                $queryString="select * from sma_stock_takes where for_month='".$for_month."' and category_id=".$category." and subcategory_id=".$subcategory;
            }
           // echo " ".$queryString;die;
            $query=$this->db->query($queryString);

            if($query->num_rows()>0)
            {

                    $this->data['categories'] = $this->site->getAllCategories();
                   $this->data['users'] = $this->stocktake_model->getAllStockTakeUsers();
                   //$this->data['error'] = '<pre>Stock take already added </pre>';
                   //$this->data['error'] = '<pre>Stock take already added </pre>';
                   $this->session->set_flashdata('error',"<pre>Stock take already added </pre>");
                   $this->data['page_title'] = lang('add_stocktake');
                $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('stocktake'), 'page' => lang('add_stocktake')), array('link' => admin_url('stocktake/index'), 'page' => lang('printers')), array('link' => '#', 'page' => lang('add_stocktake')));
                $meta = array('page_title' => lang('add_stocktake'), 'bc' => $bc);
                $this->page_construct('stocktake/add_stocktake', $meta, $this->data);

            }
            else
            {

            $data = array('category_id' => $this->input->post('category'),
                'subcategory_id' => $this->input->post('subcategory'),
                'for_month' => $for_month,
                'user_id' => $user,
                'created_date' => date('Y-m-d H:s:i'),
                'created_by' =>  $this->session->userdata('user_id'),
                'status' =>  'Assigned',
                'note' => $this->input->post('note')
                    );
             $this->db->insert('stock_takes', $data);
             $this->session->set_flashdata('message', lang("stock_take_added"));
            admin_redirect('stocktake');

            }
        }
        else
        {
           $this->data['categories'] = $this->site->getAllCategories();
            $this->data['users'] = $this->stocktake_model->getAllStockTakeUsers();
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('add_stocktake');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('stocktake'), 'page' => lang('add_stocktake')), array('link' => admin_url('stocktake/printers'), 'page' => lang('printers')), array('link' => '#', 'page' => lang('add_stocktake')));
            $meta = array('page_title' => lang('add_stocktake'), 'bc' => $bc);
            $this->page_construct('stocktake/add_stocktake', $meta, $this->data);
        }

      

       }

     
    }

    public function getStockTakesTaken($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('admin/quotes/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('quote_details'));

        $approve_link = anchor('admin/stocktake/approve/$1', '<i class="fa fa-file-text-o"></i> ' . lang('approve'));
        
        $edit_link = anchor('admin/quotes/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_quote'));
         $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_quote") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('quotes/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_quote') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $approve_link . '</li>
                       
                    </ul>
                </div></div>';
        $action = '<div class="text-center">' . $approve_link . ' '. '</div>';
        $one='01';        
        $month=" concat(MONTHNAME(STR_TO_DATE(concat('".$one."','"."-',sma_stock_take_items.for_month),'"."%d-%m-%Y"."'))";
        $month.=",'-',cast(SUBSTRING_INDEX(sma_stock_take_items.for_month,'-',-1) AS CHAR CHARACTER SET utf8) ) as yearmonth";
      //echo "string ".$month;      
     //$month="sma_stock_take_items.for_month";  
        $this->load->library('datatables');
                  $q=  $this->datatables
               ->select($this->db->dbprefix('stock_take_items').'.id,'.$month.',p.name as product,p.code as code,c.name as category_name,c1.name as subcategory_name,sma_stock_take_items.pos_stock,sma_stock_take_items.store_stock,
                (sma_stock_take_items.pos_stock - sma_stock_take_items.store_stock),
                sma_stock_take_items.stock_flag,concat(u.first_name," ",u.last_name) as user,'.$this->db->dbprefix('stock_take_items').'.is_approved,'.$this->db->dbprefix('stock_take_items').'.stock_status,'.$this->db->dbprefix('stock_take_items').'.stock_status', FALSE)
        ->from('stock_take_items')
        ->join('stock_takes st','st.id = '.$this->db->dbprefix('stock_take_items').'.stock_take_id','left')
        ->join('categories c','c.id = st.category_id','left')
        ->join('categories c1','c1.id = st.subcategory_id','left')
         ->join('users u','u.id ='.$this->db->dbprefix('stock_take_items').'.created_by','left')
        
        ->join('products p','p.id ='.$this->db->dbprefix('stock_take_items').'.product_id','left');
      //echo json_encode($q);
      //print_r($this->db->last_query());
        
       $this->datatables->add_column("Actions", $action, "id");

        $where="";
        $and="";
        if($this->input->get('category_id') && $this->input->get('subcategory_id'))
        {
           
                $where.=" st.category_id=".$this->input->get('category_id');
                $where.=" and st.subcategory_id=".$this->input->get('subcategory_id'); 
              
            

        }
        else
        {
             if($this->input->get('category_id'))
             {
                 $where.=" st.category_id=".$this->input->get('category_id');
             }


        }

        if($this->input->get('user'))
        {

               
           

            if($where)
             {
                 $where.=" and u.id=".$this->input->get('user');
             }
             else
             {
                 $where.=" u.id=".$this->input->get('user');     
             }


            
        }

        if($this->input->get('status'))
        {
             if($where)
             {
                 $where.=" and sma_stock_take_items.stock_flag='".$this->input->get('status')."' "; 
             }
             else
             {
                $where.=" sma_stock_take_items.stock_flag='".$this->input->get('status')."' ";      
             }
             
           
        }

        if($this->input->get('subcategory_id'))
        {
              

            if($where)
             {
                 $where.=" and st.subcategory_id=".$this->input->get('subcategory_id'); 
             }
             else
             {
                $where.=" st.subcategory_id=".$this->input->get('subcategory_id');      
             }
              
        }

         if($this->input->get('for_month'))
        {
             $arr=explode("-", trim($this->input->get('for_month')));
             $str=$this->getMonthNumber($arr[0])."-".$arr[1];
             if($where)
             {
                $where.=" and sma_stock_take_items.for_month='".$str."' "; 
             }
             else
             {
                $where.=" sma_stock_take_items.for_month='".$str."' ";    
             }
               
            
        }
        if(!empty($where))
        {
            $q->where($where);
        }
      //  echo "where ".$where;

        echo $this->datatables->generate();
    }

    public function stocktakestaken()
    {
       // echo "dsdsds";die;
        $this->sma->checkPermissions($warehouse_id = NULL);
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
        
        $bc = array(array('link' => '#', 'page' => 'Stock Takes List'));
        $meta = array('page_title' => 'Stock Takes List', 'bc' => $bc);
         $this->data['categories'] = $this->site->getAllCategories();
            $this->data['users'] = $this->stocktake_model->getAllStockTakeUsers();
            $this->data['statuses'] =array('0'=>'Select status','Matched'=>'Matched' ,'Mismatched'=>'Mismatched');
        $this->page_construct('stocktake/stocktakestaken', $meta, $this->data);

    }


    function edit_stocktake($id=NULL)
    {

       if($this->input->method()=="get")
       {
          if(!empty($id))
          {
              //echo "GET";
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['users'] = $this->stocktake_model->getAllStockTakeUsers();
            $this->data['stocktake'] = $this->stocktake_model->getStockTakeByID($id);
            if($this->data['stocktake'] &&  $this->data['stocktake']->for_month)
            {
                $arr=explode("-", trim($this->data['stocktake']->for_month));
                $this->data['stocktake']->for_month=$this->getMonthName(trim($arr[0]))."-".(trim($arr[1]));
            }
            $this->data['id'] = $id;
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('edit_stocktake');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('stocktake'), 'page' => lang('stocktake')), array('link' => admin_url('stocktake/edit_stocktake'), 'page' => lang('edit_stocktake')));
            $meta = array('page_title' => lang('edit_stocktake'), 'bc' => $bc);
            $this->page_construct('stocktake/add_stocktake', $meta, $this->data);

          }
          else
          {
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['users'] = $this->stocktake_model->getAllStockTakeUsers();
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('add_stocktake');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('stocktake'), 'page' => lang('stocktake')), array('link' => admin_url('stocktake/add_stocktake'), 'page' => lang('add_stocktake')));
            $meta = array('page_title' => lang('add_stocktake'), 'bc' => $bc);
            $this->page_construct('stocktake/add_stocktake', $meta, $this->data);

          }
          

       }
       else
       {
         $id= $user=$this->input->post('id');
         $this->form_validation->set_rules('category', lang("category"), 'required|is_natural_no_zero');
         $this->form_validation->set_rules('user', lang("user"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('for_month', $this->lang->line("for_month"), 'required');
       

                    $months = array(
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July ',
                'August',
                'September',
                'October',
                'November',
                'December',
            );

        $for_month=$this->input->post('for_month');
        $user=$this->input->post('user');
        $category=$this->input->post('category');
        $subcategory=$this->input->post('subcategory');
        $month_year_arr=explode("-", $for_month);
        if(sizeof($month_year_arr)>1)
        {
            $for_month=trim($month_year_arr[0]);
           
        }
        if($for_month=='January')
        {
            $for_month='01';   
        }
         if($for_month=='February')
        {
            $for_month='02';   
        }
         if($for_month=='March')
        {
            $for_month='03';   
        }
         if($for_month=='April')
        {
            $for_month='04';   
        }
         if($for_month=='May')
        {
            $for_month='05';   
        }
        if($for_month=='June')
        {
            $for_month='06';   
        }
         if($for_month=='July')
        {
            $for_month='07';   
        }
         if($for_month=='August')
        {
            $for_month='08';   
        }
         if($for_month=='September')
        {
            $for_month='09';   
        }
         if($for_month=='October')
        {
            $for_month='10';   
        }
         if($for_month=='November')
        {
            $for_month='11';   
        }
         if($for_month=='December')
        {
            $for_month='12';   
        }
        if(sizeof($month_year_arr)>1)
        {
            //$for_month=trim($month_year_arr[0]);
            $for_month.="-".trim($month_year_arr[1]);
        }
        //$for_month.="-".date("Y");
       // die;
        if ($this->form_validation->run() == true) {

            $queryString="select * from sma_stock_takes where for_month='".$for_month."' and category_id=".$category." and id!=".$id;
            if(!empty($subcategory))
            {
                $queryString="select * from sma_stock_takes where for_month='".$for_month."' and category_id=".$category." and subcategory_id=".$subcategory." and id!=".$id;
            }
            //echo " ".$queryString;die;
            $query=$this->db->query($queryString);

            if($query->num_rows()>0)
            {
                    $this->data['stocktake'] = $this->stocktake_model->getStockTakeByID($id);
                    //echo "string ".json_encode($stocktake);
            if($this->data['stocktake'] &&  $this->data['stocktake']->for_month)
            {
                $arr=explode("-", trim($this->data['stocktake']->for_month));
                $this->data['stocktake']->for_month=$this->getMonthName(trim($arr[0]));
            }
            $this->data['id'] = $id;  
                    $this->data['categories'] = $this->site->getAllCategories();
                   $this->data['users'] = $this->stocktake_model->getAllStockTakeUsers();
                   $this->data['error'] = '<pre>Stock take already added </pre>';
                   $this->data['page_title'] = lang('add_stocktake');
                $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('stocktake'), 'page' => lang('stocktake')),  array('link' => '#', 'page' => lang('edit_stocktake')));
                $meta = array('page_title' => lang('add_stocktake'), 'bc' => $bc);
                $this->page_construct('stocktake/add_stocktake', $meta, $this->data);

            }
            else
            {

            $data = array('category_id' => $this->input->post('category'),
                'subcategory_id' => $this->input->post('subcategory'),
                'for_month' => $for_month,
                'user_id' => $user,
                'created_date' => date('Y-m-d H:s:i'),
                'created_by' =>  $this->session->userdata('user_id'),
                'status' =>  'Assigned',
                'note' => $this->input->post('note')
                    );
            
             $this->db->update('stock_takes', $data, array('id' => $id));
             $this->session->set_flashdata('message', lang("stock_take_updated"));
             admin_redirect('stocktake');

            }
        }
        else
        {
           $this->data['stocktake'] = $this->stocktake_model->getStockTakeByID($id);
            if($this->data['stocktake'] &&  $this->data['stocktake']->for_month)
            {
                $arr=explode("-", trim($this->data['stocktake']->for_month));
                $this->data['stocktake']->for_month=$this->getMonthName(trim($arr[0]));
            }
            $this->data['id'] = $id;  
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['users'] = $this->stocktake_model->getAllStockTakeUsers();
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('edit_stocktake');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('stocktake'), 'page' => lang('edit_stocktake')), array('link' => admin_url('stocktake/stocktake'), 'page' => lang('stocktake')), array('link' => '#', 'page' => lang('edit_stocktake')));
            $meta = array('page_title' => lang('edit_stocktake'), 'bc' => $bc);
            $this->page_construct('stocktake/add_stocktake', $meta, $this->data);
        }

      

       }

     
    }

    public function getMonthName($value)
    {
        $value=trim($value);  
         $months = array(
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July ',
                'August',
                'September',
                'October',
                'November',
                'December',
            );
         if($value=='01' || $value=='1' )
         {
            return $months[0];
         }
         else if($value=='02' || $value=='2' )
         {
            return $months[1];
         }
         else if($value=='03' || $value=='3' )
         {
            return $months[2];
         }
         else if($value=='04' || $value=='4' )
         {
            return $months[3];
         }
         else if($value=='05' || $value=='5' )
         {
            return $months[4];
         }
         else if($value=='06' || $value=='6' )
         {
            return $months[5];
         }
         else if($value=='07' || $value=='7' )
         {
            return $months[6];
         }
         else if($value=='08' || $value=='8' )
         {
            return $months[7];
         }
        else if($value=='09' || $value=='9' )
         {
            return $months[8];
         }
         else if($value=='10'  )
         {
            return $months[9];
         }
         else if($value=='11' )
         {
            return $months[10];
         }
         else if($value=='12'  )
         {
            return $months[11];
         }
         else
         {
            return "";   
         }
          
    }

     public function getMonthNumber($value)
    {
        $value=trim($value);  
        
         if($value=='January')
         {
            return '01';
         }
         else if($value=='February' )
         {
            return '02';
         }
         else if($value=='March' )
         {
            return '03';
         }
         else if($value=='April' )
         {
            return '04';
         }
         else if($value=='May' )
         {
            return '05';
         }
         else if($value=='June' )
         {
            return '06';
         }
         else if($value=='July' )
         {
            return '07';
         }
         else if($value=='August' )
         {
            return '08';
         }
        else if($value=='September' )
         {
            return '09';
         }
         else if($value=='October'  )
         {
            return '10';
         }
         else if($value=='November' )
         {
            return '11';
         }
         else if($value=='December'  )
         {
            return '12';
         }
         else
         {
            return "";   
         }
          
    }

public function getConsolidatedReport()
    {
        $category_id=NULL;
        $subcategory_id=NULL;
        $status=NULL;
        $user_id=NULL;
        $for_month=NULL;
        if ($this->input->post('category')) {
           
            $category_id=$this->input->post('category');
        }

        if ($this->input->post('subcategory')) {
            //echo "subcategory_id ".$this->input->post('subcategory');
            $subcategory_id=$this->input->post('subcategory');
        }

        if ($this->input->post('status')) {
            //echo "#status ".$this->input->post('status');
             $status=$this->input->post('status');
        }

        if ($this->input->post('user')) {
            //echo "user ".$this->input->post('user');
             $user_id=$this->input->post('user');
        }


        if ($this->input->post('for_month')) {
            //echo "for_month ".$this->input->post('for_month');
            $for_month=$this->input->post('for_month');
        }
       // echo "dsdsds";die;
        $this->sma->checkPermissions($warehouse_id = NULL);
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
        
        $bc = array(array('link' => '#', 'page' => 'Stock Takes List Consolidated'));
        $meta = array('page_title' => 'Stock Takes List Consolidated', 'bc' => $bc);
         $this->data['categories'] = $this->site->getAllCategories();
         $this->data['users'] = $this->stocktake_model->getAllStockTakeUsers();
         $this->data['report'] = $this->stocktake_model->getConsolidatedReport($user_id,$for_month,$category_id,$subcategory_id,$status,FALSE,NULL);
         $this->data['statuses'] =array('0'=>'Select status','InProgress'=>'InProgress' ,'Completed'=>'Completed' ,'Assigned'=>'Assigned');
         //echo json_encode($this->data['report']);die;
         $this->page_construct('stocktake/consolidatedreport', $meta, $this->data);

    }


    
   
    public function excel_export($V=NULL)
    {
        //echo "dsdsdsds";
        //echo $this->input->post('chkvals');die;
      //  die();
        $category_id=NULL;
        $subcategory_id=NULL;
        $status=NULL;
        $user_id=NULL;
        $for_month=NULL;
        


       
        $this->sma->checkPermissions($warehouse_id = NULL);
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
        
        $bc = array(array('link' => '#', 'page' => 'Stock Takes List Consolidated'));
        $meta = array('page_title' => 'Stock Takes List Consolidated', 'bc' => $bc);
         $this->data['categories'] = $this->site->getAllCategories();
         $this->data['users'] = $this->stocktake_model->getAllStockTakeUsers();
         $this->data['report'] = $this->stocktake_model->getConsolidatedReport($user_id,$for_month,$category_id,$subcategory_id,$status,TRUE,$this->input->post('chkvals'));
         $this->data['statuses'] =array('0'=>'Select status','Matched'=>'Matched' ,'Mismatched'=>'Mismatched');
         //echo json_encode($this->data['report']);die;
         //$this->page_construct('stocktake/consolidatedreport', $meta, $this->data);

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('Stock Takes Conolidated Report'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('Month-Year'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('User'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('category'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('subcategory'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('products'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('stock_takes'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('Matched'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('Mismatched'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('pending'));

                    $row = 2;
                    foreach ($this->data['report']['stock_takes'] as $stocktake) {

                                    $totalTaken=0;
                                    $mismatched=0;
                                    $matched=0;
                                    $pending=0;

                                    if($stocktake->cnts)
                                    {
                                        foreach ($stocktake->cnts as $cnt) {
                                            $totalTaken+=$cnt->count;
                                            if($cnt->stock_flag=='Matched')
                                            {
                                                $matched+=$cnt->count;
                                            }
                                            if($cnt->stock_flag=='Mismatched')
                                            {
                                                $mismatched+=$cnt->count;
                                            }

                                        }

                                    }
                                    $pending=$stocktake->prod_count-$totalTaken;
                                    $arrMonth=explode("-", trim( $stocktake->for_month));

                                    $month=$this->stocktake_model->getMonthName($arrMonth[0]);
                                    $year=($arrMonth[1]);
                       
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $month."-".$year);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $stocktake->user);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $stocktake->category_name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row,$stocktake->subcategory_name);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $stocktake->prod_count);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $totalTaken);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $matched);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $mismatched);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $pending);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'stock_takes_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    return create_excel($this->excel, $filename);

    }

    public function approve($value='')
    {
        // $this->data['modal_js'] = $this->site->modal_js();approve_stock_take

        
        if($this->input->post('approve_stock_take'))
        {
            //die;
            $id=$this->input->post('id');
            $stock_qty=$this->input->post('stock_qty');
            $operation=$this->input->post('operation');
            $result= $this->stocktake_model->getStockTakeItemByID($id);
            $product=$this->products_model->getProductByID($result->product_id);
            $current_product_quantity=$product->quantity;
            $updated_product_quantity=0;

            if(empty($result))
            {
                 $this->session->set_flashdata('error', $this->lang->line("stock_take_item_not_found"));
                 admin_redirect('stocktake/stocktakestaken');
            }
            if( ($current_product_quantity < $stock_qty) && $operation=='Minus' )
            {
                 $this->session->set_flashdata('error', $this->lang->line("difference_greater_than_stock_quantity"));
                 admin_redirect('stocktake/stocktakestaken');
            }
           
         
                
            
          
            if($operation=='Plus')
            {
                $isPlus=1;
                $qtyPlus=$stock_qty;
                $updated_product_quantity=$current_product_quantity+$qtyPlus;
            }
            else
            {
                $isPlus=0;
                $qtyMinus=$stock_qty;
                $updated_product_quantity=$current_product_quantity-$qtyMinus;
            }

     
          
            $product_id=$result->product_id;
            


            //update stock take item
            if($isPlus)
            {
                $value=$result->pos_stock+$qtyPlus;     
            }
            else
            {

                 $value=$result->pos_stock-$qtyMinus; 

                 if($value<0)
                 {
                     $this->session->set_flashdata('error', $this->lang->line("invalid_qunatity"));
                   admin_redirect('stocktake/stocktakestaken');

                 }
            }

           // echo "Value ".$value;
             //die;
            
            $data=array('is_approved' => '1','stock_flag'=>'Matched','pos_stock'=>$value,'store_stock'=>$value,'stock_status'=>'Equal');
            $this->db->update('stock_take_items', $data, array('id' => $id));

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
                    $this->db->set('quantity', 'quantity-'."'".$qtyMinus."'", FALSE);
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
                    $this->db->set('quantity', 'quantity-'."'".$qtyMinus."'", FALSE);
                    $this->db->update('warehouses_products');   

            }
            
            //add audit data
            $auditData=array('stock_qty' => $updated_product_quantity,'stock_take_item_id'=>$id,'admin_id'=>$this->session->userdata('user_id'), 'created_date' => date('Y-m-d H:s:i'),'old_stock_qty'=>$current_product_quantity);
            $this->db->insert('stock_take_audit', $auditData);

           // echo "string ".json_encode($auditData);die;
            
            //$this->db->update('products', $prodData, array('id' => $product_id));
           // $this->db->update('warehouses_products', $prodData, array('product_id' => $product_id));
            
            //update in purchase item
            if($isPlus==1 && $isEqual==0)
            {
                //addition in purchase item

                //get latest purchase item and add
                $query=$this->db->query('SELECT i.id,i.product_id,p.date,quantity_received FROM sma_purchase_items i INNER JOIN  sma_purchases p ON p.id=i.purchase_id 
                WHERE  i.product_id='.$product_id.'
                ORDER BY p.date DESC LIMIT 0,1');

                if($query->num_rows()>0)
                {
                    $this->db->where('id',$query->row()->id);
                    $this->db->set('quantity_balance', 'quantity_balance+'."'".$qtyPlus."'", FALSE);
                    $this->db->update('purchase_items');
                }



            }
            else
            { //minus part
                
                $count=0;
                do 
                {
                      //minus in purchase item
                $queryString='SELECT i.id,i.product_id,p.date,quantity_balance FROM sma_purchase_items i INNER JOIN  sma_purchases p ON p.id=i.purchase_id 
                WHERE  i.product_id='.$product_id.' AND quantity_balance > 1 
                ORDER BY p.date ASC ';
               // echo " qs ".$queryString." ";
                $query=$this->db->query($queryString);

                if($query->num_rows()>0) //if rows present
                {
                    //for loop start
                    foreach (($query->result()) as $row) {

                                if(!empty($row->quantity_balance))
                                {
                                                                           
                                        if($qtyMinus<1 && $qtyMinus > 0 ) //if qty is <1 && >0 like 0.66
                                        {
                                            $this->db->where('id',$row->id);
                                            $this->db->set('quantity_balance', 'quantity_balance-'."'".$qtyMinus."'",FALSE);
                                            $this->db->update('purchase_items');
                                            $qtyMinus=0;
                                            break;


                                        }
                                        else
                                        {
                                            if($count < 1) //for first iteration
                                            {
                                                $this->db->where('id',$row->id);
                                                $this->db->set('quantity_balance', 'quantity_balance-1',FALSE);
                                                $this->db->update('purchase_items');
                                               
                                                $qtyMinus=$qtyMinus-1;

                                                if($qtyMinus==0)
                                                {
                                                    break;
                                                }

                                            }
                                            else
                                            {   //from second iteration onwords
                                                // echo "in cn2 ".$query->row()->id;
                                                 
                                               

                                                if($row->quantity_balance > $qtyMinus)
                                                {
                                                    $this->db->where('id',$row->id);
                                                    $this->db->set('quantity_balance', 'quantity_balance-'."'".$qtyMinus."'",FALSE);
                                                    $this->db->update('purchase_items');
                                                    $qtyMinus=0;
                                                  
                                                    break;

                                                }
                                                else
                                                {

                                                   $this->db->where('id',$row->id);
                                                    $this->db->set('quantity_balance', 'quantity_balance-1',FALSE);
                                                    $this->db->update('purchase_items');
                                                    $qtyMinus=$qtyMinus-1;
                                                     if($qtyMinus==0)
                                                    {
                                                         break;
                                                    }
                                                    

                                                }


                                            }
                                            
                                            if($qtyMinus==0)
                                            {
                                                break;
                                            }

                                        }    
                                                
                               }
                       
                       }//for loop end

                       $count=$count+1;
                       if($qtyMinus<=0)
                        {
                           break;
                        }
                    
                }//if row not presnt
                else
                {
                    $qtyMinus=0;
                    break;

                }
                } 
                while ($qtyMinus!=0);
             
                //die;
            }

            $this->session->set_flashdata('message', lang("stock_take_approved"));
            admin_redirect('stocktake/stocktakestaken');

        }
        else
        {
             $this->data['item']= $this->stocktake_model->getStockTakeItemByID($value);
             $product=$this->products_model->getProductByID($this->data['item']->product_id);
             $this->data['product_quantity']=$product->quantity;
             //echo "string ".json_encode( $this->data['product_quantity']);
            $this->data['modal_js'] = $this->site->modal_js();
           $this->load->view($this->theme . 'stocktake/approve_stocktake', $this->data);

        }

       
    }

    function delete($id = NULL)
    {
        if ($this->db->delete('stock_take_items', array('id' => $id)) ) {
            $this->session->set_flashdata('message', lang("stock_take_deleted"));
            admin_redirect('stocktake/stocktakestaken');
        }
    }

    public function pending($stock_take_id=NULL)
    {
        # code...
        
        if( $this->input->post('excel'))
        {
            $stock_take_id=$this->input->post('stock_take_id');
            $result=$this->stocktake_model->getPendingProducts($stock_take_id);
            $row = 2;
              $this->load->library('excel');
              $this->excel->setActiveSheetIndex(0);
              $this->excel->getActiveSheet()->setTitle(lang('Pending Products Report'));
              $this->excel->getActiveSheet()->SetCellValue('A1', lang('Product'));
              $this->excel->getActiveSheet()->SetCellValue('B1', lang('category'));
              $this->excel->getActiveSheet()->SetCellValue('C1', lang('subcategory'));

                        foreach($result as $pendingProd)
                         {

                                $this->excel->getActiveSheet()->SetCellValue('A' . $row,$pendingProd->name);
                                $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pendingProd->category_name);
                                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pendingProd->subcategory_name);
                                   $row++;
                        }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(80);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'pending_products_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    return create_excel($this->excel, $filename);
             

        }
        else
        {
            $result=$this->stocktake_model->getPendingProducts($stock_take_id);
            $this->data['page_title'] = lang('pending_products_list');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('stocktake'), 'page' => lang('stocktake')), array('link' => admin_url('stocktake/pending'), 'page' => lang('pending_products_list')));
            $meta = array('page_title' => lang('pending_products_list'), 'bc' => $bc);
            $this->data['pending']=$result;
            $this->data['id']=$stock_take_id;
            $this->page_construct('stocktake/pendingproducts', $meta, $this->data);
        }
        
       
        

    }


public function audit_report()
    {
        $category_id=NULL;
        $subcategory_id=NULL;
        $user_id=NULL;
        $start_date=NULL;
        $end_date=date('Y-m-d');
        if ($this->input->post('category')) {
           
            $category_id=$this->input->post('category');
        }

        if ($this->input->post('subcategory')) {
            //echo "subcategory_id ".$this->input->post('subcategory');
            $subcategory_id=$this->input->post('subcategory');
        }


        if ($this->input->post('user')) {
            //echo "user ".$this->input->post('user');
             $user_id=$this->input->post('user');
        }


        if ($this->input->post('start_date')) {
            //echo "for_month ".$this->input->post('for_month');
            $start_date=$this->input->post('start_date');
            $start_date = $this->sma->fld($start_date);
        }
        if ($this->input->post('end_date')) {
            //echo "for_month ".$this->input->post('for_month');
            $end_date=$this->input->post('end_date');
            $end_date=$this->sma->fld($end_date);
        }
       // echo "dsdsds";die;
        $this->sma->checkPermissions($warehouse_id = NULL);
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
        
        $bc = array(array('link' => '#', 'page' => 'Stock Takes List Consolidated'));
        $meta = array('page_title' => 'Stock Takes List Consolidated', 'bc' => $bc);
         $this->data['categories'] = $this->site->getAllCategories();
         $this->data['users'] = $this->stocktake_model->getAllAdmins();
         $this->data['report'] = $this->stocktake_model->getAuditData($category_id,$subcategory_id,$user_id,$start_date,$end_date);

        // echo "string ".json_encode( $this->data['report']);die;
         $this->data['statuses'] =array('0'=>'Select status','InProgress'=>'InProgress' ,'Completed'=>'Completed' ,'Assigned'=>'Assigned');
         //echo json_encode($this->data['report']);die;
         $this->page_construct('stocktake/auditreport', $meta, $this->data);

    }

      /* -------------------------------------------------------------------------------- */

    public function stocktake_item_actions()
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
                    $ids=$_POST['val'];
                    $arrTID=explode(",", trim($ids,','));
                    foreach ( $arrTID as $id) {
                        //$this->purchases_model->deletePurchase($id);
                      $this->db->delete('stock_take_items', array('id' => $id));
                     
                    }
                    
                    $this->session->set_flashdata('message', $this->lang->line("stock_take_items_deleted"));
                    admin_redirect('stocktake/stocktakestaken');

                }  
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_stock_take_item_selected"));
                 admin_redirect('stocktake/stocktakestaken');
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
              admin_redirect('stocktake/stocktakestaken');
        }
    }

    /* -------------------------------------------------------------------------------- */

    
}
