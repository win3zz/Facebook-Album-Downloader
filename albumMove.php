<?php
if (!session_id()) {
    session_start();
}
ini_set('max_execution_time', 300);
if (!isset($_SESSION['fb_access_token'] )) {
	header('location: https://'.$_SERVER['HTTP_HOST'].'/fb/index.php');
	exit;
}
include 'fb_config.php';
$fb->setDefaultAccessToken($_SESSION['fb_access_token']);

require_once 'libs/SDK/google/vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig('client_id.json');
$client->addScope(Google_Service_Drive::DRIVE);

function photo_download($album_id, $album_name,$folderId,$drive)
{
	global $fb;
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
	
	$fileMetadata = new Google_Service_Drive_DriveFile(array(
	'name' => $album_name,
	'mimeType' => 'application/vnd.google-apps.folder',
	'parents' => array($folderId)
	)); 
	$file = $drive->files->create($fileMetadata, array('fields' => 'id'));
	$album_folder = $file->id;
	
	foreach ($photos as $photo) {
		$fileMetadata = new Google_Service_Drive_DriveFile(array(
		  'name' => uniqid().'.jpg',
		  'parents' => array($album_folder)
		));
		$content = file_get_contents($photo['source'] );
		$file = $drive->files->create($fileMetadata, array(
		  'data' => $content,
		  'mimeType' => 'image/jpeg',
		  'uploadType' => 'multipart',
		  'fields' => 'id'));
	}
}

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
	$client->setAccessToken($_SESSION['access_token']);
	if ($client->isAccessTokenExpired()) {
		echo "Session Expired. Logout and Login Again to Google";
		echo '<script type="text/javascript">window.open("https://' . $_SERVER['HTTP_HOST'] . '/fb/oauth2callback.php", "Drive Access", width="700", height="380");</script>';
		exit;
	}
	$drive = new Google_Service_Drive($client);
	
	// username field is deprecated in graph API for versions v2.0 and higher 
	$res = $fb->get( '/me?fields=first_name,last_name' );
	$user = $res->getGraphObject();
	$username = 'facebook_'.$user->getProperty('first_name').'_'.$user->getProperty('last_name').'_albums';
	
	$fileMetadata = new Google_Service_Drive_DriveFile(array(
	'name' => $username,
	'mimeType' => 'application/vnd.google-apps.folder')); 
	$file = $drive->files->create($fileMetadata, array('fields' => 'id'));
	$folderId = $file->id;

	if(isset($_GET['single']) && !empty($_GET['single'])) {
		$single = explode( ",", $_GET['single'] );
		photo_download($single[0],$single[1],$folderId,$drive);
		echo "success!";
	}else if(isset($_GET['multiples']) && !empty($_GET['multiples']) && count($_GET['multiples']) > 0) {	
		$multiples = explode("-", $_GET['multiples']);
		foreach ( $multiples as $multiple ) {
			$multiple = explode( ",", $multiple );
			photo_download($multiple[0],$multiple[1],$folderId,$drive);
		}
		echo "success!";
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
			photo_download($album['id'],$album['name'],$folderId,$drive);
		}
		echo "success!";
	} else {
		header('location: https://'.$_SERVER['HTTP_HOST'].'/fb/fb-callback.php');
		exit;
	}

} else {
	echo "Please Login First";
	echo '<script type="text/javascript">window.open("https://' . $_SERVER['HTTP_HOST'] . '/fb/oauth2callback.php", "Drive Access", width="700", height="380");</script>';
}
?>
