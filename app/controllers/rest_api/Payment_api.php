<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Payment_api extends MY_Controller
{
    function __construct()
    {
        parent:: __construct();
        $this->load->model('rest_api_models/authenticateUser_model');
        $this->load->model('rest_api_models/tyre_booking_api_model');
        $this->load->model('rest_api_models/payment_model_api');
    }


    //view payments
    public function getPaymentsAgainstBooking()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $user_id = $this->input->post('user_id');
        $tyre_booking_id = $this->input->post('tyre_booking_id');

        if($this->authenticateUser_model->authenticateUser($username, $password)){

            //get booking deatils
            $getBookingDetails = $this->tyre_booking_api_model->getBookingDetailsById($tyre_booking_id);
            //print_r($getBookingDetails->sale_id);die;
            //
            if($getBookingDetails){
                $sale_id = $getBookingDetails->sale_id;    // get sale id

                if($sale_id != 0 && $sale_id != null){
                    $getPaymentDetails = $this->payment_model_api->getSalePayments($sale_id);

                    //get we get payments
                    if($getPaymentDetails){
                        $data['payments'] = $getPaymentDetails;
                        $response = array('success' => true, 'data' => $data, 'message' => ("Successfully"));
                    }else{
                        $response = array('success' => false, 'message' => ("Something went wrong"));
                    }
                }else{
                    $response = array('success' => false, 'message' => ("Booking is not completed"));
                }

            }else{
                $response = array('success' => false, 'message' => ("Something went wrong"));
            }

        }else{
            $response = array('success' => false, 'message' => ("You are not allowded to do that"));
        }
        echo json_encode($response);


    }

}