<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('import_points_excel'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <?php
                $attrib = array('class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form');
                echo admin_form_open_multipart("products/import_points_excel", $attrib)
                ?>
                <div class="row">
                    <div class="col-md-12">

                        <div class="well well-small">
                            
									<a  class="btn btn-primary pull-right" href="#" id="export_points_excel" data-action="export_points_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('Download_All_Points') ?>
                            </a>
							<span class="text-warning">
							First export products points in excel<br/>
							</span>								
                            <span class="text-warning">
							
							
							<?= lang("xls4"); ?></span><br/><?= lang("xls5"); ?> <span
                                class="text-info">(<?= lang("id") . ', ' . lang("name") . ', ' .lang("category").', ' .lang("points") . ', ' .  lang("code") . ', ' . lang("desc"); ?>
                                )</span> <?= lang("xls6"); ?>
                                

                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="xlsx_file"><?= lang("upload_file"); ?></label>
                                <input type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" class="form-control file" data-show-upload="false" data-show-preview="false" id="xlsx_file" required="required"/>
                            </div>

                            <div class="form-group">
                                <?php echo form_submit('import', $this->lang->line("import"), 'class="btn btn-primary"'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>

<div style="display: none;">

<?php
$attrib1 = array('class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form','id'=>'frmActions');
 echo admin_form_open_multipart("products/product_actions", $attrib1) ?>
        <input type="hidden" name="form_action" value="export_points_excel" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>

    <script type="text/javascript">
        $(document).ready(function (argument) {
            // body...
             $('#action-form-submit').removeAttr("disabled");
        });
    </script>