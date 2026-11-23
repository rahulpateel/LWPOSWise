<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Shared result table for stock_upload / stock_check.  Vars: $rows (array), $tbl_id (string) */
?>
<div class="clearfix" style="margin-bottom:8px;">
    <label class="checkbox-inline" style="font-weight:bold;">
        <input type="checkbox" id="<?= $tbl_id; ?>_only_diff"> Show only products with difference
    </label>
</div>
<div class="table-responsive">
    <table id="<?= $tbl_id; ?>" class="table table-bordered table-condensed table-hover table-striped" style="width:100%;">
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Barcode</th>
            <th>WhQty</th>
            <th>MRP</th>
            <th>Cost</th>
            <th>Price</th>
            <th>Floor Qty</th>
            <th>Total Qty<br><small>(WhQty + Floor Qty)</small></th>
            <th>System Qty</th>
            <th>Difference<br><small>(Total - System)</small></th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        var data = <?= json_encode($rows, JSON_PARTIAL_OUTPUT_ON_ERROR); ?>;
        var num = function (d) {
            if (d === '' || d === null) { return ''; }
            return parseFloat(parseFloat(d).toFixed(4)).toString();
        };
        var tbl = $('#<?= $tbl_id; ?>').dataTable({
            "aaData": data,
            "bDeferRender": true,
            "aaSorting": [[0, "asc"]],
            "aLengthMenu": [[25, 50, 100, 500, -1], [25, 50, 100, 500, "All"]],
            "iDisplayLength": 50,
            "aoColumns": [
                null, null, null,
                {"mRender": num, "sClass": "text-right"},
                {"mRender": num, "sClass": "text-right"},
                {"mRender": num, "sClass": "text-right"},
                {"mRender": num, "sClass": "text-right"},
                {"mRender": num, "sClass": "text-right"},
                {"mRender": num, "sClass": "text-right"},
                {"mRender": num, "sClass": "text-right"},
                {"mRender": num, "sClass": "text-right"}
            ],
            "fnCreatedRow": function (nRow, aData) {
                var d = parseFloat(aData[10]);
                if (d > 0) { $(nRow).addClass('success'); }
                else if (d < 0) { $(nRow).addClass('danger'); }
                $('td:eq(10)', nRow).css('font-weight', 'bold');
            }
        });
        $.fn.dataTableExt.afnFiltering.push(function (settings, row) {
            if (settings.nTable.id !== '<?= $tbl_id; ?>' || !$('#<?= $tbl_id; ?>_only_diff').is(':checked')) { return true; }
            return parseFloat(row[10]) !== 0;
        });
        $('#<?= $tbl_id; ?>_only_diff').on('change', function () { tbl.fnDraw(); });
    });
</script>
