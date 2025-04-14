<?php

use Phppot\DataSource;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$callingFrom = $_SERVER['PHP_SELF'];
$callingFrom = explode("/", $callingFrom);

//local
$pos = $callingFrom[1];

$server_name = $_SERVER['SERVER_NAME'];

if(strpos($pos, 'efaktur-pil.com') === false){
    //remote  
    
	if($server_name == "efaktur-pil.com")
	
    
     if(count($callingFrom) == 2){
        $pathdatasource = '../'; //remote: root
        $mysearchpath = '../pdf/';
    }else{
        $pathdatasource = '../../'; //remote: subdirectory
        $mysearchpath = '../pdf/';
        
    }
    
    require_once($pathdatasource.'includes.efaktur-pil.com/server_local_setting.php');
	if($server_name == "efaktur-pil.com") //LIVE
		$table = remote_db['tablename_live'];
	else //STAGING
		$table = remote_db['tablename_staging'];
    
}else{
    //local
    $pos = $callingFrom[2];
    
    if(strpos($pos, 'php') === false){
        $pathdatasource = '../../../'; //local: subdirectory
        $mysearchpath = '../pdf/';
    }else{
        $pathdatasource = '../../'; //local: root
        $mysearchpath = './pdf/';
        
    }
    
    require_once($pathdatasource.'includes.efaktur-pil.com/server_local_setting.php');
	$table = local_db['tablename_dev'];
}

require_once $pathdatasource.'includes.efaktur-pil.com/DataSource.php';
$db = new DataSource();
$conn = $db->getConnection();
require_once ('vendor/autoload.php');

$tahun = "";
if (isset($_POST["checkbutton"]) || isset($_POST["deletebutton"])){
	
	if(isset($_POST["tahun"])){
		$tahun = $_POST['tahun'];

		$num_rows_to_delete = 0;

		//WARNING!! DIff between views
		//$mypath = dirname(__DIR__);
		$mypath = __DIR__ . '\\pdf\\';

		if (isset($_POST["checkbutton"])) {

			$selectQuery = "select * from `".$table."` WHERE tahun='".$tahun."'";
			$squery = mysqli_query($conn, $selectQuery);

			$num_rows_to_delete = mysqli_num_rows($squery);

			$type_maintenance = "success";
			$message_maintenance = $num_rows_to_delete." records. Sure to be deleted? <br />";

		}else if (isset($_POST["deletebutton"])) {

			//Prepare File PDF deletion
			$selectQuery = "select nomor_faktur_pajak, identitas_pembeli from `".$table."` WHERE tahun='".$tahun."'";
			$squery = mysqli_query($conn, $selectQuery);

			$array = array();
			while (($result = mysqli_fetch_assoc($squery))) {
				$filename = $result['nomor_faktur_pajak'].'-'.$result['identitas_pembeli'].'.pdf';
				array_push($array, $filename);
			}

			$message_delete = "";
			$num_deleted_files = 0;

			$deletepath = "";

			foreach ($array as $file) {

				//Windows path \
				//unix path /

				$deletepath = $mypath.$file;

				if (file_exists($deletepath)) {
					unlink($deletepath);
					$num_deleted_files++;

				} else {
					// File not found.
				}		
			}

			if($num_deleted_files){
					$type_maintenance = "success";
					$message_maintenance = $num_deleted_files." deleted PDF file/s<br />";
			}else{
					$type_maintenance = "error";
					$message_maintenance = "<span class=\"text-danger\">Problem in deleting PDF file/s</span><br />";
			}

			$deleteQuery = "delete from `".$table."` WHERE tahun='".$tahun."'";

			$squery = mysqli_query($conn, $deleteQuery);

			if ($squery) {
				$type_maintenance = "success";
				$message_maintenance .= "Records deleted: ".mysqli_affected_rows($conn)."</br />";

			} else {
			  $type_maintenance = "error";
			  $message_maintenance .= "Problem in Deleting Data";
			}
		}
	}
}


?>