<!DOCTYPE html>

<?php
session_start();
/*
 * VIEW: Client
 * Changelog:
 * Search Data using NPWP No. and Invoice No.
 */

$server_name = $_SERVER['SERVER_NAME'];
        
$callingFrom = $_SERVER['PHP_SELF'];
$callingFrom = explode("/", $callingFrom);
$pos = $callingFrom[1];

$webtitle = "Efaktur Search Engine";

if(strpos($pos, 'efaktur-pil.com') === false){
    //remote  

    if(count($callingFrom) == 2){
        $pathdatasource = '../'; //remote: root
        $mysearchpath = '../pdf/';
		$path2 = './';
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
        $path2 = './';
    }
	
	require_once($pathdatasource.'includes.efaktur-pil.com/server_local_setting.php');
	$table = local_db['tablename_dev'];
}


/* search engine
 * 
 */
//require_once($path2.'new_search_engine.php');
require_once($path2.'header.php');
?>		
		<main>
			<div class=" container rounded" style="margin-top:100px; margin-bottom: 10px">

				<?php
				if (isset($_SESSION['username'])) {
    			?>
					<h1>Selamat datang di halaman Tools for CUSTOMER, <?php echo $_SESSION['username']; ?>!</h1>
					<?php if($_SESSION['roles'] == 'admin'){?>
						<a href="<?php echo $path2?>admin/" >View: ADMIN</a> |
					<?php } ?>
					<a href="<?php echo $path2?>view-finance/" >View: FINANCE</a> | <a href="<?php echo $path2?>logout.php">Log Me Out!</a>
				
				
				<hr />
				<?php } ?>
				
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
							<div id="spinner-div" class="spinner-border" role="status">
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

