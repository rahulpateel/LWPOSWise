<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content" style="width:70%">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('Cost_Margin'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("storesales/add/0/" . $purchase_id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <?= lang("Cost_Margin_(%)", "slcostperc"); ?>

                        <?php 
                        $data = array( 'type' => 'number', 'name' => 'cost_perc', 'value' => (isset($_POST['cost_perc']) ? $_POST['cost_perc'] : ''), 'id' => 'slcostperc', 'class' => 'form-control input-tip', 'required' => 'required' );

                        echo form_input($data); ?>
                    </div>
                </div>  
            </div>
        <div class="modal-footer">
            <?php echo form_submit('add', lang('Submit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
