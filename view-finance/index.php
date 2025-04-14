<!DOCTYPE html>

<?php
session_start();
 
$server_name = $_SERVER['SERVER_NAME'];
        
$callingFrom = $_SERVER['PHP_SELF'];
$callingFrom = explode("/", $callingFrom);
$pos = $callingFrom[1];

$webtitle = "FINANCE Tools";

if(strpos($pos, 'efaktur-pil.com') === false){
    //remote  

    if(count($callingFrom) == 2){
        $pathdatasource = '../../'; //remote: root
        $mysearchpath = '../pdf/';
			 
    }else{
        $pathdatasource = '../../'; //remote: subdirectory
        $mysearchpath = '../pdf/';
		 
		$path2 = '../';
		$mypdftraversepath = '..\\pdf';
		$mypdftraversepath2 = '../pdf';
		$pathtopdf = '/pdf/';
		$myuploadpath = './uploads/'; 
        
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
		
		$path2 = '../';
		$mypdftraversepath = '..\\pdf\\';
		$mypdftraversepath2 = '..\\pdf';
		$pathtopdf = '/pdf/';
		$myuploadpath = './uploads/';
    }else{
        $pathdatasource = '../../'; //local: root
        $mysearchpath = './pdf/';
        
    }
	
	require_once($pathdatasource.'includes.efaktur-pil.com/server_local_setting.php');
	$table = local_db['tablename_dev'];
}


if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit(); // Terminate script execution after the redirect
}

/*
 * VIEW: Finance
 * Changelog:
 * search pdf using npwp dan invoice 
 * upload excel file, parse to insert to mysql database
 * midware to rename all pdf file in remote storage
 */

require_once($pathdatasource.'includes.efaktur-pil.com/server_local_setting.php');

/* Note:
 * Tested and worked only on PHP Version 7.4
 */
require_once($path2.'excel_parser.php');

/* Renaming Engine
 * 
 */

require_once($path2.'renaming_engine.php');

/* listing pdf files
 * 
 */
 
require_once($path2.'listing_files_folders.php');
require_once($path2.'header.php');
?>		
		<main>
			<div class=" container rounded">
				
				
				
			<h1>Selamat datang di halaman Tools for FINANCE, <?php echo $_SESSION['username']; ?>!</h1>
				<?php if($_SESSION['roles'] == 'admin'){?>
					<a href="<?php echo $path2?>admin/" >View: ADMIN</a> |
				<?php } ?>
				 <a href="<?php echo $path2?>" >View: CUSTOMER</a> | <a href="<?php echo $path2?>logout.php">Log Me Out!</a>
				
				<hr />
				
				<div class="row mb-3">
					<div class="text-bg-light col-lg-6 col-md-6 col-12">

						<h3>Import Excel File into MySQL Database</h3>
						
						<form class="form-horizontal" method="post"
							name="frmExcelImport" id="frmExcelImport"
							enctype="multipart/form-data" onsubmit="return validateFile()">


							<?php if(!empty($message)) {
									if($type == "success"){
										echo '<div id="response" class="alert alert-primary alert-dismissible fade show" role="alert">';		
									}else{
										echo '<div id="response" class="alert alert-danger alert-dismissible fade show" role="alert">';
									}	
									
									echo '<div style="padding:10px">'.$message.'</div>
										  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

								</div>';
							} ?>										

							<div Class="input-row">
								<label>Choose your file. <a href="<?php echo $path2;?>Template/import-template.xlsx"
									download>Download excel template</a></label>
								<div>
									<input type="file" name="file" id="file" class="file"
										accept=".xls,.xlsx">
								</div>
								<div class="row g-3">

									<div class="col">
										<button type="submit" class="btn btn-primary" id="checkbutton" name="import">Import
										Excel and Save Data</button>
									</div>
									<div class="col">
										<button type="submit" class="btn btn-warning" id="renameall" name="renameall">Rename All Submitted PDFs</button>
									</div>
								</div>
							</div>
						</form>

						<hr />

						<?php if(!empty($message1)) {
								if($type1 == "success"){
									echo '<div id="responsemidware" class="alert alert-primary alert-dismissible fade show" role="alert">';			
								}else{
									echo '<div id="responsemidware" class="alert alert-danger alert-dismissible fade show" role="alert">';
								}	
								
								echo '<div class="p-3">'.$message1.'</div>
										<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
								</div>';
							} ?>
									
						
						
						<h3>List of Files in PDF Folder</h3>

						<?php
						//windows path \
						// while unix path /

						//WARNING!! DIff between views
						//$mypath = dirname(__DIR__);
						//$mypath = '..\\pdf\\';

							printf("There are %d Files", count_dir_files($pathtopdf));
						$items = scandir( $mypdftraversepath2 );

						?>

						<div style="height: 100px; overflow-y: scroll;" class="border rounded">
						<?php					

						// GET THE BLOCKS STARTED, FALSE TO INDICATE MAIN FOLDERs

						//echo display_block($mypath);				
						build_blocks( $items, $mypdftraversepath2 );
						?>

						<?php if($toggle_sub_folders) { ?>
						<script type="text/javascript">
							$(document).ready(function() 
							{
								$("a.dir").click(function(e)
								{
									$(this).toggleClass('open');
									$('.sub[data-folder="' + $(this).attr('href') + '"]').slideToggle();
									e.preventDefault();
								});
							});
						</script>
						<?php } ?>
						</div>
					</div>

					<div class="col-lg-6 col-md-6 col-12">
						<div class="card" style="margin-bottom:10px">
							<div class="card-header text-center">
								  
								<figure>
								<blockquote class="blockquote">
									<h4>Latest 2 Records in Excel Database (SQL)</h4>
								</blockquote>
							<?php

									$sqlSelect = "SELECT * FROM `".$table."`";
									$result = $db->getRecordCount($sqlSelect);
							?>
								  <figcaption class="blockquote-footer">
									  	<?php echo $result;?> rows recorded 
									</figcaption>
								</figure>
							</div>
							<div class="card-body">
								<div style="height: 170px; overflow-y: scroll;">

									<?php

									$sqlSelect = "SELECT * FROM `".$table."`";
									$result = $db->getRecordCount($sqlSelect);

									$sqlSelect = "SELECT * FROM `".$table."` ORDER BY id DESC LIMIT 2";
									$result = $db->select($sqlSelect);
									if (! empty($result)) {
										?>

								<?php
									foreach ($result as $row) { // ($row = mysqli_fetch_array($result))
										$filename = $row['nomor_faktur_pajak'].'-'.$row['identitas_pembeli'].'.pdf';
										?>
										<div Class="input-row">
											ID: <?php  echo $row['id']; ?><br />
											Nama Pembeli: <?php  echo $row['nama_pembeli']; ?><br />
											NPWP Company Number: <?php  echo $row['identitas_pembeli']; ?><br />
											Nomor Faktur Pajak: <?php  echo $row['nomor_faktur_pajak']; ?><br />
											Nomor Invoice: <?php  echo $row['Referensi']; ?><br />
											PDF Terkait: <a href="<?php echo $mypdftraversepath.$filename; ?>" target="_blank" rel="noopener noreferrer"><?php echo $row['Referensi'].'-'.$filename; ?></a><br />
											File Import terkait:<br />
											<span class="text-danger"><?php echo $row['filename_import_ref']; ?></span>
											<hr />

										</div>
								<?php
									}
									?>

								<?php
								}
								?>
								</div>	
							</div>
						</div>

						<figure>						  
							  <blockquote class="blockquote"><p>Next Step:</p></blockquote>
							 	<figcaption class="blockquote-footer">
									Upload the pdf files of what are listed in the uploaded excel</figcaption> 
						</figure>

						<?php
							if (isset($_POST["openftp"])) {

								//$output=null;
								//$retval=null;
								//$WshShell = new COM(c);
								//$oExec = $WshShell->Run("\"C:\Program Files (x86)\WinSCP\winSCP.exe\"", 3, true); 
								exec("\"C:\Program Files (x86)\WinSCP\winSCP.exe\"");

							}
						?>

							<form action="#" method="post" name="openftpform" id="openftpform" enctype="multipart/form-data">

							<?php if(!empty($type_pdf)) {
									echo '<div id="responsePDF" class="'.$type_pdf.' px-2" style="margin-bottom:10px">';
								}else{
									echo '<div id="responsePDF" class="px-2" style="margin-bottom:10px">';
								}
								
								if(!empty($message_pdf)) { 
									echo $message_pdf; 
								} 
								
								echo '</div>';
							?>

							<div class="row g-3">


								<div class="col-auto">
									<button type="submit" class="btn btn-outline-warning" id="openftp" name="openftp">Open Default FTP Program</button>
								</div>

							</div>
							</form>
						</div>
					</div>
			</div>
		</main>

	<?php require_once($path2.'footer.php');?>