<?php

/* Note:
 * Tested and worked only on PHP Version 7.4
 */

use Phppot\DataSource;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$callingFrom = $_SERVER['PHP_SELF'];
$callingFrom = explode("/", $callingFrom);

//local
$pos = $callingFrom[1];

$server_name = $_SERVER['SERVER_NAME'];

if(strpos($pos, 'efaktur-pil.com') === false){ //related to local settings
    //remote  
    
	if(count($callingFrom) == 2){
        
        $mysearchpath = '../pdf/';
         $pathdatasource = '../../'; //remote: root
        $myuploadpath = '../uploads/';
    }else{
        $pathdatasource = '../../'; //remote: subdirectory
        $mysearchpath = '../pdf/';
        $myuploadpath = '../uploads/';
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
        $myuploadpath = '../uploads/';
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


$type = "";
$message = "";

//WARNING!! DIff between views
//$mypath = dirname(__DIR__);
//$myuploadpath = './uploads/';

if (isset($_POST["import"])) {
    
    $allowedFileType = [
        'application/vnd.ms-excel',
        'text/xls',
        'text/xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];
       
    if (in_array($_FILES["file"]["type"], $allowedFileType)) {

        $targetPath = $myuploadpath . $_FILES['file']['name'];
        @move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);

		$Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $spreadSheet = $Reader->load($targetPath);
        $excelSheet = $spreadSheet->getActiveSheet();
        $spreadSheetAry = $excelSheet->toArray();
        $sheetCount = count($spreadSheetAry);

        $numrows_without_head = 0;
        $numrows_without_head = $sheetCount - 1;
        
        $inserted_rows = 0;
        $failed_rows = 0;
        $failed_array = array();
        $filename = $_FILES['file']['name'];
                        
        for ($i = 1; $i <= $sheetCount; $i ++) {
            $identitas_pembeli = "";
            if (isset($spreadSheetAry[$i][0])) {
                $identitas_pembeli = mysqli_real_escape_string($conn, $spreadSheetAry[$i][0]);
            }
            $nama_pembeli = "";
            if (isset($spreadSheetAry[$i][1])) {
                $nama_pembeli = mysqli_real_escape_string($conn, $spreadSheetAry[$i][1]);
            }
			$kode_transaksi = "";
            if (isset($spreadSheetAry[$i][2])) {
                $kode_transaksi = mysqli_real_escape_string($conn, $spreadSheetAry[$i][2]);
            }
			$nomor_faktur_pajak = "";
            if (isset($spreadSheetAry[$i][3])) {
                $nomor_faktur_pajak = mysqli_real_escape_string($conn, $spreadSheetAry[$i][3]);
            }
			$tanggal_faktur_pajak = "";
            if (isset($spreadSheetAry[$i][4])) {
                $tanggal_faktur_pajak = mysqli_real_escape_string($conn, $spreadSheetAry[$i][4]);
            }
			$masa_pajak = "";
            if (isset($spreadSheetAry[$i][5])) {
                $masa_pajak = mysqli_real_escape_string($conn, $spreadSheetAry[$i][5]);
            }
			$tahun = "";
            if (isset($spreadSheetAry[$i][6])) {
                $tahun = mysqli_real_escape_string($conn, $spreadSheetAry[$i][6]);
            }
			$status_faktur = "";
            if (isset($spreadSheetAry[$i][7])) {
                $status_faktur = mysqli_real_escape_string($conn, $spreadSheetAry[$i][7]);
            }
			$ESignStatus = "";
            if (isset($spreadSheetAry[$i][8])) {
                $ESignStatus = mysqli_real_escape_string($conn, $spreadSheetAry[$i][8]);
            }
			$harga_jual_penggantian_dpp = "";
            if (isset($spreadSheetAry[$i][9])) {
                $harga_jual_penggantian_dpp = mysqli_real_escape_string($conn, $spreadSheetAry[$i][9]);
            }
			$dpp_nilai_lain_dpp = "";
            if (isset($spreadSheetAry[$i][10])) {
                $dpp_nilai_lain_dpp = mysqli_real_escape_string($conn, $spreadSheetAry[$i][10]);
            }
			$PPN = "";
            if (isset($spreadSheetAry[$i][11])) {
                $PPN = mysqli_real_escape_string($conn, $spreadSheetAry[$i][11]);
            }
			$PPnBM = "";
			if (isset($spreadSheetAry[$i][12])) {
                $PPnBM = mysqli_real_escape_string($conn, $spreadSheetAry[$i][12]);
            }
			$Penandatangan = "";
			if (isset($spreadSheetAry[$i][13])) {
                $Penandatangan = mysqli_real_escape_string($conn, $spreadSheetAry[$i][13]);
            }
			$Referensi = "";
			if (isset($spreadSheetAry[$i][14])) {
                $Referensi = mysqli_real_escape_string($conn, $spreadSheetAry[$i][14]);
            }
			$dilaporkan_oleh_penjual = "";
			if (isset($spreadSheetAry[$i][15])) {
                $dilaporkan_oleh_penjual = mysqli_real_escape_string($conn, $spreadSheetAry[$i][15]);
            }
			$dilaporkan_oleh_pemungut_ppn = "";
			if (isset($spreadSheetAry[$i][16])) {
                $dilaporkan_oleh_pemungut_ppn = mysqli_real_escape_string($conn, $spreadSheetAry[$i][16]);
            }				

            if(!empty($nomor_faktur_pajak)){
                $query = "insert into `".$table."` (identitas_pembeli,nama_pembeli,kode_transaksi,nomor_faktur_pajak,tanggal_faktur_pajak,masa_pajak,tahun,status_faktur,ESignStatus,harga_jual_penggantian_dpp,dpp_nilai_lain_dpp,PPN,PPnBM,Penandatangan,Referensi,dilaporkan_oleh_penjual,dilaporkan_oleh_pemungut_ppn, filename_import_ref) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
               ON DUPLICATE KEY UPDATE
                identitas_pembeli = values(identitas_pembeli),
                nama_pembeli = values(nama_pembeli),
                kode_transaksi = values(kode_transaksi),
                nomor_faktur_pajak = values(nomor_faktur_pajak),
                tanggal_faktur_pajak = values(tanggal_faktur_pajak),
                masa_pajak = values(masa_pajak),
                tahun = values(tahun),
                status_faktur = values(status_faktur),
                ESignStatus = values(ESignStatus),
                harga_jual_penggantian_dpp = values(harga_jual_penggantian_dpp),
                dpp_nilai_lain_dpp = values(dpp_nilai_lain_dpp),
                PPN = values(PPN),
                PPnBM = values(PPnBM),
                Penandatangan = values(Penandatangan),
                Referensi = values(Referensi),
                dilaporkan_oleh_penjual = values(dilaporkan_oleh_penjual),
                dilaporkan_oleh_pemungut_ppn = values(dilaporkan_oleh_pemungut_ppn), 
                filename_import_ref = values(filename_import_ref)
                ";
                //$query = "insert into tbl_info(name,description) values(?,?)";
				
				$paramType = "sssssssssssssssiss";
                $paramArray = array(
                    $identitas_pembeli,
                    $nama_pembeli,
					$kode_transaksi,
					$nomor_faktur_pajak,
					$tanggal_faktur_pajak,
					$masa_pajak,
					$tahun,
					$status_faktur,
					$ESignStatus,
					$harga_jual_penggantian_dpp,
					$dpp_nilai_lain_dpp,
					$PPN,
					$PPnBM,
					$Penandatangan,
					$Referensi,
					$dilaporkan_oleh_penjual,
					$dilaporkan_oleh_pemungut_ppn,
                    $filename
                );
						              
                
                $insertId = $db->insert($query, $paramType, $paramArray);
                // $query = "insert into tbl_info(name,description) values('" . $name . "','" . $description . "')";
                // $result = mysqli_query($conn, $query);
                                
                if (! empty($insertId)) {
                    $inserted_rows++;
                } else {
                    $failed_rows++;
                    array_push($failed_array, $nomor_faktur_pajak);
                }
            }
            
        } 
        
        if (!empty($inserted_rows)) {
            $type = "success";
            $message .= $inserted_rows." records of Excel Data has been Imported (INSERT) into the Database<br />";
            if($failed_rows){
                $message .= $failed_rows." records of Excel Data has been Imported (UPDATE) into the Database<br />";
                $message .= print_r($failed_array, true);
            }
        } else if(!empty($failed_rows)) {
            $type = "success";
            $message .= $failed_rows." records of Excel Data has been Imported (UPDATE) into the Database<br />";
            $message .= print_r($failed_array, true);
        } else{
            $type = "error";
            $message = "Unknown Errors while attempting to Insert into Database<br />";
            
        }
       
    } else {
        $type = "error";
        $message = "Invalid File Type. Upload Excel File.";
    }
    
    
    
}
?>