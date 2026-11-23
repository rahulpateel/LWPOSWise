<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#suData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('products/get_stock_uploads/' . ($warehouse ? $warehouse->id : '')); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [null, {"mRender": fld}, null, null,
                {"mRender": decimalFormat}, {"mRender": decimalFormat}, null,
                {"mRender": decode_html}, {"bSortable": false}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                nRow.className = "stock_upload_link";
                return nRow;
            },
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('warehouse');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('note');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-list"></i><?= lang('stock_upload_list') . ' (' . ($warehouse ? $warehouse->name : lang('all_warehouses')) . ')'; ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="<?= admin_url('products/stock_upload') ?>">
                        <i class="icon fa fa-plus-circle tip" data-placement="left" title="<?= lang('stock_upload') ?>"></i>
                    </a>
                </li>
                <?php if (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('products/stock_upload_list') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $wh) {
                                echo '<li><a href="' . admin_url('products/stock_upload_list/' . $wh->id) . '"><i class="fa fa-building"></i>' . $wh->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="suData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="width:50px;">ID</th>
                            <th class="col-xs-2"><?= lang("date"); ?></th>
                            <th class="col-xs-2"><?= lang("reference_no"); ?></th>
                            <th class="col-xs-2"><?= lang("warehouse"); ?></th>
                            <th><?= lang("total_products"); ?></th>
                            <th><?= lang("diff_products"); ?></th>
                            <th class="col-xs-2"><?= lang("created_by"); ?></th>
                            <th><?= lang("note"); ?></th>
                            <th style="min-width:60px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                            <th style="width:60px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
