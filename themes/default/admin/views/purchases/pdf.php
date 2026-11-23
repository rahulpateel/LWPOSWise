<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->lang->line("purchase") . " " . $inv->reference_no; ?></title>
    <link href="<?= $assets ?>styles/pdf/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">
	  <style type="text/css">

                    td{
                        font-size:12px!important;
                    }
                    .table td {
    padding: 1px!important;
}
                </style>
</head>
<body>
<div id="wrap">
    <div class="row">
        <div class="col-lg-12">
            <?php if ($logo) {
                $path = base_url() . 'assets/uploads/logos/' . $Settings->logo;
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                ?>
                <div class="text-center" style="margin-bottom:20px;">
                    <img src="<?= $base64; ?>" alt="<?=$Settings->site_name;?>">
                </div>
            <?php }
            ?>

        <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-4"><?=lang("date");?>: <?=$this->sma->hrld($inv->date);?>

                          <?php 
                                                                
                                       
                                        if(!empty($inv->supplier_inv_date) && ($inv->supplier_inv_date!='0000-00-00 00:00:00') )
                                        {?>
                             <br> <?= lang("Supplier_invoice_date"); ?>: <?= $this->sma->hrld($inv->supplier_inv_date); ?><br>              
                                        <?php }
                                        ?>
                        <br><?=lang("ref");?>: <?=$inv->reference_no;?>
                        <br>
                     <?php if(!empty($inv->ext_pur_ref_no)){echo lang("Ext_Reference_No")?>: <?php echo $inv->ext_pur_ref_no;?><?php }?></div>
                    <div class="col-xs-6 pull-right text-right order_barcodes">
                        <?= $this->sma->save_barcode($inv->reference_no, 'code128', 66, false); ?>
                        <?php /*echo $this->sma->qrcode('link', urlencode(admin_url('purchases/view/' . $inv->id)), 2);*/ ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="clearfix"></div>
            <div class="row">
	            <?php
	            $igstflag = false; $gstcols=2;
	            if($supplier->state!=$Settings->state){  $igstflag = true;$gstcols = 0;}
	            $supplierstatedata = $this->purchases_model->getStateByID($supplier->state);
	            $billerstatedata = $this->purchases_model->getStateByID($Settings->state);
	            ?>
                <div class="col-xs-5">
                    <?= $this->lang->line("to"); ?>
                    <h2 class=""><?=$supplier->company ? $supplier->company : $supplier->name;?></h2>
                    <?=$supplier->company ? "" : "Attn: " . $supplier->name?>
                    <?php
                        echo $supplier->address . "<br />" . $supplier->city . " " . $supplier->postal_code . " " . $supplierstatedata->statename . "<br />" . $supplier->country;
                        echo "<p>";
                        if ($supplier->vat_no != "-" && $supplier->vat_no != "") {
                            echo "<br>" . lang("vat_no") . ": " . $supplier->vat_no;
                        }
                        if ($supplier->cf1 != "-" && $supplier->cf1 != "") {
                            echo "<br>" . lang("scf1") . ": " . $supplier->cf1;
                        }
                        if ($supplier->cf2 != "-" && $supplier->cf2 != "") {
                            echo "<br>" . lang("scf2") . ": " . $supplier->cf2;
                        }
                        if ($supplier->cf3 != "-" && $supplier->cf3 != "") {
                            echo "<br>" . lang("scf3") . ": " . $supplier->cf3;
                        }
                        if ($supplier->cf4 != "-" && $supplier->cf4 != "") {
                            echo "<br>" . lang("scf4") . ": " . $supplier->cf4;
                        }
                        if ($supplier->cf5 != "-" && $supplier->cf5 != "") {
                            echo "<br>" . lang("scf5") . ": " . $supplier->cf5;
                        }
                        if ($supplier->cf6 != "-" && $supplier->cf6 != "") {
                            echo "<br>" . lang("scf6") . ": " . $supplier->cf6;
                        }
                        echo "</p>";
                        echo lang("tel") . ": " . $supplier->phone . "<br />" . lang("email") . ": " . $supplier->email;
                    ?>
                    <div class="clearfix"></div>
                </div>

                <div class="col-xs-5">
                    <?= $this->lang->line("from"); ?>
                   <h2 style="margin-top:10px;"><?= $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
                    <?= $biller->company ? "" : "Attn: " . $biller->name ?>

                    <?php
                    $billerstatedata = $this->purchases_model->getStateByID($biller->state);
                    echo $biller->address . "<br>" . $biller->city . " " . $biller->postal_code . " " . $billerstatedata->statename . "<br>" . $biller->country;
                    ?>
                    <div class="clearfix"></div>
                </div>

            </div>
            <p>&nbsp;</p>

            <div class="clearfix"></div>
            <?php
                $col = 8+$gstcols;
                if ($inv->status == 'partial') {
                    $col++;
                }
                if ($Settings->product_discount && $inv->product_discount != 0) {
                    $col++;
                }
                if ($Settings->tax1 && $inv->product_tax > 0) {
                    $col++;
                }
                if ( $Settings->product_discount && $inv->product_discount != 0 && $Settings->tax1 && $inv->product_tax > 0) {
                    $tcol = $col - 3;
                } elseif ( $Settings->product_discount && $inv->product_discount != 0) {
                    $tcol = $col - 2;
                } elseif ($Settings->tax1 && $inv->product_tax > 0) {
                    $tcol = $col - 2;
                } else {
                    $tcol = $col;
                }

            ?>
            <div class="col-xs-6">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped" style="font-size: 10px">
                    <thead>
                    <tr class="active">
                        <th><?=lang("no");?></th>
                        <th><?=lang("description");?></th>
                        <th><?=lang("Qty");?></th>
                        <th><?=lang("MRP");?></th>
                        <?php
                            if ($inv->status == 'partial') {
                                echo '<td style="width: 10px; text-align:center; vertical-align:middle;">'.lang("Recd").'</td>';
                            }
                        ?>
                        <th><?=lang("unit_cost");?></th>
                        <?php
/*                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<th>' . lang("tax") . '</th>';
                            }
                        */?>
	                    <?php if(!$igstflag){?>
                            <th class="col-md-1"><?= lang("CGST %"); ?></th>
                            <th class="col-md-1"><?= lang("CGST Amt"); ?></th>
                            <th  class="col-md-1"><?= lang("SGST %"); ?></th>
                            <th class="col-md-1"><?= lang("SGST Amt"); ?></th>
	                    <?php } else {?>
                            <th  class="col-md-1"><?= lang("IGST %"); ?></th>
                            <th class="col-md-1"><?= lang("IGST Amt"); ?></th>
	                    <?php }?>
                        <th  class="col-md-1"><?= lang("CESS %"); ?></th>
                        <th class="col-md-1"><?= lang("CESS Amt"); ?></th>
                        <th style="width: 10px; text-align:center; vertical-align:middle;"><?=lang("subtotal");?></th>
                    </tr>

                        <?php
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<th>' . lang("discount") . '</th>';
                            }
                        ?>


                    </thead>
                    <tbody>
                    <?php
                        $r = 1;$cgst_total=0;$sgst_total=0;$cess_total=0;$igst_total=0;
                        $rcgst_total=0;$rsgst_total=0;$rcess_total=0;$rigst_total=0;$return_total=0;
                        foreach ($rows as $row):
                            ?>
                            <tr>
                                <td style="width: 20px; text-align:center; vertical-align:middle;"><?=$r;?></td>
                                <td style="width: 30px; text-align:center; vertical-align:middle;">
                                    <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                    <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                                    <?='<strong>'.($row->gst_code ? ' (HSN :' . $row->gst_code . ')' : '');'</strong>'?>
                                    <?=$row->details ? '<br>' . $row->details : '';?>
                                    <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' .lang('expiry').': ' . $this->sma->hrsd($row->expiry) : ''; ?>
                                </td>
                                <td style="width: 30px; text-align:center; vertical-align:middle;"><?=$this->sma->formatQuantity($row->quantity).' '.$row->product_unit_code;?></td>
                                <td style="width: 30px; text-align:center; vertical-align:middle;"><?=$this->sma->formatMoney($row->mrp)?></td>
                                <?php
                                    if ($inv->status == 'partial') {
                                        echo '<td style="text-align:center;vertical-align:middle;width:10px;">'.$this->sma->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>';
                                    }
                                ?>
                                <td style="vertical-align:middle; width:40px;"><?=$this->sma->formatMoney($row->real_unit_cost);?></td>
                                <?php

                                if ($row->quantity_received == $row->unit_quantity) {
                                    $tcol = $col - 2;
                                    $col = $tcol + 2;
                                }
                                ?>
	                            <?php
	                            if(!$igstflag) {
                                    $cgst_total+=$row->cgst_amt;
                                    $sgst_total+=$row->sgst_amt;
                                    $cess_total+=$row->cess_amt; 
		                            echo '<td  style="text-align:right; vertical-align:middle;">' .( $row->cgst_rate ) . '</td>';
		                            echo '<td  style="text-align:right; vertical-align:middle;">' . $this->sma->formatMoney( $row->cgst_amt ) . '</td>';
		                            echo '<td  style="text-align:right; vertical-align:middle;">' .( $row->sgst_rate ) . '</td>';
		                            echo '<td  style="text-align:right; vertical-align:middle;">' . $this->sma->formatMoney( $row->sgst_amt ) . '</td>';
	                            }else {
                                    $igst_total+=$row->igst_amt;
		                            echo '<td  style="text-align:right; vertical-align:middle;">' .( $row->igst_rate ) . '</td>';
		                            echo '<td  style="text-align:right; vertical-align:middle;">' . $this->sma->formatMoney( $row->igst_amt ) . '</td>';
	                            }
	                            echo '<td style="text-align:right; vertical-align:middle;">' .$row->cess_rate. '</td>';
	                            echo '<td style="text-align:right; vertical-align:middle;">' .$this->sma->formatMoney($row->cess_amt). '</td>';


                                    if ($Settings->product_discount && $inv->product_discount != 0) {
                                        echo '<td style="width: 90px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                                    }
                                ?>
                                <td style="width: 10px; text-align:right; vertical-align:middle;"><?=$this->sma->formatMoney($row->subtotal);?></td>
                            </tr>
                            <?php
                            $r++;
                        endforeach;
                        if ($return_rows) {
                            echo '<tr class="warning"><td colspan="'.($col+1).'" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';
                            foreach ($return_rows as $row):
                            ?>
                                <tr class="warning">
                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?=$r;?></td>
                                    <td style="vertical-align:middle;">
                                        <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                        <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                                        <?=$row->details ? '<br>' . $row->details : '';?>
                                        <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' .lang('expiry').': ' . $this->sma->hrsd($row->expiry) : ''; ?>
                                    </td>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?=$this->sma->formatMoney($row->mrp);?></td>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?=$this->sma->formatQuantity($row->quantity).' '.$row->product_unit_code;?></td>
                                    <?php
                                        if ($inv->status == 'partial') {
                                            echo '<td style="text-align:center;vertical-align:middle;width:90px;">'.$this->sma->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>';
                                        }
                                    ?>
                                    <td style="text-align:right; width:20px;"><?=$this->sma->formatMoney($row->real_unit_cost);?></td>
                                    <?php
                                       /* if ($Settings->tax1 && $inv->product_tax > 0) {
                                            echo '<td style="width: 20px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>(' . $row->tax_code . ')</small> ' : '') . $this->sma->formatMoney($row->item_tax) . '</td>';
                                        }*/
                                        if ($Settings->product_discount && $inv->product_discount != 0) {
                                            echo '<td style="width: 20px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                                        }
                                    if(!$igstflag) {
                                        $rcgst_total+=$row->cgst_amt;
                                        $rsgst_total+=$row->sgst_amt;
                                        $rcess_total+=$row->cess_amt;
                                    echo '<td >' . $this->sma->formatDecimal($row->cgst_rate) . '</td>';
                                    echo '<td >' .$this->sma->formatMoney($row->cgst_amt) . '</td>';
                                    echo '<td >' .$this->sma->formatDecimal($row->sgst_rate). '</td>';
                                    echo '<td >' .$this->sma->formatMoney($row->sgst_amt) . '</td>';
                                    }else {
                                        $rigst_total+=$row->igst_amt;
                                    echo '<td >' .$this->sma->formatDecimal($row->igst_rate). '</td>';
                                    echo '<td >' .$this->sma->formatMoney($row->igst_amt). '</td>';
                                    }
                                    echo '<td >' .$this->sma->formatDecimal($row->cess_rate). '</td>';
                                    echo '<td >' .$this->sma->formatMoney($row->cess_amt). '</td>';
                                    ?>
                                    <td style="text-align:right; width:110px;"><?=$this->sma->formatMoney($row->subtotal);?></td>
                                </tr>

                                <?php
	                            $return_total += $row->subtotal;
                                $r++;
                            endforeach;
                        }
                    ?>

                    </tbody>
                    <tfoot>

                    <?php if ($inv->grand_total != $inv->total) { ?>
                        <tr>
                             <?php if(!$igstflag) {?>
                            <td colspan="<?= $tcol-4; ?>"
                                style="text-align:right; padding-right:10px;"><?= lang("Total Tax"); ?>
                            </td>
                            <td 
                                style="text-align:right; padding-right:10px;"><?= lang("CGST"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td
                                style="text-align:right; padding-right:10px;">
                                <?= $this->sma->formatMoney($cgst_total+$rcgst_total); ?>
                            </td>
                            <td 
                                style="text-align:right; padding-right:10px;"><?= lang("SGST"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td
                                style="text-align:right; padding-right:10px;">
                                <?= $this->sma->formatMoney($sgst_total+$rsgst_total); ?>
                            </td>
                            <?php }else{?>
                            <td colspan="<?= $tcol-4; ?>"
                                style="text-align:right; padding-right:10px;"><?= lang("Total Tax"); ?>
                            </td>
                            <td 
                                style="text-align:right; padding-right:10px;"><?= lang("IGST"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td
                                style="text-align:right; padding-right:10px;">
                                <?= $this->sma->formatMoney($igst_total+$ricgst_total); ?>
                            </td>
                            <?php }?>
                            <td
                                style="text-align:right; padding-right:10px;"><?= lang("CESS"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td
                                style="text-align:right; padding-right:10px;">
                                <?= $this->sma->formatMoney($cess_total); ?>
                            </td>
                            <?php
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_purchase ? ($inv->product_discount+$return_purchase->product_discount) : $inv->product_discount) . '</td>';
                            }
                            ?>
                            <td style="text-align:right;"><?= $this->sma->formatMoney($return_purchase ? ($inv->grand_total+$return_total) : $inv->product_tax); ?></td>
                        </tr>
                    <?php } ?>
                    <?php
                    if ($return_purchase) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang("return_total") . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($return_total) . '</td></tr>';
                    }
                    if ($inv->surcharge != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang("return_surcharge") . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($inv->surcharge) . '</td></tr>';
                    }
                    ?>
                    <?php if ($inv->order_discount != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang("order_discount") . ' (' . $default_currency->code . ')</td><td style="text-align:right;">'.($inv->order_discount_id ? '<small>('.$inv->order_discount_id.')</small> ' : '') . $this->sma->formatMoney($return_purchase ? ($inv->order_discount+$return_purchase->order_discount) : $inv->order_discount) . '</td></tr>';
                    }
                    ?>
                    <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang("order_tax") . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($return_purchase ? ($inv->order_tax+$return_purchase->order_tax) : $inv->order_tax) . '</td></tr>';
                    }
                    ?>
                    <?php if ($inv->shipping != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang("shipping") . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($inv->shipping) . '</td></tr>';
                    }
                    ?>
                    <tr>
                        <td colspan="<?= $inv->product_tax>0 ? $col : $col+1; ?>"
                            style="text-align:right; font-weight:bold;"><?= lang("total_amount"); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($return_purchase ? ($inv->grand_total+$return_total) : $inv->grand_total); ?></td>
                    </tr>
                    <tr>
                        <td colspan="<?= $inv->product_tax>0 ? $col : $col+1; ?>"
                            style="text-align:right; font-weight:bold;"><?= lang("paid"); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($return_purchase ? ($inv->paid+$return_purchase->paid) : $inv->paid); ?></td>
                    </tr>
                    <tr>
                        <td colspan="<?= $inv->product_tax>0 ? $col : $col+1; ?>"
                            style="text-align:right; font-weight:bold;"><?= lang("balance"); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney(($return_purchase ? ($inv->grand_total+$return_total) : $inv->grand_total) - ($return_purchase ? ($inv->paid+$return_purchase->paid) : $inv->paid)); ?></td>
                    </tr>

                    </tfoot>
                </table>
            </div>
            </div>
            <div class="clearfix"></div>

            <div class="col-xs-12">

                    <?php if ($inv->note || $inv->note != "") {?>
                        <div class="well well-sm">
                            <p class="bold"><?=lang("note");?>:</p>

                            <div><?=$this->sma->decode_html($inv->note);?></div>
                        </div>
                    <?php }
                    ?>
                </div>
               <div class="clearfix"></div>
                <div class="col-xs-4 pull-left">
                    <p><?=lang("order_by");?>: <?=$created_by->first_name . ' ' . $created_by->last_name;?> </p>



                    <p>&nbsp;</p>



                </div>
            <div class="col-xs-4 pull-right"> <hr>
                <p><?=lang("stamp_sign");?></p></div>
            </div>

        </div>
    </div>
</div>
</body>
</html>