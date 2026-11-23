<?php
//echo  "1";die();
$filename = 'lw_nbmkarad_store_'.date('YmdHis').'.sql';
$filenuse = 'D:\\dailybkp\\'.$filename;
exec('D:\\wamp64\\bin\\mysql\\mysql5.6.17\\bin\\mysqldump.exe -uroot lwkaraddb> '.$filenuse);

$zipFilePath="{$filenuse}.zip";

if(file_exists($filenuse)) {

	$zip = new ZipArchive();
	if ( $zip->open( $zipFilePath, ZIPARCHIVE::CREATE ) != true ) {
		die ( "Could not open archive" );
	}
	$zip->addFromString(basename($filenuse),file_get_contents($filenuse));

// close and save archive

	$zip->close();

  
// Store the path of destination file
$pdPath = 'G:\\dailybkp\\'."{$filename}.zip"; 
  
// Copy the file from /user/desktop/geek.txt 
// to user/Downloads/geeksforgeeks.txt'
// directory
if( !copy($zipFilePath, $pdPath) ) { 
    die ( "File can't be copied! \n"); 
} 
else { 
    echo "File has been copied! \n"; 
} 
	
	unlink( $filenuse );
}
else
	die ( "File in use path is not found" );
?>
<script language="JavaScript1.5" type="text/javascript">

    window.close();
</script>
