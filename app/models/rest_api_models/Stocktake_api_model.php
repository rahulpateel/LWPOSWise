<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Stocktake_api_model extends CI_Model
{

  public function __construct()
  {
    parent::__construct();

  }


  public function getStockTakesByCond($cond){
    $this->db->select('c.name as category_name,c1.name as subcategory_name,concat(u.first_name," ",u.last_name) as assignedby,s.*', FALSE)
    ->from('stock_takes s')
    ->join('categories c','c.id = s.category_id','left')
    ->join('categories c1','c1.id = s.subcategory_id','left')
    ->join('users u','u.id =s.updated_by','left');

    $this->db->where( $cond );
    $result = $this->db->get();
		//print_r($this->db->last_query());
    if ($result->num_rows() > 0) {
     foreach (($result->result()) as $row) {
      $data[] = $row;
    }
    return $data;
  }
  return FALSE;
}


public function getProductByCode($code)
{
  $this->db->select('' . $this->db->dbprefix('products') . '.id, ' . $this->db->dbprefix('products') . '.code, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price as price, ' . $this->db->dbprefix('products') . '.quantity,category_id,subcategory_id,units.name as unitname')
  ->where("type != 'combo' AND " . $this->db->dbprefix('products') . ".code ='" . $code . "'");
  $this->db->join('units', 'units.id=products.unit', 'left');
           // ->where('' . $this->db->dbprefix('product_variants') . '.name', NULL)
            //->group_by('products.id')->limit($limit);
  $q = $this->db->get('products');
      //  print_r($this->db->last_query());die;
  if ($q->num_rows() > 0) {
    return $q->row();
  }
  return FALSE;
}

public function checkStockTakeAssignment($user_id,$month,$category_id,$subcategory_id){

  $query='user_id='.$user_id.' and for_month="'.$month.'" and category_id='.$category_id;
  if($subcategory_id)
  {
    $query='user_id='.$user_id.' and for_month="'.$month.'" and category_id='.$category_id.' and subcategory_id = '.$subcategory_id;

  }
  //echo "q ".$query;
  $this->db->select('*')
  ->where($query);
            //->group_by('products.id')->limit($limit);

  $q = $this->db->get('stock_takes');
   
  if ($q->num_rows() > 0) {
    return $q->row();
  }
  return FALSE; 
}

public function getProductStockTakeBycond($cond){
  $this->db->select('p.name as product_name,c.name as category_name,c1.name as subcategory_name,s.*', FALSE)
  ->from('stock_take_items s')
  ->join('products p','p.id = s.product_id','left')
  ->join('categories c','c.id = p.category_id','left')
  ->join('categories c1','c1.id = p.subcategory_id','left');
        //->join('users u','u.id =s.updated_by','left');
  if($cond)
    $this->db->where( $cond );
  $result = $this->db->get();
        //print_r($this->db->last_query());
  if ($result->num_rows() > 0) {
    foreach (($result->result()) as $row) {
      $data[] = $row;
    }
    return $data;
  }
  return FALSE;
}

public function getStockTakeDashboardCount($user_id,$for_month){

  $cond="created_by=".$user_id." AND for_month='".$for_month."'";
       // echo "cond ".$cond;
  $this->db->select('COUNT(*) as count ,stock_flag', FALSE)
  ->from('stock_take_items')->group_by('stock_flag');
  $this->db->where( $cond );
  $result = $this->db->get();
        //print_r($this->db->last_query());
  if ($result->num_rows() > 0) {
    foreach (($result->result()) as $row) {
      $data[] = $row;
    }

    return $data;
  }
  return FALSE;
}

public function getProductsCountForDashboard($user_id,$for_month){

  $count=0;
  $countsArr = array('prod_count' => 0,'assigned_count'=>0 );
  $query = $this->db->query("SELECT * FROM sma_stock_takes WHERE user_id=".$user_id." AND for_month='".$for_month."'");
  $assigned_count=$query->num_rows();
  if($assigned_count>0 )
  {
    foreach (($query->result()) as $row) {

      if(!empty($row->subcategory_id))
      {
        $q='SELECT COUNT(*) as count FROM  sma_products where category_id='.$row->category_id.' and '.' subcategory_id='.$row->subcategory_id.' and is_active=1';


      }
      else
      {
       $q='SELECT COUNT(*) as count FROM  sma_products where category_id='.$row->category_id.' and is_active=1';
     }

     $cntQuery=$this->db->query($q);
     if($cntQuery->num_rows()>0)
     {
                            //echo "".json_encode($cntQuery->result()[0]);
       $count=$count+$cntQuery->result()[0]->count;     
     }
   }

   $countsArr['prod_count']=$count;
   $countsArr['assigned_count']=$assigned_count;

   return $countsArr;
 }


 else
 {
  return $countsArr;   
}

}

public function getReport($user_id,$for_month,$cat_id=NULL,$sub_cat_id=NULL,$status=NULL){

  $count=0;
  $result = array();

  $queryString="SELECT s.*,c.name AS category_name,sc.name AS subcategory_name FROM sma_stock_takes s INNER JOIN sma_categories c ON c.id=s.category_id  LEFT JOIN sma_categories sc ON sc.id=s.subcategory_id WHERE user_id=".$user_id." and for_month='".$for_month."'";
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
    //echo "query ".$queryString;
  $query = $this->db->query($queryString);


  $assigned_count=$query->num_rows();
  if($assigned_count>0 )
  {
    foreach (($query->result()) as $row) {

      if(!empty($row->subcategory_id))
      {
        $q='SELECT COUNT(*) as count FROM  sma_products where category_id='.$row->category_id.' and '.' subcategory_id='.$row->subcategory_id.' and is_active=1';
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
public function getStatusByStockTakeID($id){
  try
   {

    $status='Assigned';
    if(!empty($id))
    {

      $queryString="";
      $queryString='SELECT category_id,subcategory_id FROM sma_stock_takes WHERE id='.$id;
      $query = $this->db->query($queryString);
      if($query->num_rows()>0)
      {
                            //echo "".json_encode($cntQuery->result()[0]);
        $category_id=$query->result()[0]->category_id;     
        $subcategory_id=$query->result()[0]->subcategory_id; 
        $this->load->model('rest_api_models/Product_api_model', 'product');
        $productCount=$this->product->getProductsCountByCategorySubcategory($category_id,$subcategory_id);
        $query = $this->db->query('SELECT count(*) as cnt FROM sma_stock_take_items WHERE stock_take_id='.$id);
        $totalChecksCount=0;
        if($query->num_rows()>0)
        {
          $totalChecksCount=$query->result()[0]->cnt;

        }
        if($totalChecksCount==$productCount)
        {
          $status='Completed';
        }
        else if($totalChecksCount>0)
        {
          $status='InProgress';
        }
      }
     }
    }
  catch (Exception $e) {
    return $status;  
  }
  return $status;
}

public function getProductsPendingCompleted($user_id,$cat_id,$sub_cat_id,$for_month,$completed=NULL)
{
  if($completed)
  {
    $queryString='SELECT CONCAT(NAME,"( ",CODE," )") as name FROM sma_products WHERE id  IN ( ';
  }
  else
  {
    $queryString='SELECT CONCAT(NAME,"( ",CODE," )") as name FROM sma_products WHERE id not IN ( ';  
  }
  
  $subQueryString='SELECT i.product_id FROM `sma_stock_take_items` i INNER JOIN sma_stock_takes s ON i.stock_take_id=s.id WHERE 1 ';
  $queryString.=$subQueryString;
  if($user_id)
  {
     $queryString.=' and s.user_id='.$user_id;
  }
  if($cat_id)
  {
     $queryString.=' and s.category_id='.$cat_id;
  }
  if($sub_cat_id)
  {
     $queryString.=' and s.subcategory_id='.$sub_cat_id;
  }
  if($for_month)
  {
     $queryString.=' and s.for_month='."'".$for_month."' ";
  }
   $queryString.=' ) ';
  // echo " ".$queryString;
  $q = $this->db->query($queryString);

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
public function getPendingProducts($stock_take_id,$page,$term=NULL)
     {

    $stock_take=$this->getStockTakeByID($stock_take_id);

    if(empty($stock_take))
    {

      return FALSE;

    }
    else
    {
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
    $queryString.=' and p.is_active=1 ';

    if(!empty($term))
    {
       $queryString.="and  p.name like '%".$term."%' or p.code like '%".$term."%'";
    }
   $queryString.=' limit '.(($page-1)*10).' , 10 ';
   
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
    
}

}
