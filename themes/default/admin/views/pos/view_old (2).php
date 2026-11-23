<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if ($modal) { ?>
<div class="modal-dialog no-modal-header" role="document"><div class="modal-content"><div class="modal-body">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
    <?php 
} else {
    ?><!doctype html>
    <html>
    <head>
        <meta charset="utf-8">
        <title><?=$page_title . " " . lang("no") . " " . $inv->id;?></title>
        <base href="<?=base_url()?>"/>
        <meta http-equiv="cache-control" content="max-age=0"/>
        <meta http-equiv="cache-control" content="no-cache"/>
        <meta http-equiv="expires" content="0"/>
        <meta http-equiv="pragma" content="no-cache"/>
        <link rel="shortcut icon" href="<?=$assets?>images/icon.png"/>
        <link rel="stylesheet" href="<?=$assets?>styles/theme.css" type="text/css"/>
        <style type="text/css" media="all">
            body { color: #000; }
            #wrapper { max-width: 480px; margin: 0 auto; padding-top: 20px; }
            .btn { border-radius: 0; margin-bottom: 5px; }
            .bootbox .modal-footer { border-top: 0; text-align: center; }
            h3 { margin: 5px 0; }
            .order_barcodes img { float: none !important; margin-top: 5px; }
            @media print {
                .no-print { display: none; }
                #wrapper { max-width: 480px; width: 100%; min-width: 250px; margin: 0 auto; }
                .no-border { border: none !important; }
                .border-bottom { border-bottom: 1px solid #ddd !important; }
                table tfoot { display: table-row-group; }
                
            }
        </style>
    </head>

    <body>
        <?php 
    } ?>
    <div id="wrapper">
        <div id="receiptData">
            <div class="no-print">
                <?php 
                if ($message) { 
                    ?>
                    <div class="alert alert-success">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <?=is_array($message) ? print_r($message, true) : $message;?>
                    </div>
                    <?php 
                } ?>
            </div>
            <div id="receipt-data">

                <div id="buttons" style="padding-top:10px; text-transform:uppercase;" class="no-print">

                    <?php
                    if ($modal) {
                        ?>
                        <div class="btn-group btn-group-justified" role="group" aria-label="...">
                            <div class="btn-group" role="group">
                                <?php
                                if ($pos->remote_printing == 1) {
                                    echo '<button onclick="window.print();" class="btn btn-block btn-primary">'.lang("print").'</button>';
                                } else {
                                    echo '<button onclick="return printReceipt()" class="btn btn-block btn-primary">'.lang("print").'</button>';
                                }

                                ?>
                            </div>
                            <!--<div class="btn-group" role="group">
                        <a class="btn btn-block btn-success" href="#" id="email"><?/*= lang("email"); */?></a>
                    </div>-->
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close'); ?></button>
                            </div>
                        </div>
                        <?php
                    } else {
                    ?>
                    <span class="pull-right col-xs-12">
                    <?php
                    if ($pos->remote_printing == 1) {
                        echo '<button onclick="window.print();" class="btn btn-block btn-primary">' . lang("print") . '</button>';
                    } else {
                        echo '<button onclick="return printReceipt()" class="btn btn-block btn-primary">' . lang("print") . '</button>';
                        echo '<button onclick="return openCashDrawer()" class="btn btn-block btn-default">' . lang("open_cash_drawer") . '</button>';
                    }
                    }
                    ?>
                <span class="col-xs-12">
                    <a class="btn btn-block btn-warning" href="<?= admin_url('pos'); ?>"><?= lang("back_to_pos"); ?></a>
                </span>
                </div>
                <div class="text-center col-xs-12">
		            <?php  if(!empty($biller->logo)){?>

                        <span class="col-xs-4">  <img style="width: 90px" height="90px" src="<?php echo base_url('assets/uploads/logos/'.$biller->logo)?>" alt=""></span>
		            <?php }
		            ?>
                    <span class="col-xs-8"> <h3 style="text-transform:uppercase;font-size:20px;font-weight:bold"><?=$biller->company != '-' ? $biller->company : $biller->name;?></h3>
			            <?php
			            if ($pos_settings->cf_title1 != "" && $pos_settings->cf_value1 != "") {
				            echo $pos_settings->cf_title1 . ": " . $pos_settings->cf_value1 . "<br>";
			            }?>
                    </span>
                    <span class="col-xs-12">
                    <?php
                    echo "<p>" . $biller->address . " " . $biller->city . " " . $biller->postal_code . " " . $state->statename . " " . $biller->country . "&nbsp;" . lang("tel") . ": " . $biller->phone;

                    // comment or remove these extra info if you don't need
                    if (!empty($biller->cf1) && $biller->cf1 != "-") {
	                    echo  lang("bcf1") . ": " . $biller->cf1;
                    }
                    if (!empty($biller->cf2) && $biller->cf2 != "-") {
	                    echo   lang("bcf2") . ": " . $biller->cf2;
                    }
                    if (!empty($biller->cf3) && $biller->cf3 != "-") {
	                    echo  lang("bcf3") . ": " . $biller->cf3;
                    }
                    if (!empty($biller->cf4) && $biller->cf4 != "-") {
	                    echo "<br>" . lang("bcf4") . ": " . $biller->cf4;
                    }
                    if (!empty($biller->cf5) && $biller->cf5 != "-") {
	                    echo "<br>" . lang("bcf5") . ": " . $biller->cf5;
                    }
                    if (!empty($biller->cf6) && $biller->cf6 != "-") {
	                    echo "<br>" . lang("bcf6") . ": " . $biller->cf6;
                    }
                    // end of the customer fields


                    if ($pos_settings->cf_title2 != "" && $pos_settings->cf_value2 != "") {
	                    echo "<br>";
	                    echo $pos_settings->cf_title2 . ": " . $pos_settings->cf_value2 . "<br>";
                    }
                    echo '</p>';
                    ?>
                    </span>
                </div>
                <?php
                if ($Settings->invoice_view == 1) {
                    ?>
                    <div class="col-sm-12 text-center">
                        <h4 style="font-weight:bold;"><?=lang('Retail_Invoice');?></h4>
                    </div>
                    <?php
                }
                echo "<p>" .lang("date") . ": " . $this->sma->hrld($inv->date) . "&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp&nbsp;&nbsp&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp&nbsp;&nbsp";
                echo lang("sale_no_ref") . ": " . $inv->reference_no . "</p>";
                if (!empty($inv->return_sale_ref)) {
                    echo '<p>'.lang("return_ref").': '.$inv->return_sale_ref;
                    if ($inv->return_id) {
                        echo ' <a data-target="#myModal2" data-toggle="modal" href="'.admin_url('sales/modal_view/'.$inv->return_id).'"><i class="fa fa-external-link no-print"></i></a><br>';
                    } else {
                        echo '</p>';
                    }
                }
                echo "<p>". lang("sales_person") . ": " . $created_by->first_name." ".$created_by->last_name . "</p>";
                echo "<p>";
                echo lang("customer") . ": " . ($customer->company && $customer->company != '-' ? $customer->company : $customer->name) . "&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp&nbsp;&nbsp&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp&nbsp;&nbsp";
                echo lang("membership_id") . ": " . ($customer->membership_id) . "<br>";
                if ($pos_settings->customer_details) {
                   /* if ($customer->vat_no != "-" && $customer->vat_no != "") {
                        echo "<br>" . lang("vat_no") . ": " . $customer->vat_no;
                    }*/

                   /* echo lang("tel") . ": " . $customer->phone . "<br>";
                    echo lang("tel") . ": " . $customer->phone . "<br>";
                    echo lang("address") . ": " . $customer->address . "&nbsp;";
                    echo $customer->city ." ".$customer->state." ".$customer->country ."&nbsp;";*/
                    if (!empty($customer->cf1) && $customer->cf1 != "-") {
                        echo "<br>" . lang("ccf1") . ": " . $customer->cf1;
                    }
                    if (!empty($customer->cf2) && $customer->cf2 != "-") {
                        echo "<br>" . lang("ccf2") . ": " . $customer->cf2;
                    }
                    if (!empty($customer->cf3) && $customer->cf3 != "-") {
                        echo "<br>" . lang("ccf3") . ": " . $customer->cf3;
                    }
                    if (!empty($customer->cf4) && $customer->cf4 != "-") {
                        echo "<br>" . lang("ccf4") . ": " . $customer->cf4;
                    }
                    if (!empty($customer->cf5) && $customer->cf5 != "-") {
                        echo "<br>" . lang("ccf5") . ": " . $customer->cf5;
                    }
                    if (!empty($customer->cf6) && $customer->cf6 != "-") {
                        echo "<br>" . lang("ccf6") . ": " . $customer->cf6;
                    }
                }
                echo "</p>";
                ?>

                <div style="clear:both;"></div>
                <table class="table table-striped table-condensed">
                    <tbody>
                        <?php
                        $r = 1; $category = 0;$i = 1;
                        $tax_summary = array();
                        $gst_perc = array();
                        $cess_perc = array();
                        $cess_amount = array();
                        $tempcgst='';$cgsttotperc=0;$sgsttotperc=0;$totcessperc=0;$totcessamt=0;
                        $sub_total_qty = 0;
                        $ret_subtotal = 0;
                        echo '<tr><th>' . lang("HSN") . '</th>
                            <th colspan="2">' . lang("Particulars") . '</th>
                            <th>' . lang("Qty") . '</th>
                            <th>' . lang("Rate") . '</th>
                            <th style="text-align: right">' . lang("Value") . '</th>
                            </tr>';
                        foreach ($rows as $row) {
                            $cgst=0;$sgst=0;$cess_rate=0;$cess_amt=0;
                            if ($pos_settings->item_order == 1 && $category != $row->category_id) {
                                $category = $row->category_id;
                                echo '<tr><td colspan="100%" class="no-border"><strong>'.$row->category_name.'</strong></td></tr>';
                            }
                            if ($Settings->invoice_view == 1) {
                                if (isset($tax_summary[$row->tax_code])) {
                                    $tax_summary[$row->tax_code]['items'] += $row->unit_quantity;
                                    $tax_summary[$row->tax_code]['tax'] += $row->item_tax;
                                    $tax_summary[$row->tax_code]['amt'] += ($row->unit_quantity * $row->net_unit_price) - $row->item_discount;
                                } else {
                                    $tax_summary[$row->tax_code]['items'] = $row->unit_quantity;
                                    $tax_summary[$row->tax_code]['tax'] = $row->item_tax;
                                    $tax_summary[$row->tax_code]['amt'] = ($row->unit_quantity * $row->net_unit_price) - $row->item_discount;
                                    $tax_summary[$row->tax_code]['name'] = $row->tax_name;
                                    $tax_summary[$row->tax_code]['code'] = $row->tax_code;
                                    $tax_summary[$row->tax_code]['rate'] = $row->tax_rate;
                                }
                            }

                           $cgst = $row->cgst_rate;
                           $sgst = $row->sgst_rate;
                           $cess_rate = $row->cess_rate;
                           $cess_amt = $row->cess_amt;
                           $total_item_tax = $row->item_tax;
                           if($cess_rate!='0.00') {
                               $cessmark = '*';
                               $totcessperc+=$cess_rate;
                               $totcessamt+=$cess_amt;
                               $total_item_tax-=$cess_amt;
                           }
                           else {
                               $cessmark = '';
                               $cess_rate = '0.00';
                               $cess_amt = '0.00';
                           }

                           $cgsttotperc += $cgst;
                           $sgsttotperc += $sgst;
                            $sub_total_qty += $row->quantity;


                            if($cgst!=$tempcgst) {
                                $tax_amount[$cgst] = 0;
                                $total_amount[$cgst] = 0;
                                $taxable_amount[$cgst] = 0;
                                $cess_amount [$cgst] = 0;
                                $gst_perc[] = $cgst;
                                $tax_['gstdata']['gstamt'][] = $cgst;

                                echo '<tr><td>' . $i++ . '</td>
                                        <td>CGST</td>
                                        <td>' . $cgst . '%</td>
                                        <td>SGST</td><td>' . $sgst . '%</td>
                                        <td></td>
										</tr>';
                            }
                            $tax_amount[$cgst] += $total_item_tax;
                            $total_amount[$cgst] += $row->subtotal;
                            $taxable_amount[$cgst] += ($row->net_unit_price*$row->quantity);
                            $cess_amount[$cgst] += $cess_amt;

                            $tempcgst = $cgst;

                            if(strstr($row->quantity,'.0000'))
                                $qntty= $this->sma->formatQuantity($row->quantity);
                            else
                                $qntty=  $this->sma->formatQuantity($row->quantity,3);

                            echo '<tr><td  class="no-border">' .substr($row->gst_code,0 ,4) . '</td>';
                                    if($english){
                                      echo '<td colspan="2">'.$cessmark. product_name($row->product_name, ($printer ? $printer->char_per_line : null)) . ($row->variant ? ' (' . $row->variant . ')' : '') . '<span class="pull-right">' . ($row->tax_code ? '*'.$row->tax_code : '') . '</span></td>';
                                      }else{

                                        $prod_name= (($pos_settings->language_for_receipt && $row->marathi_name) ? ($row->marathi_name) : ($row->product_name));
                                        //$row->marathi_name ? $row->marathi_name : $row->product_name
                                        echo '<td colspan="2">'.$cessmark. product_name($prod_name, ($printer ? $printer->char_per_line : null)) . ($row->variant ? ' (' . $row->variant . ')' : '') . '<span class="pull-right">' . ($row->tax_code ? '*'.$row->tax_code : '') . '</span></td>';
                                    }
                                       echo  '<td>' . $qntty . '</td>
                                        <td>'.$this->sma->formatDecimal($row->unit_price).'</td>
                                        <td style="text-align: right">'. $this->sma->formatDecimal($row->subtotal) . '</td>
                                        </tr>';


                            $r++;
                        }
                        //print_r($cess_amount);die;


                        ?>

                    </tbody>
                    <tfoot>

                        <tr>
                            <th><? #=lang("Item");?></th>
                            <th class=""><?php ?></th>
                             <th><?=lang("Qty");?></th>
                            <th class=""><?=$sub_total_qty;?></th>
                            <th><?=lang("total");?></th>
                            <th style="font-size:23px!important" class="text-right"><?=$this->sma->formatMoney(($inv->total + $inv->product_tax));?></th>
                        </tr>

                        <tr><td colspan="6">

                                <table width="100%" class="table table-striped table-condensed">
                                    <tr><th colspan="9" style="text-align: center"><b>GST Breakup Details</b></th> </tr>

                        <tr><th>Sr</th>
                            <th>Tax Amt</th>
                            <th>CGST%</th>
                            <th>CGST₹ </th>
                            <th>SGST%</th>
                            <th>SGST₹ </th>
                            <th style="text-align: right">Total Amt </th>
                        </tr>

                        <?php
                        $sr =1;$total_tax_amt=0;$sgst_amt=0;$cgst_amt=0;$cess_amt=0;$sub_total_tax=0;
                        $cgst_perc_total=0;$cess_perc_total=0;$tax_sub_amount=0;$cess_total=0;
                        foreach ($gst_perc as $perc){
                            $cgst_perc_total += $perc;
                            $tax_sub_amount +=$taxable_amount[$perc];

                           $total_tax_amt = $tax_amount[$perc];

                           $sub_total_tax +=$total_tax_amt;
                            $cess_total += $cess_amount[$perc];
                           if($perc!='00.0')
                           {
                               $sgst_amt = $total_tax_amt/2;
                               $cgst_amt = $sgst_amt;

                           }
?>


                            <tr><td><?=$sr++?></td>
                                <td><?=$this->sma->formatMoney($taxable_amount[$perc])?> </td>
                                <td><?=$perc?></td>
                                <td><?=$this->sma->formatMoney($cgst_amt)?></td>
                                <td><?=$perc?></td>
                                <td><?=$this->sma->formatMoney($sgst_amt)?></td>
                                <td align="right"><?=$this->sma->formatMoney($total_amount[$perc]-$cess_amount[$perc])?></td></tr>

                        <?php }
                        $cgst_sub_total =0; $sgst_sub_total=0;
                        $sgst_sub_total =  $sub_total_tax/2;
                        $cgst_sub_total = $sgst_sub_total;

                        ?>


                                    <tr><th>T</th>
                                        <th><?=$this->sma->formatMoney($tax_sub_amount)?></th>
                                        <th><?=$cgst_perc_total?></th>
                                        <th><?=$this->sma->formatMoney($cgst_sub_total)?></th>
                                        <th><?=$cgst_perc_total?></th>
                                        <th><?=$this->sma->formatMoney($sgst_sub_total)?></th>
                                        <th  style="text-align: right;font-size:23px;font-weight:bold" ><?=$this->sma->formatMoney(($inv->total + ($inv->product_tax) - $cess_total))?> </th></tr>
                                </table>
                            </td></tr>

<?php if($totcessperc!=0 ){ ?>

                        <tr><td colspan="6">

                                <table width="100%" class="table table-striped table-condensed">
                                    <tr><th colspan="2" style="text-align: center"><b>*CESS Details </b></th> </tr>

                                    <tr><th >CESS (%)</th><th style="text-align: right">CESS Total </th></tr>
                                   <tr><td><?=$this->sma->formatDecimal($totcessperc,2)?></td>
                                       <td style="text-align: right"><?=$this->sma->formatMoney($totcessamt)?></td></tr>
                                </table>
                            </td></tr>
<?php }?>

                        <?php


                        $retQty =0;
                        if ($return_rows) {
	                        echo '<tr class="warning"><td colspan="6" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';

	                        foreach ($return_rows as $row) {
		                        if ($pos_settings->item_order == 1 && $category != $row->category_id) {
			                        $category = $row->category_id;
			                        echo '<tr><td colspan="6" class="no-border"><strong>'.$row->category_name.'</strong></td></tr>';
		                        }



		                        if ($Settings->invoice_view == 1) {
			                        if (isset($tax_summary[$row->tax_code])) {
				                        $tax_summary[$row->tax_code]['items'] += $row->unit_quantity;
				                        $tax_summary[$row->tax_code]['tax'] += $row->item_tax;
				                        $tax_summary[$row->tax_code]['amt'] += ($row->unit_quantity * $row->net_unit_price) - $row->item_discount;
			                        } else {
				                        $tax_summary[$row->tax_code]['items'] = $row->unit_quantity;
				                        $tax_summary[$row->tax_code]['tax'] = $row->item_tax;
				                        $tax_summary[$row->tax_code]['amt'] = ($row->unit_quantity * $row->net_unit_price) - $row->item_discount;
				                        $tax_summary[$row->tax_code]['name'] = $row->tax_name;
				                        $tax_summary[$row->tax_code]['code'] = $row->tax_code;
				                        $tax_summary[$row->tax_code]['rate'] = $row->tax_rate;
			                        }
		                        }
		                        echo '<tr><td colspan="3" class="no-border">' . substr($row->gst_code,0 ,4) . ': &nbsp;&nbsp;' . product_name($row->product_name, ($printer ? $printer->char_per_line : null)) . ($row->variant ? ' (' . $row->variant . ')' : '') . '<span class="pull-right">' . ($row->tax_code ? '*'.$row->tax_code : '') . '</span></td>';
		                        echo '<td class="no-border border-bottom" colspan="2">' . $this->sma->formatQuantity($row->quantity) . ' x '.$this->sma->formatDecimal($row->unit_price).'</td><td class="no-border border-bottom text-right">' . $this->sma->formatDecimal($row->subtotal) . '</td><td></td></tr>';


		                        $r++;
		                        $retQty += $row->quantity;
		                        $ret_subtotal += $row->subtotal;
	                        }



                        $sub_total_qty = $sub_total_qty+$retQty;

?>
                        <tr>
                            <th><? #=lang("Item");?></th>
                        <th class=""><?php ?></th>
                        <th><?=lang("Qty");?></th>
                        <th class=""><?=$sub_total_qty;?></th>
                        <th><?=lang("total");?></th>
                        <th class="text-right"><?=$this->sma->formatMoney($return_sale ? (($inv->total + $inv->product_tax)+($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax));?></th>
                            </tr>
                        <?php }
                        if ($inv->order_tax != 0) {
                            echo '<tr><th colspan="5">' . lang("tax") . '</th><th class="text-left">' . $this->sma->formatMoney($return_sale ? ($inv->order_tax+$return_sale->order_tax) : $inv->order_tax) . '</th></tr>';
                        }
                        if ($inv->order_discount != 0) {
                            echo '<tr><th colspan="5">' . lang("order_discount") . '</th><th class="text-right">' . $this->sma->formatMoney($inv->order_discount) . '</th></tr>';
                        }

                        if ($inv->shipping != 0) {
                            echo '<tr><th colspan="5">' . lang("shipping") . '</th><th class="text-right">' . $this->sma->formatMoney($inv->shipping) . '</th></tr>';
                        }

                        if ($return_sale) {
                            if ($return_sale->surcharge != 0) {
                                echo '<tr><th colspan="5">' . lang("order_discount") . '</th><th class="text-right">' . $this->sma->formatMoney($return_sale->surcharge) . '</th></tr>';
                            }
                        }

                        if ($pos_settings->rounding || $inv->rounding != 0) {
                            ?>
                            <tr>
                                <th colspan="5"><?=lang("rounding");?></th>
                                <th class="text-right"><?= $this->sma->formatMoney($inv->rounding);?></th>
                            </tr>
                            <tr>
                                <th colspan="5"><?=lang("grand_total");?></th>
                                <th class="text-right" style="font-size:23px;font-weight:bold"><?=$this->sma->formatMoney($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding));?></th>
                            </tr>
                            <?php
                        } else {
                            ?>
                            <tr>
                                <th colspan="5"><?=lang("grand_total");?></th>
                                <th style="font-size:23px;font-weight:bold" class="text-right"><?=$this->sma->formatMoney($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total);?></th>
                            </tr>
                            <?php
                        }
                        //print_r($inv->paid);die;
                        if ($inv->paid < $inv->grand_total) {
                            ?>
                            <tr>
                                <th colspan="5"><?=lang("paid_amount");?></th>
                                <th class="text-right"><?=$this->sma->formatMoney($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid);?></th>
                            </tr>
                            <tr>
                                <th colspan="5"><?=lang("due_amount");?></th>
                                <th class="text-right"><?=$this->sma->formatMoney(($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid));?></th>
                            </tr>
                            <?php
                        } ?>

                        <tr>
                            <th colspan="5"><?=lang("Total Weight");?></th>
                            <th class="text-right"><?=$this->sma->formatDecimal($inv->total_weight);?> kg</th>
                        </tr>
                        <tr>
                            <th colspan="5"><?=lang("Total Saving");?></th>
                            <th  class="text-right">
                                <?=$this->sma->formatMoney($inv->total_mrp-$inv->grand_total);?></th>
                        </tr>
                    </tfoot>
                </table>
                <?php
                if ($payments) {
                    echo '<table class="table table-striped table-condensed"><tbody>';
                    foreach ($payments as $payment) {
                        echo '<tr>';
                        if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("change") . ': ' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                        } elseif (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe')) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("no") . ': ' . 'xxxx xxxx xxxx ' . substr($payment->cc_no, -4) . '</td>';
                            echo '<td>' . lang("name") . ': ' . $payment->cc_holder . '</td>';
                        } elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("cheque_no") . ': ' . $payment->cheque_no . '</td>';
                        } elseif ($payment->paid_by == 'gift_card' && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("no") . ': ' . $payment->cc_no . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("balance") . ': ' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                        } elseif ($payment->paid_by == 'other' && $payment->amount) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo $payment->note ? '</tr><td colspan="2">' . lang("payment_note") . ': ' . $payment->note . '</td>' : '';
                        }
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                }

                if ($return_payments) {
                    echo '<strong>'.lang('return_payments').'</strong><table class="table table-striped table-condensed"><tbody>';
                    foreach ($return_payments as $payment) {
                        $payment->amount = (0-$payment->amount);
                        echo '<tr>';
                        if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("change") . ': ' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                        } elseif (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("no") . ': ' . 'xxxx xxxx xxxx ' . substr($payment->cc_no, -4) . '</td>';
                            echo '<td>' . lang("name") . ': ' . $payment->cc_holder . '</td>';
                        } elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("cheque_no") . ': ' . $payment->cheque_no . '</td>';
                        } elseif ($payment->paid_by == 'gift_card' && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("no") . ': ' . $payment->cc_no . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("balance") . ': ' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                        } elseif ($payment->paid_by == 'other' && $payment->amount) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo $payment->note ? '</tr><td colspan="2">' . lang("payment_note") . ': ' . $payment->note . '</td>' : '';
                        }
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                }
                // commented when discussion done with sunil
               /* if ($Settings->invoice_view == 1) {
                    if (!empty($tax_summary)) {
                        echo '<h4 style="font-weight:bold;">' . lang('tax_summary') . '</h4>';
                        echo '<table class="table table-condensed"><thead><tr><th class="text-center">' . lang('name') . '</th><th class="text-center">' . lang('code') . '</th><th class="text-center">' . lang('qty') . '</th><th class="text-center">' . lang('tax_excl') . '</th><th class="text-center">' . lang('tax_amt') . '</th></tr></td><tbody>';
                        foreach ($tax_summary as $summary) {
                            echo '<tr><td>' . $summary['name'] . '</td><td class="text-center">' . $summary['code'] . '</td><td class="text-center">' . $this->sma->formatQuantity($summary['items']) . '</td><td class="text-right">' . $this->sma->formatMoney($summary['amt']) . '</td><td class="text-right">' . $this->sma->formatMoney($summary['tax']) . '</td></tr>';
                        }
                        echo '</tbody></tfoot>';
                        echo '<tr><th colspan="4" class="text-right">' . lang('total_tax_amount') . '</th><th class="text-right">' . $this->sma->formatMoney($return_sale ? $inv->product_tax+$return_sale->product_tax : $inv->product_tax) . '</th></tr>';
                        echo '</tfoot></table>';
                    }
                }*/
                ?>
               

               
                <?= empty($is_default_customer) && floor($points['total_points'])!= 0 && $Settings->each_spent > 0 ?'<p class="text-center">'.lang('reward_points').'= '.(int)$points['old_points'].' (Balance) + '.floor($points['current_points']).' (Purchase) = '.floor($points['total_points']).' (Total Balance) </p>'  : ''; ?>

                <?= $inv->note ? '<p class="text-center">' . $this->sma->decode_html($inv->note) . '</p>' : ''; ?>
                <?= $inv->staff_note ? '<p class="no-print"><strong>' . lang('staff_note') . ':</strong> ' . $this->sma->decode_html($inv->staff_note) . '</p>' : ''; ?>
                <?= $biller->invoice_footer ? '<p class="text-center">'.$this->sma->decode_html($biller->invoice_footer).'</p>' : ''; ?>
            </div>

            <div class="order_barcodes text-center">
                <?# $this->sma->save_barcode($inv->reference_no, 'code128', 66, false); ?>
                <br>
                <?# $this->sma->qrcode('link', urlencode(admin_url('sales/view/' . $inv->id)), 2); ?>
                <div>Designed &amp; Developed by <span style="font-weight: bold;">LogicoWise Systems Pune.</span></div>
                <div>Contact Email.- <span style="font-weight: bold;">logicowise@gmail.com</span>&nbsp;</div>
                <div>Website- <span style="font-weight: bold;">www.logicowise.com</span></div>
            </div>
            <div style="clear:both;"></div>
        </div>

        <div id="buttons" style="padding-top:10px; text-transform:uppercase;" class="no-print">
            <hr>
            <?php
            if ($message) {
                ?>
                <div class="alert alert-success">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <?=is_array($message) ? print_r($message, true) : $message;?>
                </div>
                <?php
            } ?>
            <?php
            if ($modal) {
                ?>
                <div class="btn-group btn-group-justified" role="group" aria-label="...">
                    <div class="btn-group" role="group">
                        <?php
                        if ($pos->remote_printing == 1) {
                            echo '<button onclick="window.print();" class="btn btn-block btn-primary">'.lang("print").'</button>';
                        } else {
                            echo '<button onclick="return printReceipt()" class="btn btn-block btn-primary">'.lang("print").'</button>';
                        }

                        ?>
                    </div>
                    <!--<div class="btn-group" role="group">
                        <a class="btn btn-block btn-success" href="#" id="email"><?/*= lang("email"); */?></a>
                    </div>-->
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close'); ?></button>
                    </div>
                </div>
                <?php
            } else {
                ?>
                <span class="pull-right col-xs-12">
                    <?php
                    if ($pos->remote_printing == 1) {
                        echo '<button onclick="window.print();" class="btn btn-block btn-primary">'.lang("print").'</button>';
                    } else {
                        echo '<button onclick="return printReceipt()" class="btn btn-block btn-primary">'.lang("print").'</button>';
                        echo '<button onclick="return openCashDrawer()" class="btn btn-block btn-default">'.lang("open_cash_drawer").'</button>';
                    }
                    ?>
                </span>
               <!-- <span class="pull-left col-xs-12"><a class="btn btn-block btn-success" href="#" id="email"><?/*= lang("email"); */?></a></span>-->
                <span class="col-xs-12">
                    <a class="btn btn-block btn-warning" href="<?= admin_url('pos'); ?>"><?= lang("back_to_pos"); ?></a>
                </span>
                <?php
            }
            if ($pos->remote_printing == 1) {
                ?>
                <div style="clear:both;"></div>
                <div class="col-xs-12" style="background:#F5F5F5; padding:10px;">
                    <p style="font-weight:bold;">
                        Please don't forget to disable the header and footer in browser print settings.
                    </p>
                    <p style="text-transform: capitalize;">
                        <strong>FF:</strong> File &gt; Print Setup &gt; Margin &amp; Header/Footer Make all --blank--
                    </p>
                    <p style="text-transform: capitalize;">
                        <strong>chrome:</strong> Menu &gt; Print &gt; Disable Header/Footer in Option &amp; Set Margins to None
                    </p>
                </div>
                <?php 
            } ?>
            <div style="clear:both;"></div>
        </div>
    </div>

    <?php
    if( ! $modal) {
        ?>
        <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
        <?php
    }

    ?>
    <script type="text/javascript">

            <?php /*
 $(document).ready(function () {
            $('#email').click(function () {
                bootbox.prompt({
                    title: "<?= lang("email_address"); ?>",
                    inputType: 'email',
                    value: "<?= $customer->email; ?>",
                    callback: function (email) {
                        if (email != null) {
                            $.ajax({
                                type: "post",
                                url: "<?= admin_url('pos/email_receipt') ?>",
                                data: {<?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>", email: email, id: <?= $inv->id; ?>},
                                dataType: "json",
                                success: function (data) {
                                    bootbox.alert({message: data.msg, size: 'small'});
                                },
                                error: function () {
                                    bootbox.alert({message: '<?= lang('ajax_request_failed'); ?>', size: 'small'});
                                    return false;
                                }
                            });
                        }
                    }
                });
                return false;
            });
        });
*/?>
        <?php
        if ($pos_settings->remote_printing == 1) {
        ?>

            //alert(is_chrome());
            if(is_chrome()){
                $(window).load(function () {
                    window.print();
                    setTimeout(function(){ window.location='<?= admin_url('pos'); ?>';}, 1000);
                    //give them 10 seconds to print, then close

                    return false;
                });
            }//if chrome
            else{
                $(window).load(function () {
                    window.print();
                    window.location='<?= admin_url('pos'); ?>';
                    return false;
                });
            }

            <?php
            }
            ?>
            function is_chrome (){
                return Boolean(window.chrome)
            }
    </script>
    <?php /* include FCPATH.'themes'.DIRECTORY_SEPARATOR.$Settings->theme.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'pos'.DIRECTORY_SEPARATOR.'remote_printing.php'; */ ?>
    <?php include 'remote_printing.php'; ?>
    <?php
    if($modal) {
        ?>
    </div>
</div>
</div>
<?php 
} else {
    ?>
</body>
</html>
<?php
}
?>