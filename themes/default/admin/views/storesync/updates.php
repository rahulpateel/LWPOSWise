<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-upload"></i><?= lang('Download_Updates'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"></p>

                <div class="row">
                    <div class="col-md-12">

                        <p>Last updates downloaded details</p>
                        <p><label>Last Downloaded Date Time : <?php echo date('d-m-Y H:i:s',strtotime($lastdownload->downloaded_at))?></label></p>
                        <p><label>Last Downloaded By : <?php echo $lastdownload->user?></label></p>
                        <?php

                            echo admin_form_open('store_sync/download_updates');
                        ?>

                            <div class="form-group">
                                <?= form_submit('download', lang('download'), 'class="btn btn-primary"'); ?>
                            </div>
                        <?php
                            echo form_close();


                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>