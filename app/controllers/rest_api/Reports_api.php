<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Reports_api extends MY_Controller
{
	
	function __construct()
	{
		parent:: __construct();
		$this->load->model('rest_api_models/stocktake_api_model');
		$this->load->admin_model('products_model');
		define('AUTHKEY','cvsk2783lejr');
	}


	public function getStockTakesReport(){

		try
		 {
			$user_id = $this->input->get('user_id');
			$auth_key = $this->input->get('auth_key');
			$for_month = $this->input->get('for_month');
			$cat_id = $this->input->get('cat_id');
			$sub_cat_id = $this->input->get('sub_cat_id');
			$status = $this->input->get('status');

			
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

			$result = $this->stocktake_api_model->getReport($user_id,$for_month,$cat_id,$sub_cat_id,$status);

			if($result){
				$response = array('success' => 1, 'results' => $result, 'message' => ("Records found"));
			}else{
				$response = array('success' => 0, 'message' => ("No records found"));
			}
			echo json_encode($response);

		} catch (Exception $e) {
			$response = array('success' => 0, 'message' => ("Some error occurred"));
				echo json_encode($response);

		}
		
	}

	public function getCount()
	{
		$id = $this->input->get('sid');
		$count=$this->stocktake_api_model->getStatusByStockTakeID($id);
		echo "".$count;

	}

	
}
