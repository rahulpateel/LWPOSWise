<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css">
  
</style>
<script type="text/javascript">
    var count = 1, an = 1;
    var type_opt = {'addition': '<?= lang('addition'); ?>', 'subtraction': '<?= lang('subtraction'); ?>'};

    var rp=false;

    <?php 
            if( $this->session->userdata('remove_qals'))
            {
                 $this->session->set_userdata('remove_qals', 0);
                ?>
                rp=true;
<?php
            }
     ?>
    $(document).ready(function () {
       if (rp) {
            if (localStorage.getItem('stitems')) {
                localStorage.removeItem('stitems');
            }
            if (localStorage.getItem('qaref')) {
                localStorage.removeItem('qaref');
            }
            if (localStorage.getItem('qawarehouse')) {
                localStorage.removeItem('qawarehouse');
            }
            if (localStorage.getItem('qanote')) {
                localStorage.removeItem('qanote');
            }
            if (localStorage.getItem('qadate')) {
                localStorage.removeItem('qadate');
            }
            localStorage.removeItem('remove_qals');
        }

        <?php if ($adjustment_items) { ?>
        localStorage.setItem('stitems', JSON.stringify(<?= $adjustment_items; ?>));
        <?php } ?>
        <?php if ($warehouse_id) { ?>
        localStorage.setItem('qawarehouse', '<?= $warehouse_id; ?>');
        $('#qawarehouse').select2('readonly', true);
        <?php } ?>

        <?php if ($Owner || $Admin) { ?>
        if (!localStorage.getItem('qadate')) {
            $("#qadate").datetimepicker({
                format: site.dateFormats.js_ldate,
                fontAwesome: true,
                language: 'sma',
                weekStart: 1,
                todayBtn: 1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0
            }).datetimepicker('update', new Date());
        }
        $(document).on('change', '#qadate', function (e) {
            localStorage.setItem('qadate', $(this).val());
        });
        if (qadate = localStorage.getItem('qadate')) {
            $('#qadate').val(qadate);
        }
        <?php } ?>
        
        $("#add_item").autocomplete({
            source: '<?= admin_url('products/qa_suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_adjustment_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
    });
</script>

<div class="box" style="display: block;!important">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('stocktake'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo admin_form_open_multipart("products/stocktake", $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                      
                       
                        <?= form_hidden('count_id', $count_id); ?>

                    
                       

                        <div class="clearfix"></div>


                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang("add_product_to_order") . '"'); ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("products"); ?> *</label>

                                <div class="controls table-controls">
                                    <table id="qaTable" class="table items table-striped table-bordered table-condensed table-hover">
                                        <thead>
                                        <tr>
                                            <th><?= lang("product_name") . " (" . lang("product_code") . ")"; ?></th>
                                            <th class="col-md-2"><?= lang("pos_qty"); ?></th>
                                            <th class="col-md-2"><?= lang("store_qty"); ?></th>
                                            <th class="col-md-2"><?= lang("Diff."); ?></th>
                                            
                                            <th colspan="2" class="col-md-2"><?= lang("action"); ?></th>
                                          
                                           
                                           
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                       

                           
                            <div class="clearfix"></div>

                      
                    </div>
                </div>
                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>



<script type="text/javascript">
    $(".rin").change(function (argument) {
        // body...
         var r=$(this).attr("data-res");
         console.log(r);
          if($(this).val()!=r)
          {
            alert("Wrong");
            $(this).focus();
          }

    });


</script>
<script type="text/javascript">
    $(document).ready(function (argument) {

       $(document).on('click', '.sv2',  
        // body...
       function (argument) {
            // body...
            var id=$(this).attr("data-rid");
            var pid=$("#p"+id).val();
            var posqty=$("#pos"+id).val();
            var stockqty=$("#stock"+id).val();
            var diff1=$("#diff"+id).val();

            console.log("P "+pid);
            console.log("Pq "+posqty);
            console.log("sq "+stockqty);
            console.log("diff "+diff1);
            
            $.ajax({
            async:false,
            type: 'get',
            url: site.base_url+'products/savestocktake',
            dataType: "json",
            data: { 
                product_id: pid,
                pos_qty: posqty,
                stock_qty: stockqty,
                diff: diff1,
                rc: 1
                
             },
            success: function (data) {
               if(data=="1")
               {
                  alert("Updated");
                  location.reload();
               }
                else if(data=="3")
               {
                  alert("Quantities are equal..");
                  //location.reload();
                  return;
               }
            }
        });


        });

         $(document).on('click', '.sv1',  
        // body...
       function (argument) {
            // body...
            var id=$(this).attr("data-rid");
            var pid=$("#p"+id).val();
            var posqty=$("#pos"+id).val();
            var stockqty=$("#stock"+id).val();
            var diff1=$("#diff"+id).val();

            console.log("P "+pid);
            console.log("Pq "+posqty);
            console.log("sq "+stockqty);
            console.log("diff "+diff1);
            
            $.ajax({
            async:false,
            type: 'get',
            url: site.base_url+'products/savestocktake',
            dataType: "json",
            data: { 
                product_id: pid,
                pos_qty: posqty,
                stock_qty: stockqty,
                diff: diff1,
                rc: 0
                
             },
            success: function (data) {
               if(data=="1")
               {
                  alert("Updated");
                  location.reload();
               }
                else if(data=="3")
               {
                  alert("Quantities are equal..");
                  //location.reload();
                  return;
               }
            }
        });


        });
        

    });

    function cancel_data(cancel_data) {
    // body...
   
}
</script>