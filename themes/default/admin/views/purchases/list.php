<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
           
            <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-5">
                    <p class="bold">
                        <?= lang("date"); ?>: <?= $this->sma->hrld($inv->date); ?><br>
                        <?= lang("ref"); ?>: <?= $inv->reference_no; ?><br>
                       
                       
                      
                    </p>
                    </div>
                    <div class="col-xs-7 text-right ">
                       
                        <?php if ($inv->updated_by) { ?>
                        <p>
                            <?= lang("created_by"); ?>: <?= $updated_by->first_name . ' ' . $updated_by->last_name;; ?><br>
                            <?= lang("update_at"); ?>: <?= $this->sma->hrld($inv->updated_at); ?>
                        </p>
                        <?php } ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>

       

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped print-table order-table">

                    <thead>

                    <tr>
                        <th><?= lang("no"); ?></th>
                        <th><?= lang("description"); ?></th>
                        <th><?= lang("mrp"); ?></th>
                        <th><?= lang("quantity"); ?></th>
                      
                        
                       
                      
                    </tr>

                    </thead>

                    <tbody>

                    <?php $r = 1;$cgst_total=0;$sgst_total=0;$cess_total=0;$igst_total=0;
                    $rcgst_total=0;$rsgst_total=0;$rcess_total=0;$rigst_total=0;$return_total=0;$totalqty=0;
                    $tax_summary = array();
                    foreach ($rows as $row):
                    ?>
                        <tr>
                            <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                            <td style="vertical-align:middle;">
                                <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                               
                            </td>
                            
							 <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatMoney($row->mrp); ?></td>
							<td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity); ?></td>
                           
                           
                 
                            

                        </tr>
                        <?php
                        $totalqty += $row->unit_quantity;
                        $r++;
                    endforeach;
                    
                    
                    ?>
					
					<tr class="bold">
					<td colspan="3" style="font-size:20px;text-align:right; padding-right:10px;">Total Qty:-</td>
					 <td  style="font-size:20px;text-align:right; padding-right:10px;"><?= $totalqty; ?>
                            </td>
					</tr>
                    </tbody>
               
                </table>
            </div>

            
      
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready( function() {
        $('.tip').tooltip();
    });
</script>
