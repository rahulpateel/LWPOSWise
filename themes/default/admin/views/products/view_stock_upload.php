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
            <div class="text-center" style="margin-bottom:20px;">
                <img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>" alt="<?= $Settings->site_name; ?>">
            </div>
            <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-5">
                        <p class="bold">
                            <?= lang("date"); ?>: <?= $this->sma->hrld($inv->date); ?><br>
                            <?= lang("ref"); ?>: <?= $inv->reference_no; ?><br>
                            <?= lang("warehouse"); ?>: <?= $warehouse ? $warehouse->name : ''; ?><br>
                            <?= lang("total_products"); ?>: <?= (int)$inv->total_products; ?> &nbsp;|&nbsp;
                            <?= lang("diff_products"); ?>: <?= (int)$inv->diff_products; ?>
                        </p>
                    </div>
                    <div class="col-xs-7 text-right order_barcodes">
                        <?= $this->sma->save_barcode($inv->reference_no, 'code128', 66, false); ?>
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
                        <th>Wh Qty</th>
                        <th>Floor Qty</th>
                        <th>Total Qty</th>
                        <th>System Qty</th>
                        <th>Difference</th>
                        <th><?= lang("type"); ?></th>
                        <th>MRP</th>
                        <th><?= lang("cost"); ?></th>
                        <th><?= lang("price"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $r = 1; $tot_diff = 0;
                    foreach ($rows as $row) {
                        $tot_diff += (float)$row->difference; ?>
                        <tr>
                            <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                            <td style="vertical-align:middle;"><?= $row->product_code . ' - ' . $row->product_name; ?></td>
                            <td class="text-center"><?= (float)$row->wh_qty + 0; ?></td>
                            <td class="text-center"><?= (float)$row->floor_qty + 0; ?></td>
                            <td class="text-center"><?= (float)$row->total_qty + 0; ?></td>
                            <td class="text-center"><?= (float)$row->system_qty + 0; ?></td>
                            <td class="text-center bold <?= $row->difference < 0 ? 'text-danger' : 'text-success'; ?>"><?= (float)$row->difference + 0; ?></td>
                            <td><?= lang($row->type); ?></td>
                            <td class="text-right"><?= $row->mrp !== null ? $this->sma->formatMoney($row->mrp) : ''; ?></td>
                            <td class="text-right"><?= $row->cost !== null ? $this->sma->formatMoney($row->cost) : ''; ?></td>
                            <td class="text-right"><?= $row->price !== null ? $this->sma->formatMoney($row->price) : ''; ?></td>
                        </tr>
                        <?php $r++;
                    } ?>
                    <tr>
                        <th colspan="6" style="text-align: right"><?= lang("total"); ?></th>
                        <th class="text-center"><?= $tot_diff + 0; ?></th>
                        <th colspan="4"></th>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-xs-7">
                    <?php if ($inv->note) { ?>
                        <div class="well well-sm">
                            <p class="bold"><?= lang("note"); ?>:</p>
                            <div><?= $this->sma->decode_html($inv->note); ?></div>
                        </div>
                    <?php } ?>
                </div>
                <div class="col-xs-5 pull-right">
                    <div class="well well-sm">
                        <p>
                            <?= lang("created_by"); ?>: <?= $created_by ? $created_by->first_name . ' ' . $created_by->last_name : ''; ?><br>
                            <?= lang("date"); ?>: <?= $this->sma->hrld($inv->date); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
