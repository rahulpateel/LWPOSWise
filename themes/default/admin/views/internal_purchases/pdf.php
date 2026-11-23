<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->lang->line("internal_purchase") . " " . $inv->reference_no; ?></title>
    <link href="<?= $assets ?>styles/pdf/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">
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
                        <br><?=lang("ref");?>: <?=$inv->reference_no;?></div>
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

                <div class="col-xs-5">
                    <?= $this->lang->line("to"); ?>
                    <h2 class=""><?=$Settings->site_name;?></h2>
                    <?=$warehouse->name?>

                    <?php
                        echo $warehouse->address . "<br>";
                        echo ($warehouse->phone ? lang("tel") . ": " . $warehouse->phone . "<br>" : '') . ($warehouse->email ? lang("email") . ": " . $warehouse->email : '');
                    ?>
                    <div class="clearfix"></div>
                </div>

                <div class="col-xs-5">
                    <?= $this->lang->line("from"); ?>
                    <h2 class=""><?=$branch->name;?></h2>
                    <?php
                        echo $branch->address . $branch->city . " " . $branch->postal_code . " " . $branch->state . "<br />" . $branch->country;
                        
                        echo lang("tel") . ": " . $branch->phone . "<br />" . lang("email") . ": " . $branch->email;
                    ?>
                    
                    <div class="clearfix"></div>
                </div>

            </div>
            <p>&nbsp;</p>

            <div class="clearfix"></div>
            <?php
                $col = 7;
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
            <div class="col-xs-12">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr class="active">
                        <td style="width: 10px; text-align:center; vertical-align:middle;"><?=lang("no");?></td>
                        <td style="width: 10px; text-align:center; vertical-align:middle;"><?=lang("description");?></td>
                        <td style="width: 10px; text-align:center; vertical-align:middle;"><?=lang("Qty");?></td>
                        <?php
                            if ($inv->status == 'partial') {
                                echo '<td style="width: 10px; text-align:center; vertical-align:middle;">'.lang("Recd").'</td>';
                            }
                        ?>
                        <td style="width: 10px; text-align:center; vertical-align:middle;"><?=lang("unit_cost");?></td>
                        <?php
/*                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<th>' . lang("tax") . '</th>';
                            }
                        */?>
                        <td class="col-md-1" style="width: 10px;"><?= lang("CGST %"); ?></td>
                        <td class="col-md-1"><?= lang("CGST Amt"); ?></th>
                        <td class="col-md-1" style="width: 10px;"><?= lang("SGST %"); ?></td>
                        <td class="col-md-1"><?= lang("SGST Amt"); ?></td>
                        <!-- <td class="col-md-1" style="width: 10px;"><?= lang("IGST %"); ?></td> -->
                        <!-- <td class="col-md-1"><?= lang("IGST Amt"); ?></td> -->
                        <td style="width: 10px; text-align:center; vertical-align:middle;"><?=lang("subtotal");?></td>
                    </tr>

                        <?php
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<th>' . lang("discount") . '</th>';
                            }
                        ?>


                    </thead>
                    <tbody>
                    <?php
                        $r = 1;
                        foreach ($rows as $row):
                            ?>
                            <tr>
                                <td style="width: 10px; text-align:center; vertical-align:middle;"><?=$r;?></td>
                                <td style="width: 10px; text-align:center; vertical-align:middle;">
                                    <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                    <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                                    <?=$row->details ? '<br>' . $row->details : '';?>
                                    <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' .lang('expiry').': ' . $this->sma->hrsd($row->expiry) : ''; ?>
                                </td>
                                <td style="width: 10px; text-align:center; vertical-align:middle;"><?=$this->sma->formatQuantity($row->quantity).' '.$row->product_unit_code;?></td>
                                <?php
                                    if ($inv->status == 'partial') {
                                        echo '<td style="text-align:center;vertical-align:middle;width:10px;">'.$this->sma->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>';
                                    }
                                ?>
                                <td style="text-align:right; width:10px;"><?=$this->sma->formatMoney($row->real_unit_cost);?></td>
                                <?php
                                    /*if ($Settings->tax1 && $inv->product_tax > 0) {
                                        echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>(' . $row->tax_code . ')</small> ' : '') . $this->sma->formatMoney($row->item_tax) . '</td>';
                                    }*/
                                if ($row->quantity_received == $row->unit_quantity) {
                                    $tcol = $col - 2;
                                    $col = $tcol + 2;
                                }
                                ?>
                                <?php

                                echo '<td >' . $this->sma->formatDecimal($row->cgst_rate) . '</td>';
                                echo '<td >' .$this->sma->formatMoney($row->cgst_amt) . '</td>';
                                echo '<td >' .$this->sma->formatDecimal($row->sgst_rate). '</td>';
                                echo '<td >' .$this->sma->formatMoney($row->sgst_amt) . '</td>';
                               /* echo '<td >' .$this->sma->formatDecimal($row->igst_rate). '</td>';
                                echo '<td >' .$this->sma->formatMoney($row->igst_amt). '</td>';*/


                                    if ($Settings->product_discount && $inv->product_discount != 0) {
                                        echo '<td style="width: 90px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                                    }
                                ?>
                                <td style="width: 10px; text-align:center; vertical-align:middle;"><?=$this->sma->formatMoney($row->subtotal);?></td>
                            </tr>
                            <?php
                            $r++;
                        endforeach;
                        if ($return_rows) {
                            echo '<tr class="warning"><td colspan="'.($col+1).'" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';
                            foreach ($return_rows as $row):
                            ?>
                                <tr>
                                    <td style="text-align:center; width:20px; vertical-align:middle;"><?=$r;?></td>
                                    <td style="vertical-align:middle;">
                                        <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                        <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                                        <?=$row->details ? '<br>' . $row->details : '';?>
                                        <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' .lang('expiry').': ' . $this->sma->hrsd($row->expiry) : ''; ?>
                                    </td>
                                    <td style="width: 20px; text-align:center; vertical-align:middle;"><?=$this->sma->formatQuantity($row->quantity).' '.$row->product_unit_code;?></td>
                                    <?php
                                        if ($inv->status == 'partial') {
                                            echo '<td style="text-align:center;vertical-align:middle;width:30px;">'.$this->sma->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>';
                                        }
                                    ?>
                                    <td style="text-align:right; width:20px;"><?=$this->sma->formatMoney($row->real_unit_cost);?></td>
                                    <?php
                                        if ($Settings->tax1 && $inv->product_tax > 0) {
                                            echo '<td style="width: 20px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>(' . $row->tax_code . ')</small> ' : '') . $this->sma->formatMoney($row->item_tax) . '</td>';
                                        }
                                        if ($Settings->product_discount && $inv->product_discount != 0) {
                                            echo '<td style="width: 20px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                                        }


                                    echo '<td >' . $this->sma->formatDecimal($row->cgst_rate) . '</td>';
                                    echo '<td >' .$this->sma->formatMoney($row->cgst_amt) . '</td>';
                                    echo '<td >' .$this->sma->formatDecimal($row->sgst_rate). '</td>';
                                    echo '<td >' .$this->sma->formatMoney($row->sgst_amt) . '</td>';
                                    /*echo '<td >' .$this->sma->formatDecimal($row->igst_rate). '</td>';
                                    echo '<td >' .$this->sma->formatMoney($row->igst_amt). '</td>';*/

                                    ?>
                                    <td style="text-align:right; width:10px;"><?=$this->sma->formatMoney($row->subtotal);?></td>
                                </tr>

                                <?php
                                $r++;
                            endforeach;
                        }
                    ?>

                    </tbody>
                    <tfoot>

                    <?php if ($inv->grand_total != $inv->total) { ?>
                        <tr>
                            <td colspan="<?= $tcol+2; ?>"
                                style="text-align:right;"><?= lang("total"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <?php
                           /* if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_purchase ? ($inv->product_tax+$return_purchase->product_tax) : $inv->product_tax) . '</td>';
                            }*/
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_purchase ? ($inv->product_discount+$return_purchase->product_discount) : $inv->product_discount) . '</td>';
                            }
                            ?>
                            <td style="text-align:right;"><?= $this->sma->formatMoney($return_purchase ? (($inv->total + $inv->product_tax)+($return_purchase->total + $return_purchase->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                        </tr>
                    <?php } ?>
                    <?php
                    if ($return_purchase) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang("return_total") . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($return_purchase->grand_total) . '</td></tr>';
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
                        <td colspan="<?= $col; ?>"
                            style="text-align:right; font-weight:bold;"><?= lang("total_amount"); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($return_purchase ? ($inv->grand_total+$return_purchase->grand_total) : $inv->grand_total); ?></td>
                    </tr>
                    <tr>
                        <td colspan="<?= $col; ?>"
                            style="text-align:right; font-weight:bold;"><?= lang("paid"); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($return_purchase ? ($inv->paid+$return_purchase->paid) : $inv->paid); ?></td>
                    </tr>
                    <tr>
                        <td colspan="<?= $col; ?>"
                            style="text-align:right; font-weight:bold;"><?= lang("balance"); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney(($return_purchase ? ($inv->grand_total+$return_purchase->grand_total) : $inv->grand_total) - ($return_purchase ? ($inv->paid+$return_purchase->paid) : $inv->paid)); ?></td>
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