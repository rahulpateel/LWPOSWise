<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Functions_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }


    //get currentcuSymbol
    public function getCurrencySymbol(){
        $symbol = "";
        $sql = "SELECT symbol FROM lw_settings";

        $result = $this->db->query($sql)->result();

        if($result[0]->symbol != NULl){
            $symbol = $result[0]->symbol;
        }

        return $symbol;
    }

}
