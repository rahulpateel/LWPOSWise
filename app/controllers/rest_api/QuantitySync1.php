<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class QuantitySync extends MY_Controller
{
	
    function __construct()
    {
        parent:: __construct();
       // $this->load->model('rest_api_models/stocktake_api_model');
         $this->load->admin_model('products_model');
		define('AUTHKEY','cvsk2783lejr');
    }

    

	public function syncQuantity()
	{
		# code...
		echo "<html><body>Start ".date("d-m-Y H:i:s")."<br/>";
		 $pp = "( SELECT product_id, SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN quantity ELSE 0 END) as purchasedQty, SUM(pi.quantity_balance) as balacneQty  from {$this->db->dbprefix('purchase_items')} pi inner JOIN {$this->db->dbprefix('purchases')} p on p.id = pi.purchase_id ";
         $sp = "( SELECT si.product_id, SUM( si.quantity ) soldQty from " . $this->db->dbprefix('sales') . " s JOIN " . $this->db->dbprefix('sale_items') . " si on s.id = si.sale_id ";

         $pp .= " GROUP BY pi.product_id ) PCosts";
         $sp .= " GROUP BY si.product_id ) PSales";

         $this->db
                ->select($this->db->dbprefix('products') . ".id, " . $this->db->dbprefix('products') . ".name," . $this->db->dbprefix('products') . ".code,
				COALESCE( PCosts.purchasedQty, 0 ) as PurchasedQty,
				COALESCE( PSales.soldQty, 0 ) as SoldQty,
				COALESCE( PCosts.purchasedQty-PSales.soldQty, 0 ) as BalacneQty,
				COALESCE( PCosts.balacneQty, 0 ) as QtyBal,
				", FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
                ->order_by('products.name');

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }
           foreach ($data as $data_row) {
           	
         //  	echo " code ".$data_row->code." name ".$data_row->name." RequiredQuantity ".$data_row->BalacneQty;

           // echo " purchasedQty ".$data_row->PurchasedQty."  soldQty ".$data_row->SoldQty."<br/>";

              //code starts /*
              
           		if($data_row->BalacneQty<=0)
           		{
           			//echo "Qty <=0 ".$data_row->BalacneQty."<br/>";
           			    $this->db->where('id',$data_row->id);
                    	$this->db->set('quantity', 0, FALSE);
                    	$this->db->update('products'); 

                    	//update quantity in warehouses product
	 	            	$this->db->where('product_id',$data_row->id);
                    	$this->db->set('quantity', 0, FALSE);
                    	$this->db->update('warehouses_products'); 
           		}	
           		else if($data_row->QtyBal!=$data_row->BalacneQty)
           		{
           			//echo "NQ ";
           			if($data_row->QtyBal>$data_row->BalacneQty)
           			{
           			//	echo " Minus ".($data_row->QtyBal-$data_row->BalacneQty)."<br/>";
           				$amount=($data_row->QtyBal-$data_row->BalacneQty);
           				$this->adjustQty($data_row->id,$amount,NULL,1,$data_row->BalacneQty);
           			}
           			else
           			{
           				$amount=($data_row->BalacneQty - $data_row->QtyBal);
           			//	echo " Plus ".($data_row->BalacneQty - $data_row->QtyBal)."<br/>";
           				$this->adjustQty($data_row->id,$amount,1,1,$data_row->BalacneQty);
           			}
           		}
           		else
           		{
           			  //  echo "Qty == ".$data_row->BalacneQty."<br/>";
           			    
           				$this->db->where('id',$data_row->id);
                    	$this->db->set('quantity',$data_row->BalacneQty, FALSE);
                    	$this->db->update('products'); 

                    	//update quantity in warehouses product
	 	            	$this->db->where('product_id',$data_row->id);
                    	$this->db->set('quantity', $data_row->BalacneQty, FALSE);
                    	$this->db->update('warehouses_products'); 

           		}

              
              
              // */code ends
           }
           echo "End ".date("d-m-Y H:i:s")."<br/>";
          // echo "<script>  window.close(); </script></body>";
	}
	 public function adjustQty($product_id,$amount,$is_plus=NULL,$is_update_purchase=NULL,$orgQty)
	 {		
	 	           /* if($is_plus)
	 	            {
	 	            	echo "adjustQty Plus ".$amount."<br/>";
	 	            }
	 	            else
	 	            {
	 	            	echo "adjustQty Minus ".$amount."<br/>";
	 	            }
	 	            return 0;*/

	 	            if($is_plus)
	 	            {
	 	            	//update quantity in product
	 	            	$this->db->where('id',$product_id);
                    	$this->db->set('quantity',$orgQty, FALSE);
                    	$this->db->update('products'); 

                    	//update quantity in warehouses product
	 	            	$this->db->where('product_id',$product_id);
                    	$this->db->set('quantity', $orgQty, FALSE);
                    	$this->db->update('warehouses_products'); 

                    	if($is_update_purchase)
                    	{
                    	  //update quantity in purchase item balance
                    	  //get latest purchase item and add
			                $query=$this->db->query('SELECT i.id,i.product_id,p.date,quantity_received FROM sma_purchase_items i INNER JOIN  sma_purchases p ON p.id=i.purchase_id 
			                WHERE  i.product_id='.$product_id.'
			                ORDER BY p.date DESC LIMIT 0,1');

			                if($query->num_rows()>0)
			                {
			                    $this->db->where('id',$query->row()->id);
			                    $this->db->set('quantity_balance', 'quantity_balance+'."'".$amount."'", FALSE);
			                    $this->db->update('purchase_items');
			                }

                    	}


	 	            }
	 	           else //minus part
	 	           {
	 	            	//update quantity in product
	 	            	$this->db->where('id',$product_id);
                    	$this->db->set('quantity', $orgQty, FALSE);
                    	$this->db->update('products'); 

                    	//update quantity in warehouses product
	 	            	$this->db->where('product_id',$product_id);
                    	$this->db->set('quantity', $orgQty, FALSE);
                    	$this->db->update('warehouses_products'); 

             if($is_update_purchase)
             { //minus part
                
				$qtyMinus=$amount;
                $count=0;
                do 
                {
                      //minus in purchase item
                $queryString='SELECT i.id,i.product_id,p.date,quantity_balance FROM sma_purchase_items i INNER JOIN  sma_purchases p ON p.id=i.purchase_id 
                WHERE  i.product_id='.$product_id.' AND quantity_balance > 1 
                ORDER BY p.date ASC ';
               // echo " qs ".$queryString." ";
                $query=$this->db->query($queryString);

                if($query->num_rows()>0) //if rows present
                {
                    //for loop start
                    foreach (($query->result()) as $row) {

                                if(!empty($row->quantity_balance))
                                {
                                                                           
                                        if($qtyMinus<1 && $qtyMinus > 0 ) //if qty is <1 && >0 like 0.66
                                        {
                                            $this->db->where('id',$row->id);
                                            $this->db->set('quantity_balance', 'quantity_balance-'."'".$qtyMinus."'",FALSE);
                                            $this->db->update('purchase_items');
                                            $qtyMinus=0;
                                            break;


                                        }
                                        else
                                        {
                                            if($count < 1) //for first iteration
                                            {
                                                $this->db->where('id',$row->id);
                                                $this->db->set('quantity_balance', 'quantity_balance-1',FALSE);
                                                $this->db->update('purchase_items');
                                               
                                                $qtyMinus=$qtyMinus-1;

                                                if($qtyMinus==0)
                                                {
                                                    break;
                                                }

                                            }
                                            else
                                            {   //from second iteration onwords
                                                // echo "in cn2 ".$query->row()->id;
                                                 
                                               

                                                if($row->quantity_balance > $qtyMinus)
                                                {
                                                    $this->db->where('id',$row->id);
                                                    $this->db->set('quantity_balance', 'quantity_balance-'."'".$qtyMinus."'",FALSE);
                                                    $this->db->update('purchase_items');
                                                    $qtyMinus=0;
                                                  
                                                    break;

                                                }
                                                else
                                                {

                                                   $this->db->where('id',$row->id);
                                                    $this->db->set('quantity_balance', 'quantity_balance-1',FALSE);
                                                    $this->db->update('purchase_items');
                                                    $qtyMinus=$qtyMinus-1;
                                                     if($qtyMinus==0)
                                                    {
                                                         break;
                                                    }
                                                    

                                                }


                                            }
                                            
                                            if($qtyMinus==0)
                                            {
                                                break;
                                            }

                                        }    
                                                
                               }
                       
                       }//for loop end

                       $count=$count+1;
                       if($qtyMinus<=0)
                        {
                           break;
                        }
                    
                }//if row not presnt
                else
                {
                    $qtyMinus=0;
                    break;

                }
                } 
                while ($qtyMinus!=0);
             
                //die;
            }

	 	}
	 	            
	 	
	 }
}
