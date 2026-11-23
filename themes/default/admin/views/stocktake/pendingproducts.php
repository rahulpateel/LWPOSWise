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
       
        $('#QUData').dataTable({
            "bPaginate":   false,
        "ordering": false,
        "info":     false

        });

        $("#excel").click(function() {
   
          
                $( "#frmExcel" ).submit();
        
           });

        //  
 

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
                class="fa-fw fa fa-heart-o"></i><?= lang('Pending_Products_Report') ; ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                                                
                        <li>
                           <a href="#"  id="excel" data-action="export_excel">
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

                

                <div class="table-responsive">
                    <table id="QUData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            
                            <th><?= lang("Product( Code )"); ?></th>
                             <th><?= lang("category"); ?></th>
                            <th><?= lang("Subcategory"); ?></th>
                            
                                                     
                        </tr>
                        </thead>
                        <tbody>
                            <?php 
                           

                            if($pending)
                            {
                                foreach($pending as $pendingProd) {


                                   
                                  
                                    ?>
                                    <tr>
                                      
                                        <td><?= $pendingProd->name; ?></td>
                                        <td><?= $pendingProd->category_name; ?></td>
                                         <td><?= $pendingProd->subcategory_name; ?></td>
                                      

                                       
                                    </tr>


                               


                            <?php }
                                }
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
  echo admin_form_open('stocktake/pending/0', 'id="frmExcel"');
 ?>
    <input type="hidden" name="excel" value="1"/>
     <input type="hidden" name="stock_take_id" id="stock_take_id" value="<?=$id; ?>" / >
<?php
 echo form_close();
?>
