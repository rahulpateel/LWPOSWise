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
<style type="text/css">
     #PrRData td:nth-child(2){ text-align: right; width: 12%; }

</style>
<script>
    $(document).ready(function () {
        oTable = $('#PrRData').dataTable({
            "scrollX":true,
            "aaSorting": [[0, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/gettax_rate_gstReport/?v=1'.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"mRender": fsd},null,{"mRender": currencyFormat, "bSearchable": false},{"mRender": currencyFormat, "bSearchable": false},{"mRender": currencyFormat, "bSearchable": false},{"mRender": currencyFormat, "bSearchable": false},{"mRender": currencyFormat, "bSearchable": false}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, tcgst = 0, tsgst = 0,tigst = 0,tcess = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal += parseFloat(aaData[aiDisplay[i]][1]);
                    tcgst += parseFloat(aaData[aiDisplay[i]][3]);
                    tsgst += parseFloat(aaData[aiDisplay[i]][4]);
                    tigst += parseFloat(aaData[aiDisplay[i]][5]);
                    tcess += parseFloat(aaData[aiDisplay[i]][6]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[1].innerHTML = currencyFormat(parseFloat(gtotal));
                nCells[3].innerHTML = currencyFormat(parseFloat(tcgst));
                nCells[4].innerHTML = currencyFormat(parseFloat(tsgst));
                nCells[5].innerHTML = currencyFormat(parseFloat(tigst));
                nCells[6].innerHTML = currencyFormat(parseFloat(tcess));
                //alert(gtotal);
            }
        }).fnSetFilteringDelay().dtFilter([
        {column_number: 0, filter_default_label: "[<?=lang('date');?>]", filter_type: "text", data: []},
        {column_number: 2, filter_default_label: "[<?=lang('tax_rates');?>]", filter_type: "text", data: []},
            
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
            <i class="fa-fw fa fa-cubes"></i><?= lang('Tax_Rate_GST_Report'); ?> <?php
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
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('customize_report'); ?></p>

                <div id="form">

                    <?php echo admin_form_open("reports/tax_rate_gst"); ?>
                    <div class="row">

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
                        <div class="form-group">
                        <div class="controls" style="padding-top: 30px;"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    </div>
                    
                    <?php echo form_close(); ?>

                </div>

                <div class="clearfix"></div>

                <div class="table-responsive" >
                    <table id="PrRData"
                           class="table table-striped table-bordered table-condensed table-hover dfTable reports-table"
                           style="margin-bottom:5px;max-width: 1000px;">
                        <thead>
                        <tr class="active">
                            <th><?= lang("date");?></th>
                            <th><?= lang("total"); ?></th>
                            <th><?= lang("tax_rates");?></th>

                            <th><?= lang("CGST"); ?></th>
                            <th><?= lang("SGST"); ?></th>
                            <th><?= lang("IGST"); ?></th>
                            <th><?= lang("CESS"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th>
                            <th><?= lang("total"); ?></th>

                             <th><?= lang("tax_rates"); ?></th>

                            <th><?= lang("CGST"); ?></th>
                            <th><?= lang("SGST"); ?></th>
                            <th><?= lang("IGST"); ?></th>
                            <th><?= lang("CESS"); ?></th>
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
            window.location.href = "<?=admin_url('reports/gettax_rate_gstReport/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/gettax_rate_gstReport/0/xls/?v=1'.$v)?>";
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