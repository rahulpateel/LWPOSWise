<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }

        $this->lang->admin_load('reports', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('reports_model');
        $this->data['pb'] = array(
            'cash' => lang('cash'),
            'CC' => lang('CC'),
            'Cheque' => lang('Cheque'),
            'paypal_pro' => lang('paypal_pro'),
            'stripe' => lang('stripe'),
            'gift_card' => lang('gift_card'),
            'deposit' => lang('deposit'),
            'authorize' => lang('authorize'),
        );

    }

    function index()
    {
        $this->sma->checkPermissions();
        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['monthly_sales'] = $this->reports_model->getChartData();
        $this->data['stock'] = $this->reports_model->getStockValueMod();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('reports')));
        $meta = array('page_title' => lang('reports'), 'bc' => $bc);
        $this->page_construct('reports/index', $meta, $this->data);

    }

    function warehouse_stock($warehouse = NULL)
    {
        $this->sma->checkPermissions('index', TRUE);
        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        }

        $this->data['stock'] = $warehouse ? $this->reports_model->getWarehouseStockValueMod($warehouse) : $this->reports_model->getStockValueMod();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse;
        $this->data['warehouse'] = $warehouse ? $this->site->getWarehouseByID($warehouse) : NULL;
        $this->data['totals'] = $this->reports_model->getWarehouseTotals($warehouse);
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('reports')));
        $meta = array('page_title' => lang('reports'), 'bc' => $bc);
        $this->page_construct('reports/warehouse_stock', $meta, $this->data);

    }

    function expiry_alerts($warehouse_id = NULL)
    {
        $this->sma->checkPermissions('expiry_alerts');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $user = $this->site->getUser();
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $user->warehouse_id;
            $this->data['warehouse'] = $user->warehouse_id ? $this->site->getWarehouseByID($user->warehouse_id) : NULL;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('product_expiry_alerts')));
        $meta = array('page_title' => lang('product_expiry_alerts'), 'bc' => $bc);
        $this->page_construct('reports/expiry_alerts', $meta, $this->data);
    }

    function getExpiryAlerts($warehouse_id = NULL)
    {
        $this->sma->checkPermissions('expiry_alerts', TRUE);
        $date = date('Y-m-d', strtotime('+3 months'));

        if (!$this->Owner && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("image, product_code, product_name, quantity_balance, warehouses.name, expiry")
                ->from('purchase_items')
                ->join('products', 'products.id=purchase_items.product_id', 'left')
                ->join('warehouses', 'warehouses.id=purchase_items.warehouse_id', 'left')
                ->where('warehouse_id', $warehouse_id)
                ->where('expiry !=', NULL)->where('expiry !=', '0000-00-00')
                ->where('expiry <', $date);
        } else {
            $this->datatables
                ->select("image, product_code, product_name, quantity_balance, warehouses.name, expiry")
                ->from('purchase_items')
                ->join('products', 'products.id=purchase_items.product_id', 'left')
                ->join('warehouses', 'warehouses.id=purchase_items.warehouse_id', 'left')
                ->where('expiry !=', NULL)->where('expiry !=', '0000-00-00')
                ->where('expiry <', $date);
        }
        echo $this->datatables->generate();
    }

    function quantity_alerts($warehouse_id = NULL)
    {
        $this->sma->checkPermissions('quantity_alerts');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $user = $this->site->getUser();
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $user->warehouse_id;
            $this->data['warehouse'] = $user->warehouse_id ? $this->site->getWarehouseByID($user->warehouse_id) : NULL;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('product_quantity_alerts')));
        $meta = array('page_title' => lang('product_quantity_alerts'), 'bc' => $bc);
        $this->page_construct('reports/quantity_alerts', $meta, $this->data);
    }

    function getQuantityAlerts($warehouse_id = NULL, $pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('quantity_alerts', TRUE);
        if (!$this->Owner && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        if ($pdf || $xls) {

            if ($warehouse_id) {
                $this->db
                    ->select('products.code, products.name,brands.name as brand,units.name as unit, warehouses_products.quantity, alert_quantity')
                    ->from('products')->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
	                ->join("brands",'brands.id=products.brand', 'left')
	                ->join("units",'units.id=products.unit', 'left')
                    ->where('alert_quantity > warehouses_products.quantity', NULL)
                    ->where('warehouse_id', $warehouse_id)
                    ->where('track_quantity', 1)
                    ->order_by('products.code desc');
            } else {
                $this->db
                    ->select('products.code, products.name,brands.name as brand,units.name as unit, quantity, alert_quantity')
                    ->from('products')
	                ->join("brands",'brands.id=products.brand', 'left')
	                ->join("units",'units.id=products.unit', 'left')
                    ->where('alert_quantity > quantity', NULL)
                    ->where('track_quantity', 1)
                    ->order_by('products.code desc');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('product_quantity_alerts'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('brand'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('unit'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('quantity'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('alert_quantity'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->brand);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->unit);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->quantity);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->alert_quantity);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $filename = 'product_quantity_alerts';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            if ($warehouse_id) {
                $this->datatables
                  //  ->select('code, name, wp.quantity, alert_quantity')
	                ->select('products.code, products.name,brands.name as brand,units.name as unit, wp.quantity, alert_quantity')
                    ->from('products')
                    ->join("( SELECT * from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) wp", 'products.id=wp.product_id', 'left')
	                ->join("brands",'brands.id=products.brand', 'left')
	                ->join("units",'units.id=products.unit', 'left')
                    ->where('alert_quantity > wp.quantity', NULL)
                    ->or_where('wp.quantity', NULL)
                    ->where('track_quantity', 1)
                    ->group_by('products.id');
            } else {
                $this->datatables
                    ->select('products.code, products.name,brands.name as brand,units.name as unit, quantity, alert_quantity')
                    ->from('products')
	                ->join("brands",'brands.id=products.brand', 'left')
	                ->join("units",'units.id=products.unit', 'left')
                    ->where('alert_quantity > quantity', NULL)
                    ->where('track_quantity', 1);
            }

            echo $this->datatables->generate();

        }

    }

    function suggestions()
    {
        $term = $this->input->get('term', TRUE);
        if (strlen($term) < 1) {
            die();
        }

        $rows = $this->reports_model->getProductNames($term);
        if ($rows) {
            foreach ($rows as $row) {
                $pr[] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")");

            }
            $this->sma->send_json($pr);
        } else {
            echo FALSE;
        }
    }

    public function best_sellers($warehouse_id = NULL)
    {
        $this->sma->checkPermissions('products');

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $y1 = date('Y', strtotime('-1 month'));
        $m1 = date('m', strtotime('-1 month'));
        $m1sdate = $y1.'-'.$m1.'-01 00:00:00';
        $m1edate = $y1.'-'.$m1.'-'. days_in_month($m1, $y1) . ' 23:59:59';
        $this->data['m1'] = date('M Y', strtotime($y1.'-'.$m1));
        $this->data['m1bs'] = $this->reports_model->getBestSeller($m1sdate, $m1edate, $warehouse_id);
        $y2 = date('Y', strtotime('-2 months'));
        $m2 = date('m', strtotime('-2 months'));
        $m2sdate = $y2.'-'.$m2.'-01 00:00:00';
        $m2edate = $y2.'-'.$m2.'-'. days_in_month($m2, $y2) . ' 23:59:59';
        $this->data['m2'] = date('M Y', strtotime($y2.'-'.$m2));
        $this->data['m2bs'] = $this->reports_model->getBestSeller($m2sdate, $m2edate, $warehouse_id);
        $y3 = date('Y', strtotime('-3 months'));
        $m3 = date('m', strtotime('-3 months'));
        $m3sdate = $y3.'-'.$m3.'-01 23:59:59';
        $this->data['m3'] = date('M Y', strtotime($y3.'-'.$m3)).' - '.$this->data['m1'];
        $this->data['m3bs'] = $this->reports_model->getBestSeller($m3sdate, $m1edate, $warehouse_id);
        $y4 = date('Y', strtotime('-12 months'));
        $m4 = date('m', strtotime('-12 months'));
        $m4sdate = $y4.'-'.$m4.'-01 23:59:59';
        $this->data['m4'] = date('M Y', strtotime($y4.'-'.$m4)).' - '.$this->data['m1'];
        $this->data['m4bs'] = $this->reports_model->getBestSeller($m4sdate, $m1edate, $warehouse_id);
        // $this->sma->print_arrays($this->data['m1bs'], $this->data['m2bs'], $this->data['m3bs'], $this->data['m4bs']);
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('best_sellers')));
        $meta = array('page_title' => lang('best_sellers'), 'bc' => $bc);
        $this->page_construct('reports/best_sellers', $meta, $this->data);

    }

    function products()
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['brands'] = $this->site->getAllBrands();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('products_report')));
        $meta = array('page_title' => lang('products_report'), 'bc' => $bc);
        $this->page_construct('reports/products', $meta, $this->data);
    }

    function getProductsReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);

        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $brand = $this->input->get('brand') ? $this->input->get('brand') : NULL;
        $subcategory = $this->input->get('subcategory') ? $this->input->get('subcategory') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $cf1 = $this->input->get('cf1') ? $this->input->get('cf1') : NULL;
        $cf2 = $this->input->get('cf2') ? $this->input->get('cf2') : NULL;
        $cf3 = $this->input->get('cf3') ? $this->input->get('cf3') : NULL;
        $cf4 = $this->input->get('cf4') ? $this->input->get('cf4') : NULL;
        $cf5 = $this->input->get('cf5') ? $this->input->get('cf5') : NULL;
        $cf6 = $this->input->get('cf6') ? $this->input->get('cf6') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        $pp = "( SELECT product_id, 
            SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN quantity ELSE 0 END) as purchasedQty,
             SUM(CASE WHEN pi.purchase_id IS  NULL THEN COALESCE(quantity_balance,0) ELSE 0 END) as adjQty,
COALESCE((SUM(pi.quantity)),0 ) as balacneQty,

		( unit_cost ) balacneValue, SUM( (CASE WHEN pi.purchase_id IS NOT NULL THEN (pi.subtotal) ELSE 0 END) ) totalPurchase from {$this->db->dbprefix('purchase_items')} pi inner JOIN {$this->db->dbprefix('purchases')} p on p.id = pi.purchase_id ";
        $sp = "( SELECT si.product_id, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from " . $this->db->dbprefix('sales') . " s JOIN " . $this->db->dbprefix('sale_items') . " si on s.id = si.sale_id ";
        if ($start_date || $warehouse) {
            $pp .= " WHERE ";
            $sp .= " WHERE ";
            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                $pp .= " p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
                if ($warehouse) {
                    $pp .= " AND ";
                    $sp .= " AND ";
                }
            }
            if ($warehouse) {
                $pp .= " pi.warehouse_id = '{$warehouse}' ";
                $sp .= " si.warehouse_id = '{$warehouse}' ";
            }
        }
        $pp .= " GROUP BY pi.product_id ) PCosts";
        $sp .= " GROUP BY si.product_id ) PSales";
        if ($pdf || $xls) {

            $this->db
                ->select($this->db->dbprefix('products') . ".code, " . $this->db->dbprefix('products') . ".name,
				COALESCE( PCosts.purchasedQty, 0 ) as PurchasedQty,
				COALESCE( PSales.soldQty, 0 ) as SoldQty,
				COALESCE( PCosts.balacneQty-PSales.soldQty, 0 ) as BalacneQty,
				COALESCE( PCosts.totalPurchase, 0 ) as TotalPurchase,
				COALESCE( (PCosts.balacneQty-PSales.soldQty)* PCosts.balacneValue, 0 ) as TotalBalance,
				COALESCE( PSales.totalSale, 0 ) as TotalSales,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )) as Profit", FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
                ->order_by('products.name');

            if ($product) {
                $this->db->where($this->db->dbprefix('products') . ".id", $product);
            }
            if ($cf1) {
                $this->db->where($this->db->dbprefix('products') . ".cf1", $cf1);
            }
            if ($cf2) {
                $this->db->where($this->db->dbprefix('products') . ".cf2", $cf2);
            }
            if ($cf3) {
                $this->db->where($this->db->dbprefix('products') . ".cf3", $cf3);
            }
            if ($cf4) {
                $this->db->where($this->db->dbprefix('products') . ".cf4", $cf4);
            }
            if ($cf5) {
                $this->db->where($this->db->dbprefix('products') . ".cf5", $cf5);
            }
            if ($cf6) {
                $this->db->where($this->db->dbprefix('products') . ".cf6", $cf6);
            }
            if ($category) {
                $this->db->where($this->db->dbprefix('products') . ".category_id", $category);
            }
            if ($subcategory) {
                $this->db->where($this->db->dbprefix('products') . ".subcategory_id", $subcategory);
            }
            if ($brand) {
                $this->db->where($this->db->dbprefix('products') . ".brand", $brand);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('products_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('purchased'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('sold'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('purchased_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('sold_amount'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('profit_loss'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('stock_in_hand'));

                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $sAmt = 0;
                $pAmt = 0;
                $bQty = 0;
                $bAmt = 0;
                $pl = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->PurchasedQty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->SoldQty);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->BalacneQty);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->TotalPurchase);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->TotalSales);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->Profit);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->TotalBalance);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $bQty += $data_row->BalacneQty;
                    $pAmt += $data_row->TotalPurchase;
                    $sAmt += $data_row->TotalSales;
                    $bAmt += $data_row->TotalBalance;
                    $pl += $data_row->Profit;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("C" . $row . ":I" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $bQty);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $pAmt);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sAmt);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $pl);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $bAmt);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                $filename = 'products_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('products') . ".code, " . $this->db->dbprefix('products') . ".name,
				CONCAT(COALESCE( PCosts.purchasedQty, 0 ), '__', COALESCE( PCosts.totalPurchase, 0 )) as purchased,
				CONCAT(COALESCE( PSales.soldQty, 0 ), '__', COALESCE( PSales.totalSale, 0 )) as sold,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )) as Profit,
				
				CONCAT(COALESCE( COALESCE(PCosts.balacneQty,0)-COALESCE(PSales.soldQty,0), 0 ), '__', COALESCE( (PCosts.balacneQty-PSales.soldQty)* PCosts.balacneValue, 0 )) as balance,

                CONCAT(COALESCE( COALESCE(PCosts.balacneQty,0)
                + COALESCE(PCosts.adjQty,0)
                -COALESCE(PSales.soldQty,0), 0 ), '__', COALESCE( (PCosts.balacneQty+COALESCE(PCosts.adjQty,0)-PSales.soldQty)* PCosts.balacneValue, 0 )) as balance1,
				
				{$this->db->dbprefix('products')}.id as id", FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
                ->group_by('products.code, PSales.soldQty, PSales.totalSale, PCosts.purchasedQty, PCosts.totalPurchase, PCosts.balacneQty, PCosts.balacneValue');

            if ($product) {
                $this->datatables->where($this->db->dbprefix('products') . ".id", $product);
            }
            if ($cf1) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf1", $cf1);
            }
            if ($cf2) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf2", $cf2);
            }
            if ($cf3) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf3", $cf3);
            }
            if ($cf4) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf4", $cf4);
            }
            if ($cf5) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf5", $cf5);
            }
            if ($cf6) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf6", $cf6);
            }
            if ($category) {
                $this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
            }
            if ($subcategory) {
                $this->datatables->where($this->db->dbprefix('products') . ".subcategory_id", $subcategory);
            }
            if ($brand) {
                $this->datatables->where($this->db->dbprefix('products') . ".brand", $brand);
            }

            echo $this->datatables->generate();

        }

    }

    function categories()
    {
        $this->sma->checkPermissions('products', TRUE);
        $warehouse = $this->input->post('warehouse') ? $this->input->post('warehouse') : NULL;
        $category = $this->input->post('category') ? $this->input->post('category') : NULL;
        $start_date = $this->input->post('start_date') ? $this->input->post('start_date') : NULL;
        $end_date = $this->input->post('end_date') ? $this->input->post('end_date') : NULL;
       //   echo "string ".$warehouse;die();
          $pp = "( SELECT pp.category_id as category, SUM( pi.quantity ) purchasedQty, SUM( pi.subtotal ) totalPurchase from {$this->db->dbprefix('products')} pp
                left JOIN " . $this->db->dbprefix('purchase_items') . " pi ON pp.id = pi.product_id
                left join " . $this->db->dbprefix('purchases') . " p ON p.id = pi.purchase_id ";
        $sp = "( SELECT sp.category_id as category, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from {$this->db->dbprefix('products')} sp
                left JOIN " . $this->db->dbprefix('sale_items') . " si ON sp.id = si.product_id
                left join " . $this->db->dbprefix('sales') . " s ON s.id = si.sale_id ";
        if ($start_date || $warehouse) {
            $pp .= " WHERE ";
            $sp .= " WHERE ";
            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                $pp .= " p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
                if ($warehouse) {
                    $pp .= " AND ";
                    $sp .= " AND ";
                }
            }
            if ($warehouse) {
                $pp .= " pi.warehouse_id = '{$warehouse}' ";
                $sp .= " si.warehouse_id = '{$warehouse}' ";
            }
            
        }
        $pp .= " GROUP BY pp.category_id ) PCosts";
        $sp .= " GROUP BY sp.category_id ) PSales";
        $this->db
                ->select($this->db->dbprefix('categories') . ".code, " . $this->db->dbprefix('categories') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", FALSE)
                ->from('categories')
                ->join($sp, 'categories.id = PSales.category', 'left')
                ->join($pp, 'categories.id = PCosts.category', 'left')
                ->group_by('categories.id, categories.code, categories.name')
                ->order_by('categories.code', 'asc');

            if ($category) {
                $this->db->where('categories.id', $category);
            }
         $data1=array();
                      $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data1[] = $row;
                }
            } else {
                $data1 = NULL;
            }
          //  echo "".json_encode($data);
        $this->sma->checkPermissions('products');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['records'] = $data1;
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('categories_report')));
        $meta = array('page_title' => lang('categories_report'), 'bc' => $bc);
       
        $this->page_construct('reports/categories', $meta, $this->data);
    }

    function getCategoriesReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        $pp = "( SELECT pp.category_id as category, SUM( pi.quantity ) purchasedQty, SUM( pi.subtotal ) totalPurchase from {$this->db->dbprefix('products')} pp
                left JOIN " . $this->db->dbprefix('purchase_items') . " pi ON pp.id = pi.product_id
                left join " . $this->db->dbprefix('purchases') . " p ON p.id = pi.purchase_id ";
        $sp = "( SELECT sp.category_id as category, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from {$this->db->dbprefix('products')} sp
                left JOIN " . $this->db->dbprefix('sale_items') . " si ON sp.id = si.product_id
                left join " . $this->db->dbprefix('sales') . " s ON s.id = si.sale_id ";
        if ($start_date || $warehouse) {
            $pp .= " WHERE ";
            $sp .= " WHERE ";
            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                $pp .= " p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
                if ($warehouse) {
                    $pp .= " AND ";
                    $sp .= " AND ";
                }
            }
            if ($warehouse) {
                $pp .= " pi.warehouse_id = '{$warehouse}' ";
                $sp .= " si.warehouse_id = '{$warehouse}' ";
            }
        }
        $pp .= " GROUP BY pp.category_id ) PCosts";
        $sp .= " GROUP BY sp.category_id ) PSales";

        if ($pdf || $xls) {

            $this->db
                ->select($this->db->dbprefix('categories') . ".code, " . $this->db->dbprefix('categories') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", FALSE)
                ->from('categories')
                ->join($sp, 'categories.id = PSales.category', 'left')
                ->join($pp, 'categories.id = PCosts.category', 'left')
                ->group_by('categories.id, categories.code, categories.name')
                ->order_by('categories.code', 'asc');

            if ($category) {
                $this->db->where($this->db->dbprefix('categories') . ".id", $category);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('categories_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('category_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('category_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('purchased'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('sold'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('purchased_amount'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('sold_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('profit_loss'));

                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $sAmt = 0;
                $pAmt = 0;
                $pl = 0;
                foreach ($data as $data_row) {
                    $profit = $data_row->TotalSales - $data_row->TotalPurchase;
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->PurchasedQty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->SoldQty);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->TotalPurchase);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->TotalSales);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $profit);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $pAmt += $data_row->TotalPurchase;
                    $sAmt += $data_row->TotalSales;
                    $pl += $profit;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("C" . $row . ":G" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $pAmt);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $sAmt);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $pl);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                $filename = 'categories_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {


            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('categories') . ".id as cid, " .$this->db->dbprefix('categories') . ".code, " . $this->db->dbprefix('categories') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", FALSE)
                ->from('categories')
                ->join($sp, 'categories.id = PSales.category', 'left')
                ->join($pp, 'categories.id = PCosts.category', 'left');

            if ($category) {
                $this->datatables->where('categories.id', $category);
            }
            $this->datatables->group_by('categories.id, categories.code, categories.name, PSales.SoldQty, PSales.totalSale, PCosts.purchasedQty, PCosts.totalPurchase');
            $this->datatables->unset_column('cid');
            echo $this->datatables->generate();

        }

    }

    function brands()
    {
        $this->sma->checkPermissions('products');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['brands'] = $this->site->getAllBrands();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('brands_report')));
        $meta = array('page_title' => lang('brands_report'), 'bc' => $bc);
        $this->page_construct('reports/brands', $meta, $this->data);
    }

    function getBrandsReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $brand = $this->input->get('brand') ? $this->input->get('brand') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        $pp = "( SELECT pp.brand as brand, SUM( pi.quantity ) purchasedQty, SUM( pi.subtotal ) totalPurchase from {$this->db->dbprefix('products')} pp
                left JOIN " . $this->db->dbprefix('purchase_items') . " pi ON pp.id = pi.product_id
                left join " . $this->db->dbprefix('purchases') . " p ON p.id = pi.purchase_id ";
        $sp = "( SELECT sp.brand as brand, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from {$this->db->dbprefix('products')} sp
                left JOIN " . $this->db->dbprefix('sale_items') . " si ON sp.id = si.product_id
                left join " . $this->db->dbprefix('sales') . " s ON s.id = si.sale_id ";
        if ($start_date || $warehouse) {
            $pp .= " WHERE ";
            $sp .= " WHERE ";
            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                $pp .= " p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
                if ($warehouse) {
                    $pp .= " AND ";
                    $sp .= " AND ";
                }
            }
            if ($warehouse) {
                $pp .= " pi.warehouse_id = '{$warehouse}' ";
                $sp .= " si.warehouse_id = '{$warehouse}' ";
            }
        }
        $pp .= " GROUP BY pp.brand ) PCosts";
        $sp .= " GROUP BY sp.brand ) PSales";

        if ($pdf || $xls) {

            $this->db
                ->select($this->db->dbprefix('brands') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", FALSE)
                ->from('brands')
                ->join($sp, 'brands.id = PSales.brand', 'left')
                ->join($pp, 'brands.id = PCosts.brand', 'left')
                ->group_by('brands.id, brands.name')
                ->order_by('brands.code', 'asc');

            if ($brand) {
                $this->db->where($this->db->dbprefix('brands') . ".id", $brand);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('brands_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('brands'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('purchased'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('sold'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('purchased_amount'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('sold_amount'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('profit_loss'));

                $row = 2; $sQty = 0; $pQty = 0; $sAmt = 0; $pAmt = 0; $pl = 0;
                foreach ($data as $data_row) {
                    $profit = $data_row->TotalSales - $data_row->TotalPurchase;
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->PurchasedQty);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->SoldQty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->TotalPurchase);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->TotalSales);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $profit);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $pAmt += $data_row->TotalPurchase;
                    $sAmt += $data_row->TotalSales;
                    $pl += $profit;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("B" . $row . ":F" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $pAmt);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sAmt);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $pl);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                $filename = 'brands_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {


            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('brands') . ".id as id, " . $this->db->dbprefix('brands') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", FALSE)
                ->from('brands')
                ->join($sp, 'brands.id = PSales.brand', 'left')
                ->join($pp, 'brands.id = PCosts.brand', 'left');

            if ($brand) {
                $this->datatables->where('brands.id', $brand);
            }
            $this->datatables->group_by('brands.id, brands.name, PSales.SoldQty, PSales.totalSale, PCosts.purchasedQty, PCosts.totalPurchase');
            $this->datatables->unset_column('id');
            echo $this->datatables->generate();

        }

    }

    function profit($date = NULL, $warehouse_id = NULL, $re = NULL)
    {
        if ( ! $this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->sma->md();
        }
        if ( ! $date) { $date = date('Y-m-d'); }
        $this->data['costing'] = $this->reports_model->getSales($date, $warehouse_id);
        $this->data['discount'] = $this->reports_model->getOrderDiscount($date, $warehouse_id);
        $this->data['expenses'] = $this->reports_model->getExpenses($date, $warehouse_id);
        $this->data['returns'] = $this->reports_model->getReturns($date, $warehouse_id);
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['swh'] = $warehouse_id;
        $this->data['date'] = $date;
        if ($re) {
            echo $this->load->view($this->theme . 'reports/profit', $this->data, TRUE);
            exit();
        }
        $this->load->view($this->theme . 'reports/profit', $this->data);
    }
    function monthly_profit($year, $month, $warehouse_id = NULL, $re = NULL)
    {
        if ( ! $this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->sma->md();
        }

        $this->data['costing'] = $this->reports_model->getSales(NULL, $warehouse_id, $year, $month);
        $this->data['discount'] = $this->reports_model->getOrderDiscount(NULL, $warehouse_id, $year, $month);
        $this->data['expenses'] = $this->reports_model->getExpenses(NULL, $warehouse_id, $year, $month);
        $this->data['returns'] = $this->reports_model->getReturns(NULL, $warehouse_id, $year, $month);
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['swh'] = $warehouse_id;
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        $this->data['date'] = date('F Y', strtotime($year.'-'.$month.'-'.'01'));
        if ($re) {
            echo $this->load->view($this->theme . 'reports/monthly_profit', $this->data, TRUE);
            exit();
        }
        $this->load->view($this->theme . 'reports/monthly_profit', $this->data);
    }

    function daily_sales($warehouse_id = NULL, $year = NULL, $month = NULL, $pdf = NULL, $user_id = NULL)
    {
        $this->sma->checkPermissions();
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        if (!$year) {
            $year = date('Y');
        }
        if (!$month) {
            $month = date('m');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $config = array(
            'show_next_prev' => TRUE,
            'next_prev_url' => admin_url('reports/daily_sales/'.($warehouse_id ? $warehouse_id : 0)),
            'month_type' => 'long',
            'day_type' => 'long'
        );

        $config['template'] = '{table_open}<div class="table-responsive"><table border="0" cellpadding="0" cellspacing="0" class="table table-bordered dfTable">{/table_open}
		{heading_row_start}<tr>{/heading_row_start}
		{heading_previous_cell}<th><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
		{heading_title_cell}<th colspan="{colspan}" id="month_year">{heading}</th>{/heading_title_cell}
		{heading_next_cell}<th><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}
		{heading_row_end}</tr>{/heading_row_end}
		{week_row_start}<tr>{/week_row_start}
		{week_day_cell}<td class="cl_wday">{week_day}</td>{/week_day_cell}
		{week_row_end}</tr>{/week_row_end}
		{cal_row_start}<tr class="days">{/cal_row_start}
		{cal_cell_start}<td class="day">{/cal_cell_start}
		{cal_cell_content}
		<div class="day_num">{day}</div>
		<div class="content">{content}</div>
		{/cal_cell_content}
		{cal_cell_content_today}
		<div class="day_num highlight">{day}</div>
		<div class="content">{content}</div>
		{/cal_cell_content_today}
		{cal_cell_no_content}<div class="day_num">{day}</div>{/cal_cell_no_content}
		{cal_cell_no_content_today}<div class="day_num highlight">{day}</div>{/cal_cell_no_content_today}
		{cal_cell_blank}&nbsp;{/cal_cell_blank}
		{cal_cell_end}</td>{/cal_cell_end}
		{cal_row_end}</tr>{/cal_row_end}
		{table_close}</table></div>{/table_close}';

        $this->load->library('calendar', $config);
        $sales = $user_id ? $this->reports_model->getStaffDailySales($user_id, $year, $month, $warehouse_id) : $this->reports_model->getDailySales($year, $month, $warehouse_id);

        if (!empty($sales)) {
            foreach ($sales as $sale) {
                $daily_sale[$sale->date] = "<table class='table table-bordered table-hover table-striped table-condensed data' style='margin:0;'><tr><td>" . lang("discount") . "</td><td>" . $this->sma->formatMoney($sale->discount) . "</td></tr><tr><td>" . lang("shipping") . "</td><td>" . $this->sma->formatMoney($sale->shipping) . "</td></tr><tr><td>" . lang("product_tax") . "</td><td>" . $this->sma->formatMoney($sale->tax1) . "</td></tr><tr><td>" . lang("order_tax") . "</td><td>" . $this->sma->formatMoney($sale->tax2) . "</td></tr><tr><td>" . lang("total") . "</td><td>" . $this->sma->formatMoney($sale->total) . "</td></tr></table>";
            }
        } else {
            $daily_sale = array();
        }

        $this->data['calender'] = $this->calendar->generate($year, $month, $daily_sale);
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/daily', $this->data, true);
            $name = lang("daily_sales") . "_" . $year . "_" . $month . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_calendar_text") . '</p>', '', $html);
            $this->sma->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['sel_warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('daily_sales_report')));
        $meta = array('page_title' => lang('daily_sales_report'), 'bc' => $bc);
        $this->page_construct('reports/daily', $meta, $this->data);

    }


    function monthly_sales($warehouse_id = NULL, $year = NULL, $pdf = NULL, $user_id = NULL)
    {
        $this->sma->checkPermissions();
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        if (!$year) {
            $year = date('Y');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->load->language('calendar');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['year'] = $year;
        $this->data['sales'] = $user_id ? $this->reports_model->getStaffMonthlySales($user_id, $year, $warehouse_id) : $this->reports_model->getMonthlySales($year, $warehouse_id);
        if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/monthly', $this->data, true);
            $name = lang("monthly_sales") . "_" . $year . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_calendar_text") . '</p>', '', $html);
            $this->sma->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['sel_warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('monthly_sales_report')));
        $meta = array('page_title' => lang('monthly_sales_report'), 'bc' => $bc);
        $this->page_construct('reports/monthly', $meta, $this->data);

    }

    function sales()
    {
        $this->sma->checkPermissions('sales');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('sales_report')));
        $meta = array('page_title' => lang('sales_report'), 'bc' => $bc);
        $this->page_construct('reports/sales', $meta, $this->data);
    }

    function getSalesReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('sales', TRUE);
        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        $serial = $this->input->get('serial') ? $this->input->get('serial') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $this->db
                ->select("date, reference_no, biller, customer, GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('sale_items') . ".product_name, ' (', " . $this->db->dbprefix('sale_items') . ".quantity, ')') SEPARATOR '\n') as iname, grand_total, paid, payment_status", FALSE)
                ->from('sales')
                ->join('sale_items', 'sale_items.sale_id=sales.id', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
                ->group_by('sales.id')
                ->order_by('sales.date desc');

            if ($user) {
                $this->db->where('sales.created_by', $user);
            }
            if ($product) {
                $this->db->where('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->db->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($warehouse) {
                $this->db->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('sales_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('payment_status'));

                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, ($data_row->grand_total - $data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, lang($data_row->payment_status));
                    $total += $data_row->grand_total;
                    $paid += $data_row->paid;
                    $balance += ($data_row->grand_total - $data_row->paid);
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("F" . $row . ":H" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $paid);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $balance);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                $filename = 'sales_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $si = "( SELECT sale_id, product_id, serial_no, GROUP_CONCAT(CONCAT({$this->db->dbprefix('sale_items')}.product_name, '__', {$this->db->dbprefix('sale_items')}.quantity) SEPARATOR '___') as item_nane from {$this->db->dbprefix('sale_items')} ";
            if ($product) {
                $si .= " WHERE {$this->db->dbprefix('sale_items')}.product_id = {$product} ";
            }
            $si .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSI";
            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, biller, customer, FSI.item_nane as iname, grand_total, paid, (grand_total-paid) as balance, payment_status, {$this->db->dbprefix('sales')}.id as id", FALSE)
                ->from('sales')
                ->join($si, 'FSI.sale_id=sales.id', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left');
            // ->group_by('sales.id');

            if ($user) {
                $this->datatables->where('sales.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FSI.product_id', $product, FALSE);
            }
            if ($serial) {
                $this->datatables->like('FSI.serial_no', $serial, FALSE);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($warehouse) {
                $this->datatables->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }

    function getQuotesReport($pdf = NULL, $xls = NULL)
    {

        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('customer')) {
            $customer = $this->input->get('customer');
        } else {
            $customer = NULL;
        }
        if ($this->input->get('biller')) {
            $biller = $this->input->get('biller');
        } else {
            $biller = NULL;
        }
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = NULL;
        }
        if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if ($pdf || $xls) {

            $this->db
                ->select("date, reference_no, biller, customer, GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('quote_items') . ".product_name, ' (', " . $this->db->dbprefix('quote_items') . ".quantity, ')') SEPARATOR '<br>') as iname, grand_total, status", FALSE)
                ->from('quotes')
                ->join('quote_items', 'quote_items.quote_id=quotes.id', 'left')
                ->join('warehouses', 'warehouses.id=quotes.warehouse_id', 'left')
                ->group_by('quotes.id');

            if ($user) {
                $this->db->where('quotes.created_by', $user);
            }
            if ($product) {
                $this->db->where('quote_items.product_id', $product);
            }
            if ($biller) {
                $this->db->where('quotes.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('quotes.customer_id', $customer);
            }
            if ($warehouse) {
                $this->db->where('quotes.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('quotes.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('quotes').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('quotes_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->status);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                $filename = 'quotes_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $qi = "( SELECT quote_id, product_id, GROUP_CONCAT(CONCAT({$this->db->dbprefix('quote_items')}.product_name, '__', {$this->db->dbprefix('quote_items')}.quantity) SEPARATOR '___') as item_nane from {$this->db->dbprefix('quote_items')} ";
            if ($product) {
                $qi .= " WHERE {$this->db->dbprefix('quote_items')}.product_id = {$product} ";
            }
            $qi .= " GROUP BY {$this->db->dbprefix('quote_items')}.quote_id ) FQI";
            $this->load->library('datatables');
            $this->datatables
                ->select("date, reference_no, biller, customer, FQI.item_nane as iname, grand_total, status, {$this->db->dbprefix('quotes')}.id as id", FALSE)
                ->from('quotes')
                ->join($qi, 'FQI.quote_id=quotes.id', 'left')
                ->join('warehouses', 'warehouses.id=quotes.warehouse_id', 'left')
                ->group_by('quotes.id');

            if ($user) {
                $this->datatables->where('quotes.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FQI.product_id', $product, FALSE);
            }
            if ($biller) {
                $this->datatables->where('quotes.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('quotes.customer_id', $customer);
            }
            if ($warehouse) {
                $this->datatables->where('quotes.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('quotes.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('quotes').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }

    function getTransfersReport($pdf = NULL, $xls = NULL)
    {
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }

        if ($pdf || $xls) {

            $this->db
                ->select($this->db->dbprefix('transfers') . ".date, transfer_no, (CASE WHEN " . $this->db->dbprefix('transfers') . ".status = 'completed' THEN  GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('purchase_items') . ".product_name, ' (', " . $this->db->dbprefix('purchase_items') . ".quantity, ')') SEPARATOR '<br>') ELSE GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('transfer_items') . ".product_name, ' (', " . $this->db->dbprefix('transfer_items') . ".quantity, ')') SEPARATOR '<br>') END) as iname, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname,to_warehouse_code as tcode, grand_total, " . $this->db->dbprefix('transfers') . ".status")
                ->from('transfers')
                ->join('transfer_items', 'transfer_items.transfer_id=transfers.id', 'left')
                ->join('purchase_items', 'purchase_items.transfer_id=transfers.id', 'left')
                ->group_by('transfers.id')->order_by('transfers.date desc');
            if ($product) {
                $this->db->where($this->db->dbprefix('purchase_items') . ".product_id", $product);
                $this->db->or_where($this->db->dbprefix('transfer_items') . ".product_id", $product);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('transfers_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('transfer_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('warehouse') . ' (' . lang('from') . ')');
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('warehouse') . ' (' . lang('to') . ')');
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->transfer_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->fname . ' (' . $data_row->fcode . ')');
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->tname . ' (' . $data_row->tcode . ')');
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->status);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('C2:C' . $row)->getAlignment()->setWrapText(true);
                $filename = 'transfers_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select("{$this->db->dbprefix('transfers')}.date, transfer_no, (CASE WHEN {$this->db->dbprefix('transfers')}.status = 'completed' THEN  GROUP_CONCAT(CONCAT({$this->db->dbprefix('purchase_items')}.product_name, '__', {$this->db->dbprefix('purchase_items')}.quantity) SEPARATOR '___') ELSE GROUP_CONCAT(CONCAT({$this->db->dbprefix('transfer_items')}.product_name, '__', {$this->db->dbprefix('transfer_items')}.quantity) SEPARATOR '___') END) as iname, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname,to_warehouse_code as tcode, grand_total, {$this->db->dbprefix('transfers')}.status, {$this->db->dbprefix('transfers')}.id as id", FALSE)
                ->from('transfers')
                ->join('transfer_items', 'transfer_items.transfer_id=transfers.id', 'left')
                ->join('purchase_items', 'purchase_items.transfer_id=transfers.id', 'left')
                ->group_by('transfers.id');
            if ($product) {
                $this->datatables->where(" (({$this->db->dbprefix('purchase_items')}.product_id = {$product}) OR ({$this->db->dbprefix('transfer_items')}.product_id = {$product})) ", NULL, FALSE);
            }
            $this->datatables->edit_column("fname", "$1 ($2)", "fname, fcode")
                ->edit_column("tname", "$1 ($2)", "tname, tcode")
                ->unset_column('fcode')
                ->unset_column('tcode');
            echo $this->datatables->generate();

        }

    }

    function purchases()
    {
        $this->sma->checkPermissions('purchases');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('purchases_report')));
        $meta = array('page_title' => lang('purchases_report'), 'bc' => $bc);
        $this->page_construct('reports/purchases', $meta, $this->data);
    }

    function getPurchasesReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('purchases', TRUE);

        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $this->db
                ->select("" . $this->db->dbprefix('purchases') . ".date, reference_no, " . $this->db->dbprefix('warehouses') . ".name as wname, supplier, GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('purchase_items') . ".product_name, ' (', " . $this->db->dbprefix('purchase_items') . ".quantity, ')') SEPARATOR '\n') as iname,real_unit_cost, grand_total, paid, " . $this->db->dbprefix('purchases') . ".status", FALSE)
                ->from('purchases')
                ->join('purchase_items', 'purchase_items.purchase_id=purchases.id', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
                ->group_by('purchases.id')
                ->order_by('purchases.date desc');

            if ($user) {
                $this->db->where('purchases.created_by', $user);
            }
            if ($product) {
                $this->db->where('purchase_items.product_id', $product);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->db->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('purchase_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('supplier'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('status'));

                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->wname);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->supplier);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, ($data_row->grand_total - $data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->status);
                    $total += $data_row->grand_total;
                    $paid += $data_row->paid;
                    $balance += ($data_row->grand_total - $data_row->paid);
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("F" . $row . ":H" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $paid);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $balance);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                $filename = 'purchase_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $pi = "( SELECT purchase_id, product_id, (GROUP_CONCAT(CONCAT({$this->db->dbprefix('purchase_items')}.product_name, '__', {$this->db->dbprefix('purchase_items')}.quantity) SEPARATOR '___')) as item_name,(GROUP_CONCAT(CONCAT({$this->db->dbprefix('purchase_items')}.product_name, '__', {$this->db->dbprefix('purchase_items')}.real_unit_cost) SEPARATOR '___')) as real_unit_cost from {$this->db->dbprefix('purchase_items')} ";
            if ($product) {
                $pi .= " WHERE {$this->db->dbprefix('purchase_items')}.product_id = {$product} ";
            }
            $pi .= " GROUP BY {$this->db->dbprefix('purchase_items')}.purchase_id ) FPI";

            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%Y-%m-%d %T') as date, reference_no, {$this->db->dbprefix('warehouses')}.name as wname, supplier, (FPI.item_name) as iname,(FPI.real_unit_cost) as iprice, grand_total, paid, (grand_total-paid) as balance, {$this->db->dbprefix('purchases')}.status, {$this->db->dbprefix('purchases')}.id as id", FALSE)
                ->from('purchases')
                ->join($pi, 'FPI.purchase_id=purchases.id', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left');
            // ->group_by('purchases.id');

            if ($user) {
                $this->datatables->where('purchases.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FPI.product_id', $product, FALSE);
            }
            if ($supplier) {
                $this->datatables->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->datatables->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }

    function payments()
    {
        $this->sma->checkPermissions('payments');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['pos_settings'] = POS ? $this->reports_model->getPOSSetting('biller') : FALSE;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('payments_report')));
        $meta = array('page_title' => lang('payments_report'), 'bc' => $bc);
        $this->page_construct('reports/payments', $meta, $this->data);
    }

    function getPaymentsReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('payments', TRUE);

        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;
        $customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $payment_ref = $this->input->get('payment_ref') ? $this->input->get('payment_ref') : NULL;
        $paid_by = $this->input->get('paid_by') ? $this->input->get('paid_by') : NULL;
        $sale_ref = $this->input->get('sale_ref') ? $this->input->get('sale_ref') : NULL;
        $purchase_ref = $this->input->get('purchase_ref') ? $this->input->get('purchase_ref') : NULL;
        $card = $this->input->get('card') ? $this->input->get('card') : NULL;
        $cheque = $this->input->get('cheque') ? $this->input->get('cheque') : NULL;
        $transaction_id = $this->input->get('tid') ? $this->input->get('tid') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
        if ($pdf || $xls) {

            $this->db
                ->select("" . $this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref, " . $this->db->dbprefix('sales') . ".reference_no as sale_ref, " . $this->db->dbprefix('purchases') . ".reference_no as purchase_ref, paid_by, amount, type")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
                ->group_by('payments.id')
                ->order_by('payments.date desc');

            if ($user) {
                $this->db->where('payments.created_by', $user);
            }
            if ($card) {
                $this->db->like('payments.cc_no', $card, 'both');
            }
            if ($cheque) {
                $this->db->where('payments.cheque_no', $cheque);
            }
            if ($transaction_id) {
                $this->db->where('payments.transaction_id', $transaction_id);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($payment_ref) {
                $this->db->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($paid_by) {
                $this->db->where('payments.paid_by', $paid_by);
            }
            if ($sale_ref) {
                $this->db->like('sales.reference_no', $sale_ref, 'both');
            }
            if ($purchase_ref) {
                $this->db->like('purchases.reference_no', $purchase_ref, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('payments_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('payment_reference'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('sale_reference'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('purchase_reference'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('paid_by'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('type'));

                $row = 2;
                $total = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->payment_ref);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->sale_ref);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->purchase_ref);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($data_row->paid_by));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->amount);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->type);
                    if ($data_row->type == 'returned' || $data_row->type == 'sent') {
                        $total -= $data_row->amount;
                    } else {
                        $total += $data_row->amount;
                    }
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("F" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $filename = 'payments_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT({$this->db->dbprefix('payments')}.date, '%Y-%m-%d %T') as date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref, " . $this->db->dbprefix('sales') . ".reference_no as sale_ref, " . $this->db->dbprefix('purchases') . ".reference_no as purchase_ref, paid_by, amount, type, {$this->db->dbprefix('payments')}.id as id")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
                ->group_by('payments.id');

            if ($user) {
                $this->datatables->where('payments.created_by', $user);
            }
            if ($card) {
                $this->datatables->like('payments.cc_no', $card, 'both');
            }
            if ($cheque) {
                $this->datatables->where('payments.cheque_no', $cheque);
            }
            if ($transaction_id) {
                $this->datatables->where('payments.transaction_id', $transaction_id);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($supplier) {
                $this->datatables->where('purchases.supplier_id', $supplier);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($payment_ref) {
                $this->datatables->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($paid_by) {
                $this->datatables->where('payments.paid_by', $paid_by);
            }
            if ($sale_ref) {
                $this->datatables->like('sales.reference_no', $sale_ref, 'both');
            }
            if ($purchase_ref) {
                $this->datatables->like('purchases.reference_no', $purchase_ref, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }
    
    function conspayments()
    {
        $this->sma->checkPermissions('payments');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['pos_settings'] = POS ? $this->reports_model->getPOSSetting('biller') : FALSE;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('consolidated_payments_report')));
        $meta = array('page_title' => lang('consolidated_payments_report'), 'bc' => $bc);
        $this->page_construct('reports/conspayments', $meta, $this->data);
    }

    function getConsPaymentsReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('payments', TRUE);

        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;
        $customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $payment_ref = $this->input->get('payment_ref') ? $this->input->get('payment_ref') : NULL;
        $paid_by = $this->input->get('paid_by') ? $this->input->get('paid_by') : NULL;
        $sale_ref = $this->input->get('sale_ref') ? $this->input->get('sale_ref') : NULL;
        $purchase_ref = $this->input->get('purchase_ref') ? $this->input->get('purchase_ref') : NULL;
        $card = $this->input->get('card') ? $this->input->get('card') : NULL;
        $cheque = $this->input->get('cheque') ? $this->input->get('cheque') : NULL;
        $transaction_id = $this->input->get('tid') ? $this->input->get('tid') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld(trim($start_date));
            $end_date = $this->sma->fld(trim($end_date));
        }

        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
        if ($pdf || $xls) {

            $this->db
                ->select("paid_by, SUM(amount) as amount,type")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
                ->group_by('payments.paid_by')
                ->order_by('payments.date desc');

            if ($user) {
                $this->db->where('payments.created_by', $user);
            }
            if ($card) {
                $this->db->like('payments.cc_no', $card, 'both');
            }
            if ($cheque) {
                $this->db->where('payments.cheque_no', $cheque);
            }
            if ($transaction_id) {
                $this->db->where('payments.transaction_id', $transaction_id);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($payment_ref) {
                $this->db->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($paid_by) {
                $this->db->where('payments.paid_by', $paid_by);
            }
            if ($sale_ref) {
                $this->db->like('sales.reference_no', $sale_ref, 'both');
            }
            if ($purchase_ref) {
                $this->db->like('purchases.reference_no', $purchase_ref, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('consolidated_payments_report'));
       
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('paid_by'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('amount'));
            

                $row = 2;
                $total = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, lang($data_row->paid_by));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->amount);
                   
                    if ($data_row->type == 'returned' || $data_row->type == 'sent') {
                        $total -= $data_row->amount;
                    } else {
                        $total += $data_row->amount;
                    }
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("A" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('B' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $filename = 'Consolidated_payments_report';
                if(!empty($start_date) && !empty($end_date))
                {
                      $filename = 'Consolidated_payments_report_'.$start_date."-".$end_date;
                }

                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select("paid_by, SUM(amount), type")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
                ->group_by('payments.paid_by');

            if ($user) {
                $this->datatables->where('payments.created_by', $user);
            }
            if ($card) {
                $this->datatables->like('payments.cc_no', $card, 'both');
            }
            if ($cheque) {
                $this->datatables->where('payments.cheque_no', $cheque);
            }
            if ($transaction_id) {
                $this->datatables->where('payments.transaction_id', $transaction_id);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($supplier) {
                $this->datatables->where('purchases.supplier_id', $supplier);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($payment_ref) {
                $this->datatables->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($paid_by) {
                $this->datatables->where('payments.paid_by', $paid_by);
            }
            if ($sale_ref) {
                $this->datatables->like('sales.reference_no', $sale_ref, 'both');
            }
            if ($purchase_ref) {
                $this->datatables->like('purchases.reference_no', $purchase_ref, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }

    function customers()
    {
        $this->sma->checkPermissions('customers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('customers_report')));
        $meta = array('page_title' => lang('customers_report'), 'bc' => $bc);
        $this->page_construct('reports/customers', $meta, $this->data);
    }

    function getCustomers($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('customers', TRUE);

        if ($pdf || $xls) {

            $this->db
                ->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email, count(" . $this->db->dbprefix('sales') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", FALSE)
                ->from("companies")
                ->join('sales', 'sales.customer_id=companies.id')
                ->where('companies.group_name', 'customer')
                ->order_by('companies.company asc')
                ->group_by('companies.id');

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('customers_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('total_sales'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('total_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->company);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->phone);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->email);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->total);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->sma->formatMoney($data_row->total_amount));
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->sma->formatMoney($data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->sma->formatMoney($data_row->balance));
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $filename = 'customers_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $s = "( SELECT customer_id, count(" . $this->db->dbprefix('sales') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance from {$this->db->dbprefix('sales')} GROUP BY {$this->db->dbprefix('sales')}.customer_id ) FS";

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email, FS.total, FS.total_amount, FS.paid, FS.balance", FALSE)
                ->from("companies")
                ->join($s, 'FS.customer_id=companies.id')
                ->where('companies.group_name', 'customer')
                ->group_by('companies.id')
                ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . admin_url('reports/customer_report/$1') . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></div>", "id")
                ->unset_column('id');
            echo $this->datatables->generate();

        }

    }

    function customer_report($user_id = NULL)
    {
        $this->sma->checkPermissions('customers', TRUE);
        if (!$user_id) {
            $this->session->set_flashdata('error', lang("no_customer_selected"));
            admin_redirect('reports/customers');
        }

        $this->data['sales'] = $this->reports_model->getSalesTotals($user_id);
        $this->data['total_sales'] = $this->reports_model->getCustomerSales($user_id);
        $this->data['total_quotes'] = $this->reports_model->getCustomerQuotes($user_id);
        $this->data['total_returns'] = $this->reports_model->getCustomerReturns($user_id);
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $this->data['user_id'] = $user_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('customers_report')));
        $meta = array('page_title' => lang('customers_report'), 'bc' => $bc);
        $this->page_construct('reports/customer_report', $meta, $this->data);

    }

    function suppliers()
    {
        $this->sma->checkPermissions('suppliers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('suppliers_report')));
        $meta = array('page_title' => lang('suppliers_report'), 'bc' => $bc);
        $this->page_construct('reports/suppliers', $meta, $this->data);
    }

    function getSuppliers($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('suppliers', TRUE);

        if ($pdf || $xls) {

            $this->db
                ->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email, count({$this->db->dbprefix('purchases')}.id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", FALSE)
                ->from("companies")
                ->join('purchases', 'purchases.supplier_id=companies.id')
                ->where('companies.group_name', 'supplier')
                ->order_by('companies.company asc')
                ->group_by('companies.id');

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('suppliers_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('total_purchases'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('total_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->company);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->phone);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->email);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->total);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->total_amount);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->balance);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $filename = 'suppliers_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $p = "( SELECT supplier_id, count(" . $this->db->dbprefix('purchases') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance from {$this->db->dbprefix('purchases')} GROUP BY {$this->db->dbprefix('purchases')}.supplier_id ) FP";

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email, FP.total, FP.total_amount, FP.paid, FP.balance", FALSE)
                ->from("companies")
                ->join($p, 'FP.supplier_id=companies.id')
                ->where('companies.group_name', 'supplier')
                ->group_by('companies.id')
                ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . admin_url('reports/supplier_report/$1') . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></div>", "id")
                ->unset_column('id');
            echo $this->datatables->generate();

        }

    }

    function supplier_report($user_id = NULL)
    {
        $this->sma->checkPermissions('suppliers', TRUE);
        if (!$user_id) {
            $this->session->set_flashdata('error', lang("no_supplier_selected"));
            admin_redirect('reports/suppliers');
        }

        $this->data['purchases'] = $this->reports_model->getPurchasesTotals($user_id);
        $this->data['total_purchases'] = $this->reports_model->getSupplierPurchases($user_id);
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $this->data['user_id'] = $user_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('suppliers_report')));
        $meta = array('page_title' => lang('suppliers_report'), 'bc' => $bc);
        $this->page_construct('reports/supplier_report', $meta, $this->data);

    }

    function users()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('staff_report')));
        $meta = array('page_title' => lang('staff_report'), 'bc' => $bc);
        $this->page_construct('reports/users', $meta, $this->data);
    }

    function getUsers()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('users').".id as id, first_name, last_name, email, company, ".$this->db->dbprefix('groups').".name, active")
            ->from("users")
            ->join('groups', 'users.group_id=groups.id', 'left')
            ->group_by('users.id')
            ->where('company_id', NULL);
        if (!$this->Owner) {
            $this->datatables->where('group_id !=', 1);
        }
        $this->datatables
            ->edit_column('active', '$1__$2', 'active, id')
            ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . admin_url('reports/staff_report/$1') . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></div>", "id")
            ->unset_column('id');
        echo $this->datatables->generate();
    }

    function staff_report($user_id = NULL, $year = NULL, $month = NULL, $pdf = NULL, $cal = 0)
    {

        if (!$user_id) {
            $this->session->set_flashdata('error', lang("no_user_selected"));
            admin_redirect('reports/users');
        }
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $this->data['purchases'] = $this->reports_model->getStaffPurchases($user_id);
        $this->data['sales'] = $this->reports_model->getStaffSales($user_id);
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        if (!$year) {
            $year = date('Y');
        }
        if (!$month || $month == '#monthly-con') {
            $month = date('m');
        }
        if ($pdf) {
            if ($cal) {
                $this->monthly_sales($year, $pdf, $user_id);
            } else {
                $this->daily_sales($year, $month, $pdf, $user_id);
            }
        }
        $config = array(
            'show_next_prev' => TRUE,
            'next_prev_url' => admin_url('reports/staff_report/'.$user_id),
            'month_type' => 'long',
            'day_type' => 'long'
        );

        $config['template'] = '{table_open}<div class="table-responsive"><table border="0" cellpadding="0" cellspacing="0" class="table table-bordered dfTable reports-table">{/table_open}
		{heading_row_start}<tr>{/heading_row_start}
		{heading_previous_cell}<th class="text-center"><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
		{heading_title_cell}<th class="text-center" colspan="{colspan}" id="month_year">{heading}</th>{/heading_title_cell}
		{heading_next_cell}<th class="text-center"><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}
		{heading_row_end}</tr>{/heading_row_end}
		{week_row_start}<tr>{/week_row_start}
		{week_day_cell}<td class="cl_wday">{week_day}</td>{/week_day_cell}
		{week_row_end}</tr>{/week_row_end}
		{cal_row_start}<tr class="days">{/cal_row_start}
		{cal_cell_start}<td class="day">{/cal_cell_start}
		{cal_cell_content}
		<div class="day_num">{day}</div>
		<div class="content">{content}</div>
		{/cal_cell_content}
		{cal_cell_content_today}
		<div class="day_num highlight">{day}</div>
		<div class="content">{content}</div>
		{/cal_cell_content_today}
		{cal_cell_no_content}<div class="day_num">{day}</div>{/cal_cell_no_content}
		{cal_cell_no_content_today}<div class="day_num highlight">{day}</div>{/cal_cell_no_content_today}
		{cal_cell_blank}&nbsp;{/cal_cell_blank}
		{cal_cell_end}</td>{/cal_cell_end}
		{cal_row_end}</tr>{/cal_row_end}
		{table_close}</table></div>{/table_close}';

        $this->load->library('calendar', $config);
        $sales = $this->reports_model->getStaffDailySales($user_id, $year, $month);

        if (!empty($sales)) {
            foreach ($sales as $sale) {
                $daily_sale[$sale->date] = "<table class='table table-bordered table-hover table-striped table-condensed data' style='margin:0;'><tr><td>" . lang("discount") . "</td><td>" . $this->sma->formatMoney($sale->discount) . "</td></tr><tr><td>" . lang("product_tax") . "</td><td>" . $this->sma->formatMoney($sale->tax1) . "</td></tr><tr><td>" . lang("order_tax") . "</td><td>" . $this->sma->formatMoney($sale->tax2) . "</td></tr><tr><td>" . lang("total") . "</td><td>" . $this->sma->formatMoney($sale->total) . "</td></tr></table>";
            }
        } else {
            $daily_sale = array();
        }
        $this->data['calender'] = $this->calendar->generate($year, $month, $daily_sale);
        if ($this->input->get('pdf')) {

        }
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        $this->data['msales'] = $this->reports_model->getStaffMonthlySales($user_id, $year);
        $this->data['user_id'] = $user_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('staff_report')));
        $meta = array('page_title' => lang('staff_report'), 'bc' => $bc);
        $this->page_construct('reports/staff_report', $meta, $this->data);

    }

    function getUserLogins($id = NULL, $pdf = NULL, $xls = NULL)
    {
        if ($this->input->get('start_date')) {
            $login_start_date = $this->input->get('start_date');
        } else {
            $login_start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $login_end_date = $this->input->get('end_date');
        } else {
            $login_end_date = NULL;
        }
        if ($login_start_date) {
            $login_start_date = $this->sma->fld($login_start_date);
            $login_end_date = $login_end_date ? $this->sma->fld($login_end_date) : date('Y-m-d H:i:s');
        }
        if ($pdf || $xls) {

            $this->db
                ->select("login, ip_address, time")
                ->from("user_logins")
                ->where('user_id', $id)
                ->order_by('time desc');
            if ($login_start_date) {
                $this->db->where("time BETWEEN '{$login_start_date}' and '{$login_end_date}'", NULL, FALSE);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('staff_login_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('email'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('ip_address'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('time'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->login);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->ip_address);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->sma->hrld($data_row->time));
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('C2:C' . $row)->getAlignment()->setWrapText(true);
                $filename = 'staff_login_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select("login, ip_address, DATE_FORMAT(time, '%Y-%m-%d %T') as time")
                ->from("user_logins")
                ->where('user_id', $id);
            if ($login_start_date) {
                $this->datatables->where("time BETWEEN '{$login_start_date}' and '{$login_end_date}'", NULL, FALSE);
            }
            echo $this->datatables->generate();

        }

    }

    function getCustomerLogins($id = NULL)
    {
        if ($this->input->get('login_start_date')) {
            $login_start_date = $this->input->get('login_start_date');
        } else {
            $login_start_date = NULL;
        }
        if ($this->input->get('login_end_date')) {
            $login_end_date = $this->input->get('login_end_date');
        } else {
            $login_end_date = NULL;
        }
        if ($login_start_date) {
            $login_start_date = $this->sma->fld($login_start_date);
            $login_end_date = $login_end_date ? $this->sma->fld($login_end_date) : date('Y-m-d H:i:s');
        }
        $this->load->library('datatables');
        $this->datatables
            ->select("login, ip_address, time")
            ->from("user_logins")
            ->where('customer_id', $id);
        if ($login_start_date) {
            $this->datatables->where('time BETWEEN "' . $login_start_date . '" and "' . $login_end_date . '"');
        }
        echo $this->datatables->generate();
    }

    function profit_loss($start_date = NULL, $end_date = NULL)
    {
        $this->sma->checkPermissions('profit_loss');
        if (!$start_date) {
            $start = $this->db->escape(date('Y-m') . '-1');
            $start_date = date('Y-m') . '-1';
        } else {
            $start = $this->db->escape(urldecode($start_date));
        }
        if (!$end_date) {
            $end = $this->db->escape(date('Y-m-d H:i'));
            $end_date = date('Y-m-d H:i');
        } else {
            $end = $this->db->escape(urldecode($end_date));
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $this->data['total_purchases'] = $this->reports_model->getTotalPurchases($start, $end);
        $this->data['total_sales'] = $this->reports_model->getTotalSales($start, $end);
        $this->data['total_expenses'] = $this->reports_model->getTotalExpenses($start, $end);
        $this->data['total_paid'] = $this->reports_model->getTotalPaidAmount($start, $end);
        $this->data['total_received'] = $this->reports_model->getTotalReceivedAmount($start, $end);
        $this->data['total_received_cash'] = $this->reports_model->getTotalReceivedCashAmount($start, $end);
        $this->data['total_received_cc'] = $this->reports_model->getTotalReceivedCCAmount($start, $end);
        $this->data['total_received_cheque'] = $this->reports_model->getTotalReceivedChequeAmount($start, $end);
        $this->data['total_received_ppp'] = $this->reports_model->getTotalReceivedPPPAmount($start, $end);
        $this->data['total_received_stripe'] = $this->reports_model->getTotalReceivedStripeAmount($start, $end);
        $this->data['total_returned'] = $this->reports_model->getTotalReturnedAmount($start, $end);
        $this->data['start'] = urldecode($start_date);
        $this->data['end'] = urldecode($end_date);

        $warehouses = $this->site->getAllWarehouses();
        foreach ($warehouses as $warehouse) {
            $total_purchases = $this->reports_model->getTotalPurchases($start, $end, $warehouse->id);
            $total_sales = $this->reports_model->getTotalSales($start, $end, $warehouse->id);
            $total_expenses = $this->reports_model->getTotalExpenses($start, $end, $warehouse->id);
            $warehouses_report[] = array(
                'warehouse' => $warehouse,
                'total_purchases' => $total_purchases,
                'total_sales' => $total_sales,
                'total_expenses' => $total_expenses,
            );
        }
        $this->data['warehouses_report'] = $warehouses_report;

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('profit_loss')));
        $meta = array('page_title' => lang('profit_loss'), 'bc' => $bc);
        $this->page_construct('reports/profit_loss', $meta, $this->data);
    }

    function profit_loss_pdf($start_date = NULL, $end_date = NULL)
    {
        $this->sma->checkPermissions('profit_loss');
        if (!$start_date) {
            $start = $this->db->escape(date('Y-m') . '-1');
            $start_date = date('Y-m') . '-1';
        } else {
            $start = $this->db->escape(urldecode($start_date));
        }
        if (!$end_date) {
            $end = $this->db->escape(date('Y-m-d H:i'));
            $end_date = date('Y-m-d H:i');
        } else {
            $end = $this->db->escape(urldecode($end_date));
        }

        $this->data['total_purchases'] = $this->reports_model->getTotalPurchases($start, $end);
        $this->data['total_sales'] = $this->reports_model->getTotalSales($start, $end);
        $this->data['total_expenses'] = $this->reports_model->getTotalExpenses($start, $end);
        $this->data['total_paid'] = $this->reports_model->getTotalPaidAmount($start, $end);
        $this->data['total_received'] = $this->reports_model->getTotalReceivedAmount($start, $end);
        $this->data['total_received_cash'] = $this->reports_model->getTotalReceivedCashAmount($start, $end);
        $this->data['total_received_cc'] = $this->reports_model->getTotalReceivedCCAmount($start, $end);
        $this->data['total_received_cheque'] = $this->reports_model->getTotalReceivedChequeAmount($start, $end);
        $this->data['total_received_ppp'] = $this->reports_model->getTotalReceivedPPPAmount($start, $end);
        $this->data['total_received_stripe'] = $this->reports_model->getTotalReceivedStripeAmount($start, $end);
        $this->data['total_returned'] = $this->reports_model->getTotalReturnedAmount($start, $end);
        $this->data['start'] = urldecode($start_date);
        $this->data['end'] = urldecode($end_date);

        $warehouses = $this->site->getAllWarehouses();
        foreach ($warehouses as $warehouse) {
            $total_purchases = $this->reports_model->getTotalPurchases($start, $end, $warehouse->id);
            $total_sales = $this->reports_model->getTotalSales($start, $end, $warehouse->id);
            $total_expenses = $this->reports_model->getTotalExpenses($start, $end, $warehouse->id);
            $warehouses_report[] = array(
                'warehouse' => $warehouse,
                'total_purchases' => $total_purchases,
                'total_sales' => $total_sales,
                'total_expenses' => $total_expenses,
            );
        }
        $this->data['warehouses_report'] = $warehouses_report;

        $html = $this->load->view($this->theme . 'reports/profit_loss_pdf', $this->data, true);
        $name = lang("profit_loss") . "-" . str_replace(array('-', ' ', ':'), '_', $this->data['start']) . "-" . str_replace(array('-', ' ', ':'), '_', $this->data['end']) . ".pdf";
        $this->sma->generate_pdf($html, $name, false, false, false, false, false, 'L');
    }

    function register()
    {
        $this->sma->checkPermissions('register');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('register_report')));
        $meta = array('page_title' => lang('register_report'), 'bc' => $bc);
        $this->page_construct('reports/register', $meta, $this->data);
    }

    function getRrgisterlogs($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('register', TRUE);
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }

        if ($pdf || $xls) {

            $this->db
			->select("date, closed_at, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name, ' (', users.email, ')') as user, cash_in_hand, total_cc_slips, total_cheques,  total_other, total_cash, total_cc_slips_submitted, total_cheques_submitted, total_other_submitted, total_cash_submitted,total_online_payment_submitted,
                total_phonepe_submitted,
                total_cc_amount,
                total_gc,
                total_sales,
                total_refunds,
                total_expenses,


             note", FALSE)
                ->from("pos_register")
                ->join('users', 'users.id=pos_register.user_id', 'left')
                ->order_by('date desc');
            //->where('status', 'close');

            if ($user) {
                $this->db->where('pos_register.user_id', $user);
            }
            if ($start_date) {
                $this->db->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('register_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('open_time'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('close_time'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('user'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('cash_in_hand'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('cc_slips'));
               // $this->excel->getActiveSheet()->SetCellValue('F1', lang('cheques'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('other'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('total_cash'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('cc_slips_submitted'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('cheques_submitted'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('other_submitted'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('total_cash_submitted'));
                $this->excel->getActiveSheet()->SetCellValue('L1', lang('online_payment'));
                $this->excel->getActiveSheet()->SetCellValue('M1', lang('phone_pe'));
                         /*total_cc_amount,
                total_gc,
                total_sales,
                total_refunds,
                total_expenses,*/
                $this->excel->getActiveSheet()->SetCellValue('N1', lang('total_cc_amount'));
                $this->excel->getActiveSheet()->SetCellValue('O1', lang('total_gc'));
                $this->excel->getActiveSheet()->SetCellValue('P1', lang('total_sales'));
                $this->excel->getActiveSheet()->SetCellValue('Q1', lang('total_refunds'));
                $this->excel->getActiveSheet()->SetCellValue('R1', lang('total_expenses'));
                $this->excel->getActiveSheet()->SetCellValue('S1', lang('note'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->closed_at);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->user);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->cash_in_hand);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->total_cc_slips);
                   // $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->total_cheques);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->total_other);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->total_cash);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->total_cc_slips_submitted);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->total_cheques_submitted);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->total_other_submitted);
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->total_cash_submitted);
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->total_online_payment_submitted);
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->total_phonepe_submitted);

                    $this->excel->getActiveSheet()->SetCellValue('N' . $row, $data_row->total_cc_amount);
                    $this->excel->getActiveSheet()->SetCellValue('O' . $row, $data_row->total_gc);
                    $this->excel->getActiveSheet()->SetCellValue('P' . $row, $data_row->total_sales);
                    $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $data_row->total_refunds);
                    $this->excel->getActiveSheet()->SetCellValue('R' . $row, $data_row->total_expenses);

                    $this->excel->getActiveSheet()->SetCellValue('S' . $row, $data_row->note);
                    if($data_row->total_cash_submitted < $data_row->total_cash || $data_row->total_cheques_submitted < $data_row->total_cheques || $data_row->total_cc_slips_submitted < $data_row->total_cc_slips || $data_row->total_other_submitted < $data_row->total_other) {
                        $this->excel->getActiveSheet()->getStyle('A'.$row.':N'.$row)->applyFromArray(
                            array( 'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'F2DEDE')) )
                        );
                    }
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(35);

                $this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('Q')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('R')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('S')->setWidth(35);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $filename = 'register_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
			->select("pos_register.id,date, closed_at, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name, '<br>', " . $this->db->dbprefix('users') . ".email) as user, cash_in_hand, CONCAT(total_cc_slips, ' (', total_cc_slips_submitted, ')'),  CONCAT(total_other, ' (', total_other_submitted, ')'), CONCAT(total_cash, ' (', total_cash_submitted, ')'), total_online_payment_submitted,total_phonepe_submitted,
                total_cc_amount,
                total_gc,
                total_sales,
                total_refunds,
                total_expenses, note", FALSE)
                ->from("pos_register")
                ->join('users', 'users.id=pos_register.user_id', 'left')

                ->add_column("Actions", "<div class='text-center'><a class='tip'
                        data-toggle='modal'data-target='#myModal'
                 title='" . lang("view_register") . "' href='" . admin_url('pos/view_register/$1') . "'><span class='label label-primary'>" . lang("view_register") . "</span></a></div>", "pos_register.id,closed_at")
                ->unset_column('pos_register.id');

                /*<a class="btn borange pos-tip" id="close_register" title="<span><?=lang('close_register')?></span>" data-placement="bottom" data-html="true" data-backdrop="static" href="<?=admin_url('pos/close_register')?>" data-toggle="modal" data-target="#myModal">
                            <i class="fa fa-times-circle"></i>
                        </a>*/

            if ($user) {
                $this->datatables->where('pos_register.user_id', $user);
            }
            if ($start_date) {
                $this->datatables->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }

    public function expenses($id = null)
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['categories'] = $this->reports_model->getExpenseCategories();
		$this->data['warehouses'] = $this->site->getAllWarehouses();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('expenses')));
        $meta = array('page_title' => lang('expenses'), 'bc' => $bc);
        $this->page_construct('reports/expenses', $meta, $this->data);
    }

    public function getExpensesReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('expenses');

        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $note = $this->input->get('note') ? $this->input->get('note') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
		$warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }

        if ($pdf || $xls) {

            $this->db
                ->select("date, reference, {$this->db->dbprefix('expense_categories')}.name as category, amount, note, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user, attachment, {$this->db->dbprefix('expenses')}.id as id", false)
                ->from('expenses')
                ->join('users', 'users.id=expenses.created_by', 'left')
                ->join('expense_categories', 'expense_categories.id=expenses.category_id', 'left')
                ->group_by('expenses.id');

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('created_by', $this->session->userdata('user_id'));
            }
			if ($warehouse) {
		        $this->db->where("{$this->db->dbprefix('expenses')}.warehouse_id", $warehouse);

	        }
            if ($note) {
                $this->db->like('note', $note, 'both');
            }
            if ($reference_no) {
                $this->db->like('reference', $reference_no, 'both');
            }
            if ($category) {
                $this->db->where('category_id', $category);
            }
            if ($user) {
                $this->db->where('created_by', $user);
            }
            if ($start_date) {
                $this->db->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('expenses_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('category'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('amount'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('created_by'));

                $row = 2; $total = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->category);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->amount);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->note);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->user);
                    $total += $data_row->amount;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("D" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $filename = 'expenses_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference, {$this->db->dbprefix('expense_categories')}.name as category, amount, note, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user, attachment, {$this->db->dbprefix('expenses')}.id as id", false)
                ->from('expenses')
                ->join('users', 'users.id=expenses.created_by', 'left')
                ->join('expense_categories', 'expense_categories.id=expenses.category_id', 'left')
                ->group_by('expenses.id');

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('created_by', $this->session->userdata('user_id'));
            }
			if ($warehouse) {
		        $this->datatables->where("{$this->db->dbprefix('expenses')}.warehouse_id", $warehouse);

	        }
            if ($note) {
                $this->datatables->like('note', $note, 'both');
            }
            if ($reference_no) {
                $this->datatables->like('reference', $reference_no, 'both');
            }
            if ($category) {
                $this->datatables->where('category_id', $category);
            }
            if ($user) {
                $this->datatables->where('created_by', $user);
            }
            if ($start_date) {
                $this->datatables->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
    }

    function daily_purchases($warehouse_id = NULL, $year = NULL, $month = NULL, $pdf = NULL, $user_id = NULL)
    {
        $this->sma->checkPermissions();
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        if (!$year) {
            $year = date('Y');
        }
        if (!$month) {
            $month = date('m');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $config = array(
            'show_next_prev' => TRUE,
            'next_prev_url' => admin_url('reports/daily_purchases/'.($warehouse_id ? $warehouse_id : 0)),
            'month_type' => 'long',
            'day_type' => 'long'
        );

        $config['template'] = '{table_open}<div class="table-responsive"><table border="0" cellpadding="0" cellspacing="0" class="table table-bordered dfTable">{/table_open}
        {heading_row_start}<tr>{/heading_row_start}
        {heading_previous_cell}<th><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
        {heading_title_cell}<th colspan="{colspan}" id="month_year">{heading}</th>{/heading_title_cell}
        {heading_next_cell}<th><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}
        {heading_row_end}</tr>{/heading_row_end}
        {week_row_start}<tr>{/week_row_start}
        {week_day_cell}<td class="cl_wday">{week_day}</td>{/week_day_cell}
        {week_row_end}</tr>{/week_row_end}
        {cal_row_start}<tr class="days">{/cal_row_start}
        {cal_cell_start}<td class="day">{/cal_cell_start}
        {cal_cell_content}
        <div class="day_num">{day}</div>
        <div class="content">{content}</div>
        {/cal_cell_content}
        {cal_cell_content_today}
        <div class="day_num highlight">{day}</div>
        <div class="content">{content}</div>
        {/cal_cell_content_today}
        {cal_cell_no_content}<div class="day_num">{day}</div>{/cal_cell_no_content}
        {cal_cell_no_content_today}<div class="day_num highlight">{day}</div>{/cal_cell_no_content_today}
        {cal_cell_blank}&nbsp;{/cal_cell_blank}
        {cal_cell_end}</td>{/cal_cell_end}
        {cal_row_end}</tr>{/cal_row_end}
        {table_close}</table></div>{/table_close}';

        $this->load->library('calendar', $config);
        $purchases = $user_id ? $this->reports_model->getStaffDailyPurchases($user_id, $year, $month, $warehouse_id) : $this->reports_model->getDailyPurchases($year, $month, $warehouse_id);

        if (!empty($purchases)) {
            foreach ($purchases as $purchase) {
                $daily_purchase[$purchase->date] = "<table class='table table-bordered table-hover table-striped table-condensed data' style='margin:0;'><tr><td>" . lang("discount") . "</td><td>" . $this->sma->formatMoney($purchase->discount) . "</td></tr><tr><td>" . lang("shipping") . "</td><td>" . $this->sma->formatMoney($purchase->shipping) . "</td></tr><tr><td>" . lang("product_tax") . "</td><td>" . $this->sma->formatMoney($purchase->tax1) . "</td></tr><tr><td>" . lang("order_tax") . "</td><td>" . $this->sma->formatMoney($purchase->tax2) . "</td></tr><tr><td>" . lang("total") . "</td><td>" . $this->sma->formatMoney($purchase->total) . "</td></tr></table>";
            }
        } else {
            $daily_purchase = array();
        }

        $this->data['calender'] = $this->calendar->generate($year, $month, $daily_purchase);
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/daily', $this->data, true);
            $name = lang("daily_purchases") . "_" . $year . "_" . $month . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_calendar_text") . '</p>', '', $html);
            $this->sma->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['sel_warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('daily_purchases_report')));
        $meta = array('page_title' => lang('daily_purchases_report'), 'bc' => $bc);
        $this->page_construct('reports/daily_purchases', $meta, $this->data);

    }


    function monthly_purchases($warehouse_id = NULL, $year = NULL, $pdf = NULL, $user_id = NULL)
    {
        $this->sma->checkPermissions();
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        if (!$year) {
            $year = date('Y');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->load->language('calendar');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['year'] = $year;
        $this->data['purchases'] = $user_id ? $this->reports_model->getStaffMonthlyPurchases($user_id, $year, $warehouse_id) : $this->reports_model->getMonthlyPurchases($year, $warehouse_id);
        if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/monthly', $this->data, true);
            $name = lang("monthly_purchases") . "_" . $year . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_calendar_text") . '</p>', '', $html);
            $this->sma->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['sel_warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('monthly_purchases_report')));
        $meta = array('page_title' => lang('monthly_purchases_report'), 'bc' => $bc);
        $this->page_construct('reports/monthly_purchases', $meta, $this->data);

    }

    function adjustments($warehouse_id = NULL)
    {
        $this->sma->checkPermissions('products');

        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('adjustments_report')));
        $meta = array('page_title' => lang('adjustments_report'), 'bc' => $bc);
        $this->page_construct('reports/adjustments', $meta, $this->data);
    }

  function getAdjustmentReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);

        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        $serial = $this->input->get('serial') ? $this->input->get('serial') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $ai = "( SELECT adjustment_id, product_id, serial_no, GROUP_CONCAT(CONCAT({$this->db->dbprefix('products')}.name, ' (', (CASE WHEN {$this->db->dbprefix('adjustment_items')}.type  = 'subtraction' THEN (0-{$this->db->dbprefix('adjustment_items')}.quantity) ELSE {$this->db->dbprefix('adjustment_items')}.quantity END), ')') SEPARATOR '\n') as item_nane,sum((CASE WHEN {$this->db->dbprefix('adjustment_items')}.type  = 'subtraction' THEN (0-{$this->db->dbprefix('adjustment_items')}.quantity) ELSE {$this->db->dbprefix('adjustment_items')}.quantity END)*{$this->db->dbprefix('products')}.cost) as amount from {$this->db->dbprefix('adjustment_items')} LEFT JOIN {$this->db->dbprefix('products')} ON {$this->db->dbprefix('products')}.id={$this->db->dbprefix('adjustment_items')}.product_id GROUP BY {$this->db->dbprefix('adjustment_items')}.adjustment_id ) FAI";

            $this->db->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, warehouses.name as wh_name, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by, note, FAI.item_nane as iname,FAI.amount as total, {$this->db->dbprefix('adjustments')}.id as id", FALSE)
                ->from('adjustments')
                ->join($ai, 'FAI.adjustment_id=adjustments.id', 'left')
                ->join('users', 'users.id=adjustments.created_by', 'left')
                ->join('warehouses', 'warehouses.id=adjustments.warehouse_id', 'left');

            if ($user) {
                $this->db->where('adjustments.created_by', $user);
            }
            if ($product) {
                $this->db->where('FAI.product_id', $product, FALSE);
            }
            if ($serial) {
                $this->db->like('FAI.serial_no', $serial, FALSE);
            }
            if ($warehouse) {
                $this->db->where('adjustments.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('adjustments.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('adjustments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('adjustments_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('created_by'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('products'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('grand_total'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->wh_name);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->created_by);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->sma->decode_html($data_row->note));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->total);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                $this->excel->getActiveSheet()->getStyle('F2:F' . $row)->getAlignment()->setWrapText(true);
                $filename = 'adjustments_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $ai = "( SELECT adjustment_id, product_id, serial_no, GROUP_CONCAT(CONCAT({$this->db->dbprefix('products')}.name, '__', (CASE WHEN {$this->db->dbprefix('adjustment_items')}.type  = 'subtraction' THEN (0-{$this->db->dbprefix('adjustment_items')}.quantity) ELSE {$this->db->dbprefix('adjustment_items')}.quantity END)) SEPARATOR '___') as item_nane,sum((CASE WHEN {$this->db->dbprefix('adjustment_items')}.type  = 'subtraction' THEN (0-{$this->db->dbprefix('adjustment_items')}.quantity) ELSE {$this->db->dbprefix('adjustment_items')}.quantity END)*{$this->db->dbprefix('products')}.cost) as amount from {$this->db->dbprefix('adjustment_items')} LEFT JOIN {$this->db->dbprefix('products')} ON {$this->db->dbprefix('products')}.id={$this->db->dbprefix('adjustment_items')}.product_id ";
            if ($product) {
                $ai .= " WHERE {$this->db->dbprefix('adjustment_items')}.product_id = {$product} ";
            }
            $ai .= " GROUP BY {$this->db->dbprefix('adjustment_items')}.adjustment_id ) FAI";
            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, warehouses.name as wh_name, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by, note, FAI.item_nane as iname,FAI.amount as total, {$this->db->dbprefix('adjustments')}.id as id", FALSE)
                ->from('adjustments')
                ->join($ai, 'FAI.adjustment_id=adjustments.id', 'left')
                ->join('users', 'users.id=adjustments.created_by', 'left')
                ->join('warehouses', 'warehouses.id=adjustments.warehouse_id', 'left');

            if ($user) {
                $this->datatables->where('adjustments.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FAI.product_id', $product, FALSE);
            }
            if ($serial) {
                $this->datatables->like('FAI.serial_no', $serial, FALSE);
            }
            if ($warehouse) {
                $this->datatables->where('adjustments.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('adjustments.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('adjustments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }

    }


    function get_deposits($company_id = NULL)
    {
        $this->sma->checkPermissions('customers', TRUE);
        $this->load->library('datatables');
        $this->datatables
            ->select("date, amount, paid_by, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by, note", false)
            ->from("deposits")
            ->join('users', 'users.id=deposits.created_by', 'left')
            ->where($this->db->dbprefix('deposits').'.company_id', $company_id);
        echo $this->datatables->generate();
    }

    //added by Dnyandev on 11/08/2017

     function HSNOutward()
    {

        $this->sma->checkPermissions('products');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('HSN-Wise_Summary_Of_Outward_Supplies')));
        $meta = array('page_title' => lang('HSN-Wise_Summary_Of_Outward_Supplies'), 'bc' => $bc);
        $this->page_construct('reports/hsn_outward', $meta, $this->data);
    }

  function getHSNOutwardReport($pdf = NULL, $xls = NULL)
    {

        $this->sma->checkPermissions('products', TRUE);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($pdf || $xls) {


            
              /*  ->select("{$this->db->dbprefix('sale_items')}.gst_code,name_of_commodity,{$this->db->dbprefix('sale_items')}.product_code, SUM( {$this->db->dbprefix('sale_items')}.quantity ) saleQty, SUM( {$this->db->dbprefix('sale_items')}.subtotal ) totalSale, SUM( {$this->db->dbprefix('sale_items')}.net_unit_price ) totalTaxable,SUM({$this->db->dbprefix('sale_items')}.cgst_amt) TCGST,SUM({$this->db->dbprefix('sale_items')}.sgst_amt) TSGST,SUM({$this->db->dbprefix('sale_items')}.cess_amt) totcess")*/

              $this->db->select("gst_code,product_name as name_of_commodity,product_code, product_name,sum(quantity) as saleQty ,sum(subtotal) as totalSale , sum(net_unit_price*quantity) as totalTaxable,sum(cgst_amt) as TCGST,sum(sgst_amt) as TSGST, sum(cess_amt) as totcess")
                ->from('sale_items')
              ->join('sales', "sales.id = sale_items.id")
                ->group_start()
                ->where($this->db->dbprefix('sale_items').'.gst_code <> ', "")
                ->where($this->db->dbprefix('sale_items').'.gst_code <> ', 'No code found')
                 ->group_end()
                ->group_by("{$this->db->dbprefix('sale_items')}.product_code");

                //echo "string";die();

            if ($start_date || $warehouse) {


                if ($start_date) {
                    $start_date = $this->sma->fld($start_date);
                    $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');

                    
                    $this->db->where("{$this->db->dbprefix('sales')}.date >=", $start_date);
                    $this->db->where("{$this->db->dbprefix('sales')}.date <=", $end_date);

                }
               /* if ($warehouse) {
                    $this->db->where("{$this->db->dbprefix('sale_items')}.warehouse_id", $warehouse);

                }*/
            }

           // $this->db->group_by("{$this->db->dbprefix('sale_items')}.product_code");

            $q = $this->db->get();
           
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('HSN-wise Summary of outward'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('HSN Code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('Description'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Product Code'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('Product Name'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('total_quantity'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('Total_Value'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('Total_Taxable_Value'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('CGST'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('SGST'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('CESS'));

                $row = 2;
                $TOT = 0;
                $TXAmt = 0;
                $sAmt = 0;
                $pAmt = 0;
                $pQty = 0;
                $sg = 0;
                $cg = 0;
                $cs = 0;
                foreach ($data as $data_row) {

                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->gst_code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name_of_commodity);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->product_code);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->product_name);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->saleQty);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->totalSale);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->totalTaxable);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->TCGST);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->TSGST);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->totcess);

                    $pQty += $data_row->saleQty;
                    $TOT += $data_row->totalSale;
                    $TXAmt += $data_row->totalTaxable;
                   // $sAmt += $data_row->TotalSales;
                    $sg += $data_row->TSGST;
                    $cg += $data_row->TCGST;
                    $cs += $data_row->totcess;

                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("D" . $row . ":I" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $TOT);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $TXAmt);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $sg);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $cg);
                $this->excel->getActiveSheet()->SetCellValue('J' . $row, $cs);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                $filename = 'hsn_wise_report';
                $this->load->helper('excel');
               // print_r($this->excel);die;
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {


            $this->load->library('datatables');
            // $this->datatables           
            //   ->select("gst_code,product_name as name_of_commodity,product_code,product_name, sum(saleQty) as saleQty ,sum(totalSale) as totalSale , sum(totalTaxable) as totalTaxable,sum(TCGST) as TCGST,sum(TSGST) as TSGST, sum(totcess) as totcess")
            //     ->from('hsnwise_sale')
            //     ->group_by("{$this->db->dbprefix('hsnwise_sale')}.product_code");


              $this->datatables->select("gst_code,product_name as name_of_commodity,product_code, product_name,sum(quantity) as saleQty ,sum(subtotal) as totalSale , sum(net_unit_price*quantity) as totalTaxable,sum(cgst_amt) as TCGST,sum(sgst_amt) as TSGST, sum(cess_amt) as totcess")
                ->from('sale_items')
              ->join('sales', "sales.id = sale_items.id")
              //  ->group_start()
                ->where($this->db->dbprefix('sale_items').'.gst_code <> ', "")
                ->where($this->db->dbprefix('sale_items').'.gst_code <> ', 'No code found')
                // ->group_end()
                ->group_by("{$this->db->dbprefix('sale_items')}.product_code");

            if ($start_date || $warehouse) {


                if ($start_date) {
                    $start_date = $this->sma->fld($start_date);
                    $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                    $this->datatables->where("{$this->db->dbprefix('sales')}.date >=", $start_date);
                    $this->datatables->where("{$this->db->dbprefix('sales')}.date <=", $end_date);

                }
               /* if ($warehouse) {
                    $this->datatables->where("{$this->db->dbprefix('sale_items')}.warehouse_id", $warehouse);

                }*/
            }

           // $this->datatables->group_by("{$this->db->dbprefix('sale_items')}.product_code");


            $this->datatables->unset_column('date');
            echo $this->datatables->generate();


        }

    }


    function getHSNOutwardReport1($pdf = NULL, $xls = NULL)
    {

        $this->sma->checkPermissions('products', TRUE);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($pdf || $xls) {

            $this->db
              /*  ->select("{$this->db->dbprefix('sale_items')}.gst_code,name_of_commodity,{$this->db->dbprefix('sale_items')}.product_code, SUM( {$this->db->dbprefix('sale_items')}.quantity ) saleQty, SUM( {$this->db->dbprefix('sale_items')}.subtotal ) totalSale, SUM( {$this->db->dbprefix('sale_items')}.net_unit_price ) totalTaxable,SUM({$this->db->dbprefix('sale_items')}.cgst_amt) TCGST,SUM({$this->db->dbprefix('sale_items')}.sgst_amt) TSGST,SUM({$this->db->dbprefix('sale_items')}.cess_amt) totcess")*/

              ->select("gst_code,product_name as name_of_commodity,product_code, product_name,sum(saleQty) as saleQty ,sum(totalSale) as totalSale , sum(totalTaxable) as totalTaxable,sum(TCGST) as TCGST,sum(TSGST) as TSGST, sum(totcess) as totcess")
                ->from('hsnwise_sale')
                //->join('gst_goods_rates', "gst_goods_rates.plain_hsn_code = hsnwise_sale.gst_code")
                ->group_by("{$this->db->dbprefix('hsnwise_sale')}.product_code");


            if ($start_date || $warehouse) {


                if ($start_date) {
                    $start_date = $this->sma->fld($start_date);
                    $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                    $this->db->where("{$this->db->dbprefix('hsnwise_sale')}.date >=", $start_date);
                    $this->db->where("{$this->db->dbprefix('hsnwise_sale')}.date <=", $end_date);

                }
               /* if ($warehouse) {
                    $this->db->where("{$this->db->dbprefix('sale_items')}.warehouse_id", $warehouse);

                }*/
            }

           // $this->db->group_by("{$this->db->dbprefix('sale_items')}.product_code");

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('HSN-wise Summary of outward'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('HSN Code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('Description'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Product Code'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('Product Name'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('total_quantity'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('Total_Value'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('Total_Taxable_Value'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('CGST'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('SGST'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('CESS'));

                $row = 2;
                $TOT = 0;
                $TXAmt = 0;
                $sAmt = 0;
                $pAmt = 0;
                $pQty = 0;
                $sg = 0;
                $cg = 0;
                $cs = 0;
                foreach ($data as $data_row) {

                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->gst_code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name_of_commodity);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->product_code);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->product_name);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->saleQty);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->totalSale);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->totalTaxable);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->TCGST);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->TSGST);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->totcess);

                    $pQty += $data_row->saleQty;
                    $TOT += $data_row->totalSale;
                    $TXAmt += $data_row->totalTaxable;
                   // $sAmt += $data_row->TotalSales;
                    $sg += $data_row->TSGST;
                    $cg += $data_row->TCGST;
                    $cs += $data_row->totcess;

                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("D" . $row . ":I" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $TOT);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $TXAmt);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $sg);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $cg);
                $this->excel->getActiveSheet()->SetCellValue('J' . $row, $cs);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                $filename = 'hsn_wise_report';
                $this->load->helper('excel');
               // print_r($this->excel);die;
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {


            $this->load->library('datatables');
            $this->datatables
              /*  ->select("{$this->db->dbprefix('sale_items')}.gst_code,name_of_commodity,{$this->db->dbprefix('sale_items')}.product_code, SUM( {$this->db->dbprefix('sale_items')}.quantity ) saleQty, SUM( {$this->db->dbprefix('sale_items')}.subtotal ) totalSale, SUM( {$this->db->dbprefix('sale_items')}.net_unit_price ) totalTaxable,SUM({$this->db->dbprefix('sale_items')}.cgst_amt) TCGST,SUM({$this->db->dbprefix('sale_items')}.sgst_amt) TSGST,SUM({$this->db->dbprefix('sale_items')}.cess_amt) totcess")*/
              ->select("gst_code,name_of_commodity,product_code,product_name, sum(saleQty) as saleQty ,sum(totalSale) as totalSale , sum(totalTaxable) as totalTaxable,sum(TCGST) as TCGST,sum(TSGST) as TSGST, sum(totcess) as totcess")
                ->from('hsnwise_sale')
                ->join('gst_goods_rates', "gst_goods_rates.plain_hsn_code = hsnwise_sale.gst_code")
                ->group_by("{$this->db->dbprefix('hsnwise_sale')}.product_code");


            if ($start_date || $warehouse) {


                if ($start_date) {
                    $start_date = $this->sma->fld($start_date);
                    $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                    $this->datatables->where("{$this->db->dbprefix('hsnwise_sale')}.date >=", $start_date);
                    $this->datatables->where("{$this->db->dbprefix('hsnwise_sale')}.date <=", $end_date);

                }
               /* if ($warehouse) {
                    $this->datatables->where("{$this->db->dbprefix('sale_items')}.warehouse_id", $warehouse);

                }*/
            }

           // $this->datatables->group_by("{$this->db->dbprefix('sale_items')}.product_code");


            $this->datatables->unset_column('date');
            echo $this->datatables->generate();


        }

    }



    function supplier_inward()
    {

        $this->sma->checkPermissions('products');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('Supplier_Inward_Report')));
        $meta = array('page_title' => lang('Supplier_Inward_Report'), 'bc' => $bc);
        $this->page_construct('reports/supplier_inward', $meta, $this->data);
    }

    function getSupplierInwardReport($pdf = NULL, $xls = NULL)
    {

        $this->sma->checkPermissions('products', TRUE);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($pdf || $xls) {

            $this->db
                ->select("{$this->db->dbprefix('companies')}.name,company,vat_no,SUM( {$this->db->dbprefix('purchase_items')}.quantity ) prQty, SUM( {$this->db->dbprefix('purchase_items')}.subtotal ) totalPurchase, SUM( {$this->db->dbprefix('purchase_items')}.unit_cost ) totalTaxable,SUM({$this->db->dbprefix('purchase_items')}.cgst_amt) TCGST,SUM({$this->db->dbprefix('purchase_items')}.sgst_amt) TSGST,SUM({$this->db->dbprefix('purchase_items')}.cess_amt) totcess")
                ->from('purchase_items')
                ->join('purchases', "{$this->db->dbprefix('purchases')}.id = {$this->db->dbprefix('purchase_items')}.purchase_id")
                ->join('companies', "{$this->db->dbprefix('companies')}.id = {$this->db->dbprefix('purchases')}.supplier_id")
                ->group_by("{$this->db->dbprefix('purchases')}.supplier_id");



            if ($start_date || $warehouse) {


                if ($start_date) {
                    $start_date = $this->sma->fld($start_date);
                    $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                    $this->db->where("{$this->db->dbprefix('purchases')}.date >=", $start_date);
                    $this->db->where("{$this->db->dbprefix('purchases')}.date <=", $end_date);

                }
                if ($warehouse) {
                    $this->db->where("{$this->db->dbprefix('purchase_items')}.warehouse_id", $warehouse);

                }
            }

            // $this->db->group_by("{$this->db->dbprefix('sale_items')}.product_code");

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('Supplier_Inward_Report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('Supplier Name'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('Company Name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('GSTIN Number'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('total_quantity'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('Total_Value'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('Total_Taxable_Value'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('CGST'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('SGST'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('CESS'));

                $row = 2;
                $TOT = 0;
                $TXAmt = 0;
                $pQty = 0;
                $sg = 0;
                $cg = 0;
                $cs = 0;
                foreach ($data as $data_row) {

                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->company);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->vat_no);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->prQty);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->totalPurchase);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->totalTaxable);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->TCGST);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->TSGST);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->totcess);

                    $pQty += $data_row->prQty;
                    $TOT += $data_row->totalPurchase;
                    $TXAmt += $data_row->totalTaxable;
                    // $sAmt += $data_row->TotalSales;
                    $sg += $data_row->TSGST;
                    $cg += $data_row->TCGST;
                    $cs += $data_row->totcess;

                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("C" . $row . ":G" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $TOT);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $TXAmt);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sg);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $cg);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $cs);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                $filename = 'Supplier_Inward_Report';
                $this->load->helper('excel');
                // print_r($this->excel);die;
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {


            $this->load->library('datatables');
            $this->datatables
                ->select("{$this->db->dbprefix('companies')}.name,company,vat_no,SUM( {$this->db->dbprefix('purchase_items')}.quantity ) prQty, SUM( {$this->db->dbprefix('purchase_items')}.subtotal ) totalPurchase, SUM( {$this->db->dbprefix('purchase_items')}.unit_cost ) totalTaxable,SUM({$this->db->dbprefix('purchase_items')}.cgst_amt) TCGST,SUM({$this->db->dbprefix('purchase_items')}.sgst_amt) TSGST,SUM({$this->db->dbprefix('purchase_items')}.cess_amt) totcess")
                ->from('purchase_items')
                ->join('purchases', "{$this->db->dbprefix('purchases')}.id = {$this->db->dbprefix('purchase_items')}.purchase_id")
                ->join('companies', "{$this->db->dbprefix('companies')}.id = {$this->db->dbprefix('purchases')}.supplier_id")
                 ->group_by("{$this->db->dbprefix('purchases')}.supplier_id");


            if ($start_date || $warehouse) {


                if ($start_date) {
                    $start_date = $this->sma->fld($start_date);
                    $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                    $this->datatables->where("{$this->db->dbprefix('purchases')}.date >=", $start_date);
                    $this->datatables->where("{$this->db->dbprefix('purchases')}.date <=", $end_date);

                }
                if ($warehouse) {
                    $this->datatables->where("{$this->db->dbprefix('sale_items')}.warehouse_id", $warehouse);

                }
            }


            //  $this->datatables->unset_column('cid');
            echo $this->datatables->generate();


        }

    }


function daily_profitloss(){


    $this->sma->checkPermissions('products');
    $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
    $this->data['categories'] = $this->site->getAllCategories();
    $this->data['warehouses'] = $this->site->getAllWarehouses();
    if ($this->input->post('start_date')) {
        $dt = "From " . $this->input->post('start_date') ;
    }

    $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('Daily_Profit_Loss')));
    $meta = array('page_title' => lang('Daily_Profit_Loss'), 'bc' => $bc);
    $this->page_construct('reports/daily_profitloss_report', $meta, $this->data);

}

    function getDailyProfitLossReport($pdf = NULL, $xls = NULL)
    {
      
        $this->sma->checkPermissions('sales', TRUE);

		$this->db->query("drop table {$this->db->dbprefix('temp_prls')}");


        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : date('d-m-Y');


        $start_date = date('Y-m-d',strtotime(str_replace('/','-',$start_date)));

	    $this->db->query("create table {$this->db->dbprefix('temp_prls')} SELECT product_id,product_code,product_name,sum(quantity) as saleQty,(i.subtotal/i.quantity) as product_price,sum(i.subtotal) saletotal FROM {$this->db->dbprefix('sale_items')} i inner join {$this->db->dbprefix('sales')} s on s.id=i.sale_id where date(date) = '{$start_date}' group by i.product_id");
        if ($pdf || $xls) {

            /*$this->db
                ->select("{$this->db->dbprefix('sale_items')}.product_code,{$this->db->dbprefix('sale_items')}.product_name,
                sum({$this->db->dbprefix('sale_items')}.quantity) as saleQty,({$this->db->dbprefix('sale_items')}.subtotal/{$this->db->dbprefix('sale_items')}.quantity) as product_price,sum({$this->db->dbprefix('sale_items')}.subtotal) saletotal,
               avg_purchase_price as purchase_price,
                (avg_purchase_price*sum({$this->db->dbprefix('sale_items')}.quantity)) as purchase_total,
                (sum({$this->db->dbprefix('sale_items')}.subtotal)-(avg_purchase_price * sum({$this->db->dbprefix('sale_items')}.quantity))) as totalprofit", FALSE)
                ->from('sale_items')
                ->join('sales', 'sales.id=sale_items.sale_id')
                ->join('avg_prprice', 'avg_prprice.product_id=sale_items.product_id')
                ->group_by("{$this->db->dbprefix('sale_items')}.product_id");



            $this->db->where("date({$this->db->dbprefix('sales')}.date)='{$start_date}'");*/

	        $this->db
		        ->select("product_code,product_name,saleQty,product_price,saletotal,avg_purchase_price as purchase_price,(avg_purchase_price*saleQty) as purchase_total,saletotal-(avg_purchase_price * saleQty) as totalprofit", FALSE)
		        ->from('temp_prls')
		        ->join('avg_prprice', 'avg_prprice.product_id=temp_prls.product_id','left');

            //  $this->db->where("sale_status='completed'");
            // $this->db->group_by("{$this->db->dbprefix('sale_items')}.product_code");

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('Productwise Sale on '.date('d-m-Y',strtotime($start_date))));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('Product Code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('Product Name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Total Quantity'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('Product Price'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('Total_Sale'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('Purchase_Price'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('Purchase_Total'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('Total_Profit'));


                $row = 2;

                $prPrice = 0;
                $sQty = 0;
                $sTot = 0;
                $pTot = 0;
                $pPrice = 0;
                $tProf = 0;
                foreach ($data as $data_row) {

                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->product_code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->product_name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->saleQty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->product_price);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->saletotal);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->purchase_price);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->purchase_total);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->totalprofit);

                    $sQty += $data_row->saleQty;
                    $sTot += $data_row->saletotal;
                    //$prPrice += $data_row->product_price;
                    $pTot += $data_row->purchase_total;
                    $pPrice += $data_row->purchase_price;
                    $tProf += $data_row->totalprofit;


                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("C" . $row . ":I" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sTot);
                //  $this->excel->getActiveSheet()->SetCellValue('F' . $row, $pPrice);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $pTot);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $tProf);


                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(35);

                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                $filename = 'daily_profitloss_'.date('d-m-Y',strtotime($start_date));
                $this->load->helper('excel');
                // print_r($this->excel);die;
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            /*$this->datatables
                ->select("{$this->db->dbprefix('sale_items')}.product_code,{$this->db->dbprefix('sale_items')}.product_name,
                sum({$this->db->dbprefix('sale_items')}.quantity) as saleQty,({$this->db->dbprefix('sale_items')}.subtotal/{$this->db->dbprefix('sale_items')}.quantity) as product_price,sum({$this->db->dbprefix('sale_items')}.subtotal) saletotal,
                avg_purchase_price as purchase_price,
                (avg_purchase_price*sum({$this->db->dbprefix('sale_items')}.quantity)) as purchase_total,
                (sum({$this->db->dbprefix('sale_items')}.subtotal)-(avg_purchase_price * sum({$this->db->dbprefix('sale_items')}.quantity))) as totalprofit", FALSE)
                ->from('sale_items')
                ->join('sales', 'sales.id=sale_items.sale_id')
                ->join('avg_prprice', 'avg_prprice.product_id=sale_items.product_id')
                ->group_by("{$this->db->dbprefix('sale_items')}.product_id");


            $this->datatables->where("date({$this->db->dbprefix('sales')}.date)='{$start_date}'");*/

	        $this->datatables
		        ->select("product_code,product_name,saleQty,product_price,saletotal,avg_purchase_price as purchase_price,(avg_purchase_price*saleQty) as purchase_total,saletotal-(avg_purchase_price * saleQty) as totalprofit", FALSE)
		        ->from('temp_prls')
		        ->join('avg_prprice', 'avg_prprice.product_id=temp_prls.product_id','left');

            echo $this->datatables->generate();

        }

        }

//Harshad
    function gst()
    {
        $this->sma->checkPermissions('products');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        //$this->data['brands'] = $this->site->getAllBrands();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('gst_report')));
        $meta = array('page_title' => lang('gst_report'), 'bc' => $bc);
        $this->page_construct('reports/gst', $meta, $this->data);
    }
	
	function getGSTReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        // $category = $this->input->get('category') ? $this->input->get('category') : NULL;
       $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : date('Y-m-d 00:00');
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : date('Y-m-d 11:59');
        if ($start_date && $end_date) {
            $start_date = $end_date ? $this->sma->fld($start_date) : date('Y-m-d');
            $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
            $pp = " {$this->db->dbprefix('sales')}.date >= '{$start_date}' AND {$this->db->dbprefix('sales')}.date < '{$end_date}' ";
        }

        $p='(SELECT sale_id,GROUP_CONCAT( paid_by SEPARATOR "," ) as pays FROM sma_payments';
               

               $q=' GROUP BY sale_id ) as  FI';
               

                if ($start_date && $end_date) {
               $p.="  where  sale_id in (select id from sma_sales where ".$pp.") ";

               }
               $p.=$q;

      
        if ($pdf || $xls){
            
             $totalRound=0;

             $qp="select sum(COALESCE(rounding,0)) totrnd from sma_sales ";

              if ($start_date && $end_date) {
               $qp.="   where ".$pp." ";

               }
               $qq=$this->db->query($qp);
               if($qq->num_rows()>0)
               {
                  

                  if($qq->row()->totrnd)
                  {
                    $totalRound=$qq->row()->totrnd;
                  }
               }

             $ptypes=array(
                'cash',
                'gift_card',
                'Online Payment',
                'Phone Pe',
                'Cheque',
                'CC' );
                
                   $this->db
                ->select("'Sales' as Saletype,reference_no,DATE_FORMAT(date, '%d/%m/%Y') as sale_date,


                   (
                    CASE WHEN pos = 0  THEN customer

                     WHEN FI.pays = 'cash'  THEN 'cash'
                      WHEN FI.pays= 'Online Payment'  THEN 'Online Payment'
                      WHEN FI.pays = 'Phone pe'  THEN 'Phone pe'
                      WHEN FI.pays = 'gift_card'  THEN 'Gift card'
                      WHEN FI.pays = 'other'  THEN 'Other'
                      WHEN FI.pays = 'CC'  THEN 'Credit Card'   
                      WHEN FI.pays = 'Cheque'  THEN 'Cheque' 
                     ELSE  'customer'
                     end 
                 )

                    as party



                    ,'Maharashtra' as State, vat_no ,

                      (
                    CASE WHEN pos = 0  THEN 'regular'

                    
                     ELSE  'Unregistered/Consumer'
                     end 
                 )

                    as reg_type


                    ,sale_items.product_name,'' as ProdGroup,product_unit_code as Unit,sale_items.tax_rate_id as GST_Rate,sale_items.gst_code as GSTCode,sale_items.quantity as Unit_Qty,sale_items.unit_price as Rate,
                    
                    (net_unit_price*quantity) as totalTaxable ,
                    sale_items.subtotal as Value,'' as Sales_Ledger,sale_items.item_discount as Discount,sale_items.cgst_rate,sale_items.sgst_rate, sale_items.igst_rate,sale_items.cgst_amt,sale_items.sgst_amt,sale_items.igst_amt,sale_items.cess_rate,sale_items.cess_amt,

                    rounding
                    


                    ")
                ->from('sales')
                ->join('sale_items', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id",'inner')
                ->join($p,"FI.sale_id={$this->db->dbprefix('sales')}.id",'left')
                ->join('companies',"companies.id={$this->db->dbprefix('sales')}.customer_id",'left')
              
            

                ->where($pp);


            $q = $this->db->get();
            $old_id=0;
            $rounding=0;
                

           
// echo "string".$pp;die();
            
            if ($q->num_rows() > 0) {
                $prevrefNo="";
                $rn=0;
                foreach (($q->result_array()) as $row) {

                   

                    if($rn>0)
                    {

                        if($prevrefNo==$row['reference_no'])
                        {
                            $row['rounding']=0;
                        }


                    }
                    $rn++;
                    
                    $data[] = $row;
                     $prevrefNo=$row['reference_no'];
                }

            } else {
                $data = NULL;
            }
            if (!empty($data)) {
                $sdate = str_replace(' ','_',$start_date);
                $edate = str_replace(' ','_',$end_date);

                $filename = "gst_report_{$sdate}_To_{$edate}.csv";
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=$filename");
                header("Content-Type: application/csv; ");
                $file = fopen('php://output', 'w');
$header = array("Sale Type","Reference No","Sale Date","Party","State","GSTIN","Reg. Type","Product name","Product group","Unit","GST Rate","GST Code","Unit Qty","Rate","Taxable","Value","Sale Ledger","Discount","CGST Rate","SGST Rate","IGST Rate","CGST Amt","SGST Amt","IGST Amt","CESS Rate","CESS Amt","Rounding");


                fputcsv($file, $header);
                foreach ($data as $key=>$line){
                    fputcsv($file,$line);
                }
                fputcsv($file, array());
                
                 foreach ($ptypes as $pt) {
                        
                        
                         $parr=$this->reports_model->getAllPayment($start_date, $end_date,$pt);
                       
                        fputcsv($file, array('Total '.$pt ." : ".($parr?$parr->total_amount:"0.0") ) );
                    } 
                fclose($file);
                exit;
            }
            else
            {
                $this->session->set_flashdata('error', lang('nothing_found'));
                 redirect($_SERVER["HTTP_REFERER"]);
               
            }
        } else {

            $this->load->library('datatables');
             $this->datatables
                ->select("{$this->db->dbprefix('sales')}.id,'Sales' as Saletype,reference_no,DATE_FORMAT(date, '%d/%m/%Y') as sale_date,


                   (
                    CASE WHEN pos = 0  THEN customer

                     WHEN FI.pays = 'cash'  THEN 'cash'
                      WHEN FI.pays= 'Online Payment'  THEN 'Online Payment'
                      WHEN FI.pays = 'Phone pe'  THEN 'Phone pe'
                      WHEN FI.pays = 'gift_card'  THEN 'Gift card'
                      WHEN FI.pays = 'other'  THEN 'Other'
                      WHEN FI.pays = 'CC'  THEN 'Credit Card'   
                      WHEN FI.pays = 'Cheque'  THEN 'Cheque' 
                     ELSE  'customer'
                     end 
                 )

                    as party



                    ,'Maharashtra' as State, vat_no ,

                      (
                    CASE WHEN pos = 0  THEN 'regular'

                    
                     ELSE  'Unregistered/Consumer'
                     end 
                 )

                    as reg_type


                    ,sale_items.product_name,'' as ProdGroup,product_unit_code as Unit,sale_items.tax_rate_id as GST_Rate,sale_items.gst_code as GSTCode,sale_items.quantity as Unit_Qty,sale_items.unit_price as Rate,sale_items.subtotal as Value,'' as Sales_Ledger,sale_items.item_discount as Discount,sale_items.cgst_rate,sale_items.sgst_rate, sale_items.igst_rate,sale_items.cgst_amt,sale_items.sgst_amt,sale_items.igst_amt,sale_items.cess_rate,sale_items.cess_amt,

                    pos,rounding,customer_id,(net_unit_price*quantity) as totalTaxable,product_id
                    


                    ")
                ->from('sales')
                ->join('sale_items', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id",'inner')
                ->join($p,"FI.sale_id={$this->db->dbprefix('sales')}.id",'left')
                ->join('companies',"companies.id={$this->db->dbprefix('sales')}.customer_id",'left')
              
            

                ->where($pp);


            $this->datatables->unset_column("{$this->db->dbprefix('sales')}.id");
            echo $this->datatables->generate();

        }

    }
	
	  function getGSTReport_old($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        // $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : date('Y-m-d 00:00');
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : date('Y-m-d 11:59');
		/*Sale Type	Reference No	Sale Date	Party Name	State	GST NO	Reg Type	Product Name	Product Group	
		Unit

		GST Rate	GST Code	Unit Qty	Rate	Value	
		Sale Ledger	Discount	CGST Rate	SGST Rate	IGST Rate	CGST Amt	SGST Amt	IGST Amt	CESS Rate	CESS Amt	
*/

		$this->load->library('excel');
         $sht=$this->excel->getActiveSheet();
			    //$this->excel->setActiveSheetIndex(0);
			              $sht->setTitle(lang('gst_report'));
                $sht->SetCellValue('A1', lang('Sale Type'));
                $sht->SetCellValue('B1', lang('Reference no'));
                $sht->SetCellValue('C1', lang('Sale Date'));
                $sht->SetCellValue('D1', lang('Party Name'));
                $sht->SetCellValue('E1', lang('State'));
                $sht->SetCellValue('F1', lang('GST NO'));
                $sht->SetCellValue('G1', lang('Reg Type'));
                
                
                $sht->SetCellValue('H1', lang('Product Name'));
                $sht->SetCellValue('I1', lang('Product Group'));
                $sht->SetCellValue('J1', lang('Unit'));
                $sht->SetCellValue('K1', lang('GST Rate'));
                $sht->SetCellValue('L1', lang('GST Code'));
                $sht->SetCellValue('M1', lang('Unit Qty'));
                $sht->SetCellValue('N1', lang('Rate'));
                $sht->SetCellValue('O1', lang('Taxable'));
                $sht->SetCellValue('P1', lang('Value'));
                $sht->SetCellValue('Q1', lang('Sale Ledger'));
                $sht->SetCellValue('R1', lang('Discount'));
                $sht->SetCellValue('S1', lang('CGST Rate'));
                $sht->SetCellValue('T1', lang('SGST Rate'));
                $sht->SetCellValue('U1', lang('IGST Rate'));
                
                $sht->SetCellValue('V1', lang('CGST Amt'));
                $sht->SetCellValue('W1', lang('SGST Amt'));
                $sht->SetCellValue('X1', lang('IGST Amt'));

        
                $sht->SetCellValue('Y1', lang('CESS Rate'));
                 $sht->SetCellValue('Z1', lang('CESS Amt'));
                 $sht->SetCellValue('AA1', lang('Rounding'));

        if ($start_date && $end_date) {



            $start_date = $end_date ? $this->sma->fld($start_date) : date('Y-m-d');
            $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
            $pp = " {$this->db->dbprefix('sales')}.date >= '{$start_date}' AND {$this->db->dbprefix('sales')}.date < '{$end_date}' ";
        }

         $p='(SELECT sale_id,GROUP_CONCAT( paid_by SEPARATOR "," ) as pays FROM sma_payments';
               

               $q=' GROUP BY sale_id ) as  FI';
               

                if ($start_date && $end_date) {
               $p.="  where  sale_id in (select id from sma_sales where ".$pp.") ";

               }
               $p.=$q;

        if ($pdf || $xls){

            $totalRound=0;

             $qp="select sum(COALESCE(rounding,0)) totrnd from sma_sales ";

              if ($start_date && $end_date) {
               $qp.="   where ".$pp." ";

               }
               
              

               $qq=$this->db->query($qp);
               if($qq->num_rows()>0)
               {
                  

                  if($qq->row()->totrnd)
                  {
                    $totalRound=$qq->row()->totrnd;
                  }
               }

             $ptypes=array(
                'cash',
                'gift_card',
                'Online Payment',
                'Phone Pe',
                'Cheque',
                'CC' );

          

            // $this->db
            //     ->select("'Sales' as Saletype,reference_no,DATE_FORMAT(date, '%d/%m/%Y') as Sale_Date,'customer' as Party_Name,'Maharashtra' as State,'' as GST_NO,sale_items.product_name,'' as ProdGroup,'' as Unit,sale_items.tax_rate_id as GST_Rate,sale_items.gst_code as GSTCode,sale_items.quantity as Unit_Qty,sale_items.unit_price as Rate,sale_items.subtotal as Value,'' as Sales_Ledger,sale_items.item_discount as Discount,sale_items.cgst_rate,sale_items.sgst_rate, sale_items.igst_rate,sale_items.cgst_amt,sale_items.sgst_amt,sale_items.igst_amt,sale_items.cess_rate,sale_items.cess_amt")
            //     ->from('sales')
            //     ->join('sale_items', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id")
            //     ->where($pp);

             $this->db
                ->select("{$this->db->dbprefix('sales')}.id,'Sales' as Saletype,reference_no,DATE_FORMAT(date, '%d/%m/%Y') as sale_date,


                   (
                    CASE WHEN pos = 0  THEN customer

                     WHEN FI.pays = 'cash'  THEN 'cash'
                      WHEN FI.pays= 'Online Payment'  THEN 'Online Payment'
                      WHEN FI.pays = 'Phone pe'  THEN 'Phone pe'
                      WHEN FI.pays = 'gift_card'  THEN 'Gift card'
                      WHEN FI.pays = 'other'  THEN 'Other'
                      WHEN FI.pays = 'CC'  THEN 'Credit Card'   
                      WHEN FI.pays = 'Cheque'  THEN 'Cheque' 
                     ELSE  'customer'
                     end 
                 )

                    as party



                    ,'Maharashtra' as State, vat_no ,

                      (
                    CASE WHEN pos = 0  THEN 'regular'

                    
                     ELSE  'Unregistered/Consumer'
                     end 
                 )

                    as reg_type


                    ,sale_items.product_name,'' as ProdGroup,product_unit_code as Unit,sale_items.tax_rate_id as GST_Rate,sale_items.gst_code as GSTCode,sale_items.quantity as Unit_Qty,sale_items.unit_price as Rate,sale_items.subtotal as Value,'' as Sales_Ledger,sale_items.item_discount as Discount,sale_items.cgst_rate,sale_items.sgst_rate, sale_items.igst_rate,sale_items.cgst_amt,sale_items.sgst_amt,sale_items.igst_amt,sale_items.cess_rate,sale_items.cess_amt,

                    pos,rounding,customer_id,(net_unit_price*quantity) as totalTaxable,product_id
                    


                    ")
                ->from('sales')
                ->join('sale_items', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id",'inner')
                ->join($p,"FI.sale_id={$this->db->dbprefix('sales')}.id",'left')
                ->join('companies',"companies.id={$this->db->dbprefix('sales')}.customer_id",'left')
              
            

                ->where($pp);


            $q = $this->db->get();
			$old_id=0;
			$rounding=0;

           

           

            if ($q->num_rows() > 0) {

 //echo "AQ ".$q->num_rows();die();
//echo "string".$q->num_rows();die();
                 $row = 2;
                foreach ($q->result() as $data_row) {
                    

                  
					if($old_id!=$data_row->id)
					{
						$old_id=$data_row->id;
						$rounding=$data_row->rounding;
						
					}
                    $sht->SetCellValue('A' . $row, $data_row->Saletype);
				    $sht->SetCellValue('B' . $row, $data_row->reference_no);
				    $sht->SetCellValue('C' . $row, $data_row->sale_date);
				    $sht->SetCellValue('D' . $row, $data_row->party);
				    $sht->SetCellValue('E' . $row, $data_row->State);

                    if($data_row->pos==0)
                    {
                       
                        $sht->SetCellValue('F' .$row, $data_row->vat_no);
                    }
                    else
                    {

					$sht->SetCellValue('F' . $row, "");
                    }
				    $sht->SetCellValue('G' . $row, $data_row->reg_type);
					
					 $sht->SetCellValue('H' . $row, $data_row->product_name);
				    $sht->SetCellValue('I' . $row, $data_row->ProdGroup);

                      //$uname=$this->site->getUnitName($data_row->product_id);
				    $sht->SetCellValue('J' . $row, $data_row->Unit);
                    $sht->SetCellValue('K' . $row, $data_row->GST_Rate);
                    $sht->SetCellValue('L' . $row, $data_row->GSTCode);
                    $sht->SetCellValue('M' . $row, $data_row->Unit_Qty);
                
                    $sht->SetCellValue('N' . $row, $data_row->Rate);
                    $sht->SetCellValue('O' . $row, $data_row->totalTaxable);
                    $sht->SetCellValue('P' . $row, $data_row->Value);
                    $sht->SetCellValue('Q' . $row, $data_row->Sales_Ledger);
                    $sht->SetCellValue('R' . $row, $data_row->Discount);
                    
                    $sht->SetCellValue('S' . $row, $data_row->cgst_rate);
                    $sht->SetCellValue('T' . $row, $data_row->sgst_rate);
                    $sht->SetCellValue('U' . $row, $data_row->igst_rate);
                    
                    $sht->SetCellValue('V' . $row, $data_row->cgst_amt);
                    $sht->SetCellValue('W' . $row, $data_row->sgst_amt);
                    $sht->SetCellValue('X' . $row, $data_row->igst_amt);
                
                
                    $sht->SetCellValue('Y' . $row, $data_row->cess_rate);
                    $sht->SetCellValue('Z' . $row, $data_row->cess_amt);
                    $sht->SetCellValue('AA' . $row, $rounding);
					$rounding='';
                    $row++;
				}
				$uppercase = range('A', 'Z');
				foreach($uppercase as $letter)
				{
					  $sht->getColumnDimension($letter)->setWidth(35);
				}
				
                $sht->SetCellValue('A' . $row, $rounding);

                    foreach ($ptypes as $pt) {
                        # code...
                         $row++;
                         $parr=$this->reports_model->getAllPayment($start_date, $end_date,$pt);
                       
                        $sht->SetCellValue('A' . $row, 'Total '.$pt ." : ".($parr?$parr->total_amount:"0.0") );
                    } 

				$filename = 'gst_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            } else {
                 $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
            }
           
        } else {

           
            $this->load->library('datatables');
            $this->datatables
                ->select("{$this->db->dbprefix('sales')}.id,'Sales' as Saletype,reference_no,DATE_FORMAT(date, '%d/%m/%Y') as Sale_Date,


                   (
                    CASE WHEN pos = 0  THEN customer

                     WHEN FI.pays = 'cash'  THEN 'cash'
                      WHEN FI.pays= 'Online Payment'  THEN 'Online Payment'
                      WHEN FI.pays = 'Phone pe'  THEN 'Phone pe'
                      WHEN FI.pays = 'gift_card'  THEN 'Gift card'
                      WHEN FI.pays = 'other'  THEN 'Other'
                      WHEN FI.pays = 'CC'  THEN 'Credit Card'   
                      WHEN FI.pays = 'Cheque'  THEN 'Cheque' 
                     ELSE  'customer'
                     end 
                 )

                    as party



                    ,'Maharashtra' as State,'' as GST_NO,sale_items.product_name,'' as ProdGroup,'' as Unit,sale_items.tax_rate_id as GST_Rate,sale_items.gst_code as GSTCode,sale_items.quantity as Unit_Qty,sale_items.unit_price as Rate,sale_items.subtotal as Value,'' as Sales_Ledger,sale_items.item_discount as Discount,sale_items.cgst_rate,sale_items.sgst_rate, sale_items.igst_rate,sale_items.cgst_amt,sale_items.sgst_amt,sale_items.igst_amt,sale_items.cess_rate,sale_items.cess_amt,

                    pos
                    


                    ")
                ->from('sales')
                ->join('sale_items', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id",'inner')
                ->join($p,"FI.sale_id={$this->db->dbprefix('sales')}.id",'left')
                ->where($pp);


            $this->datatables->unset_column("{$this->db->dbprefix('sales')}.id");
            echo $this->datatables->generate();

        }

    }
	
	//new
	 function getGSTReport4($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        // $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : date('Y-m-d 00:00');
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : date('Y-m-d 11:59');
		/*Sale Type	Reference No	Sale Date	Party Name	State	GST NO	Reg Type	Product Name	Product Group	
		Unit

		GST Rate	GST Code	Unit Qty	Rate	Value	
		Sale Ledger	Discount	CGST Rate	SGST Rate	IGST Rate	CGST Amt	SGST Amt	IGST Amt	CESS Rate	CESS Amt	
*/
		$this->load->library('excel');
			    $this->excel->setActiveSheetIndex(0);
			    $this->excel->getActiveSheet()->setTitle(lang('gst_report'));
			    $this->excel->getActiveSheet()->SetCellValue('A1', lang('Sale Type'));
			    $this->excel->getActiveSheet()->SetCellValue('B1', lang('Reference no'));
			    $this->excel->getActiveSheet()->SetCellValue('C1', lang('Sale Date'));
			    $this->excel->getActiveSheet()->SetCellValue('D1', lang('Party Name'));
			    $this->excel->getActiveSheet()->SetCellValue('E1', lang('State'));
			    $this->excel->getActiveSheet()->SetCellValue('F1', lang('GST NO'));
			    $this->excel->getActiveSheet()->SetCellValue('G1', lang('Reg Type'));
				
				
			    $this->excel->getActiveSheet()->SetCellValue('H1', lang('Product Name'));
			    $this->excel->getActiveSheet()->SetCellValue('I1', lang('Product Group'));
			    $this->excel->getActiveSheet()->SetCellValue('J1', lang('Unit'));
			    $this->excel->getActiveSheet()->SetCellValue('K1', lang('GST Rate'));
			    $this->excel->getActiveSheet()->SetCellValue('L1', lang('GST Code'));
			    $this->excel->getActiveSheet()->SetCellValue('M1', lang('Unit Qty'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('Rate'));
                $this->excel->getActiveSheet()->SetCellValue('O1', lang('Taxable'));
				$this->excel->getActiveSheet()->SetCellValue('P1', lang('Value'));
				$this->excel->getActiveSheet()->SetCellValue('Q1', lang('Sale Ledger'));
				$this->excel->getActiveSheet()->SetCellValue('R1', lang('Discount'));
				$this->excel->getActiveSheet()->SetCellValue('S1', lang('CGST Rate'));
			    $this->excel->getActiveSheet()->SetCellValue('T1', lang('SGST Rate'));
				$this->excel->getActiveSheet()->SetCellValue('U1', lang('IGST Rate'));
			    
			    $this->excel->getActiveSheet()->SetCellValue('V1', lang('CGST Amt'));
			    $this->excel->getActiveSheet()->SetCellValue('W1', lang('SGST Amt'));
				$this->excel->getActiveSheet()->SetCellValue('X1', lang('IGST Amt'));

		
			    $this->excel->getActiveSheet()->SetCellValue('Y1', lang('CESS Rate'));
				 $this->excel->getActiveSheet()->SetCellValue('Z1', lang('CESS Amt'));
                 $this->excel->getActiveSheet()->SetCellValue('AA1', lang('Rounding'));

        if ($start_date && $end_date) {



            $start_date = $end_date ? $this->sma->fld($start_date) : date('Y-m-d');
            $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
            $pp = " {$this->db->dbprefix('sales')}.date >= '{$start_date}' AND {$this->db->dbprefix('sales')}.date < '{$end_date}' ";
        }

         $p='(SELECT sale_id,GROUP_CONCAT( paid_by SEPARATOR "," ) as pays FROM sma_payments';
               

               $q=' GROUP BY sale_id ) as  FI';
               

                if ($start_date && $end_date) {
               $p.="  where  sale_id in (select id from sma_sales where ".$pp.") ";

               }
               $p.=$q;

        if ($pdf || $xls){

            $totalRound=0;

             $qp="select sum(COALESCE(rounding,0)) totrnd from sma_sales ";

              if ($start_date && $end_date) {
               $qp.="   where ".$pp." ";

               }
               
              

               $qq=$this->db->query($qp);
               if($qq->num_rows()>0)
               {
                  

                  if($qq->row()->totrnd)
                  {
                    $totalRound=$qq->row()->totrnd;
                  }
               }

             $ptypes=array(
                'cash',
                'gift_card',
                'Online Payment',
                'Phone Pe',
                'Cheque',
                'CC' );

          

            // $this->db
            //     ->select("'Sales' as Saletype,reference_no,DATE_FORMAT(date, '%d/%m/%Y') as Sale_Date,'customer' as Party_Name,'Maharashtra' as State,'' as GST_NO,sale_items.product_name,'' as ProdGroup,'' as Unit,sale_items.tax_rate_id as GST_Rate,sale_items.gst_code as GSTCode,sale_items.quantity as Unit_Qty,sale_items.unit_price as Rate,sale_items.subtotal as Value,'' as Sales_Ledger,sale_items.item_discount as Discount,sale_items.cgst_rate,sale_items.sgst_rate, sale_items.igst_rate,sale_items.cgst_amt,sale_items.sgst_amt,sale_items.igst_amt,sale_items.cess_rate,sale_items.cess_amt")
            //     ->from('sales')
            //     ->join('sale_items', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id")
            //     ->where($pp);

             $this->db
                ->select("{$this->db->dbprefix('sales')}.id,'Sales' as Saletype,reference_no,DATE_FORMAT(date, '%d/%m/%Y') as sale_date,


                   (
                    CASE WHEN pos = 0  THEN customer

                     WHEN FI.pays = 'cash'  THEN 'cash'
                      WHEN FI.pays= 'Online Payment'  THEN 'Online Payment'
                      WHEN FI.pays = 'Phone pe'  THEN 'Phone pe'
                      WHEN FI.pays = 'gift_card'  THEN 'Gift card'
                      WHEN FI.pays = 'other'  THEN 'Other'
                      WHEN FI.pays = 'CC'  THEN 'Credit Card'   
                      WHEN FI.pays = 'Cheque'  THEN 'Cheque' 
                     ELSE  'customer'
                     end 
                 )

                    as party



                    ,'Maharashtra' as State,'' as vat_no ,

                      (
                    CASE WHEN pos = 0  THEN 'regular'

                    
                     ELSE  'Unregistered/Consumer'
                     end 
                 )

                    as reg_type


                    ,sale_items.product_name,'' as ProdGroup,'' as Unit,sale_items.tax_rate_id as GST_Rate,sale_items.gst_code as GSTCode,sale_items.quantity as Unit_Qty,sale_items.unit_price as Rate,sale_items.subtotal as Value,'' as Sales_Ledger,sale_items.item_discount as Discount,sale_items.cgst_rate,sale_items.sgst_rate, sale_items.igst_rate,sale_items.cgst_amt,sale_items.sgst_amt,sale_items.igst_amt,sale_items.cess_rate,sale_items.cess_amt,

                    pos,rounding,customer_id,(net_unit_price*quantity) as totalTaxable,product_id
                    


                    ")
                ->from('sales')
                ->join('sale_items', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id",'inner')
                ->join($p,"FI.sale_id={$this->db->dbprefix('sales')}.id",'left')
            

                ->where($pp);


            $q = $this->db->get();
			$old_id=0;
			$rounding=0;
            if ($q->num_rows() > 0) {

//echo "string".$q->num_rows();die();
                 $row = 2;
                foreach ($q->result() as $data_row) {
                    

                  
					if($old_id!=$data_row->id)
					{
						$old_id=$data_row->id;
						$rounding=$data_row->rounding;
						
					}
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->Saletype);
				    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
				    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->sale_date);
				    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->party);
				    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->State);

                    if($data_row->pos==1)
                    {
                        $vat_no=$this->site->getVatByID($data_row->customer_id);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $vat_no?$vat_no:"");
                    }
                    else
                    {

					$this->excel->getActiveSheet()->SetCellValue('F' . $row, "");
                    }
				    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->reg_type);
					
					 $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->product_name);
				    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->ProdGroup);

                      $uname=$this->site->getUnitName($data_row->product_id);
				    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $uname);
				    $this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->GST_Rate);
				    $this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->GSTCode);
				    $this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->Unit_Qty);
				
                    $this->excel->getActiveSheet()->SetCellValue('N' . $row, $data_row->Rate);
				    $this->excel->getActiveSheet()->SetCellValue('O' . $row, $data_row->totalTaxable);
				    $this->excel->getActiveSheet()->SetCellValue('P' . $row, $data_row->Value);
				    $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $data_row->Sales_Ledger);
				    $this->excel->getActiveSheet()->SetCellValue('R' . $row, $data_row->Discount);
					
				    $this->excel->getActiveSheet()->SetCellValue('S' . $row, $data_row->cgst_rate);
				    $this->excel->getActiveSheet()->SetCellValue('T' . $row, $data_row->sgst_rate);
					$this->excel->getActiveSheet()->SetCellValue('U' . $row, $data_row->igst_rate);
					
					 $this->excel->getActiveSheet()->SetCellValue('V' . $row, $data_row->cgst_amt);
				    $this->excel->getActiveSheet()->SetCellValue('W' . $row, $data_row->sgst_amt);
					$this->excel->getActiveSheet()->SetCellValue('X' . $row, $data_row->igst_amt);
				
				
					 $this->excel->getActiveSheet()->SetCellValue('Y' . $row, $data_row->cess_rate);
				    $this->excel->getActiveSheet()->SetCellValue('Z' . $row, $data_row->cess_amt);
					$this->excel->getActiveSheet()->SetCellValue('AA' . $row, $rounding);
					$rounding='';
                    $row++;
				}
				$uppercase = range('A', 'Z');
				foreach($uppercase as $letter)
				{
					  $this->excel->getActiveSheet()->getColumnDimension($letter)->setWidth(35);
				}
				
                $this->excel->getActiveSheet()->SetCellValue('A' . $row, $rounding);

                    foreach ($ptypes as $pt) {
                        # code...
                         $row++;
                         $parr=$this->reports_model->getAllPayment($start_date, $end_date,$pt);
                       
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, 'Total '.$pt ." : ".($parr?$parr->total_amount:"0.0") );
                    } 

				$filename = 'gst_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            } else {
                 $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
            }
           
        } else {

           
            $this->load->library('datatables');
            $this->datatables
                ->select("{$this->db->dbprefix('sales')}.id,'Sales' as Saletype,reference_no,DATE_FORMAT(date, '%d/%m/%Y') as Sale_Date,


                   (
                    CASE WHEN pos = 0  THEN customer

                     WHEN FI.pays = 'cash'  THEN 'cash'
                      WHEN FI.pays= 'Online Payment'  THEN 'Online Payment'
                      WHEN FI.pays = 'Phone pe'  THEN 'Phone pe'
                      WHEN FI.pays = 'gift_card'  THEN 'Gift card'
                      WHEN FI.pays = 'other'  THEN 'Other'
                      WHEN FI.pays = 'CC'  THEN 'Credit Card'   
                      WHEN FI.pays = 'Cheque'  THEN 'Cheque' 
                     ELSE  'customer'
                     end 
                 )

                    as party



                    ,'Maharashtra' as State,'' as GST_NO,sale_items.product_name,'' as ProdGroup,'' as Unit,sale_items.tax_rate_id as GST_Rate,sale_items.gst_code as GSTCode,sale_items.quantity as Unit_Qty,sale_items.unit_price as Rate,sale_items.subtotal as Value,'' as Sales_Ledger,sale_items.item_discount as Discount,sale_items.cgst_rate,sale_items.sgst_rate, sale_items.igst_rate,sale_items.cgst_amt,sale_items.sgst_amt,sale_items.igst_amt,sale_items.cess_rate,sale_items.cess_amt,

                    pos
                    


                    ")
                ->from('sales')
                ->join('sale_items', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id",'inner')
                ->join($p,"FI.sale_id={$this->db->dbprefix('sales')}.id",'left')
                ->where($pp);


            $this->datatables->unset_column("{$this->db->dbprefix('sales')}.id");
            echo $this->datatables->generate();

        }

    }

     function getGSTReport2($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        // $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : date('Y-m-d 00:00');
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : date('Y-m-d 11:59');
		/*Sale Type	Reference No	Sale Date	Party Name	State	GST NO	Reg Type	Product Name	Product Group	
		Unit

		GST Rate	GST Code	Unit Qty	Rate	Value	
		Sale Ledger	Discount	CGST Rate	SGST Rate	IGST Rate	CGST Amt	SGST Amt	IGST Amt	CESS Rate	CESS Amt	
*/
		$this->load->library('excel');
			    $this->excel->setActiveSheetIndex(0);
			    $this->excel->getActiveSheet()->setTitle(lang('gst_report'));
			    $this->excel->getActiveSheet()->SetCellValue('A1', lang('Sale Type'));
			    $this->excel->getActiveSheet()->SetCellValue('B1', lang('Reference no'));
			    $this->excel->getActiveSheet()->SetCellValue('C1', lang('Sale Date'));
			    $this->excel->getActiveSheet()->SetCellValue('D1', lang('Party Name'));
			    $this->excel->getActiveSheet()->SetCellValue('E1', lang('State'));
			    $this->excel->getActiveSheet()->SetCellValue('F1', lang('GST NO'));
			    $this->excel->getActiveSheet()->SetCellValue('G1', lang('Reg Type'));
				
				
			    $this->excel->getActiveSheet()->SetCellValue('H1', lang('Product Name'));
			    $this->excel->getActiveSheet()->SetCellValue('I1', lang('Product Group'));
			    $this->excel->getActiveSheet()->SetCellValue('J1', lang('Unit'));
			    $this->excel->getActiveSheet()->SetCellValue('K1', lang('GST Rate'));
			    $this->excel->getActiveSheet()->SetCellValue('L1', lang('GST Code'));
			    $this->excel->getActiveSheet()->SetCellValue('M1', lang('Unit Qty'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('Rate'));
				$this->excel->getActiveSheet()->SetCellValue('O1', lang('Value'));
				$this->excel->getActiveSheet()->SetCellValue('P1', lang('Sale Ledger'));
				$this->excel->getActiveSheet()->SetCellValue('Q1', lang('Discount'));
				$this->excel->getActiveSheet()->SetCellValue('R1', lang('CGST Rate'));
			    $this->excel->getActiveSheet()->SetCellValue('S1', lang('SGST Rate'));
				$this->excel->getActiveSheet()->SetCellValue('T1', lang('IGST Rate'));
			    
			    $this->excel->getActiveSheet()->SetCellValue('U1', lang('CGST Amt'));
			    $this->excel->getActiveSheet()->SetCellValue('V1', lang('SGST Amt'));
				$this->excel->getActiveSheet()->SetCellValue('W1', lang('IGST Amt'));

		
			    $this->excel->getActiveSheet()->SetCellValue('X1', lang('CESS Rate'));
				 $this->excel->getActiveSheet()->SetCellValue('Y1', lang('CESS Amt'));
                 $this->excel->getActiveSheet()->SetCellValue('Z1', lang('Rounding'));

        if ($start_date && $end_date) {



            $start_date = $end_date ? $this->sma->fld($start_date) : date('Y-m-d');
            $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
            $pp = " {$this->db->dbprefix('sales')}.date >= '{$start_date}' AND {$this->db->dbprefix('sales')}.date < '{$end_date}' ";
        }

         $p='(SELECT sale_id,GROUP_CONCAT( paid_by SEPARATOR "," ) as pays FROM sma_payments';
               

               $q=' GROUP BY sale_id ) as  FI';
               

                if ($start_date && $end_date) {
               $p.="  where  sale_id in (select id from sma_sales where ".$pp.") ";

               }
               $p.=$q;

        if ($pdf || $xls){

            $totalRound=0;

             $qp="select sum(COALESCE(rounding,0)) totrnd from sma_sales ";

              if ($start_date && $end_date) {
               $qp.="   where ".$pp." ";

               }
               
              

               $qq=$this->db->query($qp);
               if($qq->num_rows()>0)
               {
                  

                  if($qq->row()->totrnd)
                  {
                    $totalRound=$qq->row()->totrnd;
                  }
               }

             $ptypes=array(
                'cash',
                'gift_card',
                'Online Payment',
                'Phone Pe',
                'Cheque',
                'CC' );

          

            // $this->db
            //     ->select("'Sales' as Saletype,reference_no,DATE_FORMAT(date, '%d/%m/%Y') as Sale_Date,'customer' as Party_Name,'Maharashtra' as State,'' as GST_NO,sale_items.product_name,'' as ProdGroup,'' as Unit,sale_items.tax_rate_id as GST_Rate,sale_items.gst_code as GSTCode,sale_items.quantity as Unit_Qty,sale_items.unit_price as Rate,sale_items.subtotal as Value,'' as Sales_Ledger,sale_items.item_discount as Discount,sale_items.cgst_rate,sale_items.sgst_rate, sale_items.igst_rate,sale_items.cgst_amt,sale_items.sgst_amt,sale_items.igst_amt,sale_items.cess_rate,sale_items.cess_amt")
            //     ->from('sales')
            //     ->join('sale_items', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id")
            //     ->where($pp);

             $this->db
                ->select("{$this->db->dbprefix('sales')}.id,'Sales' as Saletype,reference_no,DATE_FORMAT(date, '%d/%m/%Y') as sale_date,


                   (
                    CASE WHEN pos = 0  THEN customer

                     WHEN FI.pays = 'cash'  THEN 'cash'
                      WHEN FI.pays= 'Online Payment'  THEN 'Online Payment'
                      WHEN FI.pays = 'Phone pe'  THEN 'Phone pe'
                      WHEN FI.pays = 'gift_card'  THEN 'Gift card'
                      WHEN FI.pays = 'other'  THEN 'Other'
                      WHEN FI.pays = 'CC'  THEN 'Credit Card'   
                      WHEN FI.pays = 'Cheque'  THEN 'Cheque' 
                     ELSE  'customer'
                     end 
                 )

                    as party



                    ,'Maharashtra' as State,'' as vat_no ,

                      (
                    CASE WHEN pos = 0  THEN 'regular'

                    
                     ELSE  'Unregistered / Consumer'
                     end 
                 )

                    as reg_type


                    ,sale_items.product_name,'' as ProdGroup,'' as Unit,sale_items.tax_rate_id as GST_Rate,sale_items.gst_code as GSTCode,sale_items.quantity as Unit_Qty,sale_items.unit_price as Rate,sale_items.subtotal as Value,'' as Sales_Ledger,sale_items.item_discount as Discount,sale_items.cgst_rate,sale_items.sgst_rate, sale_items.igst_rate,sale_items.cgst_amt,sale_items.sgst_amt,sale_items.igst_amt,sale_items.cess_rate,sale_items.cess_amt,

                    pos,rounding,customer_id
                    


                    ")
                ->from('sales')
                ->join('sale_items', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id",'inner')
                ->join($p,"FI.sale_id={$this->db->dbprefix('sales')}.id",'left')
            

                ->where($pp);


            $q = $this->db->get();
			$old_id=0;
			$rounding=0;
            if ($q->num_rows() > 0) {

//echo "string".$q->num_rows();die();
                 $row = 2;
                foreach ($q->result() as $data_row) {
                    

                  
					if($old_id!=$data_row->id)
					{
						$old_id=$data_row->id;
						$rounding=$data_row->rounding;
						
					}
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->Saletype);
				    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
				    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->sale_date);
				    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->party);
				    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->State);

                    if($data_row->pos==1)
                    {
                        $vat_no=$this->site->getVatByID($data_row->customer_id);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $vat_no?$vat_no:"");
                    }
                    else
                    {

					$this->excel->getActiveSheet()->SetCellValue('F' . $row, "");
                    }
				    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->reg_type);
					
					 $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->product_name);
				    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->ProdGroup);
				    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->Unit);
				    $this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->GST_Rate);
				    $this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->GSTCode);
				    $this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->Unit_Qty);
				
				    $this->excel->getActiveSheet()->SetCellValue('N' . $row, $data_row->Rate);
				    $this->excel->getActiveSheet()->SetCellValue('O' . $row, $data_row->Value);
				    $this->excel->getActiveSheet()->SetCellValue('P' . $row, $data_row->Sales_Ledger);
				    $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $data_row->Discount);
					
				    $this->excel->getActiveSheet()->SetCellValue('R' . $row, $data_row->cgst_rate);
				    $this->excel->getActiveSheet()->SetCellValue('S' . $row, $data_row->sgst_rate);
					$this->excel->getActiveSheet()->SetCellValue('T' . $row, $data_row->igst_rate);
					
					 $this->excel->getActiveSheet()->SetCellValue('U' . $row, $data_row->cgst_amt);
				    $this->excel->getActiveSheet()->SetCellValue('V' . $row, $data_row->sgst_amt);
					$this->excel->getActiveSheet()->SetCellValue('W' . $row, $data_row->igst_amt);
				
				
					 $this->excel->getActiveSheet()->SetCellValue('X' . $row, $data_row->cess_rate);
				    $this->excel->getActiveSheet()->SetCellValue('Y' . $row, $data_row->cess_amt);
					$this->excel->getActiveSheet()->SetCellValue('Z' . $row, $rounding);
					$rounding='';
                    $row++;
				}
				$uppercase = range('A', 'Z');
				foreach($uppercase as $letter)
				{
					  $this->excel->getActiveSheet()->getColumnDimension($letter)->setWidth(35);
				}
				
                $this->excel->getActiveSheet()->SetCellValue('A' . $row, $rounding);

                    foreach ($ptypes as $pt) {
                        # code...
                         $row++;
                         $parr=$this->reports_model->getAllPayment($start_date, $end_date,$pt);
                       
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, 'Total '.$pt ." : ".($parr?$parr->total_amount:"0.0") );
                    } 

				$filename = 'gst_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            } else {
                 $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
            }
           
        } else {

           
            $this->load->library('datatables');
            $this->datatables
                ->select("{$this->db->dbprefix('sales')}.id,'Sales' as Saletype,reference_no,DATE_FORMAT(date, '%d/%m/%Y') as Sale_Date,


                   (
                    CASE WHEN pos = 0  THEN customer

                     WHEN FI.pays = 'cash'  THEN 'cash'
                      WHEN FI.pays= 'Online Payment'  THEN 'Online Payment'
                      WHEN FI.pays = 'Phone pe'  THEN 'Phone pe'
                      WHEN FI.pays = 'gift_card'  THEN 'Gift card'
                      WHEN FI.pays = 'other'  THEN 'Other'
                      WHEN FI.pays = 'CC'  THEN 'Credit Card'   
                      WHEN FI.pays = 'Cheque'  THEN 'Cheque' 
                     ELSE  'customer'
                     end 
                 )

                    as party



                    ,'Maharashtra' as State,'' as GST_NO,sale_items.product_name,'' as ProdGroup,'' as Unit,sale_items.tax_rate_id as GST_Rate,sale_items.gst_code as GSTCode,sale_items.quantity as Unit_Qty,sale_items.unit_price as Rate,sale_items.subtotal as Value,'' as Sales_Ledger,sale_items.item_discount as Discount,sale_items.cgst_rate,sale_items.sgst_rate, sale_items.igst_rate,sale_items.cgst_amt,sale_items.sgst_amt,sale_items.igst_amt,sale_items.cess_rate,sale_items.cess_amt,

                    pos
                    


                    ")
                ->from('sales')
                ->join('sale_items', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id",'inner')
                ->join($p,"FI.sale_id={$this->db->dbprefix('sales')}.id",'left')
                ->where($pp);


            $this->datatables->unset_column("{$this->db->dbprefix('sales')}.id");
            echo $this->datatables->generate();

        }

    }
     function getGSTReport1($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        // $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : date('Y-m-d 00:00');
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : date('Y-m-d 11:59');


        if ($start_date && $end_date) {



            $start_date = $end_date ? $this->sma->fld($start_date) : date('Y-m-d');
            $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
            $pp = " {$this->db->dbprefix('sales')}.date >= '{$start_date}' AND {$this->db->dbprefix('sales')}.date < '{$end_date}' ";
        }
        if ($pdf || $xls){

            $this->db
                ->select("'Sales' as Saletype,reference_no,DATE_FORMAT(date, '%d/%m/%Y') as Sale_Date,'customer' as Party_Name,'Maharashtra' as State,'' as GST_NO,sale_items.product_name,'' as ProdGroup,'' as Unit,sale_items.tax_rate_id as GST_Rate,sale_items.gst_code as GSTCode,sale_items.quantity as Unit_Qty,sale_items.unit_price as Rate,sale_items.subtotal as Value,'' as Sales_Ledger,sale_items.item_discount as Discount,sale_items.cgst_rate,sale_items.sgst_rate, sale_items.igst_rate,sale_items.cgst_amt,sale_items.sgst_amt,sale_items.igst_amt,sale_items.cess_rate,sale_items.cess_amt")
                ->from('sales')
                ->join('sale_items', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id")
                ->where($pp);


            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result_array()) as $row) {
                    $data[] = $row;
                }

            } else {
                $data = NULL;
            }
            if (!empty($data)) {
                $sdate = str_replace(' ','_',$start_date);
                $edate = str_replace(' ','_',$end_date);

                $filename = "gst_report_{$sdate}_To_{$edate}.csv";
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=$filename");
                header("Content-Type: application/csv; ");
                $file = fopen('php://output', 'w');
                $header = array("Sale Type","Reference No","Sale Date","Party Name","State","GST NO","Product Name","Product Group","Unit","GST Rate","GST Code","Unit Qty","Rate","Value","Sale Ledger","Discount","CGST Rate","SGST Rate","IGST Rate","CGST Amt","SGST Amt","IGST Amt","CESS Rate","CESS Amt");
                fputcsv($file, $header);
                foreach ($data as $key=>$line){
                    fputcsv($file,$line);
                }
                fclose($file);
                exit;
            }
        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select("{$this->db->dbprefix('sales')}.id,'Sales' as Saletype,reference_no,DATE_FORMAT(date, '%d/%m/%Y') as Sale_Date,'customer' as Party_Name,'Maharashtra' as State,'' as GST_NO,sale_items.product_name,'' as ProdGroup,'' as Unit,sale_items.tax_rate_id as GST_Rate,sale_items.gst_code as GSTCode,sale_items.quantity as Unit_Qty,sale_items.unit_price as Rate,sale_items.subtotal as Value,'' as Sales_Ledger,sale_items.item_discount as Discount,sale_items.cgst_rate,sale_items.sgst_rate, sale_items.igst_rate,sale_items.cgst_amt,sale_items.sgst_amt,sale_items.igst_amt,sale_items.cess_rate,sale_items.cess_amt")
                ->from('sales')
                ->join('sale_items', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id",'inner')
                ->where($pp);


            $this->datatables->unset_column("{$this->db->dbprefix('sales')}.id");
            echo $this->datatables->generate();

        }

    }
//Tax Rate GST Report
    function tax_rate_gst()
    {
        //$this->sma->checkPermissions('products');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('Tax_Rate_GST_Report')));
        $meta = array('page_title' => lang('Tax_Rate_GST_Report'), 'bc' => $bc);
        $this->page_construct('reports/tax_rate_gst', $meta, $this->data);
    }

   
     function gettax_rate_gstReport($pdf = NULL, $xls = NULL)
    {

	    $this->sma->checkPermissions('products', TRUE);
	    $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : date('Y-m-d 00:00');
	    $end_date = $this->input->get('end_date') ? $this->input->get('end_date') :date('Y-m-d 11:59');

	    if ($start_date && $end_date) {
		    $start_date = $end_date ? $this->sma->fld($start_date) : date('Y-m-d');
		    $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
		    $pp = " {$this->db->dbprefix('sales')}.date >= '{$start_date}' AND {$this->db->dbprefix('sales')}.date < '{$end_date}' ";
	    }

	    if ($pdf || $xls) {
		    $this->db
			    ->select("DATE({$this->db->dbprefix('sales')}.date) AS date,SUM(subtotal) as Total,({$this->db->dbprefix('sale_items')}.cgst_rate * 2) as tax_rate, SUM(cgst_amt) as CGST,SUM(sgst_amt) as SGST,SUM(igst_amt) as IGST,SUM(cess_amt) as CESS")
			    ->from('sale_items')
			    ->join('sales', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id",'inner')
			    ->where($pp)
			    //  ->group_by('cgst_rate');
			    ->group_by(array("DATE({$this->db->dbprefix('sales')}.date)","cgst_rate"));

		    $q = $this->db->get();
		    if ($q->num_rows() > 0) {
			    foreach (($q->result()) as $row) {
				    $data[] = $row;
			    }
		    } else {
			    $data = NULL;
		    }

		    if (!empty($data)) {
			    $this->load->library('excel');
			    $this->excel->setActiveSheetIndex(0);
			    $this->excel->getActiveSheet()->setTitle(lang('tax_rate_gst_report'));
			    $this->excel->getActiveSheet()->SetCellValue('A1', lang('Date'));
			    $this->excel->getActiveSheet()->SetCellValue('B1', lang('total'));
			    $this->excel->getActiveSheet()->SetCellValue('C1', lang('tax_rates'));
			    $this->excel->getActiveSheet()->SetCellValue('D1', lang('CGST'));
			    $this->excel->getActiveSheet()->SetCellValue('E1', lang('SGST'));
			    $this->excel->getActiveSheet()->SetCellValue('F1', lang('IGST'));
			    $this->excel->getActiveSheet()->SetCellValue('G1', lang('CESS'));

			    $row = 2;
			    $total = 0;
			    $cgst = 0;
			    $sgst = 0;
			    $igst = 0;
			    $cess = 0;

			    foreach ($data as $data_row) {
				    $this->excel->getActiveSheet()->SetCellValue('A' . $row, date('d/m/Y',strtotime($data_row->date)));

				    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->Total);
				    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->tax_rate);
				    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->CGST);
				    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->SGST);
				    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->IGST);
				    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->CESS);

				    $total += $data_row->Total;
				    $cgst  += $data_row->CGST;
				    $sgst += $data_row->SGST;
				    $igst += $data_row->IGST;
				    $cess += $data_row->CESS;
				    $row++;
			    }
			    $this->excel->getActiveSheet()->getStyle("B" . $row . ":D" . $row)->getBorders()
			                ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
			    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $total);
			    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $cgst);
			    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sgst);
			    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $igst);
			    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $cess);

			    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
			    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
			    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
			    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
			    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
			    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
			    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);

			    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			    $this->excel->getActiveSheet()->getStyle('C2:D' . $row)->getAlignment()->setWrapText(true);
			    $filename = 'tax_rate_gst_report' . $start_date . 'To' . $end_date;
			    $this->load->helper('excel');
			    return create_excel($this->excel, $filename);

		    }
		    $this->session->set_flashdata('error', lang('nothing_found'));
		    redirect($_SERVER["HTTP_REFERER"]);

	    } else {
		    $this->load->library('datatables');
		    $this->datatables
			    ->select("DATE({$this->db->dbprefix('sales')}.date) AS date,SUM(subtotal) as Total,({$this->db->dbprefix('sale_items')}.cgst_rate * 2),SUM(cgst_amt) as CGST,SUM(sgst_amt) as SGST,SUM(igst_amt) as IGST,SUM(cess_amt) as CESS")
			    ->from('sale_items')
			    ->join('sales', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id",'left')
			    ->where($pp)
			    ->group_by(array("DATE({$this->db->dbprefix('sales')}.date)","cgst_rate"));
		    //->group_by('cgst_rate');

		    //$this->datatables->unset_column("{$this->db->dbprefix('sale_items')}.sale_id");
		    echo $this->datatables->generate();

	    }

    }

	function daywise_profit(){


		$this->sma->checkPermissions('products');
		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

		if ($this->input->post('start_date') && $this->input->post('end_date')) {
			$dt = "From " . $this->input->post('start_date') ."To ". $this->input->post('end_date') ;
		}

		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('Daywise_Profit')));
		$meta = array('page_title' => lang('Daywise_Profit'), 'bc' => $bc);
		$this->page_construct('reports/daywise_profit', $meta, $this->data);

	}

	function getDaywiseProfitLossReport($pdf = NULL, $xls = NULL) {
		$this->sma->checkPermissions( 'sales', true );

		$start_date = $this->input->get( 'start_date' ) ? $this->input->get( 'start_date' ) : null;
		$end_date   = $this->input->get( 'end_date' ) ? $this->input->get( 'end_date' ) : null;
		if ( $start_date && $end_date ):

			$start_date = date( 'Y-m-d', strtotime( str_replace( '/', '-', $start_date ) ) );
			$end_date   = date( 'Y-m-d', strtotime( str_replace( '/', '-', $end_date ) ) );

			if ($pdf==NULL and $xls==NULL) :
				$this->db->query( "drop table {$this->db->dbprefix('temp_daywise_profit')}" );
				$this->db->query( "create table {$this->db->dbprefix('temp_daywise_profit')}  SELECT DATE_FORMAT(s.date,'%Y-%m-%d') as date,sum(quantity) as saleQty,sum(i.subtotal) saletotal,(a.avg_purchase_price*sum(quantity)) as purchasetotal FROM {$this->db->dbprefix('sale_items')} i inner join {$this->db->dbprefix('sales')} s on s.id=i.sale_id inner join {$this->db->dbprefix('avg_prprice1')} a on a.product_id=i.product_id where date between '{$start_date} 00:00:00' AND '{$end_date} 23:59:59' group by i.product_id,date(date) order by date asc" );
			endif;
		else:
			$this->db->query( "truncate table {$this->db->dbprefix('temp_daywise_profit')}" );
		endif;
//

		if ($pdf || $xls) {

			$this->db
				->select("DATE_FORMAT(date,'%d-%m-%Y') as date,SUM(saletotal) as sale_total,SUM(purchasetotal) as purchase_total,(sum(saletotal)-sum(purchasetotal)) as totalprofit", FALSE)
				->from('temp_daywise_profit')
				->group_by('date')
				->order_by('date');

			$q = $this->db->get();
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			} else {
				$data = NULL;
			}

			if (!empty($data)) {

				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('Daywise'.date('d-m-Y',strtotime($start_date)).lang('-'.date('d-m-Y',strtotime($end_date)))));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('Date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('Total_Sale'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('Purchase_Total'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('Total_Profit'));

				$row = 2;


				$sTot = 0;
				$pTot = 0;
				$tProf = 0;
				foreach ($data as $data_row) {

					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->date);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->sale_total);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->purchase_total);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->totalprofit);


					$sTot += $data_row->sale_total;
					$pTot += $data_row->purchase_total;
					$tProf += $data_row->totalprofit;
					$row++;
				}
				$this->excel->getActiveSheet()->getStyle("B" . $row . ":D" . $row)->getBorders()
				            ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
				$this->excel->getActiveSheet()->SetCellValue('B' . $row, $sTot);
				$this->excel->getActiveSheet()->SetCellValue('C' . $row, $pTot);
				$this->excel->getActiveSheet()->SetCellValue('D' . $row, $tProf);


				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);


				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B2:G' . $row)->getAlignment()->setWrapText(true);
				$filename = 'daywise_profit_'.date('d-m-Y',strtotime($start_date)).lang(' TO '.date('d-m-Y',strtotime($end_date)));
				$this->load->helper('excel');
				// print_r($this->excel);die;
				return create_excel($this->excel, $filename);

			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			redirect($_SERVER["HTTP_REFERER"]);

		} else {

			$this->load->library('datatables');

			$this->datatables
				->select("DATE_FORMAT(date,'%d-%m-%Y') as date,SUM(saletotal) as sale_total,SUM(purchasetotal) as purchase_total,(sum(saletotal)-sum(purchasetotal)) as totalprofit", FALSE)
				->from('temp_daywise_profit')
				->group_by('date');
			// ->order('date');

			echo $this->datatables->generate();

		}


	}

    function points_history($warehouse_id = NULL)
    {
       
        $cust_id=NULL;
        $start_date=NULL;
        $end_date=date('Y-m-d');
       


        if ($this->input->post('customer')) {
            //echo "user ".$this->input->post('user');
             $cust_id=$this->input->post('customer');
        }


        if ($this->input->post('start_date')) {
            //echo "for_month ".$this->input->post('for_month');
            $start_date=$this->input->post('start_date');
            $start_date = $this->sma->fld($start_date);
        }
        if ($this->input->post('end_date')) {
            //echo "for_month ".$this->input->post('for_month');
            $end_date=$this->input->post('end_date');
            $end_date=$this->sma->fld($end_date);
        }
        $this->data['report']=$this->reports_model->getPointsHistory($cust_id,$start_date,$end_date,NULL,NULL);
        //echo json_encode($this->data['report']);
        //$this->data['users']=$this->companies_model->getAllCustomers();
        $this->sma->checkPermissions('points_history');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $user = $this->site->getUser();
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $user->warehouse_id;
            $this->data['warehouse'] = $user->warehouse_id ? $this->site->getWarehouseByID($user->warehouse_id) : NULL;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('points_history')));
        $meta = array('page_title' => lang('points_history'), 'bc' => $bc);
        $this->page_construct('reports/points_history', $meta, $this->data);
    }

    

     public function points_history_export($V=NULL)
    {
        //echo "val ".$this->input->post('chkvals');die();
    
         $this->data['report'] = $this->reports_model->getPointsHistory(NULL,NULL,NULL,TRUE,$this->input->post('chkvals'));
        
         //echo json_encode($this->data['report']);die;
         //$this->page_construct('stocktake/consolidatedreport', $meta, $this->data);

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('Points History Report'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('points'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('created_date'));
                   

                    $row = 2;
                    foreach ($this->data['report'] as $report) {

                                   
                       
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $report->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $report->Customer);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $report->Points);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row,$report->created_date);
                       
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'points_history_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    return create_excel($this->excel, $filename);

    }

      public function consolidated()
    {
           $this->sma->checkPermissions('index');
       
            $start_date = $this->input->post('start_date') ? $this->input->post('start_date') : NULL;
            $end_date = $this->input->post('end_date') ? $this->input->post('end_date') : NULL;   
           
            $this->data['conssales'] = $this->pos_model->getPaymentTypeWiseSales($start_date,$end_date);
            $this->data['totsales'] = $this->pos_model->getTotalSalesByDate($start_date,$end_date);
            $this->data['totrefunds'] = $this->pos_model->getRefundsByDate($start_date,$end_date);
            $this->data['tot_expenses'] = $this->pos_model->getExpensesByDate($start_date,$end_date);

            $this->data['tot_cash_ref'] = $this->pos_model->getCashRefundsByDate($start_date,$end_date);
            $this->data['tot_cih'] = $this->pos_model->getCashInHandByDate($start_date,$end_date);
            $this->data['tot_cc_slips'] = $this->pos_model->getTotalCCSlipsByDate($start_date,$end_date);
            $this->data['total_cash']='0.00';
            $this->data['total_cc']='0.00';
            $this->data['total_gc']='0.00';
            $this->data['total_op']='0.00';
            $this->data['total_other']='0.00';
            $this->data['total_pp']='0.00';

            if($this->data['conssales'])
            {
                $s=$this->data['conssales'];
                foreach ($s as $t) {
                    # code...
                    if($t->paid_by=='cash')
                    {
                        $this->data['total_cash']=$t->paid;
                    }
                     if($t->paid_by=='CC')
                    {
                        $this->data['total_cc']=$t->paid;
                    }
                    if($t->paid_by=='gift_card')
                    {
                        $this->data['total_gc']=$t->paid;
                    }
                     if($t->paid_by=='Online Payment')
                    {
                        $this->data['total_op']=$t->paid;
                    }
                    if($t->paid_by=='other')
                    {
                        $this->data['total_other']=$t->paid;
                    }
                     if($t->paid_by=='Phone Pe')
                    {
                        $this->data['total_pp']=$t->paid;
                    }
                }

            }
           
            
            $this->data['user_id'] = $this->session->userdata('user_id');
            $this->data['modal_js'] = $this->site->modal_js();
            /*echo "string conssales <br/>".json_encode($this->data['conssales']);
            echo "string totsales<br/>".json_encode($this->data['totsales']);
            echo "string totrefunds<br/>".json_encode($this->data['totrefunds']);
            echo "string tot_expenses<br/>".json_encode($this->data['tot_expenses']);
            echo "string tot_cash_ref<br/>".json_encode($this->data['tot_cash_ref']);
            echo "string tot_cih<br/>".json_encode($this->data['tot_cih']);
                */
           // die();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('consolidated_report')));
        $meta = array('page_title' => lang('consolidated_report'), 'bc' => $bc);
        $this->page_construct('reports/viewall', $meta, $this->data);
           // $this->load->view($this->theme . 'reports/viewall', $this->data);
    }

     function advproducts()
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['brands'] = $this->site->getAllBrands();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => 'Advanced '.lang('products_report')));
        $meta = array('page_title' => 'Advanced '.lang('products_report'), 'bc' => $bc);
        $this->page_construct('reports/advproducts', $meta, $this->data);
    }

    function getAdvProductsReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);

        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $brand = $this->input->get('brand') ? $this->input->get('brand') : NULL;
        $subcategory = $this->input->get('subcategory') ? $this->input->get('subcategory') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $cf1 = $this->input->get('cf1') ? $this->input->get('cf1') : NULL;
        $cf2 = $this->input->get('cf2') ? $this->input->get('cf2') : NULL;
        $cf3 = $this->input->get('cf3') ? $this->input->get('cf3') : NULL;
        $cf4 = $this->input->get('cf4') ? $this->input->get('cf4') : NULL;
        $cf5 = $this->input->get('cf5') ? $this->input->get('cf5') : NULL;
        $cf6 = $this->input->get('cf6') ? $this->input->get('cf6') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        $pp = "( SELECT product_id, SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN quantity ELSE 0 END) as purchasedQty, SUM(pi.quantity) as balacneQty, AVG(COALESCE(pi.unit_cost,0) ) as balacneValue, SUM( (CASE WHEN pi.purchase_id IS NOT NULL THEN (pi.subtotal) ELSE 0 END) ) totalPurchase from {$this->db->dbprefix('purchase_items')} pi inner JOIN {$this->db->dbprefix('purchases')} p on p.id = pi.purchase_id ";
        $sp = "( SELECT si.product_id, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale,
        AVG(COALESCE(si.unit_price,0) ) as avgPrice
         from " . $this->db->dbprefix('sales') . " s JOIN " . $this->db->dbprefix('sale_items') . " si on s.id = si.sale_id ";

         $ai = "( SELECT product_id, SUM(CASE WHEN ai.type='addition' THEN quantity ELSE 0 END) as plusQty,SUM(CASE WHEN ai.type='subtraction' THEN quantity ELSE 0 END) as damageQty,'0' as shopLiftingQty from {$this->db->dbprefix('adjustment_items')} ai left join {$this->db->dbprefix('adjustments')} ad on ai.adjustment_id=ad.id ";
        if ($start_date || $warehouse) {
            $pp .= " WHERE ";
            $sp .= " WHERE ";
            $ai .= " WHERE ";
            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                $pp .= " p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
                $ai .= " ad.date >= '{$start_date}' AND ad.date < '{$end_date}' ";
                if ($warehouse) {
                    $pp .= " AND ";
                    $sp .= " AND ";
                    $ai .= " AND ";
                }
            }
            if ($warehouse) {
                $pp .= " pi.warehouse_id = '{$warehouse}' ";
                $sp .= " si.warehouse_id = '{$warehouse}' ";
                $ai .= " ai.warehouse_id = '{$warehouse}' ";
            }
        }
        $pp .= " GROUP BY pi.product_id ) PCosts";
        $sp .= " GROUP BY si.product_id ) PSales";
        $ai .= " GROUP BY ai.product_id ) PPlusMinus";
        if ($pdf || $xls) {

           /* $this->db
                ->select($this->db->dbprefix('products') . ".code, " . $this->db->dbprefix('products') . ".name,
                COALESCE( PCosts.purchasedQty, 0 ) as PurchasedQty,
                COALESCE( PSales.soldQty, 0 ) as SoldQty,
                
                COALESCE( PCosts.balacneQty-PSales.soldQty, 0 ) as BalacneQty,
                COALESCE( PCosts.totalPurchase, 0 ) as TotalPurchase,
                COALESCE( (PCosts.balacneQty-PSales.soldQty)* PCosts.balacneValue, 0 ) as TotalBalance,
                COALESCE( PSales.totalSale, 0 ) as TotalSales,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )) as Profit", FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
                ->order_by('products.name');*/

                  $this->db->select($this->db->dbprefix('products') . ".code, "
                 . $this->db->dbprefix('products') . ".name,"
                 . $this->db->dbprefix('products') . ".quantity,
                (COALESCE( PCosts.purchasedQty, 0 )) as purchasedQty ,
                 COALESCE( PCosts.totalPurchase, 0 ) as purchasedCost,
                (COALESCE( PSales.soldQty, 0 )) as soldQty,
                 (COALESCE( PSales.totalSale, 0 )) as soldAmt,

                (COALESCE( (PSales.soldQty*(PSales.avgPrice-PCosts.balacneValue) ),0)) AS Profit, 

                ( (COALESCE( PCosts.purchasedQty, 0 ))-(COALESCE( PSales.soldQty, 0 )) ) as stockQty,

                 COALESCE(  ((COALESCE( PCosts.purchasedQty, 0 ))-(COALESCE( PSales.soldQty, 0 ))

                 )* PCosts.balacneValue, 0 )  as stockAmt,

                   COALESCE(PPlusMinus.damageQty,0) as DamageQty, 
                   
                   COALESCE( COALESCE(PPlusMinus.damageQty,0)* COALESCE(PCosts.balacneValue,0), 0 ) as DamageAmt, 
                   

                   COALESCE(PPlusMinus.plusQty,0) as PlusQty, 

                    COALESCE( COALESCE(PPlusMinus.plusQty,0)* COALESCE(PCosts.balacneValue,0), 0 ) as PlusAmt, 

                   COALESCE(PPlusMinus.shopLiftingQty,0) as shopLiftingQty, 

                   0 as shopLiftingAmt,
                  ((COALESCE( PCosts.purchasedQty, 0 )-CONCAT(COALESCE( PSales.soldQty, 0 )))- COALESCE(PPlusMinus.damageQty,0) + COALESCE(PPlusMinus.plusQty,0)) AS Avilable_Stock, 

                 {$this->db->dbprefix('products')}.id as id", FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
                ->join($ai, 'products.id = PPlusMinus.product_id', 'left')
                ->group_by('products.code, PSales.soldQty, PSales.totalSale, PCosts.purchasedQty, PCosts.totalPurchase, PCosts.balacneQty, PCosts.balacneValue,PPlusMinus.damageQty,PPlusMinus.plusQty,PPlusMinus.shopLiftingQty');

            if ($product) {
                $this->db->where($this->db->dbprefix('products') . ".id", $product);
            }
            if ($cf1) {
                $this->db->where($this->db->dbprefix('products') . ".cf1", $cf1);
            }
            if ($cf2) {
                $this->db->where($this->db->dbprefix('products') . ".cf2", $cf2);
            }
            if ($cf3) {
                $this->db->where($this->db->dbprefix('products') . ".cf3", $cf3);
            }
            if ($cf4) {
                $this->db->where($this->db->dbprefix('products') . ".cf4", $cf4);
            }
            if ($cf5) {
                $this->db->where($this->db->dbprefix('products') . ".cf5", $cf5);
            }
            if ($cf6) {
                $this->db->where($this->db->dbprefix('products') . ".cf6", $cf6);
            }
            if ($category) {
                $this->db->where($this->db->dbprefix('products') . ".category_id", $category);
            }
            if ($subcategory) {
                $this->db->where($this->db->dbprefix('products') . ".subcategory_id", $subcategory);
            }
            if ($brand) {
                $this->db->where($this->db->dbprefix('products') . ".brand", $brand);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }


                    $pQty = 0;
                    $pAmt = 0;
                    $sQty = 0;
                    $sAmt = 0;
                    $pl = 0;
                    $stockQty = 0;
                    $stockAmt =0;
                    $dQty = 0;
                    $dAmt = 0;

                     $plusQty = 0;
                    $PlusAmt =0;

                     $slQty = 0;
                    $slAmt = 0;
                    $totalQty = 0;

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('products_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('products_master_quantity'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('purchased_qty'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('purchased_amt'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('sold_qty'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('sold_amt'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('profit_loss'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('stock_in_hand_qty'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('stock_in_hand_amt'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('damage_qty'));
                $this->excel->getActiveSheet()->SetCellValue('L1', lang('damage_amt'));
                $this->excel->getActiveSheet()->SetCellValue('M1', lang('qty_plus'));
                $this->excel->getActiveSheet()->SetCellValue('N1', lang('qty_plus_amt'));
                $this->excel->getActiveSheet()->SetCellValue('O1', lang('shop_lifting_qty'));
                $this->excel->getActiveSheet()->SetCellValue('P1', lang('shop_lifting_amt'));
                $this->excel->getActiveSheet()->SetCellValue('Q1', lang('avil_stock_qty'));

                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $sAmt = 0;
                $pAmt = 0;
                $bQty = 0;
                $bAmt = 0;
                $pl = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                     $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->quantity);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->purchasedQty);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->purchasedCost);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->soldQty);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->soldAmt);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->Profit);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->stockQty);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->stockAmt);
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->DamageQty);
                    $this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->DamageAmt);
                    
                    $this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->PlusQty);
                    $this->excel->getActiveSheet()->SetCellValue('N' . $row, $data_row->PlusAmt);
                    $this->excel->getActiveSheet()->SetCellValue('O' . $row, $data_row->shopLiftingQty);
                    $this->excel->getActiveSheet()->SetCellValue('P' . $row, $data_row->shopLiftingAmt);
                    $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $data_row->Avilable_Stock);
                
                    $pQty += $data_row->purchasedQty;
                    $pAmt += $data_row->purchasedCost;
                    $sQty += $data_row->soldQty;
                    $sAmt += $data_row->soldAmt;
                    $pl += $data_row->Profit;
                    $stockQty += $data_row->stockQty;
                    $stockAmt += $data_row->stockAmt;
                    $dQty += $data_row->DamageQty;
                    $dAmt += $data_row->DamageAmt;

                     $plusQty += $data_row->PlusQty;
                    $PlusAmt += $data_row->PlusAmt;

                     $slQty += $data_row->shopLiftingQty;
                    $slAmt += $data_row->shopLiftingAmt;
                    $totalQty += $data_row->Avilable_Stock;

                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("C" . $row . ":P" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $pAmt);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $sAmt);

                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $pl);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $stockQty);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $stockAmt);
                $this->excel->getActiveSheet()->SetCellValue('J' . $row, $dQty);
                $this->excel->getActiveSheet()->SetCellValue('K' . $row, $dAmt);
                $this->excel->getActiveSheet()->SetCellValue('L' . $row, $plusQty);
                $this->excel->getActiveSheet()->SetCellValue('M' . $row, $PlusAmt);
                $this->excel->getActiveSheet()->SetCellValue('N' . $row, $slQty);
                $this->excel->getActiveSheet()->SetCellValue('O' . $row, $slAmt);
                $this->excel->getActiveSheet()->SetCellValue('P' . $row, $totalQty);

                

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
                 $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(25);
                 $this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(25);
                
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('C2:O' . $row)->getAlignment()->setWrapText(true);
                $filename = 'products_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('products') . ".code, "
                 . $this->db->dbprefix('products') . ".name,"
                 . $this->db->dbprefix('products') . ".quantity,
                (COALESCE( PCosts.purchasedQty, 0 )) as purchasedQty ,
                 COALESCE( PCosts.totalPurchase, 0 ) as purchasedCost,
                (COALESCE( PSales.soldQty, 0 )) as soldQty,
                 (COALESCE( PSales.totalSale, 0 )) as soldAmt,

               (COALESCE( (PSales.soldQty*(PSales.avgPrice-PCosts.balacneValue) ),0)) AS Profit, 


                ( (COALESCE( PCosts.purchasedQty, 0 ))-(COALESCE( PSales.soldQty, 0 )) ) as stockQty,

                 COALESCE(  ((COALESCE( PCosts.purchasedQty, 0 ))-(COALESCE( PSales.soldQty, 0 ))

                 )* PCosts.balacneValue, 0 )  as stockAmt,

                   COALESCE(PPlusMinus.damageQty,0) as DamageQty, 
                   
                   COALESCE( COALESCE(PPlusMinus.damageQty,0)* COALESCE(PCosts.balacneValue,0), 0 ) as DamageAmt, 
                   

                   COALESCE(PPlusMinus.plusQty,0) as PlusQty, 

                    COALESCE( COALESCE(PPlusMinus.plusQty,0)* COALESCE(PCosts.balacneValue,0), 0 ) as PlusAmt, 

                   COALESCE(PPlusMinus.shopLiftingQty,0), 

                   0 as shopLiftingAmt,
                  ((COALESCE( PCosts.purchasedQty, 0 )-CONCAT(COALESCE( PSales.soldQty, 0 )))- COALESCE(PPlusMinus.damageQty,0) + COALESCE(PPlusMinus.plusQty,0)) AS Avilable_Stock, 

                 {$this->db->dbprefix('products')}.id as id", FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
                ->join($ai, 'products.id = PPlusMinus.product_id', 'left')
                ->group_by('products.code, PSales.soldQty, PSales.totalSale, PCosts.purchasedQty, PCosts.totalPurchase, PCosts.balacneQty, PCosts.balacneValue,PPlusMinus.damageQty,PPlusMinus.plusQty,PPlusMinus.shopLiftingQty');

            if ($product) {
                $this->datatables->where($this->db->dbprefix('products') . ".id", $product);
            }
            if ($cf1) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf1", $cf1);
            }
            if ($cf2) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf2", $cf2);
            }
            if ($cf3) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf3", $cf3);
            }
            if ($cf4) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf4", $cf4);
            }
            if ($cf5) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf5", $cf5);
            }
            if ($cf6) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf6", $cf6);
            }
            if ($category) {
                $this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
            }
            if ($subcategory) {
                $this->datatables->where($this->db->dbprefix('products') . ".subcategory_id", $subcategory);
            }
            if ($brand) {
                $this->datatables->where($this->db->dbprefix('products') . ".brand", $brand);
            }

            echo $this->datatables->generate();

        }

    }

        //new
     function getSupplierReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('purchases', TRUE);

        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $this->db
                ->select("" . $this->db->dbprefix('purchases') . ".date, reference_no,ext_pur_ref_no," . $this->db->dbprefix('warehouses') . ".name as wname, supplier, GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('purchase_items') . ".product_name, ' (', " . $this->db->dbprefix('purchase_items') . ".quantity, ')') SEPARATOR '\n') as iname, grand_total, paid, " . $this->db->dbprefix('purchases') . ".status", FALSE)
                ->from('purchases')
               
                ->join('purchase_items', 'purchase_items.purchase_id=purchases.id', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
                ->group_by('purchases.id')
                ->order_by('purchases.date desc');

            if ($user) {
                $this->db->where('purchases.created_by', $user);
            }
            if ($product) {
                $this->db->where('purchase_items.product_id', $product);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->db->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('purchase_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('ex_reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('warehouse'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('supplier'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('status'));

                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->ext_pur_ref_no);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->wname);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->supplier);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, ($data_row->grand_total - $data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->status);
                    $total += $data_row->grand_total;
                    $paid += $data_row->paid;
                    $balance += ($data_row->grand_total - $data_row->paid);
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("G" . $row . ":I" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $total);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $paid);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $balance);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                $filename = 'purchase_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $pi = "( SELECT purchase_id, product_id, (GROUP_CONCAT(CONCAT({$this->db->dbprefix('purchase_items')}.product_name, '__', {$this->db->dbprefix('purchase_items')}.quantity) SEPARATOR '___')) as item_name,(GROUP_CONCAT(CONCAT({$this->db->dbprefix('purchase_items')}.product_name, '__', {$this->db->dbprefix('purchase_items')}.real_unit_cost) SEPARATOR '___')) as real_unit_cost from {$this->db->dbprefix('purchase_items')} ";
            if ($product) {
                $pi .= " WHERE {$this->db->dbprefix('purchase_items')}.product_id = {$product} ";
            }
            $pi .= " GROUP BY {$this->db->dbprefix('purchase_items')}.purchase_id ) FPI";

            $this->load->library('datatables');
            $this->datatables
            ->select("DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%Y-%m-%d %T') as date, reference_no, {$this->db->dbprefix('warehouses')}.name as wname, supplier, (FPI.item_name) as iname, grand_total, paid, (grand_total-paid) as balance, {$this->db->dbprefix('purchases')}.status, {$this->db->dbprefix('purchases')}.id as id", FALSE)
               /* ->select("DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%Y-%m-%d %T') as date, reference_no,  ext_pur_ref_no, {$this->db->dbprefix('warehouses')}.name as wname, grand_total, paid, (grand_total-paid) as balance, {$this->db->dbprefix('purchases')}.status, {$this->db->dbprefix('purchases')}.id as id", FALSE)*/
                ->from('purchases')
             
                ->join($pi, 'FPI.purchase_id=purchases.id', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left');
            // ->group_by('purchases.id');

            if ($user) {
                $this->datatables->where('purchases.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FPI.product_id', $product, FALSE);
            }
            if ($supplier) {
                $this->datatables->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->datatables->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }

//products reports
      function slowproducts()
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['brands'] = $this->site->getAllBrands();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('slowproducts_report')));
        $meta = array('page_title' => lang('slowproducts_report'), 'bc' => $bc);
        $this->page_construct('reports/slowproducts', $meta, $this->data);
    }

    function getSlowProductsReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);

        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $brand = $this->input->get('brand') ? $this->input->get('brand') : NULL;
        $subcategory = $this->input->get('subcategory') ? $this->input->get('subcategory') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $cf1 = $this->input->get('cf1') ? $this->input->get('cf1') : NULL;
        $cf2 = $this->input->get('cf2') ? $this->input->get('cf2') : NULL;
        $cf3 = $this->input->get('cf3') ? $this->input->get('cf3') : NULL;
        $cf4 = $this->input->get('cf4') ? $this->input->get('cf4') : NULL;
        $cf5 = $this->input->get('cf5') ? $this->input->get('cf5') : NULL;
        $cf6 = $this->input->get('cf6') ? $this->input->get('cf6') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        $pp = "( SELECT product_id, SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN quantity ELSE 0 END) as purchasedQty, SUM(pi.quantity) as balacneQty, ( unit_cost ) balacneValue, SUM( (CASE WHEN pi.purchase_id IS NOT NULL THEN (pi.subtotal) ELSE 0 END) ) totalPurchase from {$this->db->dbprefix('purchase_items')} pi inner JOIN {$this->db->dbprefix('purchases')} p on p.id = pi.purchase_id ";
        $sp = "( SELECT si.product_id, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from " . $this->db->dbprefix('sales') . " s JOIN " . $this->db->dbprefix('sale_items') . " si on s.id = si.sale_id ";
        if ($start_date || $warehouse) {
            $pp .= " WHERE ";
            $sp .= " WHERE ";
            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                $pp .= " p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
                if ($warehouse) {
                    $pp .= " AND ";
                    $sp .= " AND ";
                }
            }
            if ($warehouse) {
                $pp .= " pi.warehouse_id = '{$warehouse}' ";
                $sp .= " si.warehouse_id = '{$warehouse}' ";
            }
        }
        $pp .= " GROUP BY pi.product_id ) PCosts";
        $sp .= " GROUP BY si.product_id ) PSales";
        if ($pdf || $xls) {

            $this->db
                ->select($this->db->dbprefix('products') . ".code, " . $this->db->dbprefix('products') . ".name,
                COALESCE( PCosts.purchasedQty, 0 ) as PurchasedQty,
                COALESCE( PSales.soldQty, 0 ) as SoldQty,
                COALESCE( PCosts.balacneQty-PSales.soldQty, 0 ) as BalacneQty,
                COALESCE( PCosts.totalPurchase, 0 ) as TotalPurchase,
                COALESCE( (PCosts.balacneQty-PSales.soldQty)* PCosts.balacneValue, 0 ) as TotalBalance,
                COALESCE( PSales.totalSale, 0 ) as TotalSales,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )) as Profit", FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
                ->order_by('products.name');

            if ($product) {
                $this->db->where($this->db->dbprefix('products') . ".id", $product);
            }
            if ($cf1) {
                $this->db->where($this->db->dbprefix('products') . ".cf1", $cf1);
            }
            if ($cf2) {
                $this->db->where($this->db->dbprefix('products') . ".cf2", $cf2);
            }
            if ($cf3) {
                $this->db->where($this->db->dbprefix('products') . ".cf3", $cf3);
            }
            if ($cf4) {
                $this->db->where($this->db->dbprefix('products') . ".cf4", $cf4);
            }
            if ($cf5) {
                $this->db->where($this->db->dbprefix('products') . ".cf5", $cf5);
            }
            if ($cf6) {
                $this->db->where($this->db->dbprefix('products') . ".cf6", $cf6);
            }
            if ($category) {
                $this->db->where($this->db->dbprefix('products') . ".category_id", $category);
            }
            if ($subcategory) {
                $this->db->where($this->db->dbprefix('products') . ".subcategory_id", $subcategory);
            }
            if ($brand) {
                $this->db->where($this->db->dbprefix('products') . ".brand", $brand);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('products_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('purchased'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('sold'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('purchased_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('sold_amount'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('profit_loss'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('stock_in_hand'));

                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $sAmt = 0;
                $pAmt = 0;
                $bQty = 0;
                $bAmt = 0;
                $pl = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->PurchasedQty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->SoldQty);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->BalacneQty);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->TotalPurchase);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->TotalSales);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->Profit);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->TotalBalance);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $bQty += $data_row->BalacneQty;
                    $pAmt += $data_row->TotalPurchase;
                    $sAmt += $data_row->TotalSales;
                    $bAmt += $data_row->TotalBalance;
                    $pl += $data_row->Profit;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("C" . $row . ":I" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $bQty);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $pAmt);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sAmt);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $pl);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $bAmt);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                $filename = 'products_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('products') . ".code, " . $this->db->dbprefix('products') . ".name,
                CONCAT(COALESCE( PCosts.purchasedQty, 0 ), '__', COALESCE( PCosts.totalPurchase, 0 )) as purchased,
                CONCAT(COALESCE( PSales.soldQty, 0 ), '__', COALESCE( PSales.totalSale, 0 )) as sold,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )) as Profit,
                CONCAT(COALESCE( PCosts.balacneQty-PSales.soldQty, 0 ), '__', COALESCE( (PCosts.balacneQty-PSales.soldQty)* PCosts.balacneValue, 0 )) as balance, {$this->db->dbprefix('products')}.id as id", FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
                ->group_by('products.code, PSales.soldQty, PSales.totalSale, PCosts.purchasedQty, PCosts.totalPurchase, PCosts.balacneQty, PCosts.balacneValue');
                $this->db->order_by('PSales.soldQty');

            if ($product) {
                $this->datatables->where($this->db->dbprefix('products') . ".id", $product);
            }
            if ($cf1) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf1", $cf1);
            }
            if ($cf2) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf2", $cf2);
            }
            if ($cf3) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf3", $cf3);
            }
            if ($cf4) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf4", $cf4);
            }
            if ($cf5) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf5", $cf5);
            }
            if ($cf6) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf6", $cf6);
            }
            if ($category) {
                $this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
            }
            if ($subcategory) {
                $this->datatables->where($this->db->dbprefix('products') . ".subcategory_id", $subcategory);
            }
            if ($brand) {
                $this->datatables->where($this->db->dbprefix('products') . ".brand", $brand);
            }

            echo $this->datatables->generate();

        }

    }



   //cancel sales
     function cancel_sales($warehouse_id = NULL)
    {
       
        $user=NULL;
        $start_date=NULL;
        $end_date=date('Y-m-d');
       


        if ($this->input->post('user')) {
            //echo "user ".$this->input->post('user');
             $user=$this->input->post('user');
        }


        if ($this->input->post('start_date')) {
            //echo "for_month ".$this->input->post('for_month');
            $start_date=$this->input->post('start_date');
            $start_date = $this->sma->fld($start_date);
        }
        if ($this->input->post('end_date')) {
            //echo "for_month ".$this->input->post('for_month');
            $end_date=$this->input->post('end_date');
            $end_date=$this->sma->fld($end_date);
        }
        $this->data['report']=$this->reports_model->getCanceledSales($user,$start_date,$end_date,NULL,NULL);
        //echo json_encode($this->data['report']);
        //$this->data['users']=$this->companies_model->getAllCustomers();
        $this->sma->checkPermissions('points_history');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $user = $this->site->getUser();
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $user->warehouse_id;
            $this->data['warehouse'] = $user->warehouse_id ? $this->site->getWarehouseByID($user->warehouse_id) : NULL;
        }
       // echo "".json_encode($this->data['report']);die();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('cancel_sales')));
        $meta = array('page_title' => lang('cancel_sales'), 'bc' => $bc);
        $this->page_construct('reports/cancel_sales', $meta, $this->data);
    }


     public function cancel_sales_export($V=NULL)
    {
        //echo "val ".$this->input->post('chkvals');die();
    
         $this->data['report'] = $this->reports_model->getCanceledSales(NULL,NULL,NULL,TRUE,$this->input->post('chkvals'));
        
         //echo json_encode($this->data['report']);die;
         //$this->page_construct('stocktake/consolidatedreport', $meta, $this->data);

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('Canceled Sales Report'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('created_date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('user'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('grand_total'));
                  
                   

                    $row = 2;
                    foreach ($this->data['report'] as $report) {

                                   
                       
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $report->created_date);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $report->user);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $report->grand_total);
                      
                       
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                   
                    
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'canceled_sales_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    return create_excel($this->excel, $filename);

    }



 //cancel sales
     function pnoun($warehouse_id = NULL)
    {
       
        $user=NULL;
        $start_date=NULL;
        $end_date=date('Y-m-d');
       


        if ($this->input->post('user')) {
            //echo "user ".$this->input->post('user');
             $user=$this->input->post('user');
        }


        if ($this->input->post('start_date')) {
            //echo "for_month ".$this->input->post('for_month');
            $start_date=$this->input->post('start_date');
            $start_date = $this->sma->fld($start_date);
        }
        if ($this->input->post('end_date')) {
            //echo "for_month ".$this->input->post('for_month');
            $end_date=$this->input->post('end_date');
            $end_date=$this->sma->fld($end_date);
        }
        $this->data['report']=$this->reports_model->getCanceledSales($user,$start_date,$end_date,NULL,NULL);
        //echo json_encode($this->data['report']);
        //$this->data['users']=$this->companies_model->getAllCustomers();
        $this->sma->checkPermissions('points_history');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $user = $this->site->getUser();
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $user->warehouse_id;
            $this->data['warehouse'] = $user->warehouse_id ? $this->site->getWarehouseByID($user->warehouse_id) : NULL;
        }
       // echo "".json_encode($this->data['report']);die();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('cancel_sales')));
        $meta = array('page_title' => lang('cancel_sales'), 'bc' => $bc);
        $this->page_construct('reports/pnoun', $meta, $this->data);
    }





//QTY AUDIT

    public function qtyaudits($id = null)
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['categories'] = $this->reports_model->getExpenseCategories();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('qauntity_qudit')));
        $meta = array('page_title' => lang('qauntity_qudit'), 'bc' => $bc);
        $this->page_construct('reports/qtyaudits', $meta, $this->data);
    }
public function getQtyAudits($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('expenses');

        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $note = $this->input->get('note') ? $this->input->get('note') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        $ids = $this->input->get('ids') ? $this->input->get('ids') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }

        if ($pdf || $xls) {

             $this->db
            ->select("{$this->db->dbprefix('qtyaudit')}.id,{$this->db->dbprefix('qtyaudit')}.created_date, p.name, {$this->db->dbprefix('qtyaudit')}.org_qty, {$this->db->dbprefix('qtyaudit')}.new_qty, u.username,{$this->db->dbprefix('qtyaudit')}.is_del")
            ->from("qtyaudit")
            ->join('products p', 'p.id=product_id', 'left')
            ->join('users u', 'u.id=user_id', 'left');

            if ($ids) {

               // echo "".json_encode(explode(",", $ids));die();
                $this->db->where_in("{$this->db->dbprefix('qtyaudit')}.id", explode(",", $ids));

            }
            else
            {    
            
            if ($product) {
                $this->db->like('product_id', $product, 'both');
            }
            if ($category) {
                $this->db->where('category_id', $category);
            }
            if ($user) {
                $this->db->where('user_id', $user);
            }
            if ($start_date) {
                $this->db->where('created_date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
           }
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('expenses_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('org_qty'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('new_qty'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('username'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('is_deleted'));

                $row = 2; $total = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->created_date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->org_qty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->new_qty);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->username);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->is_del);
                    $row++;
                   
                }
                $this->excel->getActiveSheet()->getStyle("D" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
              //  $this->excel->getActiveSheet()->SetCellValue('D' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $filename = 'qtyaudits_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
             $this->datatables
            ->select("{$this->db->dbprefix('qtyaudit')}.id,{$this->db->dbprefix('qtyaudit')}.created_date, p.name, {$this->db->dbprefix('qtyaudit')}.org_qty, {$this->db->dbprefix('qtyaudit')}.new_qty, u.username,{$this->db->dbprefix('qtyaudit')}.is_del")
            ->from("qtyaudit")
            ->join('products p', 'p.id=product_id', 'left')
            ->join('users u', 'u.id=user_id', 'left');

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('created_by', $this->session->userdata('user_id'));
            }
            
            if ($product) {
                $this->datatables->like('product_id', $product, 'both');
            }
            
            if ($user) {
                $this->datatables->where('user_id', $user);
            }
            if ($start_date) {
                $this->datatables->where('created_date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
	}
		//new
		
        function getGSTAdvReport1($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : date('Y-m-d 00:00');
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : date('Y-m-d 11:59');

        $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('gst_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('Type'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('Reference no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Ext Ref No'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('Suppliers Inv Date'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('Purchase Date'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('Supplier Name'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('State'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('GST NO'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('Reg Type'));
                                
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('Product Name'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('Product Group'));
                $this->excel->getActiveSheet()->SetCellValue('L1', lang('Unit'));
                $this->excel->getActiveSheet()->SetCellValue('M1', lang('GST Rate'));
                $this->excel->getActiveSheet()->SetCellValue('N1', lang('GST Code'));
                $this->excel->getActiveSheet()->SetCellValue('O1', lang('Unit Qty'));
                $this->excel->getActiveSheet()->SetCellValue('P1', lang('Rate'));
                $this->excel->getActiveSheet()->SetCellValue('Q1', lang('Value'));
                $this->excel->getActiveSheet()->SetCellValue('R1', lang('Purchase Ledger'));
                $this->excel->getActiveSheet()->SetCellValue('S1', lang('Discount'));
                $this->excel->getActiveSheet()->SetCellValue('T1', lang('CGST Rate'));
                $this->excel->getActiveSheet()->SetCellValue('U1', lang('SGST Rate'));
                $this->excel->getActiveSheet()->SetCellValue('V1', lang('IGST Rate'));
                
                $this->excel->getActiveSheet()->SetCellValue('W1', lang('CGST Amt'));
                $this->excel->getActiveSheet()->SetCellValue('X1', lang('SGST Amt'));
                $this->excel->getActiveSheet()->SetCellValue('Y1', lang('IGST Amt'));

        
                // $this->excel->getActiveSheet()->SetCellValue('Z1', lang('CESS Rate'));
                //  $this->excel->getActiveSheet()->SetCellValue('AA1', lang('CESS Amt'));
                 $this->excel->getActiveSheet()->SetCellValue('Z1', lang('Rounding'));

        if ($start_date && $end_date) {



            $start_date = $end_date ? $this->sma->fld($start_date) : date('Y-m-d');
            $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
            $pp = " {$this->db->dbprefix('purchases')}.date >= '{$start_date}' AND {$this->db->dbprefix('purchases')}.date < '{$end_date}' ";
        }

         $p='(SELECT purchase_id,GROUP_CONCAT( paid_by SEPARATOR "," ) as pays FROM sma_payments';
               

               $q=' GROUP BY purchase_id ) as  FI';
               

                if ($start_date && $end_date) {
               $p.="  where  purchase_id in (select id from sma_purchases where ".$pp.") ";

               }
               $p.=$q;

               //purchase_items.tax_rate_id

        if ($pdf || $xls){

            $totalRound=0;

            
             $ptypes=array(
                'cash',
                'gift_card',
                'Online Payment',
                'Phone Pe',
                'Cheque',
                'CC' );
             $this->db
                ->select("{$this->db->dbprefix('purchases')}.id,'Purchases' as Saletype,reference_no,ext_pur_ref_no,DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%d/%m/%Y') as purchase_date,


                  supplier

                    as party



                    ,'Maharashtra' as State,'' as vat_no ,

                     'Regular'

                    as reg_type


                    ,purchase_items.product_name,'' as ProdGroup,
                    '' as Unit,
                    COALESCE(  ( 

                        COALESCE({$this->db->dbprefix('purchase_items')}.cgst_rate ,0) +
                        COALESCE({$this->db->dbprefix('purchase_items')}.sgst_rate ,0) +
                        COALESCE({$this->db->dbprefix('purchase_items')}.igst_rate ,0) 



                    )


                        ,0)
                     as GST_Rate,
                    purchase_items.gst_code as GSTCode,
                    purchase_items.quantity as Unit_Qty,
                    purchase_items.unit_cost as Rate,
                    purchase_items.subtotal as Value,
                    '' as Purchase_Ledger,
                    purchase_items.item_discount as Discount,
                    purchase_items.cgst_rate,
                    purchase_items.sgst_rate,
                    purchase_items.igst_rate,
                    purchase_items.cgst_amt,
                    purchase_items.sgst_amt,
                    purchase_items.igst_amt,
                    purchase_items.cess_rate,
                    purchase_items.cess_amt,

                    ,0 as rounding,supplier_id,product_id
                    


                    ")
                ->from('purchases')
                ->join('purchase_items', 
                "{$this->db->dbprefix('purchase_items')}.purchase_id 
                = {$this->db->dbprefix('purchases')}.id",'inner')
                ->join($p,"FI.purchase_id={$this->db->dbprefix('purchases')}.id",'left')
                ->where("{$this->db->dbprefix('purchases')}.status",'received')
                ->where($pp);


            $q = $this->db->get();
            $old_id=0;
            $rounding=0;

            if ($q->num_rows() > 0) {


                 $row = 2;
                foreach ($q->result() as $data_row) {
                    

                  
                    if($old_id!=$data_row->id)
                    {
                        $old_id=$data_row->id;
                        $rounding=$data_row->rounding;
                        
                    }
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->Saletype);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->ext_pur_ref_no);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->purchase_date);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->purchase_date);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->party);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->State);

                   
                        $vat_no=$this->site->getVatByID($data_row->supplier_id);
                        $uname=$this->site->getUnitName($data_row->product_id);

                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $vat_no?$vat_no:"");
                    
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->reg_type);
                    
                     $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->product_name);
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->ProdGroup);
                    $this->excel->getActiveSheet()->SetCellValue('L' . $row, $uname);
                    $this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->GST_Rate);

                    $gcode=$data_row->GSTCode;
                    $sl=strlen($gcode);
                    if($sl>6)
                    {
                        $gcode=substr($gcode,0, 6);
                    }
                    
                    $this->excel->getActiveSheet()->SetCellValue('N' . $row, $gcode);
                    $this->excel->getActiveSheet()->SetCellValue('O' . $row, $data_row->Unit_Qty);
                
                    $this->excel->getActiveSheet()->SetCellValue('P' . $row, $data_row->Rate);
                    $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $data_row->Value);
                    $this->excel->getActiveSheet()->SetCellValue('R' . $row, $data_row->Purchase_Ledger);
                    $this->excel->getActiveSheet()->SetCellValue('S' . $row, $data_row->Discount);
                    
                    $this->excel->getActiveSheet()->SetCellValue('T' . $row, $data_row->cgst_rate);
                    $this->excel->getActiveSheet()->SetCellValue('U' . $row, $data_row->sgst_rate);
                    $this->excel->getActiveSheet()->SetCellValue('V' . $row, $data_row->igst_rate);
                    
                     $this->excel->getActiveSheet()->SetCellValue('W' . $row, $data_row->cgst_amt);
                    $this->excel->getActiveSheet()->SetCellValue('X' . $row, $data_row->sgst_amt);
                    $this->excel->getActiveSheet()->SetCellValue('Y' . $row, $data_row->igst_amt);
                
                
                    //  $this->excel->getActiveSheet()->SetCellValue('X' . $row, $data_row->cess_rate);
                    // $this->excel->getActiveSheet()->SetCellValue('Y' . $row, $data_row->cess_amt);
                    $this->excel->getActiveSheet()->SetCellValue('Z' . $row, $rounding);
                    $rounding='';
                    $row++;
                }
                $uppercase = range('A', 'Z');
                foreach($uppercase as $letter)
                {
                      $this->excel->getActiveSheet()->getColumnDimension($letter)->setWidth(35);
                }
                
                $this->excel->getActiveSheet()->SetCellValue('A' . $row, $rounding);

                    foreach ($ptypes as $pt) {
                        # code...
                         $row++;
                         $parr=$this->reports_model->getAllPayment($start_date, $end_date,$pt);
                       
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, 'Total '.$pt ." : ".($parr?$parr->total_amount:"0.0") );
                    } 

                $filename = 'gst_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            } else {
                 $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
            }
           
        } else {

           
            $this->load->library('datatables');
             $this->datatables
                ->select("'Purchases' as Saletype,
                    reference_no,
                    DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%d/%m/%Y') as purchase_date,


                  supplier

                    as party



                    ,'Maharashtra' as State, vat_no ,

                     'Regular'

                    as reg_type


                    ,purchase_items.product_name,'' as ProdGroup,
                    '' as Unit,purchase_items.tax_rate_id as GST_Rate,
                    purchase_items.gst_code as GSTCode,
                    purchase_items.quantity as Unit_Qty,
                    purchase_items.unit_cost as Rate,
                    purchase_items.subtotal as Value,
                    '' as Purchase_Ledger,
                    purchase_items.item_discount as Discount,
                    purchase_items.cgst_rate,
                    purchase_items.sgst_rate,
                    purchase_items.igst_rate,
                    purchase_items.cgst_amt,
                    purchase_items.sgst_amt,
                    purchase_items.igst_amt,
                    purchase_items.cess_rate,
                    purchase_items.cess_amt,

                    ,0 as rounding
                    


                    ")
                ->from('purchases')
                ->join('purchase_items', 
                "{$this->db->dbprefix('purchase_items')}.purchase_id 
                = {$this->db->dbprefix('purchases')}.id",'inner')
                ->join($p,"FI.purchase_id={$this->db->dbprefix('purchases')}.id",'left')
                ->join("companies","supplier_id=companies.id",'left')
                ->where($pp);


            $this->datatables->unset_column("{$this->db->dbprefix('sales')}.id");
            echo $this->datatables->generate();

        }
	}
    

      function suppliergst()
    {
       // $this->sma->checkPermissions('products');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        //$this->data['brands'] = $this->site->getAllBrands();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('supplier_gst_report')));
        $meta = array('page_title' => lang('supplier_gst_report'), 'bc' => $bc);
        $this->page_construct('reports/suppliergst', $meta, $this->data);
    }

		  function getGSTAdvReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : date('Y-m-d 00:00');
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : date('Y-m-d 11:59');

        $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('gst_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('Type'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('Reference no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Ext Ref No'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('Suppliers Inv Date'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('Purchase Date'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('Supplier Name'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('State'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('GST NO'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('Reg Type'));
                                
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('Product Name'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('Product Group'));
                $this->excel->getActiveSheet()->SetCellValue('L1', lang('Unit'));
                $this->excel->getActiveSheet()->SetCellValue('M1', lang('GST Rate'));
                $this->excel->getActiveSheet()->SetCellValue('N1', lang('GST Code'));
                $this->excel->getActiveSheet()->SetCellValue('O1', lang('Unit Qty'));
                $this->excel->getActiveSheet()->SetCellValue('P1', lang('Rate'));
                $this->excel->getActiveSheet()->SetCellValue('Q1', lang('Taxable'));
                $this->excel->getActiveSheet()->SetCellValue('R1', lang('Value'));
                $this->excel->getActiveSheet()->SetCellValue('S1', lang('Purchase Ledger'));
                $this->excel->getActiveSheet()->SetCellValue('T1', lang('Discount'));
                $this->excel->getActiveSheet()->SetCellValue('V1', lang('CGST Rate'));
                $this->excel->getActiveSheet()->SetCellValue('V1', lang('SGST Rate'));
                $this->excel->getActiveSheet()->SetCellValue('W1', lang('IGST Rate'));
                
                $this->excel->getActiveSheet()->SetCellValue('X1', lang('CGST Amt'));
                $this->excel->getActiveSheet()->SetCellValue('Y1', lang('SGST Amt'));
                $this->excel->getActiveSheet()->SetCellValue('Z1', lang('IGST Amt'));

        
                // $this->excel->getActiveSheet()->SetCellValue('Z1', lang('CESS Rate'));
                //  $this->excel->getActiveSheet()->SetCellValue('AA1', lang('CESS Amt'));
                 $this->excel->getActiveSheet()->SetCellValue('AA1', lang('Rounding'));

        if ($start_date && $end_date) {



            $start_date = $end_date ? $this->sma->fld($start_date) : date('Y-m-d');
            $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
            $pp = " {$this->db->dbprefix('purchases')}.date >= '{$start_date}' AND {$this->db->dbprefix('purchases')}.date < '{$end_date}' ";
        }

         $p='(SELECT purchase_id,GROUP_CONCAT( paid_by SEPARATOR "," ) as pays FROM sma_payments';
               

               $q=' GROUP BY purchase_id ) as  FI';
               

                if ($start_date && $end_date) {
               $p.="  where  purchase_id in (select id from sma_purchases where ".$pp.") ";

               }
               $p.=$q;

               //purchase_items.tax_rate_id

        if ($pdf || $xls){

            $totalRound=0;

            
             $ptypes=array(
                'cash',
                'gift_card',
                'Online Payment',
                'Phone Pe',
                'Cheque',
                'CC' );
             $this->db
                ->select("{$this->db->dbprefix('purchases')}.id,'Purchases' as Saletype,reference_no,ext_pur_ref_no,DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%d/%m/%Y') as purchase_date,


                  supplier

                    as party



                    ,'Maharashtra' as State,'' as vat_no ,

                     'Regular'

                    as reg_type


                    ,purchase_items.product_name,'' as ProdGroup,
                    '' as Unit,
                    COALESCE(  ( 

                        COALESCE({$this->db->dbprefix('purchase_items')}.cgst_rate ,0) +
                        COALESCE({$this->db->dbprefix('purchase_items')}.sgst_rate ,0) +
                        COALESCE({$this->db->dbprefix('purchase_items')}.igst_rate ,0) 



                    )


                        ,0)
                     as GST_Rate,
                    purchase_items.gst_code as GSTCode,
                    purchase_items.quantity as Unit_Qty,
                    purchase_items.unit_cost as Rate,
                    purchase_items.subtotal as Value,
                    '' as Purchase_Ledger,
                    purchase_items.item_discount as Discount,
                    purchase_items.cgst_rate,
                    purchase_items.sgst_rate,
                    purchase_items.igst_rate,
                    purchase_items.cgst_amt,
                    purchase_items.sgst_amt,
                    purchase_items.igst_amt,
                    purchase_items.cess_rate,
                    purchase_items.cess_amt,

                    ,0 as rounding,supplier_id,product_id
                    
                    ,
                    ( (unit_cost*quantity) 
                        -(
                            COALESCE(cgst_amt,0) +
                            COALESCE(sgst_amt,0) +
                            COALESCE(igst_amt,0) 

                        ) 
                        

                )   as totalTaxable

                    ")
                ->from('purchases')
                ->join('purchase_items', 
                "{$this->db->dbprefix('purchase_items')}.purchase_id 
                = {$this->db->dbprefix('purchases')}.id",'inner')
                ->join($p,"FI.purchase_id={$this->db->dbprefix('purchases')}.id",'left')
                ->where("{$this->db->dbprefix('purchases')}.status",'received')
                ->where($pp);


            $q = $this->db->get();
            $old_id=0;
            $rounding=0;

            if ($q->num_rows() > 0) {


                 $row = 2;
                foreach ($q->result() as $data_row) {
                    

                  
                    if($old_id!=$data_row->id)
                    {
                        $old_id=$data_row->id;
                        $rounding=$data_row->rounding;
                        
                    }
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->Saletype);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->ext_pur_ref_no);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->purchase_date);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->purchase_date);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->party);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->State);

                   
                        $vat_no=$this->site->getVatByID($data_row->supplier_id);
                        $uname=$this->site->getUnitName($data_row->product_id);

                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $vat_no?$vat_no:"");
                    
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->reg_type);
                    
                     $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->product_name);
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->ProdGroup);
                    $this->excel->getActiveSheet()->SetCellValue('L' . $row, $uname);
                    $this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->GST_Rate);

                    $gcode=$data_row->GSTCode;
                    $sl=strlen($gcode);
                    if($sl>6)
                    {
                        $gcode=substr($gcode,0, 6);
                    }
                    
                    $this->excel->getActiveSheet()->SetCellValue('N' . $row, $gcode);
                    $this->excel->getActiveSheet()->SetCellValue('O' . $row, $data_row->Unit_Qty);
                
                    $this->excel->getActiveSheet()->SetCellValue('P' . $row, $data_row->Rate);
                    $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $data_row->totalTaxable);
                    $this->excel->getActiveSheet()->SetCellValue('R' . $row, $data_row->Value);
                    $this->excel->getActiveSheet()->SetCellValue('S' . $row, $data_row->Purchase_Ledger);
                    $this->excel->getActiveSheet()->SetCellValue('T' . $row, $data_row->Discount);
                    
                    $this->excel->getActiveSheet()->SetCellValue('V' . $row, $data_row->cgst_rate);
                    $this->excel->getActiveSheet()->SetCellValue('V' . $row, $data_row->sgst_rate);
                    $this->excel->getActiveSheet()->SetCellValue('W' . $row, $data_row->igst_rate);
                    
                     $this->excel->getActiveSheet()->SetCellValue('X' . $row, $data_row->cgst_amt);
                    $this->excel->getActiveSheet()->SetCellValue('Y' . $row, $data_row->sgst_amt);
                    $this->excel->getActiveSheet()->SetCellValue('Z' . $row, $data_row->igst_amt);
                
                
                    //  $this->excel->getActiveSheet()->SetCellValue('X' . $row, $data_row->cess_rate);
                    // $this->excel->getActiveSheet()->SetCellValue('Y' . $row, $data_row->cess_amt);
                    $this->excel->getActiveSheet()->SetCellValue('AA' . $row, $rounding);
                    $rounding='';
                    $row++;
                }
                $uppercase = range('A', 'Z');
                foreach($uppercase as $letter)
                {
                      $this->excel->getActiveSheet()->getColumnDimension($letter)->setWidth(35);
                }
                
                $this->excel->getActiveSheet()->SetCellValue('A' . $row, $rounding);

                    foreach ($ptypes as $pt) {
                        # code...
                         $row++;
                         $parr=$this->reports_model->getAllPayment($start_date, $end_date,$pt);
                       
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, 'Total '.$pt ." : ".($parr?$parr->total_amount:"0.0") );
                    } 

                $filename = 'gst_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            } else {
                 $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
            }
           
        } else {

           
            $this->load->library('datatables');
             $this->datatables
                ->select("'Purchases' as Saletype,
                    reference_no,
                    DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%d/%m/%Y') as purchase_date,


                  supplier

                    as party



                    ,'Maharashtra' as State, vat_no ,

                     'Regular'

                    as reg_type


                    ,purchase_items.product_name,'' as ProdGroup,
                    '' as Unit,purchase_items.tax_rate_id as GST_Rate,
                    purchase_items.gst_code as GSTCode,
                    purchase_items.quantity as Unit_Qty,
                    purchase_items.unit_cost as Rate,
                    purchase_items.subtotal as Value,
                    '' as Purchase_Ledger,
                    purchase_items.item_discount as Discount,
                    purchase_items.cgst_rate,
                    purchase_items.sgst_rate,
                    purchase_items.igst_rate,
                    purchase_items.cgst_amt,
                    purchase_items.sgst_amt,
                    purchase_items.igst_amt,
                    purchase_items.cess_rate,
                    purchase_items.cess_amt,

                    ,0 as rounding
                    


                    ")
                ->from('purchases')
                ->join('purchase_items', 
                "{$this->db->dbprefix('purchase_items')}.purchase_id 
                = {$this->db->dbprefix('purchases')}.id",'inner')
                ->join($p,"FI.purchase_id={$this->db->dbprefix('purchases')}.id",'left')
                ->join("companies","supplier_id=companies.id",'left')
                ->where($pp);


            $this->datatables->unset_column("{$this->db->dbprefix('sales')}.id");
            echo $this->datatables->generate();

        }

    }
	
	//gst
	
    function getGSTReportCSV($pdf = NULL, $xls = NULL)
    {
       
        $this->sma->checkPermissions('products', TRUE);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        // $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : date('Y-m-d 00:00');
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : date('Y-m-d 11:59');
            


        if ($start_date && $end_date) {
            $start_date = $end_date ? $this->sma->fld($start_date) : date('Y-m-d');
            $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
            $pp = " {$this->db->dbprefix('sales')}.date >= '{$start_date}' AND {$this->db->dbprefix('sales')}.date < '{$end_date}' ";
        }

         $p='(SELECT sale_id,GROUP_CONCAT( paid_by SEPARATOR "," ) as pays FROM sma_payments';
               

               $q=' GROUP BY sale_id ) as  FI';
               

                if ($start_date && $end_date) {
               $p.="  where  sale_id in (select id from sma_sales where ".$pp.") ";

               }
               $p.=$q;

        if ($pdf || $xls){

            
            $totalRound=0;

             $qp="select sum(COALESCE(rounding,0)) totrnd from sma_sales ";

              if ($start_date && $end_date) {
               $qp.="   where ".$pp." ";

               }
               
              

               $qq=$this->db->query($qp);
               if($qq->num_rows()>0)
               {
                  

                  if($qq->row()->totrnd)
                  {
                    $totalRound=$qq->row()->totrnd;
                  }
               }

             $ptypes=array(
                'cash',
                'gift_card',
                'Online Payment',
                'Phone Pe',
                'Cheque',
                'CC' );

             $this->db
                ->select("{$this->db->dbprefix('sales')}.id,'Sales' as Saletype,reference_no,DATE_FORMAT(date, '%d/%m/%Y') as sale_date,


                   (
                    CASE WHEN pos = 0  THEN customer

                     WHEN FI.pays = 'cash'  THEN 'cash'
                      WHEN FI.pays= 'Online Payment'  THEN 'Online Payment'
                      WHEN FI.pays = 'Phone pe'  THEN 'Phone pe'
                      WHEN FI.pays = 'gift_card'  THEN 'Gift card'
                      WHEN FI.pays = 'other'  THEN 'Other'
                      WHEN FI.pays = 'CC'  THEN 'Credit Card'   
                      WHEN FI.pays = 'Cheque'  THEN 'Cheque' 
                     ELSE  'customer'
                     end 
                 )

                    as party



                    ,'Maharashtra' as State, vat_no ,

                      (
                    CASE WHEN pos = 0  THEN 'regular'

                    
                     ELSE  'Unregistered/Consumer'
                     end 
                 )

                    as reg_type


                    ,sale_items.product_name,'' as ProdGroup,product_unit_code as Unit,sale_items.tax_rate_id as GST_Rate,sale_items.gst_code as GSTCode,sale_items.quantity as Unit_Qty,sale_items.unit_price as Rate,sale_items.subtotal as Value,'' as Sales_Ledger,sale_items.item_discount as Discount,sale_items.cgst_rate,sale_items.sgst_rate, sale_items.igst_rate,sale_items.cgst_amt,sale_items.sgst_amt,sale_items.igst_amt,sale_items.cess_rate,sale_items.cess_amt,

                    pos,rounding,customer_id,(net_unit_price*quantity) as totalTaxable,product_id
                    


                    ")
                ->from('sales')
                ->join('sale_items', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id",'inner')
                ->join($p,"FI.sale_id={$this->db->dbprefix('sales')}.id",'left')
                ->join('companies',"companies.id={$this->db->dbprefix('sales')}.customer_id",'left')
              
            

                ->where($pp);


            $q = $this->db->get();
            $old_id=0;
            $rounding=0;
            
           

           

           

            if ($q->num_rows() > 0) {

                 $myrows=array();
        //          ini_set('display_errors', 0);
        // ini_set('log_errors', 1);
        // error_reporting(E_ALL & ~E_NOTICE);
 

                  $curow1=array();
                 $row = 2;

              //  if(1==2) 
                foreach ($q->result() as $data_row) {
                    

                    $curow=array();
                    if($old_id!=$data_row->id)
                    {
                        $old_id=$data_row->id;
                        $rounding=$data_row->rounding;
                        
                    }
                    $currow[]=($data_row->Saletype);
                    $currow[]=($data_row->reference_no);
                    $currow[]=($data_row->sale_date);
                    $currow[]=($data_row->party);
                    $currow[]=($data_row->State);

                    if($data_row->pos==0)
                    {
                       
                        $currow[]=($data_row->vat_no);
                    }
                    else
                    {

                    $currow[]=("");
                    }
                    $currow[]=($data_row->reg_type);
                    
                     $currow[]=($data_row->product_name);
                    $currow[]=( $data_row->ProdGroup);

                      //$uname=$this->site->getUnitName($data_row->product_id);
                    $currow[]=( $data_row->Unit);
                    $currow[]=( $data_row->GST_Rate);
                    $currow[]=( $data_row->GSTCode);
                    $currow[]=( $data_row->Unit_Qty);
                
                    $currow[]=($data_row->Rate);
                    $currow[]=($data_row->totalTaxable);
                    $currow[]=($data_row->Value);
                    $currow[]=($data_row->Sales_Ledger);
                    $currow[]=($data_row->Discount);
                    
                    $currow[]=($data_row->cgst_rate);
                    $currow[]=($data_row->sgst_rate);
                    $currow[]=($data_row->igst_rate);
                    
                    $currow[]=($data_row->cgst_amt);
                    $currow[]=($data_row->sgst_amt);
                    $currow[]=($data_row->igst_amt);
                
                
                    $currow[]=($data_row->cess_rate);
                    $currow[]=($data_row->cess_amt);
                    $currow[]=($rounding);
                    $rounding='';
                    $row++;
                    $currow1[]=$currow;
                   // $writer->writeSheetRow('Sheet1', $currow );
                }

            if (!empty($currow)) {
                $sdate = str_replace(' ','_',$start_date);
                $edate = str_replace(' ','_',$end_date);

                $filename = "gst_report_{$sdate}_To_{$edate}.csv";
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=$filename");
                header("Content-Type: application/csv; ");
                $file = fopen('php://output', 'w');
              $header = array("Sale Type","Reference No","Sale Date","Party Name","State","GST NO","Reg Type","Product Name","Product Group","Unit","GST Rate","GST Code","Unit Qty","Rate","Value","Sale Ledger","Discount","CGST Rate","SGST Rate","IGST Rate","CGST Amt","SGST Amt","IGST Amt","CESS Rate","CESS Amt","Rounding");
                fputcsv($file, $header);
                foreach ($currow1 as $line){
                    fputcsv($file,$line);
                }
                fclose($file);
                exit;
            }
                
                 
               
               

                 


                exit(0);

            } else {
                 $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
            }
           
        } else {

           
            $this->load->library('datatables');
            $this->datatables
                ->select("{$this->db->dbprefix('sales')}.id,'Sales' as Saletype,reference_no,DATE_FORMAT(date, '%d/%m/%Y') as Sale_Date,


                   (
                    CASE WHEN pos = 0  THEN customer

                     WHEN FI.pays = 'cash'  THEN 'cash'
                      WHEN FI.pays= 'Online Payment'  THEN 'Online Payment'
                      WHEN FI.pays = 'Phone pe'  THEN 'Phone pe'
                      WHEN FI.pays = 'gift_card'  THEN 'Gift card'
                      WHEN FI.pays = 'other'  THEN 'Other'
                      WHEN FI.pays = 'CC'  THEN 'Credit Card'   
                      WHEN FI.pays = 'Cheque'  THEN 'Cheque' 
                     ELSE  'customer'
                     end 
                 )

                    as party



                    ,'Maharashtra' as State,'' as GST_NO,sale_items.product_name,'' as ProdGroup,'' as Unit,sale_items.tax_rate_id as GST_Rate,sale_items.gst_code as GSTCode,sale_items.quantity as Unit_Qty,sale_items.unit_price as Rate,sale_items.subtotal as Value,'' as Sales_Ledger,sale_items.item_discount as Discount,sale_items.cgst_rate,sale_items.sgst_rate, sale_items.igst_rate,sale_items.cgst_amt,sale_items.sgst_amt,sale_items.igst_amt,sale_items.cess_rate,sale_items.cess_amt,

                    pos
                    


                    ")
                ->from('sales')
                ->join('sale_items', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id",'inner')
                ->join($p,"FI.sale_id={$this->db->dbprefix('sales')}.id",'left')
                ->where($pp);


            $this->datatables->unset_column("{$this->db->dbprefix('sales')}.id");
            echo $this->datatables->generate();

        }

    }
	
	 function getFooter($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        // $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : date('Y-m-d 00:00');
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : date('Y-m-d 11:59');
		/*Sale Type	Reference No	Sale Date	Party Name	State	GST NO	Reg Type	Product Name	Product Group	
		Unit

		GST Rate	GST Code	Unit Qty	Rate	Value	
		Sale Ledger	Discount	CGST Rate	SGST Rate	IGST Rate	CGST Amt	SGST Amt	IGST Amt	CESS Rate	CESS Amt	
*/

		$this->load->library('excel');
         $sht=$this->excel->getActiveSheet();
			    //$this->excel->setActiveSheetIndex(0);
			              $sht->setTitle(lang('gst_report'));
                $sht->SetCellValue('A1', lang('Sale Type'));
                $sht->SetCellValue('B1', lang('Reference no'));
                $sht->SetCellValue('C1', lang('Sale Date'));
                $sht->SetCellValue('D1', lang('Party Name'));
                $sht->SetCellValue('E1', lang('State'));
                $sht->SetCellValue('F1', lang('GST NO'));
                $sht->SetCellValue('G1', lang('Reg Type'));
                
                
                $sht->SetCellValue('H1', lang('Product Name'));
                $sht->SetCellValue('I1', lang('Product Group'));
                $sht->SetCellValue('J1', lang('Unit'));
                $sht->SetCellValue('K1', lang('GST Rate'));
                $sht->SetCellValue('L1', lang('GST Code'));
                $sht->SetCellValue('M1', lang('Unit Qty'));
                $sht->SetCellValue('N1', lang('Rate'));
                $sht->SetCellValue('O1', lang('Taxable'));
                $sht->SetCellValue('P1', lang('Value'));
                $sht->SetCellValue('Q1', lang('Sale Ledger'));
                $sht->SetCellValue('R1', lang('Discount'));
                $sht->SetCellValue('S1', lang('CGST Rate'));
                $sht->SetCellValue('T1', lang('SGST Rate'));
                $sht->SetCellValue('U1', lang('IGST Rate'));
                
                $sht->SetCellValue('V1', lang('CGST Amt'));
                $sht->SetCellValue('W1', lang('SGST Amt'));
                $sht->SetCellValue('X1', lang('IGST Amt'));

        
                $sht->SetCellValue('Y1', lang('CESS Rate'));
                 $sht->SetCellValue('Z1', lang('CESS Amt'));
                 $sht->SetCellValue('AA1', lang('Rounding'));

        if ($start_date && $end_date) {



            $start_date = $end_date ? $this->sma->fld($start_date) : date('Y-m-d');
            $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
            $pp = " {$this->db->dbprefix('sales')}.date >= '{$start_date}' AND {$this->db->dbprefix('sales')}.date < '{$end_date}' ";
        }

         $p='(SELECT sale_id,GROUP_CONCAT( paid_by SEPARATOR "," ) as pays FROM sma_payments';
               

               $q=' GROUP BY sale_id ) as  FI';
               

                if ($start_date && $end_date) {
               $p.="  where  sale_id in (select id from sma_sales where ".$pp.") ";

               }
               $p.=$q;

        if ($pdf || $xls){

            $totalRound=0;

             $qp="select sum(COALESCE(rounding,0)) totrnd from sma_sales ";

              if ($start_date && $end_date) {
               $qp.="   where ".$pp." ";

               }
               
              

               $qq=$this->db->query($qp);
               if($qq->num_rows()>0)
               {
                  

                  if($qq->row()->totrnd)
                  {
                    $totalRound=$qq->row()->totrnd;
                  }
               }

             $ptypes=array(
                'cash',
                'gift_card',
                'Online Payment',
                'Phone Pe',
                'Cheque',
                'CC' );

          

            // $this->db
            //     ->select("'Sales' as Saletype,reference_no,DATE_FORMAT(date, '%d/%m/%Y') as Sale_Date,'customer' as Party_Name,'Maharashtra' as State,'' as GST_NO,sale_items.product_name,'' as ProdGroup,'' as Unit,sale_items.tax_rate_id as GST_Rate,sale_items.gst_code as GSTCode,sale_items.quantity as Unit_Qty,sale_items.unit_price as Rate,sale_items.subtotal as Value,'' as Sales_Ledger,sale_items.item_discount as Discount,sale_items.cgst_rate,sale_items.sgst_rate, sale_items.igst_rate,sale_items.cgst_amt,sale_items.sgst_amt,sale_items.igst_amt,sale_items.cess_rate,sale_items.cess_amt")
            //     ->from('sales')
            //     ->join('sale_items', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id")
            //     ->where($pp);

             $this->db
                ->select("{$this->db->dbprefix('sales')}.id,'Sales' as Saletype,reference_no,DATE_FORMAT(date, '%d/%m/%Y') as sale_date,


                   (
                    CASE WHEN pos = 0  THEN customer

                     WHEN FI.pays = 'cash'  THEN 'cash'
                      WHEN FI.pays= 'Online Payment'  THEN 'Online Payment'
                      WHEN FI.pays = 'Phone pe'  THEN 'Phone pe'
                      WHEN FI.pays = 'gift_card'  THEN 'Gift card'
                      WHEN FI.pays = 'other'  THEN 'Other'
                      WHEN FI.pays = 'CC'  THEN 'Credit Card'   
                      WHEN FI.pays = 'Cheque'  THEN 'Cheque' 
                     ELSE  'customer'
                     end 
                 )

                    as party



                    ,'Maharashtra' as State, vat_no ,

                      (
                    CASE WHEN pos = 0  THEN 'regular'

                    
                     ELSE  'Unregistered/Consumer'
                     end 
                 )

                    as reg_type


                    ,sale_items.product_name,'' as ProdGroup,product_unit_code as Unit,sale_items.tax_rate_id as GST_Rate,sale_items.gst_code as GSTCode,sale_items.quantity as Unit_Qty,sale_items.unit_price as Rate,sale_items.subtotal as Value,'' as Sales_Ledger,sale_items.item_discount as Discount,sale_items.cgst_rate,sale_items.sgst_rate, sale_items.igst_rate,sale_items.cgst_amt,sale_items.sgst_amt,sale_items.igst_amt,sale_items.cess_rate,sale_items.cess_amt,

                    pos,rounding,customer_id,(net_unit_price*quantity) as totalTaxable,product_id
                    


                    ")
                ->from('sales')
                ->join('sale_items', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id",'inner')
                ->join($p,"FI.sale_id={$this->db->dbprefix('sales')}.id",'left')
                ->join('companies',"companies.id={$this->db->dbprefix('sales')}.customer_id",'left')
              
            

                ->where($pp);


            $q = $this->db->get();
			$old_id=0;
			$rounding=0;

           

           

            if ($q->num_rows() > 0) {

 //echo "AQ ".$q->num_rows();die();
//echo "string".$q->num_rows();die();
                 $row = 2;
                foreach ($q->result() as $data_row) {
                    

                  
					/*if($old_id!=$data_row->id)
					{
						$old_id=$data_row->id;
						$rounding=$data_row->rounding;
						
					}
                    $sht->SetCellValue('A' . $row, $data_row->Saletype);
				    $sht->SetCellValue('B' . $row, $data_row->reference_no);
				    $sht->SetCellValue('C' . $row, $data_row->sale_date);
				    $sht->SetCellValue('D' . $row, $data_row->party);
				    $sht->SetCellValue('E' . $row, $data_row->State);

                    if($data_row->pos==0)
                    {
                       
                        $sht->SetCellValue('F' .$row, $data_row->vat_no);
                    }
                    else
                    {

					$sht->SetCellValue('F' . $row, "");
                    }
				    $sht->SetCellValue('G' . $row, $data_row->reg_type);
					
					 $sht->SetCellValue('H' . $row, $data_row->product_name);
				    $sht->SetCellValue('I' . $row, $data_row->ProdGroup);

                      //$uname=$this->site->getUnitName($data_row->product_id);
				    $sht->SetCellValue('J' . $row, $data_row->Unit);
                    $sht->SetCellValue('K' . $row, $data_row->GST_Rate);
                    $sht->SetCellValue('L' . $row, $data_row->GSTCode);
                    $sht->SetCellValue('M' . $row, $data_row->Unit_Qty);
                
                    $sht->SetCellValue('N' . $row, $data_row->Rate);
                    $sht->SetCellValue('O' . $row, $data_row->totalTaxable);
                    $sht->SetCellValue('P' . $row, $data_row->Value);
                    $sht->SetCellValue('Q' . $row, $data_row->Sales_Ledger);
                    $sht->SetCellValue('R' . $row, $data_row->Discount);
                    
                    $sht->SetCellValue('S' . $row, $data_row->cgst_rate);
                    $sht->SetCellValue('T' . $row, $data_row->sgst_rate);
                    $sht->SetCellValue('U' . $row, $data_row->igst_rate);
                    
                    $sht->SetCellValue('V' . $row, $data_row->cgst_amt);
                    $sht->SetCellValue('W' . $row, $data_row->sgst_amt);
                    $sht->SetCellValue('X' . $row, $data_row->igst_amt);
                
                
                    $sht->SetCellValue('Y' . $row, $data_row->cess_rate);
                    $sht->SetCellValue('Z' . $row, $data_row->cess_amt);
                    $sht->SetCellValue('AA' . $row, $rounding);
					$rounding='';*/
                    $row++;
				}
				$uppercase = range('A', 'Z');
				foreach($uppercase as $letter)
				{
					  $sht->getColumnDimension($letter)->setWidth(35);
				}
				
                $sht->SetCellValue('A' . $row, $rounding);

                    foreach ($ptypes as $pt) {
                        # code...
                         $row++;
                         $parr=$this->reports_model->getAllPayment($start_date, $end_date,$pt);
                       
                        $sht->SetCellValue('A' . $row, 'Total '.$pt ." : ".($parr?$parr->total_amount:"0.0") );
                    } 

				$filename = 'gst_report';
                $this->load->helper('excel');
                return create_excel($this->excel, $filename);

            } else {
                 $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
            }
           
        } else {

           
            $this->load->library('datatables');
            $this->datatables
                ->select("{$this->db->dbprefix('sales')}.id,'Sales' as Saletype,reference_no,DATE_FORMAT(date, '%d/%m/%Y') as Sale_Date,


                   (
                    CASE WHEN pos = 0  THEN customer

                     WHEN FI.pays = 'cash'  THEN 'cash'
                      WHEN FI.pays= 'Online Payment'  THEN 'Online Payment'
                      WHEN FI.pays = 'Phone pe'  THEN 'Phone pe'
                      WHEN FI.pays = 'gift_card'  THEN 'Gift card'
                      WHEN FI.pays = 'other'  THEN 'Other'
                      WHEN FI.pays = 'CC'  THEN 'Credit Card'   
                      WHEN FI.pays = 'Cheque'  THEN 'Cheque' 
                     ELSE  'customer'
                     end 
                 )

                    as party



                    ,'Maharashtra' as State,'' as GST_NO,sale_items.product_name,'' as ProdGroup,'' as Unit,sale_items.tax_rate_id as GST_Rate,sale_items.gst_code as GSTCode,sale_items.quantity as Unit_Qty,sale_items.unit_price as Rate,sale_items.subtotal as Value,'' as Sales_Ledger,sale_items.item_discount as Discount,sale_items.cgst_rate,sale_items.sgst_rate, sale_items.igst_rate,sale_items.cgst_amt,sale_items.sgst_amt,sale_items.igst_amt,sale_items.cess_rate,sale_items.cess_amt,

                    pos
                    


                    ")
                ->from('sales')
                ->join('sale_items', "{$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id",'inner')
                ->join($p,"FI.sale_id={$this->db->dbprefix('sales')}.id",'left')
                ->where($pp);


            $this->datatables->unset_column("{$this->db->dbprefix('sales')}.id");
            echo $this->datatables->generate();

        }

    }
	
    }

