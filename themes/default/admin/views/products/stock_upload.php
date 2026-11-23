<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function () {
        <?php if ($Owner || $Admin) { ?>
        $("#qadate").datetimepicker({
            format: site.dateFormats.js_ldate,
            fontAwesome: true,
            language: 'sma',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0
        }).datetimepicker('update', new Date());
        <?php } ?>

        // client side validation of the file before it is posted
        $('#stock_form').on('submit', function (e) {
            var f = $('#stock_file').val();
            if (!f) {
                bootbox.alert('Please select the Excel file to upload.');
                return false;
            }
            if (!/\.(xls|xlsx)$/i.test(f)) {
                bootbox.alert('Only .xls or .xlsx files are allowed.');
                return false;
            }
            $('#upload_stock').prop('disabled', true).val('Reading file, please wait...');
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file-excel-o"></i><?= lang('stock_upload'); ?></h2>
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

                <?php if ($rows === null) { ?>
                    <p class="introtext"><?= lang('enter_info'); ?></p>
                    <?php
                    $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'stock_form');
                    echo admin_form_open_multipart("products/stock_upload", $attrib);
                    ?>
                    <div class="row">
                        <div class="col-lg-12">
                            <?php if ($Owner || $Admin) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("date", "qadate"); ?>
                                        <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="qadate" required="required"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("reference_no", "qaref"); ?>
                                    <?= form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="qaref"'); ?>
                                </div>
                            </div>

                            <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("warehouse", "qawarehouse"); ?>
                                        <?php
                                        $wh[''] = '';
                                        foreach ($warehouses as $warehouse) {
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }
                                        echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="qawarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            <?php } else {
                                echo form_input(array('type' => 'hidden', 'name' => 'warehouse', 'id' => 'qawarehouse', 'value' => $this->session->userdata('warehouse_id')));
                            } ?>

                            <div class="clearfix"></div>

                            <div class="col-md-12">
                                <div class="well well-small">
                                    <span class="text-warning">Upload StockProducts.xls</span><br>
                                    Columns required in the first row:
                                    <span class="text-info">id, name, barcode, WhQty, mrp, cost, price, Floor Qty</span>.
                                    <br><strong>Total Qty = WhQty + Floor Qty.</strong> WhQty and Floor Qty must not be blank (use 0).
                                </div>
                                <div class="form-group">
                                    <label for="stock_file"><?= lang("upload_file"); ?> (.xls / .xlsx)</label>
                                    <input type="file" data-browse-label="<?= lang('browse'); ?>" name="stock_file"
                                           class="form-control file" data-show-upload="false" data-show-preview="false"
                                           id="stock_file" accept=".xls,.xlsx" required="required"/>
                                </div>
                            </div>
                            <div class="clearfix"></div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <?= lang("note", "qanote"); ?>
                                    <?= form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="qanote" style="margin-top: 10px; height: 80px;"'); ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>

                            <div class="col-md-12">
                                <input type="submit" name="upload_stock" value="Upload &amp; Preview" id="upload_stock"
                                       class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;">
                            </div>
                        </div>
                    </div>
                    <?= form_close(); ?>

                <?php } else {
                    $total = count($rows);
                    $diff_cnt = 0;
                    foreach ($rows as $r) {
                        if ($r[10] != 0) $diff_cnt++;
                    } ?>
                    <div class="alert alert-info">
                        <strong><?= $total; ?></strong> products read from the file,
                        <strong><?= $diff_cnt; ?></strong> with a quantity difference.
                        Saving stores them in <code>stock_diff</code> / <code>stock_diff_items</code>.
                        <strong>Product quantity in the system will NOT be changed (no sync).</strong>
                    </div>

                    <?php
                    echo admin_form_open("products/stock_upload_save", array('id' => 'save_form', 'role' => 'form'));
                    echo form_hidden('upload_token', $token);
                    ?>
                    <div style="margin-bottom:12px;">
                        <button type="submit" class="btn btn-primary" id="save_stock" <?= $diff_cnt ? '' : 'disabled'; ?>>
                            <i class="fa fa-save"></i> <?= lang('save'); ?>
                        </button>
                        <a href="<?= admin_url('products/stock_upload'); ?>" class="btn btn-danger"><?= lang('cancel'); ?></a>
                        <?php if (!$diff_cnt) { ?><span class="text-success" style="margin-left:10px;">All quantities already match - nothing to save.</span><?php } ?>
                    </div>
                    <?= form_close(); ?>

                    <?php $this->load->view($this->theme . 'products/stock_table', array('rows' => $rows, 'tbl_id' => 'stkUpTbl')); ?>
                <?php } ?>

            </div>
        </div>
    </div>
</div>
<?php if ($rows !== null) { ?>
<script type="text/javascript">
    $('#save_form').on('submit', function () {
        $('#save_stock').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
    });
</script>
<?php } ?>
