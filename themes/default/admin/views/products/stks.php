<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" type="text/css" media="screen" href="<?= $assets ?>styles/jquery-ui.min.css">
<script type="text/javascript" src="<?= $assets ?>js/jquery-ui.min_new.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.validate.min.js"></script>

<?php
$v="";
if ($this->input->get('start_date')) {
    $v .= "&start_date=" . $this->input->get('start_date');
}
if ($this->input->get('end_date')) {
    $v .= "&end_date=" . $this->input->get('end_date');
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
                    url: "<?= admin_url('products/getStks') ?>/" + v,
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
                class="fa-fw fa fa-heart-o"></i><?= lang($ttl) ; ?>
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
               <!-- <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?/*= lang('save_image') */?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>-->
            </ul>
        </div>
		
    </div>
    <div class="box-content">

          <div id="form">

                    <?php echo admin_form_open("products/stks",array("method"=>"get")); ?>
                    <div class="row">
                      
                      
                       
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_GET['start_date']) ? $_GET['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_GET['end_date']) ? $_GET['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> 
							
							<?php if($rc) { ?>
							<a 
							class="btn btn-primary" href="<?=admin_url('products/stks/1')?>">Reset</a>
							<?php } else { ?>
							<a 
							class="btn btn-primary" href="<?=admin_url('products/stks')?>">Reset</a>
							<?php } ?>
							
							</div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>

        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>

                

                <div class="table-responsive">
                    <table id="QUData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            
                            <th><?= lang("Product"); ?></th>
							<th><?= lang("Code"); ?></th>
                             <th><?= lang("stock_qty"); ?></th>
                            <th><?= lang("Diff"); ?></th>
                            <th><?= lang("Product Cost"); ?></th>
                            <th><?= lang("Value"); ?></th>
                            <th><?= lang("status"); ?></th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("user"); ?></th>
                            
                                                     
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
										 <td><?= $pendingProd->code; ?></td>
                                        <td><?= $pendingProd->stock_qty; ?></td>
                                         <td><?= $pendingProd->diff_qty; ?></td>
                                         <td><?= $pendingProd->cost; ?></td>
                                         <td><?= $pendingProd->value; ?></td>
                                         <td><?= $pendingProd->status; ?></td>
                                         <td><?= $pendingProd->date; ?></td>
                                         <td><?= $pendingProd->user; ?></td>
                                      

                                       
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

<script type="text/javascript">
    $(document).ready(function () {
       
        $('#xls').click(function (event) {
            event.preventDefault();
			<?php
			if($rc)
			{
			?>
            window.location.href = "<?=admin_url('products/stks/1/xls/?v=1'.$v)?>";
			<?php }
                  else{ ?>
					  window.location.href = "<?=admin_url('products/stks/0/xls/?v=1'.$v)?>";
				  <?php } ?>					  
			
            
			return false;
        });
      
    });
</script>