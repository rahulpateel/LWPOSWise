<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
             <h4 class="modal-title"
                id="myModalLabel">Register For <?= $this->session->userdata('username'). ' (' . $this->sma->hrld($reg->date ) . ' - ' . $this->sma->hrld($reg->closed_at) . ')';?></h4>
        </div>
        
        <div class="modal-body">
            <div id="alerts"></div>
            <table width="100%" class="stable">
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('cash_in_hand'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->sma->formatMoney($reg->cash_in_hand); ?></span></h4>
                    </td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('cash_sale'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->sma->formatMoney($reg->total_cash); ?></span>
                        </h4></td>
                </tr>


                
                
                <tr>
                    <td id="cc_sale1" style="border-bottom: 1px solid #EEE;"><h4><?= lang('cc_sale'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->sma->formatMoney($reg->total_cc_amount); ?></span>
                        </h4></td>
                       
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('gc_sale'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4>
                            <span><?= $this->sma->formatMoney($reg->total_gc); ?></span>
                        </h4></td>

                       
                </tr>
				<tr>
					<td style="border-bottom: 1px solid #DDD;">
						<h4><?= lang('online_payment'); ?>:</h4></td>
					<td style="text-align:right;border-bottom: 1px solid #DDD;">
						<h4>
							<span><?= $this->sma->formatMoney($reg->total_online_payment_submitted); ?></span>
						</h4></td>
				</tr>
				<tr>
					<td style="border-bottom: 1px solid #DDD;">
						<h4><?= lang('phone_pe'); ?>:</h4></td>
					<td style="text-align:right;border-bottom: 1px solid #DDD;">
						<h4>
							<span><?= $this->sma->formatMoney($reg->total_phonepe_submitted); ?></span>
						</h4></td>
				</tr>
                <?php if ($pos_settings->paypal_pro) { ?>
                    <tr>
                        <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('paypal_pro'); ?>:</h4></td>
                        <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                                <span><?= $this->sma->formatMoney($pppsales->paid ? $pppsales->paid : '0.00') . ' (' . $this->sma->formatMoney($pppsales->total ? $pppsales->total : '0.00') . ')'; ?></span>
                            </h4></td>
                    </tr>
                <?php } ?>
                <?php if ($pos_settings->stripe) { ?>
                    <tr>
                        <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('stripe'); ?>:</h4></td>
                        <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                                <span><?= $this->sma->formatMoney($stripesales->paid ? $stripesales->paid : '0.00') . ' (' . $this->sma->formatMoney($stripesales->total ? $stripesales->total : '0.00') . ')'; ?></span>
                            </h4></td>
                    </tr>
                <?php } ?>
                <?php if ($pos_settings->authorize) { ?>
                    <tr>
                        <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('authorize'); ?>:</h4></td>
                        <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4>
                                <span><?= $this->sma->formatMoney($authorizesales->paid ? $authorizesales->paid : '0.00') . ' (' . $this->sma->formatMoney($authorizesales->total ? $authorizesales->total : '0.00') . ')'; ?></span>
                            </h4></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td width="300px;" style="font-weight:bold;"><h4><?= lang('total_sales'); ?>:</h4></td>
                    <td width="200px;" style="font-weight:bold;text-align:right;"><h4>
                           <span><?= $this->sma->formatMoney($reg->total_sales); ?></span>
                        </h4></td>
                      
                </tr>
                <tr>
                    <td style="border-top: 1px solid #DDD;"><h4><?= lang('refunds'); ?>:</h4></td>
                    <td style="text-align:right;border-top: 1px solid #DDD;"><h4>
                            <span><?= $this->sma->formatMoney($reg->total_refunds); ?></span>
                        </h4></td>

                             
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('expenses'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4>
                            <span><?= $this->sma->formatMoney($reg->total_expenses); ?></span>
                        </h4></td>
                        
                </tr>
                <tr>
                    <td width="300px;" style="font-weight:bold;"><h4><strong><?= lang('total_cash'); ?></strong>:</h4>
                    </td>
                    <td style="text-align:right;"><h4>
                   
                            <span><?= $this->sma->formatMoney($reg->total_cash); ?></span>
                        </h4></td>
                </tr>

                <tr>
                    <td width="300px;" style="font-weight:bold;"><h4><strong><?= lang('total_cash_submitted'); ?></strong>:</h4>
                    </td>
                    <td style="text-align:right;"><h4>
                   
                            <span><?= $this->sma->formatMoney($reg->total_cash_submitted); ?></span>
                        </h4></td>
                </tr>

                <tr>
                    <td width="300px;" style="font-weight:bold;"><h4><strong><?= lang('total_cc_slips_submitted'); ?></strong>:</h4>
                    </td>
                    <td style="text-align:right;"><h4>
                   
                            <span><?= ($reg->total_cc_slips_submitted); ?></span>
                        </h4></td>
                </tr>
            </table>

            
            <hr>
            
           

        </div>
        
    </div>
    
</div>

</div>
<?= $modal_js ?>
<script type="text/javascript">
    $(document).ready(function () {
        $(document).on('click', '.po', function (e) {
            e.preventDefault();
            $('.po').popover({
                html: true,
                placement: 'left',
                trigger: 'manual'
            }).popover('show').not(this).popover('hide');
            return false;
        });
        $(document).on('click', '.po-close', function () {
            $('.po').popover('hide');
            return false;
        });
        $(document).on('click', '.po-delete', function (e) {
            var row = $(this).closest('tr');
            e.preventDefault();
            $('.po').popover('hide');
            var link = $(this).attr('href');
            $.ajax({
                type: "get", url: link,
                success: function (data) {
                    row.remove();
                    addAlert(data, 'success');
                },
                error: function (data) {
                    addAlert('Failed', 'danger');
                }
            });
            return false;
        });
    });
    function addAlert(message, type) {
        $('#alerts').empty().append(
            '<div class="alert alert-' + type + '">' +
            '<button type="button" class="close" data-dismiss="alert">' +
            '&times;</button>' + message + '</div>');
    }
</script>


