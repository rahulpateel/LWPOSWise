<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css">
    #POData th { width: 0%; }
</style>
<?php

$v = "";
if ($this->input->post('brand')) {
    $v .= "&brand=" . $this->input->post('brand');
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
        oTable = $('#POData').dataTable({
            "aaSorting": [[1, "desc"], [2, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=admin_url('purchases/getPurchaseOrderInternal' . ($branch_code ? '/' . $branch_code : '')).'/?v=1' . $v?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false,"mRender": checkbox}, {"mRender": fld}, null, null, {"mRender": row_status},{mRender: function (data, type, row) {
                   if(row[4]=='completed')
                        return "";
                    else
                        return row[5];
                }}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "internal_purchase_order_link";
                nRow.style = "cursor: pointer";
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                /*var total = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][6]);
                    paid += parseFloat(aaData[aiDisplay[i]][7]);
                    balance += parseFloat(aaData[aiDisplay[i]][8]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(total);
                nCells[7].innerHTML = currencyFormat(paid);
                nCells[8].innerHTML = currencyFormat(balance);*/
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('ref_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('branch');?>]", filter_type: "text", data: []},
            /*{column_number: 4, filter_default_label: "[<?=lang('brand');?>]", filter_type: "text", data: []},*/
            {column_number: 4, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
           /* {column_number: 8, filter_default_label: "[<=lang('payment_status');?>]", filter_type: "text", data: []},*/
        ], "footer");

        <?php if ($this->session->userdata('remove_pols')) {?>
        if (localStorage.getItem('poitems')) {
            localStorage.removeItem('poitems');
        }
        if (localStorage.getItem('podiscount')) {
            localStorage.removeItem('podiscount');
        }
        if (localStorage.getItem('potax2')) {
            localStorage.removeItem('potax2');
        }
        if (localStorage.getItem('poshipping')) {
            localStorage.removeItem('poshipping');
        }
        if (localStorage.getItem('poref')) {
            localStorage.removeItem('poref');
        }
        if (localStorage.getItem('powarehouse')) {
            localStorage.removeItem('powarehouse');
        }
        if (localStorage.getItem('ponote')) {
            localStorage.removeItem('ponote');
        }
        if (localStorage.getItem('posupplier')) {
            localStorage.removeItem('posupplier');
        }
        if (localStorage.getItem('pocurrency')) {
            localStorage.removeItem('pocurrency');
        }
        if (localStorage.getItem('poextras')) {
            localStorage.removeItem('poextras');
        }
        if (localStorage.getItem('podate')) {
            localStorage.removeItem('podate');
        }
        if (localStorage.getItem('postatus')) {
            localStorage.removeItem('postatus');
        }
        if (localStorage.getItem('popayment_term')) {
            localStorage.removeItem('popayment_term');
        }
        if (localStorage.getItem('pobrand')) {
            localStorage.removeItem('pobrand');
        }
        <?php $this->sma->unset_data('remove_pols');}
        ?>
    });

</script>

<script type="text/javascript">
    $(document).ready(function () {        
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

<!--  -->
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-star"></i><?=lang('Purchase_Requisitions') . ' (' . ($branch_code ? $branch->name : lang('all_branches')) . ')';?>
        </h2>
        <!-- <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>"><i class="icon fa fa-toggle-up"></i></a></li>
                <li class="dropdown"><a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>"><i class="icon fa fa-toggle-down"></i></a></li>
            </ul>
        </div> -->
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                       <!--  <li>
                            <a href="<?=admin_url('purchases/add_po_internal')?>">
                                <i class="fa fa-plus-circle"></i> <?=lang('add_po_internal')?>
                            </a>
                        </li> -->
                        <li>
                            <a href="#" id="excel" data-action="add">
                                <i class="fa fa-plus-circle"></i> <?=lang('add_po_internal')?>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php if (!empty($branches)) {
                    ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?=lang("branches")?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?=admin_url('purchases/list_po_internal')?>"><i class="fa fa-building-o"></i> <?=lang('all_branches')?></a></li>
                            <li class="divider"></li>
                            <?php
                            	foreach ($branches as $branch) {
                            	        echo '<li ' . ($branch_code && $branch_code == $branch->code ? 'class="active"' : '') . '><a href="' . admin_url('purchases/list_po_internal/' . $branch->code) . '"><i class="fa fa-building"></i>' . $branch->name . '</a></li>';
                            	    }
                                ?>
                        </ul>
                    </li>
                <?php }
                ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?=lang('list_results');?></p>
                <div id="form">

                    <?php echo admin_form_open("purchases/list_po_internal"); ?>
                    <div class="row">                      
                        <!-- <div class="col-sm-3">
                            <div class="form-group all">
                        <?= lang("brand", "brand") ?>
                        <?php
                        $br[''] = "";
                        foreach ($brands as $brand) {
                            $br[$brand->id] = $brand->name;
                        }
                        echo form_dropdown('brand', $br, (isset($_POST['brand']) ? $_POST['brand'] : ''), 'class="form-control select" id="brand" placeholder="' . lang("select") . " " . lang("brand") . '" style="width:100%"')
                        ?>
                    </div>
                        </div> -->
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("Start_Date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("End_Date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="controls" style="margin-top: 30px;"> 
                                <?php echo form_submit('submit', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> 
                            </div>
                        </div>
                    </div>
                    
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>
                <?php if ($Owner || $GP['bulk_actions']) {
                    echo admin_form_open('purchases/add_po_internal', 'id="action-form"');
                }
                ?>
                
                <div class="table-responsive">
                    <table id="POData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th style="width:200px;"><?= lang("date"); ?></th>
                            <th><?= lang("ref_no"); ?></th>
                            <th><?= lang("branch"); ?></th>
                            <!-- <th><?= lang("brand"); ?></th> -->
                            <th><?= lang("status"); ?></th>
                            <th style="width:100px;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?=lang('loading_data_from_server');?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <!-- <th></th> -->
                            <th style="width:100px; text-align: center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

 <?php if ($Owner || $GP['bulk_actions']) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php }
?> 