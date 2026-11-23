<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Order_api extends MY_Controller
{
    function __construct()
    {
        parent:: __construct();
        $this->load->model('rest_api_models/tyre_booking_api_model');

    }

    //get all products


	public function getproductsbysearch($w,$p,$d){
		$post_params = array('type' =>'standard','width'=>$w,'profile'=>$p,'rim_diameter'=>$d);
		                    // 'quantity > '=>'0');


		$result = $this->product_api_model->getProducts($post_params);

		if($result){
			$response = array('success' => 1, 'data' => $result, 'message' => ("Products are available"));


		}else{
			$response = array('success' => 0, 'message' => ("Products Not found"));
		}

		echo json_encode($response);

	}
}
