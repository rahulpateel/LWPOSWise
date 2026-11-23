<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Stocktake_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getAllStockTakes()
    {
        $q = $this->db->get('stock_takes');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getStockTakeByID($id)
    {
        $q = $this->db->get_where('stock_takes', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getAllStockTakeUsers() {
        $this->db->where('group_id', 14);
        $q = $this->db->get("users");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getMonthName($value)
    {
        $value=trim($value);  
         $months = array(
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July ',
                'August',
                'September',
                'October',
                'November',
                'December',
            );
         if($value=='01' || $value=='1' )
         {
            return $months[0];
         }
         else if($value=='02' || $value=='2' )
         {
            return $months[1];
         }
         else if($value=='03' || $value=='3' )
         {
            return $months[2];
         }
         else if($value=='04' || $value=='4' )
         {
            return $months[3];
         }
         else if($value=='05' || $value=='5' )
         {
            return $months[4];
         }
         else if($value=='06' || $value=='6' )
         {
            return $months[5];
         }
         else if($value=='07' || $value=='7' )
         {
            return $months[6];
         }
         else if($value=='08' || $value=='8' )
         {
            return $months[7];
         }
        else if($value=='09' || $value=='9' )
         {
            return $months[8];
         }
         else if($value=='10'  )
         {
            return $months[9];
         }
         else if($value=='11' )
         {
            return $months[10];
         }
         else if($value=='12'  )
         {
            return $months[11];
         }
         else
         {
            return "";   
         }
          
    }


public function getConsolidatedReport($user_id=NULL,$for_month=NULL,$cat_id=NULL,$sub_cat_id=NULL,$status=NULL,$excel=FALSE,$ids=NULL){

  $count=0;
  $result = array();

  $queryString='SELECT s.*,c.name AS category_name,sc.name AS subcategory_name,concat(u.first_name," ",u.last_name) as user FROM sma_stock_takes s INNER JOIN sma_categories c ON c.id=s.category_id 
      LEFT JOIN sma_categories sc ON sc.id=s.subcategory_id 
      INNER JOIN sma_users u on u.id=s.user_id 
      WHERE 1 ';

if($excel)
{
  $arrTID=explode(",", trim($ids,','));
  $idvals="";
  $idvals=implode(',', $arrTID); 
  $queryString.=" and s.id in ( ".$idvals.") ";
  //echo "string ".$queryString;die;
  //$this->db->where_in('s.id', $arrTID);
} 
else
{
  if(!empty($user_id))
  {
    $queryString.=" and s.user_id=".$user_id; 
  }
  if(!empty($for_month))
  {
    $queryString.=" and for_month='".$for_month."'";
  }

  if(!empty($cat_id))
  {
    $queryString.=" and s.category_id=".$cat_id; 
  }
  if(!empty($sub_cat_id))
  {
    $queryString.=" and s.subcategory_id=".$sub_cat_id; 
  }
  if(!empty($status))
  {
    $queryString.=" and s.status='".$status."'"; 
  }

}
  
    //echo "query ".$queryString;
  $query = $this->db->query($queryString);


  $assigned_count=$query->num_rows();
  if($assigned_count>0 )
  {
    foreach (($query->result()) as $row) {

      if(!empty($row->subcategory_id))
      {
        $q='SELECT COUNT(*) as count FROM  sma_products where category_id='.$row->category_id.' and '.' subcategory_id='.$row->subcategory_id .' and is_active=1';
      }
      else
      {
       $q='SELECT COUNT(*) as count FROM  sma_products where category_id='.$row->category_id.' and is_active=1';
     }

     $cntQuery=$this->db->query($q);
     if($cntQuery->num_rows()>0)
     {
                            //echo "".json_encode($cntQuery->result()[0]);
      $row->prod_count=$cntQuery->result()[0]->count;     
    }


    $queryForCounts="SELECT COUNT(*) as count,stock_flag from sma_stock_take_items WHERE stock_take_id=".$row->id ." group by stock_flag";

    $countsQuery=$this->db->query($queryForCounts);
    if($countsQuery->num_rows()>0)
    {
      foreach (($countsQuery->result()) as $cntrow) {


       $row->cnts[]=$cntrow;
     //  echo "ROW ".json_encode($cntrow);
     }


     $data[]=$row;

   }
   else
   {
          $row->cnts[]=array('count' => "0",'stock_flag'=>'Matched');
          $row->cnts[]=array('count' => "0",'stock_flag'=>'Mismatched');
          $data[]=$row;
   }

 }

 if(!empty($data))
 {
  $result['stock_takes']=$data;
}
else
{
  $result=array();
}


return $result;
}
else
{

   // $result['stock_takes']=FALSE;
  return $result;   
}

}
   
 public function getStockTakeItemByID($id)
    {
        $q = $this->db->get_where('stock_take_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

public function getPendingProducts($stock_take_id)
     {


    $stock_take=$this->getStockTakeByID($stock_take_id);
    $queryString='SELECT CONCAT(p.NAME,"( ",p.CODE," )") as name ,c.name as category_name,sc.name as subcategory_name FROM sma_products p '; 

     $queryString.=' inner join sma_categories c on c.id=p.category_id left join sma_categories sc on sc.id=p.subcategory_id ';

     $queryString.= ' WHERE p.id not IN ( ';
  
  
  $subQueryString='SELECT product_id FROM sma_stock_take_items  WHERE stock_take_id='.$stock_take_id;


   $queryString.=$subQueryString;
   $queryString.=' ) ';
   $queryString.=' and category_id='.$stock_take->category_id;
   if(!empty($stock_take->subcategory_id))
   {
     $queryString.=' and subcategory_id='.$stock_take->subcategory_id;

   }
   $queryString.=' and p.is_active=1';
   
    //echo "  ".$queryString;
 
  $q = $this->db->query($queryString);
    if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
           return $data;
        }
        return FALSE;
}

public function getAuditData($cat_id=NULL,$sub_cat_id=NULL,$user_id=NULL,$from_date=NULL,$to_date=NULL)
     {

        $queryString='SELECT a.id as audit_id,p.name AS product,c.name AS category,sc.name AS subcategory ,CONCAT(u.first_name," ",u.last_name) AS USER,a.created_date,a.stock_qty,a.old_stock_qty FROM sma_stock_take_audit a LEFT JOIN sma_stock_take_items i ON a.stock_take_item_id = i.id LEFT JOIN sma_products
          p ON p.id=i.product_id  INNER JOIN sma_users u ON u.id=a.admin_id 
          LEFT JOIN sma_categories c ON c.id=p.category_id
          LEFT JOIN sma_categories sc ON sc.id=p.subcategory_id where 1 ';

          if(!empty($cat_id))
          {
            $queryString.=' and p.category_id='.$cat_id;              
          }
           if(!empty($sub_cat_id))
          {
            $queryString.=' and p.subcategory_id='.$sub_cat_id;              
          } 
          if(!empty($user_id))
          {
            $queryString.=' and a.admin_id='.$user_id;              
          }  
          if(!empty($from_date))
          {
            $queryString.=" and a.created_date >= '$from_date' and a.created_date < '$to_date'";              
          } 

          $queryString.=' ORDER BY a.created_date DESC ';

            //echo "string ".$queryString;
              $q = $this->db->query($queryString);
            if($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
           return $data;
            }
            return FALSE;
}

public function getAllAdmins() {
        $this->db->where('group_id', 1);
        $q = $this->db->get("users");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

   

    
}
