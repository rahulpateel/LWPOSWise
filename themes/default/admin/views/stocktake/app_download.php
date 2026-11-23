<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
    <div class="modal-header">
       
        <h4 class="modal-title" id="myModalLabel">Download Stocktake App</h4>
    </div>
        <div class="modal-body">

            <div class="row">
				<div class="col-xs-7">
				<?= $this->sma->qrcode('link', urlencode(base_url('apk/stocktake.apk')), 2,'H'); ?>
				</div>
</div>
</div>
</div>
</div>
<style type="text/css">
	.qrimg{
		width: 200px!important;
	}
</style>