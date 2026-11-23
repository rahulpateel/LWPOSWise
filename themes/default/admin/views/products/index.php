<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css" media="screen">

  
</style>
<?php 
     //  echo json_encode($GP);die();


 ?>

<script>
    var oTable;
	 var bk=0;
    $(document).ready(function () {
		        if(performance.navigation.type == 2) {
                bk=1;
               <?php  $a=1;   ?> 
}


if(bk!=1)
{
   $.removeCookie('SpryMedia_DataTables_PRData_products', { path: '/<?= basename(FCPATH)?>/admin/' });
}
        oTable = $('#PRData').dataTable({
			"bStateSave": true,
            "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('products/getProducts'.($warehouse_id ? '/'.$warehouse_id : '').($supplier ? '?supplier='.$supplier->id : '')) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "product_link";
                //if(aData[7] > aData[9]){ nRow.className = "product_link warning"; } else { nRow.className = "product_link"; }
                return nRow;
            }, // $this->session->userdata('show_cost') $this->session->userdata('show_price')
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox}, null, null, null, null, <?php if($Owner || $Admin) { echo '{"mRender": currencyFormat}, {"mRender": currencyFormat},'; } else { if(
                       $GP['products-cost']
                    ) { echo '{"mRender": currencyFormat},';  }  else{ echo '{"bVisible": false},'; } if($GP['products-price']) { echo '{"mRender": currencyFormat},';  }  else{echo '{"bVisible": false},'; } } ?> {"bVisible": true}, {"mRender": formatWR},{"mRender": prod_status}, <?php if(!$warehouse_id || !$Settings->racks) { echo '{"bVisible": false},'; } else { echo '{"bSortable": true},'; } ?>
                 <?php if ($Owner || $Admin) {?>
                null <?php } else {  echo '{"bVisible": false}'; } ?>  ,null,null
            ]
        }).fnSetFilteringDelay().dtFilter([
        ], "footer");

    });
</script>
<?php if ($Owner || $GP['bulk_actions']) {
    echo admin_form_open('products/product_actions'.($warehouse_id ? '/'.$warehouse_id : ''), 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-barcode"></i><?= lang('products') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')'.($supplier ? ' ('.lang('supplier').': '.($supplier->company && $supplier->company != '-' ? $supplier->company : $supplier->name).')' : ''); ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= admin_url('products/add') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('add_product') ?>
                            </a>
                        </li>
                        <?php if(!$warehouse_id) { ?>
                        <li>
                            <a href="<?= admin_url('products/update_price') ?>" data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-file-excel-o"></i> <?= lang('update_price') ?>
                            </a>
                        </li>
                        <?php } ?>
                        <li>
                            <a href="#" id="labelProducts" data-action="labels">
                                <i class="fa fa-print"></i> <?= lang('print_barcode_label') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="sync_quantity" data-action="sync_quantity">
                                <i class="fa fa-arrows-v"></i> <?= lang('sync_quantity') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="alldownload" data-action="all_export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('Download_All_to_Excel') ?>
                            </a>
                        </li>

                        <li>
                            <a href="#" id="qtycsvdownload" data-action="export_qty_csv">
                                <i class="fa fa-file-excel-o"></i> <?= lang('Download Qty CSV') ?>
                            </a>
                        </li>


                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_products") ?></b>"
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                                data-html="true" data-placement="left">
                            <i class="fa fa-trash-o"></i> <?= lang('delete_products') ?>
                             </a>
                         </li>
                    </ul>
                </li>
                <?php if (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('products') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . admin_url('products/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
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

                            <div style="text-align:right">
 <a href="#" data-toggle="modal" data-target="#scanModal">
        <i class="fa fa-camera" style=" font-size: 40px;"></i>
        </a></div>

                <div class="table-responsive">
                    <table id="PRData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="primary">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>

                            <th><?= lang("name") ?></th>
                            <th><?= lang("code") ?></th>
                            <th><?= lang("brand") ?></th>
                            <th><?= lang("category") ?></th>
                            <th><?= lang("cost") ?></th>
                            <th><?= lang("price") ?></th>
                            <?php
                            /*if ($Owner || $Admin) {
                                echo '<th>' . lang("cost") . '</th>';
                                echo '<th>' . lang("price") . '</th>';
                            } else {
                                if ($GP['products-cost']) { //$this->session->userdata('show_cost')
                                    echo '<th>' . lang("cost") . '</th>';
                                }

                                if ($GP['products-price']) { //$this->session->userdata('show_price')
                                    echo '<th>' . lang("price") . '</th>';
                                }
                            }*/
                            ?>
                            <th><?= lang("gst_rate") ?></th>
                            <th><?= lang("quantity") ?></th>
                            <th><?= lang("status") ?></th>
                            
                            <th><?= lang("rack") ?></th>
                            <th><?= lang("alert_quantity") ?></th>
                        
                            <!--<th><?/*= lang("alert_quantity") */?></th>-->
                            <th><?= lang("mrp") ?></th>
                            <th style="min-width:65px; text-align:center;"><?= lang("actions") ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>

                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                          <th><?= lang("name") ?></th>
                            <th><?= lang("code") ?></th>
                            <th><?= lang("brand") ?></th>
                            <th><?= lang("category") ?></th>
                            <th><?= lang("cost") ?></th>
                            <th><?= lang("price") ?></th>
                            <?php
                            /*if ($Owner || $Admin) {
                                echo '<th>' . lang("cost") . '</th>';
                                echo '<th>' . lang("price") . '</th>';
                            } else {
                                if ($GP['products-cost']) { //$this->session->userdata('show_cost')
                                    echo '<th>' . lang("cost") . '</th>';
                                }

                                if ($GP['products-price']) { //$this->session->userdata('show_price')
                                    echo '<th>' . lang("price") . '</th>';
                                }
                            }*/
                            ?>
                            <th><?= lang("gst_rate") ?></th>
                            <th><?= lang("quantity") ?></th>
                            <th><?= lang("status") ?></th>
                            
                            <th><?= lang("rack") ?></th>
                            <th><?= lang("alert_quantity") ?></th>
                        
                            <!--<th><?/*= lang("alert_quantity") */?></th>-->
                            <th><?= lang("mrp") ?></th>
                            <th style="min-width:65px; text-align:center;"><?= lang("actions") ?></th>
                        </tr>
                        </tfoot>
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
     $('#scanModal').modal('toggle');
     oTable.fnFilter(decodedText); 
     $(".loader").hide();
     $(".blackbg").hide();
     $(".modal-backdrop").hide();
    // $("#did").val(++cnt+"");
     //oTable.redraw();
         html5QrCode.stop().then((ignore) => {
  // QR Code scanning is stopped.
  console.log("stopped");
 

  }
 
  ,
  (errorMessage) => {
    // parse error, ignore it.
  })
.catch((err) => {
  // Start failed, handle it.
});
    });
    }
});


    $("#scanModal").on("hidden.bs.modal", function () {
    // put your default event here
    html5QrCode.stop().then((ignore) => {
  // QR Code scanning is stopped.
  console.log("stopped");
}).catch((err) => {
  // Stop failed, handle it.
});
});


    $("#btn_close").click(function (argument) {
        // body...
         $('#scanModal').modal('toggle');
    });
</script>