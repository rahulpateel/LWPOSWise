<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
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
            if (localStorage.getItem('catitems')) {
                localStorage.removeItem('catitems');
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


<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('set_category'); ?></h2>
    </div>
    <div class="box-content">
         <?php
                  $attrib = array('data-toggle' => 'validator', 'role' => 'form','id'=>'frmEdit');
                echo admin_form_open("products/setcategory", $attrib);
                ?>
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
               
                <div class="row">
                      <?= form_hidden('count_id', $count_id); ?>
                    <div class="col-lg-12">
                      
                       <div class="col-lg-3">
                    <div class="form-group all">
                       <label for="category" >Category</label>
                        <?php
                        $cat[''] = "";
                        foreach ($categories as $category) {
                            $cat[$category->id] = $category->name;
                        }
                        echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select " id="category" placeholder="' . lang("select") . " " . lang("category") . '"  required="required" style="width:100%"  ')
                        ?>
                    </div> 
                </div>
                        <div class="col-lg-2">
                    <div class="form-group all">
                        <?= lang("subcategory", "subcategory") ?>
                        <div class="controls" id="subcat_data"> <?php
                            echo form_input('subcategory', (isset($_POST['subcategory']) ? $_POST['subcategory'] : ''), 'class="form-control" id="subcategory"  placeholder="' . lang("select_category_to_load") . '"');
                            ?>
                        </div>
                    </div>
                </div>
        
                    
                       

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

                         <div style="text-align:right">
 <a href="#" data-toggle="modal" data-target="#scanModal">
        <i class="fa fa-camera" style=" font-size: 40px;"></i>
        </a></div>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("products"); ?> *</label>

                                <div class="controls table-controls">
                                    <table id="qaTable" class="table items table-striped table-bordered table-condensed table-hover">
                                        <thead>
                                        <tr>
                                            <th><?= lang("product_name") . " (" . lang("product_code") . ")"; ?></th>
                                          
                                          
                                           
                                            <th style="max-width: 30px !important; text-align: center;">
                                                <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                       

                           
                            <div class="clearfix"></div>

                        <div class="col-md-12">
                            <div
                                class="form-group"><?php echo form_submit('add_adjustment', lang("set_category"), 'id="add_adjustment" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
                    </div>
                </div>
                

            </div>

        </div>
        <?php echo form_close(); ?>
    </div>
</div>


<script language="javascript">
    $(document).ready(function () {

            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
            placeholder: "<?= lang('select_category_to_load') ?>", data: [
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


        $('#delete').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#excel').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#pdf').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#update_price').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

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

function additem(bcode) {


$.ajax({


                    type: "get",
                    async: false,
                    url: "<?= admin_url('products/qa_suggestions'); ?>/?term="+bcode,
                    dataType: "json",
                    success: function (content)
                    {
                        var item=content;
                        if (bcode.length >= 16 && content[0].id == 0) {
                     bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                   
                   }
                else if (content.length == 1 && content[0].id != 0) {
                    item = content[0];
                   
                }
                else if (content.length == 1 && content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                   
                }
                
                 event.preventDefault();
                if (item.id !== 0) {
                    var row = add_adjustment_item(item);
                    if (row)
                    {
                        alert("added...");
                        $("#add_item").val('');
                    }
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
                
                     
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                        $('#modal-loading').hide();
                    }
                });
    // body...
}
</script>


<div class="modal fade in" id="scanModal" tabindex="-1" role="dialog" aria-labelledby="scanModalLabel" aria-hidden="true">
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('scan_barcode'); ?></h4>
        </div>
      
        <div class="modal-body">
                     
          
            <div class="form-group form-inline">
                <?= lang('Device', 'Device'); ?>
                <?php 
                        $ct=array('0' => 'Select device' );
                 ?>
               
                <?= form_dropdown('device', $ct, set_value('device'), 'class="form-control tip" id="device_sel"'); ?> <button id="cam_start" type="button">Start</button>
            </div>

          

            <div class="form-group">
                <div class="col-12">
                     <div id="reader" style="width: auto;height: auto"></div>
                </div>
             
            </div>

        

        </div>
        <div class="modal-footer">
           <button type="button" id="btn_close">Close</button>
        </div>
    </div>
    
</div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html5-qrcode.min.js"></script>
<script>
    var html5QrCode ="";
    var cnt=0;
    function docReady(fn) {
        // see if DOM is already available
        if (document.readyState === "complete"
            || document.readyState === "interactive") {
            // call on next available tick
            //setTimeout(fn, 1);
        } else {
            document.addEventListener("DOMContentLoaded", fn);
        }
    }

    docReady(function () {
     

       


        html5QrCode = new Html5Qrcode(/* element id */ "reader");
        // This method will trigger user permissions
        Html5Qrcode.getCameras().then(devices => {
          /**
           * devices would be an array of objects of type:
           * { id: "id", label: "label" }
           */
          if (devices && devices.length) {
         //   var cameraId = devices[0].id;
           // console.log(cameraId);
            // .. use this to start scanning.

            for (var i = 0; i < devices.length ; i++) {
                $('#device_sel')
                   .append($("<option></option>")
                    .attr("value", devices[i].id)
                    .text(devices[i].label)); 
            }

          }
        }).catch(err => {
          // handle err
        });


    });

    $("#cam_start").click(function (argument) {

        var cameraId1= $('#device_sel').val();
       
         if(cameraId1!=0)

      {
        // body...
        html5QrCode.start(
  cameraId1, 
  {
    fps: 10,    // Optional, frame per seconds for qr code scanning
    qrbox: { width: 250, height: 250 }  // Optional, if you want bounded box UI
  },
  (decodedText, decodedResult) => {
     // do something when code is read
    // $('#scanModal').modal('toggle');
   //  oTable.fnFilter(decodedText); 

//alert(decodedText);
     additem(decodedText);
     $(".loader").hide();
    $(".blackbg").hide();
     $(".modal-backdrop").hide();
   

//  html5QrCode.stop().then((ignore) => {
//   // QR Code scanning is stopped.
//   console.log("stopped");
 

//   }
 
//   ,
//   (errorMessage) => {
//     // parse error, ignore it.
//   }

//   )
// .catch((err) => {
//   // Start failed, handle it.
// }

// );
    });
    }
});


    $("#scanModal").on("hidden.bs.modal", function () {
    // put your default event here
//     html5QrCode.stop().then((ignore) => {
//   // QR Code scanning is stopped.
//   console.log("stopped");
// }).catch((err) => {
//   // Stop failed, handle it.
// });
});


    $("#btn_close").click(function (argument) {
        // body...
         $('#scanModal').modal('toggle');
    });
</script>