<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Products_api extends MY_Controller
{
    function __construct()
    {
        parent:: __construct();
        $this->load->model('rest_api_models/product_api_model');
        
         define('AUTHKEY','cvsk2783lejr');
    }

    //get all products
    public function getproducts(){
          $post_params = array('type' =>'standard');
           // 'quantity > '=>'0');


        $result = $this->product_api_model->getProducts($post_params);

        if($result){
            $response = array('success' => 1, 'data' => $result, 'message' => ("Products are available"));


        }else{
            $response = array('success' => 0, 'message' => ("Products Not found"));
        }

        echo json_encode($response);

    }

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
    
    public function getSubCategories()
    {
       

        $auth_key = $this->input->get('auth_key');
        
        if ( $auth_key == '' || AUTHKEY != $auth_key) {
            $response = array('success' => 0, 'message' => ("Authentication key is not matching!"));
            echo json_encode($response);
            die;
        }   

         $id=$this->input->get('cat_id');
       
        $data=$this->product_api_model->getSubCategories($id);
        if($data)
        {
            $response = array('success' => true, 'data' => $data, 'message' => ("Data Found"));

        }
        else
        {
            $response = array('success' => false, 'data' => '', 'message' => ("No data Found"));
        }
      
         echo json_encode($response);
    }

      public function getCategories()
    {
       

        $auth_key = $this->input->get('auth_key');
        
        if ( $auth_key == '' || AUTHKEY != $auth_key) {
            $response = array('success' => 0, 'message' => ("Authentication key is not matching!"));
            echo json_encode($response);
            die;
        }   

       
       
        $data=$this->product_api_model->getAllCategories();
        if($data)
        {
            $response = array('success' => true, 'data' => $data, 'message' => ("Data Found"));

        }
        else
        {
            $response = array('success' => false, 'data' => '', 'message' => ("No data Found"));
        }
      
         echo json_encode($response);
    }

}
