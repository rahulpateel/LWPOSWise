<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">

            <h4 class="modal-title" id="myModalLabel"><?php echo lang('import_Store_purchases'); ?></h4>
        </div>
		<?php
		$attrib = array('class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form');
		echo admin_form_open_multipart("store_sync/install_store_purchases", $attrib)
		?>
        <div class="modal-body">

            <div class="col-md-12">
                <div class="form-group">
			        <?= lang("supplier", "supplier"); ?>
			        <?php
			        $sp[''] = '';
			        foreach ($suppliers as $supplier) {
				        $sp[$supplier->id] = $supplier->name;
			        }
			        echo form_dropdown('supplier', $sp, (isset($_POST['supplier']) ? $_POST['supplier'] :''), 'id="storesupplier" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("supplier") . '" required="required" ');
			        ?>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
			        <?= lang("warehouse", "slwarehouse"); ?>
			        <?php
			        $wh[''] = '';
			        foreach ($warehouses as $warehouse) {
				        $wh[$warehouse->id] = $warehouse->name;
			        }
			        echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
			        ?>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="zip_file"><?= lang("upload_file"); ?></label>
                    <input type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" class="form-control file" data-show-upload="false"  data-show-preview="false" id="purchase_file" required="required"/>
                </div>
            </div>

            <div class="clearfix"></div>
        </div>
        <div class="modal-footer">
			<?php echo form_submit('import', lang('import'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
	<?php echo form_close(); ?>
</div>
<!--<script type="text/javascript" src="--><?//= $assets ?><!--js/custom.js"></script>-->


<script type="text/javascript">

    $('select').on('change', function() {  // when the value changes
        $(this).valid(); // trigger validation on this element
    });
    $('#purchase_file').change(function(e) {
        v = $(this).val();
        if (v != '') {
            var validExts = new Array(".zip");
            var fileExt = v;
            fileExt = fileExt.substring(fileExt.lastIndexOf('.'));
            //alert(fileExt)
            if (validExts.indexOf(fileExt) < 0) {
                e.preventDefault();
                bootbox.alert("Invalid file selected. Only .zip file is allowed.");
                $(this).val('');
                $(this).fileinput('clear');
                $('form[data-toggle="validator"]').bootstrapValidator('updateStatus', 'zip_file', 'NOT_VALIDATED');
                return false;
            }
            else {
                 var fileName = v;
                 var substring = "storepurchase_";
               ;
                var store_code = '<?php echo $this->Settings->branch_code; ?>';
                var ipo_substring =store_code+'_purchase_';
                if ( fileName.indexOf(substring) === -1 && fileName.indexOf(ipo_substring) === -1) {
                    e.preventDefault();
                    bootbox.alert("Invalid file selected. Use store purchase file downloaded from main store.");
                    $(this).val('');
                    $(this).fileinput('clear');
                    $('form[data-toggle="validator"]').bootstrapValidator('updateStatus', 'zip_file', 'NOT_VALIDATED');
                    return false;

                }
                return true;
            }
        }
    });

</script>