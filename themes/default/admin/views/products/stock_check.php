<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#check_form').on('submit', function () {
            var f = $('#stock_file').val();
            if (!f) {
                bootbox.alert('Please select the Excel file to upload.');
                return false;
            }
            if (!/\.(xls|xlsx)$/i.test(f)) {
                bootbox.alert('Only .xls or .xlsx files are allowed.');
                return false;
            }
           // $('#check_stock').prop('disabled', true).val('Checking, please wait...');
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-check-square-o"></i><?= lang('stock_check'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <?php if (!empty($upload_errors)) { ?>
                    <div class="alert alert-danger">
                        <strong>Validation errors (<?= count($upload_errors); ?>) - please correct the Excel file and upload again:</strong>
                        <ul style="margin-top:8px; max-height:260px; overflow:auto;">
                            <?php foreach (array_slice($upload_errors, 0, 200) as $e) { ?>
                                <li><?= htmlspecialchars($e); ?></li>
                            <?php } ?>
                            <?php if (count($upload_errors) > 200) { ?>
                                <li><em>... and <?= count($upload_errors) - 200; ?> more.</em></li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>

                <?php
                echo admin_form_open_multipart("products/stock_check", array('role' => 'form', 'id' => 'check_form'));
                ?>
                <div class="well well-small">
                    Upload the same Excel (id, name, barcode, WhQty, mrp, cost, price, Floor Qty).
                    <strong>Total Qty (WhQty + Floor Qty)</strong> is compared with the system quantity.
                    Nothing is saved on this screen.
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="stock_file"><?= lang("upload_file"); ?> (.xls / .xlsx)</label>
                            <input type="file" data-browse-label="<?= lang('browse'); ?>" name="stock_file"
                                   class="form-control file" data-show-upload="false" data-show-preview="false"
                                   id="stock_file" accept=".xls,.xlsx" required="required"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <input type="submit" name="check_stock" id="check_stock" value="Upload &amp; Check"
                               class="btn btn-primary" style="margin-top:25px; padding: 6px 15px;">
                    </div>
                </div>
                <?= form_close(); ?>

                <?php if ($rows !== null) { ?>
                    <?php if ($mismatch == 0) { ?>
                        <div class="alert alert-success" style="font-size:16px;">
                            <i class="fa fa-check-circle"></i>
                            <strong>All <?= count($rows); ?> products have the correct quantity</strong>
                            (Total Qty matches the system quantity).
                        </div>
                    <?php } else { ?>
                        <div class="alert alert-danger" style="font-size:16px;">
                            <i class="fa fa-exclamation-triangle"></i>
                            <strong>All products are NOT with correct quantity:</strong>
                            <?= $mismatch; ?> of <?= count($rows); ?> products have a quantity difference.
                        </div>
                    <?php } ?>
                    <?php $this->load->view($this->theme . 'products/stock_table', array('rows' => $rows, 'tbl_id' => 'stkChkTbl')); ?>
                <?php } ?>

            </div>
        </div>
    </div>
</div>
