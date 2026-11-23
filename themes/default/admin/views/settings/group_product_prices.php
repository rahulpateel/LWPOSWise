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
<script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
<script>

    $(document).ready(function () {
    $.validator.setDefaults({
        ignore: []
    });


    $('#prgroup').validate({
        errorElement: 'small',
        errorClass: 'error',
        rules: {
            singleselect:'required',
            percentage:'required'
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
        $(document).on('change', '.price', function () {alert(1)
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
        $(document).on('click', '.form-submit', function () {
            var btn = $(this);
            btn.html('<i class="fa fa-circle-o-notch fa-spin fa-fw"></i>');
            var row = btn.closest('tr');
            var cost = parseFloat(row.find("td:nth-child(6)").text());
            var price = row.find('.price').val();
            if(price < cost | row.find("input[type=text]").val()=='') {
                bootbox.alert('Product price should be greater than cost');
               // row.find("input[type=text]").val('')
                row.find("input[type=text]").focus();
                //row.find("input[type=text]").css('border', '1px solid red');

                btn.removeClass('btn-primary').addClass('btn-danger').html('<i class="fa fa-times"></i>');
                return;
            }
            var product_id = row.attr('id');

            $.ajax({
                type: 'post',
                url: '<?= admin_url('system_settings/update_product_group_price/'.$price_group->id); ?>',
                dataType: "json",
                data: {
                    <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>',
                    product_id: product_id, price: price
                },
                success: function (data) {
                    if (data.status != 1)
                        btn.removeClass('btn-primary').addClass('btn-danger').html('<i class="fa fa-times"></i>');
                    else
                        btn.removeClass('btn-primary').removeClass('btn-danger').addClass('btn-success').html('<i class="fa fa-check"></i>');
                },
                error: function (data) {
                    btn.removeClass('btn-primary').addClass('btn-danger').html('<i class="fa fa-times"></i>');
                }
            });
            // btn.html('<i class="fa fa-check"></i>');
        });
        function price_input(x) {
            ti = ti+1;
            var v = x.split('__');
            return "<div class=\"text-center\"><input type=\"text\" name=\"price"+v[0]+"\" value=\""+(v[1] != '' ? formatDecimals(v[1]) : '')+"\" class=\"form-control text-center price\" tabindex=\""+(ti)+"\" style=\"padding:2px;height:auto;\"></div>"; // onclick=\"this.select();\"
        }

        $('#CGData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('system_settings/getProductPrices/'.$price_group->id.'/?v=1'.$v) ?>',
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
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, null,null,null,null,null, {"bSortable": false, "mRender": price_input}, {"bSortable": false}]
        }).fnSetFilteringDelay();
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-building"></i><?= $page_title ?> (<?= $price_group->name; ?>)</h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="#" id="update_price" data-action="update_price">
                                <i class="fa fa-rupee"></i> <?= lang('update_price') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo admin_url('system_settings/update_prices_csv/'.$price_group->id); ?>" data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-upload"></i> <?= lang('update_prices_csv') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" id="delete" data-action="delete">
                                <i class="fa fa-trash-o"></i> <?= lang('delete_product_group_prices') ?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang("list_results"); ?></p>
                <?php $attributes = array('id' => 'prgroup','class' =>'pricegroup');
                echo admin_form_open("system_settings/group_product_prices/$price_group->id", $attributes); ?>
                <div class="col-lg-3">
                    <div class="form-group all">
                        <?= lang("brand", "brand") ?>
                        <?php
                        $br[''] = "";
                        foreach ($brands as $brand) {
                            $br[$brand->id] = $brand->name;
                        }
                        echo form_dropdown('brand', $br, (isset($_POST['brand']) ? $_POST['brand'] : ''), 'class="form-control select" id="brand" placeholder="' . lang("select") . " " . lang("brand") . '" style="width:100%"')
                        ?>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group all">
                       <label id="codespan" >Category <span>*</span></label>
                        <?php
                        $cat[''] = "";
                        foreach ($categories as $category) {
                            $cat[$category->id] = $category->name;
                        }
                        echo form_dropdown('singleselect', $cat, (isset($_POST['singleselect']) ? $_POST['singleselect'] : ''), 'class="form-control select category" id="singleselect" placeholder="' . lang("select") . " " . lang("category") . '" required="required" style="width:100%"')
                        ?>
                    </div> 
                </div>
                <div class="col-lg-2">
                    <div class="form-group all">
                        <?= lang("subcategory", "subcategory") ?>
                        <div class="controls" id="subcat_data"> <?php
                            echo form_input('subcategory', (isset($_POST['subcategory']) ? $_POST['subcategory'] : ''), 'class="form-control" id="subcategory"  placeholder="' . lang("select_category_to_load") . '"');
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group all">
                        <?= lang("percentage", "percentage") ?>
                            <?= form_input('percentage', (isset($_POST['percentage']) ? $_POST['percentage'] : ''), 'class="form-control tip" id="percentage" required="required"') ?>
                    </div>
                </div>
                <div class="col-lg-1">
                <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"style="margin-top:30px;" '); ?> </div>
                    </div>
                </div>
                 <div class="col-sm-1">
                    <div class="form-group">
                        <?php echo form_button('reset',lang("reset"), 'class="btn btn-primary" onclick ="window.location=\''. admin_url('system_settings/group_product_prices/'.$price_group->id).'\'" style="margin-left:20px;margin-top:30px;"');?>
                    </div>
                </div>
                <div class="col-sm-12 error" style="display: none" id="valErr" align="center">Product price should be greater than cost</div>
                    <?php echo form_close(); ?>

                <?= admin_form_open('system_settings/product_group_price_actions/'.$price_group->id, 'id="action-form"') ?>
                <div class="table-responsive">
                    <table id="CGData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th class="col-xs-3"><?= lang("product_code"); ?></th>
                            <th class="col-xs-4"><?= lang("product_name"); ?></th>
                            <th><?= lang("MRP"); ?></th>
                            <th><?= lang("product_price"); ?></th>
                            <th><?= lang("product_cost"); ?></th>
                            <th><?= lang("price"); ?></th>
                            <th style="width:85px;"><?= lang("update"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>

                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>

<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action"/>
    <?= form_submit('submit', 'submit', 'id="action-form-submit"') ?>
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

