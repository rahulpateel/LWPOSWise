<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";


if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}

?>
<script>
    $(document).ready(function () {
        oTable = $('#PrRData').dataTable({
            "aaSorting": [[0, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/getDailyProfitLossReport/?v=1'.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [null,null, {"mRender": decimalFormat, "bSearchable": false}, {"mRender": decimalFormat, "bSearchable": false}, {"mRender": decimalFormat, "bSearchable": false},{"mRender": decimalFormat, "bSearchable": false}, {"mRender": decimalFormat, "bSearchable": false},{"mRender": decimalFormat, "bSearchable": false}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var pQty = 0, sQty = 0, Amt = 0, sAmt = 0, pl = 0,tAmt=0;
                for (var i = 0; i < aaData.length; i++) {
                    pQty += parseFloat(aaData[aiDisplay[i]][2]);
                    sQty += parseFloat(aaData[aiDisplay[i]][4]);
                    Amt += parseFloat(aaData[aiDisplay[i]][6]);
                    tAmt += parseFloat(aaData[aiDisplay[i]][7]);
                   //sAmt += parseFloat(aaData[aiDisplay[i]][7]);
                   // pl += parseFloat(aaData[aiDisplay[i]][7]);
                  //  cess += parseFloat(aaData[aiDisplay[i]][8]);

                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[2].innerHTML = currencyFormat(parseFloat(pQty));
                nCells[4].innerHTML = currencyFormat(parseFloat(sQty));
                nCells[6].innerHTML = currencyFormat(parseFloat(Amt));
                nCells[7].innerHTML = decimalFormat(parseFloat(tAmt));
               // nCells[7].innerHTML = currencyFormat(parseFloat(sAmt));
              //  nCells[7].innerHTML = currencyFormat(parseFloat(pl));

            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('product_code');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},

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
            <i class="fa-fw fa fa-folder-open"></i><?= lang('Daily Productwise Report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "Sale Date " . $this->input->post('start_date') ;
            }
            else
                echo "Sale Date " . date('d/m/Y') ;
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

                    <?php echo admin_form_open("reports/daily_profitloss"); ?>
                    <div class="row">

                        <div class="col-sm-6">
                            <div class="form-group">
                                <?= lang("Sale_date", "Sale_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : date('d/m/Y')), 'class="date" id="start_date"'); ?>
                            <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
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

                            <th ><?= lang("product_code"); ?></th>
                            <th ><?= lang("product_name"); ?></th>
                            <th><?= lang("total_quantity"); ?></th>
                            <th><?= lang("product_price"); ?></th>
                            <th><?= lang("Total_Sale"); ?></th>
                            <th><?= lang("Purchase_Price"); ?></th>
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
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getHSNOutwardReport/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getDailyProfitLossReport/0/xls/?v=1'.$v)?>";
            return false;
        });

    });
</script>