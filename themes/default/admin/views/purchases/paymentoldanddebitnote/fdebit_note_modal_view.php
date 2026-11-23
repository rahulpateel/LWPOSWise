<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <div class="row">
                <div class="col-xs-7">
                    <h3 class="text-right">DEBIT NOTE</h3>
                </div>
                <div class="col-xs-1 col-xs-offset-3">
                    <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                        <i class="fa fa-print"></i> <?= lang('print'); ?>
                    </button>
                </div>
                <div class="col-xs-1">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i class="fa fa-2x">&times;</i>
                    </button>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="row">
                <div class="col-xs-4">
                    <h2 style="margin-top:10px;"><?= $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
                    <?= $biller->company ? "" : "Attn: " . $biller->name ?>

                    <?php
                    $billerstatedata = $this->purchases_model->getStateByID($biller->state);
                    echo $biller->address . "<br>" . $biller->city . " " . $biller->postal_code . " " . $billerstatedata->statename . "<br>" . $biller->country;
                    ?>
                </div>
                <div class="col-xs-4">
                <?php if ($logo) { ?>
                <div class="text-center" style="margin-bottom:20px;">
                    <img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>"
                         alt="<?= $Settings->site_name; ?>">
                </div>
            <?php } ?> 
                </div>
                <div class="col-xs-4 text-right order_barcodes">
                        <?= $this->sma->save_barcode($inv->ret_refrence_no, 'code128', 66, false); ?>
                        <!-- <?= $this->sma->qrcode('link', urlencode(admin_url('purchases/view/' . $inv->id)), 2); ?> -->
                    </div>
            <div class="clearfix"></div>
            </div>
            <div class="clearfix"></div>
            <div class="well" style="padding: 0px;background-color: white;margin-bottom: 5px;">
                <div class="row bold">
                    <div class="col-xs-12">
                    <p class="bold" style="margin : 5px 5px 5px;">
                        INFORMATION
                    </p>
                    </div>
                    
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="clearfix"></div>
           <div class="well well-sm" style="padding: 0px;background-color: white;margin-bottom: 5px;">
                <div class="row">
                    <div class="col-xs-6">
                        <div class="col-xs-4">
                            <h2 style="margin-top:10px;">Vender</h2>
                        </div>
                        <div class="col-xs-8">
                            <?php 
                $igstflag = false; $gstcols=2;
                if($supplier->state!=$Settings->state){  $igstflag = true;$gstcols = 1;}
                ?>
                            <h2 style="margin-top:10px;margin-bottom: 0px;"><?= $supplier->company ? $supplier->company : $supplier->name; ?></h2>
                            <?= $supplier->company ? "" : "Attn: " . $supplier->name ?>
                            <?php
                            echo $supplier->address . "<br />" . $supplier->city . " " . $supplier->postal_code . "," . $supplier_state->statename . "<br />" . $supplier->country;

                           /* echo "<p>";
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

                        echo "</p>";*/
                        echo lang("tel") . ": " . $supplier->phone . "<br />" . lang("email") . ": " . $supplier->email;
                            
                            ?>
                        </div>
                        <div class="col-xs-4">
                            <h2 style="margin-top:10px;">GSTIN NO</h2>
                        </div>
                        <div class="col-xs-6">
                            <?php  echo "<p style='margin-top:10px;''>";if ($supplier->vat_no != "-" && $supplier->vat_no != "") {
                            echo  $supplier->vat_no;
                            echo "</p>";
                    } 
                            ?>
                        </div>                    
                    </div>
                    <div class="col-xs-6">
                        <div class="row">
                        <div class="col-xs-4 col-xs-offset-2">
                            <p style="margin-bottom: 0px;">Debit Note Ref No</p>
                        </div>
                        <div class="col-xs-6">
                           <?= $inv->ret_refrence_no; ?>
                        </div>
                        </div>
                        <div class="row">
                        <div class="col-xs-4 col-xs-offset-2">
                            <p style="margin-bottom: 0px;">Debit Note Date</p>
                        </div>
                        <div class="col-xs-6">
                           <?= date('d-m-Y H:i:s',strtotime($inv->date)); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-4 col-xs-offset-2">
                            <p style="margin-bottom: 0px;">Vender Invoice Ref No</p>
                        </div>
                        <div class="col-xs-6">
                           <?= !empty($purchase)?$purchase->ext_pur_ref_no:""; ?>
                        </div>
                    </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>


        

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped print-table order-table">

                    <thead>

                    <tr>
                        <th><?= lang("no"); ?></th>
                        <th><?= lang("description"); ?></th>
                        <th><?= lang("quantity"); ?></th>
                        <?php
                            if ($inv->status == 'partial') {
                                echo '<th>'.lang("received").'</th>';
                            }
                        ?>
                        <th><?= lang("unit_cost"); ?></th>
                        <?php if(!$igstflag){?>
                            <th class="col-md-1"><?= lang("CGST %"); ?></th>
                            <th class="col-md-1"><?= lang("CGST Amt"); ?></th>
                            <th  class="col-md-1"><?= lang("SGST %"); ?></th>
                            <th class="col-md-1"><?= lang("SGST Amt"); ?></th>
                        <?php } else {?>
                            <th  class="col-md-1"><?= lang("IGST %"); ?></th>
                            <th class="col-md-1"><?= lang("IGST Amt"); ?></th>
                        <?php }?>
                        <th style="width: 20px;" class="col-md-1"><?= lang("CESS %"); ?></th>
                        <th style="width: 20px;" class="col-md-1"><?= lang("CESS Amt"); ?></th>
                        <?php
                       /* if ($Settings->tax1 && $inv->product_tax > 0) {
                            echo '<th>' . lang("tax") . '</th>';
                        }*/
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            echo '<th>' . lang("discount") . '</th>';
                        }
                        ?>
                        <th><?= lang("subtotal"); ?></th>
                    </tr>

                    </thead>

                    <tbody>

                    <?php $r = 1;$cgst_total=0;$sgst_total=0;$cess_total=0;$igst_total=0;
                    $tax_summary = array();
                    foreach ($rows as $row):
                    ?>
                        <tr>
                            <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                            <td style="vertical-align:middle;">
                                <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                                <?= $row->details ? '<br>' . $row->details : ''; ?>
                                <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>'.lang('expiry').': ' . $this->sma->hrsd($row->expiry) : ''; ?>
                            </td>
                            <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                            <?php
                            if ($inv->status == 'partial') {
                                echo '<td style="text-align:center;vertical-align:middle;width:80px;">'.$this->sma->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>';
                            }
                            ?>
                            <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->real_unit_cost); ?></td>
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
                            ?>
                            <td ><?= $row->cess_rate?> </td>
                            <td ><?= $this->sma->formatMoney($row->cess_amt)?> </td>
                            <?php
                            /*if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>('.$row->tax_code.')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
                            }*/
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                            }
                            ?>
                            
                            <td style="text-align:right; width:120px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                            

                        </tr>
                        <?php
                        $r++;
                    endforeach;
                    if ($return_rows) {
                        echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';
                        foreach ($return_rows as $row):
                        ?>
                            <tr class="warning">
                                <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                <td style="vertical-align:middle;">
                                    <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                    <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                                    <?= $row->details ? '<br>' . $row->details : ''; ?>
                                    <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>'.lang('expiry').': ' . $this->sma->hrsd($row->expiry) : ''; ?>
                                </td>
                                <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                                <?php
                                if ($inv->status == 'partial') {
                                    echo '<td style="text-align:center;vertical-align:middle;width:80px;">'.$this->sma->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>';
                                }
                                ?>
                                <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->real_unit_cost); ?></td>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>('.$row->tax_code.')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
                                }
                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                    echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                                }
                                ?>
                                <td style="text-align:right; width:120px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                            </tr>
                            <?php
                            $r++;
                        endforeach;
                    }
                    ?>
                    </tbody>
                    <tfoot>
                    <?php
                    $col = 7+$gstcols;
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
                        $tcol = $col - 2;
                    } elseif ( $Settings->product_discount && $inv->product_discount != 0) {
                        $tcol = $col - 1;
                    } elseif ($Settings->tax1 && $inv->product_tax > 0) {
                        $tcol = $col - 1;
                    } else {
                        $tcol = $col;
                    }
                    ?>
                    <?php if ($inv->grand_total != $inv->total) { ?>
                        <tr>
                            <?php if(!$igstflag) {?>
                            <td colspan="<?= $tcol-5; ?>"
                                style="text-align:right; padding-right:10px;"><?= lang("Total Tax"); ?>
                            </td>
                            <td 
                                style="text-align:right; padding-right:10px;"><?= lang("CGST"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td
                                style="text-align:right; padding-right:10px;">
                                <?= $this->sma->formatMoney($cgst_total); ?>
                            </td>
                            <td 
                                style="text-align:right; padding-right:10px;"><?= lang("SGST"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td
                                style="text-align:right; padding-right:10px;">
                                <?= $this->sma->formatMoney($sgst_total); ?>
                            </td>
                            <td
                                style="text-align:right; padding-right:10px;"><?= lang("CESS"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td
                                style="text-align:right; padding-right:10px;">
                                <?= $this->sma->formatMoney($cess_total); ?>
                            </td>
                            <?php }else{?>
                            <td colspan="<?= $tcol-5; ?>"
                                style="text-align:right; padding-right:10px;"><?= lang("Total Tax"); ?>
                            </td>
                            <td 
                                style="text-align:right; padding-right:10px;"><?= lang("IGST"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td
                                style="text-align:right; padding-right:10px;">
                                <?= $this->sma->formatMoney($igst_total); ?>
                            </td>
                            <?php }?>
                             <?php
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_purchase ? ($inv->product_discount+$return_purchase->product_discount) : $inv->product_discount) . '</td>';
                            }
                            ?>
                            <td  style="text-align:right; padding-right:10px;"><?= $this->sma->formatMoney($return_purchase ? ($inv->product_tax+$return_purchase->product_tax) : $inv->product_tax); ?></td>
                        </tr>
                    <?php } ?>
                    <?php
                    if ($return_purchase) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("return_total") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($return_purchase->grand_total) . '</td></tr>';
                    }
                    
                    ?>

                    <?php if ($inv->order_discount != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("order_discount") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">'.($inv->order_discount_id ? '<small>('.$inv->order_discount_id.')</small> ' : '') . $this->sma->formatMoney($return_purchase ? ($inv->order_discount+$return_purchase->order_discount) : $inv->order_discount) . '</td></tr>';
                    }
                    ?>
                    <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang("order_tax") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($return_purchase ? ($inv->order_tax+$return_purchase->order_tax) : $inv->order_tax) . '</td></tr>';
                    }
                    ?>
                    <?php if ($inv->shipping != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("shipping") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->shipping) . '</td></tr>';
                    }
                    ?>
                    <tr>
                        <td colspan="<?= $inv->product_tax>0 ? $col : $col+1; ?>"
                            style="text-align:right; font-weight:bold;"><?= lang("total_amount"); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->sma->formatMoney($return_purchase ? ($inv->grand_total+$return_purchase->grand_total) : $inv->grand_total); ?></td>
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
                        <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney(($return_purchase ? ($inv->grand_total+$return_purchase->grand_total) : $inv->grand_total) - ($return_purchase ? ($inv->paid+$return_purchase->paid) : $inv->paid)); ?></td>
                    </tr>
                    <tr>
                        <td colspan="8">

                                <table width="100%" class="table table-striped table-condensed">
                                    <tr><th colspan="9" style="text-align: center"><b>GST Breakup Details</b></th> </tr>

                        <tr><th>Sr</th>
                            <th>Tax Amt</th>
                            <?php if(!$igstflag){?>
                            <th>CGST%</th>
                            <th>CGST₹ </th>
                            <th>SGST%</th>
                            <th>SGST₹ </th>
                            <?php } else{ ?>
                            <th>IGST%</th>                            
                            <th>IGST₹ </th>
                            <?php }?>
                            <th>CESS%</th>
                            <th>CESS₹ </th>
                            <th style="text-align: right">Total Amt </th>
                        </tr>

                        <?php
                        $sr =1;
                       
                        foreach ($taxdata as $value){ ?>
                           <tr><td><?=$sr++?></td>
                                <?php if(!$igstflag){?>
                                <td><?= $this->sma->formatMoney(($value->item_tax)-($value->cgst_total)-($value->sgst_total)-($value->cess_total))?> </td> 
                                <td><?=$value->cgst_rate ?></td>
                                <td><?=$this->sma->formatMoney($value->cgst_total)?></td>
                                <td><?=$value->sgst_rate?></td>
                                <td><?=$this->sma->formatMoney($value->sgst_total)?></td>
                                <?php }else{?>
                                <td><?=$this->sma->formatMoney(($value->item_tax)-($value->igst_total)-($value->cess_total))?> </td>
                                <td><?=$value->igst_rate?></td>                              
                                <td><?=$this->sma->formatMoney($value->igst_total)?></td>
                                <?php } ?>
                                <td><?=$value->cess_rate?></td>
                                <td><?=$this->sma->formatMoney($value->cess_total)?></td>
                                <td><?=$this->sma->formatMoney($value->item_tax)?> </td>

                        <?php }
                        
                        ?>


                                   
                                </table>
                            </td>
                    </tr>
                    <tr>
                        <td colspan="6" style="text-align:left;">
                            <p>PREPERED BY :</p>
                            <p>Date :</p>
                        </td>
                        <td colspan="6" style="text-align:left;">
                            <p>APPROVED BY :</p>
                            <p>Date :</p>
                        </td>
                    </tr>

                    </tfoot>
                </table>
            </div>

            <div class="row">
                

                <div class="col-xs-5 pull-right">
                    <div class="well well-sm">
                        <p>
                            <?= lang("created_by"); ?>: <?= $created_by->first_name . ' ' . $created_by->last_name; ?> <br>
                            <?= lang("date"); ?>: <?= $this->sma->hrld($inv->date); ?>
                        </p>
                      
                    </div>
                </div>
            </div>
            <?php if (!$Supplier || !$Customer) { ?>
                <div class="buttons">
                    
                    <div class="btn-group btn-group-justified">
                      
                        <div class="btn-group">
                            <a href="<?= admin_url('purchases/email/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('email') ?>">
                                <i class="fa fa-envelope-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= admin_url('purchases/fdebit_note_pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                                <i class="fa fa-download"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                            </a>
                        </div>
                       
						
						<?php if(!$dn_paid){ ?>
                        <div class="btn-group">
                            <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete") ?></b>"
                                data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('purchases/delete_debit_note/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                data-html="true" data-placement="top">
                                <i class="fa fa-trash-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                            </a>
                        </div>
						<?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready( function() {
        $('.tip').tooltip();
    });
</script>
