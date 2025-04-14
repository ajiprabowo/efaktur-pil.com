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
    
	if(count($callingFrom) == 2){
        $pathdatasource = '../../'; //remote: root
		$myrenamepath = '../pdf/';		 
		 
    }else{
        $pathdatasource = '../../'; //remote: subdirectory
		 $myrenamepath = '../pdf/';
        
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
		$myrenamepath = '../pdf/';
		$pathparser = '../';
    }else{
        $pathdatasource = '../../'; //local: root
        
    }
	
	require_once($pathdatasource.'includes.efaktur-pil.com/server_local_setting.php');
	$table = local_db['tablename_dev'];
}

require_once $pathdatasource.'includes.efaktur-pil.com/DataSource.php';
$db = new DataSource();
$conn = $db->getConnection();
require_once ('vendor/autoload.php');

include 'alt_autoload.php-dist';

$num_renamed_files = 0;
$num_failed_renamed = 0;

if (isset($_POST["renameall"])) {

	//filenaming format
	//[nomor faktur]-[npwp customer].pdf

	//WARNING!! DIff between views
	//$mypath = dirname(__DIR__);
	//$mypath = './pdf/';	

	$files = scandir($myrenamepath);

	foreach($files as $key=>$name){
		$oldName = $name;

		//remove spaces and (
		//$name = str_replace(' ', '', trim($name));
		$name = trim($name);
		$name = preg_split("/([\(\s:])/", $name);

		if(substr($name[0], -3) != "pdf")
			$newName = substr($name[0], -34).'.pdf';
		else
			$newName = substr($name[0], -38);
				
		//exclude .httaccess, .ftpquota, .., directory,.. already renamed files (38 Chars length)	
		if(substr($newName, 0, 1) === '.' || is_dir($myrenamepath.$oldName) || strlen($oldName) == 38 || strlen($newName) != 38 ){}else{
			
			if(@rename("$myrenamepath/$oldName","$myrenamepath/$newName")===true){
				++$num_renamed_files;
			}else{
				++$num_failed_renamed;
			}
			
		}

  }

   if($num_renamed_files > 0){
		$type1 = "success";
		$message1 = $num_renamed_files." PDF Files have been renamed!";
	}else{
		$type1 = "error";
		$message1 = "Problem in Renaming pdf files";
	}

}else if (isset($_POST["rename_resourceHungry"])) {

	//filenaming format
	//[nomor faktur]-[npwp customer].pdf

	//WARNING!! DIff between views
	//$mypath = dirname(__DIR__);
	//$mypath = './pdf/';	

	require_once ('vendor/autoload.php');

	$files = scandir($myrenamepath);

	foreach($files as $key=>$name){
		$oldName = $name;		

		//exclude .httaccess, .ftpquota, .., directory,.. already renamed files (38 Chars length)	
		if(substr($oldName, 0, 1) === '.' || is_dir($myrenamepath.$oldName) || strlen($oldName) == 38 || strlen($newName) != 38) {}else{

			//Troubleshooting: Uncaught Exception: Unable to find startxref alt_autoload.php-dist 
			// https://stackoverflow.com/questions/32271078/fpdf-error-unable-to-find-startxref-keyword
			//echo $myrenamepath.$oldName;
			//exit;
			
			// Parse PDF file and build necessary objects.
			$parser = new \Smalot\PdfParser\Parser();
			$pdf = $parser->parseFile($myrenamepath.$oldName);

			$text = $pdf->getText();

			$myArray = explode('Nomor Seri Faktur Pajak:', $text);
			$faktur_no = substr($myArray[1],1,17);
			$myArray = explode('NPWP :', $myArray[1]);
			$npwp_no = substr($myArray[2],1,16);

			$newName = $faktur_no.'-'.$npwp_no.'.pdf';

			if(@rename("$myrenamepath/$oldName","$myrenamepath/$newName")===true){
				++$num_renamed_files;
			}else{
				++$num_failed_renamed;
			}
		}

  }

	if($num_renamed_files > 0){
		$type_maintenance = "success";
		$message_maintenance = $num_renamed_files." PDF Files have been renamed!";
	}else{
		$type_maintenance = "error";
		$message_maintenance = "Problem in Renaming pdf files";
	}

}else if (isset($_POST["check_existpdf"])) {

		$files = scandir($myrenamepath);

		$faktur_array = array();
		foreach($files as $key=>$name){
			
			//exclude .httaccess, .ftpquota, .., directory ..			
			if(substr($name, 0, 1) === '.' || is_dir($myrenamepath.$name)){}else{
				$faktur_no = substr($name,1,16);

				array_push($faktur_array, $faktur_no);
			}
		}
	
		$updateQuery = 'UPDATE `'.$table.'` SET is_pdf_exist=true WHERE nomor_faktur_pajak IN (' . implode(',', $faktur_array) . ')';
		$squery = mysqli_query($conn, $updateQuery);

			if ($squery) {
				$type_maintenance = "success";
				$message_maintenance = "Records updated: ".mysqli_affected_rows($conn)."</br />";
			} else {
			  $type_maintenance = "error";
			  $message_maintenance = "Problem in Updating Data";
			}
	
		$selectQuery = "select * from `".$table."` WHERE is_pdf_exist=false ORDER BY nama_pembeli";
		$squery = mysqli_query($conn, $selectQuery);
		
		date_default_timezone_set('Asia/Jakarta');
	
		$file = "missing_pdfs.txt";
		$txt = fopen($file, "w") or die("Unable to open file!");
		
		fwrite($txt, "Missing PDF Files: ".mysqli_affected_rows($conn)." files".PHP_EOL);
		fwrite($txt, "----------------------".PHP_EOL);
		fwrite($txt, "Per ".date("l, d-m-Y h:i:sa").PHP_EOL);
	
		$current_pembeli = null;
		$counter = 0;
		if (! empty($squery)) {
			foreach ($squery as $row){
				if($current_pembeli != $row['nama_pembeli']){
					$current_pembeli = $row['nama_pembeli'];				
					fwrite($txt, PHP_EOL.'Nama Customer: '.$row['nama_pembeli'].PHP_EOL);
				}
				
				fwrite($txt, ++$counter.'. '.$row['nomor_faktur_pajak'].'-'.$row['identitas_pembeli'].'.pdf Invoice:  '.$row['Referensi'].PHP_EOL);
			}
			
			fclose($txt);

			header('Content-Description: File Transfer');
			header('Content-Disposition: attachment; filename='.$file);
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			header("Content-Type: text/plain");
			
			//preventing html page being outputted in the downloaded file
			ob_clean();
			flush();
			
			readfile($file);
			exit;
		}
}
?>