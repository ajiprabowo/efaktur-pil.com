<html lang="en">
<head>
    <title><?php echo $webtitle;?></title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,height=device-height,initial-scale=1.0,maximum-scale=1.0, viewport-fit=cover">
	<link href="//fonts.googleapis.com/css?family=Lato:400,900" rel="stylesheet" type="text/css" />

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	
	<!-- https://sahrebook.com/berita-detail/cara-membuat-hide-show-input-password-dengan-javascript-dan-bootstrap -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

	<link rel="stylesheet" href="<?php echo $path2?>style.css">
	<link rel="stylesheet" href="<?php echo $path2?>style_excelparser.css">
	<link rel="stylesheet" href="<?php echo $path2?>style_listingfiles.css">
	
	
	
	<style>
		 /* following style will be applied to every element */
		  *, *::before, *::after{
			 margin: 0;
			 padding: 0;
			 box-sizing: border-box; 
		  }
		  /* write your own css after this part */
		
		#header{
			height: 100px;
			
		}
		
		#header img {
			position: absolute;
			top: 10px;
			right: 10px;
			
		}
		
		/*https://css-tricks.com/css-fix-for-100vh-in-mobile-webkit/*/
		body {
		  min-height: 100vh;
		  display: flex;
		  flex-direction: column;
		min-height: -webkit-fill-available;
			max-width: 100vw;
		}
		html {
		  height: -webkit-fill-available;
		}
		.wrapper {
		  	display: flex;

			/*vertically stack children*/
			flex-direction: column;

			/* expand to take full height of page */
			/* min-height overrides both height & max-height */
			/*min-height: 100vh;*/ 		
			
			  flex-grow: 1;			 
			
		}
		
		  main{
			flex-grow: 1;
			 min-width:100vw;
			  display: flex; 
			  flex-direction: column;
		  }

		footer {
		  	min-width:100vw;
			margin-top: auto;
			display: flex; 
			
		}
		.child{
			flex: 0 0 auto; /*flex: [flex-grow] [flex-shrink] [flex-basis];*/
			
		}
		.child-three{
			flex: 1 1 auto;
			background-color: #0072bb;
		}

		.responsive{
			max-width: 100%;
			height: auto;
		}
		
		
		#spinner-div {
		  position: fixed;
		  display: none;
		  text-align: center;
		  background-color: rgba(255, 255, 255, 0.8);
		  z-index: 2;
		}
									
	
	</style>
	
	
</head>
<body>
	<div class="wrapper">
		<div id="header">
			<img src="<?php echo $path2?>header_logo.png" />
		</div>