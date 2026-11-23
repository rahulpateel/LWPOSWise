<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Storesync_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

	public function getLastDownloadedData($id)
	{
		$this->db->select("downloaded_at,CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name, ' (', users.email, ')') as user", FALSE)
		         ->from("update_status")
		         ->join('users', 'users.id=update_status.download_by', 'left')
			     -> where('update_status.id',$id);
		$q = $this->db->get();
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}

	public function getUpdatedData($table,$date,$columns,$w_c){
		//print_r(implode(',',$columns));die;
		$this->db->select(implode(',',$columns))
		         ->from($table)
		         ->where($w_c.' >', $date);
		if($table=='companies')
			$this->db->where('group_id',3);
		$q = $this->db->get();
		if ($q->num_rows() > 0) {
			/*foreach (($q->result_array()) as $row) {
				$data[] = $row;
			}*/
			return $q->result_array();
		}
		return FALSE;
	}
	public function getAllSubstores()
	{
		$this->db->select('id,name,membership_id')
		         ->from('companies')
		         ->where('customer_group_name','SMSubstores');
		$q = $this->db->get();
		if ($q->num_rows() > 0) {
			foreach (($q->result()) as $row) {
				$data[] = $row;
			}
			return $data;
		}
		return FALSE;
	}

	public function getTableData($table,$columns,$w_c){
		//print_r($w_c);die;
		$this->db->select(implode(',',$columns))
		         ->from($table);

		if(count($w_c)>0)
		{
			foreach ($w_c as $key=>$val){
				
				if($key=='date')
					$this->db->where($key.' >', $val);
				elseif(is_array($val))
				{
					$this->db->where_in($key,$val);
				}
				else
					$this->db->where($key,$val);

			}
		}

		$q = $this->db->get();
		if ($q->num_rows() > 0) {
			/*foreach (($q->result_array()) as $row) {
				$data[] = $row;
			}*/
			return $q->result_array();
		}
		return FALSE;
	}

	public function getAllInternalBranches() 
	{
		$this->db->select('id,name')
		         ->from('internal_branches');
		$q = $this->db->get();
		if ($q->num_rows() > 0) {
			foreach (($q->result()) as $row) {
				$data[] = $row;
			}
			return $data;
		}
		return FALSE;
	}

	 public function addInternalPurchase($data, $items)
    {

        if ($this->db->insert('internal_purchases', $data)) {
            $purchase_id = $this->db->insert_id();
            if ($this->site->getReference('ipo') == $data['reference_no']) {
                $this->site->updateReference('ipo');
            }
            foreach ($items as $item) {
                $item['purchase_id'] = $purchase_id;
                $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : NULL;
                $this->db->insert('internal_purchase_items', $item);
            }
            return true;
        }
        return false;
    }

    public function getAllBranches($code) {
        $this->db->where('code !=',$code);
        $q = $this->db->get('internal_branches');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getBranchByID($id) {
        $q = $this->db->get_where('internal_branches',array('id'=>$id),1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateInternalPurchase($data, $items ,$purchase_id)
    {

        if ($this->db->update('internal_purchases', $data,array('id'=>$purchase_id)) && $this->db->delete('internal_purchase_items',array('purchase_id'=>$purchase_id))) {
            foreach ($items as $item) {
                $item['purchase_id'] = $purchase_id;
                $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : NULL;
                $this->db->insert('internal_purchase_items', $item);
            }
            return true;
        }
        return false;
    }
}
