<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard_api extends MY_Controller
{
	
    function __construct()
    {
        parent:: __construct();
        $this->load->model('rest_api_models/stocktake_api_model');
         $this->load->admin_model('products_model');
		define('AUTHKEY','cvsk2783lejr');
    }

    
	public function dashboardcount(){
	
		$user_id = $this->input->get('user_id');
		$auth_key = $this->input->get('auth_key');
		$for_month = $this->input->get('for_month');
		
		if ( $auth_key == '' || AUTHKEY != $auth_key) {
			$response = array('success' => 0, 'message' => ("Authentication key is not matching!"));
			echo json_encode($response);
			die;
		}	
		if ($user_id == '' ) {
			$response = array('success' => 0, 'message' => ("User id is required"));
			echo json_encode($response);
			die;
		}	
		if ($for_month == '' ) {
			$response = array('success' => 0, 'message' => ("month-year is required"));
			echo json_encode($response);
			die;
		}	

		$result = $this->stocktake_api_model->getStockTakeDashboardCount($user_id,$for_month);

		if($result){
			$response = array('success' => 1, 'counts' => $result, 'message' => ("Records found"));
			$response['assignedcnt_productcnt']=$this->stocktake_api_model->getProductsCountForDashboard($user_id,$for_month);


		}else{
			$response = array('success' => 0, 'message' => ("No records found"));
		}
		echo json_encode($response);
	}

	function getproduct()
    {

		$user_id = $this->input->get('user_id');
		$auth_key = $this->input->get('auth_key');
		$code = $this->input->get('code', TRUE);
		$month = $this->input->get('month');
		
		if ( $auth_key == '' || AUTHKEY != $auth_key) {
			$response = array('success' => 0, 'message' => ("Authentication key is not matching!"));
			echo json_encode($response);
			die;
		}	
		if ($user_id=='') {
			$response = array('success' => 0, 'message' => ("User Id is required"));
			echo json_encode($response);
			die;
		}	

       if ($code == '') {
			$response = array('success' => 0, 'message' => ("Product Code is required"));
			echo json_encode($response);
			die;
		}	
		$product = $this->products_model->getProductByCode($code);
		//print_r($product->id);die;
		$product_id = $product->id;
		$cond = '1';
       if($product_id!='')
			$cond .= ' and product_id ='.$product_id;
		if($month!='')
			$cond .= " and for_month ='".$month."'";
 		$row1 = $this->stocktake_api_model->getProductStockTakeBycond($cond);
 		//print_r($this->db->last_query());die;
 		//print_r($row1);die;
 		if($row1)
 		{
 			$this->sma->send_json(array('success' => 0, 'label' => lang('Stock is already taken for this product')));	
        		
        		die();
 		}
        $row = $this->stocktake_api_model->getProductByCode($code);
        if ($row) {
            
                $pr = array('success'=>1,'id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'quantity' =>$row->quantity,'unitname'=>$row->unitname);
            
               $stocktake =  $this->stocktake_api_model->checkStockTakeAssignment($user_id,$month,$row->category_id,$row->subcategory_id);
               if($stocktake){

           		 $this->sma->send_json($pr);
        		}
        		else{
        		$this->sma->send_json(array('success' => 0, 'label' => lang('This product is not assigned to you'), 'value' => $code));	
        		}
        } else {
            $this->sma->send_json(array('success' => 0, 'label' => lang('No Product Found'), 'value' => $code));
        }
    }

    function savestocktakes()
    {

		$user_id = $this->input->get('user_id');
		$auth_key = $this->input->get('auth_key');
		$product_id = $this->input->get('product_id');
		$month = date('m-Y');
		$store_qnty = $this->input->get('current_qty');
		$pos_qnty = $this->input->get('existing_qty');
		if ( $auth_key == '' || AUTHKEY != $auth_key) {
			$response = array('success' => 0, 'message' => ("Authentication key is not matching!"));
			echo json_encode($response);
			die;
		}	
		if ($user_id=='') {
			$response = array('success' => 0, 'message' => ("User Id is required"));
			echo json_encode($response);
			die;
		}	

       if ($product_id == '') {
			$response = array('success' => 0, 'message' => ("Product id is required"));
			echo json_encode($response);
			die;
		}	
        if ($store_qnty == '') {
			$response = array('success' => 0, 'message' => ("Store quantity is required"));
			echo json_encode($response);
			die;
		}	
		$data = array('product_id'=>$product_id,'pos_stock'=>$pos_qnty,'store_stock'=>$store_qnty,'for_month'=>$month);
		if($store_qnty == $pos_qnty){
			$data ['stock_status'] = 'Equal';
			$data ['stock_flag'] = 'Matched';
		}
		elseif($store_qnty < $pos_qnty){
			$data ['stock_status'] = 'Lesser';
			$data ['stock_flag'] = 'Mismatched';
		}
		elseif($store_qnty > $pos_qnty){
			$data ['stock_status'] = 'Greater';
			$data ['stock_flag'] = 'Mismatched';
		}
		$data ['created_by'] = $user_id;
        $row = $this->db->insert('stock_take_items', $data);
       // print_r($this->db->last_query());die;
        $this->sma->send_json(array('success' =>1, 'label' => lang('Stock Take Entry added successfully')));
        
    }

    function getproductsstocktakes()
    {

		$user_id = $this->input->get('user_id');
		$auth_key = $this->input->get('auth_key');
		$product_id = $this->input->get('product_id');
		$month = $this->input->get('month');
		
		if ( $auth_key == '' || AUTHKEY != $auth_key) {
			$response = array('success' => 0, 'message' => ("Authentication key is not matching!"));
			echo json_encode($response);
			die;
		}	
		if ($user_id=='') {
			$response = array('success' => 0, 'message' => ("User Id is required"));
			echo json_encode($response);
			die;
		}	
		$cond = '1';
		if($product_id!='')
			$cond .= ' and product_id ='.$product_id;
		if($month!='')
			$cond .= " and for_month ='".$month."'";
 		$row = $this->stocktake_api_model->getProductStockTakeBycond($cond);
        if ($row) {
        	$response = array('success' => 1, 'data' => $row, 'message' => (""));
		echo json_encode($response);
        }
        else
$this->sma->send_json(array('success' => 0, 'label' => lang('No any result found')));
	}
}
