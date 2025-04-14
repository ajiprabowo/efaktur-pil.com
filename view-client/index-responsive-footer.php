<!DOCTYPE html>

<?php

/*
 * VIEW: Client
 * Changelog:
 * Search Data using NPWP No. and Invoice No.
 */
	

require_once('../server_local_setting.php');

/* search engine
 * 
 */
require_once('../new_search_engine.php');
?>

<html lang="en">
<head>
    <title>VIEW: CLIENT .. efaktur-pil.com</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,height=device-height,initial-scale=1.0,maximum-scale=1.0, viewport-fit=cover">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
	<link href="//fonts.googleapis.com/css?family=Lato:400,900" rel="stylesheet" type="text/css" />

	<link rel="stylesheet" href=
"https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="../style.css">
	<link rel="stylesheet" href="../style_excelparser.css">
	<link rel="stylesheet" href="../style_listingfiles.css">
	
	<style>
		 /* following style will be applied to every element */
		  *, *::before, *::after{
			 margin: 0;
			 padding: 0;
			 box-sizing: border-box; 
		  }
		  /* write your own css after this part */
		
		#header img {
			position: absolute;
			top: 10px;
			right: 10px;
		}
		.wrapper {
		  	display: flex;

			/*vertically stack children*/
			flex-direction: column;

			/* expand to take full height of page */
			/* min-height overrides both height & max-height */
			min-height: 100vh; 
		}
		
		  main{
			flex-grow: 1;
		  }

		footer {
		  margin-top: auto;

		}

		.responsive{
			max-width: 100%;
			height: auto;
		}
	
	</style>
	
</head>
<body>
	<div class="wrapper">
		<div id="header">
			<img src="../header_logo.png" />
		</div>
		
		<main>
			<div class=" container rounded" style="margin-top:100px; padding:10px;">

				<div class="row">
					<div class="col-lg-5 col-md-6 col-12" style="margin-bottom:15px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);">
						<h3>Retrieved Documents</h3>

						<form method="post" class="<?php if (isset($_POST['search'])){?> was-validated<?php } ?>">
							<div id="responseMaintenance" class="<?php if(!empty($type_search)) { echo $type_search . " px-2"; } ?>" style="margin-bottom:10px">
								<?php if(!empty($message_search)) { echo $message_search; } ?></div>

							<div class="form-group">
								<label for="inputNpwp">NPWP Company Number*</label>
								<input id="inputNpwp" class="form-control form-control-lg <?php if(!empty($_POST['npwplt202503'])){?>is_valid<?php }?>" type="text" placeholder="Nomor NPWP Perusahaan Customer PIL" name="npwplt202503" value="<?php if(!empty($_POST['npwplt202503'])) echo $_POST['npwplt202503'];?>" required >
								<div class="invalid-feedback">
								  Silakan terlebih dahulu isi Nomor NPWP Perusahaannya
								</div>
								<div class="valid-feedback">
								  Looks good!
								</div>

							</div>
							<div class="form-group">							
								<label for="inputInvoiceNo">Invoice Number*</label>
								<input class="form-control form-control-lg <?php if(!empty($_POST['noinv202503'])){?>is_valid<?php }?>" type="text" placeholder="Nomor Invoice" name="noinv202503" value="<?php if(!empty($_POST['noinv202503'])) echo $_POST['noinv202503'];?>" required>
								<div class="invalid-feedback">
								  Silakan terlebih dahulu isi Nomor Invoice nya
								</div>
								<div class="valid-feedback">
								  Looks good!
								</div>
							</div>	
							<div class="form-group">

								<input type="hidden" name="sourcerequest" value="view" />
								<button type="submit" class="btn btn-primary" name="search" value="Submit">Retrieve Documents</button>
								<span class="text-danger" style="float:right">*required (wajib diisi)</span>
							</div>
						</form>
					</div>
					<div class="col-lg-7 col-md-6 col-12">
						<div class="card">
							<div class="card-header text-center">
							<h4>Search result for e-Faktur</h4>				
							<?php
								if(!empty($searchcontent))
									echo $searchcontent;
							?>						
							</div>
						</div>
					</div>
				</div>
			</div>
		</main>
	</div>	
	<footer>
		<img src="../bg_image-footer-fotor-2025040495026.png" class="responsive" />
	</footer>
		
</body>
</html>
