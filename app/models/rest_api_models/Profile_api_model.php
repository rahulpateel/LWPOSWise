<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Profile_api_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }


    public function getVanDriverProfileData($id)
    {

        $query= $this->db->get_where('van_drivers', array('id'=>$id));

        if($query->num_rows()>0){
            return $query->row();
        }
        return false;
    }

    public function updateVanDriverProfile($data, $id){

        $this->db->where('id',$id);
        $sql=$this->db->update('van_drivers',$data);

        if ($sql) {
            return true;
        } else {
            return false;
        }
    }

    public function checkOldPass($id,$old_password)
    {
        $this->db->where('id', $id);
        $this->db->where('password', $old_password);
        $query = $this->db->get('van_drivers');
        if($query->num_rows() > 0)
            return 1;
        else
            return 0;
    }

}
