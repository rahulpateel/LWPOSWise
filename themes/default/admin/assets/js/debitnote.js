$(document).ready(function () {
$('body a, body button').attr('tabindex', -1);
check_add_item_val();
if (site.settings.set_focus != 1) {
    $('#add_item').focus();
}
// Order level shipping and discoutn localStorage
if (podiscount = localStorage.getItem('podiscount')) {
    $('#podiscount').val(podiscount);
}
$('#potax2').change(function (e) {
    localStorage.setItem('potax2', $(this).val());
});
if (potax2 = localStorage.getItem('potax2')) {
    $('#potax2').select2("val", potax2);
}
$('#postatus').change(function (e) {
    localStorage.setItem('postatus', $(this).val());
});
if (postatus = localStorage.getItem('postatus')) {
    $('#postatus').select2("val", postatus);
}
var old_shipping;
$('#poshipping').focus(function () {
    old_shipping = $(this).val();
}).change(function () {
    if (!is_numeric($(this).val())) {
        $(this).val(old_shipping);
        bootbox.alert(lang.unexpected_value);
        return;
    } else {
        shipping = $(this).val() ? parseFloat($(this).val()) : '0';
    }
    localStorage.setItem('poshipping', shipping);
    var gtotal = ((total + invoice_tax) - order_discount) + shipping;
    
    console.log('pos_settings'+pos_settings);
    if(pos_settings.rounding != 0) {
                gtotal = roundNumber(gtotal, parseInt(pos_settings.rounding));
               
            }
    $('#gtotal').text(formatMoney(gtotal));
    $('#tship').text(formatMoney(shipping));
});
if (poshipping = localStorage.getItem('poshipping')) {
    shipping = parseFloat(poshipping);
    $('#poshipping').val(shipping);
}

$('#popayment_term').change(function (e) {
    localStorage.setItem('popayment_term', $(this).val());
});
if (popayment_term = localStorage.getItem('popayment_term')) {
    $('#popayment_term').val(popayment_term);
}



// If there is any item in localStorage
if (localStorage.getItem('dnitems')) {
    loadItems();
}

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('dnitems')) {
                    localStorage.removeItem('dnitems');
                }
                if (localStorage.getItem('podiscount')) {
                    localStorage.removeItem('podiscount');
                }
                if (localStorage.getItem('potax2')) {
                    localStorage.removeItem('potax2');
                }
                if (localStorage.getItem('poshipping')) {
                    localStorage.removeItem('poshipping');
                }
                if (localStorage.getItem('poref')) {
                    localStorage.removeItem('poref');
                }
                if (localStorage.getItem('powarehouse')) {
                    localStorage.removeItem('powarehouse');
                }
                if (localStorage.getItem('ponote')) {
                    localStorage.removeItem('ponote');
                }
                if (localStorage.getItem('posupplier')) {
                    localStorage.removeItem('posupplier');
                }
                if (localStorage.getItem('pocurrency')) {
                    localStorage.removeItem('pocurrency');
                }
                if (localStorage.getItem('poextras')) {
                    localStorage.removeItem('poextras');
                }
                if (localStorage.getItem('podate')) {
                    localStorage.removeItem('podate');
                }
                if (localStorage.getItem('postatus')) {
                    localStorage.removeItem('postatus');
                }
                if (localStorage.getItem('popayment_term')) {
                    localStorage.removeItem('popayment_term');
                }
                if (localStorage.getItem('pobrand')) {
                    localStorage.removeItem('pobrand');
                }
                if (localStorage.getItem('posupplierstate')) {
                    localStorage.removeItem('posupplierstate');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
    });

// save and load the fields in and/or from localStorage
var $supplier = $('#posupplier'), $currency = $('#pocurrency');

$('#poref').change(function (e) {
    localStorage.setItem('poref', $(this).val());
});
if (poref = localStorage.getItem('poref')) {
    $('#poref').val(poref);
}
$('#powarehouse').change(function (e) {
    localStorage.setItem('powarehouse', $(this).val());
});
if (powarehouse = localStorage.getItem('powarehouse')) {
    $('#powarehouse').select2("val", powarehouse);
}

        $('#ponote').redactor('destroy');
        $('#ponote').redactor({
            buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
            formattingTags: ['p', 'pre', 'h3', 'h4'],
            minHeight: 100,
            changeCallback: function (e) {
                var v = this.get();
                localStorage.setItem('ponote', v);
            }
        });
        if (ponote = localStorage.getItem('ponote')) {
            $('#ponote').redactor('set', ponote);
        }
        $supplier.change(function (e) {
            var path = window.location.pathname;
            var page = path.split("/").pop();
            localStorage.setItem('posupplier', $(this).val());
            $('#supplier_id').val($(this).val());
            var status = $('#postatus :selected').text();
            $.ajax({
                type: "get", async: false,
                url: site.base_url + "suppliers/getSupplier/" + $(this).val(),
                dataType: "json",
                success: function (res) {
                    localStorage.setItem('posupplierstate', res[0].state);
                    $('#supplier_state').val(res[0].state);
                    loadItems();
                }
            });
            
            if(page !='add_po_internal')
            {
            // $.ajax({
            //     type: "get", async: false,
            //     url: site.base_url + "purchases/getProductBySupplier/" + $(this).val(),
            //     dataType: "json",
            //     success: function (res) {
            //         if(res!='false')
            //         {
            //             localStorage.setItem('dnitems', JSON.stringify(res));
            //             count = (Object.keys(res).length + 1);
            //             loadItems(count);
            //         }
            //         else
            //         {
            //             if (localStorage.getItem('dnitems')) {
            //                     localStorage.removeItem('dnitems');
            //             }
            //             $('#modal-loading').show();
            //             location.reload();
            //         }
            //     }
            // });        
            }
        });

        if (posupplier = localStorage.getItem('posupplier')) {
            console.log("posupplier "+posupplier);
            $('#supplier_state').val(localStorage.getItem('posupplierstate'));
            $supplier.val(posupplier).select2({
                minimumInputLength: 1,
                data: [],
                initSelection: function (element, callback) {

                    $.ajax({
                        type: "get", async: false,
                        url: site.base_url+"suppliers/getSupplier/" + $(element).val(),
                        dataType: "json",
                        success: function (data) {

                            callback(data[0]);

                        }
                    });
                },
                ajax: {
                    url: site.base_url + "suppliers/suggestions",
                    dataType: 'json',
                    quietMillis: 15,
                    data: function (term, page) {
                        return {
                            term: term,
                            limit: 10
                        };
                    },
                    results: function (data, page) {
                        if (data.results != null) {

                            return {results: data.results};
                        } else {
                            return {results: [{id: '', text: 'No Match Found'}]};
                        }
                    }
                }
            });

} else {
    nsSupplier();
}

    /*$('.rexpiry').change(function (e) {
        var item_id = $(this).closest('tr').attr('data-item-id');
        dnitems[item_id].row.expiry = $(this).val();
        localStorage.setItem('dnitems', JSON.stringify(dnitems));
    });*/
if (localStorage.getItem('poextras')) {
    $('#extras').iCheck('check');
    $('#extras-con').show();
}
$('#extras').on('ifChecked', function () {
    localStorage.setItem('poextras', 1);
    $('#extras-con').slideDown();
});
$('#extras').on('ifUnchecked', function () {
    localStorage.removeItem("poextras");
    $('#extras-con').slideUp();
});
$(document).on('change', '.rexpiry', function () {
    var item_id = $(this).closest('tr').attr('data-item-id');
    dnitems[item_id].row.expiry = $(this).val();
    localStorage.setItem('dnitems', JSON.stringify(dnitems));
});


// prevent default action upon enter
$('body').bind('keypress', function (e) {
    if ($(e.target).hasClass('redactor_editor')) {
        return true;
    }
    if (e.keyCode == 13) {
        e.preventDefault();
        return false;
    }
});

// Order tax calcuation
if (site.settings.tax2 != 0) {
    $('#potax2').change(function () {
        localStorage.setItem('potax2', $(this).val());
        loadItems();
        return;
    });
}

// Order discount calcuation
var old_podiscount;
$('#podiscount').focus(function () {
    old_podiscount = $(this).val();
}).change(function () {
    if (is_valid_discount($(this).val())) {
        localStorage.removeItem('podiscount');
        localStorage.setItem('podiscount', $(this).val());
        loadItems();
        return;
    } else {
        $(this).val(old_podiscount);
        bootbox.alert(lang.unexpected_value);
        return;
    }

});


    /* ----------------------
     * Delete Row Method
     * ---------------------- */

     $(document).on('click', '.podel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete dnitems[item_id];
        row.remove();
        if(dnitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('dnitems', JSON.stringify(dnitems));
            loadItems();
            return;
        }
    });

    /* -----------------------
     * Edit Row Modal Hanlder
     ----------------------- */
     $(document).on('click', '.edit', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = dnitems[item_id];
        var qty = row.children().children('.rquantity').val(),
        product_option = row.children().children('.roption').val(),
        unit_cost = formatDecimal(row.children().children('.rucost').val()),
        discount = row.children().children('.rdiscount').val();
        $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
        var real_unit_cost = item.row.real_unit_cost;
        var net_cost = real_unit_cost;
        if (site.settings.tax1) {
            $('#ptax').select2('val', item.row.tax_rate);
            $('#old_tax').val(item.row.tax_rate);
            var item_discount = 0, ds = discount ? discount : '0';
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    item_discount = parseFloat(((real_unit_cost) * parseFloat(pds[0])) / 100);
                } else {
                    item_discount = parseFloat(ds);
                }
            } else {
                item_discount = parseFloat(ds);
            }
            net_cost -= item_discount;
            var pr_tax = item.row.tax_rate, pr_tax_val = 0;
            if (pr_tax !== null && pr_tax != 0) {
                $.each(tax_rates, function () {
                    if(this.id == pr_tax){
                        if (this.type == 1) {

                            if (dnitems[item_id].row.tax_method == 0) {
                                pr_tax_val = formatDecimal((((real_unit_cost-item_discount) * parseFloat(this.rate)) / (100 + parseFloat(this.rate))), 4);
                                pr_tax_rate = formatDecimal(this.rate) + '%';
                                net_cost -= pr_tax_val;
                            } else {
                                pr_tax_val = formatDecimal((((real_unit_cost-item_discount) * parseFloat(this.rate)) / 100), 4);
                                pr_tax_rate = formatDecimal(this.rate) + '%';
                            }

                        } else if (this.type == 2) {

                            pr_tax_val = parseFloat(this.rate);
                            pr_tax_rate = this.rate;

                        }
                    }
                });
            }
        }
        if (site.settings.product_serial !== 0) {
            $('#pserial').val(row.children().children('.rserial').val());
        }
        var opt = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if(item.options !== false) {
            var o = 1;
            opt = $("<select id=\"poption\" name=\"poption\" class=\"form-control select\" />");
            $.each(item.options, function () {
                if(o == 1) {
                    if(product_option == '') { product_variant = this.id; } else { product_variant = product_option; }
                }
                $("<option />", {value: this.id, text: this.name}).appendTo(opt);
                o++;
            });
        }

        uopt = $("<select id=\"punit\" name=\"punit\" class=\"form-control select\" />");
        $.each(item.units, function () {
            if(this.id == item.row.unit) {
                $("<option />", {value: this.id, text: this.name, selected:true}).appendTo(uopt);
            } else {
                $("<option />", {value: this.id, text: this.name}).appendTo(uopt);
            }
        });

        $('#poptions-div').html(opt);
        $('#punits-div').html(uopt);
        $('select.select').select2({minimumResultsForSearch: 7});
        $('#pquantity').val(qty);
        $('#old_qty').val(qty);
        $('#pcost').val(unit_cost);
        $('#punit_cost').val(formatDecimal(parseFloat(unit_cost)+parseFloat(pr_tax_val)));
        $('#poption').select2('val', item.row.option);
        $('#old_cost').val(unit_cost);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#pexpiry').val(row.children().children('.rexpiry').val());
        $('#pdiscount').val(discount);
        $('#net_cost').text(formatMoney(net_cost));
        $('#pro_tax').text(formatMoney(pr_tax_val));
        $('#psubtotal').val('');
        $('#prModal').appendTo("body").modal('show');

    });

    $('#prModal').on('shown.bs.modal', function (e) {
        if($('#poption').select2('val') != '') {
            $('#poption').select2('val', product_variant);
            product_variant = 0;
        }
    });

    $(document).on('change', '#pcost, #ptax, #pdiscount', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var unit_cost = parseFloat($('#pcost').val());
        var item = dnitems[item_id];
        var ds = $('#pdiscount').val() ? $('#pdiscount').val() : '0';
        if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
                item_discount = parseFloat(((unit_cost) * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_cost -= item_discount;
        var pr_tax = $('#ptax').val(), item_tax_method = item.row.tax_method;
        var pr_tax_val = 0, pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if(this.id == pr_tax){
                    if (this.type == 1) {

                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimal((((unit_cost) * parseFloat(this.rate)) / (100 + parseFloat(this.rate))), 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                            unit_cost -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimal((((unit_cost) * parseFloat(this.rate)) / 100), 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                        }

                    } else if (this.type == 2) {

                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;

                    }
                }
            });
        }

        $('#net_cost').text(formatMoney(unit_cost));
        $('#pro_tax').text(formatMoney(pr_tax_val));
    });

    $(document).on('change', '#punit', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = dnitems[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var unit = $('#punit').val();
        if(unit != dnitems[item_id].row.base_unit) {
            $.each(item.units, function() {
                if (this.id == unit) {
                    $('#pcost').val(formatDecimal((parseFloat(item.row.base_unit_cost)*(unitToBaseQty(1, this))), 4)).change();
                }
            });
        } else {
            $('#pcost').val(formatDecimal(item.row.base_unit_cost)).change();
        }
    });

    $(document).on('click', '#calculate_unit_price', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = dnitems[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var subtotal = parseFloat($('#psubtotal').val()),
        qty = parseFloat($('#pquantity').val());
        $('#pcost').val(formatDecimal((subtotal/qty), 4)).change();
        return false;
    });

    /* -----------------------
     * Edit Row Method
     ----------------------- */
     $(document).on('click', '#editItem', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id'), new_pr_tax = $('#ptax').val(), new_pr_tax_rate = {};
        if (new_pr_tax) {
            $.each(tax_rates, function () {
                if (this.id == new_pr_tax) {
                    new_pr_tax_rate = this;
                }
            });
        }

        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }

        var unit = $('#punit').val();
        var base_quantity = parseFloat($('#pquantity').val());
        if(unit != dnitems[item_id].row.base_unit) {
            $.each(dnitems[item_id].units, function(){
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }

        dnitems[item_id].row.fup = 1,
        dnitems[item_id].row.qty = parseFloat($('#pquantity').val()),
        dnitems[item_id].row.base_quantity = parseFloat(base_quantity),
        dnitems[item_id].row.unit = unit,
        dnitems[item_id].row.real_unit_cost = parseFloat($('#pcost').val()),
        dnitems[item_id].row.tax_rate = new_pr_tax,
        dnitems[item_id].tax_rate = new_pr_tax_rate,
        dnitems[item_id].row.discount = $('#pdiscount').val() ? $('#pdiscount').val() : '0',
        dnitems[item_id].row.option = $('#poption').val(),
        dnitems[item_id].row.expiry = $('#pexpiry').val() ? $('#pexpiry').val() : '';
        localStorage.setItem('dnitems', JSON.stringify(dnitems));
        $('#prModal').modal('hide');
        loadItems();
        return;
    });

    /* ------------------------------
     * Show manual item addition modal
     ------------------------------- */
     $(document).on('click', '#addManually', function (e) {
        $('#mModal').appendTo("body").modal('show');
        return false;
    });

    /* --------------------------
     * Edit Row Quantity Method
     -------------------------- */
     var old_row_qty;
     $(document).on("focus", '.rquantity', function () {
        old_row_qty = $(this).val();
    }).on("change", '.rquantity', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_qty = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        dnitems[item_id].row.base_quantity = new_qty;
        if(dnitems[item_id].row.unit != dnitems[item_id].row.base_unit) {
            $.each(dnitems[item_id].units, function(){
                if (this.id == dnitems[item_id].row.unit) {
                    dnitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        dnitems[item_id].row.qty = new_qty;
        dnitems[item_id].row.received = new_qty;
        localStorage.setItem('dnitems', JSON.stringify(dnitems));
        loadItems();
    });


    /* --------------------------
     * Edit Net unit cost Method
     -------------------------- */
     var old_unit_cost;
     $(document).on("focus", '.rucost', function () {
        old_unit_cost = $(this).val();
    }).on("change", '.rucost', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_unit_cost);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_unit_cost = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        dnitems[item_id].row.real_unit_cost = new_unit_cost;
        /*if(dnitems[item_id].row.unit != dnitems[item_id].row.base_unit) {
            $.each(dnitems[item_id].units, function(){
                if (this.id == dnitems[item_id].row.unit) {
                    dnitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }*/
        dnitems[item_id].row.real_unit_cost = new_unit_cost;
        /*dnitems[item_id].row.received = new_qty;*/
        localStorage.setItem('dnitems', JSON.stringify(dnitems));
        loadItems();
    });


    var old_received;
     $(document).on("focus", '.received', function () {
        old_received = $(this).val();
    }).on("change", '.received', function () {
        var row = $(this).closest('tr');
        new_received = $(this).val() ? $(this).val() : 0;
        if (!is_numeric(new_received)) {
            $(this).val(old_received);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_received = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        if (new_received > dnitems[item_id].row.qty) {
            $(this).val(old_received);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        unit = formatDecimal(row.children().children('.runit').val()),
        $.each(dnitems[item_id].units, function(){
            if (this.id == unit) {
                qty_received = formatDecimal(unitToBaseQty(new_received, this), 4);
            }
        });
        dnitems[item_id].row.unit_received = new_received;
        dnitems[item_id].row.qty = new_received;
        dnitems[item_id].row.received = qty_received;
        localStorage.setItem('dnitems', JSON.stringify(dnitems));
        loadItems();
    });

    /* --------------------------
     * Edit Row Cost Method
     -------------------------- */
     var old_cost;
     $(document).on("focus", '.rcost', function () {
        old_cost = $(this).val();
    }).on("change", '.rcost', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val())) {
            $(this).val(old_cost);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_cost = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        dnitems[item_id].row.cost = new_cost;
        localStorage.setItem('dnitems', JSON.stringify(dnitems));
        loadItems();
    });

    $(document).on("click", '#removeReadonly', function () {
     $('#posupplier').select2('readonly', false);
     return false;
 });

    if (po_edit) {
        $('#posupplier').select2("readonly", true);
    }

});
/* -----------------------
 * Misc Actions
 ----------------------- */

// helper function for supplier if no localStorage value
function nsSupplier() {
    $('#posupplier').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "suppliers/suggestions",
            dataType: 'json',
            quietMillis: 15,
            data: function (term, page) {
                return {
                    term: term,
                    limit: 10
                };
            },
            results: function (data, page) {
                if (data.results != null) {

                    return {results: data.results};
                } else {
                    return {results: [{id: '', text: 'No Match Found'}]};
                }
            }
        }
    });
}

function loadItems(cnt='') {


    if (localStorage.getItem('dnitems')) {
        total = 0;
        count = cnt ? cnt : 1;
        rec_count = 0;
        an = 1;
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0; total_sgst = 0; total_cgst = 0; total_igst = 0; total_sgst_amt = 0; total_cgst_amt = 0; total_igst_amt = 0; total_cess = 0; total_cess_amt = 0;
        $("#poTable tbody").empty();
        dnitems = JSON.parse(localStorage.getItem('dnitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(dnitems, function(o){return [parseInt(o.order)];}) : dnitems;

        var path = window.location.pathname;
        var page = path.split("/");
        var order_no = new Date().getTime();
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : order_no++;

            var product_id = item.row.id, item_type = item.row.type, combo_items = item.combo_items, item_cost = item.row.cost, item_oqty = item.row.oqty,item_gstrate = item.row.gst_rate, item_cessrate = item.row.cess,item_gstcode = item.row.gst_code, item_qty = item.row.qty, item_bqty = item.row.quantity_balance, item_expiry = item.row.expiry, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_option = item.row.option, item_code = item.row.code, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");

            var qty_received = (item.row.received >= 0) ? item.row.received : item.row.qty;
            var item_supplier_part_no = item.row.supplier_part_no ? item.row.supplier_part_no : '';
            if (item.row.new_entry == 1) { item_bqty = item_qty; item_oqty = item_qty; }
            var unit_cost = item.row.real_unit_cost;
            var product_unit = item.row.unit, base_quantity = item.row.base_quantity;
            var supplier = localStorage.getItem('posupplier'), belong = false;
            var mrp = item.row.mrp,stock = item.row.quantity > 0 ? item.row.quantity : item.row.quantity ,alert_quantity = item.row.alert_quantity ? item.row.alert_quantity : '0';


                if (supplier == item.row.supplier1) {
                    belong = true;
                } else
                if (supplier == item.row.supplier2) {
                    belong = true;
                } else
                if (supplier == item.row.supplier3) {
                    belong = true;
                } else
                if (supplier == item.row.supplier4) {
                    belong = true;
                } else
                if (supplier == item.row.supplier5) {
                    belong = true;
                }
                var unit_qty_received = qty_received;
                var shopState = site.settings.state;
                var supstate = localStorage.getItem('posupplierstate');

                if(item.row.fup != 1 && product_unit != item.row.base_unit) {
                    $.each(item.units, function(){
                        if (this.id == product_unit) {
                            base_quantity = formatDecimal(unitToBaseQty(item.row.qty, this), 4);
                            unit_qty_received = item.row.unit_received ? item.row.unit_received : formatDecimal(baseToUnitQty(qty_received, this), 4);
                            unit_cost = formatDecimal((parseFloat(item.row.base_unit_cost)*(unitToBaseQty(1, this))), 4);
                           // console.log(unit_cost);
                        }
                    });
                }
                var ds = item_ds ? item_ds : '0';
                if (ds.indexOf("%") !== -1) {
                    var pds = ds.split("%");
                    if (!isNaN(pds[0])) {
                        item_discount = formatDecimal((parseFloat(((unit_cost) * parseFloat(pds[0])) / 100)), 4);
                    } else {
                        item_discount = formatDecimal(ds);
                    }
                } else {
                     item_discount = formatDecimal(ds);
                }
                product_discount += parseFloat(item_discount * item_qty);

                unit_cost = formatDecimal(unit_cost-item_discount);
                //var pr_tax = item.tax_rate;
                var pr_tax = item_gstrate;
                var pr_tax_val = 0, pr_tax_rate = 0;

                if(item_cessrate!='0.00')
                pr_tax = parseFloat(pr_tax) + parseFloat(item_cessrate);

         //   alert('prodTax='+pr_tax);

          if (item_tax_method == '0') { //inclusive tax

                pr_tax_val = formatDecimal(((unit_cost) * parseFloat(pr_tax)) / (100 + parseFloat(pr_tax)), 4);
                pr_tax_rate = formatDecimal(pr_tax) + '%';
            } else {
                pr_tax_val = formatDecimal(((unit_cost) * parseFloat(pr_tax)) / 100, 4);
                pr_tax_rate = formatDecimal(pr_tax) + '%';
            }

            var prod_tax = pr_tax_val * item_qty;
            product_tax += prod_tax;

           // alert('prodTaxval='+prod_tax);
                item_cost = item_tax_method == 0 ? formatDecimal(unit_cost-pr_tax_val, 4) : formatDecimal(unit_cost);

// change for cess on item
            if(item_cessrate!='0.00')
            {
                var unit_tax = prod_tax / pr_tax;
                var cess_amt = formatDecimal(item_cessrate*unit_tax, 4); //alert('cess-'+cess_amt)
                var total_gst_val = parseFloat(prod_tax) - parseFloat(cess_amt);//alert('TotalGST-'+total_gst_val);
                var gst_amt = formatDecimal(total_gst_val/(2*item_qty), 4);
            }
               else {
                var gst_amt = item_tax_method == 0 ? formatDecimal(pr_tax_val / 2, 4) : formatDecimal(pr_tax_val / 2);
                var cess_amt = 0.00;
            }

            if(supstate == shopState)
            {
                var sgst_amt = gst_amt*item_qty;
                var cgst_amt = gst_amt*item_qty;
                var cgst_per = item_gstrate/2;
                var sgst_per = item_gstrate/2;
                var igst_amt = 0,igst_per =0;


            }
            else
            {
                var igst_amt = gst_amt*item_qty*2;
                var igst_per = item_gstrate;
                sgst_amt = 0.00;
                cgst_amt = 0.00;

                var cgst_per = 0;
                var sgst_per = 0;

            }
            var cess_per = item_cessrate;
            total_sgst += parseFloat(sgst_per);
            total_cgst += parseFloat(cgst_per);
            total_igst += parseFloat(igst_per);
            total_cess += parseFloat(cess_per);
            total_sgst_amt += parseFloat(sgst_amt);
            total_cgst_amt += parseFloat(cgst_amt);
            total_igst_amt += parseFloat(igst_amt);
            total_cess_amt += parseFloat(cess_amt);
               console.log(total_igst);
                unit_cost = formatDecimal(unit_cost+item_discount, 4);
                var sel_opt = '';
                $.each(item.options, function () {
                    if(this.id == item_option) {
                        sel_opt = this.name;
                    }
                });

            var row_no = (new Date).getTime();
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><!--input name="product_id[]" type' +'="hidden" class="rid" value="' + product_id + '"--><input name="product[]" type="hidden" class="rcode" value="' + item_code + '"><!--input name="product_name[]" type="hidden" class="rname" value="' + item_name + '"><input name="product_option[]" type="hidden" class="roption" value="' + item_option + '"><input name="part_no[]" type="hidden" class="rpart_no" value="' + item_supplier_part_no + '"--><input name="gst_rate[]" type="hidden" class="gstrate" value="' + item_gstrate + '"><input name="gst_code[]" type="hidden"  value="' + item_gstcode + '"> <input name="cgst_amount[]" type="hidden"  value="' + cgst_amt + '"><input name="sgst_amount[]" type="hidden"  value="' + sgst_amt + '"><input name="igst_amount[]" type="hidden"  value="' + igst_amt + '"><input name="cess_rate[]" type="hidden" class="cessrate" value="' + item_cessrate + '"><input name="cess_amount[]" type="hidden"  value="' + cess_amt + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - '+ item_name +(sel_opt != '' ? ' ('+sel_opt+')' : '')+' <span class="label label-default">'+item_supplier_part_no+'</span></span>';
                if (page.indexOf('edit_purchase_order') <= -1) 
                { 
                  tr_html += '<i class="pull-right fa fa-edit tip edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';
                }

            tr_html += '<td class="text-right"><span class="text-right rmrp" id="rmrp_' + row_no + '">' +formatMoney(mrp)+ '</span></td>';
            if (site.settings.product_expiry == 1) {
              //  tr_html += '<td><input class="form-control date rexpiry" name="expiry[]" type="text" value="' + item_expiry + '" data-id="' + row_no + '" data-item="' + item_id + '" id="expiry_' + row_no + '"></td>';
            }

            if(page.indexOf('edit_purchase_order') > -1)
            {
                tr_html += '<td class="text-right"><input class="form-control input-sm text-right rcost" style="width: 70px;" name="net_cost[]" type="hidden" id="cost_' + row_no + '" value="' + item_cost + '"><input style="width: 70px;" class="form-control text-center rucost" name="unit_cost[]" type="hidden" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" value="' + unit_cost + '" data-id="' + row_no + '" data-item="' + item_id + '" id="rucost_' + row_no + '" onClick="this.select();"><input class="realucost" name="real_unit_cost[]" type="hidden" value="' + item.row.real_unit_cost + '"><span class="text-right sreal_unit_cost" id="sreal_unit_cost' + row_no + '">' +formatMoney(unit_cost)+ '</span></td>';
                tr_html += '<td class="text-right"><span class="text-right" id="stock_' + row_no + '">' +formatQuantity2(stock)+ '</span></td>';
                tr_html += '<td class="text-right"><span class="text-right" id="alert_quantity_' + row_no + '">' +formatQuantity2(alert_quantity)+ '</span></td>';
                tr_html += '<td class="text-right"><input name="quantity_balance[]" type="hidden" class="rbqty" value="' + item_bqty + '"><input class="form-control text-center rquantity" name="quantity[]" type="hidden" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" value="' + formatQuantity2(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"><input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '"><span class="text-right squantity" id="squantity' + row_no + '">' +formatQuantity2(item_qty)+ '</span></td>';
            }
            else
            {
                tr_html += '<td class="text-right"><input class="form-control input-sm text-right rcost" name="net_cost[]" type="hidden" id="cost_' + row_no + '" value="' + item_cost + '"><input style="width:70px" class="form-control text-center rucost" name="unit_cost[]" type="text" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" value="' + unit_cost + '" data-id="' + row_no + '" data-item="' + item_id + '" id="rucost_' + row_no + '" onClick="this.select();"><input class="realucost" name="real_unit_cost[]" type="hidden" value="' + item.row.real_unit_cost + '"></td>';
               
                tr_html += '<td class="text-right"><span class="text-right" id="price_' + row_no + '">' +formatDecimal(item.row.price)+ '</span></td>';
               
                tr_html += '<td class="text-right"><span class="text-right" id="stock_' + row_no + '">' +formatQuantity2(stock)+ '</span></td>';
                tr_html += '<td class="text-right"><input class="form-control text-center alert_quantity" name="alert_quantity[]" type="text" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" value="' + formatQuantity2(alert_quantity) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="alert_quantity_' + row_no + '" onClick="this.select();"></td>'
                tr_html += '<td><input name="quantity_balance[]" type="hidden" class="rbqty" value="' + item_bqty + '"><input class="form-control text-center rquantity" name="quantity[]" type="text" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" value="' + formatQuantity2(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"><input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '"></td>';
            }



            
            if (po_edit) {
                if(page.indexOf('edit_purchase_order') > -1)
                {
                    tr_html += '<td class="rec_con text-right"><input name="ordered_quantity[]" type="hidden" class="oqty" value="' + item_oqty + '"><input class="form-control text-center received" name="received[]" type="hidden" value="' + formatDecimal(unit_qty_received) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="received_' + row_no + '" onClick="this.select();"><input name="received_base_quantity[]" type="hidden" class="rrbase_quantity" value="' + qty_received + '"><span class="text-right" id="rec_' + row_no + '">' +formatDecimal(unit_qty_received)+ '</span></td>';
                }
                else
                {
                    tr_html += '<td class="rec_con"><input name="ordered_quantity[]" type="hidden" class="oqty" value="' + item_oqty + '"><input class="form-control text-center received" name="received[]" type="text" value="' + formatDecimal(unit_qty_received) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="received_' + row_no + '" onClick="this.select();"><input name="received_base_quantity[]" type="hidden" class="rrbase_quantity" value="' + qty_received + '"></td>';
                }
            }
            if (site.settings.product_discount == 1) {
                tr_html += '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '"><span class="text-right sdiscount text-danger" id="sdiscount_' + row_no + '">' + formatMoney(0 - (item_discount * item_qty)) + '</span></td>';
            }

                tr_html += '<td class="text-right"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' +formatMoney(cgst_per)+ '</span></td>';
            tr_html += '<td class="text-right"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' +formatMoney(cgst_amt)+ '</span></td>';

            tr_html += '<td class="text-right"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' +formatMoney(sgst_per)+ '</span></td>';

            tr_html += '<td class="text-right"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' +formatMoney(sgst_amt)+ '</span></td>';
            tr_html += '<td class="text-right"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' +formatMoney(igst_per)+ '</span></td>';
            tr_html += '<td class="text-right"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' +formatMoney(igst_amt)+ '</span></td>';


            tr_html += '<td class="text-right"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' +formatMoney(cess_per)+ '</span></td>';
            tr_html += '<td class="text-right"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' +formatMoney(cess_amt)+ '</span></td>';

            tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(((parseFloat(item_cost) + parseFloat(pr_tax_val)) * parseFloat(item_qty))) + '</span></td>';
            if(page.indexOf('edit_purchase_order') <= -1)
            {
                tr_html += '<td class="text-center"><i class="fa fa-times tip podel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            }

          //  console.log(pr_tax_val);

            newTr.html(tr_html);
            newTr.prependTo("#poTable");
            $('#poTable tr').addClass('display_subtotal');

            //alert($('#poTable tr').find('td:nth-last-child(2) span').html());
           
           total += formatDecimal(((parseFloat(item_cost) + parseFloat(pr_tax_val)) * parseFloat(item_qty)), 4);
            // total += formatDecimal(((parseFloat(item_cost) ) * parseFloat(item_qty)), 4);
          //  console.log(total);
            count += parseFloat(item_qty);
            rec_count += parseFloat(qty_received);
            an++;
            if(!belong)
                $('#row_' + row_no).addClass('warning');
        });

        var col = 6;
        if(page.indexOf('edit_purchase_order') > -1)
         {
                col = 6;
         }

        if (site.settings.product_expiry == 1) { col++; }
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-right">' + formatQty(parseFloat(count) - 1) + '</th>';
          if(page.indexOf('edit_purchase_order') <= -1)
         {      
        //  tfoot += '<th class="text-right">'+ formatQty(parseFloat(tot_free_quantity) ) + '</th>';  
          }  
        if (po_edit) {
            tfoot += '<th class="rec_con text-right">'+ formatQty(parseFloat(rec_count) ) + '</th>';
        }
        if (site.settings.product_discount == 1) {
            tfoot += '<th class="text-right">'+formatMoney(product_discount)+'</th>';
        }
        /*if (site.settings.tax1 == 1) {
            tfoot += '<th class="text-right">'+formatMoney(product_tax)+'</th>';
        }*/
        tfoot += '<th class="text-right"></th>'; //CGST %
        tfoot += '<th class="text-right">'+formatMoney(total_cgst_amt)+'</th>'; //CGST Amt
        tfoot += '<th class="text-right"></th>'; //SGST %
        tfoot += '<th class="text-right">'+formatMoney(total_sgst_amt)+'</th>'; //SGST Amt
        tfoot += '<th class="text-right"></th>'; //IGST %
        tfoot += '<th class="text-right">'+formatMoney(total_igst_amt)+'</th>'; //IGST Amt
        tfoot += '<th class="text-right"></th>'; //CESS %
        tfoot += '<th class="text-right">'+formatMoney(total_cess_amt)+'</th>'; //CESS Amt
        tfoot += '<th class="text-right">'+formatMoney(total)+'</th>' ;
        if(page.indexOf('edit_purchase_order') <= -1)
        {
            tfoot += '<th class="text-center"><i  style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        }
        $('#poTable tfoot').html(tfoot);



        // Order level discount calculations
        if (podiscount = localStorage.getItem('podiscount')) {
            var ds = podiscount;
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    order_discount = formatDecimal(((total * parseFloat(pds[0])) / 100), 4);
                } else {
                    order_discount = formatDecimal(ds);
                }
            } else {
                order_discount = formatDecimal(ds);
            }
        }

        // Order level tax calculations
        if (site.settings.tax2 != 0) {
            if (potax2 = localStorage.getItem('potax2')) {
                $.each(tax_rates, function () {
                    if (this.id == potax2) {
                        if (this.type == 2) {
                            invoice_tax = formatDecimal(this.rate);
                            console.log();
                        }
                        if (this.type == 1) {
                            invoice_tax = formatDecimal((((total - order_discount) * this.rate) / 100), 4);
                        }
                    }
                });
            }
        }
        total_discount = parseFloat(order_discount + product_discount);
        // Totals calculations after item addition
        var gtotal = ((total ) - order_discount) + shipping;
        console.log('pos_settings');
        console.log(pos_settings);
        if(pos_settings.rounding != 0) {
                gtotal = roundNumber(gtotal, parseInt(pos_settings.rounding));
               
            }
     //   console.log(gtotal);
        $('#total').text(formatMoney(total));
        $('#titems').text((an-1)+' ('+(formatQty(parseFloat(count) - 1))+')');
        $('#tds').text(formatMoney(order_discount));

        $('#ttax2').text(formatMoney(product_tax));

       /* if (site.settings.tax1) {
            $('#ttax').text(formatMoney(product_tax));
        }
        if (site.settings.tax2 != 0) {
            $('#ttax2').text(formatMoney(invoice_tax));
        }*/
        $('#gtotal').text(formatMoney(gtotal));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
    }
}

/* -----------------------------
 * Add Purchase Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
 function add_purchase_item(item) {
    if (count == 1) {
        dnitems = {};
        if ($('#posupplier').val()) {
            $('#posupplier').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null)
        return;


    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (dnitems[item_id]) {

        var new_qty = parseFloat(dnitems[item_id].row.qty) + 1;
        dnitems[item_id].row.base_quantity = new_qty;
        if(dnitems[item_id].row.unit != dnitems[item_id].row.base_unit) {
            $.each(dnitems[item_id].units, function(){
                if (this.id == dnitems[item_id].row.unit) {
                    dnitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        dnitems[item_id].row.qty = new_qty;

    } else {
        dnitems[item_id] = item;
        var product_id = $('#add_prod').val();
        if(product_id)
            $('#add_prod').val(product_id+','+item_id);
        else
            $('#add_prod').val(item_id);
    }
    dnitems[item_id].order = new Date().getTime();
    localStorage.setItem('dnitems', JSON.stringify(dnitems));
    loadItems();
    return true;

}

if (typeof (Storage) === "undefined") {
    $(window).bind('beforeunload', function (e) {
        if (count > 1) {
            var message = "You will loss data!";
            return message;
        }
    });
}

/* --------------------------
     * Edit Alert Quantity Method
     -------------------------- */
     var old_row_qty;
     $(document).on("focus", '.alert_quantity', function () {
        old_row_qty = $(this).val();
    }).on("change", '.alert_quantity', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) | $(this).val()< 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_qty = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        var product_id = dnitems[item_id].row.product_id;
        $.ajax({
                type: "get", async: false,
                url: site.base_url + "purchases/updateAlertQuantity",
                data: { alert_quantity: new_qty, product_id: product_id },
                success: function (res) {
                    if(res=='false')
                    {
                        $(this).val(old_row_qty);
                    }
                    else
                    {
                        dnitems[item_id].row.alert_quantity = new_qty;
                        localStorage.setItem('dnitems', JSON.stringify(dnitems));
                        loadItems();
                    }
                    
                }
            });
    });

    $('#remove_items').on('ifChecked', function (e) {
        if (localStorage.getItem('dnitems')) {
            localStorage.removeItem('dnitems');
        } 
        $('#modal-loading').show();
        location.reload();

    });

    
