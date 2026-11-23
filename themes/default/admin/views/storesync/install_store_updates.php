<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">

            <h4 class="modal-title" id="myModalLabel"><?php echo lang('import_Store_updates'); ?></h4>
        </div>
		<?php
		$attrib = array('class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form');
		echo admin_form_open_multipart("store_sync/install_store_update_files", $attrib)
		?>
        <div class="modal-body">


            <div class="col-md-12">
                <div class="form-group">
                    <label for="zip_file"><?= lang("upload_file"); ?></label>
                    <input type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" class="form-control file" data-show-upload="false"  data-show-preview="false" id="store_update" required="required"/>
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
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>


<script type="text/javascript">


    $('#store_update').change(function(e) {
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
                var substring = "storeupdates_";
                ;
                // alert(fileName)
                if ( fileName.indexOf(substring) === -1) {
                    e.preventDefault();
                    bootbox.alert("Invalid file selected. Use store update file downloaded from main store.");
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