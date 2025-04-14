<!DOCTYPE html>

<?php
session_start();

$server_name = $_SERVER['SERVER_NAME'];
        
$callingFrom = $_SERVER['PHP_SELF'];
$callingFrom = explode("/", $callingFrom);
$pos = $callingFrom[1];

$webtitle = "ADMIN Tools";

if(strpos($pos, 'efaktur-pil.com') === false){
    //remote  

    if(count($callingFrom) == 2){
        $pathdatasource = '../'; //remote: root
        $mysearchpath = '../pdf/';
			 
    }else{
        $pathdatasource = '../../'; //remote: subdirectory
        $mysearchpath = '../pdf/';
		 
		$path2 = '../';
		$mypdftraversepath = '..\\pdf\\';
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
}else if($_SESSION['roles'] == 'finance'){
	header("Location: ../view-finance");
    exit(); // Terminate script execution after the redirect
}/*
 * Changelog:
 * search pdf using npwp dan invoice 
 * upload excel file, parse to insert to mysql database
 * midware to rename all pdf file in remote storage
 */

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

/* deleting records based on years
 * 
 */

require_once($path2.'deleting_mechanism.php');
	
/* search engine
 * 
 */
//require_once($path2.'new_search_engine.php');
require_once($path2.'header.php');
?>		
		<main>
			<div class=" container">
								
			<h1>Selamat datang di halaman TOOLS for ADMIN, <?php echo $_SESSION['username']; ?>!</h1>
				
				<a href="<?php echo $path2?>view-finance/">View: FINANCE</a> | <a href="<?php echo $path2?>" >View: Customer</a> | <a href="<?php echo $path2?>logout.php">Log Me Out!</a>
				
				<hr />

				<div class="row">
					<div class="bg-body-tertiary p-2 text-whites col-lg-6 col-md-6 col-12 rounded">

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
									echo '<div class="p-3">'.$message.'</div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
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
										<button type="submit" class="btn btn-primary" id="import" name="import">Import
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
							echo '<div style="padding:10px">'.$message1.'</div>
										<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
								</div>';
						} ?>		
												
						
						<h3>List of Files in PDF Folder</h3>

						<?php
						//windows path \
						// while unix path /

						//WARNING!! DIff between views
						//$mypath = dirname(__DIR__);
						//$mypath = './pdf/';

						printf("There are %d Files", count_dir_files($pathtopdf));
						$items = scandir( $mypdftraversepath2 );

						?>

						<div style="height: 400px; overflow-y: scroll;" class="border rounded">
						<?php					

						// GET THE BLOCKS STARTED, FALSE TO INDICATE MAIN FOLDER

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
						<div class="card">
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
											PDF Terkait: <a href="<?php echo $mypdftraversepath.$filename; ?>"><?php echo $row['Referensi'].'-'.$filename; ?></a><br />
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

						<hr />

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

							<form method="post" name="openftpform" id="openftpform" enctype="multipart/form-data">

							<div class="row g-3">
								<div class="col-auto">
									<button type="submit" class="btn btn-outline-warning" id="openftp" name="openftp">Open Default FTP Program</button>
								</div>

							</div>
							</form>

						<hr />
						<div class="bg-secondary bg-gradient p-3 rounded text-white">
							
									<h4>for Data MAINTENANCE:</h4>
								
							
							<form method="post"
							name="maintenancedel" id="maintenancedel"
							enctype="multipart/form-data">
								
								<?php if(!empty($message_maintenance)) {
										if($type_maintenance == "success"){
											echo '<div id="responseMaintenance" class="alert alert-primary alert-dismissible fade show" role="alert">';		
										}else{
											echo '<div id="responseMaintenance" class="alert alert-danger alert-dismissible fade show" role="alert">';
										}	
										
										echo '<div style="padding:10px">'.$message_maintenance.'</div>
										<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
								</div>';
								} ?>	
																
								
								<div class="row">
										    <label for="tahun" class="col-sm-2 col-form-label">Tahun</label>
    								<div class="col">

										<select class="form-select" id="tahun" name="tahun">

								<?php
										$sqlSelect = "SELECT DISTINCT tahun FROM `".$table."`";
										$result = $db->select($sqlSelect);

										foreach ($result as $row) {
											echo "<option value=" .$row['tahun']. ">" .$row['tahun']. "</option>";
								?>
								<?php
										}
								?>
										</select>
									</div>
								</div>
								<div class="row g-3 mt-0">

									<div class="col">
										<button type="submit" class="btn btn-primary" id="checkbutton" name="checkbutton">Periksa Jumlah Data</button>
									</div>
									<div class="col">
										<button type="submit" class="btn btn-warning" id="deletebutton" name="deletebutton">Hapus</button>
									</div>
									<div class="col">
										<button type="submit" id="submit" name="rename_resourceHungry" class="btn btn-danger">Rename Special (WARNING!! Resource Hungry)</button>
									</div>
									<div class="col">
										<button type="submit" id="check_existpdf" name="check_existpdf" class="btn btn-success">pdf exist checking by rows of records</button>
									</div>
								</div>
							</form>
														
						</div>
					</div>		
				</div>
			</div>

			<div class="container" style="margin-top:10px; margin-bottom: 10px">
				<div class="row">
					<div class="col-lg-5 col-md-6 col-12" style="padding:10px; margin-bottom:15px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);">
						<h3>Retrieved Documents</h3>

						<form name="searchform" id="searchform" method="POST" class="<?php if (isset($_POST['search'])){?> was-validated<?php } ?>">

							<div class="row mb-3">
								<label for="npwplt202503" class="form-label">NPWP Company Number*</label>
								    <div class="cols">

										<input id="npwplt202503" class="form-control form-control-lg <?php if(!empty($_POST['npwplt202503'])){?>is_valid<?php }?>" type="text" placeholder="Nomor NPWP Perusahaan" name="npwplt202503" value="<?php if(!empty($_POST['npwplt202503'])) echo $_POST['npwplt202503'];?>" required >
										<div class="invalid-feedback">
										  Silakan terlebih dahulu isi Nomor NPWP Perusahaannya
										</div>
										<div class="valid-feedback">
										  Looks good!
										</div>
								</div>
							
							</div>
							<div class="row mb-3">							
								<label for="noinv202503" class="form-label">Invoice Number*</label>
								<div class="col">
									<input id="noinv202503" class="form-control form-control-lg <?php if(!empty($_POST['noinv202503'])){?>is_valid<?php }?>" type="text" placeholder="Nomor Invoice" name="noinv202503" value="<?php if(!empty($_POST['noinv202503'])) echo $_POST['noinv202503'];?>" required>
									<div class="invalid-feedback">
									  Silakan terlebih dahulu isi Nomor Invoice nya
									</div>
									<div class="valid-feedback">
									  Looks good!
									</div>
								</div>
							</div>	
							<div class="form-group">

								<button type="submit" class="btn btn-primary" name="search" id="search" value="Submit">Retrieve Documents</button>
								
    										
								<span class="text-danger" style="float:right">*required (wajib diisi)</span>
							</div>
						</form>
					</div>
					<div class="col-lg-7 col-md-6 col-12">
						<div class="card">
							<div class="card-header text-center">
								<h4>Search result for e-Faktur</h4>	
							</div>
							<div id="spinner-div" class="spinner-border text-primary" role="status">
							  <span class="visually-hidden">Loading...</span>
							</div>
									
							<div id="searchresult">
																	
							</div>	
							
						</div>
					</div>
				</div>
			</div>
		</main>
	<script>
						
	//JSON.parse: unexpected character at line 1 column 1 of the JSON data
	//https://community.esri.com/t5/web-appbuilder-custom-widgets-questions/json-parse-unexpected-character-at-line-1-column-1/m-p/855221#M11305

	// Variable to hold request
		var request;

		$(document).ready(function() {
			
			// Bind to the submit event of our form
			//$("#searchform").submit(function(event){
			$("#searchform").submit(function(event) {

				var npwplt202503 = $('#npwplt202503').val();
				var noinv202503 = $('#noinv202503').val();

				// Prevent default posting of form - put here to work in case of errors
				event.preventDefault();

				// Abort any pending request
				if (request) {
					request.abort();
				}		

				if(noinv202503 && npwplt202503){				

					$.ajax({
					  type: "POST",
						url: '<?php echo $path2;?>new_search_engine_ajax.php',
						/*async: false,*/
						data: {noinv202503: noinv202503, npwplt202503: npwplt202503, format: 'html' },					
						beforeSend: function () {
						// ... your initialization code here (so show loader) ...
							$('#spinner-div').show();//Load button clicked show spinner
							$('#search').html('<span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>Loading...');
							$("body").css({"opacity": "0.5"});
						},
						success: function(data){					   

						   $("#searchresult").html(data);

						   console.log(data);

						  $.ajax({
							  type: "POST",
							   url: '<?php echo $path2;?>new_search_engine_ajax.php',						   
							   data: {noinv202503: noinv202503, npwplt202503: npwplt202503, format: 'json' },
							   success: function(data){					   

								  var result = $.parseJSON(data);
								  console.log(result);					   

								  return true;

							   },
							   complete: function() {},
							   error: function(xhr, textStatus, errorThrown) {
								 console.log('ajax loading error...');
								 return false;
							   }
							});

						  return true;
					   },
					   complete: function() {
						   $('#spinner-div').hide();//Request is complete so hide spinner
							$('#search').html('Retrieve Documents');	
						   $("body").css({"opacity": "1"});
					   },
					   error: function(xhr, textStatus, errorThrown) {
						 console.log('ajax loading error...');
						 return false;
					   }
					});
				}else{ 
					if(!noinv202503)
						$( "#noinv202503" ).addClass( "is-invalid" );
					else
						$( "#noinv202503" ).addClass( "is-valid" );	
					if(!npwplt202503)
						$( "#npwplt202503" ).addClass( "is-invalid" );
					else
						$( "#npwplt202503" ).addClass( "is-valid" );
				}
			});		

		});
	

	
	
</script>

	<?php require_once($path2.'footer.php');?>
		