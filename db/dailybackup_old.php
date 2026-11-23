<?php
//echo  "1";die();
$filenuse = 'F:\\dailybackup\\lw_kmahastore_'.date('YmdHis').'.sql';
exec('D:\\wamp64\\bin\\mysql\\mysql5.7.19\\bin\\mysqldump.exe -uroot lw_smbranch_kmaha> '.$filenuse);

$zipFilePath="{$filenuse}.zip";

if(file_exists($filenuse)) {

	$zip = new ZipArchive();
	if ( $zip->open( $zipFilePath, ZIPARCHIVE::CREATE ) != true ) {
		die ( "Could not open archive" );
	}
	$zip->addFromString(basename($filenuse),file_get_contents($filenuse));

// close and save archive

	$zip->close();
	unlink( $filenuse );
}
else
	die ( "File in use path is not found" );
?>
<script language="JavaScript1.5" type="text/javascript">

    window.close();
</script>
