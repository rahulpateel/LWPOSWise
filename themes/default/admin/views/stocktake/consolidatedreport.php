<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" type="text/css" media="screen" href="<?= $assets ?>styles/jquery-ui.min.css">
<script type="text/javascript" src="<?= $assets ?>js/jquery-ui.min_new.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.validate.min.js"></script>

<?php
$params="";
if ($this->input->post('category')) {
    //echo "nocategory_id ".$this->input->post('category');
    $params.="&category_id=".$this->input->post('category');
}

if ($this->input->post('subcategory')) {
    //echo "subcategory_id ".$this->input->post('subcategory');
    $params.="&subcategory_id=".$this->input->post('subcategory');
}

if ($this->input->post('status')) {
    //echo "#status ".$this->input->post('status');
     $params.="&status=".$this->input->post('status');
}

if ($this->input->post('user')) {
    //echo "user ".$this->input->post('user');
     $params.="&user=".$this->input->post('user');
}


if ($this->input->post('for_month')) {
    //echo "for_month ".$this->input->post('for_month');
    $params.="&for_month=".$this->input->post('for_month');
}

?>

<script>

    <?php



    $url='stocktake/getStockTakesTaken';

    if(!empty($params))
    {
        $url='stocktake/getStockTakesTaken?'.$params;        
    }
    ?>
    $(document).ready(function () {
        oTable = $('#QUData').dataTable({

            "aoColumns": [{"bSortable": false,"mRender": checkbox},null, null, null, null,null,null,null,null,{
                mRender:function (data, type, row) {
                     
                    if(row[9]!='0')
                    {
                      return  "<a href='<?= admin_url('stocktake/pending') ?>/"+row[0]+"'>"+row[9]+"</a>";
                    }
                    else
                    {
                        return row[9];
                    }
                    
                }
            }]
          
        });

        $("#excel").click(function() {
        //Do stuff when clicked
console.log("sss");
var vals="";

var len=$('input.checkbox:checkbox:checked').length;

console.log("len "+len);

          if(len==0)
          {
            alert('Please select atleast one record');
          }
          else
          {
            var cnt=0;
            $('input.checkbox:checkbox:checked').each(function () {
                cnt++;
            var sThisVal = $(this).val();
            if(sThisVal=='on'|| sThisVal=='On' || sThisVal=='ON' || sThisVal=='oN' )
            {

            }
            else
            {
                 vals+=sThisVal+",";
                 $("#chkvals").val(vals);
            }
          
            if(cnt==len)
            {
                $( "#frmExcel" ).submit();
            }
           });

          }
        //  
    });

         $('#for_month').datepicker( {
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            dateFormat: 'MM-yy',
            onClose: function(dateText, inst) { 
                $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
            }
            }).focus(function () {
    $(".ui-datepicker-calendar").hide();
    $(".ui-datepicker-current").hide();
    $("#ui-datepicker-div").position({
      my: "left top",
      at: "left bottom",
      of: $(this)
    })});

                    $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
            placeholder: "<?= lang('select_category_to_load') ?>", minimumResultsForSearch: 7, data: [
                {id: '', text: '<?= lang('select_category_to_load') ?>'}
            ]
        });

         $('#category').change(function () {
            var v = $(this).val();
            $('#modal-loading').show();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= admin_url('products/getSubCategories') ?>/" + v,
                    dataType: "json",
                    success: function (scdata) {
                        if (scdata != null) {
                            scdata.push({id: '', text: '<?= lang('select_subcategory') ?>'});
                            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                                placeholder: "<?= lang('select_category_to_load') ?>",
                                minimumResultsForSearch: 7,
                                data: scdata
                            });
                        } else {
                            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('no_subcategory') ?>").select2({
                                placeholder: "<?= lang('no_subcategory') ?>",
                                minimumResultsForSearch: 7,
                                data: [{id: '', text: '<?= lang('no_subcategory') ?>'}]
                            });
                        }
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                        $('#modal-loading').hide();
                    }
                });
            } else {
                $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
                    placeholder: "<?= lang('select_category_to_load') ?>",
                    minimumResultsForSearch: 7,
                    data: [{id: '', text: '<?= lang('select_category_to_load') ?>'}]
                });
            }
            $('#modal-loading').hide();
        });


         <?php if($this->input->post('category'))
         {?>

          $.ajax({
            type: "get", async: false,
            url: "<?= admin_url('products/getSubCategories') ?>/" + <?= $this->input->post('category') ?>,
            dataType: "json",
            success: function (scdata) {
                if (scdata != null) {
                     scdata.push({id: '', text: '<?= lang('select_subcategory') ?>'});
                    $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                        placeholder: "<?= lang('select_category_to_load') ?>",
                        data: scdata
                    });
                } else {
                    $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('no_subcategory') ?>").select2({
                        placeholder: "<?= lang('no_subcategory') ?>",
                        data: [{id: '', text: '<?= lang('no_subcategory') ?>'}]
                    });
                }
            }
        });
      <?php } ?>
        
    });

</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-heart-o"></i><?= lang('Stock_Take_Consolidated_Report') ; ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= admin_url('stocktake/assign') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('Assign_Stock_Take') ?>
                            </a>
                        </li>
                        
                        <li>
                           
                                
                           
                            <a  id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                           
                            
                        </li>
                       
                        
                        <li class="divider"></li>
                       
                    </ul>
                </li>
                <?php if (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('quotes') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . admin_url('quotes/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
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

                 <div class="row">

                     <div id="form">

                    <?php 
                     $attrib = array('role' => 'form','id'=>'stocktakeFrm');
                    echo admin_form_open("stocktake/getConsolidatedReport", $attrib); ?>
            <div class="col-lg-12">
                <div class="col-sm-2">
                <div class="form-group">
                        <?= lang("category", "category") ?>
                        <?php
                        $cat[''] = "";
                        if(!empty($categories))
                        foreach ($categories as $category) {
                            $cat[$category->id] = $category->name;
                        }
                        echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : (!empty($stocktake)? $stocktake->category_id:'')), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '"  style="width:100%"')
                        ?>
                    </div>
                </div>

                <div class="col-sm-2">
                 <div class="form-group">
                        <?= lang("Subcategory", "subcategory") ?>
                        <div class="controls" id="subcat_data"> <?php
                            echo form_input('subcategory',(isset($_POST['subcategory']) ? $_POST['subcategory'] : (!empty($stocktake)? $stocktake->subcategory_id:'')), 'class="form-control" id="subcategory"  placeholder="' . lang("select_category_to_load") . '"');
                            ?>
                        </div>
                    </div>
                </div>

                <div class="col-sm-2">
                 <div class="form-group">
                        <?= lang("User", "user") ?>
                        <?php
                        $u[''] = "";
                        if(!empty($users))
                        foreach ($users as $user) {
                            $u[$user->id] = $user->username;
                        }
                        echo form_dropdown('user', $u, (isset($_POST['user']) ? $_POST['user'] : (!empty($stocktake) ? $stocktake->user_id:'')), 'class="form-control select" id="user" placeholder="' . lang("select") . " " . lang("user") . '"  style="width:100%"')
                        ?>
                    </div>
                </div>

                <div class="col-sm-2">
                 <div class="form-group">
                        <?= lang("Status", "status") ?>
                        <?php
                        $u[''] = "";
                        //if(!empty($statuses))
                       
                        echo form_dropdown('status', $statuses, (isset($_POST['status']) ? $_POST['status']:'' ), 'class="form-control select" id="status" placeholder="' . lang("select") . " " . lang("status") . '"  style="width:100%"')
                        ?>
                    </div>
                </div>


         <div class="col-sm-2">
        <div class="form-group all">
                                    <?= lang("Month-Year", "for_month"); ?>
                                    <?php echo form_input('for_month', (isset($_POST['for_month']) ? $_POST['for_month'] : (!empty($stocktake) ? $stocktake->for_month:'')), 'class="form-control" id="for_month"  readonly="readonly"'); ?>
                                </div>
                            </div>

                             <div class="col-sm-1">
                            <div class="form-group">
                                <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary" style="margin-top:30px;"'); ?>
                            </div>
                        </div>
                         <?php echo form_close(); ?>
                        <?php 
                     $attrib = array('role' => 'form','id'=>'stocktakeFrmReset');
                    echo admin_form_open("stocktake/getConsolidatedReport", $attrib); ?>
                     <div class="col-sm-1">
                            <div class="form-group">
                                <?php echo form_submit('submit_report', $this->lang->line("reset"), 'class="btn btn-primary" style="margin-top:30px;"'); ?>
                            </div>
                        </div>
                    <?php echo form_close(); ?>
                        </div>
                    </div>
                   
                    </div>

                <div class="table-responsive">
                    <table id="QUData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                             <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("Month-Year"); ?></th>
                             <th><?= lang("User"); ?></th>
                            <th><?= lang("category"); ?></th>
                            <th><?= lang("Subcategory"); ?></th>
                            <th><?= lang("Products"); ?></th>
                            <th><?= lang("Stock_Takes"); ?></th>
                            <th><?= lang("Matched"); ?></th>
                            <th><?= lang("Mismatched"); ?></th>
                            <th><?= lang("Pending"); ?></th>
                           
                            
                           
                          
                        </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $CI =& get_instance();
                           $CI->load->model('admin/Stocktake_model');

                            if($report && $report['stock_takes'])
                            {
                                foreach($report['stock_takes'] as $stocktake) {


                                    $totalTaken=0;
                                    $mismatched=0;
                                    $matched=0;
                                    $pending=0;

                                    if($stocktake->cnts)
                                    {
                                        foreach ($stocktake->cnts as $cnt) {
                                            //echo "CNTS ".json_encode($cnt);
                                            if(is_array($cnt))
                                            {
                                                $totalTaken+=$cnt['count'];
                                            }
                                            else
                                            {
                                                 $totalTaken+=$cnt->count;     
                                            }
                                            
                                            if(is_array($cnt))
                                            {
                                                if($cnt['stock_flag']=='Matched')
                                                  {
                                                $matched+=$cnt['count'];
                                                  }
                                                if($cnt['stock_flag']=='Mismatched')
                                                    {
                                                        $mismatched+=$cnt['count'];
                                                    }

                                            }
                                            else
                                            {
                                                if($cnt->stock_flag=='Matched')
                                                  {
                                                $matched+=$cnt->count;
                                                  }
                                                    if($cnt->stock_flag=='Mismatched')
                                                    {
                                                        $mismatched+=$cnt->count;
                                                    }

                                            }
                                            

                                        }

                                    }
                                    $pending=$stocktake->prod_count-$totalTaken;
                                    $arrMonth=explode("-", trim( $stocktake->for_month));


                                    $month=$CI->Stocktake_model->getMonthName($arrMonth[0]);
                                    $year=($arrMonth[1]);
                                  
                                    ?>
                                    <tr>
                                        <td><?= $stocktake->id; ?></td>
                                        <td><?= $month."-".$year; ?></td>
                                         <td><?= $stocktake->user; ?></td>
                                        <td><?= $stocktake->category_name; ?></td>
                                        <td><?= $stocktake->subcategory_name; ?></td>
                                        <td><?= $stocktake->prod_count; ?></td>
                                        <td><?= $totalTaken; ?></td>
                                        <td><?= $matched; ?></td>
                                        <td><?= $mismatched; ?></td>
                                        <td><?= $pending; ?></td>

                                       
                                    </tr>


                                <?php }
                                ?>


                            <?php }

                            ?>
                       
                        </tbody>
                       
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>

<?php 
  echo admin_form_open('stocktake/excel_export', 'id="frmExcel"');
 ?>
     <input type="hidden" name="chkvals" id="chkvals"/ >
<?php
 echo form_close();
?>
