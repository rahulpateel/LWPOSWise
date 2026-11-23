<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";
/* if($this->input->post('name')){
  $v .= "&name=".$this->input->post('name');
} */

if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
?>
<script>
    $(document).ready(function () {

       
      
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        //$('#form').hide();
        
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

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-money"></i><?= lang('consolidated_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            } ?>
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
       
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

               

                <div id="form">

                    <?php echo admin_form_open("reports/consolidated"); ?>
                    <div class="row">
                    
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>


                <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
           
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
             <h4 class="modal-title"
                id="myModalLabel">All Data <?php
            if ($this->input->post('start_date')) {
                echo "( Date: From " . $this->input->post('start_date') . " to " . $this->input->post('end_date')." )";
            } ?></h4>
        </div>
        
        <div class="modal-body">
            <div id="alerts"></div>
            <table width="100%" class="stable">
                

                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('cash_in_hand'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->sma->formatMoney($tot_cih->total); ?></span></h4>
                    </td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('cash_sale'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->sma->formatMoney($total_cash?$total_cash:'0.00'); ?></span>
                        </h4></td>
                </tr>


                
                
                <tr>
                    <td id="cc_sale1" style="border-bottom: 1px solid #EEE;"><h4><?= lang('cc_sale'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->sma->formatMoney($total_cc?$total_cc:'0.00'); ?></span>
                        </h4></td>
                       
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('gc_sale'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4>
                           <span><?= $this->sma->formatMoney($total_gc?$total_gc:'0.00'); ?></span>
                        </h4></td>

                       
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;">
                        <h4><?= lang('online_payment'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;">
                        <h4>
                             <span><?= $this->sma->formatMoney($total_op?$total_op:'0.00'); ?></span>
                        </h4></td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;">
                        <h4><?= lang('phone_pe'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;">
                        <h4>
                            <span><?= $this->sma->formatMoney($total_pp?$total_pp:'0.00'); ?></span>
                        </h4></td>
                </tr>

                 <tr>
                    <td style="border-bottom: 1px solid #DDD;">
                        <h4><?= lang('other'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;">
                        <h4>
                            <span><?= $this->sma->formatMoney($total_other?$total_other:'0.00'); ?></span>
                        </h4></td>
                </tr>
    
                <tr>
                    <td width="300px;" style="font-weight:bold;"><h4><?= lang('total_sales'); ?>:</h4></td>
                    <td width="200px;" style="font-weight:bold;text-align:right;"><h4>
                           <span><?= $this->sma->formatMoney($totsales->paid); ?></span>
                        </h4></td>
                      
                </tr>
                <tr>
                    <td style="border-top: 1px solid #DDD;"><h4><?= lang('refunds'); ?>:</h4></td>
                    <td style="text-align:right;border-top: 1px solid #DDD;"><h4>
                            <span><?= $this->sma->formatMoney($totrefunds->total); ?></span>
                        </h4></td>

                             
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('expenses'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4>
                            <span><?= $this->sma->formatMoney($tot_expenses->total); ?></span>
                        </h4></td>
                        
                </tr>
                <tr>
                    <td width="300px;" style="font-weight:bold;"><h4><strong><?= lang('total_cash'); ?></strong>:</h4>
                    </td>
                    <td style="text-align:right;"><h4>
                   
                            <span><?= $this->sma->formatMoney($total_cash-$tot_expenses->total+$totrefunds->total); ?></span>
                        </h4></td>
                </tr>

               

                <tr>
                    <td width="300px;" style="font-weight:bold;"><h4><strong><?= lang('total_cc_slips'); ?></strong>:</h4>
                    </td>
                    <td style="text-align:right;"><h4>
                   
                            <span><?= ($tot_cc_slips->total_cc_slips); ?></span>
                        </h4></td>
                </tr>
            </table>

            
            <hr>
            
           

        </div>
        
    </div>
    
</div>

</div>
<?= $modal_js ?>

            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>

<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getConsPaymentsReport/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getConsPaymentsReport/0/xls/?v=1'.$v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });
            return false;
        });
    });
</script>