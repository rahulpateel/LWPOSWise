<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('update_product_alert_quantity'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <?php
                $attrib = array('class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form');
                echo admin_form_open_multipart("products/import_alert_quantity", $attrib)
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-6"></div>
<div class="col-md-3">
                            <a href="<?php echo base_url(); ?>assets/csv/sample_alert_quantity.csv"
                               class="btn btn-primary pull-left"><i class="fa fa-download"></i> <?= lang("download_sample_file") ?></a>
</div>
                        <div class="col-md-3">
                            <a href="<?= admin_url('products/download_products'); ?>"
                               class="btn btn-primary pull-right"><i class="fa fa-download"></i> <?= lang("download_product_file") ?></a>
</div>
                        <div class="well well-small col-md-12">
                            <span class="text-warning"><?= lang("csv1"); ?></span><br/><?= lang("csv2"); ?> <span
                                class="text-info">(<?= lang("name") . ', ' . lang("code") . ', ' . lang("alert_quantity"); ?>
                                )</span> <?= lang("csv3"); ?>

                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="csv_file"><?= lang("upload_file"); ?></label>
                                <input type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" class="form-control file" data-show-upload="false" data-show-preview="false" id="csv_file" required="required"/>
                            </div>

                            <div class="form-group">
                                <?php echo form_submit('import', $this->lang->line("update"), 'class="btn btn-primary"'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>