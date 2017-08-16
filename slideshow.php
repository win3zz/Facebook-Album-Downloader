<?php
if (!session_id()) {
    session_start();
}
if (!isset($_SESSION['fb_access_token'] )) {
	header('location: https://' . $_SERVER['HTTP_HOST'] . '/fb/index.php');
	exit;
}
include 'fb_config.php';

$helper = $fb->getRedirectLoginHelper();

if(isset($_GET['album_id']))
{
	$fb->setDefaultAccessToken($_SESSION['fb_access_token']);
	try {
		$photos_request = $fb->get('/'.$_GET['album_id'].'/photos?fields=source,picture');
		$photos = $photos_request->getGraphEdge();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		echo 'Graph returned an error: ' . $e->getMessage();
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}
}
else
{
	header('HTTP/1.0 400 Bad Request');
	echo 'Bad request';
	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	<title>Full Screen Slideshow</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel='stylesheet' id='fluid_dg-css'  href='libs/css/fluid_dg.css' type='text/css' media='all'>
	<link rel="shortcut icon" href="libs/images/ico/favicon.png">
	<style>
	html, body {
		height: 100%;
		margin: 0;
		padding: 0;
	}
	a {
		color: #fff;
	}
	a:hover {
		text-decoration: none;
	}
	#back_to_fluid_dg {
		background: rgba(2,2,2,.5);
		clear: both;
		display: block;
		height: 20px;
		line-height: 20px;
		padding: 20px;
		position: relative;
		z-index: 1;
	}
	.fluid_container {
		bottom: 0;
		height: 100%;
		left: 0;
		position: fixed;
		right: 0;
		top: 0;
		z-index: 0;
	}
	#fluid_dg_wrap_4 {
		bottom: 0;
		height: 100%;
		left: 0;
		margin-bottom: 0!important;
		position: fixed;
		right: 0;
		top: 0;
	}
	.fluid_dg_bar {
		z-index: 2;
	}
	.fluid_dg_prevThumbs, .fluid_dg_nextThumbs, .fluid_dg_prev, .fluid_dg_next, .fluid_dg_commands, .fluid_dg_thumbs_cont {
		background: #222;
		background: rgba(2, 2, 2, .7);
	}
	.fluid_dg_thumbs {
		margin-top: -100px;
		position: relative;
		z-index: 1;
	}
	.fluid_dg_thumbs_cont {
		border-radius: 0;
		-moz-border-radius: 0;
		-webkit-border-radius: 0;
	}
	.fluid_dg_overlayer {
		opacity: .1;
	}
	.button {
	  width: auto;
	  border: 0 !important;
	  background-color: #b10021;
	  color: white;
	  font-size: 16px;
	  padding: 10px 20px;
	  margin-bottom: 0;
	  text-decoration: none;
	}
	</style>
</head>
<body>
	<div class="fluid_container">
		<div class="fluid_dg_wrap fluid_dg_emboss pattern_1 fluid_dg_white_skin" id="fluid_dg_wrap_4">
			<?php
				foreach ($photos as $photo){
					echo '<div data-thumb="'.$photo['picture'].'" data-src="'.$photo['source'].'"></div>';
				}
			?>
		</div>
	</div>

	<a href="fb-callback.php" class="button" style="cursor: pointer; position: fixed; bottom: 20px; right: 20px;" title="Click to return on Album page">Back to Home</a>
	
	<script type='text/javascript' src='libs/js/jquery-1.10.2.min.js'></script>
	<script type='text/javascript' src='libs/js/jquery.mobile.customized.min.js'></script> 
	<script type='text/javascript' src='libs/js/jquery.easing.1.3.js'></script> 
	<script type='text/javascript' src='libs/js/fluid_dg.min.js'></script> 
	
	<script>
		jQuery(document).ready(function(){
			jQuery(function(){			
				jQuery('#fluid_dg_wrap_4').fluid_dg({
					height: 'auto', 
					loader: 'bar', 
					pagination: false, 
					thumbnails: true, 
					hover: false, 
					opacityOnGrid: false, 
					imagePath: ''
				});
			}); 
		});
	</script>
</body>
</html>