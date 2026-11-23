<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";
/*if ($this->input->post('substore')) {
    $v .= "&substore=" . $this->input->post('substore');
}*/
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
?>
<script>

    $(document).ready(function () {

        $("#mul_payment").click(function (argument) {
            // body...
             console.log($(".multi-select:checked").length);

             if($(".multi-select:checked").length<1)
             {
                alert("Please select atleast one purchase");
                return false;
             }
             else
             {
                var cntr=$(".multi-select:checked").length;
                var ids=[];
                for (var i = 0; i < cntr ; i++) {
                    
                      console.log($(".multi-select:checked")[i].value);
                      //ids+=$(".multi-select:checked")[i].value+"_";
                      ids[i]=$(".multi-select:checked")[i].value;
                }
                   
                

                                       $.ajax({
                       type: "POST",
                       data: {pids:ids ,  <?= $this->security->get_csrf_token_name() ?> : "<?= $this->security->get_csrf_hash() ?>"},
                       url: "<?= admin_url('purchases/add_payment_multi') ?>",
                       success: function(msg){

                        if(msg!="0")
                        {
                         return  $("#myModal").html(msg);
                        }
                        else{
                             window.location.reload();
                            return true;
                        }
                       }
                    });
                                        
             }
        });

        oTable = $('#POData').dataTable({
            "aaSorting": [[1, "desc"], [2, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=admin_url('purchases/getPurchases' . ($warehouse_id ? '/' . $warehouse_id : ''))?>?v=1<?=$v?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false,"mRender": checkbox}, {"mRender": fld}, null, null,null, {"mRender": row_status}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": pay_status}, {mRender: function (data, type, row) {
                   if(row[5]=='Canceled')
                        return "";
                    else
                        return row[10];
                }}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                var $nRow = $(nRow);
                nRow.id = aData[0];
                $nRow.attr("balance",aData[8] );

                if(aData[5]!='Canceled')
                nRow.className = "purchase_link";
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][6]);
                    paid += parseFloat(aaData[aiDisplay[i]][7]);
                    balance += parseFloat(aaData[aiDisplay[i]][8]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(total);
                nCells[7].innerHTML = currencyFormat(paid);
                nCells[8].innerHTML = currencyFormat(balance);
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('ref_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('ext_ref_no');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('supplier');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('purchase_status');?>]", filter_type: "text", data: []},
            {column_number: 9, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
        ], "footer").on('draw.dt', function() {
       
         $("#baldiv").hide();
         $("#balanceval").val("");
         $("#balval").html("");
         $("#purchaseids").val("");
});



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
        if (localStorage.getItem('ext_pur_ref_no')) {
            localStorage.removeItem('ext_pur_ref_no');
        }
        <?php $this->sma->unset_data('remove_pols');}
        ?>
    });

</script>
<?php if ($Owner || $GP['bulk_actions']) {
        echo admin_form_open('purchases', 'id="action-form"');
    }
?>

        <div class="row">
            <div class="col-lg-12">

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
        <div class="col-sm-1">
            <div class="form-group margin" style="margin-top: 30px;">
                <?= form_submit('view', lang('view'), 'class="btn btn-primary" value="view"');  ?>
            </div>
        </div>
        <div class="col-sm-1">
            <div class="form-group margin" style="margin-top: 30px;">
                <?= form_reset('reset', lang('reset'), 'class="btn btn-primary" value="view"');  ?>
            </div>
        </div>
    </div>
</div>
        <?php
        echo form_close();
        ?>


        <?php if ($Owner) {
        echo admin_form_open('purchases', 'id="action-form"');
    }
?>
<div class="row" id="baldiv" style="display:none;">

     <div class="col-sm-3" style="margin-left:14px!important">
           
                <span id="balval"></span>
                <input type="hidden" id="balanceval" />
                <input type="hidden" id="purchaseids" />
           
        </div>
        <!--
<div class="col-sm-3">
            <div class="form-group margin" style="margin-top: 0px;display:none;">
                form_submit('Payment', lang('Payment'), 'class="btn btn-primary" value="view"');
            </div>
        </div>-->
    </div>

 <?php
        echo form_close();
        ?>

<?php if ($Owner || $GP['bulk_actions']) {
	    echo admin_form_open('purchases/purchase_actions', 'id="action-form"');
	}
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-star"></i><?=lang('purchases') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')';?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?=admin_url('purchases/add_order')?>">
                                <i class="fa fa-plus-circle"></i> <?=lang('add_purchase_order')?>
                            </a>
                        </li>
                         <li>
                            <a data-toggle="modal"  data-target="#myModal" id="mul_payment" href="#">
                                <i class="fa fa-plus-circle"></i> <?=lang('add_combine_payment')?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="combine" data-action="combine">
                                <i class="fa fa-file-pdf-o"></i> <?=lang('combine_to_pdf')?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?=lang("delete_purchases")?></b>"
                                data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
                                data-html="true" data-placement="left">
                                <i class="fa fa-trash-o"></i> <?=lang('delete_purchases')?>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php if (!empty($warehouses)) {
                    ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?=lang("warehouses")?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?=admin_url('purchases')?>"><i class="fa fa-building-o"></i> <?=lang('all_warehouses')?></a></li>
                            <li class="divider"></li>
                            <?php
                            	foreach ($warehouses as $warehouse) {
                            	        echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . admin_url('purchases/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
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

                <div class="table-responsive">
                    <table id="POData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("ref_no"); ?></th>
                            <th><?= lang("ext_ref_no"); ?></th>
                            <th><?= lang("supplier"); ?></th>
                            <th><?= lang("purchase_status"); ?></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th><?= lang("payment_status"); ?></th>
                            <!--<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>-->
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
                            <th></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th></th>
                            <!--<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>-->
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

<script type="text/javascript">
   



    $(document).on('ifUnchecked', '.multi-select', function(event) {
        getBal();

    });

    $(document).on('ifChecked', '.multi-select', function(event) {

        getBal();
   
    });

    function getBal() {
        // body...
        var bal=parseFloat('0');   
        var pids="";   
         var sel=false; 
         $("#balanceval").val("");
         $("#purchaseids").val("");
                       $("input[name='val[]']").each(function() {

                          
                           if($(this)[0].checked)
                           {
                                pids+=$(this).val()+",";
                                sel=true;
                                bal+=parseFloat($(this).closest('tr').attr("balance"));
                           }
                          
                          });

                       $("#baldiv").hide();
                       if(sel)
                       {
                        
                        $("#balval").html("<h2 style='display:inline!important;color:blue'>Balance:</h2><b style='font-size: 24px;color: red;'>Rs."+bal+"</b>");
                        $("#balanceval").val(""+bal);
                        $("#purchaseids").val(""+pids);
                        $("#baldiv").show();

                       }


                       console.log("balance "+bal)
    }
</script>