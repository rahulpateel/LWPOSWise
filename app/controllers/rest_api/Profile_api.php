<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Profile_api extends MY_Controller
{
    function __construct()
    {
        parent:: __construct();
        $this->load->library('form_validation');
        $this->load->model('rest_api_models/profile_api_model');
        $this->load->model('rest_api_models/authenticateUser_model');
        $this->load->model('rest_api_models/user_api_model');
        define('AUTHKEY','cvsk2783lejr');
    }


    public function getProfileData()
    {
        $id=$this->input->get('user_id');
       
        $data=$this->user_api_model->getProfileData($id);

        $auth_key = $this->input->get('auth_key');
        
        if ( $auth_key == '' || AUTHKEY != $auth_key) {
            $response = array('success' => 0, 'message' => ("Authentication key is not matching!"));
            echo json_encode($response);
            die;
        }   

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

    //update profile
    public function updateProfile()
    {
        try {
                $this->form_validation->set_data($this->input->get());
                $first_name = $this->input->get('first_name');
                $last_name = $this->input->get('last_name');
                $phone = $this->input->get('phone');
                $email = $this->input->get('email');
                $user_id = $this->input->get('user_id');
                $auth_key = $this->input->get('auth_key');

                if ( $auth_key == '' || AUTHKEY != $auth_key) {
                      $response = array('success' => 0, 'message' => ("Authentication key is not matching!"));
                       echo json_encode($response);
                       die;
                    } 

               
                $this->form_validation->set_rules('user_id', 'User ID','trim|required');
                $this->form_validation->set_rules('first_name', 'First name','trim|required');
                $this->form_validation->set_rules('last_name', 'Last name','trim|required');
                $this->form_validation->set_rules('phone', 'Phone No','trim|required|regex_match[/^[0-9]{10}$/]|callback_phone_check');
                 $this->form_validation->set_rules('email','Email' ,'trim|required|valid_email|callback_email_check');
                 $data = array();
                 if($this->form_validation->run()===FALSE){

                    $data = array('success'=>false,'data'=>"",'message'=>$this->stripHTMLtags( validation_errors()));
                 }
                 else
                 {

                    $userData = array('first_name' =>$first_name ,'last_name' =>$last_name ,'phone' =>$phone ,'email' =>$email );
                    $result=$this->db->update('users', $userData, array('id' => $user_id));
                    $userData=$this->user_api_model->getProfileData($user_id);

                    $data = array('success'=>true,'data'=>$userData,'message'=>'Profile updated successfully');

                 }
                
            
        } catch (Exception $e) {
            $data = array('success'=>false,'data'=>"",'message'=>'Some error occured');
            
        }
         echo json_encode($data);
     
    }

    //update new or change  password
    public function changePassword()
    {

        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $user_name = $this->input->post('user_name');
        $user_id = $this->input->post('user_id');

        if($this->authenticateUser_model->authenticateUser($username, $password)){

            $old_pass       = ($this->input->post('old_pass'))              ? trim($this->input->post('old_pass')) : '';
            $new_pass       = ($this->input->post('new_pass'))              ? trim($this->input->post('new_pass')) : '';
            $new_pass_con   = ($this->input->post('new_pass_con'))          ? trim($this->input->post('new_pass_con')) : '';

            if( $old_pass == "" )
            {
                $response = array( 'success' => false, 'data' => '' , 'message' => ("Please enter current password") );
                echo json_encode($response);
            }
            if( $new_pass == "" )
            {
                $response = array( 'success' => false, 'data' => '' , 'message' => ("Please enter new password") );
                echo json_encode($response);
            }

            if( $new_pass != $new_pass_con )
            {
                $response = array( 'success' => false, 'data' => '' , 'message' => ("Password confirm password mismatched"));
                echo json_encode($response);
            }

            $post_params = array(
                'password' => md5($new_pass),
            );

            $getOldPsw = $this->profile_api_model->checkOldPass($user_id, md5($old_pass));

            //check for old entered password is correct or not
            if($getOldPsw){
                //update password
                $if_updated = $this->profile_api_model->updateVanDriverProfile($post_params, $user_id);

                if($if_updated){

                    $profile_data = $this->profile_api_model->getVanDriverProfileData($user_id);

                    $response = array( 'success' => true, 'data' => $profile_data , 'message' => ("Password successfully changed"));
                    echo json_encode($response);
                }else{
                    $response = array( 'success' => false, 'data' => '' , 'message' => ("Something went wrong"));
                    echo json_encode($response);
                }

            }else{

                $response = array( 'success' => false, 'data' => '' , 'message' => ("Invalid old password"));
                echo json_encode($response);
            }

        }else{
            $response = array('success' => false, 'message' => ("You are not allowded to do that"));
            echo json_encode($response);
        }
    }


function email_check($email)
{
     $user_id = $this->input->get('user_id');
     $queryString='SELECT * FROM sma_users WHERE email='."'".$email."'".' AND id!='.$user_id;
     $query = $this->db->query($queryString);
     if($query->num_rows()>0)
     {
        $this->form_validation->set_message('email_check', 'email already used');
        return FALSE;
     }
     else
     {
       
        return TRUE;
     }
 
 }

function phone_check($phone)
{
     $user_id = $this->input->get('user_id');
     $queryString='SELECT * FROM sma_users WHERE phone='."'".$phone."'".' AND id!='.$user_id;
     $query = $this->db->query($queryString);
     if($query->num_rows()>0)
     {
        $this->form_validation->set_message('phone_check', 'Mobile no already used');
        return FALSE;
     }
     else
     {
       
        return TRUE;
     }
 
 }
private function stripHTMLtags($str)
{
    $t = preg_replace('/<[^<|>]+?>/', '', htmlspecialchars_decode($str));
    $t = htmlentities($t, ENT_QUOTES, "UTF-8");
    return $t;
}

}

?>