<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('approve_stock_take'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator1', 'role' => 'form','id'=>'approveFrm');
        echo admin_form_open("stocktake/approve", $attrib); ?>
         <div class="modal-body">
        
       
            <p><?= lang('enter_info'); ?></p>
            <input type="hidden" name="id" value="<?= $item->id; ?>">
            <input type="hidden" id="prod_qty" name="prod_qty" value="<?= $product_quantity; ?>">

          
            <div class="row">
                <div class="col-sm-4">
                 <label for="product_quantity"><?php echo $this->lang->line("product_quantity"); ?>
                <?php echo form_input('product_quantity',$product_quantity, 'class="form-control"  id="product_quantity"  disabled="disabled" '); ?>
            </div>
            <div class="col-sm-4">
                   <?php
                        $oprArr = array('Plus' =>'Plus' ,'Minus' =>'Minus' );
                        $u[''] = "";
                        $selOpr="Plus";

                        $diff=($item->store_stock-$item->pos_stock);
                        if($diff<0)
                        {
                             $selOpr="Minus";
                             $diff=$diff*-1;
                        }

                        ?>
            <div class="form-group">
                <label for="stock_qty"><?php echo $this->lang->line("Difference"); ?></label>
                <?php echo form_input('stock_qty',$diff, 'class="form-control"   id="stock_qty"   pattern="^(?:[1-9]\d*|0)?(?:\.\d+)?$" required="required" data-bv-regexp-message="not in correct format" title="only numbers allowed eg 10,10.00,0.10"'); ?>
            </div>
        </div>


            <div class="col-sm-4">
                 <div class="form-group">
                        <?= lang("Operation", "Operation") ?>

                       
                       <?php
                        echo form_dropdown('operation', $oprArr,$selOpr, 'class="form-control select" id="operation" placeholder="' . lang("select") . " " . lang("status") . '"  style="width:100%"')
                        ?>
                    </div>
                </div>
            </div>

       
        <div class="modal-footer">
            <?php echo form_submit('approve_stock_take', lang('approve_stock_take'), 'class="btn btn-primary" id="btnApprove"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
</div>
<?= $modal_js ?>

<script type="text/javascript">

    $(document).ready(
        function(e){

                $("#approveFrm").submit(function(e){
   console.log(e);
   var prod_qty=parseFloat($("#prod_qty").val().trim());
   var diff=parseFloat($("#stock_qty").val().trim());
   var operationType=$("#operation").val().trim();
   
   if(operationType=='Minus' &&   (diff>prod_qty) )
   {
    alert("Difference can't be greater than product quantity");
   //$("#approveFrm").data('bootstrapValidator').resetForm();
     e.stopPropagation();
    e.preventDefault();

    console.log(e.isDefaultPrevented());
    //return false;
 
   }
   }); 



        }

        );

</script>