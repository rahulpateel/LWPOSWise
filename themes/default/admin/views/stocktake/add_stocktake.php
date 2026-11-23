
<link rel="stylesheet" type="text/css" media="screen" href="<?= $assets ?>styles/jquery-ui.min.css">
<script type="text/javascript" src="<?= $assets ?>js/jquery-ui.min_new.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.validate.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {

        /* $("#sldate").datetimepicker({
                format:'MM yy',
                fontAwesome: true,
                language: 'sma',
                weekStart: 1,
                todayBtn: 1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0,
                changeMonth: true,
                changeYear: true
            }).datetimepicker('update', new Date());*/

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

         <?php if( (!empty($stocktake) && !empty($stocktake->category_id)) )
         {?>

          $.ajax({
            type: "get", async: false,
            url: "<?= admin_url('products/getSubCategories') ?>/" + <?= $stocktake->category_id ?>,
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
    

              $( "#stocktakeFrm" ).validate(


                  {
                    ignore:[],
                   
                   rules: {
                     category: {
                      required: true
                      
                    }
                    ,
                    user: {
                      required: true
                      
                    }
                    ,
                    for_month: {
                      required: true
                      
                    }
                   } ,
                  messages:{
                      category: {
                      required: "Please select category",
                     
                    }
                    ,
                    user: {
                      required: "Please select user",
                     
                    }
                    ,
                    for_month: {
                      required: "Please select month",
                     
                    }

                    }
                  ,
                  errorPlacement: function (error, element) {

                          // removeServerValidation();

                          error.insertAfter(element);
                        }
                       

  });

    });
</script>
 <style>
    .ui-datepicker-calendar {
        display: none;
    }
    .error {
        color:red;
    }
    </style>
<div class="box">
    <div class="box-header">
        <?php 
        if(!empty($stocktake) && !empty($stocktake->id) )
        {?>
             <h2 class="blue"><i class="fa fa-caret-down"></i><?= lang('edit_stocktake'); ?></h2>  
        <?php }
        else
        {?>
            <h2 class="blue"><i class="fa fa-caret-down"></i><?= lang('add_stocktake'); ?></h2>    
        <?php }
        ?>
        
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p><?= lang('enter_info'); ?></p>
                <?php 
                 $attrib = array('role' => 'form','id'=>'stocktakeFrm');
                if(!empty($stocktake))
                {
                    echo admin_form_open("stocktake/edit_stocktake", $attrib);
                }
                else
                {
                    echo admin_form_open("stocktake/add_stocktake", $attrib);    
                }
                

                ?>

                <div class="col-md-6">
                   

                                <div class="form-group all">
                                    <?= lang("Month-Year", "for_month"); ?>
                                    <?php echo form_input('for_month', (isset($_POST['for_month']) ? $_POST['for_month'] : (!empty($stocktake) ? $stocktake->for_month:'')), 'class="form-control" id="for_month" required="required" readonly="readonly"'); ?>
                                </div>
                           
                 <div class="form-group all">
                        <?= lang("User", "user") ?>
                        <?php
                        $u[''] = "";
                        if($users):
                        foreach ($users as $user) {
                            $u[$user->id] = $user->username;
                        }
                    endif;
                        echo form_dropdown('user', $u, (isset($_POST['user']) ? $_POST['user'] : (!empty($stocktake) ? $stocktake->user_id:'')), 'class="form-control select" id="user" placeholder="' . lang("select") . " " . lang("user") . '" required="required" style="width:100%"')
                        ?>
                    </div>

                    <div class="form-group all">
                        <?= lang("category", "category") ?>
                        <?php
                        $cat[''] = "";
                        foreach ($categories as $category) {
                            $cat[$category->id] = $category->name;
                        }
                        echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : (!empty($stocktake)? $stocktake->category_id:'')), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" required="required" style="width:100%"')
                        ?>
                    </div>

                 

                  <div class="form-group all">
                        <?= lang("Subcategory", "subcategory") ?>
                        <div class="controls" id="subcat_data"> <?php
                            echo form_input('subcategory',(isset($_POST['subcategory']) ? $_POST['subcategory'] : (!empty($stocktake)? $stocktake->subcategory_id:'')), 'class="form-control" id="subcategory"  placeholder="' . lang("select_category_to_load") . '"');
                            ?>
                        </div>
                    </div>

                    <div class="form-group all">
                        <?= lang("note", "note") ?>
                        <?= form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : (!empty($stocktake) ? $stocktake->note : '')), 'class="form-control" id="note"'); ?>
                    </div>

                   <?php 
                        if(!empty($stocktake))
                        {?> 

                            <input type="hidden" name="id" value="<?=$stocktake->id?>">
                            <?php } ?>

                    <div class="form-group">

                        <?php 
                        if(!empty($stocktake))
                        {

                            echo form_submit('add_stocktake', $this->lang->line("edit_stocktake"), 'class="btn btn-primary"');  
                        }
                        else
                        {
                             echo form_submit('add_stocktake', $this->lang->line("add_stocktake"), 'class="btn btn-primary"');    
                        }
                       ?> 
                    </div>
                </div>
                <?php echo form_close();?>
            </div>
        </div>
    </div>
</div>


