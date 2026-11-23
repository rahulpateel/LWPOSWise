<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <?php if ($logo) { ?>
                <div class="text-center" style="margin-bottom:20px;">
                    <img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>"
                         alt="<?= $Settings->site_name; ?>">
                </div>
            <?php } ?>
            <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-5">
                    <p class="bold">
                        <?= lang("date"); ?>: <?= $this->sma->hrld($inv->date); ?><br>
                        <?= lang("ref"); ?>: <?= $inv->reference_no; ?><br>
                        <?= lang("status"); ?>: <?= lang($inv->status); ?><br>
                        <?= lang("payment_status"); ?>: <?= lang($inv->payment_status); ?>
                    </p>
                    </div>
                    <div class="col-xs-7 text-right order_barcodes">
                        <?= $this->sma->save_barcode($inv->reference_no, 'code128', 66, false); ?>
                        <?# $this->sma->qrcode('link', urlencode(admin_url('purchases/view/' . $inv->id)), 2); ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="row" style="margin-bottom:15px;">
                <div class="col-xs-6">
                    <?php echo $this->lang->line("to"); ?>:
                    <h2 style="margin-top:10px;"><?= $Settings->site_name; ?></h2>
                    <?= $warehouse->name ?>

                    <?php
                    echo $warehouse->address;
                    echo ($warehouse->phone ? lang("tel") . ": " . $warehouse->phone . "<br>" : '') . ($warehouse->email ? lang("email") . ": " . $warehouse->email : '');
                    ?>                    
                </div>
                <div class="col-xs-6">
                    <?php echo $this->lang->line("from"); ?>:<br/>
                    <h2 style="margin-top:10px;"><?= $branch->name; ?></h2>
                    <?php
                    echo $branch->address . $branch->city . " " . $branch->postal_code . " " . $state->statename . "<br />" . $branch->country;
                    echo lang("tel") . ": " . $branch->phone . "<br />" . lang("email") . ": " . $branch->email;
                    ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped print-table order-table">

                    <thead>

                    <tr>
                        <th><?= lang("no"); ?></th>
                        <th><?= lang("description"); ?></th>
                        <!-- <th><?= lang("brand"); ?></th> -->
                        <th><?= lang("quantity"); ?></th>
                        <?php
                            if ($inv->status == 'pending') {
                                echo '<th>'.lang("sold").'</th>';
                                echo '<th>'.lang("remaining").'</th>';
                            }
                        ?>
                    </tr>

                    </thead>

                    <tbody>

                    <?php $r = 1;$qun = 0;$rqun =0;$remqun=0;
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
                            <!-- <td style="vertical-align:middle;"><?= $inv->brand ?></td> -->
                            <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                            <?php $qun += $row->unit_quantity; ?>
                            <?php
                            if ($inv->status == 'pending') {
                                echo '<td style="text-align:center;vertical-align:middle;width:80px;">'.$this->sma->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>';
                                echo '<td style="text-align:center;vertical-align:middle;width:80px;">'.$this->sma->formatQuantity($row->unit_quantity - $row->quantity_received).' '.$row->product_unit_code.'</td>';
                                $rqun +=$row->quantity_received;
                                $remqun +=($row->unit_quantity - $row->quantity_received);
                            }
                            ?>
                        </tr>
                        <?php
                        $r++;
                    endforeach;
                    ?>
                    </tbody>
                    <tfoot>
                    <?php
                    $col = 3;
                    if ($inv->status == 'partial') {
                        $col++;
                    }
                    ?>
                    <?php if ($inv->grand_total != $inv->total) { ?>
                        <tr>
                            <td colspan="<?= $col-1; ?>"
                                style="text-align:right; padding-right:10px;"><?= lang("Total Quantity"); ?>
                            </td>
                            <td style="text-align:center"><?= $qun.' '.$row->product_unit_code ?>
                            </td>
                            <?php
                            if ($inv->status == 'pending') {
                                echo '<td style="text-align:center;vertical-align:middle;width:80px;">'.$this->sma->formatQuantity($rqun).' '.$row->product_unit_code;'</td>';
                                echo '<td style="text-align:center;vertical-align:middle;width:80px;">'.$this->sma->formatQuantity($remqun).' '.$row->product_unit_code;'</td>';
                            }
                            ?>                            
                        </tr>
                    <?php } ?>
                    <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang("order_tax") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($return_purchase ? ($inv->order_tax+$return_purchase->order_tax) : $inv->order_tax) . '</td></tr>';
                    }
                    ?>
                    </tfoot>
                </table>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <?php
                        if ($inv->note || $inv->note != "") { ?>
                            <div class="well well-sm">
                                <p class="bold"><?= lang("note"); ?>:</p>
                                <div><?= $this->sma->decode_html($inv->note); ?></div>
                            </div>
                        <?php
                        }
                        ?>
                </div>
            </div>
            <?php if (!$Supplier || !$Customer) { ?>
                <div class="buttons">
                    <?php if ($inv->attachment) { ?>
                        <div class="btn-group">
                            <a href="<?= admin_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
                                <i class="fa fa-chain"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                            </a>
                        </div>
                    <?php } ?>
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
