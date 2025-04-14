<!DOCTYPE html>

<?php
session_start();
/*
 * Changelog:
 * Login into Finance Page
 */

$server_name = $_SERVER['SERVER_NAME'];
        
$callingFrom = $_SERVER['PHP_SELF'];
$callingFrom = explode("/", $callingFrom);
$pos = $callingFrom[1];

$webtitle = "FINANCE: Efaktur Search Engine";

if(strpos($pos, 'efaktur-pil.com') === false){
    //remote  
    
	if(count($callingFrom) == 2){
        
		$pathdatasource = '../../'; //remote: root
		$path2 = '../';
			 
    }else{
        $pathdatasource = '../../'; //remote: subdirectory
    	$path2 = '../';
	    
    }
    
}else{
    //local
    $pos = $callingFrom[2];
    
    if(strpos($pos, 'php') === false){
        $pathdatasource = '../../../'; //local: subdirectory
    	$path2 = '../';
	
    }else{
        $pathdatasource = '../../'; //local: root
        $path2 = '../';
        
    }
}


require_once($pathdatasource.'includes.efaktur-pil.com/server_local_setting.php');

use Phppot\DataSource;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

require_once $pathdatasource.'includes.efaktur-pil.com/DataSource.php';
$db = new DataSource();
$conn = $db->getConnection();
require_once ($path2.'vendor/autoload.php'); 

if (isset($_SESSION['username'])) {
    if($_SESSION['roles'] == 'finance'){
		
		header("Location: view-finance");
		exit(); // Terminate script execution after the redirect

	}if($_SESSION['roles'] == 'finance'){
		header("Location: admin");
		exit(); // Terminate script execution after the redirect
	}else{
		header("Location: ".$path2);
		exit();
	}
}

 
if (isset($_POST['login'])) {
	
	//in your php ignore any submissions that inlcude this field
	//https://mountcreo.com/article/protecting-your-html-form-against-spam-using-php/
	if(!empty($_POST['email']) || !empty($_POST['link']) || !empty($_POST['description']) || !empty($_POST['website'])) die();
	
	if($_SESSION['tiket_cap'] == $_POST['kcapt']){
		echo "<script type='text/javascript'>alert('Kode CAPTCHA cocok!')</script>";
		// buat redirect halaman misalnya ke index.php
		// header('location: index.php');	
		
		//Preventing SQL injection: https://developer.okta.com/blog/2020/06/15/sql-injection-in-php
        //https://www.acunetix.com/blog/articles/exploiting-sql-injection-example/
		$username = mysqli_real_escape_string($conn, $_POST['username']);
		$password = hash('sha1', mysqli_real_escape_string($conn, $_POST['password'])); // Hash the input password using SHA-256

		//$sql = "SELECT * FROM tools_users WHERE username='$username' AND password='$password'";
		//$result = mysqli_query($conn, $sql);

		$sql = "SELECT * FROM tools_users WHERE username=? AND password=?";
		$paramType = "ss";
		$paramArray = array(
			$username,
			$password
		);

		$rows = $db->select($sql, $paramType, $paramArray);
		$num_rows_select = $db->getRecordCount($sql, $paramType, $paramArray);
				
		if ($num_rows_select > 0) {
			//$row = mysqli_fetch_assoc($result);
			foreach($rows as $row){
				$_SESSION['username'] = $row['username'];
				$_SESSION['roles'] = $row['roles'];
				
				$id = $row['id'];
			//record last login
				$sql = "UPDATE `tools_users` SET `date` = NOW() WHERE `id` = ?";
				$paramType = "s";
				$paramArray = array(
					$id
				);

				$row = $db->update($sql, $paramType, $paramArray);
				
				header("Location: index.php");
				exit();
			}
		} else {
			echo "<script>alert('Username atau password Anda salah. Silakan coba lagi!')</script>";
			$type = "error";
			$message = "Username atau password Anda salah. Silakan coba lagi!<br />";
		}
	} else {
		echo "<script type='text/javascript'>alert('Kode CAPTCHA Gagal!')</script>";
	}
}
require_once($path2.'header.php');
?>
		
		<main>
			<div class=" container rounded">
				
				<div class="row d-flex justify-content-center">
					<div class="col-lg-5 col-md-6 col-12" style="padding:10px; margin-bottom:15px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);">

						<h3>Login Form (FINANCE)</h3>
						
						<form method="post" class="<?php if (isset($_POST['login'])){?> was-validated<?php } ?>">
							<?php if(!empty($type)) {
									echo '<div id="responseMaintenance" class="'.$type.'px-2" style="margin-bottom:10px">';
									}else{
										echo '<div id="responseMaintenance" class="px-2" style="margin-bottom:10px">';	
									}
								if(!empty($message)) { 
									echo $message; }
							
								echo '</div>';
							?>
							<div class="row mb-3">
								<div class="col">
								<input id="username" class="form-control <?php if(!empty($_POST['username'])){?>is_valid<?php }?>" type="text" placeholder="Username*" name="username" value="<?php if(!empty($_POST['username'])) echo $_POST['username'];?>" required >
								</div>
							</div>
							<div class="row mb-3">							
								<div class="input-group">							
								<input class="form-control pwd <?php if(!empty($_POST['password'])){?>is_valid<?php }?>" type="password" placeholder="Password*" name="password" id="password" value="<?php if(!empty($_POST['password'])) echo $_POST['password'];?>" required>
																									
									<div class="input-group-append">

										<!-- kita pasang onclick untuk merubah icon buka/tutup mata setiap diklik  -->
										<!-- https://sahrebook.com/berita-detail/cara-membuat-hide-show-input-password-dengan-javascript-dan-bootstrap -->
										<span id="mybutton" onclick="change()" class="input-group-text">

										  <!-- icon mata bawaan bootstrap  -->
										  <i class="bi bi-eye-fill"></i>
										</span>
									  </div>
								</div>
							</div>
							<div class="row">
																
								<div class="col mb-1">
									<img src="<?php echo $path2; ?>captcha.php" class="h-100 mb-2">
								</div>
							</div>
							<div class="row mb-3">
								<div class="col">
									<input type="text" name="kcapt" class="form-control <?php if(!empty($_POST['kcapt'])){?>is_valid<?php }?>" required placeholder="Salin ketik kode dari gambar di atas*">
								</div>
								
							</div>								
							
							<div class="row mb-3">
								<div class="col">
									<!-- Honeypot -->
									<input type="text" id="email" name="email" class="d-none"/>
									<input type="text" id="link" name="link" class="d-none" />
									<textarea id="description" name="description" class="d-none"></textarea>
									<input type="text" id="website" name="website" class="d-none" />

									<button type="submit" class="btn btn-primary" name="login" value="Submit">Login Now!</button>
									<span class="text-danger" style="float:right">*required (wajib diisi)</span>
								</div>
							</div>
						</form>
												
					</div>
				</div>
			</div>
		</main>

<script>
    // membuat fungsi change
    function change() {

      // membuat variabel berisi tipe input dari id='pass', id='pass' adalah form input password 
      var x = document.getElementById('password').type;

      //membuat if kondisi, jika tipe x adalah password maka jalankan perintah di bawahnya
      if (x == 'password') {

        //ubah form input password menjadi text
        document.getElementById('password').type = 'text';

        //ubah icon mata terbuka menjadi tertutup
        document.getElementById('mybutton').innerHTML = `<i class="bi bi-eye-slash-fill"></i>`;
      }
      else {

        //ubah form input password menjadi text
        document.getElementById('password').type = 'password';

        //ubah icon mata terbuka menjadi tertutup
        document.getElementById('mybutton').innerHTML = `<i class="bi bi-eye-fill"></i>`;
      }
    }
  </script>

	<?php require_once($path2.'footer.php');?>