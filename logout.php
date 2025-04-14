<?php
session_start();

$server_name = $_SERVER['SERVER_NAME'];
        
$callingFrom = $_SERVER['PHP_SELF'];
$callingFrom = explode("/", $callingFrom);
$pos = $callingFrom[1];

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
        $path2 = './';
   
    }
}

$goto = "Location: ".$path2."";

if($_SESSION['roles'] == 'finance'){
	$goto = "Location: view-finance";
} else if($_SESSION['roles'] == 'admin'){
	$goto = "Location: admin";
} 

session_unset();
session_destroy();

header($goto);
?>