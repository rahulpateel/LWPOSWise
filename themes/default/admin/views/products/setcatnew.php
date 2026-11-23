<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";

if ($this->input->post('singleselect')) {
    $v .= "&category=" . $this->input->post('singleselect');
}
if ($this->input->post('subcategory')) {
    $v .= "&subcategory=" . $this->input->post('subcategory');
}
if ($this->input->post('percentage')) {
    $v .= "&percentage=" . $this->input->post('percentage');
}
if ($this->input->post('brand')) {
    $v .= "&brand=" . $this->input->post('brand');
}

?>
<style type="text/css">
    .error{
        color: #a94442;
    }
    small{
        font-size: 90%;
    }
</style>

<script type="text/javascript" src="<?= $assets ?>js/jv.js"></script>
<script>

function hidbox(x) {
    return '<input type="hidden" name="products[]" value="' + x + '" />';
}
    $(document).ready(function () {
    $.validator.setDefaults({
        ignore: []
    });


    $('#prgroup').validate({
        errorElement: 'small',
        errorClass: 'error',
        rules: {
           // singleselect:'required',
            //percentage:'required'
        },
        
        highlight: function (element, errorClass, validClass) {
            
            var elem = $(element);
            
            if(elem.hasClass('s-select2')) {
                var isMulti = (!!elem.attr('multiple')) ? 's' : '';
                elem.siblings('.category').find('.select2-choice'+isMulti+'').addClass(errorClass);            
            } else {
                elem.addClass(errorClass);
            }
            
        },
        unhighlight: function (element, errorClass, validClass) {
            var elem = $(element);
            
            if(elem.hasClass('category')) {
                elem.siblings('.category').find('.select2-choice').removeClass(errorClass);
            } else {
                elem.removeClass(errorClass);
            }
        }
    });

     $('select').on('change', function() {
        $(this).valid();
    });

        $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
            placeholder: "<?= lang('select_category_to_load') ?>", minimumResultsForSearch: 7, data: [
                {id: '', text: '<?= lang('select_category_to_load') ?>'}
            ]
        });
        $('#singleselect').change(function () {
            var v = $(this).val();
            $('#modal-loading').show();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= admin_url('products/getSubCategories') ?>/" + v,
                    dataType: "json",
                    success: function (scdata) {
                        if (scdata != null) {
                            scdata.push({id: '', text: '<?= lang('select_subcategory') ?>'});
                            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                                placeholder: "<?= lang('select_category_to_load') ?>",
                                minimumResultsForSearch: 7,
                                data: scdata
                            });
                        } else {
                            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('no_subcategory') ?>").select2({
                                placeholder: "<?= lang('no_subcategory') ?>",
                                minimumResultsForSearch: 7,
                                data: [{id: '', text: '<?= lang('no_subcategory') ?>'}]
                            });
                        }
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                        $('#modal-loading').hide();
                    }
                });
            } else {
                $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
                    placeholder: "<?= lang('select_category_to_load') ?>",
                    minimumResultsForSearch: 7,
                    data: [{id: '', text: '<?= lang('select_category_to_load') ?>'}]
                });
            }
            $('#modal-loading').hide();
        });

        <?php if (isset($_POST['singleselect']) && !empty($_POST['singleselect'])) { ?>
        $.ajax({
            type: "get", async: false,
            url: "<?= admin_url('products/getSubCategories') ?>/" + <?= $_POST['singleselect'] ?>,
            dataType: "json",
            success: function (scdata) {
                if (scdata != null) {
                    $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({allowClear: true,
                        placeholder: "<?= lang('no_subcategory') ?>",
                        data: scdata
                    });
                }
            }
        });
        <?php } ?>

        var ti = 0;
        $(document).on('change', '.price', function () {//alert(1)
            var row = $(this).closest('tr');
            var price = $(this).val();
            var cost = parseFloat(row.find("td:nth-child(6)").text());
        if(price < cost){
            bootbox.alert('Product price should be greater than cost');
            $(this).val('');
            $(this).focus();
            row.first('td').find('input[type="checkbox"]').iCheck('uncheck');
        }
          else {
            row.first('td').find('input[type="checkbox"]').iCheck('check');
            row.first('td').find('input[type="checkbox"]').iCheck('enable');
            row.find("input[type=text]").css('border','1px solid #ccc');

        }
        });

        $(document).on('ifChecked', 'input.multi-select', function () {
            var row = $(this).closest('tr');
            var price = parseFloat(row.find("input[type=text]").val());
            var cost = parseFloat(row.find("td:nth-child(6)").text());
            if(price < cost | row.find("input[type=text]").val()==''){
               // bootbox.alert('Product price should be greater than cost');
                row.find("input[type=text]").val('')
                row.find("input[type=text]").focus();
                row.find("input[type=text]").css('border','1px solid red')
                row.first('td').find('input[type="checkbox"]').prop( "checked", false );
                row.first('td').find('input[type="checkbox"]').iCheck('disable');
                $('#valErr').css('display','');
                //return false;
              //  row.find(".icheckbox_square-blue").removeAttr("class").removeClass('checked').addClass("icheckbox_square-blue unchecked");
               // alert(row.attr('id'))
            }
            else
            {
               // $('#valErr').css('display','none');
            }

        });
      
        function price_input(x) {
            ti = ti+1;
            var v = x.split('__');
            return "<div class=\"text-center\"><input type=\"text\" name=\"price"+v[0]+"\" value=\""+(v[1] != '' ? formatDecimals(v[1]) : '')+"\" class=\"form-control text-center price\" tabindex=\""+(ti)+"\" style=\"padding:2px;height:auto;\"></div>"; // onclick=\"this.select();\"
        }

	<?php if($this->input->post("search") ){ ?>
        $('#CGData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('products/getProductsForCAT/?v=1'.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                nRow.className = "product_group_price_id";
                return nRow;
            },
            "aoColumns": [{"bSortable": false, "mRender": hidbox}, null,null,null]
        }).fnSetFilteringDelay();
		
	<?php } ?>
    });
</script>
<?php $attributes = array('id' => 'prgroup','class' =>'pricegroup');
                echo admin_form_open("products/set_cat_new/", $attributes); ?>
<div class="box">
   

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang("list_results"); ?></p>
                
               
                <div class="col-lg-3">
                    <div class="form-group all">
                       <label id="singleselect" >Category</label>
                        <?php
                        $cat[''] = "";
                        foreach ($categories as $category) {
                            $cat[$category->id] = $category->name;
                        }
                        echo form_dropdown('singleselect', $cat, (isset($_POST['singleselect']) ? $_POST['singleselect'] : ''), 'class="form-control select " id="singleselect" required="required" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                        ?>
                    </div> 
                </div>
               
              
                 <div class="col-lg-2">
                <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('search', $this->lang->line("search"), 'class="btn btn-primary"style="margin-top:30px;" '); ?> </div>
                    </div>
					
                </div>
			
			
				
				
				 <div class="col-sm-3" style="margin-top:29px">
				 <?= form_submit('submit', 'Set category', 'id="action-form-submit" class="btn btn-primary input-xs"') ?>
				
				 </div>
                 <div class="col-sm-2">
                    <div class="form-group">
                        <?php echo form_button('reset',lang("reset"), 'class="btn btn-primary" onclick ="window.location=\''. admin_url('products/set_cat_new/').'\'" style="margin-left:20px;margin-top:30px;"');?>
                    </div>
					 <div class="form-group">
				 </div>
                </div>
				
				
                <div class="col-sm-12 error" style="display: none" id="valErr" align="center">Product price should be greater than cost</div>
                  
			</div>		
			</div>

              
				
				<div class="table-responsive">
                    <table id="CGData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th class="col-xs-3"><?= lang("product_code"); ?></th>
                            <th class="col-xs-4"><?= lang("product_name"); ?></th>
                            <th><?= lang("Subcategory"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="4" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>

                        </tbody>
                    </table>
                </div>
 
            </div>

        </div>
 <?php echo form_close(); ?>

<div class="row" style="display: block;padding:5px">
   
   
</div>
<?= form_close() ?>
<script language="javascript">
    $(document).ready(function () {

        $('#delete').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#excel').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#pdf').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#update_price').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

    });
</script>

