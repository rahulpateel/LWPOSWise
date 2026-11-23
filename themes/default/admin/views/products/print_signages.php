<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css">
    @font-face {
    font-family: 'MyCustomFont';
    src: url('<?= $assets ?>fonts/bahnschrift.ttf');
}
</style>
<div class="box">
    <div class="box-header no-print">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('print_signages'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="no-print" onclick="window.print();return false;" id="print-icon" class="tip" title="<?= lang('print') ?>">
                        <i class="icon fa fa-print no-print"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                 <?php echo form_input('add_item', '', 'class="form-control" id="add_item" placeholder="' . $this->lang->line("add_item") . '"'); ?>
                <div class="well well-sm no-print">
                    <div class="form-group">
                        <?= lang("add_product", "add_item"); ?>
                       
                    </div>
                    <?= admin_form_open("products/print_signages", 'id="barcode-print-form" data-toggle="validator"'); ?>
                    <div class="controls table-controls">
                        <table id="bcTable"
                               class="table items table-striped table-bordered table-condensed table-hover">
                            <thead>
                            <tr>
                                <th class="col-xs-10"><?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?></th>
                                <th class="col-xs-1"><?= lang("quantity"); ?></th>

                                <th class="col-xs-1 text-center">
                                    <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                </th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                        <div class="form-group">
                            <?= lang('style', 'style'); ?>
                            <?php $opts = array('' => lang('select').' '.lang('style'), 1 => lang('1_per_line'), 2 => lang('2_per_line'), 3 => lang('3_per_line'), 4 => lang('4_per_line')); ?>
                            <?= form_dropdown('style', $opts, set_value('style', 1), 'class="form-control tip" id="style" required="required"'); ?>
                            <div class="row cf-con" style="margin-top: 10px; display: none;">
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <?= form_input('cf_width', '', 'class="form-control" id="cf_width" placeholder="' . lang("width") . '"'); ?>
                                            <span class="input-group-addon" style="padding-left:10px;padding-right:10px;"><?= lang('inches'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <?= form_input('cf_height', '', 'class="form-control" id="cf_height" placeholder="' . lang("height") . '"'); ?>
                                            <span class="input-group-addon" style="padding-left:10px;padding-right:10px;"><?= lang('inches'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="form-group">
                                    <?php $oopts = array(0 => lang('portrait'), 1 => lang('landscape')); ?>
                                        <?= form_dropdown('cf_orientation', $oopts , '', 'class="form-control" id="cf_orientation" placeholder="' . lang("orientation") . '"'); ?>
                                    </div>
                                </div>
                            </div>
                            <span class="help-block"><?= lang('barcode_tip'); ?></span>
                            <div class="clearfix"></div>


                        </div>
               

                    <div class="form-group">
                        <span style="font-weight: bold; margin-right: 15px;"><?= lang('language'); ?>:</span>

                        <input name="langauge" type="radio" id="langauge1" value="marathi" style="display:inline-block;" <?php if($this->input->post('langauge')=='marathi'){echo 'checked';}?> checked />
                        <label for="langauge1" class="padding05"><?= lang('Marathi'); ?></label>
                        <input name="langauge" type="radio" id="langauge2" value="english" style="display:inline-block;" <?php if($this->input->post('langauge')=='english'){echo 'checked';}?> />
                        <label for="langauge2" class="padding05"><?= lang('English'); ?></label>
                    </div>

                     <div class="form-group">
                        <input name="isgst" type="checkbox" id="isgst" value="GST Benifit" style="display:inline-block;" <?php if($this->input->post('isgst')){echo 'checked';}?>  />
                        <label for="isgst" class="padding05"><?= lang('GST_Benifit'); ?></label>
                     </div>

                    <div class="form-group">
                        <?php echo form_submit('print', lang("update"), 'class="btn btn-primary"'); ?>
<!--	                    --><?php //echo form_submit('download', lang("download"), 'class="btn btn-primary"'); ?>
                        <button type="button" id="reset" class="btn btn-danger"><?= lang('reset'); ?></button>
                    </div>
                  
                    <div class="clearfix"></div>
                      <?= form_close(); ?>
                </div>
                
                <div id="barcode-con">
                    <?php
                        if ($this->input->post('print')) {

                            if (!empty($barcodes)) {
                                echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" title="'.lang('print').'"><i class="icon fa fa-print no-print"></i> '.lang('print').'</button>';
                                $c = 1;
                               // if ($style == 1 || $style == 2 || $style == 4 ) { //|| $style == 40
                                    echo '<div class="barcodea4">';
                                //}
                                foreach ($barcodes as $item) {
                                    for ($r = 1; $r <= $item['quantity']; $r++) {
                                        echo '<div class="style'.$style.' signagebox" '.
                                        ($style != 1 ? 'style="margin-right:1%;border:5px solid!important;float:left;padding:10px 5px;width:'.((100/$style)-2).'%;"' : '').'>';

                                        $discount = $item['mrp'] - $item['price'];
//print_r($item);die;

                                        if($item['marathi_name']!='' && $item['langauge'] == 'marathi') {
                                         
                                                if($this->input->post('isgst')){
                                           echo '<div class="firstline">अधिक GST सवलत </div>'; 
                                                        }

                                            echo '<span class="firstline" > ₹<span class="discprice"> '.(int)$this->sma->formatDecimal($discount,0).'</span> सुट</span>';
                                            echo '<span class="barcode_name" style="float:left" >'.$item['marathi_name'].'</span>';
                                            echo '<span class="barcode_price"  style="font-size:22px!important">मुळ किंमत : ₹ '.$this->sma->formatDecimal($item['mrp']).'</span><br>';
                                            echo '<span class="barcode_price" style="float:right" >न्यू बिग मार्ट किंमत : ₹ '. $this->sma->formatDecimal($item['price']).'</span> ';
                                        }
                                        elseif($item['name']) {
                                            
                                            if($item['langauge'] == 'marathi') {

                                            if($this->input->post('isgst')){        
                                            echo '<div class="firstline">अधिक GST सवलत </div>';
                                            }    
                                            echo '<span class="firstline">₹<span class="discprice">'.$this->sma->formatPrice($discount,1).'</span> सूट</span>';

                                            echo '<span class="barcode_name">'.$item['name'].'</span><br/>';
                                            echo '<span  class="barcode_price" style="font-size:22px!important">मुळ किंमत : ₹ '.$this->sma->formatSig($item['mrp']).'</span>';
                                            echo '<span class="barcode_price">न्यू बिग मार्ट किंमत : ₹ '. $this->sma->formatSig($item['price']).'</span> ';
                                        }
                                        else
                                        {
                                            if($this->input->post('isgst')){
                                            echo '<div class="firstline">GST Benifit</div>'; 
                                                }

                                            echo '<span class="firstline">₹ <span class="discprice"> '.$this->sma->formatPrice($discount,1).'</span> Discount</span><br/>';

                                            echo '<span class="barcode_namee">'.$this->sma->camelCase(($item['name'])).'</span><br/>';
                                            echo '<span class="barcode_pricee">'.lang('mrp').' : ₹ '.$this->sma->formatPrice($item['mrp']).'';
                                            echo '&nbsp'.lang('Shetkari_Market_Price').' : ₹ '. $this->sma->formatPrice($item['price']).'</span> ';

                                        }


                                            // echo '<span class="barcode_name">'.$item['name'].'</span>';
                                            // echo '<span class="barcode_price">'.lang('mrp').' : ₹ '.$this->sma->formatDecimal($item['mrp']).'</span><br> ';
                                            // echo '<span class="barcode_price">'.lang('Shetkari_Market_Price').' : ₹ '. $this->sma->formatDecimal($item['price']).'</span> ';
                                       


                                        }



                                        echo '</div>';
                                        if ($style == 2) {
                                            if ($c % 2 == 0) {
                                                echo '<div  class="clearfix"></div>';
                                                echo '<div  class="" style="margin-bottom:6px;"></div>';
                                            }

                                        }if ($style == 3) {
                                            if ($c % 3 == 0) {
                                                echo '<div class="clearfix"></div>';
                                                echo '<div  class="" style="margin-bottom:6px;"></div>';
                                            }

                                        }if ($style == 4) {
                                            if ($c % 4 == 0) {
                                                echo '<div class="clearfix"></div>';
                                            }

                                        }
                                        $c++;
                                    }
                                }
                                echo '</div>';
                                echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" title="'.lang('print').'"><i class="icon fa fa-print no-print"></i> '.lang('print').'</button>';
                            } else {
                                echo '<h3>'.lang('no_product_selected').'</h3>';
                            }
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var ac = false; bcitems = {};
    if (localStorage.getItem('bcitems')) {
        bcitems = JSON.parse(localStorage.getItem('bcitems'));
    }
    <?php if($items) { ?>
    localStorage.setItem('bcitems', JSON.stringify(<?= $items; ?>));
    <?php } ?>
    $(document).ready(function() {
        <?php if ($this->input->post('print')) { ?>
            $( window ).load(function() {
                $('html, body').animate({
                    scrollTop: ($("#barcode-con").offset().top)-15
                }, 1000);
            });
        <?php } ?>
        if (localStorage.getItem('bcitems')) {
            loadItems();
        }
        $("#add_item").autocomplete({
            source: '<?= admin_url('products/get_suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');

                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_product_item(ui.item);
                    if (row) {
                        $(this).val('');
                    }
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>');
                }
            }
        });
        check_add_item_val();



        $('#cf_width').change(function (e) {
            localStorage.setItem('cf_width', $(this).val());
        });
        if (cf_width = localStorage.getItem('cf_width')) {
            $('#cf_width').val(cf_width);
        }

        $('#cf_height').change(function (e) {
            localStorage.setItem('cf_height', $(this).val());
        });
        if (cf_height = localStorage.getItem('cf_height')) {
            $('#cf_height').val(cf_height);
        }

        $('#cf_orientation').change(function (e) {
            localStorage.setItem('cf_orientation', $(this).val());
        });
        if (cf_orientation = localStorage.getItem('cf_orientation')) {
            $('#cf_orientation').val(cf_orientation);
        }

        $(document).on('ifChecked', '#site_name', function(event) {
            localStorage.setItem('bcsite_name', 1);
        });
        $(document).on('ifUnchecked', '#site_name', function(event) {
            localStorage.setItem('bcsite_name', 0);
        });
        if (site_name = localStorage.getItem('bcsite_name')) {
            if (site_name == 1)
                $('#site_name').iCheck('check');
            else
                $('#site_name').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#product_name', function(event) {
            localStorage.setItem('bcproduct_name', 1);
        });
        $(document).on('ifUnchecked', '#product_name', function(event) {
            localStorage.setItem('bcproduct_name', 0);
        });
        if (product_name = localStorage.getItem('bcproduct_name')) {
            if (product_name == 1)
                $('#product_name').iCheck('check');
            else
                $('#product_name').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#price', function(event) {
            localStorage.setItem('bcprice', 1);
        });
        $(document).on('ifUnchecked', '#price', function(event) {
            localStorage.setItem('bcprice', 0);
            $('#currencies').iCheck('uncheck');
        });
        if (price = localStorage.getItem('bcprice')) {
            if (price == 1)
                $('#price').iCheck('check');
            else
                $('#price').iCheck('uncheck');
        }


        $(document).on('ifChecked', '.checkbox', function(event) {
            var item_id = $(this).attr('data-item-id');
            var vt_id = $(this).attr('id');
            bcitems[item_id]['selected_variants'][vt_id] = 1;
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
        });
        $(document).on('ifUnchecked', '.checkbox', function(event) {
            var item_id = $(this).attr('data-item-id');
            var vt_id = $(this).attr('id');
            bcitems[item_id]['selected_variants'][vt_id] = 0;
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
        });

        $(document).on('click', '.del', function () {
            var id = $(this).attr('id');
            delete bcitems[id];
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
            $(this).closest('#row_' + id).remove();
        });

        $('#reset').click(function (e) {

            bootbox.confirm(lang.r_u_sure, function (result) {
                if (result) {
                    if (localStorage.getItem('bcitems')) {
                        localStorage.removeItem('bcitems');
                    }
                    if (localStorage.getItem('bcstyle')) {
                        localStorage.removeItem('bcstyle');
                    }
                    if (localStorage.getItem('bcsite_name')) {
                        localStorage.removeItem('bcsite_name');
                    }
                    if (localStorage.getItem('bcproduct_name')) {
                        localStorage.removeItem('bcproduct_name');
                    }
                    if (localStorage.getItem('bcprice')) {
                        localStorage.removeItem('bcprice');
                    }


                    $('#modal-loading').show();
                    window.location.replace("<?= admin_url('products/print_signages'); ?>");
                }
            });
        });

        var old_row_qty;
        $(document).on("focus", '.quantity', function () {
            old_row_qty = $(this).val();
        }).on("change", '.quantity', function () {
            var row = $(this).closest('tr');
            if (!is_numeric($(this).val())) {
                $(this).val(old_row_qty);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_qty = parseFloat($(this).val()),
            item_id = row.attr('data-item-id');
            bcitems[item_id].qty = new_qty;
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
        });

    });

    function add_product_item(item) {
        ac = true;
        if (item == null) {
            return false;
        }
        item_id = item.id;
        if (bcitems[item_id]) {
            bcitems[item_id].qty = parseFloat(bcitems[item_id].qty) + 1;
        } else {
            bcitems[item_id] = item;
            bcitems[item_id]['selected_variants'] = {};
            $.each(item.variants, function () {
                bcitems[item_id]['selected_variants'][this.id] = 1;
            });
        }

        localStorage.setItem('bcitems', JSON.stringify(bcitems));
        loadItems();
        return true;

    }

    function loadItems () {

        if (localStorage.getItem('bcitems')) {
            $("#bcTable tbody").empty();
            bcitems = JSON.parse(localStorage.getItem('bcitems'));

            $.each(bcitems, function () {

                var item = this;
                var row_no = item.id;
                var vd = '';
                var newTr = $('<tr id="row_' + row_no + '" class="row_' + item.id + '" data-item-id="' + item.id + '"></tr>');
                tr_html = '<td><input name="product[]" type="hidden" value="' + item.id + '"><span id="name_' + row_no + '">' + item.name + ' (' + item.code + ')</span></td>';
                tr_html += '<td><input class="form-control quantity text-center" name="quantity[]" type="number" value="' + formatDecimal(item.qty) + '" data-id="' + row_no + '" data-item="' + item.id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';

                // tr_html += '<td>'+vd+'</td>';
                tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.appendTo("#bcTable");
            });
            $('input[type="checkbox"],[type="radio"]').not('.skip').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%'
            });
            return true;
        }
    }

</script>
<style type="text/css">
    .barcodea4{
        /*padding: 0.1in 0 0 0.1in;*/
    }
    .style1 .firstline{
        font-weight: bold;
        font-size: 18px;
    }
    .style2 .firstline{
        font-weight: bold;
        font-size: 44px;
        font-family: "Arial Black";
    }
    .style3 .firstline{
        font-weight: bold;
        font-size: 20px;
    }
    .style4 .firstline{
        font-weight: bold;
        font-size: 10px;
    }
    .style1 .discprice{
        font-size: 200px;
        font-weight: bold;
        line-height: normal;
    }
    .style2 .discprice{
        font-size: 80px;
        font-weight: bold;
        font-family: "Arial Black";
    }
    .style3 .discprice{
        font-size: 60px; /*35px*/
        font-weight: bold;
		font-family: 'Ubuntu';
    }
    .style4 .discprice{
        font-size: 18px;
        font-weight: bold;
    }
    .signagebox{
        text-align: center;
        padding: 50px;
        border: 0.5px solid;
        page-break-inside: avoid;

    }
    .style1 .barcode_name, .style1 .barcode_price{

        font-size: 24px;
    }
    .style2 .barcode_name{

        font-size: 18px;
        font-weight: bold;
        font-family: "Arial Black";
        /*padding: 0 35px;*/
    }
    .style3 .barcode_name, .style3 .barcode_price{

        font-size: 19px;/*17px*/
		 font-weight: bold;
        /*padding: 0 35px;*/
    }

     .style3 .barcode_namee, .style3 .barcode_pricee{

        font-size: 15px;
         font-weight: bold;
        /*padding: 0 35px;*/
    }
    .style4 .barcode_name, .style4 .barcode_price{

        font-size: 11px;
        /*padding: 0 35px;*/
    }

    .style2 .barcode_price{

        font-size: 16px;
        font-weight: bold;
        /*padding: 0 35px;*/
    }
</style>