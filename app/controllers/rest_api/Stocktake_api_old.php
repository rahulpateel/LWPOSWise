<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Stocktake_api extends MY_Controller
{
	
    function __construct()
    {
        parent:: __construct();
        $this->load->model('rest_api_models/stocktake_api_model');
         $this->load->admin_model('products_model');
		define('AUTHKEY','cvsk2783lejr');
    }

    
	public function assignedstocktakes(){
	
		$user_id = $this->input->get('user_id');
		$auth_key = $this->input->get('auth_key');
		
		if ( $auth_key == '' || AUTHKEY != $auth_key) {
			$response = array('success' => 0, 'message' => ("Authentication key is not matching!"));
			echo json_encode($response);
			die;
		}	
		if ($user_id == '' || $auth_key == '') {
			$response = array('success' => 0, 'message' => ("User id is required"));
			echo json_encode($response);
			die;
		}	
		$cond = 's.user_id='.$user_id;
		$cond .= " and s.for_month='".date('m-Y')."'";
		$result = $this->stocktake_api_model->getStockTakesByCond($cond);

		if($result){
			$response = array('success' => 1, 'data' => $result, 'message' => (""));


		}else{
			$response = array('success' => 0, 'message' => ("No any record found"));
		}

		echo json_encode($response);

	}

	function getproduct()
    {

		$user_id = $this->input->get('user_id');
		$auth_key = $this->input->get('auth_key');
		$code = $this->input->get('code', TRUE);
		$month = $this->input->get('month');
		$category_id = $this->input->get('category_id');
		$subcategory_id = $this->input->get('subcategory_id');

		/*echo "category_id ".$category_id;
		echo "subcategory_id ".$subcategory_id;
		die;*/
		
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
		$product_id=NULL;
		if($product) //check product exists
		{


			if( ($product->category_id==$category_id) && (

				(!empty($subcategory_id))?($product->subcategory_id==$subcategory_id)
				:TRUE
			) ) //check if for this stock take
			{
				
				$product_id = $product->id;
				$cond = '1';
		        if(!empty($product_id))
					$cond .= ' and product_id ='.$product_id;
				if($month!='')
					$cond .= " and for_month ='".$month."'";
		 		$row1 = $this->stocktake_api_model->getProductStockTakeBycond($cond);
		 		if($row1)
		 		{
		 			$this->sma->send_json(array('success' => 0, 'label' => lang('Stock is already taken for this product')));	
        		   die();

		 		}
		 		else
		 		{
		 			$product=$this->stocktake_api_model->getProductByCode($code);
		 			$pr = array('success'=>1,'id' => $product->id, 'label' => $product->name . " (" . $product->code . ")", 'code' => $product->code, 'name' => $product->name, 'price' => $product->price, 'quantity' =>$product->quantity,'unitname'=>$product->unitname);
		 			 $stocktake =  $this->stocktake_api_model->checkStockTakeAssignment($user_id,$month,$product->category_id,$subcategory_id);
		               if($stocktake){

		           		 $this->sma->send_json($pr);
		        		}
		        		else{
		        		$this->sma->send_json(array('success' => 0, 'label' => lang('This product is not assigned to you'), 'value' => $code));	
		        		}

		 		}

			}	
			else
			{
				$this->sma->send_json(array('success' => 0, 'label' => lang('Not in this stock take'), 'value' => $code));

			}
					
		}
		else
		 {
            $this->sma->send_json(array('success' => 0, 'label' => lang('No Product Found'), 'value' => $code));
        }	
		
	   
 		//print_r($this->db->last_query());die;
 		//print_r($row1);die;
 		/*if($row1)
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
        }*/
    }

    function savestocktakes()
    {

		$user_id = $this->input->get('user_id');
		$auth_key = $this->input->get('auth_key');
		$product_id = $this->input->get('product_id');
		$stock_check_id = $this->input->get('stock_check_id');
		$month = $this->input->get('month');//date('m-Y');
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
		
		$stockTakecond = '1';
		if(!empty($product_id))
		  $stockTakecond .= ' and product_id ='.$product_id;
		if($month!='')
		  $stockTakecond .= " and for_month ='".$month."'";
		$stocktakeTaken = $this->stocktake_api_model->getProductStockTakeBycond($stockTakecond);
		if($stocktakeTaken)
		{
			$this->sma->send_json(array('success' => 0, 'label' => lang('Stock is already taken for this product')));	
           die();

		}

		$data = array('product_id'=>$product_id,'pos_stock'=>$pos_qnty,'store_stock'=>$store_qnty,'for_month'=>$month,'stock_take_id'=>$stock_check_id);
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
        $status=$this->stocktake_api_model->getStatusByStockTakeID($stock_check_id);
        $this->db->set('status',$status);
        $this->db->where('id', $stock_check_id);
        $this->db->update('stock_takes');
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
		if($user_id!='')
			$cond .= ' and created_by ='.$user_id;
 		$row = $this->stocktake_api_model->getProductStockTakeBycond($cond);
        if ($row) {
        	$response = array('success' => 1, 'data' => $row, 'message' => (""));
		echo json_encode($response);
        }
        else
$this->sma->send_json(array('success' => 0, 'label' => lang('No any result found')));
	}
}
