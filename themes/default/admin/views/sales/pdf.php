<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->lang->line('sale') . ' ' . $inv->reference_no; ?></title>
    <link href="<?= $assets ?>styles/pdf/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">
</head>

<body>
<div id="wrap" style="font-size: 11px">
    <div class="row">
        <div class="col-lg-12">
            <div class="col-xs-6 pull-left">
            <?php if ($logo) {
                $path = base_url() . 'assets/uploads/logos/' . $biller->logo;

                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                ?>
                <div class="text-left" style="margin-bottom:20px;">
                    <img src="<?= $base64; ?>" alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>">
                </div>
            <?php }
            ?>
            </div>

            <div class="col-xs-6 pull-right text-right order_barcodes">
		        <?= $this->sma->save_barcode($inv->reference_no, 'code128', 66, false); ?>
		        <?php /*echo $this->sma->qrcode('link', urlencode(admin_url('sales/view/' . $inv->id)), 2);*/ ?>
            </div>
            <div class="clearfix"></div>
            <div>
	            <?php
	            $custstatedata = $this->companies_model->getStateByID($customer->state);
	            $billerstatedata = $this->companies_model->getStateByID($biller->state);
	            ?>
                <div class="col-xs-6">
				<p style="font-weight:bold;"><?= lang("ref"); ?>: <?= $inv->reference_no; ?></p>
                    <?php echo $this->lang->line("to"); ?>:
                    <h2 class=""><?= $customer->company ? $customer->company : $customer->name; ?></h2>
                    <?= $customer->company ? '' : 'Attn: ' . $customer->name; ?>
                    <?php
                    echo $customer->address . '<br />' . $customer->city . ',' . $customer->postal_code . '<br />' . $custstatedata->statename . ',' . $customer->country;
                    echo '<p>';
                    if ($customer->vat_no != "-" && $customer->vat_no != "") {
                        echo "<br>" . lang("vat_no") . ": " . $customer->vat_no;
                    }

                    echo '</p>';
                    echo lang('tel') . ': ' . $customer->phone . '<br />' . lang('email') . ': ' . $customer->email;
                    ?>
                </div>

                <div class="col-xs-6">
				 <p style="font-weight:bold;"><?= lang("date"); ?>: <?= $this->sma->hrld($inv->date); ?></p>
                    <?php echo $this->lang->line("from"); ?>:
                    <h2 class=""><?= $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
                    <?= $biller->company ? '' : 'Attn: ' . $biller->name; ?>
                    <?php
                    echo $biller->address . '<br />' . $biller->city . ',' . $biller->postal_code . '<br />' . $billerstatedata->statename . ',' . $biller->country;

                    if ($biller->vat_no != "-" && $biller->vat_no != "") {
                        echo "<br>" . lang("vat_no") . ": " . $biller->vat_no;
                    }
                    echo "<br>" ;
                    echo lang('tel') . ': ' . $biller->phone . '<br />' . lang('email') . ': ' . $biller->email;
                    ?>
                    <div class="clearfix"></div>
                </div>

            </div>
            <div class="clearfix"></div>
          
            <div class="clearfix"></div>
        </div>
    </div>

    <div class="clearfix"></div>
    <?php
    $col = 10;
    if ( $Settings->product_discount && $inv->product_discount != 0) {
        $col++;
    }
    if ($Settings->tax1 ) {//&& $inv->product_tax > 0
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
            <table class="table table-bordered table-hover table-striped" style="font-size: 6px">
                <thead>
                <tr>
                    <th><?=lang("no");?></th>
                    <th><?= lang('description'); ?></th>
                    <th><?= lang('qty'); ?></th>
	                <?php
	                if ($Settings->product_serial) {
		                echo '<th style="text-align:center; vertical-align:middle;">' . lang("serial_no") . '</th>';
	                }
	                ?>
                    <th><?= lang('unit_price'); ?></th>
                    <th style="10px"; class="col-md-1"><?= lang("CGST %"); ?></th>
                    <th class="col-md-1"><?= lang("CGST Amt"); ?></th>
                    <th style="10px"; class="col-md-1"><?= lang("SGST %"); ?></th>
                    <th class="col-md-1"><?= lang("SGST Amt"); ?></th>
                    <th style="10px"; class="col-md-1"><?= lang("IGST %"); ?></th>
                    <th class="col-md-1"><?= lang("IGST Amt"); ?></th>
                    <th><?= lang('subtotal'); ?></th>

                    <?php
                    /* if ($Settings->tax1 && $inv->product_tax > 0) {
                         echo '<th>' . lang('tax') . '</th>';
                     }*/
                    if ( $Settings->product_discount && $inv->product_discount != 0) {
                        echo '<th>' . lang('discount') . '</th>';
                    }
                    ?>


                </thead>
                <tbody>
                <?php $r = 1;
                foreach ($rows as $row):
                    ?>
                    <tr>
                        <td style="width: 10px; text-align:left; vertical-align:top;"><?= $r; ?></td>
                        <td style="width: 10px; text-align:left; vertical-align:top;">
                             <?= $row->product_code.' - '.$row->product_name . ($row->gst_code ? ' (' . $row->gst_code . ')' : ''); ?>
                                    <?= $row->details ? '<br>' . $row->details : ''; ?>
                        </td>
                        <td style="width: 10px; text-align:left; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
	                    <?php
	                    if ($Settings->product_serial) {
		                    echo '<td style="text-align:center; vertical-align:middle;">' . $row->serial_no . '</td>';
	                    }
	                    ?>
                        <td style="width: 10px; text-align:left; vertical-align:middle;"><?= $this->sma->formatMoney($row->real_unit_price); ?></td>


                        <?php

                        echo '<td  style="text-align:left; vertical-align:middle;">' . $this->sma->formatMoney($row->cgst_rate) . '</td>';
                        echo '<td  style="text-align:left; vertical-align:middle;">' .$this->sma->formatMoney($row->cgst_amt) . '</td>';
                        echo '<td  style="text-align:left; vertical-align:middle;">' .$this->sma->formatMoney($row->sgst_rate). '</td>';
                        echo '<td  style="text-align:left; vertical-align:middle;">' .$this->sma->formatMoney($row->sgst_amt) . '</td>';
                        echo '<td  style="text-align:left; vertical-align:middle;">' .$this->sma->formatMoney($row->igst_rate). '</td>';
                        echo '<td  style="text-align:left; vertical-align:middle;">' .$this->sma->formatMoney($row->igst_amt). '</td>';
                        /* if ($Settings->tax1 && $inv->product_tax > 0) {
                             echo '<td style="width: 90px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>(' . $row->tax_code . ')</small> ' : '') . $this->sma->formatMoney($row->item_tax) . '</td>';
                         }*/
                        if ( $Settings->product_discount && $inv->product_discount != 0) {
                            echo '<td style="width: 20px; text-align:left; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                        }
                        ?>
                        <td style="width: 10px; text-align:left; vertical-align:middle;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                    </tr>
                    <?php
                    $r++;
                endforeach;
                if ($return_rows) {
                    echo '<tr class="warning"><td colspan="'.($col+1).'" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';
                    foreach ($return_rows as $row):
					
					      if($row->quantity==0 || empty($row->quantity) )
						  {
							  continue;
						  }
                        ?>
                        <tr class="warning">
                            <td style="text-align:left; width:40px; vertical-align:middle;"><?= $r; ?></td>
                            <td style="vertical-align:top;">
                                <?= $row->product_code.' - '.$row->product_name . ($row->gst_code ? ' (' . $row->gst_code . ')' : ''); ?>
                                <?= $row->details ? '<br>' . $row->details : ''; ?>
                                <?= $row->serial_no ? '<br>' . $row->serial_no : ''; ?>
                            </td>
                            <td style="width: 80px; text-align:left; vertical-align:top;"><?= $this->sma->formatQuantity($row->quantity).' '.$row->product_unit_code; ?></td>
                            <td style="text-align:right; width:90px;vertical-align:middle;"><?= $this->sma->formatMoney($row->real_unit_price); ?></td>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>('.$row->tax_code.')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
                            }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                            }
                            ?>
                            <td style="text-align:right; width:110px;vertical-align:middle;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                        </tr>
                        <?php
                        $r++;
                    endforeach;
                }
                ?>
                </tbody>
                <tfoot>

                <?php if ($inv->grand_total != $inv->total) {
                    ?>
                    <tr>
                        <td colspan="<?= $tcol-1; ?>" style="text-align:right;"><?= lang('Tax'); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <?php if ($Settings->tax1 && $inv->product_tax > 0) {
                            echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_sale ? ($inv->product_tax+$return_sale->product_tax) : $inv->product_tax) . '</td>';
                        }
                        ?>
                        <td style="text-align:right;"><?= lang('total'); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <?php

                        if ( $Settings->product_discount && $inv->product_discount != 0) {
                            echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_sale ? ($inv->product_discount+$return_sale->product_discount) : $inv->product_discount) . '</td>';
                        }
                        ?>
                        <td style="text-align:right;"><?= $this->sma->formatMoney($return_sale ? (($inv->total + $inv->product_tax)+($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                    </tr>
                <?php }
                ?>
                <?php
                if ($return_sale) {
                    echo '<tr><td colspan="' . $col-1 . '" style="text-align:right;">' . lang("return_total") . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($return_sale->grand_total) . '</td></tr>';
                }
                if ($inv->surcharge != 0) {
                    echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang("return_surcharge") . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($inv->surcharge) . '</td></tr>';
                }
                ?>
                <?php if ($inv->order_discount != 0) {
                    echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang('order_discount') . ' (' . $default_currency->code . ')</td><td style="text-align:right;">'.($inv->order_discount_id ? '<small>('.$inv->order_discount_id.')</small> ' : '') . $this->sma->formatMoney($return_sale ? ($inv->order_discount+$return_sale->order_discount) : $inv->order_discount) . '</td></tr>';
                }
                ?>
                <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                    echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang('order_tax') . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($return_sale ? ($inv->order_tax+$return_sale->order_tax) : $inv->order_tax) . '</td></tr>';
                }
                ?>
                <?php if ($inv->shipping != 0) {
                    echo '<tr><td colspan="' . ($col -1). '" style="text-align:right;">' . lang('shipping') . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($inv->shipping) . '</td></tr>';
                }
                ?>
                <tr>
                    <td colspan="<?= ($col-1) ?>"
                        style="text-align:right; font-weight:bold;"><?= lang('total_amount'); ?>
                        (<?= $default_currency->code; ?>)
                    </td>
                    <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total); ?></td>
                </tr>

                <tr>
                    <td colspan="<?= $col-1; ?>" style="text-align:right; font-weight:bold;"><?= lang('paid'); ?>
                        (<?= $default_currency->code; ?>)
                    </td>
                    <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid); ?></td>
                </tr>
                <tr>
                    <td colspan="<?= $col-1; ?>" style="text-align:right; font-weight:bold;"><?= lang('balance'); ?>
                        (<?= $default_currency->code; ?>)
                    </td>
                    <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney(($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid)); ?></td>
                </tr>

                </tfoot>
            </table>
        </div>
    </div>
    <div class="clearfix"></div>

    <div class="col-xs-12">
        <?php if ($inv->note || $inv->note != '') { ?>
            <div class="well well-sm">
                <p class="bold"><?= lang('note'); ?>:</p>

                <div><?= $this->sma->decode_html($inv->note); ?></div>
            </div>
        <?php }
        ?>
    </div>
    <div class="clearfix"></div>
    <div class="col-xs-4 pull-left">
        <p style="height: 80px;"><?= lang('seller'); ?>
            : <?= $biller->company != '-' ? $biller->company : $biller->name; ?> </p>
        <hr>
        <p><?= lang('stamp_sign'); ?></p>
    </div>
    <div class="col-xs-4 pull-right">
        <p style="height: 80px;"><?= lang('customer'); ?>
            : <?= $customer->company ? $customer->company : $customer->name; ?> </p>
        <hr>
        <p><?= lang('stamp_sign'); ?></p>
        <?php if ($customer->award_points != 0 && $Settings->each_spent > 0) { ?>
            <div class="well well-sm">
                <?=
                '<p>'.lang('this_sale').': '.floor(($inv->grand_total/$Settings->each_spent)*$Settings->ca_point)
                .'<br>'.
                lang('total').' '.lang('award_points').': '. $customer->award_points . '</p>';?>
            </div>
        <?php } ?>
    </div>
    <div class="clearfix"></div>

</div>
</div>
</div>
</body>
</html>