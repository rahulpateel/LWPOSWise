<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped print-table order-table">
                    <thead>
                        <tr>                        
                            <th><?= lang("subtotal"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <td style="text-align:center; width:120px;"><?= $this->sma->formatMoney($subtotal); ?></td>                    
                    </tbody>                    
                </table>
            </div>
        </div>
    </div>   
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>

