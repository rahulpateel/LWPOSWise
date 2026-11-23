<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" type="text/css" media="screen" href="<?= $assets ?>styles/jquery-ui.min.css">
<script type="text/javascript" src="<?= $assets ?>js/jquery-ui.min_new.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.validate.min.js"></script>

<?php
$params="";


if ($this->input->post('customer')) {
    //echo "user ".$this->input->post('user');
     $params.="&customer=".$this->input->post('customer');
}




?>

<script>

    
        $(document).ready(function () {
        oTable = $('#QUData').dataTable({

            "aoColumns": [{"bSortable": false,"mRender": checkbox},null, null, null, null]
          
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

     <?php if ($this->input->post('customer')) { ?>
        $('#customer').val(<?= $this->input->post('customer') ?>).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "customers/suggestions/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data.results[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });

        $('#customer').val('<?= $this->input->post('customer') ?>').trigger('change');
        <?php } ?>



        
    });

</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('points_history'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?></h2>
        
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        
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
                    echo admin_form_open("reports/points_history", $attrib); ?>
            <div class="col-lg-12">
                

               

                <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>

                <div class="col-sm-2">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>


       <div class="col-sm-2">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
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
                    echo admin_form_open("report/points_history", $attrib); ?>
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
                             <th >
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("customer"); ?></th>
                            <th><?= lang("Points"); ?></th>
                            <th><?= lang("date"); ?></th>
                            
                            
                           
                            
                           
                          
                        </tr>
                        </thead>
                        <tbody>
                            <?php 
                            
                            if($report)
                            {
                                foreach($report as $rpt) {


                                 
                                  
                                    ?>
                                    <tr>
                                        <td><?= $rpt->id; ?></td>
                                        <td><?= $rpt->reference_no; ?></td>
                                        <td><?= $rpt->Customer?$rpt->Customer:"---"; ?></td>
                                         <td><?= $rpt->Points?$rpt->Points:"---"; ?></td>
                                        
                                        <td><?= $rpt->created_date;; ?></td>

                                       
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
  echo admin_form_open('reports/points_history_export', 'id="frmExcel"');
 ?>
     <input type="hidden" name="chkvals" id="chkvals"/ >
<?php
 echo form_close();
?>
