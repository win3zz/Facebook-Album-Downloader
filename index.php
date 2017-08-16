<?php
if (!session_id()) {
    session_start();
}
if (isset($_SESSION['fb_access_token'] )) {
	header('location: https://'.$_SERVER['HTTP_HOST'].'/fb/fb-callback.php');
	exit;
}
?>
<html lang="en">
<head>
	<title>Facebook Album Downloader</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false);
	function hideURLbar(){ window.scrollTo(0,1); } </script>
	
	<link rel="stylesheet" href="libs/css/style_login.css" type="text/css" media="all" /> 
	<link rel="stylesheet" href="libs/css/font-awesome.min.css" />
	<link rel="shortcut icon" href="libs/images/ico/favicon.png">

</head>
<body>
	<section class="agile-main">
		<div class="agile-head">
			<h1>Facebook Album Downloader</h1>
			<p>Download Facebook album with a simply click, Backup with Google Drive. Let's start!</p>
		</div>
		<div class="agile-icon">
			<span><i class="fa fa-hand-o-down" aria-hidden="true"></i></span>
		</div>
		<br/><br/><br/>

		<div class="btn_center">
			<?php
				include 'fb_config.php';
				$helper = $fb->getRedirectLoginHelper();
				$permissions = array(
					'email',
					'user_photos'
				);
				$loginUrl = $helper->getLoginUrl('https://'.$_SERVER['HTTP_HOST'].'/fb/fb-callback.php', $permissions);
			?>
			<a href="<?php echo $loginUrl; ?>" class="button" style="vertical-align:middle"><span>Login With Facebook! </span></a>
		</div>
		
		<br/><br/>
		
		<div class="agile-social">
			<ul class="social-icons">
				<li><a href="https://www.facebook.com/hacker.bipin"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
				<li><a href="https://twitter.com/bipinjitiya"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
				<li><a href="https://plus.google.com/+BipinJitiya"><i class="fa fa-google-plus" aria-hidden="true"></i></a></li>
			</ul>
		</div>
		<div class="agile-copyright">
			<footer>&copy; <script type="text/javascript"> document.write(new Date().getFullYear()); </script> Facebook Album Downloader. All Rights Reserved. Developed by <a href="https://sites.google.com/view/bipinjitiya/" target="blank">Bipin Jitiya</a></footer>
		</div>
	</section>
</body>
</html>