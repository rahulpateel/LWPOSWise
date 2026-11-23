<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Login_api extends MY_Controller
{
    function __construct()
    {
        parent:: __construct();
        $this->load->model('rest_api_models/user_api_model');
        $this->load->model('rest_api_models/authenticateUser_model');
        $this->load->admin_model('auth_model');
        $this->load->library('ion_auth');
          }

    //get login details
    public function login_process(){
        //print_r("hii");die;

				if($this->input->get('username')=='' || $this->input->get('password')=='')
				{
					 $response = array('success' => false, 'message' => ("Enter username & password"));
					  echo json_encode($response);
					  die;
				}
         $remember = false;
            $status = $this->ion_auth->login($this->input->get('username'), $this->input->get('password'), $remember);
       

        if($status){
            $post_params = array('username' =>$this->input->get('username'),'active'=> 1,'group_id'=>14);
         $result = $this->user_api_model->login_process($post_params);
              if(!$result){
          $response = array('success' => false, 'message' => ("You don't have access this page"));
              }
              else
         $response = array('success' => true, 'data' => $result, 'message' => ("Login Successfully"));
        }else{
            $response = array('success' => false, 'message' => ("Invalid Login Details"));
        }

        echo json_encode($response);

    }

    //forgot password
    public function forgot_password(){

        $email = $this->input->get('email');
        $result = $this->user_api_model->getVanDriverByEmail($email);
        if($result) {

            $forgotten = $this->user_api_model->forgotten_password($email);
          //  print_r($forgotten);die;

            if ($forgotten) {
                $response = array('success' => true, 'data' => $forgotten, 'message' => ("Email has been sent with reset password instructions."));
            } else {
                $response = array('success' => false, 'message' => ("Something went wrong"));
            }

        }else{
            $response = array('success' => false, 'data' => $result, 'message' => ("The email address enter does not belong to any account."));
        }
        echo json_encode($response);
    }
}
