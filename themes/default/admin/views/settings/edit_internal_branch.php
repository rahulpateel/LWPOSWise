<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_internal_branch'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("system_settings/edit_internal_branch/" . $id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="code"><?php echo $this->lang->line("code"); ?></label>
                        <?php echo form_input('code', $internal_branch->code, 'class="form-control" id="code" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("opening_date", "opening_date"); ?>
                        <?php echo form_input('opening_date', (isset($_POST['opening_date']) ? $_POST['opening_date'] : $this->sma->hrsd($internal_branch->opening_date)), 'class="form-control input-tip date" id="opening_date"'); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="name"><?php echo $this->lang->line("name"); ?></label>
                        <?php echo form_input('name', $internal_branch->name, 'class="form-control" id="name" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("city", "city"); ?>
                        <?php echo form_input('city', $internal_branch->city, 'class="form-control" id="city" required="required"'); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="email"><?php echo $this->lang->line("email"); ?></label>
                        <?php echo form_input('email', $internal_branch->email, 'class="form-control" id="email"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("state", "state"); ?>
                        <?php
                        $sta['']='';
                        foreach ($states as $state) {
                            $sta[$state->id] = $state->statename . ' (' . $state-> statecode. ')';
                        }
                        echo form_dropdown('state',$sta,$internal_branch->state, 'class="form-control tip" id="state"  required="required"');
                        ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="phone"><?php echo $this->lang->line("phone"); ?></label>
                        <?php echo form_input('phone', $internal_branch->phone, 'class="form-control" id="phone"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("postal_code", "postal_code"); ?>
                        <?php echo form_input('postal_code', $internal_branch->postal_code, 'class="form-control" id="postal_code"'); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="address"><?php echo $this->lang->line("address"); ?></label>
                        <?php echo form_textarea('address', $internal_branch->address, 'class="form-control" id="address" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("country", "country"); ?>
                        <?php echo form_input('country', $internal_branch->country, 'class="form-control" id="country"'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_internal_branch', lang('edit_internal_branch'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>