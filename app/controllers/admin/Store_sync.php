<?php defined('BASEPATH') OR exit('No direct script access allowed');

class store_sync extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }

        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('admin');
        }
        $this->lang->admin_load('settings', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('storesync_model');
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif';
        $this->allowed_file_size = '1024';
	    $this->load->admin_model('companies_model');
	    $this->load->admin_model('purchases_model');
	    $this->load->admin_model('settings_model');
    }


// update migrtaion for sub stores
function download_updates()
	{
		if (DEMO) {
			$this->session->set_flashdata('warning', lang('disabled_in_demo'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		if (!$this->Owner) {
			$this->session->set_flashdata('error', lang('access_denied'));
			admin_redirect("welcome");
		}
		//$last_updt_obj = $this->storesync_model->getLastDownloadedData(1);
		unset($_SESSION['error']);
		if ($this->input->post('download')) {
			$start_date = $this->input->post('start_date') ? $this->input->post('start_date') : NULL;


			if ($start_date) {
				$start_date = $this->sma->fld($start_date);

			}
			else
				$start_date = date('Y-m-d 00:00:00');

			//die($start_date);
			$zipname = 'storeupdates_' . date( 'Y_m_d_H_i_s' ) . '.zip';
			$zip = new ZipArchive;
			$res = $zip->open( 'store_update_files/'.$zipname, ZipArchive::CREATE );
			if ( $res === true ) {

				$tbls_data = array(
					'categories' => array( 'id', 'code', 'name', 'image', 'parent_id', 'slug' ),
					'brands'      => array( 'id', 'code', 'name', 'image', 'slug' ),
					'products'=> array('id','code','name','unit','cost','category_id','subcategory_id','tax_rate','details','barcode_symbology','product_details','tax_method','type','sale_unit','purchase_unit','brand','slug','featured','weight','gst_rate','gst_name','gst_code','cess','mrp','price'),

					'units'=> array('id', 'code', 'name', 'base_unit', 'operator', 'unit_value', 'operation_value'),
					'companies'=> array('id','group_id','group_name','customer_group_id','customer_group_name','name','company','vat_no','address','city','state','postal_code','country','phone','email','cf1','cf2','cf3','cf4','cf5','cf6','invoice_footer','payment_term','logo','award_points','deposit_amount','price_group_id','price_group_name','membership_id','father_name','house_no','occupation','religion','locality','certificate_no','distinctive_no','recieved_rs','no_of_share','date_of_birth','gender')
				);
				$tbllist ='';
				$flg = false;
				foreach ( $tbls_data as $tbl_name => $tbl_columns ) {
					//print_r($tbl_name);die;

					$records = $this->storesync_model->getUpdatedData( $tbl_name, $start_date, $tbl_columns,'updated_at' );

					$headers = $tbl_columns;


					if($records):
						$flg = true;
						$tbllist .= $tbl_name.',';
					$fd = fopen( 'php://temp/maxmemory:1048576', 'w' );
					if ( false === $fd ) {
						die( 'Failed to create temporary file' );
					}

					// write the data to csv
					fputcsv( $fd, $headers );
					foreach ( $records as $record ) {
						fputcsv( $fd, $record );
					}

					rewind( $fd );
					$zip->addFromString( $tbl_name . '.csv', stream_get_contents( $fd ) );
					endif;
				}
				//print_r($tbllist);die;
				//$zip->addFromString( 'meta.txt', rtrim($tbllist,',') );
				$zip->close();
				if($flg) {
					header( 'Content-Type: application/zip' );
					header( 'Content-disposition: attachment; filename=' . $zipname );
					header( 'Content-Length: ' . filesize( 'store_update_files/' . $zipname ) );
					readfile( 'store_update_files/' . $zipname );
				//	redirect($_SERVER["HTTP_REFERER"]);
				}
				else
				{
					//unlink('store_update_files/'.$zipname);

					$this->session->set_flashdata('error', 'No any updates found');
				}

			} else {
				echo 'failed';
			}
			//$this->db->update('update_status', array('downloaded_at' => date('Y-m-d H:i:s'), 'download_by' => $this->session->userdata('user_id')),array('id' => 1));
		}

		//$this->data['lastdownload'] = $last_updt_obj;
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Download Updates')));
		$meta = array('page_title' => lang('Download Updates'), 'bc' => $bc);
		$this->page_construct('storesync/updates', $meta, $this->data);

	}

	function download_store_sales()
	{


		$substores = $this->storesync_model->getAllSubstores();

		//$this->data['lastdownload'] = $last_updt_obj;
		$this->data['substores'] = $substores;
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Download Sales')));
		$meta = array('page_title' => lang('Download Sales'), 'bc' => $bc);
		$this->page_construct('storesync/download_sales', $meta, $this->data);

	}

	function import_store_update_files()
	{
		if (DEMO) {
			$this->session->set_flashdata('warning', lang('disabled_in_demo'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		if (!$this->Owner) {
			$this->session->set_flashdata('error', lang('access_denied'));
			admin_redirect("welcome");
		}

		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('updates')));
		$meta = array('page_title' => lang('updates'), 'bc' => $bc);
		$this->page_construct('storesync/install_store_updates', $meta, $this->data);

	}




	function install_store_update_files()
	{
		if (DEMO) {
			$this->session->set_flashdata('warning', lang('disabled_in_demo'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		if (!$this->Owner) {
			$this->session->set_flashdata('error', lang('access_denied'));
			admin_redirect("welcome");
		}

		//  print_r($_FILES);die;
		$file_name = $_FILES['userfile']['name'];
		$destination = './files/storeupdates/'.$file_name;
		$tmp_name = $_FILES["userfile"]["tmp_name"];
		if(!move_uploaded_file($tmp_name,$destination)){
			$this->session->set_flashdata('error', 'There is error in upload of updates');
			admin_redirect("store_sync/install_store_update_files");

		}


		$zip = new ZipArchive();
		if($zip->open($destination)){
			$path = './files/storeupdates/latestfiles/';
			$zip->extractTo($path);
			$zip->close();
		}
		$files = scandir($path);
		foreach ($files as $file) {
			if ( '.' === $file || '..' === $file || $file ==='') {
				continue;
			}

			$arrResult = array();
			$maxid = 0;
			$csv_file_path = $path . '/' . $file;
			$handle = fopen( $csv_file_path, "r" );
			if ( $handle ) {
				while ( ( $row = fgetcsv( $handle, 5000, "," ) ) !== false ) {
					$arrResult[] = $row;
				}
				fclose( $handle );
			}
			else
				continue;


			$titles = array_shift( $arrResult );
			$final = array();
			if ( $file == 'brands.csv' ) {
				$maxid = $this->db->query("SELECT MAX(id) AS maxid FROM ".$this->db->dbprefix('brands') )->row()->maxid;

				$keys = array( 'id', 'code', 'name', 'image', 'slug' );
				foreach ( $arrResult as $key => $value ) {
					$final = array_combine( $keys, $value );
					$id = $value[0];

					if($id > $maxid)
						$this->settings_model->addBrand($final);
					else
						$this->settings_model->updateBrand($id,$final);
				}


			}

			if ( $file == 'categories.csv' ) {
				$maxid = $this->db->query("SELECT MAX(id) AS maxid FROM ".$this->db->dbprefix('categories') )->row()->maxid;
				$keys = array( 'id', 'code', 'name', 'image', 'parent_id', 'slug' );
				foreach ( $arrResult as $key => $value ) {
					$final = array_combine( $keys, $value );
					$id = $value[0];
					if($id > $maxid)
						$this->settings_model->addCategory($final);
					else
						$this->settings_model->updateCategory($id,$final);
				}



			}
			if ( $file == 'companies.csv' ) {

				$keys = array(
					'id',
					'group_id',
					'group_name',
					'customer_group_id',
					'customer_group_name',
					'name',
					'company',
					'vat_no',
					'address',
					'city',
					'state',
					'postal_code',
					'country',
					'phone',
					'email',
					'cf1',
					'cf2',
					'cf3',
					'cf4',
					'cf5',
					'cf6',
					'invoice_footer',
					'payment_term',
					'logo',
					'award_points',
					'deposit_amount',
					'price_group_id',
					'price_group_name',
					'membership_id',
					'father_name',
					'house_no',
					'occupation',
					'religion',
					'locality',
					'certificate_no',
					'distinctive_no',
					'recieved_rs',
					'no_of_share',
					'date_of_birth',
					'gender'
				);
				foreach ( $arrResult as $key => $value ) {
					$final = array_combine( $keys, $value );
					$memid = $value[28];
					$id = $value[0];
					$q = $this->db->get_where('companies', array('membership_id' => $memid), 1);
					if ($q->num_rows() > 0)
						$this->companies_model->updateCompany($id,$final);
					else
						$this->companies_model->addCompany($final);


				}
			}
			if ( $file == 'products.csv' ) {
				$maxid = $this->db->query("SELECT MAX(id) AS maxid FROM ".$this->db->dbprefix('products') )->row()->maxid;
				//echo $maxid;die;
				$keys = array(
					'id','code','name','unit','cost','category_id','subcategory_id','tax_rate','details','barcode_symbology','product_details','tax_method','type','sale_unit','purchase_unit','brand','slug','featured','weight','gst_rate','gst_name','gst_code','cess','mrp','price'
				);

			    if(sizeof($arrResult[0]) ==26)
			    {
			    	$keys[]='exp_days';
			    }
				 unset($arrResult['enable_sale_point']);
			    

				foreach ( $arrResult as $key => $value ) {
					
					// unset($keys['enable_sale_point']);
					  //unset($value['enable_sale_point']);
					//echo json_encode($keys);
					//echo " &&&&&  ";
					//echo json_encode($value);
					$final = array_combine( $keys, $value );
					$id = $value[0];
					$ispresnt = $this->db->query("SELECT id FROM ".$this->db->dbprefix('products')." where id=".$id )->row();
					if(empty($ispresnt))//$id > $maxid
						$this->db->insert('products',$final);
					else {
						unset($final['mrp']);
						unset($final['price']);

						$this->db->update( 'products', $final, array( 'id' => $id ) );
					}
				}
			}
//die();
			unlink($csv_file_path);

		}

		//unlink('./files/storeupdates/' . $file_name);
		$this->session->set_flashdata('message', lang("update_done"));
		admin_redirect("store_sync/import_store_update_files");
	}





	function install_store_purchases()
	{
		if (DEMO) {
			$this->session->set_flashdata('warning', lang('disabled_in_demo'));
			redirect($_SERVER["HTTP_REFERER"]);
		}
		if (!$this->Owner) {
			$this->session->set_flashdata('error', lang('access_denied'));
			admin_redirect("welcome");
		}
		$branch_code = $this->Settings->branch_code;

		$this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
		if ($this->form_validation->run() == true) {
			$supplier_id = $this->input->post( 'supplier' );
			$warehouse_id = $this->input->post('warehouse');
			$supplier = $this->companies_model->getCompanyByID($supplier_id);
			$supplier_name = $supplier->name;
			//  print_r($_FILES);die;
			$file_name   = $_FILES['userfile']['name'];
			$destination = './files/purchases/' . $file_name;
			$tmp_name    = $_FILES["userfile"]["tmp_name"];
			if ( ! move_uploaded_file( $tmp_name, $destination ) ) {
				$this->session->set_flashdata( 'error', 'There is error in upload of updates' );
				admin_redirect( "store_sync/import_store_purchases" );

			}


			$zip = new ZipArchive();
			if ( $zip->open( $destination ) ) {
				$path = './files/purchases/csv/';
				$zip->extractTo( $path );
				$zip->close();
			}


			$arrResult = array();

			$csv_file_path = $path . 'purchase_items.csv';
			$handle        = fopen( $csv_file_path, "r" );
			if ( $handle ) {
				while ( ( $row = fgetcsv( $handle, 5000, "," ) ) !== false ) {
					$arrResult[] = $row;
				}
				fclose( $handle );
			}


			$titles = array_shift( $arrResult );
			$final  = array();

			//$arrResult = purchase_items;
			//$arrResult1 = purchases;

			foreach ( $arrResult as $key => $value ) {

				$final[$value[0]][] =$value;
				//$final['warehouse_id'] = $warehouse_id;


			}
			

			$arrResult1 = array();

			$csv_file_path1 = $path . 'purchases.csv';
			$handle1        = fopen( $csv_file_path1, "r" );
			if ( $handle1 ) {
				while ( ( $row1 = fgetcsv( $handle1, 5000, "," ) ) !== false ) {
					$arrResult1[] = $row1;
				}
				fclose( $handle1 );
			}


			$titles = array_shift( $arrResult1 );


			$salekeys = array(

				'reference_no',
				'date',
				'supplier_id',
				'supplier',
				'warehouse_id',
				'note',
				'total',
				'product_discount',
				'order_discount_id',
				'order_discount',
				'total_discount',
				'product_tax',
				'order_tax_id',
				'order_tax',
				'total_tax',
				'shipping',
				'grand_total',
				'paid',
				'payment_status',
				'due_date',
				'ipo_reference_no',
				'status'
			);
	
			$item_keys = array(
				'product_id',
				'product_code',
				'product_name',
				'option_id',
				'net_unit_cost',
				'unit_cost',
				'quantity',
				'warehouse_id',
				'item_tax',
				'tax_rate_id',
				'tax',
				'discount',
				'item_discount',
				'subtotal',
				'real_unit_cost',
				'product_unit_id',
				'product_unit_code',
				'unit_quantity',
				'cgst_rate',
				'sgst_rate',
				'gst_code',
				'igst_rate',
				'cgst_amt',
				'sgst_amt',
				'igst_amt',
				'cess_rate',
				'cess_amt',
				'date',
				'status',
				'quantity_received',
				'quantity_balance'
			);
			
			foreach ( $arrResult1 as $key => $value ) {
				$data  = array();
				$purchase_id = $value[0];
				$purchasedate = date('Y-m-d',strtotime($value[2]));
				unset($value[0]);
				$value[] = 'received';
				
				$data = array_combine( $salekeys, $value );
				$data['supplier_id'] = $supplier_id;
				$data['supplier'] = $supplier_name;
				$data['warehouse_id'] = $warehouse_id;

				$q = $this->db->get_where('purchases', array('reference_no' => $data['reference_no']));
				
				if($data['ipo_reference_no'] !='')
				{	
					$internal_purchase = $this->storesync_model->getInternalPurchase($data['ipo_reference_no']);					
				}				

				if ($q->num_rows() > 0) {
					continue;
				}

					foreach ($final[$purchase_id] as $item):
						$item[] = $purchasedate;
						$item[] = 'received';
						$item[]= $item[7];
						$item[] = $item[7];
					
					unset($item[0]);
					
					$products[] = array_combine( $item_keys, $item );
					
				if($data['ipo_reference_no'] !='')
				{	
					$purchase_item = $this->storesync_model->getInternalPurchaseItems($internal_purchase->id,$item[1]);
					$status = 'completed';
                	if($purchase_item->status !='completed')
                	{
                    	$quantity_received = $purchase_item->quantity_received;
                    	if(($purchase_item->quantity-$purchase_item->quantity_received) > $item[7])
                        	$status ='pending';
                	}
                	$quantity_received = $quantity_received + $item[7];
					$purchase_items[] = array(
                        'id' => $purchase_item->id,
                        'product_id' => $purchase_item->product_id,
                        'purchase_id' => $purchase_item->purchase_id,
                        'product_code' => $purchase_item->product_code,
                        'product_name' => $purchase_item->product_name,
                        'option_id' => $purchase_item->option_id,
                        'net_unit_cost' => $purchase_item->net_unit_cost,
                        'unit_cost' => $this->sma->formatDecimal($purchase_item->unit_cost),
                        'quantity' => $purchase_item->quantity,
                        'product_unit_id' => $purchase_item->product_unit_id,
                        'product_unit_code' => $purchase_item->product_unit_code,
                        'unit_quantity' => $purchase_item->unit_quantity,
                        'quantity_balance' => $purchase_item->quantity_balance,
                        'quantity_received' => $quantity_received,
                        'branch_code' => $purchase_item->branch_code,
                        'item_tax' => $item[9],
                        'tax_rate_id' => $purchase_item->tax_rate_id,
                        'tax' => $purchase_item->tax,
                        'discount' => $purchase_item->discount,
                        'item_discount' => $purchase_item->discount,
                        'subtotal' => $item[14],
                        'expiry' => $purchase_item->expiry,
                        'real_unit_cost' => $purchase_item->real_unit_cost,
                        'date' => $purchase_item->date,
                        'status' => $status,
                        'supplier_part_no' => $purchase_item->supplier_part_no,
                        'gst_code' => $purchase_item->gst_code,
                        'cgst_rate'=> $purchase_item->cgst_rate,
                        'sgst_rate'=> $purchase_item->sgst_rate,
                        'igst_rate'=> $purchase_item->igst_rate,
                        'cgst_amt' => $item[23],
                        'sgst_amt'=> $item[24],
                        'igst_amt'=> $item[25],
                        'cess_rate'=> $purchase_item->cess_rate,
                        'cess_amt'=> $item[27]
                    );
                }

				endforeach;
				
				$this->purchases_model->addPurchase($data, $products);
				if($data['ipo_reference_no'] !='')
				{
                    foreach ($purchase_items as $items) {
                        $this->db->update('internal_purchase_items', $items, array('id' => $items['id']));
                        //update purchase cost to product cost 30/07/2019
	                   // $this->db->update('products', array('cost'=>$items['real_unit_cost']), array('id' => $items['product_id']));
                    }
                    $items = $this->storesync_model->getAllInternalPOItems($internal_purchase->id);
                    $purchase_status='';
                    foreach($items as $product)
                	{
                    	if($product->status =='pending' || $product->status =='ordered')
                    	{
                        	$purchase_status = 'pending';
                        	break;
                    	}
                    	elseif($product->status =='completed')
                    	{
                        	$purchase_status ='completed';
                    	}

                	}               
                	$internal_purchase->status = $purchase_status; 
                	$this->db->update('internal_purchases', $internal_purchase, array('id' => $internal_purchase->id));
				}
				unset($products);
			}

			unlink($csv_file_path);
			unlink($csv_file_path1);


			//unlink('./files/updates/' . $file_name);
			//$this->session->set_flashdata( 'success', lang( 'update_done' ) );
			$this->session->set_flashdata('message', lang("update_done"));

			admin_redirect( "store_sync/install_store_purchases" );
		}
		else{
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			//admin_redirect("store_sync/install_store_purchases");
		}
		$this->data['warehouses'] = $this->site->getAllWarehouses();
		$this->data['suppliers'] = $this->site->getAllCompanies('store_supplier');
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Import_Store_Purchases')));
		$meta = array('page_title' => lang('Import_Store_Purchases'), 'bc' => $bc);
		$this->page_construct('storesync/import_store_purchases', $meta, $this->data);
	}

	public function getSalesOfSubstore($substore_id = null)
    {
    	$substore_id = $this->input->get('substore') ? $this->input->get('substore') : NULL;
	    $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
	    $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        $this->sma->checkPermissions('index');
        $this->load->library('datatables');
        if ($substore_id) {
            $this->datatables
                ->select("{$this->db->dbprefix('sales')}.id as id, DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date, reference_no, biller, name, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, {$this->db->dbprefix('sales')}.attachment, return_id")
                ->from('sales')
                ->join('companies', "companies.id={$this->db->dbprefix('sales')}.customer_id", 'left')
                ->where('customer_id', $substore_id)
                ->where('customer_group_name', 'SMSubstores');
	        if ($start_date and $end_date) {
		        $start_date = $this->sma->fld( $start_date );
		        $end_date   =  $this->sma->fld( $end_date );
		        $this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
	        }
		        $this->datatables->group_by("{$this->db->dbprefix('sales')}.id");
        }
        //$this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    function download($id=NULL)
	{
		//$last_updt_obj = $this->storesync_model->getLastDownloadedData(2);
		unset($_SESSION['error']);
		/*if ($this->input->post('download')) {
			$substore = $this->input->get( 'substore' ) ? $this->input->get( 'substore' ) : null;*/

//print_r($category_updates);die;
			$tbl_columns = array(
				'id',
				'reference_no',
				'date',
				'customer_id',
				'customer',
				'warehouse_id',
				'note',
				'total',
				'product_discount',
				'order_discount_id',
				'order_discount',
				'total_discount',
				'product_tax',
				'order_tax_id',
				'order_tax',
				'total_tax',
				'shipping',
				'grand_total',
				'paid',
				'payment_status',
				'due_date'
			);
			$tbl_name    = 'purchases';
			$item_columns = array(
				'sale_id',
				'product_id',
				'product_code',
				'product_name',
				'option_id',
				'net_unit_price',
				'unit_price',
				'quantity',
				'warehouse_id',
				'item_tax',
				'tax_rate_id',
				'tax',
				'discount',
				'item_discount',
				'subtotal',
				'real_unit_price',
				'product_unit_id',
				'product_unit_code',
				'unit_quantity',
				'cgst_rate',
				'sgst_rate',
				'gst_code',
				'igst_rate',
				'cgst_amt',
				'sgst_amt',
				'igst_amt',
				'cess_rate',
				'cess_amt'
			);

			$zipname = 'storepurchase_' . date( 'Y_m_d_H_i_s' ) . '.zip';
			$zip     = new ZipArchive;
			$res     = $zip->open( 'store_update_files/' . $zipname, ZipArchive::CREATE );
			//echo $res;die;
			$flg = false;
			if ( $res===true) {

				//$cond['date'] = $last_updt_obj->downloaded_at;
				//if($this->input->post('substore'))
				$cond['id'] = $id;
				//$cond['id'] = 2;

				$records = $this->storesync_model->getTableData( 'sales', $tbl_columns, $cond);
				//echo '<pre>';print_r($records);die;
				$headers = $tbl_columns;

				if ( $records ):
					$flg = true;
					$fd = fopen( 'php://temp/maxmemory:1048576', 'w' );
					if ( false === $fd ) {
						die( 'Failed to create temporary file' );
					}
					$pid_arr = array();
					// write the data to csv
					fputcsv( $fd, $headers );
					foreach ( $records as $record ) {
						fputcsv( $fd, $record );
						$pid_arr[]= $record['id'];

					}
					//echo '<pre>';print_r($pid_arr);die;
					rewind( $fd );
					$zip->addFromString( $tbl_name . '.csv', stream_get_contents( $fd ) );

					$pd = fopen( 'php://temp/maxmemory:1048576', 'w' );
					if ( false === $fd ) {
						die( 'Failed to create temporary file' );
					}
					fputcsv( $pd, $item_columns );
				foreach ($pid_arr as $pid):
					$wc['sale_id'] = $pid;
					$products = $this->storesync_model->getTableData( 'sale_items', $item_columns, $wc);

//print_r($products);die;

					if ( $products ):

						// write the data to csv

						foreach ( $products as $product ) {
							fputcsv( $pd, $product );
						}
					endif;
					endforeach;
						rewind( $pd );
						$zip->addFromString( 'purchase_items.csv', stream_get_contents( $pd ) );
				endif;

				//print_r($tbllist);die;
				//$zip->addFromString( 'meta.txt', rtrim($tbllist,',') );
				$zip->close();
				if($flg) {

					header( 'Content-Type: application/zip' );
					header( 'Content-disposition: attachment; filename=' . $zipname );
					header( 'Content-Length: ' . filesize( 'store_update_files/' . $zipname ) );
					readfile( 'store_update_files/' . $zipname );
				}else
				{
					//unlink('store_update_files/'.$zipname);

					$this->session->set_flashdata('error', 'No any updates found');
				}

			} else {
				echo 'failed';
			}
			$this->db->update('update_status', array('downloaded_at' => date('Y-m-d H:i:s'), 'download_by' => $this->session->userdata('user_id')),array('id' => 2));
		/*}*/
		}

	public function store_sync_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'export_zip') {
                	unset($_SESSION['error']);
					$tbl_columns = array(
						'id',
						'reference_no',
						'date',
						'customer_id',
						'customer',
						'warehouse_id',
						'note',
						'total',
						'product_discount',
						'order_discount_id',
						'order_discount',
						'total_discount',
						'product_tax',
						'order_tax_id',
						'order_tax',
						'total_tax',
						'shipping',
						'grand_total',
						'paid',
						'payment_status',
						'due_date'
					);
					$tbl_name    = 'purchases';
					$item_columns = array(
						'sale_id',
						'product_id',
						'product_code',
						'product_name',
						'option_id',
						'net_unit_price',
						'unit_price',
						'quantity',
						'warehouse_id',
						'item_tax',
						'tax_rate_id',
						'tax',
						'discount',
						'item_discount',
						'subtotal',
						'real_unit_price',
						'product_unit_id',
						'product_unit_code',
						'unit_quantity',
						'cgst_rate',
						'sgst_rate',
						'gst_code',
						'igst_rate',
						'cgst_amt',
						'sgst_amt',
						'igst_amt',
						'cess_rate',
						'cess_amt'
					);

					$zipname = 'storepurchase_' . date( 'Y_m_d_H_i_s' ) . '.zip';
					$zip     = new ZipArchive;
					$res     = $zip->open( 'store_update_files/' . $zipname, ZipArchive::CREATE );
					$flg = false;
					if ( $res===true) {
						foreach ($_POST['val'] as $id) {
                       $cond['id'][] = $id;
                   	}
				
					$records = $this->storesync_model->getTableData( 'sales', $tbl_columns, $cond);
					$headers = $tbl_columns;

					if ( $records ):
						$flg = true;
						$fd = fopen( 'php://temp/maxmemory:1048576', 'w' );
						if ( false === $fd ) {
							die( 'Failed to create temporary file' );
						}
						$pid_arr = array();
						// write the data to csv
						fputcsv( $fd, $headers );
						foreach ( $records as $record ) {
							fputcsv( $fd, $record );
							$pid_arr[]= $record['id'];
						}
						//echo '<pre>';print_r($pid_arr);die;
						rewind( $fd );
						$zip->addFromString( $tbl_name . '.csv', stream_get_contents( $fd ) );

						$pd = fopen( 'php://temp/maxmemory:1048576', 'w' );
						if ( false === $fd ) {
							die( 'Failed to create temporary file' );
						}
						fputcsv( $pd, $item_columns );
						foreach ($pid_arr as $pid):
						$wc['sale_id'] = $pid;
						$products = $this->storesync_model->getTableData( 'sale_items', $item_columns, $wc);
						
						if ( $products ):
						// write the data to csv
						foreach ( $products as $product ) {
							fputcsv( $pd, $product );
						}
						endif;
						endforeach;
							rewind( $pd );
							$zip->addFromString( 'purchase_items.csv', stream_get_contents( $pd ) );
						endif;
						$zip->close();
					if($flg) {

						header( 'Content-Type: application/zip' );
						header( 'Content-disposition: attachment; filename=' . $zipname );
						header( 'Content-Length: ' . filesize( 'store_update_files/' . $zipname ) );
						readfile( 'store_update_files/' . $zipname );
					}else
					{
						$this->session->set_flashdata('error', 'No any updates found');
					}

				} else {
				echo 'failed';
				}
				$this->db->update('update_status', array('downloaded_at' => date('Y-m-d H:i:s'), 'download_by' => $this->session->userdata('user_id')),array('id' => 2));
                }
                
            }elseif($this->input->post('form_action') == 'reset'){
                	$this->download_store_sales();
                } else {
                $this->session->set_flashdata('error', lang("no_sale_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    //For download internal purchase order
    function download_internal_purchase_order()
	{

		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Download Internal Purchase')));
		$meta = array('page_title' => lang('Download Internal Purchase'), 'bc' => $bc);
		$this->page_construct('storesync/download_internal_purchase', $meta, $this->data);

	}

	public function getInternalPurchaseOfbranch($branch_code = null)
    {
    	$branch_code= $this->Settings->branch_code;
	    $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
	    $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        $this->sma->checkPermissions('index');
        $this->load->library('datatables');
        if ($branch_code) {
            $this->datatables
                ->select("{$this->db->dbprefix('internal_purchases')}.id as id, DATE_FORMAT({$this->db->dbprefix('internal_purchases')}.date, '%Y-%m-%d %T') as date, reference_no,status,brand")
                ->from('internal_purchases')
                /*->join('internal_branches', "internal_branches.id={$this->db->dbprefix('internal_purchases')}.branch_id", 'left')*/
                ->where('status !=','completed')
                ->where('branch_code', $branch_code);
	        if ($start_date and $end_date) {
		        $start_date = $this->sma->fld( $start_date );
		        $end_date   =  $this->sma->fld( $end_date );
		        $this->datatables->where($this->db->dbprefix('internal_purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
	        }
		        $this->datatables->group_by("{$this->db->dbprefix('internal_purchases')}.id");
        }
        //$this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function internal_purchase_sync_action()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'export_zip') {
                	unset($_SESSION['error']);
					$tbl_columns = array(
						'id',
						'reference_no',
						'date',
						//'brand_id',
						//'brand',
						'branch_code',
						'note',
						'total',
						'product_discount',
						'order_discount_id',
						'order_discount',
						'total_discount',
						'product_tax',
						'order_tax_id',
						'order_tax',
						'total_tax',
						'shipping',
						'grand_total',
						'paid',
						'status',
						'payment_status',
						'due_date'
					);
					$tbl_name    = 'internal_purchases';
					$item_columns = array(
						'purchase_id',
						'product_id',
						'product_code',
						'product_name',
						'option_id',
						'net_unit_cost',
						'unit_cost',
						'quantity',
						'branch_code',
						'item_tax',
						'tax_rate_id',
						'tax',
						'discount',
						'item_discount',
						'subtotal',
						'real_unit_cost',
						'product_unit_id',
						'product_unit_code',
						'unit_quantity',
						'cgst_rate',
						'sgst_rate',
						'gst_code',
						'igst_rate',
						'cgst_amt',
						'sgst_amt',
						'igst_amt',
						'cess_rate',
						'cess_amt'
					);

					$store_code = $this->Settings->branch_code;

					$zipname = $store_code.'_purchaserequisition_' . date( 'Y_m_d_H_i_s' ) . '.zip';
					$zip     = new ZipArchive;
						$res     = $zip->open( 'store_update_files/' . $zipname, ZipArchive::CREATE );
					$flg = false;
					if ( $res===true) {
						foreach ($_POST['val'] as $id) {
                       $cond['id'][] = $id;
                   	}
				
					$records = $this->storesync_model->getTableData( 'internal_purchases', $tbl_columns, $cond);
					$headers = $tbl_columns;

					if ( $records ):
						$flg = true;
						$fd = fopen( 'php://temp/maxmemory:1048576', 'w' );
						if ( false === $fd ) {
							die( 'Failed to create temporary file' );
						}
						$pid_arr = array();
						// write the data to csv
						fputcsv( $fd, $headers );
						foreach ( $records as $record ) {
							fputcsv( $fd, $record );
							$pid_arr[]= $record['id'];
						}
						//echo '<pre>';print_r($pid_arr);die;
						rewind( $fd );
						$zip->addFromString( $tbl_name . '.csv', stream_get_contents( $fd ) );

						$pd = fopen( 'php://temp/maxmemory:1048576', 'w' );
						if ( false === $fd ) {
							die( 'Failed to create temporary file' );
						}
						fputcsv( $pd, $item_columns );
						foreach ($pid_arr as $pid):
						$wc['purchase_id'] = $pid;
						$products = $this->storesync_model->getTableData( 'internal_purchase_items', $item_columns, $wc);
						
						if ( $products ):
						// write the data to csv
						foreach ( $products as $product ) {
							fputcsv( $pd, $product );
						}
						endif;
						endforeach;
							rewind( $pd );
							$zip->addFromString( 'internal_purchase_items.csv', stream_get_contents( $pd ) );
						endif;
						$zip->close();
					if($flg) {

						header( 'Content-Type: application/zip' );
						header( 'Content-disposition: attachment; filename=' . $zipname );
						header( 'Content-Length: ' . filesize( 'store_update_files/' . $zipname ) );
						readfile( 'store_update_files/' . $zipname );
					}else
					{
						$this->session->set_flashdata('error', 'No any updates found');
					}

				} else {
				echo 'failed';
				}
				$this->db->update('update_status', array('downloaded_at' => date('Y-m-d H:i:s'), 'download_by' => $this->session->userdata('user_id')),array('id' => 2));
                }
                
            }elseif($this->input->post('form_action') == 'reset'){
                	$this->download_store_sales();
                } else {
                $this->session->set_flashdata('error', lang("no_internal_purchase_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }



}
