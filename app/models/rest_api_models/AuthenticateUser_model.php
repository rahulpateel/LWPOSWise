<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AuthenticateUser_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    //authenticate user by username and password
    public function authenticateUser($username, $password){

        $query = "SELECT * FROM lw_van_drivers WHERE username = '". $username. "' AND password = '". $password."'";
        $row= $this->db->query($query);

        if($row->num_rows()>0){
            return true;
        }else{
            return false;
        }

    }
}
