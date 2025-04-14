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

    $searchcontent = "";

    $type_search = "";
    $message_search = "";


    // If submit button is clicked
    if (isset($_POST['search']))
    {

        //Preventing SQL injection: https://developer.okta.com/blog/2020/06/15/sql-injection-in-php
        //https://www.acunetix.com/blog/articles/exploiting-sql-injection-example/
        $referensi = mysqli_real_escape_string($conn, trim($_POST['noinv202503']));
        $identitas_pembeli = mysqli_real_escape_string($conn, trim($_POST['npwplt202503']));

        $keywords = "";
        $select = "";
																
										
        if(!empty($referensi) && !empty($identitas_pembeli)){
            $keywords .= "No NPWP: ".$identitas_pembeli."<br />";
            $keywords .= "No Invoice: ".$referensi."<br />";
            
            //$select = "identitas_pembeli = '$identitas_pembeli'";
            //$select .= " AND Referensi = '$referensi'";
            //$selectQuery = "select * from `".$table."` WHERE ".$select;
                        
            $selectQuery = "select * from `".$table."` WHERE identitas_pembeli = ? AND Referensi = ?";
            
            //$squery = mysqli_query($conn, $selectQuery);
            //$num_rows_select = mysqli_num_rows($squery);
            
            $paramType = "ss";
            $paramArray = array(
                $identitas_pembeli,
                $referensi
            );
			
            $rows = $db->select($selectQuery, $paramType, $paramArray);
        $num_rows_select = $db->getRecordCount($selectQuery, $paramType, $paramArray);            

        $searchcontent .='
        </div>
        ';

        if($num_rows_select){

            $type_search = "success";
            $message_search = $num_rows_select." record/s found!";

            $searchcontent .= '<div class="alert alert-primary alert-dismissible fade show" role="alert">
            '.$message_search.'
            
  </div>
  <div class="card-body">
        ';
                foreach($rows as $result){
                    $filename = $result['nomor_faktur_pajak'].'-'.$result['identitas_pembeli'].'.pdf';

                    $searchcontent .='<div Class="input-row">
                            Nama Customer: '.$result['nama_pembeli'].'<br />
                            NPWP Company Number: '.$result['identitas_pembeli'].'<br />
                            Invoice Number: '.$result['Referensi'].'<br />
                            Faktur Number: '.$result['nomor_faktur_pajak'].'<br /> 
                            Download Efaktur: <br />
                            <a href="'.$mysearchpath.$filename.'"  target="_blank" rel="noopener noreferrer">'.$result['Referensi'].'-'.$filename.'</a>		
                            <hr />
                        </div>';

                }

                $searchcontent .=	'
            </div>';
        }else{

            $type_search = "error";
            $message_search = $num_rows_select." record/s found!";

        $searchcontent .= '<div class="alert alert-warning alert-dismissible fade show" role="alert">
        '.$message_search.'
        
</div> 
<div class="card-body">
    ';

            $searchcontent .= '<div class="alert alert-info" role="alert">File yg anda cari tidak tersedia pastikan anda sudah benar memasukan npwp & nomor invoice</div> 
            </div>';
        }
                //echo $searchcontent;
                //sexit();

        $type_search = "success";
        $message_search = $num_rows_select." record/s found!";

    }else{
        $type_search ="error";
        $message_search = "Missing Required Fields";
    }
    }
?>