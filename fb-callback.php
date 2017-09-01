<?php
if (!session_id()) {
    session_start();
}

include 'fb_config.php';

$helper = $fb->getRedirectLoginHelper();

if(isset($_GET['logout']))
{
	$fbLogoutUrl = $helper->getLogoutUrl($_SESSION['fb_access_token'], 'https://'.$_SERVER['HTTP_HOST'].'/fb/index.php');    
	session_destroy();
	unset($_SESSION['access_token']);
	header("Location: $fbLogoutUrl");
	exit;
}

if (!isset($_SESSION['fb_access_token'] )) {
	
	try {
		$accessToken = $helper->getAccessToken();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
		echo 'Graph returned an error: ' . $e->getMessage();
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}

	if (! isset($accessToken)) {
		if ($helper->getError()) {
			header('HTTP/1.0 401 Unauthorized');
			echo "Error: " . $helper->getError() . "\n";
			echo "Error Code: " . $helper->getErrorCode() . "\n";
			echo "Error Reason: " . $helper->getErrorReason() . "\n";
			echo "Error Description: " . $helper->getErrorDescription() . "\n";
		} else {
			header('HTTP/1.0 400 Bad Request');
			echo 'Bad request';
		}
		exit;
	}

	// The OAuth 2.0 client handler helps us manage access tokens
	$oAuth2Client = $fb->getOAuth2Client();

	// Get the access token metadata from /debug_token
	$tokenMetadata = $oAuth2Client->debugToken($accessToken);

	// Validation (these will throw FacebookSDKException's when they fail)
	$tokenMetadata->validateAppId('110292192977454');
	$tokenMetadata->validateExpiration();

	if (! $accessToken->isLongLived()) {
		// Exchanges a short-lived access token for a long-lived one
		try {
			$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
		} catch (Facebook\Exceptions\FacebookSDKException $e) {
			echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n";
			exit;
		}
	}

	$_SESSION['fb_access_token'] = (string) $accessToken;
}
?>
<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> 
<![endif]-->
<!--[if IE 7]> <html class="no-js lt-ie9 lt-ie8" lang="en"> 
<![endif]-->
<!--[if IE 8]> <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>

	<title>Facebook Album Downloader</title>
    <meta name="keywords" content="" />
	<meta name="description" content="" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">

    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,700italic,400,600,700,800' rel='stylesheet' type='text/css'>

	<!-- CSS Bootstrap & Custom -->
    <link rel="stylesheet" href="libs/css/bootstrap.min.css">
    <link rel="stylesheet" href="libs/css/animate.css">
    <link rel="stylesheet" href="libs/css/font-awesome.min.css">
	<link rel="stylesheet" href="libs/css/templatemo_misc.css">

	<!-- Main CSS -->
	<link rel="stylesheet" href="libs/css/templatemo_style.css">

	<!-- Favicons -->
    <link rel="shortcut icon" href="libs/images/ico/favicon.png">
	
	<style>
	.chkchecked{
		background-color: #4CAF50;
	}
	</style>

</head>
<body>
	<div class="site-header">
		<div class="main-navigation">
			<div class="responsive_menu">
				<ul>
					<li><a class="show-1 templatemo_home" href="#">Gallery</a></li>
					<li><a class="show-3 templatemo_page3" href="#">Usage</a></li>
					<li><a class="show-4 templatemo_page4" href="#">About</a></li>
					<!--<li><a class="show-5 templatemo_page5" href="#">Contact</a></li>-->
					<li><a href="<?php echo $_SERVER['PHP_SELF'].'?logout'?>">Logout</a></li>
				</ul>
			</div>
			<div class="container">
				<div class="row">
					<div class="col-md-12 responsive-menu">
						<a href="#" class="menu-toggle-btn">
				            <i class="fa fa-bars"></i>
				        </a>
					</div> <!-- /.col-md-12 -->
					<div class="col-md-12 main_menu">
						<ul>
							<li><a class="show-1 templatemo_home" href="#">Gallery</a></li>
							<li><a class="show-3 templatemo_page3" href="#">Usage</a></li>
							<li><a class="show-4 templatemo_page4" href="#">About</a></li>
							<!--<li><a class="show-5 templatemo_page5" href="#">Contact</a></li>-->
							<li><a href="<?php echo $_SERVER['PHP_SELF'].'?logout'?>">Logout</a></li>
						</ul>
					</div> <!-- /.col-md-12 -->
				</div> <!-- /.row -->
			</div> <!-- /.container -->
		</div> <!-- /.main-navigation -->

		<div class="container">
			<br/>
			<div class="row">
				<div class="col-md-1">
					<?php
						$fb->setDefaultAccessToken($_SESSION['fb_access_token']);

						// Get User Details
						$res = $fb->get( '/me?fields=name,gender,email' );
						$user = $res->getGraphObject();
					?>
					<img src="<?php echo 'https://graph.facebook.com/'. $user->getProperty( 'id' ) .'/picture?type=normal'?>" style="float:left;background-color: white; padding: 2px; border: 1px solid #dddddd;"/>
				</div>
				<div class="col-md-3">
					<div style="padding-left:20px;">
						<p>Welcome, <strong><?php echo $user->getProperty( 'name' ); ?></strong></p>
						<p>Gender: <strong><?php echo $user->getProperty( 'gender' ); ?></strong></p>
						<p>Email: <strong><?php echo $user->getProperty( 'email' ); ?></strong></p>
						<p>UserID: <strong><?php echo $user->getProperty( 'id' ); ?></strong></p>
					</div>
				</div>
					
				<div class="col-md-8 text-center">
					<br/>
					<div id="top_buttons">
						<button id="download_all" data-toggle="tooltip" title="Download All Albums" class="button">Download All</button>
						<button id="download_selected" data-toggle="tooltip" title="Download Selected Albums" class="button">Download Selected</button>
						<button id="move_all" data-toggle="tooltip" title="Move All Albums" class="button">Move All</button>
						<button id="move_selected" data-toggle="tooltip" title="Move Selected Albums" class="button">Move Selected</button>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12 text-center">
					<a href="#" class="templatemo_logo">
						<h3><?php echo $user->getProperty( 'name' ); ?>'s Facebook Albums</h3>
					</a> <!-- /.logo -->
				</div> <!-- /.col-md-12 -->
			</div> <!-- /.row -->
		</div> <!-- /.container -->
	</div> <!-- /.site-header -->
	
	<div id="menu-container">
		<div class="content homepage" id="menu-1">
			<div class="container">
					<div class="row">
						<span id="loader" class="navbar-fixed-top"></span>

						<div class="modal fade" id="download-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal">
											<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
										</button>
										<h4 class="modal-title" id="myModalLabel">Albums Notification</h4>
									</div>
									<div class="modal-body" id="display-response">
										<!-- Download Response -->
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
					<?php
						// Get All User Album
						try {
							$response = $fb->get('/me/albums?fields=id,name,cover_photo,count,created_time');
							$albums = $response->getGraphEdge();
						} catch(Facebook\Exceptions\FacebookResponseException $e) {
							echo 'Graph returned an error: ' . $e->getMessage();
							exit;
						} catch(Facebook\Exceptions\FacebookSDKException $e) {
							echo 'Facebook SDK returned an error: ' . $e->getMessage();
							exit;
						}

						// Get All User Album Details
						foreach ($albums as $album) {
							$cover_photo_id = $album['cover_photo']['id'];
							try {
								$photos_request = $fb->get('/'.$album['id'].'/photos?fields=source');
								$photos = $photos_request->getGraphEdge();
							} catch(Facebook\Exceptions\FacebookResponseException $e) {
								echo 'Graph returned an error: ' . $e->getMessage();
								exit;
							} catch(Facebook\Exceptions\FacebookSDKException $e) {
								echo 'Facebook SDK returned an error: ' . $e->getMessage();
								exit;
							}
							$is_cover = False;
							$count_pic = 0;
							$album_cover = '';
							do{
								foreach ($photos as $photo) {
									if ($cover_photo_id == $photo['id'])
									{
										$is_cover = True;
										$album_cover = $photo['source'];
									}
								}
								$count_pic += count($photos);
								$photos = $fb->next($photos);
							}while(!is_null($photos));

							?>
						<div class="col-md-4 col-sm-6">
							<div class="product-item">
								<div class="gallery-item">
									<div class="overlay">
										<a href="<?php echo 'slideshow.php?album_id='.$album['id']; ?>" class="fa fa-expand"></a>
									</div>	
									<?php 
									if ($is_cover)
									{
										echo '<img src="'.$album_cover.'" height="300" alt="'.$album['id'].'"/>'; 
									}
									else
									{
										
									}
									?>
									<div class="content-gallery">
										<h3><?php echo $album['name'].' ('.$count_pic.')'; ?></h3>
									</div>
								</div>
								<label class="btn button btn-block ">
									<input type="checkbox" value="<?php echo $album['id'].','.$album['name']; ?>" class="chk">&nbsp;Select
								</label>
								<button id="<?php echo $album['id'].','.$album['name']; ?>" data-toggle="tooltip" title="Download this album" class="btn button album_download_btn">Download</button>
								<button id="<?php echo $album['id'].','.$album['name']; ?>" data-toggle="tooltip" title="Move this album" class="btn button album_move_btn">Move to Drive</button>
							</div> <!-- /.product-item -->
						</div> <!-- /.col-md-4 -->		
							<?php
						}
					?>
					</div> <!-- /.row -->
			</div> <!-- /.slide-item -->
		</div> <!-- /.products -->

		<div class="content services" id="menu-3">
			<div class="container">
				<div class="row">
					<div class="col-md-9 col-sm-10">
						<div class="inner-content">
							<div class="toggle-content" id="tab1">
								<h2 class="page-title">Usage</h2>
								<p>With this Web application, user can download thousands of Facebook photos in just one click with the fastest speed.</p>
								<p>With this Web application user can directly save Facebook photos to Google Drive</p>
								<p>This application is very helpful to those people who share thier important photos on Facebook and wants to backup all photos without much effort.</p>
								
								<p>Application Features:</p>
								<ul>
									<li>Download thousands of photos in just one click.</li>
									<li>Download all of photos on Facebook (cover,timeline,profile ...and other albums).</li>
									<li>One click back up and sync your photos to Google Drive</li>
									<li>Very simple and easy to use.</li>
									<li>Photos in its original quality.</li>
								</ul>
							</div> <!-- /.toggle-content -->
							<div class="toggle-content" id="tab2">
								<h2 class="page-title">Process</h2>
								<p>Following is process flow or guideline of the application.</p>
								<ul>
									<li>User can Login using Facebook Username & Password. </li>
									<li>Application will ask user to give permission to access their facebook photos.</li>
									<li>All Albums of logged in user are show in gallary with Album cover-photo and Album Name. </li>
									<li>When a user clicks on cover-photo, all photos within that album will display in full screen slideshow.</li>
									<li>At top of screen it will show two button "Download All" and "Move Button".</li>
									<li>When user clicks on "Download All" button, application will Zip all the photos of all album and show a download link to user.</li>
									<li>When user clicks on "Move All" button, application will Move all the photos of all album to user's google drive (Only first time Ask user for drive access permission).</li>
									<li>A "Download" and "Move to Drive" button is display for each album.</li>
									<li>When user clicks on "Download" button, application will Zip all the photos of that album and show a download link to user.</li>
									<li>When user clicks on "Move to Drive" button, application will Move all the photos of that album to user's google drive.</li>
									<li>There is also a checkbox is show on each album. </li>
									<li>when user select any of album using checkbox A "Download Selected" and "Move Selected" button is displayed at top.</li>
									<li>When user clicks on "Download Selected" button, application will Zip all the photos of selected album and show a download link to user.</li>
									<li>When user clicks on "Move Selected" button, application will Move all the photos of that selected album to google drive.</li>
								</ul>
							</div> <!-- /.toggle-content -->
							<div class="toggle-content" id="tab3">
								<h2 class="page-title">Library Used</h2>
								<p>Following are the list of Library which is used in this application development.</p>
								<ul>
									<li><a href="https://developers.google.com/api-client-library/php/">Google API Client Libraries (PHP)</a></li>
									<li><a href="https://github.com/facebook/php-graph-sdk">Facebook Graph SDK v5 for PHP</a></li>
									<li><a href="https://jquery.com/">jQuery: JavaScript Library.</a></li>
									<li><a href="http://getbootstrap.com/">Twitter Bootstrap</a></li>
									<li><a href="http://fontawesome.io/">Font Awesome</a></li>
								</ul>
								<p>References:</p>
								<ul>
									<li><a href="https://github.com/google/google-api-php-client/tree/master/examples">https://github.com/google/google-api-php-client/tree/master/examples</a></li>
									<li><a href="https://github.com/facebook/php-graph-sdk/tree/5.5/docs">https://github.com/facebook/php-graph-sdk/tree/5.5/docs</a></li>
									<li><a href="https://developers.facebook.com/tools/explorer/">https://developers.facebook.com/tools/explorer/</a></li>
									<li><a href="https://stackoverflow.com/">https://stackoverflow.com/</a></li>
									<li><a href="https://benmarshall.me/facebook-php-sdk/">https://benmarshall.me/facebook-php-sdk/</a></li>
								</ul>
							</div> <!-- /.toggle-content -->
						</div> <!-- /.inner-content -->
					</div> <!-- /.col-md-9 -->
					<div class="col-md-3 col-sm-2">
						<div id="icons">
							<ul class="tabs">
								<li>
									<a href="#tab1" class="icon-item">
										<i class="fa fa-book"></i>
										<span>Usage</span>
									</a> <!-- /.icon-item -->
								</li>
								<li>
									<a href="#tab2" class="icon-item">
										<i class="fa fa-tasks"></i>
										<span>Process</span>
									</a> <!-- /.icon-item -->
								</li>
								<li>
									<a href="#tab3" class="icon-item">
										<i class="fa fa-link"></i>
										<span>Library Used</span>
									</a> <!-- /.icon-item -->
								</li>
							</ul>
						</div> <!-- /.icons -->
					</div> <!-- /.col-md-3 -->
				</div> <!-- /.row -->
			</div> <!-- /.container -->
		</div> <!-- /.services -->

		<div class="content about" id="menu-4">
			<div class="container">
				<div class="row">
					<div class="col-md-9 col-sm-10">
						<div class="about-us-content">
							<div class="toggle-content" id="tab4">
								<h2 class="page-title">About</h2>
								<p>Facebook Album Downloader is developed and maintained by Mr. Bipin Jitiya.</p>
								<p>Bipin Jitiya (born 17 december 1994) is an indian web application developer, cyber security consultant and self-proclaimed hack3r. He has great interest in cyber security, exploiting security vulnerabilities & computer virus forensic analysis.</p>
								<p>To View complete profile information <a href="https://sites.google.com/view/bipinjitiya/" target="blank">Click here</a></p>
								<p>Or search him on Google!</p>
								
								
							</div> <!-- /.toggle-content -->
							<div class="toggle-content" id="tab5">
								<h2 class="page-title">Disclaimer</h2>
								<p>The information contained in pages found at this web site are publications of Facebook Album Downloader , for general purposes only and should not be considerd as professional advice or opinion on any specific facts or circumstances. If you have specific questions, you are urged to contact us concerning your own situation.</p>
								<p>Facebook Album Downloader is developed and maintained by Mr. Bipin Jitiya, If you experience any technical difficulty with this Application, or have questions, concerns or suggestions regarding this site, please contact the webmaster.
								Thank you.</p>
							</div> <!-- /.toggle-content -->
							<div class="toggle-content" id="tab6">
								<h2 class="page-title">Privacy Policy</h2>
								<p>Last updated: August 15, 2017</p>
								
								<ul>
									<li>We will not share your information with anyone</li>
									<li>We use your Information only for providing and improving the Service.</li>
								</ul>
								<br/>
								<h2 class="page-title">Security</h2>
								<p>The security of your Personal Information is important to us, but remember that no method of transmission over the Internet, or method of electronic storage is 100% secure. While we strive to use commercially acceptable means to protect your Personal Information, we cannot guarantee its absolute security.</p>
								<br/>
								<h2 class="page-title">Links To Other Sites</h2>
								<p>Our Service may contain links to other sites that are not operated by us. If you click on a third party link, you will be directed to that third party’s site. We strongly advise you to review the Privacy Policy of every site you visit.
								We have no control over, and assume no responsibility for the content, privacy policies or practices of any third party sites or services.</p>
								<br/>
								<h2 class="page-title">Changes To This Privacy Policy</h2>
								<p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page.
								You are advised to review this Privacy Policy periodically for any changes. Changes to this Privacy Policy are effective when they are posted on this page.</p>
								
								<p>If you have any questions about this Privacy Policy, please contact us.</p>
								
							</div> <!-- /.toggle-content -->
						</div> <!-- /.inner-content -->
					</div> <!-- /.col-md-9 -->
					<div class="col-md-3 col-sm-2">
						<div id="icons-about">
							<ul class="tabs">
								<li>
									<a href="#tab4" class="icon-item">
										<i class="fa fa-user"></i>
										<span>About</span>
									</a> <!-- /.icon-item -->
								</li>
								<li>
									<a href="#tab5" class="icon-item">
										<i class="fa fa-coffee"></i>
										<span>Disclaimer</span>
									</a> <!-- /.icon-item -->
								</li>
								<li>
									<a href="#tab6" class="icon-item">
										<i class="fa fa-file-text"></i>
										<span>Privacy Policy</span>
									</a> <!-- /.icon-item -->
								</li>
							</ul>
						</div> <!-- /.icons -->
					</div> <!-- /.col-md-3 -->
				</div> <!-- /.row -->
			</div> <!-- /.container -->
		</div> <!-- /.services -->

	</div> <!-- /#menu-container -->

	<div id="templatemo_footer">
		<div class="container">
			<div class="row">
				<div class="col-md-12 text-center">
					<p>&copy; <script type="text/javascript"> document.write(new Date().getFullYear()); </script> Facebook Album Downloader. All Rights Reserved. Developed by <a href="https://sites.google.com/view/bipinjitiya/" target="blank">Bipin Jitiya</a></p>
				</div> <!-- /.col-md-12 -->
			</div> <!-- /.row -->
		</div> <!-- /.container -->
	</div> <!-- /.templatemo_footer -->

	<!-- Scripts -->
	<script src="libs/js/jquery-1.10.2.min.js"></script>
	<script src="https://npmcdn.com/tether@1.2.4/dist/js/tether.min.js"></script>
	<script src="https://npmcdn.com/bootstrap@4.0.0-alpha.5/dist/js/bootstrap.min.js"></script>
	<script src="libs/js/tabs.js"></script>
	<script src="libs/js/templatemo_custom.js"></script>
	<script src="libs/js/bootstrap-waitingfor.min.js"></script>
	
	<!-- Task Handler -->
	<script>
		$(document).ready(function(){
			
			// On Select Buttons Show/Hide
			var $download_selected = $("#download_selected").hide();
			var $move_selected = $("#move_selected").hide();
			
            $cbs = $('.chk').click(function() {
                $download_selected.toggle( $cbs.is(":checked") );
				$move_selected.toggle( $cbs.is(":checked") );
				$(this).parent().parent().toggleClass("chkchecked"); 
            });
			
			// Asynchronous Process Handler
			function background_downloader(link){
			
				waitingDialog.show('Please Wait...',{
					progressType: 'success'
				});
			
				$.ajax({
					url:link,
					success:function(res){
						$("#display-response").html(res);
						waitingDialog.hide();
						$("#download-modal").modal({
							show: true
						});
					}
				});
			}
			
			// Get All Selected Album ID & NAME
			function get_selected()
			{
				var chkArray = [];
				$(".chk:checked").each(function() {
					chkArray.push($(this).val());
				});
				var selected;
				selected = chkArray.join('-');
				return selected;
			}
			
			// Download Handler Buttons
			$("#download_all").click(function(){
				background_downloader("albumDownload.php?all");
			});
			
			$(".album_download_btn").click(function(){
				var property = $(this).attr("id");
				var album = property.split(",");
				background_downloader("albumDownload.php?single="+album[0]+","+album[1]);
			});
			
			$("#download_selected").click(function(){
				background_downloader("albumDownload.php?multiples="+get_selected());
			});
			
			// Google Drive tranfer handler Buttons
			$("#move_all").click(function(){
				background_downloader("albumMove.php?all");
			});
			
			$(".album_move_btn").click(function(){
				var property = $(this).attr("id");
				var album = property.split(",");
				background_downloader("albumMove.php?single="+album[0]+","+album[1]);
			});
			
			$("#move_selected").click(function(){
				background_downloader("albumMove.php?multiples="+get_selected());
			});	
		});
	</script>

</body>
</html>
