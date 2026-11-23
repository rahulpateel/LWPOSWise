<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";

if ($this->input->post('category')) {
    $v .= "&category=" . $this->input->post('category');
}
if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
?>
<script>
    $(document).ready(function () {
        oTable = $('#PrRData').dataTable({
            "aaSorting": [[0, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/getHSNOutwardReport/?v=1'.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [null, null, null,null, {"mRender": decimalFormat, "bSearchable": false},{"mRender": decimalFormat, "bSearchable": false}, {"mRender": currencyFormat, "bSearchable": false}, {"mRender": currencyFormat, "bSearchable": false}, {"mRender": currencyFormat, "bSearchable": false}, {"mRender": currencyFormat, "bSearchable": false}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var pQty = 0, sQty = 0, Amt = 0, sAmt = 0, pl = 0,tAmt=0,cess=0;
                for (var i = 0; i < aaData.length; i++) {
                   // pQty += parseFloat(aaData[aiDisplay[i]][2]);
                    sQty += parseFloat(aaData[aiDisplay[i]][4]);
                    Amt += parseFloat(aaData[aiDisplay[i]][5]);
                    tAmt += parseFloat(aaData[aiDisplay[i]][6]);
                    sAmt += parseFloat(aaData[aiDisplay[i]][7]);
                    pl += parseFloat(aaData[aiDisplay[i]][8]);
                    cess += parseFloat(aaData[aiDisplay[i]][9]);
                }
                var nCells = nRow.getElementsByTagName('th');
               // nCells[2].innerHTML = decimalFormat(parseFloat(pQty));
                nCells[4].innerHTML = decimalFormat(parseFloat(sQty));
                nCells[5].innerHTML = currencyFormat(parseFloat(Amt));
                nCells[6].innerHTML = currencyFormat(parseFloat(tAmt));
                nCells[7].innerHTML = currencyFormat(parseFloat(sAmt));
                nCells[8].innerHTML = currencyFormat(parseFloat(pl));
                nCells[9].innerHTML = currencyFormat(parseFloat(cess));
            }
        }).fnSetFilteringDelay().dtFilter([
            //{column_number: 0, filter_default_label: "[<? #=lang('category_code');?>]", filter_type: "text", data: []},
            {column_number: 0, filter_default_label: "[<?=lang('HSN');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('Description');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });

    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-folder-open"></i><?= lang('HSN-Wise_Summary_Of_Outward_Supplies'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <!--<li class="dropdown">
                    <a href="#" id="pdf" class="tip" title="<?/*= lang('download_pdf') */?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>-->
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('customize_report'); ?></p>

                <div id="form">

                    <?php echo admin_form_open("reports/HSNOutward"); ?>
                    <div class="row">



                       <!-- <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?/*= lang("warehouse"); */?></label>
                                <?php
/*                                $wh[""] = lang('select').' '.lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                */?>
                            </div>
                        </div>-->

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>

                        <div  class="col-sm-4">
                            <div class="form-group"><?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                        </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>

                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="PrRData"
                           class="table table-striped table-bordered table-condensed table-hover dfTable reports-table"
                           style="margin-bottom:5px;">
                        <thead>
                        <tr class="active">
<!--                            <th>--><?//= lang("Sr.No."); ?><!--</th>-->
                            <th><?= lang("HSN Code"); ?></th>
                            <th><?= lang("Description"); ?></th>
                            <th><?= lang("Product Code"); ?></th>
                            <th><?= lang("Product Name"); ?></th>
                            <th><?= lang("total_quantity"); ?></th>
                            <th><?= lang("Total_Value"); ?></th>
                            <th><?= lang("Total_Taxable_Value"); ?></th>
                            <th><?= lang("CGST"); ?></th>
                            <th><?= lang("SGST"); ?></th>
                            <th><?= lang("CESS"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="7" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th><th></th><th></th><th></th>
                            <!--<th><?/*= lang("sold"); */?></th>
                            <th><?/*= lang("sold"); */?></th>
                            <th><?/*= lang("purchased_amount"); */?></th>
                            <th><?/*= lang("sold_amount"); */?></th>
                            <th><?/*= lang("profit_loss"); */?></th>
                            <th><?/*= lang("profit_loss"); */?></th>-->
                        </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getHSNOutwardReport/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getHSNOutwardReport/0/xls/?v=1'.$v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });
            return false;
        });
    });
</script>