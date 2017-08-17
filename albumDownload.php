<?php
if (!session_id()) {
    session_start();
}
if (!isset($_SESSION['fb_access_token'] )) {
	header('location: https://'.$_SERVER['HTTP_HOST'].'/fb/index.php');
	exit;
}
include 'fb_config.php';
$fb->setDefaultAccessToken($_SESSION['fb_access_token']);

$download_location = 'downloads/'.uniqid().'/';
// Mode is 0777 which means the widest possible access of directory
mkdir($download_location, 0777);

function url_get_contents ($Url) {
    if (!function_exists('curl_init')){ 
        die('CURL is not installed!');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function photo_download($album_id, $album_name)
{
	global $fb;
	global $download_location;
	try {
		$photos_request = $fb->get('/'.$album_id.'/photos?fields=source');
		$photos = $photos_request->getGraphEdge();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		echo 'Graph returned an error: ' . $e->getMessage();
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}
	
	$album_location = $download_location.$album_name;
	
	if (!file_exists($album_location)) {
		mkdir($album_location, 0777);
	}
	
	foreach ($photos as $photo) {
		//file_put_contents( $album_location.'/'.uniqid().".jpg", fopen( $photo['source'], 'r') );
		file_put_contents( $album_location.'/'.uniqid().".jpg", url_get_contents( $photo['source']) );
	}
}

function make_zip()
{
	global $download_location;
	require_once('zipper.php');
	$zipper = new zipper();
	echo $zipper->get_zip($download_location);
}

if(isset($_GET['single']) && !empty($_GET['single'])) {
	$single = explode( ",", $_GET['single'] );
	photo_download($single[0],$single[1]);
	make_zip();
}else if(isset($_GET['multiples']) && !empty($_GET['multiples']) && count($_GET['multiples']) > 0) {	
	$multiples = explode("-", $_GET['multiples']);
	foreach ( $multiples as $multiple ) {
		$multiple = explode( ",", $multiple );
		photo_download($multiple[0],$multiple[1]);
	}
	make_zip();
} else if(isset($_GET['all'])) {
	try {
		$response = $fb->get('/me/albums?fields=id,name');
		$albums = $response->getGraphEdge();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		echo 'Graph returned an error: ' . $e->getMessage();
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}
	
	foreach ($albums as $album) {
		photo_download($album['id'],$album['name']);
	}
	make_zip();
} else {
	header('location: https://'.$_SERVER['HTTP_HOST'].'/fb/fb-callback.php');
	exit;
}
?>
