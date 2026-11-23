<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";


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
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/getDaywiseProfitLossReport/?v=1'.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [null,{"mRender": decimalFormat, "bSearchable": false}, {"mRender": decimalFormat, "bSearchable": false},{"mRender": decimalFormat, "bSearchable": false}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var pQty = 0, sQty = 0, Amt = 0, sAmt = 0, pl = 0,tAmt=0;
                for (var i = 0; i < aaData.length; i++) {
                    sQty += parseFloat(aaData[aiDisplay[i]][1]);
                    Amt += parseFloat(aaData[aiDisplay[i]][2]);
                    tAmt += parseFloat(aaData[aiDisplay[i]][3]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[1].innerHTML = currencyFormat(parseFloat(sQty));
                nCells[2].innerHTML = currencyFormat(parseFloat(Amt));
                nCells[3].innerHTML = decimalFormat(parseFloat(tAmt));
            }
        }).fnSetFilteringDelay().dtFilter([

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
            <i class="fa-fw fa fa-folder-open"></i><?= lang('Daywise Profit Report'); ?> <?php
            if ($this->input->post('start_date') && $this->input->post('end_date')) {
                echo "(Sale Date From " . $this->input->post('start_date'). " To ". $this->input->post('end_date').')';
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
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('customize_report'); ?></p>

                <div id="form">

                    <?php echo admin_form_open("reports/daywise_profit"); ?>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control date" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control date" id="end_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary" style="margin-top:30px;"'); ?>
                            </div>
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
                            <th><?= lang("Date"); ?></th>
                            <th><?= lang("Total_Sale"); ?></th>
                            <th><?= lang("Purchase_Total"); ?></th>
                            <th><?= lang("Total_Profit"); ?></th>
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
               $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getDaywiseProfitLossReport/0/xls/?v=1'.$v)?>";
            return false;
        });

    });
</script>