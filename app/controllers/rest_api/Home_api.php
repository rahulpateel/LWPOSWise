<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Home_api extends MY_Controller
{
    function __construct()
    {
        parent:: __construct();
        $this->load->model('rest_api_models/authenticateUser_model');
        $this->load->model('rest_api_models/user_api_model');
        $this->load->model('rest_api_models/functions_model');

    }

    public function home_menu(){

        $username = $this->input->get('username');
        $password = $this->input->get('password');
        $user_id = $this->input->get('user_id');
        $oneSignal_id = $this->input->get('oneSignal_id');

        if($this->authenticateUser_model->authenticateUser($username, $password)){

            $profile_data = $this->user_api_model->getProfileData($user_id);

            $statusData = array(
                'notification_id' => $oneSignal_id);

            $this->db->where(array('id'=>$user_id));
            $this->db->update('lw_van_drivers',$statusData);    //update notification id


            //van driver menu list
            $menus = array("New Bookings",  //1
                "Past Bookings",   //2
                "My Profile",   //3
                "My Routes",   //6
                "About RangerTyres",     //4
                "Logout");     //5

            $menusIDs = array(1,2,3,6,4,5);

            $response = array( 'success' => true, 'currency_symbol' => $this->functions_model->getCurrencySymbol(), 'profile' => $profile_data , 'page_title' => "New Bookings", 'menu' => $menus, 'menusIDs' => $menusIDs, 'message'  => ''  );
            echo json_encode($response);

        }else{
            $response = array('success' => false, 'message' => ("You are not allowded to do that"));
            echo json_encode($response);
        }

    }

}
