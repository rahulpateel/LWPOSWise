<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {


		$("#salesearch").click(
		   function(){
		   $("#SLData tbody").empty();
		   
		   var term=$("#reference_no").val();
		   
		   if(term!="")
		   {
			   $.ajax({
                type: "get",
                url: site.base_url + "purchases/searchPurchase",
                data: { q: term },
                success: function (res) {
					console.log(res);
                     var items=JSON.parse(res);
					 $.each(items, function () {
						 
						 //console.log(this);
						  var newTr = $('<tr class="purchase_link" id="'+ this.id +'" data-item-id="' + this.id + '"></tr>');
						  var tr_html = "<td>"+fld(this.date)+"</td>";
						  tr_html +="<td>" +this.reference_no+"</td>";
						 
						  tr_html +="<td>" +this.supplier+"</td>";
						  tr_html +="<td>" +row_status(this.status)+"</td>";
						  tr_html +="<td>" +currencyFormat(this.grand_total)+"</td>";
						  tr_html +="<td>" +currencyFormat(this.paid)+"</td>";
						  tr_html +="<td>" +currencyFormat(this.balance)+"</td>";
						  tr_html +="<td>" +row_status(this.payment_status)+"</td>";
						   tr_html +="<td><a target='_blank' href='purchases/edit_old_order/"+this.id+"'>Edit</a></td>";
						   newTr.html(tr_html);
                           newTr.prependTo("#SLData");
					 });
                    
                }
            });
		   }
		   
		   }
		
		);
  

  

    });

</script>


<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-heart"></i><?=lang('Search_purchase') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')';?>
        </h2>

        <div class="box-icon">
        
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

               				
				 <div id="form">

                   
                    <div class="row">
                        
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>

                            </div>
                        </div>
						<div class="col-sm-4" >
						 <div class="form-group">
                        <div style="margin-top:29px"
                            class="controls"><input type="button" class="btn btn-primary"  id="salesearch" value="Search"/></div>
                    </div>
					</div>
                     
                    </div>
                   
                  

                </div>

                <div class="table-responsive">
                    <table id="SLData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                           
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("supplier"); ?></th>
                            <th><?= lang("purchase_status"); ?></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th><?= lang("payment_status"); ?></th>
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="9" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            
                            <th></th><th></th><th></th><th></th><th></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                                                       <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

